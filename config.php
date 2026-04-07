<?php
// ============================================================
// THE PRACTICE — FIRMREADY CONFIGURATION
// Fill in your details below. Upload to practice.finaccord.pro
// ============================================================

define('FR_FIRM_NAME',      'The Practice');
define('FR_FIRM_EMAIL',     'info@kafs-ltd.com');
define('FR_FIRM_PHONE',     '+44 7939 823988');
define('FR_FIRM_ADDRESS',   '5 Brayford Square, London, E1 0SG');
define('FR_FIRM_WEBSITE',   'https://practice.finaccord.pro');
define('FR_ICO_NUMBER',     'ZC112776');

// Dashboard password — pre-set, ready to use
define('FR_PASSWORD_HASH',  '$2y$10$fjuF9qoPdg1N2BAbkSnL1OnRW.tsXLFGpCZ1Y6vXsIMWsTfkFCBaG');

// Base URL — no trailing slash
define('FR_BASE_URL',       'https://practice.finaccord.pro');

// From email for outgoing messages (must match your Hostinger domain)
define('FR_FROM_EMAIL',     'info@kafs-ltd.com');

// Data storage directory
define('FR_DATA_DIR',       __DIR__ . '/fr_data/');

// ============================================================
// STRIPE CONFIGURATION — Phase 3 Billing
// Replace placeholder values with your Stripe keys
// Get them from: https://dashboard.stripe.com/apikeys
// ============================================================

// Test keys (use these while building, then swap for live)
define('FR_STRIPE_SECRET_KEY',     'sk_test_YOUR_SECRET_KEY_HERE');
define('FR_STRIPE_PUBLIC_KEY',     'pk_test_YOUR_PUBLIC_KEY_HERE');
// Create a recurring £12/month price in Stripe Dashboard, paste the ID here
define('FR_STRIPE_PRICE_ID',       'price_YOUR_PRICE_ID_HERE');
// Get this from Stripe Dashboard → Webhooks → your endpoint → Signing secret
define('FR_STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');

// ============================================================
// COMPANIES HOUSE API — Free lookup for UK company details
// Register free at: https://developer.company-information.service.gov.uk
// ============================================================
define('FR_CH_API_KEY', '4eca5c2d-0bce-4f44-aa20-b91a57fd65d0');

// ============================================================
// AUTO SETUP — do not edit below
// ============================================================
if (!is_dir(FR_DATA_DIR)) {
    mkdir(FR_DATA_DIR, 0750, true);
}
if (!file_exists(FR_DATA_DIR . '.htaccess')) {
    file_put_contents(FR_DATA_DIR . '.htaccess', "Deny from all\n");
}
if (!file_exists(FR_DATA_DIR . 'clients.json')) {
    file_put_contents(FR_DATA_DIR . 'clients.json', json_encode([]));
}
