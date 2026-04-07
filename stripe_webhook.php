<?php
// ============================================================
// FIRMREADY — STRIPE WEBHOOK HANDLER
// Receives real-time events from Stripe and updates subscriber
// records in fr_data/subscribers.json
//
// Events handled:
//   checkout.session.completed      → new subscriber created
//   invoice.payment_succeeded       → payment received, access active
//   invoice.payment_failed          → payment failed, send warning
//   customer.subscription.updated   → plan/status change
//   customer.subscription.deleted   → cancelled, disable access
//   customer.subscription.trial_will_end → trial ending in 3 days
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/provision.php';

// ── Verify this request is genuinely from Stripe ────────────

$payload   = file_get_contents('php://input');
$sig       = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$secret    = FR_STRIPE_WEBHOOK_SECRET;

if (!$payload || !$sig) {
    http_response_code(400);
    exit('Missing payload or signature');
}

// Stripe signature verification (without SDK — raw PHP)
$event = verify_stripe_signature($payload, $sig, $secret);
if (!$event) {
    http_response_code(400);
    exit('Invalid signature');
}

// ── Route events ─────────────────────────────────────────────

$type = $event['type'] ?? '';
$obj  = $event['data']['object'] ?? [];

switch ($type) {

    case 'checkout.session.completed':
        // New subscriber completed checkout (may still be in trial)
        handle_checkout_completed($obj);
        break;

    case 'invoice.payment_succeeded':
        // Payment received — activate/renew subscription
        handle_payment_succeeded($obj);
        break;

    case 'invoice.payment_failed':
        // Payment failed — warn subscriber
        handle_payment_failed($obj);
        break;

    case 'customer.subscription.updated':
        // Status change (e.g. trialing → active, active → past_due)
        handle_subscription_updated($obj);
        break;

    case 'customer.subscription.deleted':
        // Subscription cancelled or expired
        handle_subscription_deleted($obj);
        break;

    case 'customer.subscription.trial_will_end':
        // Trial ends in 3 days — send reminder
        handle_trial_ending($obj);
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
exit;

// ── Event handlers ───────────────────────────────────────────

function handle_checkout_completed(array $session): void {
    // Only handle subscription checkouts
    if (($session['mode'] ?? '') !== 'subscription') return;

    $email       = $session['customer_email'] ?? $session['customer_details']['email'] ?? '';
    $customerId  = $session['customer'] ?? '';
    $subId       = $session['subscription'] ?? '';
    $firmName    = $session['metadata']['firm_name'] ?? '';
    $contactName = $session['metadata']['contact_name'] ?? '';

    $subs = load_subscribers();
    // Avoid duplicates
    foreach ($subs as $s) {
        if ($s['stripe_subscription_id'] === $subId) return;
    }

    // Save initial subscriber record (slug + url filled in by provision_firm)
    $id = 'sub_fr_' . bin2hex(random_bytes(6));
    $subs[] = [
        'id'                      => $id,
        'stripe_customer_id'      => $customerId,
        'stripe_subscription_id'  => $subId,
        'email'                   => $email,
        'firm_name'               => $firmName,
        'contact_name'            => $contactName,
        'firm_slug'               => '',
        'firm_url'                => '',
        'status'                  => 'trialing',
        'trial_end'               => '',
        'current_period_end'      => '',
        'created_at'              => date('c'),
        'last_payment_at'         => '',
        'cancelled_at'            => '',
        'notes'                   => '',
    ];
    save_subscribers($subs);

    // Provision the firm instance (creates /firms/{slug}/, sends welcome email with password)
    try {
        provision_firm($firmName, $email, $contactName, $customerId, $subId);
    } catch (\Throwable $e) {
        log_webhook("PROVISION ERROR: " . $e->getMessage());
        // Notify owner of failure
        send_email(
            FR_FIRM_EMAIL,
            "FirmReady — PROVISIONING FAILED for {$firmName}",
            "<p><strong>Provisioning failed</strong> for {$firmName} ({$email}).</p><p>Error: " . htmlspecialchars($e->getMessage()) . "</p><p>Please provision manually.</p>"
        );
        return;
    }

    // Notify owner of new signup
    send_email(
        FR_FIRM_EMAIL,
        "New FirmReady Subscriber — {$firmName}",
        "<p>New subscriber provisioned: <strong>{$firmName}</strong> ({$email})</p><p>Contact: {$contactName}</p><p>Stripe customer: {$customerId}</p><p>Check the <a href='" . FR_BASE_URL . "/admin/'>admin dashboard</a> for details.</p>"
    );

    log_webhook("checkout.completed + provisioned: {$email} ({$firmName})");
}

function handle_payment_succeeded(array $invoice): void {
    $subId      = $invoice['subscription'] ?? '';
    $customerId = $invoice['customer'] ?? '';
    $periodEnd  = $invoice['lines']['data'][0]['period']['end'] ?? 0;
    $amount     = number_format(($invoice['amount_paid'] ?? 0) / 100, 2);

    if (!$subId) return;

    $subs = load_subscribers();
    foreach ($subs as $id => &$sub) {
        if ($sub['stripe_subscription_id'] === $subId) {
            $sub['status']           = 'active';
            $sub['current_period_end'] = $periodEnd ? date('c', $periodEnd) : '';
            $sub['last_payment_at']  = date('c');
            save_subscribers($subs);
            // Update per-firm subscription.json
            update_firm_subscription($subId, [
                'status'             => 'active',
                'current_period_end' => $periodEnd ? date('c', $periodEnd) : '',
            ]);

            // Don't email for £0 trial invoices
            if (($invoice['amount_paid'] ?? 0) > 0) {
                send_email(
                    $sub['email'],
                    'FirmReady — Payment Received £' . $amount,
                    payment_received_html($sub['contact_name'] ?: $sub['firm_name'], $amount, $periodEnd)
                );
            }
            log_webhook("payment_succeeded: {$sub['email']} £{$amount}");
            return;
        }
    }
}

function handle_payment_failed(array $invoice): void {
    $subId      = $invoice['subscription'] ?? '';
    $attemptCount = $invoice['attempt_count'] ?? 1;

    if (!$subId) return;

    $subs = load_subscribers();
    foreach ($subs as $id => &$sub) {
        if ($sub['stripe_subscription_id'] === $subId) {
            $sub['status'] = 'past_due';
            save_subscribers($subs);

            send_email(
                $sub['email'],
                'FirmReady — Payment Failed (Attempt ' . $attemptCount . ')',
                payment_failed_html($sub['contact_name'] ?: $sub['firm_name'], $attemptCount)
            );
            // Also notify firm owner
            send_email(
                FR_FIRM_EMAIL,
                "FirmReady — Payment Failed: {$sub['firm_name']}",
                "<p>Payment failed for <strong>{$sub['firm_name']}</strong> ({$sub['email']}). Attempt {$attemptCount}.</p>"
            );
            log_webhook("payment_failed: {$sub['email']} attempt {$attemptCount}");
            return;
        }
    }
}

function handle_subscription_updated(array $subscription): void {
    $subId  = $subscription['id'] ?? '';
    $status = $subscription['status'] ?? '';
    $trialEnd    = $subscription['trial_end'] ?? 0;
    $periodEnd   = $subscription['current_period_end'] ?? 0;

    if (!$subId) return;

    $subs = load_subscribers();
    foreach ($subs as $id => &$sub) {
        if ($sub['stripe_subscription_id'] === $subId) {
            $sub['status']             = $status;
            $sub['trial_end']          = $trialEnd  ? date('c', $trialEnd)  : '';
            $sub['current_period_end'] = $periodEnd ? date('c', $periodEnd) : '';
            save_subscribers($subs);
            // Mirror into per-firm subscription.json
            update_firm_subscription($subId, [
                'status'             => $status,
                'trial_end'          => $trialEnd  ? date('c', $trialEnd)  : '',
                'current_period_end' => $periodEnd ? date('c', $periodEnd) : '',
            ]);
            log_webhook("subscription_updated: {$sub['email']} → {$status}");
            return;
        }
    }
}

function handle_subscription_deleted(array $subscription): void {
    $subId = $subscription['id'] ?? '';
    if (!$subId) return;

    $subs = load_subscribers();
    foreach ($subs as $id => &$sub) {
        if ($sub['stripe_subscription_id'] === $subId) {
            $sub['status']       = 'cancelled';
            $sub['cancelled_at'] = date('c');
            save_subscribers($subs);
            // Set 7-day grace period on per-firm subscription.json
            suspend_firm_after_grace($subId);

            send_email(
                $sub['email'],
                'FirmReady — Your Subscription Has Been Cancelled',
                cancelled_email_html($sub['contact_name'] ?: $sub['firm_name'])
            );
            send_email(
                FR_FIRM_EMAIL,
                "FirmReady — Subscription Cancelled: {$sub['firm_name']}",
                "<p><strong>{$sub['firm_name']}</strong> ({$sub['email']}) has cancelled. 7-day grace period applied.</p>"
            );
            log_webhook("subscription_deleted: {$sub['email']} — grace period set");
            return;
        }
    }
}

function handle_trial_ending(array $subscription): void {
    $subId    = $subscription['id'] ?? '';
    $trialEnd = $subscription['trial_end'] ?? 0;
    if (!$subId) return;

    $subs = load_subscribers();
    foreach ($subs as $id => $sub) {
        if ($sub['stripe_subscription_id'] === $subId) {
            $endDate = $trialEnd ? date('j F Y', $trialEnd) : 'soon';
            send_email(
                $sub['email'],
                'FirmReady — Your Free Trial Ends in 3 Days',
                trial_ending_html($sub['contact_name'] ?: $sub['firm_name'], $endDate)
            );
            log_webhook("trial_will_end: {$sub['email']} on {$endDate}");
            return;
        }
    }
}

// ── Data helpers ──────────────────────────────────────────────

function load_subscribers(): array {
    $path = FR_DATA_DIR . 'subscribers.json';
    if (!file_exists($path)) return [];
    $fp = fopen($path, 'r');
    flock($fp, LOCK_SH);
    $data = json_decode(stream_get_contents($fp), true) ?: [];
    flock($fp, LOCK_UN);
    fclose($fp);
    return $data;
}

function save_subscribers(array $subs): void {
    $path = FR_DATA_DIR . 'subscribers.json';
    $fp   = fopen($path, 'c+');
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode(array_values($subs), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($fp, LOCK_UN);
    fclose($fp);
}

function log_webhook(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(FR_DATA_DIR . 'webhook.log', $line, FILE_APPEND | LOCK_EX);
}

// ── Stripe signature verification (no SDK needed) ────────────

function verify_stripe_signature(string $payload, string $sig_header, string $secret): ?array {
    // If webhook secret is placeholder, skip verification in dev
    if (strpos($secret, 'YOUR_') === 0) {
        return json_decode($payload, true);
    }

    $parts = [];
    foreach (explode(',', $sig_header) as $part) {
        [$k, $v] = explode('=', $part, 2);
        $parts[$k][] = $v;
    }

    $timestamp = $parts['t'][0] ?? 0;
    // Reject if timestamp is more than 5 minutes old (replay attack protection)
    if (abs(time() - (int)$timestamp) > 300) return null;

    $signed_payload = $timestamp . '.' . $payload;
    $expected       = hash_hmac('sha256', $signed_payload, $secret);
    $received       = $parts['v1'][0] ?? '';

    if (!hash_equals($expected, $received)) return null;

    return json_decode($payload, true);
}

// ── Email helpers ─────────────────────────────────────────────

function send_email(string $to, string $subject, string $html_body): void {
    $firm    = FR_FIRM_NAME;
    $from    = FR_FROM_EMAIL;
    $html    = "<!DOCTYPE html><html><body style=\"font-family:'Segoe UI',Arial,sans-serif;color:#1a2433;background:#f8f7f4\">
<table width='600' align='center' style='background:#fff;border:1px solid #ddd8cf;margin:32px auto;border-radius:4px;overflow:hidden'>
  <tr><td style='background:#1a3558;padding:24px 32px'>
    <span style='font-size:20px;font-weight:800;color:#fff'>{$firm}</span>
    <span style='display:block;color:#c9a84c;font-size:13px;margin-top:4px'>FirmReady</span>
  </td></tr>
  <tr><td style='padding:32px'>{$html_body}
    <p style='margin-top:28px;font-size:12px;color:#888;border-top:1px solid #eee;padding-top:16px'>
      {$firm} &bull; {$from}<br>
      © " . date('Y') . " FirmReady — The Practice
    </p>
  </td></tr>
</table></body></html>";

    $bnd  = md5(uniqid());
    $hdrs = implode("\r\n", [
        "From: {$firm} <{$from}>",
        'MIME-Version: 1.0',
        "Content-Type: multipart/alternative; boundary=\"{$bnd}\"",
    ]);
    $plain = strip_tags(str_replace(['<br>','<p>','</p>'], ["\n","\n",""], $html));
    $body  = "--{$bnd}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$plain}\r\n"
           . "--{$bnd}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n"
           . "--{$bnd}--";
    mail($to, $subject, $body, $hdrs);
}

// ── Email templates ───────────────────────────────────────────

function welcome_email_html(string $name, string $firm): string {
    $login = FR_BASE_URL . '/dashboard.php';
    return "<p>Dear {$name},</p>
<p>Welcome to <strong>FirmReady</strong> — your 14-day free trial has started. No payment will be taken until your trial ends.</p>
<p><strong>What you get:</strong></p>
<ul style='line-height:2'>
  <li>✅ Unlimited engagement letters — send, sign, and store</li>
  <li>✅ AML Customer Due Diligence records</li>
  <li>✅ MTD for Income Tax tracker with automated reminders</li>
  <li>✅ Statutory deadline tracker (CT, VAT, CH, Payroll, SA)</li>
  <li>✅ Professional correspondence hub with 16 templates</li>
  <li>✅ GDPR Data Processing Agreements — auto-generated</li>
  <li>✅ Companies House auto-lookup</li>
  <li>✅ Digital client onboarding</li>
</ul>
<p style='margin:24px 0'>
  <a href='{$login}' style='background:#1a3558;color:#fff;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:700'>Access Your Dashboard</a>
</p>
<p>After 14 days, your subscription is just <strong>£12/month</strong> — cancel anytime.</p>
<p>Any questions? Reply to this email or call " . FR_FIRM_PHONE . ".</p>
<p>Kind regards,<br><strong>" . FR_FIRM_NAME . "</strong></p>";
}

function payment_received_html(string $name, string $amount, int $periodEnd): string {
    $next = $periodEnd ? date('j F Y', $periodEnd) : 'next month';
    return "<p>Dear {$name},</p>
<p>Thank you — we've received your payment of <strong>£{$amount}</strong> for FirmReady.</p>
<p>Your subscription is active. Next payment: <strong>{$next}</strong>.</p>
<p>You can manage your subscription at any time via the Stripe Customer Portal.</p>
<p>Kind regards,<br><strong>" . FR_FIRM_NAME . "</strong></p>";
}

function payment_failed_html(string $name, int $attempt): string {
    $msg = $attempt >= 3
        ? 'This is our final attempt. If payment is not resolved your account may be suspended.'
        : 'Stripe will automatically retry. Please ensure your payment method is up to date.';
    return "<p>Dear {$name},</p>
<p>We were unable to collect your FirmReady subscription payment (attempt {$attempt}).</p>
<p>{$msg}</p>
<p>To update your payment details, please contact us at <a href='mailto:" . FR_FIRM_EMAIL . "'>" . FR_FIRM_EMAIL . "</a>.</p>
<p>Kind regards,<br><strong>" . FR_FIRM_NAME . "</strong></p>";
}

function trial_ending_html(string $name, string $endDate): string {
    return "<p>Dear {$name},</p>
<p>Your FirmReady free trial ends on <strong>{$endDate}</strong> — just 3 days away.</p>
<p>After that, your subscription will automatically continue at <strong>£12/month</strong>. No action needed if you'd like to keep access.</p>
<p>If you'd like to cancel before being charged, you can do so by contacting us at <a href='mailto:" . FR_FIRM_EMAIL . "'>" . FR_FIRM_EMAIL . "</a>.</p>
<p>We hope FirmReady has made a difference to your practice over the past 11 days!</p>
<p>Kind regards,<br><strong>" . FR_FIRM_NAME . "</strong></p>";
}

function cancelled_email_html(string $name): string {
    return "<p>Dear {$name},</p>
<p>Your FirmReady subscription has been cancelled. Your access will remain until the end of your current billing period.</p>
<p>We're sorry to see you go. If there's anything we could have done better, we'd love to hear your feedback — reply to this email.</p>
<p>If you change your mind, you can resubscribe at any time at <a href='" . FR_BASE_URL . "/subscribe.php'>" . FR_BASE_URL . "/subscribe.php</a>.</p>
<p>Kind regards,<br><strong>" . FR_FIRM_NAME . "</strong></p>";
}
