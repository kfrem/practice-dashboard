<?php
// ============================================================
// FIRMREADY — SUSPENDED ACCOUNT PAGE
// Shown when a firm's subscription has expired beyond the
// 7-day grace period. Loaded by dashboard.php redirect.
// ============================================================

require_once __DIR__ . '/config.php';
session_start();
session_destroy();

$sub_file = FR_DATA_DIR . 'subscription.json';
$sub = file_exists($sub_file)
    ? (json_decode(file_get_contents($sub_file), true) ?: [])
    : [];

$firm_name = FR_FIRM_NAME;
$email     = FR_FIRM_EMAIL;
$status    = $sub['status'] ?? 'cancelled';
$grace_end = $sub['grace_period_end'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Account Suspended — FirmReady</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a2433;min-height:100vh;display:flex;align-items:center;justify-content:center}
.wrap{max-width:560px;width:100%;padding:24px 16px}
.card{background:#fff;border:1px solid #ddd8cf;border-radius:6px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center}
.card-head{background:#c0392b;padding:32px 36px}
.card-head h1{font-size:22px;font-weight:800;color:#fff}
.card-head p{font-size:13px;color:#fca5a5;margin-top:6px}
.card-body{padding:40px 36px}
.icon{font-size:52px;margin-bottom:16px}
.msg{font-size:16px;font-weight:700;color:#1a3558;margin-bottom:12px}
.detail{font-size:14px;color:#64748b;line-height:1.7;margin-bottom:28px}
.btn{display:inline-block;padding:13px 32px;border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:14px;font-weight:700;cursor:pointer;border:none;text-decoration:none}
.btn-primary{background:#1a3558;color:#fff}
.btn-primary:hover{background:#0f2238}
.btn-outline{background:transparent;border:2px solid #1a3558;color:#1a3558;margin-left:12px}
.btn-outline:hover{background:#f0f4f8}
.divider{margin:28px 0;border:none;border-top:1px solid #f0ede6}
.contact-box{background:#f8f7f4;border:1px solid #ddd8cf;border-radius:4px;padding:16px 20px;font-size:13px;color:#374151}
.contact-box strong{display:block;margin-bottom:6px;color:#1a3558}
.contact-box a{color:#1a3558;font-weight:600}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="card-head">
      <h1>Account Suspended</h1>
      <p>FirmReady — <?= htmlspecialchars($firm_name) ?></p>
    </div>
    <div class="card-body">
      <div class="icon">🔒</div>
      <div class="msg">Your FirmReady subscription has ended</div>
      <div class="detail">
        <?php if ($grace_end): ?>
          Your account was suspended on <strong><?= date('j F Y', strtotime($grace_end)) ?></strong>
          following cancellation of your subscription.<br><br>
        <?php endif; ?>
        Your client data is safely stored and can be restored when you resubscribe.
        To reactivate your account, please get in touch with us.
      </div>

      <a href="mailto:<?= htmlspecialchars($email) ?>?subject=Reactivate+my+FirmReady+account&body=Hi%2C+I+would+like+to+reactivate+my+FirmReady+account+for+<?= urlencode($firm_name) ?>"
         class="btn btn-primary">Reactivate My Account</a>

      <hr class="divider">

      <div class="contact-box">
        <strong>Need help? Contact us:</strong>
        <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
        <?php if (FR_FIRM_PHONE): ?><br><?= htmlspecialchars(FR_FIRM_PHONE) ?><?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
