<?php
// ============================================================
// FIRMREADY — PROVISIONING LIBRARY
// Included by stripe_webhook.php — NOT a standalone page
// Creates a new isolated firm instance when a subscriber signs up
// ============================================================

if (!defined('FR_BASE_URL')) die('Direct access not permitted');

// ── Public entry point ────────────────────────────────────────

function provision_firm(string $firm_name, string $email, string $contact_name,
                        string $stripe_customer_id, string $stripe_subscription_id): array {

    $slug     = generate_firm_slug($firm_name);
    $firm_dir = __DIR__ . '/firms/' . $slug . '/';
    $base_url = FR_BASE_URL . '/firms/' . $slug;

    // Create directory structure
    mkdir($firm_dir,                    0755, true);
    mkdir($firm_dir . 'fr_data/',       0750, true);
    mkdir($firm_dir . 'fr_data/pdfs/',  0750, true);

    // Initialise data files
    file_put_contents($firm_dir . 'fr_data/.htaccess',    "Deny from all\n");
    file_put_contents($firm_dir . 'fr_data/clients.json', '[]');
    file_put_contents($firm_dir . 'fr_data/letters.json', '[]');

    // Firm settings (editable via setup page)
    file_put_contents($firm_dir . 'fr_data/firm_settings.json', json_encode([
        'firm_name'    => $firm_name,
        'firm_email'   => $email,
        'firm_phone'   => '',
        'firm_address' => '',
        'firm_website' => '',
        'ico_number'   => '',
        'setup_complete' => false,
    ], JSON_PRETTY_PRINT));

    // Subscription record
    file_put_contents($firm_dir . 'fr_data/subscription.json', json_encode([
        'status'                 => 'trialing',
        'stripe_customer_id'     => $stripe_customer_id,
        'stripe_subscription_id' => $stripe_subscription_id,
        'firm_name'              => $firm_name,
        'email'                  => $email,
        'contact_name'           => $contact_name,
        'slug'                   => $slug,
        'client_limit'           => 50,
        'created_at'             => date('c'),
        'trial_end'              => '',
        'current_period_end'     => '',
        'grace_period_end'       => '',
    ], JSON_PRETTY_PRINT));

    // Generate login password
    $password = generate_firm_password();
    $hash     = password_hash($password, PASSWORD_BCRYPT);

    // Write firm config.php
    file_put_contents($firm_dir . 'config.php', build_firm_config($hash, $base_url));

    // Copy application files
    $app_files = [
        'dashboard.php', 'api.php', 'client.php',
        'letters.php',   'letter_view.php', 'onboard.php', 'setup.php', 'help.php',
    ];
    foreach ($app_files as $f) {
        if (file_exists(__DIR__ . '/' . $f)) {
            copy(__DIR__ . '/' . $f, $firm_dir . $f);
        }
    }

    // Protect firms directory if .htaccess doesn't exist yet
    $firms_htaccess = __DIR__ . '/firms/.htaccess';
    if (!file_exists($firms_htaccess)) {
        file_put_contents($firms_htaccess, "Options -Indexes\n");
    }

    // Update main subscribers.json with slug + URL
    update_subscriber_slug($stripe_subscription_id, $slug, $base_url . '/dashboard.php');

    // Send welcome email
    send_firm_welcome($email, $contact_name ?: $firm_name, $firm_name,
                      $base_url . '/setup.php', $password);

    provision_log("Provisioned: {$firm_name} → /firms/{$slug}/");

    return ['slug' => $slug, 'url' => $base_url, 'password' => $password];
}

// ── Update a firm's subscription.json when Stripe events fire ─

function update_firm_subscription(string $stripe_subscription_id, array $updates): void {
    $slug = find_firm_slug_by_subscription($stripe_subscription_id);
    if (!$slug) return;

    $path = __DIR__ . '/firms/' . $slug . '/fr_data/subscription.json';
    if (!file_exists($path)) return;

    $data = json_decode(file_get_contents($path), true) ?: [];
    foreach ($updates as $k => $v) {
        $data[$k] = $v;
    }
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    provision_log("Updated subscription [{$stripe_subscription_id}]: " . json_encode($updates));
}

function suspend_firm_after_grace(string $stripe_subscription_id): void {
    // Called on subscription.deleted — sets grace_period_end to 7 days from now
    $grace = date('c', strtotime('+7 days'));
    update_firm_subscription($stripe_subscription_id, [
        'status'           => 'cancelled',
        'grace_period_end' => $grace,
    ]);
}

// ── Helpers ───────────────────────────────────────────────────

function generate_firm_slug(string $firm_name): string {
    $slug = strtolower($firm_name);
    $slug = preg_replace('/[^a-z0-9\s]/', '', $slug);
    $slug = trim(preg_replace('/\s+/', '-', $slug), '-');
    $slug = substr($slug, 0, 40) ?: 'firm';
    $base = $slug; $i = 2;
    while (is_dir(__DIR__ . '/firms/' . $slug)) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function generate_firm_password(int $len = 10): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
    $pass  = '';
    for ($i = 0; $i < $len; $i++) $pass .= $chars[random_int(0, strlen($chars) - 1)];
    return $pass;
}

function build_firm_config(string $hash, string $base_url): string {
    $ch_key = defined('FR_CH_API_KEY') ? FR_CH_API_KEY : '';
    $from   = FR_FROM_EMAIL;
    $escaped_hash = addslashes($hash);
    return <<<PHP
<?php
// ============================================================
// FIRMREADY — FIRM INSTANCE CONFIG
// Auto-generated by provisioning system.
// Firm details are stored in fr_data/firm_settings.json
// and can be updated via the Setup page in the dashboard.
// ============================================================

// Load editable firm settings from data directory
\$_frs = file_exists(__DIR__ . '/fr_data/firm_settings.json')
    ? (json_decode(file_get_contents(__DIR__ . '/fr_data/firm_settings.json'), true) ?: [])
    : [];

define('FR_FIRM_NAME',    \$_frs['firm_name']    ?? 'Your Practice');
define('FR_FIRM_EMAIL',   \$_frs['firm_email']   ?? '');
define('FR_FIRM_PHONE',   \$_frs['firm_phone']   ?? '');
define('FR_FIRM_ADDRESS', \$_frs['firm_address'] ?? '');
define('FR_FIRM_WEBSITE', \$_frs['firm_website'] ?? '');
define('FR_ICO_NUMBER',   \$_frs['ico_number']   ?? '');

define('FR_PASSWORD_HASH',  '{$escaped_hash}');
define('FR_BASE_URL',       '{$base_url}');
define('FR_FROM_EMAIL',     '{$from}');
define('FR_DATA_DIR',       __DIR__ . '/fr_data/');
define('FR_CH_API_KEY',     '{$ch_key}');

// Stripe not needed per-firm — handled centrally
define('FR_STRIPE_SECRET_KEY',     '');
define('FR_STRIPE_PUBLIC_KEY',     '');
define('FR_STRIPE_PRICE_ID',       '');
define('FR_STRIPE_WEBHOOK_SECRET', '');

if (!is_dir(FR_DATA_DIR))         mkdir(FR_DATA_DIR, 0750, true);
if (!file_exists(FR_DATA_DIR . '.htaccess'))
    file_put_contents(FR_DATA_DIR . '.htaccess', "Deny from all\n");
if (!file_exists(FR_DATA_DIR . 'clients.json'))
    file_put_contents(FR_DATA_DIR . 'clients.json', json_encode([]));
PHP;
}

function find_firm_slug_by_subscription(string $sub_id): ?string {
    $subs_path = __DIR__ . '/fr_data/subscribers.json';
    if (!file_exists($subs_path)) return null;
    $subs = json_decode(file_get_contents($subs_path), true) ?: [];
    foreach ($subs as $s) {
        if (($s['stripe_subscription_id'] ?? '') === $sub_id) {
            return $s['firm_slug'] ?? null;
        }
    }
    return null;
}

function update_subscriber_slug(string $sub_id, string $slug, string $url): void {
    $path = __DIR__ . '/fr_data/subscribers.json';
    $subs = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
    foreach ($subs as &$s) {
        if (($s['stripe_subscription_id'] ?? '') === $sub_id) {
            $s['firm_slug'] = $slug;
            $s['firm_url']  = $url;
            break;
        }
    }
    file_put_contents($path, json_encode(array_values($subs), JSON_PRETTY_PRINT));
}

function provision_log(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(__DIR__ . '/fr_data/provision.log', $line, FILE_APPEND | LOCK_EX);
}

function send_firm_welcome(string $to, string $name, string $firm,
                           string $setup_url, string $password): void {
    $from_name = FR_FIRM_NAME;
    $from_addr = FR_FROM_EMAIL;
    $subject   = 'Your FirmReady Account Is Ready — Login Details Inside';
    $html = "<!DOCTYPE html><html><body style=\"font-family:'Segoe UI',Arial,sans-serif;background:#f8f7f4\">
<table width='600' align='center' style='background:#fff;border:1px solid #ddd8cf;margin:32px auto;border-radius:4px;overflow:hidden'>
<tr><td style='background:#1a3558;padding:24px 32px'>
  <span style='font-size:22px;font-weight:800;color:#fff'>FirmReady</span>
  <span style='display:block;color:#c9a84c;font-size:13px;margin-top:4px'>Your account is ready</span>
</td></tr>
<tr><td style='padding:32px;color:#1a2433'>
  <p>Dear {$name},</p>
  <p>Your <strong>FirmReady</strong> account for <strong>{$firm}</strong> has been created. Here are your login details — keep them safe:</p>
  <div style='background:#f8f7f4;border:1px solid #ddd8cf;border-radius:4px;padding:16px 20px;margin:20px 0;font-size:15px'>
    <div><strong>Dashboard:</strong> <a href='{$setup_url}' style='color:#1a3558'>{$setup_url}</a></div>
    <div style='margin-top:10px'><strong>Password:</strong> <span style='font-family:monospace;background:#e8edf5;padding:3px 8px;border-radius:3px'>{$password}</span></div>
  </div>
  <p>Click below to complete your firm setup (takes 2 minutes) and you'll be ready to send your first engagement letter:</p>
  <p style='margin:24px 0'>
    <a href='{$setup_url}' style='background:#1a3558;color:#fff;padding:14px 32px;border-radius:4px;text-decoration:none;font-weight:700;font-size:15px'>Complete My Setup →</a>
  </p>
  <p style='font-size:13px;color:#555'>Your 14-day free trial has started. After that, just <strong>£12/month</strong> — cancel anytime. No contracts.</p>
  <p style='font-size:12px;color:#888;border-top:1px solid #eee;padding-top:16px;margin-top:24px'>
    {$from_name} &bull; {$from_addr}<br>
    This email was sent because you signed up for FirmReady.
  </p>
</td></tr>
</table></body></html>";

    $bnd  = md5(uniqid());
    $hdrs = implode("\r\n", [
        "From: {$from_name} <{$from_addr}>",
        'MIME-Version: 1.0',
        "Content-Type: multipart/alternative; boundary=\"{$bnd}\"",
    ]);
    $body = "--{$bnd}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n"
          . "Dear {$name},\n\nYour FirmReady dashboard: {$setup_url}\nPassword: {$password}\n\nKind regards,\n{$from_name}\r\n"
          . "--{$bnd}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n--{$bnd}--";
    mail($to, $subject, $body, $hdrs);
}
