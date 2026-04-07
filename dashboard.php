<?php
require_once __DIR__ . '/config.php';

// ── Subscription guard (only for provisioned firm instances) ──
$_sub_file = FR_DATA_DIR . 'subscription.json';
if (file_exists($_sub_file)) {
    $_sub = json_decode(file_get_contents($_sub_file), true) ?: [];
    if (($_sub['status'] ?? '') === 'cancelled') {
        $_grace = $_sub['grace_period_end'] ?? '';
        if ($_grace && strtotime($_grace) < time()) {
            header('Location: ' . FR_BASE_URL . '/suspended.php');
            exit;
        }
    }
}
unset($_sub_file, $_sub, $_grace);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Dashboard — The Practice</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a1a2e;min-height:100vh}
:root{
  --navy:#1a3558;--navy-dark:#0f2238;--navy-mid:#1e3d66;
  --gold:#c9a84c;--gold-light:#e8d5a0;--gold-dark:#a07830;
  --cream:#f8f7f4;--border:#ddd8cf;--text:#2c2c3e;--muted:#64748b;
  --success:#2d6a4f;--warning:#b45309;--danger:#c0392b;--info:#1e40af
}
a{text-decoration:none;color:inherit}

/* ── LOGIN ─────────────────────────── */
#loginScreen{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--navy-dark);background-image:radial-gradient(circle at 25% 50%,rgba(201,168,76,.07) 0%,transparent 55%)}
.login-box{background:rgba(255,255,255,.05);border:1px solid rgba(201,168,76,.2);border-radius:10px;padding:44px 36px;width:100%;max-width:400px;text-align:center}
.login-logo{font-size:32px;font-weight:800;color:var(--gold);margin-bottom:4px}
.login-logo span{color:#fff}
.login-sub{font-size:13px;color:#94a3b8;margin-bottom:32px}
.login-box input{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(201,168,76,.25);border-radius:5px;padding:13px 16px;color:#fff;font-family:'Segoe UI',Georgia,sans-serif;font-size:14px;outline:none;margin-bottom:14px;transition:border-color .2s}
.login-box input:focus{border-color:var(--gold)}
.login-box input::placeholder{color:#64748b}
.btn-login{width:100%;background:var(--gold);color:var(--navy-dark);border:none;border-radius:5px;padding:13px;font-family:'Segoe UI',Georgia,sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.3px}
.btn-login:hover{background:var(--gold-dark)}
#loginErr{color:#f87171;font-size:13px;margin-top:10px;display:none}

/* ── APP ───────────────────────────── */
#app{display:none}

/* NAV */
nav{border-bottom:1px solid var(--border);padding:14px 28px;background:#fff;position:sticky;top:0;z-index:100;display:flex;align-items:center;justify-content:space-between}
.logo{font-size:20px;font-weight:800;color:var(--navy)}.logo span{color:var(--gold)}
.nav-links{display:flex;gap:22px;font-size:13px}
.nav-links a{color:#374151;transition:color .2s}.nav-links a:hover{color:var(--navy)}
.nav-links a.active{color:var(--navy);font-weight:700;border-bottom:2px solid var(--gold);padding-bottom:2px}
.nav-links a.partner{color:var(--gold);font-weight:600}
.nav-right{display:flex;align-items:center;gap:14px}
.nav-cta{font-size:13px;font-weight:700;background:var(--navy);color:#fff;padding:9px 20px;border-radius:4px;cursor:pointer;border:none;font-family:'Segoe UI',Georgia,sans-serif}
.nav-cta:hover{background:var(--navy-dark)}
.logout-btn{font-size:12px;color:var(--muted);cursor:pointer;background:none;border:1px solid var(--border);border-radius:4px;padding:6px 12px;font-family:'Segoe UI',Georgia,sans-serif}
.logout-btn:hover{border-color:var(--danger);color:var(--danger)}

/* LAYOUT */
.main{max-width:1100px;margin:0 auto;padding:32px 28px 80px}

/* PAGE HEADER */
.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.page-title{font-size:26px;font-weight:800;color:var(--navy-dark);letter-spacing:-.3px}
.page-sub{font-size:13px;color:var(--muted);margin-top:2px}

/* TABS */
.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:24px}
.tab{padding:10px 22px;font-size:14px;font-weight:600;cursor:pointer;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s}
.tab:hover{color:var(--navy)}
.tab.active{color:var(--navy);border-bottom-color:var(--gold)}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat{background:#fff;border-radius:6px;border:1px solid var(--border);border-top:3px solid var(--border);padding:20px 22px}
.stat.s-total{border-top-color:var(--navy)}
.stat.s-sent{border-top-color:var(--info)}
.stat.s-signed{border-top-color:var(--success)}
.stat.s-aml{border-top-color:var(--gold)}
.stat-n{font-size:36px;font-weight:800;color:var(--navy-dark);font-family:Georgia,serif}
.stat-l{font-size:12px;color:var(--muted);margin-top:2px}

/* TABLE */
.table-wrap{background:#fff;border-radius:6px;border:1px solid var(--border);overflow:hidden}
.table-top{padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)}
.table-ttl{font-size:14px;font-weight:700;color:var(--navy-dark)}
table{width:100%;border-collapse:collapse}
th{background:var(--cream);color:var(--muted);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:10px 16px;text-align:left;border-bottom:1px solid var(--border)}
td{padding:13px 16px;font-size:13px;border-bottom:1px solid #f0ece6;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafaf7}
.c-name{font-weight:700;color:var(--navy-dark)}
.c-info{font-size:11px;color:var(--muted);margin-top:1px}

/* BADGES */
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.b-pending{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
.b-sent{background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe}
.b-signed{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
.b-overdue{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
.b-due-soon{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
.overdue-bar{background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:12px 18px;margin-bottom:20px;display:none;align-items:center;justify-content:space-between;gap:12px}
.overdue-bar.show{display:flex}
.overdue-bar-text{font-size:14px;color:#991b1b;font-weight:600}
.overdue-bar-sub{font-size:12px;color:#b91c1c;margin-top:2px}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:12px;font-weight:700;cursor:pointer;border:none;transition:all .2s;letter-spacing:.2px}
.btn-navy{background:var(--navy);color:#fff}.btn-navy:hover{background:var(--navy-dark)}
.btn-gold{background:var(--gold);color:#fff}.btn-gold:hover{background:var(--gold-dark)}
.btn-outline{background:#fff;color:var(--navy);border:1px solid var(--border)}.btn-outline:hover{border-color:var(--navy)}
.btn-red{background:#fff;color:var(--danger);border:1px solid #fca5a5}.btn-red:hover{background:var(--danger);color:#fff}
.btn-lg{padding:13px 26px;font-size:14px;border-radius:5px}

/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(15,34,56,.55);display:none;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(3px)}
.overlay.open{display:flex}
.modal{background:#fff;border-radius:8px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:mIn .25s ease}
@keyframes mIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.m-head{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.m-title{font-size:18px;font-weight:800;color:var(--navy-dark)}
.m-close{background:none;border:none;font-size:22px;color:var(--muted);cursor:pointer;line-height:1}
.m-body{padding:20px 24px}
.m-foot{padding:14px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}

/* FORM */
.fg{margin-bottom:16px}
.fg label{display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.7px;margin-bottom:5px}
.fg input,.fg select,.fg textarea{width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;color:var(--text);background:var(--cream);outline:none;transition:border-color .2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--navy);background:#fff}
.fg textarea{min-height:80px;resize:vertical}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}

/* ALERT */
.alert{padding:10px 14px;border-radius:4px;font-size:13px;margin-bottom:14px}
.a-ok{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
.a-err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
.a-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:12px}

/* LINK BOX */
.link-box{background:var(--navy-dark);color:#6ee7b7;font-family:monospace;font-size:12px;padding:12px 14px;border-radius:5px;word-break:break-all;cursor:pointer;position:relative;margin-top:12px}
.link-copied{position:absolute;top:8px;right:8px;background:var(--gold);color:var(--navy-dark);border-radius:3px;padding:2px 8px;font-size:10px;font-weight:700;display:none}

/* EMPTY */
.empty{text-align:center;padding:48px 24px;color:var(--muted)}
.empty-icon{font-size:40px;margin-bottom:12px}
.empty-lbl{font-size:14px}

/* TOAST */
.toast{position:fixed;bottom:24px;right:24px;background:var(--navy-dark);color:#fff;padding:13px 18px;border-radius:5px;font-size:13px;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2);transform:translateY(70px);opacity:0;transition:all .3s;border-left:4px solid var(--gold);max-width:320px}
.toast.show{transform:translateY(0);opacity:1}

/* FOOTER */
footer{border-top:1px solid var(--border);padding:18px 28px;text-align:center;font-size:12px;color:var(--muted);background:#fff;position:fixed;bottom:0;left:0;right:0}

/* MTD BADGES */
.b-mtd-live{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
.b-mtd-soon{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
.b-mtd-ok{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
.b-mtd-na{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}

/* DEADLINE TRACKER */
.dl-red{background:#fee2e2;color:#991b1b}
.dl-amber{background:#fff7ed;color:#c2410c}
.dl-green{background:#d1fae5;color:#065f46}
.dl-done{background:#f1f5f9;color:#94a3b8;text-decoration:line-through}
.dl-type{display:inline-block;font-size:10px;padding:2px 7px;border-radius:10px;font-weight:600;background:#e8edf5;color:var(--navy);white-space:nowrap}
.dl-filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.dl-filter{padding:5px 13px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid var(--border);background:#fff;cursor:pointer;color:var(--muted);font-family:'Segoe UI',Georgia,sans-serif}
.dl-filter.active{background:var(--navy);color:#fff;border-color:var(--navy)}
.dl-count{display:inline-block;background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:1px 6px;border-radius:8px;margin-left:3px}

/* ARCHIVE */
.hash-short{font-family:monospace;font-size:10px;color:var(--muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* MONTHLY STATS */
.mstats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.ms{background:#fff;border-radius:6px;border:1px solid var(--border);padding:18px 20px;text-align:center}
.ms-n{font-size:26px;font-weight:800;color:var(--navy-dark);font-family:Georgia,serif}
.ms-l{font-size:11px;color:var(--muted);margin-top:4px;line-height:1.4}

/* NOTES */
.notes-pill{display:inline-block;background:#e8edf5;color:var(--navy);font-size:10px;padding:2px 7px;border-radius:10px;cursor:pointer;margin-left:4px;font-weight:600;border:none;font-family:'Segoe UI',Georgia,sans-serif}
.notes-pill:hover{background:var(--navy);color:#fff}
.notes-pill.has-notes{background:#fef3c7;color:#92400e}

/* LETTER EDITOR */
.letter-editor{width:100%;min-height:300px;padding:14px;border:1.5px solid var(--border);border-radius:5px;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;line-height:1.8;color:var(--text);background:var(--cream);resize:vertical;outline:none}
.letter-editor:focus{border-color:var(--navy);background:#fff}
.modal-wide{max-width:760px!important}

@media(max-width:900px){.stats{grid-template-columns:1fr 1fr}.nav-links{display:none}.main{padding:20px 16px 70px}.mstats{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.stats{grid-template-columns:1fr 1fr}.fg-row{grid-template-columns:1fr}.mstats{grid-template-columns:1fr 1fr}}

/* SERVICE PICKER */
.svc-picker{border:1px solid var(--border);border-radius:4px;background:var(--cream);overflow:hidden}
.svc-presets{display:grid;grid-template-columns:1fr 1fr;gap:0;padding:10px 12px 6px}
.svc-cb{display:flex;align-items:center;gap:7px;font-size:12px;color:#374151;cursor:pointer;padding:4px 0;font-weight:500;text-transform:none;letter-spacing:0}
.svc-cb input[type=checkbox]{accent-color:var(--navy);width:14px;height:14px;flex-shrink:0;cursor:pointer}
.svc-custom-row{display:flex;gap:8px;padding:8px 12px;border-top:1px solid var(--border);background:#fff}
.svc-custom-row input{flex:1;padding:7px 10px;border:1px solid var(--border);border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:12px;outline:none;background:#fff;color:var(--text)}
.svc-custom-row input:focus{border-color:var(--navy)}
.btn-add-svc{background:var(--navy);color:#fff;border:none;border-radius:4px;padding:7px 14px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:'Segoe UI',Georgia,sans-serif}
.btn-add-svc:hover{background:var(--navy-dark)}
.svc-tags{display:flex;flex-wrap:wrap;gap:6px;padding:8px 12px;border-top:1px solid var(--border);min-height:34px;background:#fff}
.svc-tag{display:inline-flex;align-items:center;gap:5px;background:var(--navy);color:#fff;border-radius:3px;padding:3px 8px;font-size:11px;font-weight:600}
.svc-tag-x{background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;font-size:14px;line-height:1;padding:0;font-family:inherit}
.svc-tag-x:hover{color:#fff}

/* FIRM SIGNATURE SECTION */
.firm-sig-section{border:1px solid var(--border);border-radius:4px;overflow:hidden}
.fsig-tabs{display:flex;border-bottom:1px solid var(--border)}
.fsig-tab{flex:1;padding:8px;background:var(--cream);border:none;font-family:'Segoe UI',Georgia,sans-serif;font-size:12px;font-weight:600;color:var(--muted);cursor:pointer;transition:all .2s;border-right:1px solid var(--border)}
.fsig-tab:last-child{border-right:none}
.fsig-tab.active{background:#fff;color:var(--navy)}
.fsig-panel{padding:12px}
.fsig-preview-box{min-height:72px;display:flex;align-items:center;justify-content:center;background:#fafaf8;border:1px solid var(--border);border-radius:4px;padding:10px}
.btn-save-sig{background:var(--navy);color:#fff;border:none;border-radius:4px;padding:7px 16px;font-size:12px;font-weight:700;cursor:pointer;font-family:'Segoe UI',Georgia,sans-serif;margin-top:8px}
.btn-save-sig:hover{background:var(--navy-dark)}
.sig-saved-ok{font-size:12px;color:var(--success);font-weight:600;margin-left:8px;display:none}
</style>
</head>
<body>

<!-- LOGIN -->
<div id="loginScreen">
  <div class="login-box">
    <div class="login-logo">The <span>Practice</span></div>
    <div class="login-sub">Client Dashboard — Secure Access</div>
    <input type="password" id="pwdInput" placeholder="Enter your password" onkeydown="if(event.key==='Enter')doLogin()">
    <button class="btn-login" onclick="doLogin()">Sign In →</button>
    <div id="loginErr">Incorrect password. Please try again.</div>
  </div>
</div>

<!-- APP -->
<div id="app">

  <nav>
    <div class="logo"><?= htmlspecialchars(FR_FIRM_NAME) ?></div>
    <div class="nav-links">
      <a href="index.html">Home</a>
      <a href="pricing.html">Pricing</a>
      <a href="roi.html">ROI Calculator</a>
      <a href="scorecard.html">Practice Scorecard</a>
      <a href="portal.html">Client Portal Demo</a>
      <a href="resources.html">Free Resources</a>
      <a href="pricing.html#partnership" class="partner">★ Partner Programme</a>
      <a href="dashboard.php" class="active">Client Dashboard</a>
      <a href="letters.php">✉ Correspondence</a>
      <a href="setup.php">⚙ Settings</a>
    </div>
    <div class="nav-right">
      <span id="clientCountBadge" style="font-size:12px;color:#64748b;display:none"></span>
      <button class="logout-btn" onclick="doLogout()">Log out</button>
      <button class="btn btn-outline" style="font-size:13px;padding:8px 16px" onclick="openM('onboardModal')">📋 Send Onboarding Link</button>
      <button class="nav-cta" onclick="openAddClient()">+ Add Client</button>
    </div>
  </nav>

  <div class="main">

    <div class="page-head">
      <div>
        <div class="page-title">Client Dashboard</div>
        <div class="page-sub">Manage engagement letters, signatures and AML records</div>
      </div>
      <button class="btn btn-gold btn-lg" onclick="openAddClient()">+ Add New Client</button>
    </div>

    <!-- OVERDUE ALERT BAR -->
    <div class="overdue-bar" id="overdueBar">
      <div>
        <div class="overdue-bar-text">⚠️ <span id="overdueCount">0</span> client(s) have not signed past their deadline</div>
        <div class="overdue-bar-sub">Send reminders individually or use Remind All to chase everyone at once</div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-red" onclick="remindAll()">🔔 Remind All Overdue</button>
        <button class="btn btn-outline" style="color:#991b1b;border-color:#fca5a5" onclick="switchTab('overdue', document.getElementById('tabOverdue'))">View Overdue →</button>
      </div>
    </div>

    <!-- MONTHLY STATS -->
    <div class="mstats" id="monthlyStats"></div>

    <!-- STATS -->
    <div class="stats">
      <div class="stat s-total"><div class="stat-n" id="stTotal">0</div><div class="stat-l">Total Clients</div></div>
      <div class="stat s-sent"><div class="stat-n" id="stSent">0</div><div class="stat-l">Awaiting Signature</div></div>
      <div class="stat s-signed"><div class="stat-n" id="stSigned">0</div><div class="stat-l">Signed</div></div>
      <div class="stat s-aml"><div class="stat-n" id="stAml">0</div><div class="stat-l">AML Complete</div></div>
    </div>

    <!-- TABS -->
    <div class="tabs">
      <div class="tab active" onclick="switchTab('all',this)">All Clients</div>
      <div class="tab" onclick="switchTab('pending',this)">Awaiting Signature</div>
      <div class="tab" id="tabOverdue" onclick="switchTab('overdue',this)">⚠️ Overdue</div>
      <div class="tab" onclick="switchTab('signed',this)">Signed</div>
      <div class="tab" onclick="switchTab('aml',this)">AML Records</div>
      <div class="tab" onclick="switchTab('mtd',this)">📊 MTD Tracker</div>
      <div class="tab" id="tabDeadlines" onclick="switchTab('deadlines',this)">📅 Deadlines</div>
      <div class="tab" onclick="switchTab('archive',this)">📁 Archive</div>
    </div>

    <!-- CLIENTS TABLE -->
    <div id="viewClients">
      <div class="table-wrap">
        <div class="table-top">
          <div class="table-ttl" id="tabTitle">All Clients</div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="text" id="searchBox" placeholder="Search…" style="padding:7px 12px;border:1px solid var(--border);border-radius:4px;font-size:13px;outline:none;width:180px" oninput="renderTable()">
            <a href="api.php?action=export_csv" class="btn btn-outline" title="Export to Excel/CSV">⬇ CSV</a>
          </div>
        </div>
        <table>
          <thead>
            <tr><th>Client</th><th>Type</th><th>Service</th><th>Signature</th><th>Deadline</th><th>AML</th><th>Actions</th></tr>
          </thead>
          <tbody id="clientTbody"></tbody>
        </table>
      </div>
    </div>

    <!-- AML TABLE -->
    <div id="viewAml" style="display:none">
      <div class="table-wrap">
        <div class="table-top"><div class="table-ttl">AML Customer Due Diligence Records</div></div>
        <table>
          <thead>
            <tr><th>Client</th><th>ID Type</th><th>ID Reference</th><th>Risk</th><th>Verified</th><th>Status</th><th>Action</th></tr>
          </thead>
          <tbody id="amlTbody"></tbody>
        </table>
      </div>
    </div>

    <!-- MTD TRACKER -->
    <div id="viewMtd" style="display:none">
      <div class="table-wrap">
        <div class="table-top">
          <div class="table-ttl">📊 Making Tax Digital — Client Tracker</div>
          <div style="font-size:12px;color:var(--muted)">MTD for Income Tax mandatory from April 2026 for income over £50,000</div>
        </div>
        <div style="padding:16px 20px;background:#fffbeb;border-bottom:1px solid var(--border)">
          <div style="font-size:13px;color:#92400e;font-weight:600;margin-bottom:4px">⚠️ MTD for Income Tax is now live — 6 April 2026</div>
          <div style="font-size:12px;color:#b45309">Clients earning over £50,000 from self-employment or property must now keep digital records and submit quarterly updates. Threshold drops to £30,000 in April 2027.</div>
        </div>
        <table>
          <thead>
            <tr><th>Client</th><th>Income Threshold</th><th>MTD Status</th><th>Software</th><th>Enrolled</th><th>Next Submission</th><th>Notes</th><th>Action</th></tr>
          </thead>
          <tbody id="mtdTbody"></tbody>
        </table>
      </div>
    </div>

    <!-- DEADLINE TRACKER -->
    <div id="viewDeadlines" style="display:none">
      <div class="table-wrap">
        <div class="table-top">
          <div class="table-ttl">📅 Statutory Deadline Tracker</div>
          <div class="dl-filters">
            <button class="dl-filter active" onclick="dlFilter('all',this)">All</button>
            <button class="dl-filter" onclick="dlFilter('overdue',this)">🔴 Overdue <span id="dlCountOverdue" class="dl-count" style="display:none"></span></button>
            <button class="dl-filter" onclick="dlFilter('soon',this)">🟡 Due Soon</button>
            <button class="dl-filter" onclick="dlFilter('upcoming',this)">🟢 Upcoming</button>
            <button class="dl-filter" onclick="dlFilter('done',this)">✓ Completed</button>
          </div>
        </div>
        <div style="padding:12px 20px;background:#eff6ff;border-bottom:1px solid #bfdbfe;font-size:12px;color:#1e40af">
          ℹ️ Set up deadline tracking per client using the <strong>📅</strong> button on any client row. Deadlines auto-calculate from year end dates.
        </div>
        <table>
          <thead>
            <tr><th>Client</th><th>Deadline Type</th><th>Due Date</th><th>Days Left</th><th>Status</th><th>Action</th></tr>
          </thead>
          <tbody id="dlTbody"></tbody>
        </table>
      </div>
    </div>

    <!-- DOCUMENT ARCHIVE -->
    <div id="viewArchive" style="display:none">
      <div class="table-wrap">
        <div class="table-top">
          <div class="table-ttl">📁 Signed Document Archive</div>
          <div style="font-size:12px;color:var(--muted)">All signed engagement letters with audit trails</div>
        </div>
        <table>
          <thead>
            <tr><th>Client</th><th>Service</th><th>Signed</th><th>Method</th><th>IP Address</th><th>SHA-256 Hash</th><th>Download</th></tr>
          </thead>
          <tbody id="archiveTbody"></tbody>
        </table>
      </div>
    </div>

  </div>

  <footer>
    <strong>The Practice</strong> · practice.finaccord.pro · info@kafs-ltd.com · GDPR-compliant · UK-hosted · Strictly confidential &nbsp;|&nbsp; © 2026 A Finaccord Professional Services Programme
  </footer>
</div>

<!-- ADD CLIENT MODAL -->
<div class="overlay" id="addModal">
  <div class="modal">
    <div class="m-head"><div class="m-title">Add New Client</div><button class="m-close" onclick="closeM('addModal')">×</button></div>
    <div class="m-body">
      <div id="addAlert"></div>
      <div class="fg-row">
        <div class="fg"><label>Full Name *</label><input type="text" id="addName" placeholder="Jane Smith"></div>
        <div class="fg"><label>Email Address *</label><input type="email" id="addEmail" placeholder="jane@example.co.uk"></div>
      </div>
      <div class="fg">
        <label>Company / Trading Name
          <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px;color:var(--muted)"> — type name then click Lookup to auto-fill from Companies House</span>
        </label>
        <div style="display:flex;gap:8px">
          <input type="text" id="addCompany" placeholder="Smith Ltd" style="flex:1">
          <button type="button" class="btn btn-outline" onclick="chLookup()" style="white-space:nowrap;padding:8px 14px;font-size:13px">🔍 CH Lookup</button>
        </div>
        <div id="chResults" style="display:none;border:1px solid var(--border);border-radius:4px;margin-top:4px;max-height:200px;overflow-y:auto;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.1)"></div>
      </div>
      <div class="fg-row">
        <div class="fg"><label>Registered Address</label><input type="text" id="addAddress" placeholder="Auto-filled from Companies House"></div>
        <div class="fg"><label>Company Number</label><input type="text" id="addCompanyNumber" placeholder="e.g. 12345678"></div>
      </div>
      <div class="fg-row">
        <div class="fg" style="display:none"><label>_</label></div>
        <div class="fg"><label>Entity Type</label>
          <select id="addType">
            <option>Ltd Company</option><option>Sole Trader</option><option>Partnership</option>
            <option>LLP</option><option>Individual</option><option>Charity</option><option>Trust</option>
          </select>
        </div>
      </div>
      <div class="fg">
        <label>Services <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(tick all that apply — add custom below)</span></label>
        <div class="svc-picker">
          <div class="svc-presets">
            <label class="svc-cb"><input type="checkbox" name="svc" value="Self Assessment Tax Return"> Self Assessment Tax Return</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="Company Accounts &amp; Corporation Tax"> Company Accounts &amp; Corp Tax</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="VAT Returns"> VAT Returns</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="Bookkeeping"> Bookkeeping</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="Payroll"> Payroll</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="Making Tax Digital (MTD) Support"> MTD Support</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="Management Accounts"> Management Accounts</label>
            <label class="svc-cb"><input type="checkbox" name="svc" value="Full Accountancy Package"> Full Accountancy Package</label>
          </div>
          <div class="svc-custom-row">
            <input type="text" id="addCustomSvc" placeholder="Add a custom service not listed above…" onkeydown="if(event.key==='Enter'){event.preventDefault();addCustomSvc()}">
            <button type="button" class="btn-add-svc" onclick="addCustomSvc()">+ Add</button>
          </div>
          <div id="addSvcTags" class="svc-tags"></div>
        </div>
      </div>
      <div class="fg-row">
        <div class="fg"><label>Phone</label><input type="tel" id="addPhone" placeholder="07700 900000"></div>
        <div class="fg"></div>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('addModal')">Cancel</button>
      <button class="btn btn-navy" onclick="addClient()">Add Client</button>
    </div>
  </div>
</div>

<!-- SEND LINK MODAL — with letter editor -->
<div class="overlay" id="linkModal">
  <div class="modal modal-wide">
    <div class="m-head">
      <div class="m-title" id="linkModalTitle">Review &amp; Send Engagement Letter</div>
      <button class="m-close" onclick="closeM('linkModal')">×</button>
    </div>
    <div class="m-body">
      <div id="linkAlert"></div>

      <!-- STEP 1: EDIT LETTER -->
      <div id="linkStep1">
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Sending to: <strong id="linkEmail"></strong> — Edit the letter below before sending. The client will see exactly this.</p>
        <div class="fg-row" style="margin-bottom:16px">
          <div class="fg">
            <label>Agreed Fee (appears in letter)</label>
            <input type="text" id="linkFee" placeholder="e.g. £350 per annum + VAT">
          </div>
          <div class="fg">
            <label>Signing Deadline</label>
            <select id="linkDeadline">
              <option value="24">24 hours</option>
              <option value="48" selected>48 hours (recommended)</option>
              <option value="72">72 hours</option>
              <option value="120">5 days</option>
              <option value="168">7 days</option>
            </select>
          </div>
        </div>
        <div class="fg">
          <label>Engagement Letter — Edit as needed</label>
          <textarea class="letter-editor" id="letterEditor"></textarea>
          <p style="font-size:11px;color:var(--muted);margin-top:5px">You can edit any part of this letter. The client will sign exactly what you see here.</p>
        </div>
        <div class="fg" style="margin-top:4px">
          <label>Your Firm Signature <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(appears on the letter the client sees)</span></label>
          <div class="firm-sig-section">
            <div class="fsig-tabs">
              <button type="button" class="fsig-tab active" id="fsigTabSaved" onclick="switchFSigTab('saved')">Saved Signature</button>
              <button type="button" class="fsig-tab" id="fsigTabDraw" onclick="switchFSigTab('draw')">Draw New</button>
              <button type="button" class="fsig-tab" id="fsigTabUpload" onclick="switchFSigTab('upload')">Upload Image</button>
            </div>
            <div class="fsig-panel" id="fsigPanelSaved">
              <div class="fsig-preview-box">
                <span id="fsigNoSig" style="font-size:12px;color:var(--muted)">No signature saved yet — use Draw or Upload to add one.</span>
                <img id="fsigPreview" src="" alt="Firm signature" style="max-height:70px;max-width:100%;display:none">
              </div>
            </div>
            <div class="fsig-panel" id="fsigPanelDraw" style="display:none">
              <div style="position:relative;border:1px solid var(--border);border-radius:4px;background:#fafaf8">
                <canvas id="fsigCanvas" style="display:block;width:100%;height:110px;cursor:crosshair;touch-action:none"></canvas>
                <button type="button" onclick="clearFSig()" style="position:absolute;top:6px;right:8px;background:#fff;border:1px solid var(--border);border-radius:4px;padding:2px 8px;font-size:11px;color:var(--muted);cursor:pointer">Clear</button>
              </div>
              <button type="button" class="btn-save-sig" onclick="saveFirmSig('draw')">💾 Save &amp; Use This Signature</button>
              <span class="sig-saved-ok" id="drawSavedOk">✓ Saved</span>
            </div>
            <div class="fsig-panel" id="fsigPanelUpload" style="display:none">
              <p style="font-size:12px;color:var(--muted);margin-bottom:8px">Upload a PNG or JPG of your signature (white or transparent background works best).</p>
              <input type="file" id="fsigFile" accept="image/png,image/jpeg" onchange="loadFSigFile(this)" style="font-size:12px;margin-bottom:8px;display:block">
              <img id="fsigUploadPreview" src="" alt="" style="max-height:70px;display:none;border:1px solid var(--border);border-radius:4px;padding:4px;margin-bottom:8px">
              <button type="button" class="btn-save-sig" onclick="saveFirmSig('upload')">💾 Save &amp; Use This Signature</button>
              <span class="sig-saved-ok" id="uploadSavedOk">✓ Saved</span>
            </div>
          </div>
        </div>
      </div>

      <!-- STEP 2: SENT CONFIRMATION -->
      <div id="linkStep2" style="display:none">
        <div class="alert a-ok" style="font-size:14px;padding:14px">✓ Email sent successfully. The client will receive their signing link shortly.</div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Signing deadline set to: <strong id="linkDeadlineConfirm"></strong></p>
        <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Or share this link manually:</p>
        <div class="link-box" id="linkBox" onclick="copyLink()">
          <span id="linkText"></span>
          <span class="link-copied" id="linkCopied">Copied!</span>
        </div>
      </div>
    </div>
    <div class="m-foot" id="linkFoot">
      <button class="btn btn-outline" onclick="closeM('linkModal')">Cancel</button>
      <button class="btn btn-gold" id="sendBtn" onclick="sendLink()">📧 Save Letter &amp; Send →</button>
    </div>
  </div>
</div>

<!-- MTD MODAL -->
<div class="overlay" id="mtdModal">
  <div class="modal">
    <div class="m-head"><div class="m-title">MTD Record — <span id="mtdClientName"></span></div><button class="m-close" onclick="closeM('mtdModal')">×</button></div>
    <div class="m-body">
      <div id="mtdAlert"></div>
      <div class="fg-row">
        <div class="fg"><label>Annual Income Threshold</label>
          <select id="mtdThreshold">
            <option value="under20k">Under £20,000 — not yet in scope</option>
            <option value="20k-30k">£20,000–£30,000 — in scope April 2028</option>
            <option value="30k-50k">£30,000–£50,000 — in scope April 2027</option>
            <option value="over50k">Over £50,000 — in scope NOW (April 2026)</option>
          </select>
        </div>
        <div class="fg"><label>MTD Status</label>
          <select id="mtdStatus">
            <option value="not_started">Not started</option>
            <option value="in_progress">In progress</option>
            <option value="enrolled">Enrolled with HMRC</option>
            <option value="compliant">Fully compliant</option>
            <option value="exempt">Exempt</option>
          </select>
        </div>
      </div>
      <div class="fg-row">
        <div class="fg"><label>MTD Compatible Software</label>
          <select id="mtdSoftware">
            <option value="">Not selected</option>
            <option>Xero</option>
            <option>QuickBooks</option>
            <option>Sage</option>
            <option>FreeAgent</option>
            <option>Dext Solo</option>
            <option>Spreadsheet + Bridging Software</option>
            <option>Other</option>
          </select>
        </div>
        <div class="fg"><label>HMRC Enrolment Date</label>
          <input type="date" id="mtdEnrolDate">
        </div>
      </div>
      <div class="fg"><label>Next Quarterly Submission Due</label>
        <input type="date" id="mtdNextSubmission">
      </div>

      <!-- REMINDER PREFERENCES -->
      <div class="fg" style="border:1px solid var(--border);border-radius:6px;padding:14px 16px;background:#fafaf8;">
        <label style="display:block;margin-bottom:10px;font-weight:700;color:var(--navy-dark)">🔔 Automatic Submission Reminders</label>
        <div style="font-size:12px;color:var(--muted);margin-bottom:10px">Select when reminders are sent before the submission date. Reminders go to both the client and the accountant.</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 20px;margin-bottom:12px">
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="mtdRem28" value="28"> 28 days before
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="mtdRem14" value="14"> 14 days before
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="mtdRem7" value="7"> 7 days before
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="mtdRem3" value="3"> 3 days before
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="mtdRem1" value="1"> 1 day before
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="mtdRem0" value="0"> On the day
          </label>
        </div>
        <div style="font-size:12px;color:var(--muted)">💡 Requires cron job to be active on the server — see setup guide.</div>
      </div>

      <div class="fg"><label>MTD Notes</label>
        <textarea id="mtdNotes" placeholder="e.g. Client signed up to Xero on 1 April. First quarterly submission due July. Bank feed connected."></textarea>
      </div>
      <div class="alert a-warn" style="font-size:12px">
        MTD for Income Tax requires clients earning over £50,000 from self-employment or property to keep digital records and submit quarterly updates to HMRC using compatible software from 6 April 2026.
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('mtdModal')">Cancel</button>
      <button class="btn btn-navy" onclick="saveMtd()">Save MTD Record</button>
    </div>
  </div>
</div>

<!-- ONBOARDING MODAL -->
<div class="overlay" id="onboardModal">
  <div class="modal">
    <div class="m-head"><div class="m-title">📋 Send Client Onboarding Link</div><button class="m-close" onclick="closeM('onboardModal')">×</button></div>
    <div class="m-body">
      <div id="onboardAlert"></div>
      <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Send a link to a new or prospective client so they fill in their own details. Their information arrives directly in your dashboard ready to activate.</p>
      <div class="fg-row">
        <div class="fg"><label>Client Full Name *</label><input type="text" id="obName" placeholder="Jane Smith"></div>
        <div class="fg"><label>Client Email Address *</label><input type="email" id="obEmail" placeholder="jane@smithltd.co.uk"></div>
      </div>
      <div id="onboardLink" style="display:none;margin-top:12px">
        <div class="alert" style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:4px;padding:14px;font-size:13px">
          ✅ Onboarding link sent to <span id="obSentEmail"></span><br>
          <span style="font-size:11px;color:var(--muted)">You can also copy the link below:</span><br>
          <input type="text" id="obLinkCopy" readonly style="margin-top:6px;width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:4px;font-size:12px;background:#f8f7f4">
        </div>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('onboardModal')">Close</button>
      <button class="btn btn-navy" id="obSendBtn" onclick="sendOnboard()">Send Onboarding Link</button>
    </div>
  </div>
</div>

<!-- DEADLINE SETUP MODAL -->
<div class="overlay" id="dlModal">
  <div class="modal">
    <div class="m-head"><div class="m-title">📅 Deadline Setup — <span id="dlClientName"></span></div><button class="m-close" onclick="closeM('dlModal')">×</button></div>
    <div class="m-body">
      <div id="dlAlert"></div>
      <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Configure which deadlines apply to this client. Dates auto-calculate. You can mark individual deadlines as filed or paid from the Deadlines tab.</p>

      <!-- Year End -->
      <div class="fg-row">
        <div class="fg">
          <label>Accounting Year End Date</label>
          <input type="date" id="dlYearEnd">
          <div style="font-size:11px;color:var(--muted);margin-top:4px">Used to calculate CT return, CT payment, and Companies House accounts due dates.</div>
        </div>
        <div class="fg">
          <label>Confirmation Statement Due</label>
          <input type="date" id="dlConfirmation">
          <div style="font-size:11px;color:var(--muted);margin-top:4px">Find on Companies House. Recurs annually.</div>
        </div>
      </div>

      <!-- VAT -->
      <div class="fg">
        <label>VAT Registration</label>
        <select id="dlVat">
          <option value="none">Not VAT registered</option>
          <option value="jan">VAT Quarter: Jan / Apr / Jul / Oct</option>
          <option value="feb">VAT Quarter: Feb / May / Aug / Nov</option>
          <option value="mar">VAT Quarter: Mar / Jun / Sep / Dec</option>
        </select>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">VAT returns due 1 month + 7 days after quarter end (online filing).</div>
      </div>

      <!-- Payroll + SA -->
      <div class="fg-row">
        <div class="fg">
          <label>Payroll / RTI</label>
          <select id="dlPayroll">
            <option value="none">No payroll</option>
            <option value="monthly">Monthly payroll</option>
            <option value="weekly">Weekly payroll</option>
          </select>
          <div style="font-size:11px;color:var(--muted);margin-top:4px">RTI submissions due by 19th of each month.</div>
        </div>
        <div class="fg" style="display:flex;align-items:flex-start;flex-direction:column;justify-content:center;padding-top:8px">
          <label style="margin-bottom:12px">Self Assessment</label>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;text-transform:none;letter-spacing:0;font-weight:400">
            <input type="checkbox" id="dlSa" style="width:16px;height:16px">
            Client submits a Self Assessment tax return
          </label>
          <div style="font-size:11px;color:var(--muted);margin-top:6px">SA deadline: 31 Jan (online). Payment on account: 31 Jan + 31 Jul.</div>
        </div>
      </div>

      <div class="alert a-warn" style="font-size:12px;margin-top:4px">
        <strong>Note:</strong> Deadlines are calculated automatically. Use the Deadlines tab to mark each deadline as Filed, Paid, or Pending.
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('dlModal')">Cancel</button>
      <button class="btn btn-navy" onclick="saveDeadlines()">Save & Calculate Deadlines</button>
    </div>
  </div>
</div>

<!-- NOTES MODAL -->
<div class="overlay" id="notesModal">
  <div class="modal">
    <div class="m-head"><div class="m-title">Internal Notes</div><button class="m-close" onclick="closeM('notesModal')">×</button></div>
    <div class="m-body">
      <div id="notesAlert"></div>
      <p style="font-size:13px;color:var(--muted);margin-bottom:12px">Notes for: <strong id="notesClientName"></strong><br><span style="font-size:11px">Internal only — not visible to client</span></p>
      <div class="fg">
        <label>Notes</label>
        <textarea id="notesText" style="min-height:160px" placeholder="e.g. Client called 3x. Waiting for P60. Referred by John Smith. Chased 4 April..."></textarea>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('notesModal')">Cancel</button>
      <button class="btn btn-navy" onclick="saveNotes()">Save Notes</button>
    </div>
  </div>
</div>

<!-- AML MODAL -->
<div class="overlay" id="amlModal">
  <div class="modal">
    <div class="m-head"><div class="m-title">AML — Customer Due Diligence</div><button class="m-close" onclick="closeM('amlModal')">×</button></div>
    <div class="m-body">
      <div id="amlAlert"></div>
      <p style="font-size:13px;color:var(--navy-dark);font-weight:600;margin-bottom:16px" id="amlName"></p>
      <div class="fg-row">
        <div class="fg"><label>ID Document Type</label>
          <select id="amlIdType">
            <option>UK Passport</option><option>UK Driving Licence</option>
            <option>Non-UK Passport</option><option>National Identity Card</option>
            <option>Biometric Residence Permit</option><option>Other Government ID</option>
          </select>
        </div>
        <div class="fg"><label>ID Reference / Last 4 digits</label><input type="text" id="amlIdRef" placeholder="e.g. last 4 digits only"></div>
      </div>
      <div class="fg-row">
        <div class="fg"><label>Date of Verification</label><input type="date" id="amlDate"></div>
        <div class="fg"><label>Risk Assessment</label>
          <select id="amlRisk"><option>Low</option><option>Medium</option><option>High</option></select>
        </div>
      </div>
      <div class="fg"><label>CDD Notes</label>
        <textarea id="amlNotes" placeholder="Record how identity was verified, any enhanced due diligence conducted, and any concerns noted…"></textarea>
      </div>
      <div class="alert a-warn">
        ⚠️ You remain legally responsible for client identity verification under the Money Laundering Regulations 2017. This record assists your compliance recordkeeping — it does not constitute verification itself.
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('amlModal')">Cancel</button>
      <button class="btn btn-navy" onclick="saveAml()">Save AML Record</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const FIRM_NAME    = <?= json_encode(FR_FIRM_NAME) ?>;
const FIRM_ADDR    = <?= json_encode(FR_FIRM_ADDRESS) ?>;
const FIRM_EMAIL   = <?= json_encode(FR_FIRM_EMAIL) ?>;
const FIRM_PHONE   = <?= json_encode(FR_FIRM_PHONE) ?>;
const FIRM_WEBSITE = <?= json_encode(FR_FIRM_WEBSITE) ?>;
const FIRM_ICO     = <?= json_encode(FR_ICO_NUMBER) ?>;
let clients = [], curLinkId = '', curAmlId = '', curNotesId = '', curTab = 'all';

async function api(action, body={}) {
  const r = await fetch('api.php?action='+action, {
    method:'POST', headers:{'Content-Type':'application/json'},
    credentials:'same-origin', body:JSON.stringify(body)
  });
  return r.json();
}

function toast(msg, err=false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.borderLeftColor = err ? '#f87171' : 'var(--gold)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

async function doLogin() {
  const d = await api('login', {password: document.getElementById('pwdInput').value});
  if (d.success) {
    document.getElementById('loginScreen').style.display = 'none';
    document.getElementById('app').style.display = 'block';
    loadClients();
  } else {
    document.getElementById('loginErr').style.display = 'block';
    document.getElementById('pwdInput').value = '';
  }
}

async function doLogout() { await api('logout'); location.reload(); }

window.addEventListener('load', async () => {
  const d = await api('check');
  if (d.auth) {
    document.getElementById('loginScreen').style.display = 'none';
    document.getElementById('app').style.display = 'block';
    loadClients();
    // Auto-refresh every 60 seconds
    setInterval(loadClients, 60000);
  }
});

async function loadClients() {
  const d = await api('clients');
  if (d.success) { clients = d.clients; renderAll(); }
}

function isOverdue(c) {
  if (c.status === 'signed' || !c.deadline_at) return false;
  return new Date(c.deadline_at) < new Date();
}

// ── Monthly stats ──────────────────────────────────────
function renderMonthlyStats() {
  const now = new Date();
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
  const thisMonth = clients.filter(c => new Date(c.created_at) >= monthStart);
  const signedMonth = clients.filter(c => c.signed_at && new Date(c.signed_at) >= monthStart);
  const outstanding = clients.filter(c => c.status !== 'signed').length;

  // Average time to sign (in hours)
  const times = clients
    .filter(c => c.signed_at && c.sent_at)
    .map(c => (new Date(c.signed_at) - new Date(c.sent_at)) / 3600000);
  const avgHrs = times.length ? Math.round(times.reduce((a,b)=>a+b,0)/times.length) : null;
  const avgLabel = avgHrs === null ? 'No data' : avgHrs < 24 ? avgHrs+'h' : Math.round(avgHrs/24)+'d';

  document.getElementById('monthlyStats').innerHTML = `
    <div class="ms"><div class="ms-n">${thisMonth.length}</div><div class="ms-l">Added this month</div></div>
    <div class="ms"><div class="ms-n">${signedMonth.length}</div><div class="ms-l">Signed this month</div></div>
    <div class="ms"><div class="ms-n">${outstanding}</div><div class="ms-l">Outstanding (unsigned)</div></div>
    <div class="ms"><div class="ms-n">${avgLabel}</div><div class="ms-l">Avg. time to sign</div></div>
  `;
}

function renderAll() {
  const overdue = clients.filter(c => isOverdue(c));
  // Show client count badge (only meaningful for provisioned firm instances with a limit)
  const badge = document.getElementById('clientCountBadge');
  if (badge) {
    const active = clients.filter(c => c.status !== 'onboarding').length;
    const limit  = <?php
      $sub_file = FR_DATA_DIR . 'subscription.json';
      if (file_exists($sub_file)) {
          $sub_data = json_decode(file_get_contents($sub_file), true) ?: [];
          echo (int)($sub_data['client_limit'] ?? 50);
      } else {
          echo 0;
      }
    ?>;
    if (limit > 0) {
      badge.textContent = active + '/' + limit + ' clients';
      badge.style.display = 'inline';
      badge.style.color = active >= limit ? '#c0392b' : (active >= limit * 0.8 ? '#b45309' : '#64748b');
    }
  }
  document.getElementById('stTotal').textContent  = clients.length;
  document.getElementById('stSent').textContent   = clients.filter(c=>c.status==='sent').length;
  document.getElementById('stSigned').textContent = clients.filter(c=>c.status==='signed').length;
  document.getElementById('stAml').textContent    = clients.filter(c=>c.aml_status==='complete').length;
  const bar = document.getElementById('overdueBar');
  if (overdue.length > 0) {
    bar.classList.add('show');
    document.getElementById('overdueCount').textContent = overdue.length;
    document.getElementById('tabOverdue').textContent = '⚠️ Overdue (' + overdue.length + ')';
  } else {
    bar.classList.remove('show');
    document.getElementById('tabOverdue').textContent = '⚠️ Overdue';
  }
  renderMonthlyStats();
  renderTable();
  renderAml();
  // Update deadline tab badge
  const allDl = computeAllDeadlines();
  const today = new Date(); today.setHours(0,0,0,0);
  const overdueCount = allDl.filter(d => d.status !== 'filed' && d.status !== 'paid' && d.dueDate < today).length;
  const dlTab = document.getElementById('tabDeadlines');
  if (dlTab) dlTab.textContent = overdueCount > 0 ? `📅 Deadlines (${overdueCount})` : '📅 Deadlines';
}

function switchTab(tab, el) {
  curTab = tab;
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  if (el) el.classList.add('active');
  const titles = {all:'All Clients', pending:'Awaiting Signature', signed:'Signed Clients', overdue:'⚠️ Overdue — Action Required', mtd:'📊 MTD Tracker', archive:'📁 Signed Document Archive'};
  // Hide all views
  ['viewClients','viewAml','viewMtd','viewDeadlines','viewArchive'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  if (tab === 'aml') {
    document.getElementById('viewAml').style.display = 'block';
  } else if (tab === 'mtd') {
    document.getElementById('viewMtd').style.display = 'block';
    renderMtd();
  } else if (tab === 'deadlines') {
    document.getElementById('viewDeadlines').style.display = 'block';
    renderDeadlines('all');
  } else if (tab === 'archive') {
    document.getElementById('viewArchive').style.display = 'block';
    renderArchive();
  } else {
    document.getElementById('viewClients').style.display = 'block';
    document.getElementById('tabTitle').textContent = titles[tab] || 'Clients';
    renderTable();
  }
}

function renderTable() {
  const q = (document.getElementById('searchBox').value||'').toLowerCase();
  let list = clients.filter(c =>
    c.name.toLowerCase().includes(q)||c.email.toLowerCase().includes(q)||(c.company||'').toLowerCase().includes(q)
  );
  if (curTab==='pending') list = list.filter(c=>c.status!=='signed');
  if (curTab==='signed')  list = list.filter(c=>c.status==='signed');
  if (curTab==='overdue') list = list.filter(c=>isOverdue(c));

  const tbody = document.getElementById('clientTbody');
  if (!list.length) {
    const icon = curTab==='overdue' ? '✅' : '📋';
    const msg  = curTab==='overdue' ? 'No overdue clients — everything is on track' : 'No clients found. Click "+ Add New Client" to get started.';
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-icon">${icon}</div><div class="empty-lbl">${msg}</div></div></td></tr>`;
    return;
  }
  tbody.innerHTML = list.map(c => `<tr ${isOverdue(c)?'style="background:#fff8f8;"':''}>
    <td>
      <div class="c-name">${e(c.name)}
        <button class="notes-pill ${c.notes?'has-notes':''}" onclick="openNotes('${c.id}')" title="Internal notes">${c.notes?'📝 Notes':'+ Note'}</button>
      </div>
      <div class="c-info">${e(c.email)}${c.company?' · '+e(c.company):''}${c.fee?' · <strong>'+e(c.fee)+'</strong>':''}</div>
    </td>
    <td style="font-size:12px">${e(c.type)}</td>
    <td style="font-size:12px">${renderServices(c.service)}</td>
    <td>${sigBadge(c)}</td>
    <td>${deadlineBadge(c)}</td>
    <td>${amlBadge(c.aml_status)}</td>
    <td style="white-space:nowrap">${actions(c)}</td>
  </tr>`).join('');
}

function renderAml() {
  const tbody = document.getElementById('amlTbody');
  if (!clients.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><div class="empty-icon">🔐</div><div class="empty-lbl">No clients yet.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = clients.map(c=>`<tr>
    <td><div class="c-name">${e(c.name)}</div><div class="c-info">${e(c.company||'')}</div></td>
    <td style="font-size:12px">${e(c.aml_id_type)||'—'}</td>
    <td style="font-family:monospace;font-size:11px">${e(c.aml_id_ref)||'—'}</td>
    <td><span class="badge ${c.aml_risk==='High'?'b-pending':c.aml_risk==='Medium'?'b-sent':'b-signed'}">${e(c.aml_risk)||'—'}</span></td>
    <td style="font-size:12px">${c.aml_verified_date?new Date(c.aml_verified_date).toLocaleDateString('en-GB'):'—'}</td>
    <td>${amlBadge(c.aml_status)}</td>
    <td><button class="btn btn-outline" onclick="openAml('${c.id}')">${c.aml_status==='complete'?'Edit':'Complete'}</button></td>
  </tr>`).join('');
}

function sigBadge(c) {
  if (c.status==='signed') return `<span class="badge b-signed">✓ Signed<br><span style="font-weight:400;font-size:10px">${new Date(c.signed_at).toLocaleDateString('en-GB')}</span></span>`;
  if (c.status==='sent')   return `<span class="badge b-sent">Link Sent<br><span style="font-weight:400;font-size:10px">${new Date(c.sent_at).toLocaleDateString('en-GB')}</span></span>`;
  return '<span class="badge b-pending">Not Sent</span>';
}
function deadlineBadge(c) {
  if (c.status==='signed'||!c.deadline_at) return '<span style="color:var(--muted);font-size:11px;">—</span>';
  const dl=new Date(c.deadline_at), diff=dl-new Date();
  if (diff<0) { const h=Math.round(Math.abs(diff)/3600000); return `<span class="badge b-overdue">Overdue<br><span style="font-weight:400;font-size:10px">${h>48?Math.round(h/24)+'d ago':h+'h ago'}</span></span>`; }
  if (diff<6*3600000) return `<span class="badge b-due-soon">Due soon<br><span style="font-weight:400;font-size:10px">${dl.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'})}</span></span>`;
  return `<span style="font-size:11px;color:var(--muted)">${dl.toLocaleDateString('en-GB')}<br>${dl.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'})}</span>`;
}
function amlBadge(s) {
  return s==='complete'?'<span class="badge b-aml-ok">✓ Complete</span>':'<span class="badge b-aml-p">Pending</span>';
}
function actions(c) {
  let a='';
  if (c.status==='signed') {
    a+=`<a class="btn btn-navy" href="api.php?action=download_pdf&id=${c.id}" target="_blank" style="margin-right:4px">⬇ PDF</a>`;
  } else {
    a+=`<button class="btn btn-gold" onclick="openLink('${c.id}')" style="margin-right:4px">Send Link</button>`;
    if (c.status==='sent') {
      const n=parseInt(c.reminders_sent||0);
      const label=n>0?`🔔 Reminder ${n+1}`:'🔔 Remind';
      a+=`<button class="btn ${isOverdue(c)?'btn-red':'btn-outline'}" onclick="sendReminder('${c.id}')" style="margin-right:4px">${label}</button>`;
      // WhatsApp button if phone exists
      if (c.phone) {
        const phone = c.phone.replace(/\D/g,'');
        const waPhone = phone.startsWith('0') ? '44'+phone.slice(1) : phone;
        const waMsg = encodeURIComponent(`Dear ${c.name}, this is a reminder from The Practice. Could you please sign your engagement letter at your earliest convenience? Thank you.`);
        a+=`<a class="btn btn-outline" href="https://wa.me/${waPhone}?text=${waMsg}" target="_blank" style="margin-right:4px" title="WhatsApp reminder">💬</a>`;
      }
    }
  }
  a+=`<button class="btn btn-outline" onclick="openAml('${c.id}')" style="margin-right:4px">AML</button>`;
  a+=`<button class="btn btn-outline" onclick="openDeadlines('${c.id}')" style="margin-right:4px" title="Deadline tracker">📅</button>`;
  // DPA button
  if (c.dpa_status === 'signed') {
    a+=`<span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;margin-right:4px;font-size:10px">✓ DPA</span>`;
  } else if (c.dpa_status === 'sent') {
    a+=`<span class="badge" style="background:#fef9c3;color:#92400e;border:1px solid #fde68a;margin-right:4px;font-size:10px">DPA Sent</span>`;
  } else {
    a+=`<button class="btn btn-outline" onclick="sendDpa('${c.id}')" style="margin-right:4px;font-size:11px" title="Send GDPR Data Processing Agreement">DPA</button>`;
  }
  // Activate onboarding
  if (c.status === 'onboarded') {
    a+=`<button class="btn btn-gold" onclick="activateOnboard('${c.id}')" style="margin-right:4px;font-size:11px">✓ Activate</button>`;
  }
  a+=`<button class="btn btn-red" onclick="delClient('${c.id}')">✕</button>`;
  return a;
}
function e(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function openM(id)  { document.getElementById(id).classList.add('open'); }
function closeM(id) { document.getElementById(id).classList.remove('open'); }

// ── Add Client ──────────────────────────────────
let customSvcs = [];

function addCustomSvc() {
  const inp = document.getElementById('addCustomSvc');
  const val = inp.value.trim();
  if (!val) return;
  if (!customSvcs.includes(val)) { customSvcs.push(val); renderSvcTags(); }
  inp.value = '';
}
function renderSvcTags() {
  document.getElementById('addSvcTags').innerHTML = customSvcs.map((s,i) =>
    `<span class="svc-tag">${e(s)}<button class="svc-tag-x" onclick="customSvcs.splice(${i},1);renderSvcTags()">×</button></span>`
  ).join('');
}
function getSelectedServices() {
  const checked = [...document.querySelectorAll('#addModal input[name="svc"]:checked')].map(el => el.value);
  return [...checked, ...customSvcs];
}
function renderServices(svc) {
  if (!svc) return '—';
  const parts = svc.split('\n').filter(s => s.trim());
  if (!parts.length) return '—';
  if (parts.length === 1) return e(parts[0]);
  return e(parts[0]) + `<span style="color:var(--muted);font-size:11px"> +${parts.length-1} more</span>`;
}

function openAddClient() {
  ['addName','addEmail','addCompany','addCompanyNumber','addAddress','addPhone'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
  const chRes = document.getElementById('chResults'); if(chRes) chRes.style.display='none';
  document.querySelectorAll('#addModal input[name="svc"]').forEach(cb=>cb.checked=false);
  customSvcs = [];
  renderSvcTags();
  document.getElementById('addCustomSvc').value = '';
  document.getElementById('addAlert').innerHTML='';
  openM('addModal');
}
async function addClient() {
  const name=document.getElementById('addName').value.trim();
  const email=document.getElementById('addEmail').value.trim();
  if (!name||!email) { document.getElementById('addAlert').innerHTML='<div class="alert a-err">Name and email are required.</div>'; return; }
  const svcs = getSelectedServices();
  if (!svcs.length) { document.getElementById('addAlert').innerHTML='<div class="alert a-err">Please select at least one service.</div>'; return; }
  const d = await api('add_client',{name,email,
    company:       document.getElementById('addCompany').value.trim(),
    company_number:document.getElementById('addCompanyNumber').value.trim(),
    address:       document.getElementById('addAddress').value.trim(),
    type:          document.getElementById('addType').value,
    service:       svcs.join('\n'),
    phone:         document.getElementById('addPhone').value.trim()
  });
  if (d.success) { clients.unshift(d.client); renderAll(); closeM('addModal'); toast('Client added'); }
  else document.getElementById('addAlert').innerHTML=`<div class="alert a-err">${d.error}</div>`;
}

// ── Send Link with letter editor ─────────────────
function buildDefaultLetter(c) {
  const today = new Date().toLocaleDateString('en-GB',{day:'numeric',month:'long',year:'numeric'});
  const feeLine = c.fee ? `\n\nFEES\nOur agreed fee for the above services is: ${c.fee}\n` : '\n\nFEES\nOur fees will be agreed and confirmed separately in writing.\n';
  const svcParts = (c.service||'').split('\n').filter(s=>s.trim());
  const serviceBullets = svcParts.length ? svcParts.map(s=>'• '+s).join('\n') : '• Services to be confirmed';
  const svcSummary = svcParts.length ? svcParts.join(', ') : 'Accountancy Services';
  const clientBlock = [c.name, c.company||'', c.type].filter(Boolean).join('\n');
  return `CLIENT ENGAGEMENT LETTER
────────────────────────────────────────────────────────────────────

${FIRM_NAME}
${FIRM_ADDR}
Tel:     ${FIRM_PHONE}
Email:   ${FIRM_EMAIL}
Web:     ${FIRM_WEBSITE}
ICO Reg: ${FIRM_ICO}

                                                        ${today}

────────────────────────────────────────────────────────────────────

${clientBlock}
[Client Address — please complete before sending]

────────────────────────────────────────────────────────────────────

Dear ${c.name.split(' ')[0]},

Re: Letter of Engagement — ${svcSummary}

Thank you for choosing ${FIRM_NAME}. We are pleased to confirm our appointment as your accountants. This letter sets out the basis on which we will act for you.

SERVICES
We will provide the following services:
${serviceBullets}
${feeLine}
OUR RESPONSIBILITIES
We will carry out the agreed services with reasonable skill, care and diligence, maintain the confidentiality of your affairs and comply with UK GDPR, and communicate clearly on all matters affecting you.

YOUR RESPONSIBILITIES
You agree to provide us with complete, accurate and timely information, inform us promptly of any changes to your circumstances, and pay our fees within the agreed terms.

ANTI-MONEY LAUNDERING (AML)
We are required to comply with the Money Laundering Regulations 2017. We may need to verify your identity and you agree to provide all information we reasonably request.

MAKING TAX DIGITAL (MTD)
Where applicable, we will assist you in meeting your MTD obligations. You remain responsible for the accuracy of all information submitted to HMRC.

DATA PROTECTION
${FIRM_NAME} processes your personal data as Data Controller in accordance with UK GDPR and the Data Protection Act 2018. ICO Registration: ${FIRM_ICO}.

GOVERNING LAW
This engagement is governed by the laws of England and Wales.

Yours sincerely,


___________________________
${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}
${FIRM_WEBSITE}`;
}

// ── Firm Signature ────────────────────────────────
let savedFirmSig = '', fsigCtx = null, fsigDrawing = false, fsigLx = 0, fsigLy = 0, fsigUploadData = '';

function switchFSigTab(tab) {
  ['Saved','Draw','Upload'].forEach(t => {
    document.getElementById('fsigTab'+t).classList.toggle('active', t.toLowerCase()===tab);
    document.getElementById('fsigPanel'+t).style.display = t.toLowerCase()===tab ? 'block' : 'none';
  });
  if (tab === 'draw') setTimeout(initFSigCanvas, 50);
}
function initFSigCanvas() {
  const canvas = document.getElementById('fsigCanvas');
  if (!canvas || fsigCtx) return;
  const r = canvas.parentElement.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;
  canvas.width = r.width * dpr; canvas.height = 110 * dpr;
  canvas.style.width = r.width + 'px'; canvas.style.height = '110px';
  fsigCtx = canvas.getContext('2d');
  fsigCtx.scale(dpr, dpr);
  fsigCtx.strokeStyle = '#0f2238'; fsigCtx.lineWidth = 2.2; fsigCtx.lineCap = 'round'; fsigCtx.lineJoin = 'round';
  canvas.onmousedown = e => { fsigDrawing=true; const r=canvas.getBoundingClientRect(); fsigLx=e.clientX-r.left; fsigLy=e.clientY-r.top; };
  canvas.onmousemove = e => { if(!fsigDrawing)return; const r=canvas.getBoundingClientRect(); const x=e.clientX-r.left,y=e.clientY-r.top; fsigCtx.beginPath(); fsigCtx.moveTo(fsigLx,fsigLy); fsigCtx.lineTo(x,y); fsigCtx.stroke(); fsigLx=x; fsigLy=y; };
  canvas.onmouseup = canvas.onmouseleave = () => fsigDrawing = false;
}
function clearFSig() {
  if (fsigCtx) { const c=document.getElementById('fsigCanvas'); fsigCtx.clearRect(0,0,c.width,c.height); }
}
function loadFSigFile(input) {
  const file = input.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    fsigUploadData = ev.target.result;
    const prev = document.getElementById('fsigUploadPreview');
    prev.src = fsigUploadData; prev.style.display = 'block';
  };
  reader.readAsDataURL(file);
}
async function saveFirmSig(source) {
  const sigData = source === 'draw'
    ? document.getElementById('fsigCanvas').toDataURL('image/png')
    : fsigUploadData;
  if (!sigData || sigData === 'data:,') { toast('Nothing to save — please draw or upload first'); return; }
  const d = await api('save_firm_sig', { sig: sigData });
  if (d.success) {
    savedFirmSig = sigData;
    document.getElementById('fsigPreview').src = sigData;
    document.getElementById('fsigPreview').style.display = 'block';
    document.getElementById('fsigNoSig').style.display = 'none';
    const okId = source === 'draw' ? 'drawSavedOk' : 'uploadSavedOk';
    const ok = document.getElementById(okId);
    ok.style.display = 'inline'; setTimeout(() => ok.style.display = 'none', 2500);
    switchFSigTab('saved');
    toast('Firm signature saved');
  } else { toast('Save failed — ' + (d.error || 'unknown error')); }
}

function openLink(id) {
  curLinkId = id;
  const c = clients.find(x=>x.id===id);
  document.getElementById('linkEmail').textContent = c.email;
  document.getElementById('linkAlert').innerHTML = '';
  document.getElementById('linkFee').value = c.fee || '';
  document.getElementById('linkDeadline').value = '48';
  // Populate letter editor
  const letter = c.custom_letter && c.custom_letter.length > 100
    ? c.custom_letter
    : buildDefaultLetter(c);
  document.getElementById('letterEditor').value = letter;
  // Show step 1
  document.getElementById('linkStep1').style.display = 'block';
  document.getElementById('linkStep2').style.display = 'none';
  document.getElementById('linkFoot').style.display = 'flex';
  document.getElementById('sendBtn').disabled = false;
  document.getElementById('sendBtn').textContent = '📧 Save Letter & Send →';
  // Load firm signature
  switchFSigTab('saved');
  fsigCtx = null; // reset so canvas re-inits on next Draw tab open
  api('get_firm_sig', {}).then(d => {
    savedFirmSig = d.sig || '';
    const prev = document.getElementById('fsigPreview');
    const none = document.getElementById('fsigNoSig');
    if (savedFirmSig) { prev.src = savedFirmSig; prev.style.display='block'; none.style.display='none'; }
    else { prev.style.display='none'; none.style.display='block'; }
  });
  openM('linkModal');
}

async function sendLink() {
  document.getElementById('sendBtn').disabled = true;
  document.getElementById('sendBtn').textContent = 'Saving & Sending…';
  const deadlineHours = parseInt(document.getElementById('linkDeadline').value);
  const customLetter  = document.getElementById('letterEditor').value.trim();
  const fee           = document.getElementById('linkFee').value.trim();

  // Save letter first
  await api('update_letter', {id: curLinkId, custom_letter: customLetter, fee});

  // Then send link
  const d = await api('send_link', {id: curLinkId, deadline_hours: deadlineHours});
  if (d.success) {
    const idx = clients.findIndex(c=>c.id===curLinkId);
    clients[idx].status       = 'sent';
    clients[idx].sent_at      = new Date().toISOString();
    clients[idx].deadline_at  = d.deadline;
    clients[idx].deadline_hours = deadlineHours;
    clients[idx].custom_letter  = customLetter;
    clients[idx].fee            = fee;
    renderAll();
    // Show step 2
    document.getElementById('linkStep1').style.display = 'none';
    document.getElementById('linkStep2').style.display = 'block';
    document.getElementById('linkFoot').style.display = 'none';
    document.getElementById('linkText').textContent = d.link;
    const dl = new Date(d.deadline).toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long',hour:'2-digit',minute:'2-digit'});
    document.getElementById('linkDeadlineConfirm').textContent = dl;
    document.getElementById('linkAlert').innerHTML = '';
  } else {
    document.getElementById('linkAlert').innerHTML = `<div class="alert a-err">${d.error||'Email failed. Check your config.php email settings.'}</div>`;
    document.getElementById('sendBtn').disabled = false;
    document.getElementById('sendBtn').textContent = '📧 Save Letter & Send →';
  }
}

function copyLink() {
  navigator.clipboard.writeText(document.getElementById('linkText').textContent).then(()=>{
    const el = document.getElementById('linkCopied');
    el.style.display = 'block';
    setTimeout(()=>el.style.display='none', 2000);
  });
}

// ── Remind All Overdue ──────────────────────────
async function remindAll() {
  const overdue = clients.filter(c=>isOverdue(c));
  if (!overdue.length) { toast('No overdue clients'); return; }
  if (!confirm(`Send reminders to all ${overdue.length} overdue client(s)?`)) return;
  toast('Sending reminders…');
  const d = await api('remind_all');
  if (d.success) {
    await loadClients();
    toast(`Sent ${d.sent} reminder(s)${d.failed?', '+d.failed+' failed':''}`, d.failed>0);
  } else toast('Error sending reminders', true);
}

// ── Send individual reminder ─────────────────────
async function sendReminder(id) {
  const c = clients.find(x=>x.id===id);
  const n = parseInt(c.reminders_sent||0)+1;
  if (!confirm(`Send Reminder ${n} to ${c.name} at ${c.email}?`)) return;
  const d = await api('send_reminder',{id});
  if (d.success) {
    const idx = clients.findIndex(x=>x.id===id);
    clients[idx].reminders_sent   = d.reminder_count;
    clients[idx].last_reminder_at = new Date().toISOString();
    renderAll();
    toast(`Reminder ${d.reminder_count} sent to ${c.name}`);
  } else toast(d.error||'Could not send reminder', true);
}

// ── Notes ───────────────────────────────────────
function openNotes(id) {
  curNotesId = id;
  const c = clients.find(x=>x.id===id);
  document.getElementById('notesClientName').textContent = c.name;
  document.getElementById('notesText').value = c.notes||'';
  document.getElementById('notesAlert').innerHTML = '';
  openM('notesModal');
}
async function saveNotes() {
  const notes = document.getElementById('notesText').value;
  const d = await api('save_notes',{id:curNotesId, notes});
  if (d.success) {
    const idx = clients.findIndex(c=>c.id===curNotesId);
    clients[idx].notes = notes;
    renderAll();
    closeM('notesModal');
    toast('Notes saved');
  } else document.getElementById('notesAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
}

// ── AML ─────────────────────────────────────────
function openAml(id) {
  curAmlId=id;
  const c=clients.find(x=>x.id===id);
  document.getElementById('amlName').textContent=c.name+(c.company?' — '+c.company:'');
  document.getElementById('amlIdType').value=c.aml_id_type||'UK Passport';
  document.getElementById('amlIdRef').value=c.aml_id_ref||'';
  document.getElementById('amlDate').value=c.aml_verified_date||new Date().toISOString().split('T')[0];
  document.getElementById('amlRisk').value=c.aml_risk||'Low';
  document.getElementById('amlNotes').value=c.aml_notes||'';
  document.getElementById('amlAlert').innerHTML='';
  openM('amlModal');
}
async function saveAml() {
  const d=await api('update_aml',{id:curAmlId,
    aml_id_type:document.getElementById('amlIdType').value,
    aml_id_ref:document.getElementById('amlIdRef').value.trim(),
    aml_verified_date:document.getElementById('amlDate').value,
    aml_risk:document.getElementById('amlRisk').value,
    aml_notes:document.getElementById('amlNotes').value.trim()
  });
  if (d.success) {
    const idx=clients.findIndex(c=>c.id===curAmlId);
    Object.assign(clients[idx],{aml_status:'complete',
      aml_id_type:document.getElementById('amlIdType').value,
      aml_id_ref:document.getElementById('amlIdRef').value,
      aml_verified_date:document.getElementById('amlDate').value,
      aml_risk:document.getElementById('amlRisk').value,
      aml_notes:document.getElementById('amlNotes').value
    });
    renderAll(); closeM('amlModal'); toast('AML record saved');
  } else document.getElementById('amlAlert').innerHTML=`<div class="alert a-err">${d.error}</div>`;
}

// ── Delete ──────────────────────────────────────
async function delClient(id) {
  if (!confirm('Delete this client? This cannot be undone.')) return;
  const d=await api('delete_client',{id});
  if (d.success) { clients=clients.filter(c=>c.id!==id); renderAll(); toast('Client removed'); }
}

// ── MTD Tracker ─────────────────────────────────
let curMtdId = '';

function mtdStatusBadge(s, threshold) {
  if (!threshold) return '<span class="badge b-mtd-na">Not set</span>';
  const live = threshold === 'over50k';
  const soon27 = threshold === '30k-50k';
  const soon28 = threshold === '20k-30k';
  if (s === 'compliant') return '<span class="badge b-mtd-ok">✓ Compliant</span>';
  if (s === 'enrolled')  return '<span class="badge b-mtd-ok">Enrolled</span>';
  if (s === 'exempt')    return '<span class="badge b-mtd-na">Exempt</span>';
  if (s === 'in_progress') return '<span class="badge b-mtd-soon">In Progress</span>';
  if (live)  return '<span class="badge b-mtd-live">⚠️ Action Required</span>';
  if (soon27 || soon28) return '<span class="badge b-mtd-soon">Coming Soon</span>';
  return '<span class="badge b-mtd-na">Not in scope</span>';
}

function mtdThresholdLabel(t) {
  const map = {
    'over50k':'Over £50k — In scope NOW',
    '30k-50k':'£30k–£50k — April 2027',
    '20k-30k':'£20k–£30k — April 2028',
    'under20k':'Under £20k — Not in scope'
  };
  return map[t] || '—';
}

function renderMtd() {
  const tbody = document.getElementById('mtdTbody');
  if (!clients.length) {
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty"><div class="empty-icon">📊</div><div class="empty-lbl">No clients yet.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = clients.map(c => `<tr>
    <td><div class="c-name">${e(c.name)}</div><div class="c-info">${e(c.company||'')} · ${e(c.type)}</div></td>
    <td style="font-size:12px">${e(mtdThresholdLabel(c.mtd_threshold||''))}</td>
    <td>${mtdStatusBadge(c.mtd_status||'not_started', c.mtd_threshold||'')}</td>
    <td style="font-size:12px">${e(c.mtd_software||'—')}</td>
    <td style="font-size:12px">${c.mtd_enrol_date?new Date(c.mtd_enrol_date).toLocaleDateString('en-GB'):'—'}</td>
    <td style="font-size:12px">${c.mtd_next_sub?'<strong>'+new Date(c.mtd_next_sub).toLocaleDateString('en-GB')+'</strong>':'—'}</td>
    <td style="font-size:11px;color:var(--muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${e(c.mtd_notes||'')}">${e(c.mtd_notes||'—')}</td>
    <td><button class="btn btn-outline" onclick="openMtd('${c.id}')">Update</button></td>
  </tr>`).join('');
}

const MTD_REM_IDS = ['mtdRem28','mtdRem14','mtdRem7','mtdRem3','mtdRem1','mtdRem0'];

function openMtd(id) {
  curMtdId = id;
  const c = clients.find(x=>x.id===id);
  document.getElementById('mtdClientName').textContent = c.name;
  document.getElementById('mtdThreshold').value  = c.mtd_threshold||'';
  document.getElementById('mtdStatus').value     = c.mtd_status||'not_started';
  document.getElementById('mtdSoftware').value   = c.mtd_software||'';
  document.getElementById('mtdEnrolDate').value  = c.mtd_enrol_date||'';
  document.getElementById('mtdNextSubmission').value = c.mtd_next_sub||'';
  document.getElementById('mtdNotes').value      = c.mtd_notes||'';
  document.getElementById('mtdAlert').innerHTML  = '';
  // Load reminder day checkboxes
  const savedDays = Array.isArray(c.mtd_reminder_days) ? c.mtd_reminder_days.map(String) : [];
  MTD_REM_IDS.forEach(rid => {
    const el = document.getElementById(rid);
    el.checked = savedDays.includes(el.value);
  });
  openM('mtdModal');
}

async function saveMtd() {
  const reminderDays = MTD_REM_IDS
    .map(rid => document.getElementById(rid))
    .filter(el => el.checked)
    .map(el => parseInt(el.value));
  const d = await api('save_mtd', {
    id:                  curMtdId,
    mtd_threshold:       document.getElementById('mtdThreshold').value,
    mtd_status:          document.getElementById('mtdStatus').value,
    mtd_software:        document.getElementById('mtdSoftware').value,
    mtd_enrol_date:      document.getElementById('mtdEnrolDate').value,
    mtd_next_sub:        document.getElementById('mtdNextSubmission').value,
    mtd_notes:           document.getElementById('mtdNotes').value.trim(),
    mtd_reminder_days:   reminderDays
  });
  if (d.success) {
    const idx = clients.findIndex(c=>c.id===curMtdId);
    Object.assign(clients[idx], {
      mtd_threshold:      document.getElementById('mtdThreshold').value,
      mtd_status:         document.getElementById('mtdStatus').value,
      mtd_software:       document.getElementById('mtdSoftware').value,
      mtd_enrol_date:     document.getElementById('mtdEnrolDate').value,
      mtd_next_sub:       document.getElementById('mtdNextSubmission').value,
      mtd_notes:          document.getElementById('mtdNotes').value,
      mtd_reminder_days:  reminderDays
    });
    renderAll();
    closeM('mtdModal');
    toast('MTD record saved');
  } else {
    document.getElementById('mtdAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
  }
}

// ── Deadline Tracker ─────────────────────────────
let curDlId = null;
let dlActiveFilter = 'all';

// Add months to a date, return new Date
function addMonths(date, months) {
  const d = new Date(date);
  d.setMonth(d.getMonth() + months);
  return d;
}
function addDays(date, days) {
  const d = new Date(date);
  d.setDate(d.getDate() + days);
  return d;
}
function nextDateForDay(month0, day) {
  // Returns the next occurrence of a given month (0-based) and day
  const now = new Date(); now.setHours(0,0,0,0);
  let d = new Date(now.getFullYear(), month0, day);
  if (d <= now) d.setFullYear(d.getFullYear() + 1);
  return d;
}

// Get upcoming VAT due dates (next 4 quarters) for a given quarter group
function vatDueDates(group) {
  // quarter end months (0-based) per group
  const ends = { jan:[0,3,6,9], feb:[1,4,7,10], mar:[2,5,8,11] };
  if (!ends[group]) return [];
  const today = new Date(); today.setHours(0,0,0,0);
  const results = [];
  const months = ends[group];
  for (let y = today.getFullYear() - 1; y <= today.getFullYear() + 1; y++) {
    for (const m of months) {
      // Due = 1 month + 7 days after quarter end
      const quarterEnd = new Date(y, m + 1, 0); // last day of quarter-end month
      const due = addDays(addMonths(quarterEnd, 1), 7);
      if (due >= today) results.push(due);
    }
  }
  results.sort((a,b)=>a-b);
  return results.slice(0, 4); // next 4 quarters
}

// Compute payroll RTI due dates (19th of each month, next 3)
function payrollDueDates() {
  const today = new Date(); today.setHours(0,0,0,0);
  const results = [];
  for (let i = 0; i < 4; i++) {
    const d = new Date(today.getFullYear(), today.getMonth() + i, 19);
    if (d >= today) results.push(d);
    if (results.length >= 3) break;
  }
  return results;
}

// Return next 31 Jan / 31 Jul for SA
function saDueDates() {
  const today = new Date(); today.setHours(0,0,0,0);
  const year = today.getFullYear();
  const dates = [];
  const candidates = [
    new Date(year, 0, 31),   // 31 Jan this year
    new Date(year, 6, 31),   // 31 Jul this year
    new Date(year+1, 0, 31), // 31 Jan next year
    new Date(year+1, 6, 31), // 31 Jul next year
  ];
  candidates.forEach(d => { if (d >= today) dates.push(d); });
  return dates.slice(0, 3);
}

// Compute all deadline entries for a single client
function computeClientDeadlines(c) {
  const deadlines = [];
  const statuses  = c.dl_statuses || {};
  const today     = new Date(); today.setHours(0,0,0,0);

  function entry(type, label, dueDate, keyOverride) {
    const key = keyOverride || (type + ':' + dueDate.toISOString().slice(0,10));
    const status = statuses[key] || 'pending';
    return { clientId: c.id, clientName: c.name, type, label, dueDate, key, status };
  }

  // Corporation Tax & Companies House (needs year end)
  if (c.dl_year_end) {
    const ye = new Date(c.dl_year_end);
    const ctReturn  = addMonths(ye, 12);
    const ctPayment = addDays(addMonths(ye, 9), 1);
    const chAccounts = addMonths(ye, 9);
    deadlines.push(entry('ct_return',   'CT600 — Corporation Tax Return', ctReturn));
    deadlines.push(entry('ct_payment',  'Corporation Tax Payment',        ctPayment));
    deadlines.push(entry('ch_accounts', 'Companies House Annual Accounts', chAccounts));
  }

  // Confirmation Statement
  if (c.dl_confirmation_due) {
    const cs = new Date(c.dl_confirmation_due);
    if (cs >= today) {
      deadlines.push(entry('confirmation', 'Confirmation Statement', cs, 'confirmation:' + c.dl_confirmation_due));
    }
    // Also show next year's if this one is close
    const csNext = new Date(cs); csNext.setFullYear(csNext.getFullYear() + 1);
    if (csNext >= today && csNext <= addMonths(today, 14)) {
      deadlines.push(entry('confirmation', 'Confirmation Statement', csNext, 'confirmation:' + csNext.toISOString().slice(0,10)));
    }
  }

  // VAT
  if (c.dl_vat && c.dl_vat !== 'none') {
    vatDueDates(c.dl_vat).forEach(d => {
      deadlines.push(entry('vat', 'VAT Return & Payment', d));
    });
  }

  // Payroll RTI
  if (c.dl_payroll && c.dl_payroll !== 'none') {
    payrollDueDates().forEach(d => {
      deadlines.push(entry('payroll', 'Payroll RTI Submission', d));
    });
  }

  // Self Assessment
  if (c.dl_self_assessment) {
    saDueDates().forEach((d, i) => {
      const label = (d.getMonth() === 0) ? 'Self Assessment Return + Payment on Account' : 'SA — Second Payment on Account';
      deadlines.push(entry('sa', label, d));
    });
  }

  return deadlines;
}

// Compute all deadlines across all clients
function computeAllDeadlines() {
  let all = [];
  clients.forEach(c => { all = all.concat(computeClientDeadlines(c)); });
  all.sort((a,b) => a.dueDate - b.dueDate);
  return all;
}

function dlDaysLeft(dueDate) {
  const today = new Date(); today.setHours(0,0,0,0);
  return Math.round((dueDate - today) / 86400000);
}

function dlRowClass(d) {
  if (d.status === 'filed' || d.status === 'paid') return 'dl-done';
  const days = dlDaysLeft(d.dueDate);
  if (days < 0)  return 'dl-red';
  if (days <= 30) return 'dl-red';
  if (days <= 60) return 'dl-amber';
  return 'dl-green';
}

function dlTypeColour(type) {
  const map = {
    ct_return:'#dbeafe', ct_payment:'#fce7f3', ch_accounts:'#ede9fe',
    confirmation:'#d1fae5', vat:'#fef9c3', payroll:'#ffedd5', sa:'#e0f2fe'
  };
  return map[type] || '#f1f5f9';
}

let dlFilterState = 'all';

function dlFilter(f, el) {
  dlFilterState = f;
  document.querySelectorAll('.dl-filter').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  renderDeadlines(f);
}

function renderDeadlines(filter) {
  filter = filter || dlFilterState;
  const today = new Date(); today.setHours(0,0,0,0);
  let all = computeAllDeadlines();

  // Update overdue badge
  const overdueCount = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && d.dueDate < today).length;
  const oc = document.getElementById('dlCountOverdue');
  if (oc) { oc.textContent = overdueCount; oc.style.display = overdueCount > 0 ? 'inline' : 'none'; }

  // Apply filter
  if (filter === 'overdue') all = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && d.dueDate < today);
  else if (filter === 'soon') all = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && dlDaysLeft(d.dueDate) >= 0 && dlDaysLeft(d.dueDate) <= 30);
  else if (filter === 'upcoming') all = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && dlDaysLeft(d.dueDate) > 30);
  else if (filter === 'done') all = all.filter(d => d.status === 'filed' || d.status === 'paid');

  const tbody = document.getElementById('dlTbody');
  if (!all.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="empty-icon">📅</div><div class="empty-lbl">${filter === 'all' ? 'No deadlines set up yet. Click 📅 on any client row to configure.' : 'No deadlines match this filter.'}</div></div></td></tr>`;
    return;
  }

  tbody.innerHTML = all.map(d => {
    const days = dlDaysLeft(d.dueDate);
    const rc   = dlRowClass(d);
    const done = d.status === 'filed' || d.status === 'paid';
    const daysLabel = done ? '—'
      : days < 0  ? `<strong style="color:#991b1b">${Math.abs(days)}d overdue</strong>`
      : days === 0 ? '<strong style="color:#991b1b">Today!</strong>'
      : days === 1 ? '<strong style="color:#c2410c">Tomorrow</strong>'
      : `${days} days`;

    const statusLabel = d.status === 'filed' ? '✓ Filed'
      : d.status === 'paid' ? '✓ Paid'
      : days < 0 ? '⚠️ Overdue' : 'Pending';

    const actionBtns = done
      ? `<button class="btn btn-outline" style="font-size:11px;padding:4px 10px" onclick="markDl('${d.clientId}','${d.key}','pending')">↩ Reopen</button>`
      : `<button class="btn btn-outline" style="font-size:11px;padding:4px 10px;margin-right:4px" onclick="markDl('${d.clientId}','${d.key}','filed')">✓ Filed</button>
         <button class="btn btn-outline" style="font-size:11px;padding:4px 10px" onclick="markDl('${d.clientId}','${d.key}','paid')">£ Paid</button>`;

    return `<tr class="${rc}">
      <td style="font-weight:600">${e(d.clientName)}</td>
      <td><span class="dl-type" style="background:${dlTypeColour(d.type)}">${e(d.label)}</span></td>
      <td>${d.dueDate.toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</td>
      <td>${daysLabel}</td>
      <td><span style="font-size:12px;font-weight:600">${statusLabel}</span></td>
      <td>${actionBtns}</td>
    </tr>`;
  }).join('');
}

function openDeadlines(id) {
  curDlId = id;
  const c = clients.find(x=>x.id===id);
  document.getElementById('dlClientName').textContent = c.name;
  document.getElementById('dlYearEnd').value       = c.dl_year_end || '';
  document.getElementById('dlConfirmation').value  = c.dl_confirmation_due || '';
  document.getElementById('dlVat').value           = c.dl_vat || 'none';
  document.getElementById('dlPayroll').value       = c.dl_payroll || 'none';
  document.getElementById('dlSa').checked          = !!c.dl_self_assessment;
  document.getElementById('dlAlert').innerHTML     = '';
  openM('dlModal');
}

async function saveDeadlines() {
  const payload = {
    id:                   curDlId,
    dl_year_end:          document.getElementById('dlYearEnd').value,
    dl_confirmation_due:  document.getElementById('dlConfirmation').value,
    dl_vat:               document.getElementById('dlVat').value,
    dl_payroll:           document.getElementById('dlPayroll').value,
    dl_self_assessment:   document.getElementById('dlSa').checked
  };
  const d = await api('save_deadlines', payload);
  if (d.success) {
    const idx = clients.findIndex(c=>c.id===curDlId);
    Object.assign(clients[idx], payload);
    renderAll();
    closeM('dlModal');
    toast('Deadline settings saved');
  } else {
    document.getElementById('dlAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
  }
}

async function markDl(clientId, key, status) {
  const d = await api('mark_deadline', { id: clientId, key, status });
  if (d.success) {
    const idx = clients.findIndex(c=>c.id===clientId);
    if (!clients[idx].dl_statuses) clients[idx].dl_statuses = {};
    clients[idx].dl_statuses[key] = status;
    renderDeadlines();
    // Update tab badge
    const allDl = computeAllDeadlines();
    const today = new Date(); today.setHours(0,0,0,0);
    const oc = allDl.filter(x => x.status !== 'filed' && x.status !== 'paid' && x.dueDate < today).length;
    const dlTab = document.getElementById('tabDeadlines');
    if (dlTab) dlTab.textContent = oc > 0 ? `📅 Deadlines (${oc})` : '📅 Deadlines';
  }
}

// ── Companies House Lookup ───────────────────────
async function chLookup() {
  const q = document.getElementById('addCompany').value.trim();
  if (q.length < 2) { alert('Enter at least 2 characters to search.'); return; }
  const res  = document.getElementById('chResults');
  res.style.display = 'block';
  res.innerHTML = '<div style="padding:12px;color:var(--muted);font-size:13px">Searching Companies House…</div>';
  const d = await api('ch_lookup', { query: q });
  if (!d.ok || !d.results.length) {
    res.innerHTML = '<div style="padding:12px;font-size:13px;color:var(--muted)">' + (d.error || 'No active companies found.') + '</div>';
    return;
  }
  res.innerHTML = d.results.map(r => `
    <div onclick="chSelect(${JSON.stringify(r).replace(/"/g,'&quot;')})"
         style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f0ede6;font-size:13px"
         onmouseover="this.style.background='#f8f7f4'" onmouseout="this.style.background=''">
      <strong>${e(r.title)}</strong>
      <span style="font-size:11px;color:var(--muted);margin-left:8px">${e(r.company_number)}</span>
      <div style="font-size:11px;color:var(--muted)">${e([r.address_line_1,r.locality,r.postal_code].filter(Boolean).join(', '))}</div>
    </div>
  `).join('');
}

function chSelect(r) {
  document.getElementById('addCompany').value       = r.title;
  document.getElementById('addCompanyNumber').value = r.company_number;
  const addr = [r.address_line_1, r.address_line_2, r.locality, r.region, r.postal_code].filter(Boolean).join(', ');
  document.getElementById('addAddress').value = addr;
  // Map CH company type to our entity types
  const typeMap = { 'ltd': 'Ltd Company', 'llp': 'LLP', 'private-unlimited': 'Ltd Company', 'plc': 'Ltd Company' };
  const mapped  = typeMap[(r.type||'').toLowerCase()] || '';
  if (mapped) document.getElementById('addType').value = mapped;
  document.getElementById('chResults').style.display = 'none';
}

// ── Onboarding ────────────────────────────────────
async function sendOnboard() {
  const name  = document.getElementById('obName').value.trim();
  const email = document.getElementById('obEmail').value.trim();
  if (!name || !email) { document.getElementById('onboardAlert').innerHTML = '<div class="alert a-err">Name and email are required.</div>'; return; }
  document.getElementById('obSendBtn').disabled = true;
  document.getElementById('obSendBtn').textContent = 'Sending…';
  const d = await api('create_onboard', { name, email });
  document.getElementById('obSendBtn').disabled = false;
  document.getElementById('obSendBtn').textContent = 'Send Onboarding Link';
  if (d.success) {
    document.getElementById('onboardAlert').innerHTML = '';
    document.getElementById('obSentEmail').textContent = email;
    document.getElementById('obLinkCopy').value = d.link;
    document.getElementById('onboardLink').style.display = 'block';
    document.getElementById('obName').value  = '';
    document.getElementById('obEmail').value = '';
    loadClients();
  } else {
    document.getElementById('onboardAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
  }
}

async function activateOnboard(id) {
  if (!confirm('Activate this client? They will move from Onboarded to Pending and be ready for an engagement letter.')) return;
  const d = await api('activate_onboard', { id });
  if (d.success) { await loadClients(); toast('Client activated'); }
  else alert(d.error);
}

// ── DPA ───────────────────────────────────────────
async function sendDpa(id) {
  const c = clients.find(x=>x.id===id);
  if (!confirm(`Send a GDPR Data Processing Agreement to ${c.name} (${c.email}) for e-signature?`)) return;
  const d = await api('send_dpa', { id });
  if (d.success) {
    const idx = clients.findIndex(x=>x.id===id);
    clients[idx].dpa_status  = 'sent';
    renderAll();
    toast('DPA sent to ' + c.email);
  } else {
    alert('Error: ' + d.error);
  }
}

// ── Document Archive ─────────────────────────────
function renderArchive() {
  const tbody = document.getElementById('archiveTbody');
  const signed = clients.filter(c => c.status === 'signed');
  if (!signed.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><div class="empty-icon">📁</div><div class="empty-lbl">No signed documents yet. Documents will appear here after clients sign.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = signed
    .sort((a,b) => new Date(b.signed_at) - new Date(a.signed_at))
    .map(c => `<tr>
    <td><div class="c-name">${e(c.name)}</div><div class="c-info">${e(c.email)}${c.company?' · '+e(c.company):''}</div></td>
    <td style="font-size:12px">${renderServices(c.service)}</td>
    <td style="font-size:12px">${new Date(c.signed_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
    <td><span class="badge b-signed">${c.sig_method==='draw'?'✍️ Drawn':'Aa Typed'}</span></td>
    <td style="font-size:11px;color:var(--muted)">${e(c.signed_ip||'—')}</td>
    <td><div class="hash-short" title="${e(c.doc_hash||'')}">${c.doc_hash?c.doc_hash.substring(0,16)+'…':'—'}</div></td>
    <td><a class="btn btn-navy" href="api.php?action=download_pdf&id=${c.id}" target="_blank">⬇ PDF</a></td>
  </tr>`).join('');
}

</script>
</body>
</html>
