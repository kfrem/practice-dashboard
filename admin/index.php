<?php
// ============================================================
// FIRMREADY — ADMIN DASHBOARD
// Owner-only: see all subscribers, statuses, client counts
// Protected by FR_ADMIN_PASSWORD in root config.php
// Access: https://practice.finaccord.pro/admin/
// ============================================================

require_once __DIR__ . '/../config.php';
session_start();

// ── Auth ─────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], FR_ADMIN_PASSWORD)) {
        $_SESSION['fr_admin'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    $login_error = 'Incorrect password.';
}

if (isset($_POST['logout'])) {
    unset($_SESSION['fr_admin']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$authed = !empty($_SESSION['fr_admin']);

// ── Load data (only if authed) ────────────────────────────────

$subscribers = [];
$firm_stats  = [];

if ($authed) {
    $subs_path = FR_DATA_DIR . 'subscribers.json';
    if (file_exists($subs_path)) {
        $subscribers = json_decode(file_get_contents($subs_path), true) ?: [];
        // Reverse so newest first
        $subscribers = array_reverse($subscribers);
    }

    // Load per-firm stats
    foreach ($subscribers as &$sub) {
        $slug = $sub['firm_slug'] ?? '';
        if (!$slug) continue;
        $clients_file = __DIR__ . '/../firms/' . $slug . '/fr_data/clients.json';
        $sub_file     = __DIR__ . '/../firms/' . $slug . '/fr_data/subscription.json';
        $sub['client_count'] = 0;
        if (file_exists($clients_file)) {
            $clients = json_decode(file_get_contents($clients_file), true) ?: [];
            $sub['client_count'] = count($clients);
        }
        if (file_exists($sub_file)) {
            $sub_data = json_decode(file_get_contents($sub_file), true) ?: [];
            $sub['grace_period_end']   = $sub_data['grace_period_end']   ?? '';
            $sub['current_period_end'] = $sub_data['current_period_end'] ?? ($sub['current_period_end'] ?? '');
        }
    }
    unset($sub);

    // Summary stats
    $total       = count($subscribers);
    $active      = count(array_filter($subscribers, fn($s) => in_array($s['status'] ?? '', ['active','trialing'])));
    $past_due    = count(array_filter($subscribers, fn($s) => ($s['status'] ?? '') === 'past_due'));
    $cancelled   = count(array_filter($subscribers, fn($s) => ($s['status'] ?? '') === 'cancelled'));
    $mrr         = $active * 12; // £12/month per active/trialing sub
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — FirmReady</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f0f2f5;color:#1a2433;min-height:100vh}
a{text-decoration:none;color:inherit}

/* LOGIN */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-card{background:#fff;border:1px solid #ddd8cf;border-radius:6px;padding:40px;width:360px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
.login-card h1{font-size:20px;font-weight:800;color:#1a3558;margin-bottom:4px}
.login-card p{font-size:13px;color:#64748b;margin-bottom:24px}
.login-card input{width:100%;padding:11px 14px;border:1.5px solid #ddd8cf;border-radius:4px;font-size:14px;margin-bottom:12px;outline:none}
.login-card input:focus{border-color:#1a3558}
.btn-login{width:100%;padding:12px;background:#1a3558;color:#fff;border:none;border-radius:4px;font-size:14px;font-weight:700;cursor:pointer}
.btn-login:hover{background:#0f2238}
.err{color:#c0392b;font-size:13px;margin-top:8px}

/* APP */
nav{background:#1a3558;padding:14px 28px;display:flex;align-items:center;justify-content:space-between}
.nav-logo{font-size:18px;font-weight:800;color:#fff}.nav-logo span{color:#c9a84c}
.nav-tag{font-size:11px;color:#c9a84c;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);border-radius:3px;padding:2px 8px;margin-left:8px}
.btn-logout{background:transparent;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:4px;padding:7px 16px;font-size:12px;cursor:pointer;font-family:'Segoe UI',Georgia,sans-serif}
.btn-logout:hover{background:rgba(255,255,255,.1)}

.main{max-width:1200px;margin:0 auto;padding:32px 24px 80px}
.page-head{margin-bottom:28px}
.page-title{font-size:24px;font-weight:800;color:#1a3558}
.page-sub{font-size:13px;color:#64748b;margin-top:4px}

.stats{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px}
.stat{background:#fff;border-radius:6px;border:1px solid #ddd8cf;padding:20px;border-top:3px solid #ddd8cf}
.stat.s-active{border-top-color:#2d6a4f}
.stat.s-trial{border-top-color:#1e40af}
.stat.s-due{border-top-color:#b45309}
.stat.s-can{border-top-color:#c0392b}
.stat.s-mrr{border-top-color:#c9a84c}
.stat-n{font-size:32px;font-weight:800;color:#1a3558;font-family:Georgia,serif}
.stat-l{font-size:11px;color:#64748b;margin-top:4px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}

.card{background:#fff;border:1px solid #ddd8cf;border-radius:6px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid #ddd8cf;display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:700;color:#1a3558}
table{width:100%;border-collapse:collapse}
th{background:#f8f7f4;color:#64748b;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:10px 16px;text-align:left;border-bottom:1px solid #ddd8cf}
td{padding:12px 16px;font-size:13px;border-bottom:1px solid #f0ede6;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafaf7}

.badge{display:inline-block;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px}
.b-active{background:#d1fae5;color:#065f46}
.b-trialing{background:#dbeafe;color:#1e40af}
.b-past_due{background:#fef3c7;color:#b45309}
.b-cancelled{background:#fee2e2;color:#991b1b}
.b-unknown{background:#f0ede6;color:#64748b}

.limit-bar{display:inline-block;height:6px;border-radius:3px;background:#ddd8cf;width:80px;vertical-align:middle;position:relative;margin-left:6px}
.limit-fill{height:100%;border-radius:3px;background:#1a3558;position:absolute;top:0;left:0}
.limit-fill.warn{background:#b45309}
.limit-fill.full{background:#c0392b}

.action-btn{font-size:11px;padding:4px 10px;border-radius:3px;cursor:pointer;border:1px solid #ddd8cf;background:#fff;font-family:'Segoe UI',Georgia,sans-serif;color:#1a3558}
.action-btn:hover{background:#f0f4f8;border-color:#1a3558}
.empty{text-align:center;padding:48px 20px;color:#64748b;font-size:14px}
@media(max-width:900px){.stats{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<?php if (!$authed): ?>
<!-- LOGIN -->
<div class="login-wrap">
  <div class="login-card">
    <h1>FirmReady Admin</h1>
    <p>Owner access only — enter admin password to continue</p>
    <form method="POST">
      <input type="password" name="password" placeholder="Admin password" autofocus>
      <button type="submit" class="btn-login">Sign In →</button>
      <?php if (!empty($login_error)): ?>
        <div class="err"><?= htmlspecialchars($login_error) ?></div>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ADMIN APP -->
<nav>
  <div>
    <span class="nav-logo">Firm<span>Ready</span></span>
    <span class="nav-tag">ADMIN</span>
  </div>
  <form method="POST" style="display:inline">
    <input type="hidden" name="logout" value="1">
    <button type="submit" class="btn-logout">Sign Out</button>
  </form>
</nav>

<div class="main">
  <div class="page-head">
    <div class="page-title">Subscriber Dashboard</div>
    <div class="page-sub">All FirmReady subscribers — <?= date('j F Y') ?></div>
  </div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat s-active">
      <div class="stat-n"><?= $active ?></div>
      <div class="stat-l">Active / Trial</div>
    </div>
    <div class="stat s-due">
      <div class="stat-n"><?= $past_due ?></div>
      <div class="stat-l">Past Due</div>
    </div>
    <div class="stat s-can">
      <div class="stat-n"><?= $cancelled ?></div>
      <div class="stat-l">Cancelled</div>
    </div>
    <div class="stat">
      <div class="stat-n"><?= $total ?></div>
      <div class="stat-l">Total Subscribers</div>
    </div>
    <div class="stat s-mrr">
      <div class="stat-n">£<?= $mrr ?></div>
      <div class="stat-l">Est. MRR</div>
    </div>
  </div>

  <!-- SUBSCRIBER TABLE -->
  <div class="card">
    <div class="card-head">
      <div class="card-title">All Subscribers (<?= $total ?>)</div>
      <a href="export.php" style="font-size:12px;color:#1a3558;font-weight:600">⬇ Export CSV</a>
    </div>
    <?php if (empty($subscribers)): ?>
      <div class="empty">No subscribers yet. Share your signup page to get started!</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Firm</th>
          <th>Email</th>
          <th>Status</th>
          <th>Clients</th>
          <th>Trial Ends</th>
          <th>Period End</th>
          <th>Signed Up</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($subscribers as $sub): ?>
        <?php
          $status    = $sub['status'] ?? 'unknown';
          $slug      = $sub['firm_slug'] ?? '';
          $trial_end = $sub['trial_end'] ?? '';
          $per_end   = $sub['current_period_end'] ?? '';
          $grace     = $sub['grace_period_end'] ?? '';
          $clients   = $sub['client_count'] ?? 0;
          $limit     = $sub['client_limit'] ?? 50;
          $pct       = $limit > 0 ? min(100, round($clients / $limit * 100)) : 0;
          $fill_cls  = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
          $created   = $sub['created_at'] ?? '';
        ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($sub['firm_name'] ?? '—') ?></strong>
            <?php if ($slug): ?>
              <div style="font-size:11px;color:#64748b;margin-top:2px">/firms/<?= htmlspecialchars($slug) ?></div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($sub['email'] ?? '—') ?></td>
          <td>
            <span class="badge b-<?= htmlspecialchars($status) ?>">
              <?= ucfirst(str_replace('_', ' ', $status)) ?>
            </span>
            <?php if ($status === 'cancelled' && $grace && strtotime($grace) > time()): ?>
              <div style="font-size:11px;color:#b45309;margin-top:2px">Grace until <?= date('j M', strtotime($grace)) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?= $clients ?>/<?= $limit ?>
            <span class="limit-bar"><span class="limit-fill <?= $fill_cls ?>" style="width:<?= $pct ?>%"></span></span>
          </td>
          <td style="font-size:12px;color:#64748b"><?= $trial_end ? date('j M Y', strtotime($trial_end)) : '—' ?></td>
          <td style="font-size:12px;color:#64748b"><?= $per_end ? date('j M Y', strtotime($per_end)) : '—' ?></td>
          <td style="font-size:12px;color:#64748b"><?= $created ? date('j M Y', strtotime($created)) : '—' ?></td>
          <td>
            <?php if ($slug): ?>
              <a href="<?= FR_BASE_URL ?>/firms/<?= htmlspecialchars($slug) ?>/dashboard.php" target="_blank" class="action-btn">View →</a>
            <?php else: ?>
              <span style="font-size:11px;color:#64748b">Provisioning…</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div style="margin-top:20px;font-size:12px;color:#94a3b8;text-align:center">
    FirmReady Admin &bull; <?= htmlspecialchars(FR_FIRM_NAME) ?> &bull; <?= date('Y') ?>
  </div>
</div>
<?php endif; ?>

</body>
</html>
