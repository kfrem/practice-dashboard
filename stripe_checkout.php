<?php
// ============================================================
// FIRMREADY — STRIPE CHECKOUT SESSION CREATOR
// Receives form POST from subscribe.php, creates a Stripe
// Checkout session, then redirects to Stripe's hosted page.
// ============================================================

require_once __DIR__ . '/config.php';

// ── Input validation ────────────────────────────────────────

$first_name = trim(strip_tags($_POST['first_name'] ?? ''));
$last_name  = trim(strip_tags($_POST['last_name']  ?? ''));
$firm_name  = trim(strip_tags($_POST['firm_name']  ?? ''));
$email      = trim($_POST['email'] ?? '');

if (!$first_name || !$last_name || !$firm_name || !$email) {
    redirect_back('Please fill in all fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_back('Please enter a valid email address.');
}

// ── Stripe API call ─────────────────────────────────────────

$base_url    = FR_BASE_URL;
$price_id    = FR_STRIPE_PRICE_ID;
$secret_key  = FR_STRIPE_SECRET_KEY;
$full_name   = $first_name . ' ' . $last_name;

// Build POST body for Stripe Checkout Session
$params = http_build_query([
    'mode'                                  => 'subscription',
    'line_items[0][price]'                  => $price_id,
    'line_items[0][quantity]'               => '1',
    'customer_email'                        => $email,
    'subscription_data[trial_period_days]'  => '14',
    'subscription_data[metadata][firm_name]'=> $firm_name,
    'subscription_data[metadata][contact]'  => $full_name,
    'success_url'                           => $base_url . '/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'                            => $base_url . '/cancel.php',
    'allow_promotion_codes'                 => 'true',
    'billing_address_collection'            => 'auto',
    'metadata[firm_name]'                   => $firm_name,
    'metadata[contact_name]'                => $full_name,
]);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $params,
    CURLOPT_USERPWD        => $secret_key . ':',
    CURLOPT_HTTPHEADER     => ['Stripe-Version: 2024-06-20'],
    CURLOPT_TIMEOUT        => 15,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    redirect_back('Connection error. Please try again.');
}

$data = json_decode($response, true);

if ($http_code !== 200 || empty($data['url'])) {
    $stripe_msg = $data['error']['message'] ?? 'Unknown error from payment provider.';
    error_log('[FirmReady Stripe] HTTP ' . $http_code . ': ' . $stripe_msg);
    redirect_back('Payment setup failed: ' . htmlspecialchars($stripe_msg));
}

// ── Redirect to Stripe Checkout ─────────────────────────────

header('Location: ' . $data['url']);
exit;

// ── Helper ──────────────────────────────────────────────────

function redirect_back(string $msg): void {
    $encoded = urlencode($msg);
    header('Location: ' . FR_BASE_URL . '/subscribe.php?error=' . $encoded);
    exit;
}
