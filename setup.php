<?php
require_once __DIR__ . '/config.php';
session_start();

// Must be logged in
if (empty($_SESSION['fr_auth'])) {
    header('Location: ' . FR_BASE_URL . '/dashboard.php');
    exit;
}

// Load current settings
$settings_path = FR_DATA_DIR . 'firm_settings.json';
$settings = file_exists($settings_path)
    ? (json_decode(file_get_contents($settings_path), true) ?: [])
    : [];

$saved  = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = [
        'firm_name'      => trim(strip_tags($_POST['firm_name']      ?? '')),
        'firm_email'     => trim(strip_tags($_POST['firm_email']      ?? '')),
        'firm_phone'     => trim(strip_tags($_POST['firm_phone']      ?? '')),
        'firm_address'   => trim(strip_tags($_POST['firm_address']    ?? '')),
        'firm_website'   => trim(strip_tags($_POST['firm_website']    ?? '')),
        'ico_number'     => trim(strip_tags($_POST['ico_number']      ?? '')),
        'setup_complete' => true,
    ];
    if (!$new['firm_name'])  $errors[] = 'Firm name is required.';
    if (!filter_var($new['firm_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

    // Handle logo upload
    if (!empty($_FILES['logo']['tmp_name'])) {
        $allowed = ['image/png','image/jpeg','image/gif','image/webp'];
        if (!in_array($_FILES['logo']['type'], $allowed)) {
            $errors[] = 'Logo must be PNG, JPG, GIF or WebP.';
        } elseif ($_FILES['logo']['size'] > 500000) {
            $errors[] = 'Logo must be under 500KB.';
        } else {
            $img_data = file_get_contents($_FILES['logo']['tmp_name']);
            $new['logo_b64'] = 'data:' . $_FILES['logo']['type'] . ';base64,' . base64_encode($img_data);
        }
    } else {
        $new['logo_b64'] = $settings['logo_b64'] ?? '';
    }

    // New password
    $new_pass = trim($_POST['new_password'] ?? '');
    if ($new_pass) {
        if (strlen($new_pass) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } else {
            // Write new hash to config.php
            $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $config_content = file_get_contents(__DIR__ . '/config.php');
            $config_content = preg_replace(
                "/define\('FR_PASSWORD_HASH',\s*'[^']+'\)/",
                "define('FR_PASSWORD_HASH',  '" . addslashes($new_hash) . "')",
                $config_content
            );
            file_put_contents(__DIR__ . '/config.php', $config_content);
        }
    }

    if (!$errors) {
        file_put_contents($settings_path, json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $settings = $new;
        $saved = true;
        // If this is first setup, redirect to dashboard
        if (!empty($_POST['first_setup'])) {
            header('Location: ' . FR_BASE_URL . '/dashboard.php?welcome=1');
            exit;
        }
    }
}

$is_first = empty($settings['setup_complete']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $is_first ? 'Welcome — Complete Your Setup' : 'Firm Settings' ?> | FirmReady</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a2433;min-height:100vh}
.wrap{max-width:680px;margin:0 auto;padding:48px 16px 80px}
.card{background:#fff;border:1px solid #ddd8cf;border-radius:6px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)}
.card-head{background:#1a3558;padding:28px 36px}
.card-head h1{font-size:22px;font-weight:800;color:#fff}
.card-head p{font-size:13px;color:#c9a84c;margin-top:4px}
.card-body{padding:36px}
.section{margin-bottom:32px;padding-bottom:28px;border-bottom:1px solid #f0ede6}
.section:last-child{border-bottom:none;margin-bottom:0}
.section-title{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#1a3558;margin-bottom:18px}
.fg{margin-bottom:16px}
.fg label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#64748b;margin-bottom:6px}
.fg input,.fg textarea{width:100%;padding:10px 12px;border:1.5px solid #ddd8cf;border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:14px;color:#1a2433;background:#fafaf8;outline:none;transition:border-color .15s}
.fg input:focus,.fg textarea:focus{border-color:#1a3558;background:#fff}
.fg textarea{min-height:80px;resize:vertical}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn{padding:12px 28px;border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:14px;font-weight:700;cursor:pointer;border:none}
.btn-primary{background:#1a3558;color:#fff}
.btn-primary:hover{background:#0f2238}
.btn-outline{background:transparent;border:1.5px solid #1a3558;color:#1a3558}
.btn-outline:hover{background:#f0f4f8}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:13px}
.alert-ok{background:#d1fae5;border:1px solid #a7f3d0;color:#065f46}
.alert-err{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b}
.logo-preview{max-height:60px;max-width:200px;object-fit:contain;margin-top:8px;border:1px solid #ddd8cf;border-radius:4px;padding:4px}
.welcome-banner{background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:20px 24px;margin-bottom:28px}
.welcome-banner h2{font-size:18px;color:#92400e;margin-bottom:8px}
.welcome-banner p{font-size:13px;color:#b45309;line-height:1.6}
@media(max-width:540px){.fg-row{grid-template-columns:1fr}.card-body{padding:24px 20px}}
</style>
</head>
<body>
<div class="wrap">
  <?php if ($is_first): ?>
  <div class="welcome-banner">
    <h2>🎉 Welcome to FirmReady!</h2>
    <p>Complete your firm details below so your engagement letters, correspondence, and client portal all show the right information. This takes less than 2 minutes.</p>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-head">
      <h1><?= $is_first ? '⚙ Complete Your Firm Setup' : '⚙ Firm Settings' ?></h1>
      <p><?= $is_first ? 'Fill in your firm details to get started' : 'Update your firm details and branding' ?></p>
    </div>
    <div class="card-body">
      <?php if ($saved): ?>
        <div class="alert alert-ok">✅ Settings saved successfully.</div>
      <?php endif; ?>
      <?php foreach ($errors as $err): ?>
        <div class="alert alert-err">⚠️ <?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>

      <form method="POST" enctype="multipart/form-data">
        <?php if ($is_first): ?><input type="hidden" name="first_setup" value="1"><?php endif; ?>

        <!-- FIRM IDENTITY -->
        <div class="section">
          <div class="section-title">Firm Identity</div>
          <div class="fg-row">
            <div class="fg">
              <label>Firm Name *</label>
              <input type="text" name="firm_name" value="<?= htmlspecialchars($settings['firm_name'] ?? '') ?>" placeholder="Smith & Co Accountants" required>
            </div>
            <div class="fg">
              <label>ICO Registration Number</label>
              <input type="text" name="ico_number" value="<?= htmlspecialchars($settings['ico_number'] ?? '') ?>" placeholder="ZC123456">
            </div>
          </div>
          <div class="fg">
            <label>Registered / Trading Address</label>
            <textarea name="firm_address" placeholder="1 High Street&#10;London&#10;EC1A 1BB"><?= htmlspecialchars($settings['firm_address'] ?? '') ?></textarea>
          </div>
          <div class="fg-row">
            <div class="fg">
              <label>Email Address *</label>
              <input type="email" name="firm_email" value="<?= htmlspecialchars($settings['firm_email'] ?? '') ?>" placeholder="info@smithco.co.uk" required>
            </div>
            <div class="fg">
              <label>Phone Number</label>
              <input type="tel" name="firm_phone" value="<?= htmlspecialchars($settings['firm_phone'] ?? '') ?>" placeholder="+44 20 1234 5678">
            </div>
          </div>
          <div class="fg">
            <label>Website</label>
            <input type="url" name="firm_website" value="<?= htmlspecialchars($settings['firm_website'] ?? '') ?>" placeholder="https://www.smithco.co.uk">
          </div>
        </div>

        <!-- LOGO -->
        <div class="section">
          <div class="section-title">Firm Logo</div>
          <?php if (!empty($settings['logo_b64'])): ?>
            <img src="<?= $settings['logo_b64'] ?>" class="logo-preview" alt="Current logo"><br><br>
          <?php endif; ?>
          <div class="fg">
            <label>Upload Logo <span style="font-weight:400;text-transform:none;font-size:11px">(PNG or JPG, max 500KB — appears on all letters)</span></label>
            <input type="file" name="logo" accept="image/*" style="font-size:13px">
          </div>
        </div>

        <!-- SECURITY -->
        <div class="section">
          <div class="section-title">Change Password</div>
          <div class="fg">
            <label>New Password <span style="font-weight:400;text-transform:none;font-size:11px">(leave blank to keep current password)</span></label>
            <input type="password" name="new_password" placeholder="Minimum 8 characters" autocomplete="new-password">
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px">
          <?php if (!$is_first): ?>
            <a href="<?= FR_BASE_URL ?>/dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">
            <?= $is_first ? 'Save & Go to Dashboard →' : 'Save Settings' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
