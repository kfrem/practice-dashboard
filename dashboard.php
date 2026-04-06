<?php require_once __DIR__ . '/config.php'; ?>
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
    <div class="logo">The <span>Practice</span></div>
    <div class="nav-links">
      <a href="index.html">Home</a>
      <a href="pricing.html">Pricing</a>
      <a href="roi.html">ROI Calculator</a>
      <a href="scorecard.html">Practice Scorecard</a>
      <a href="portal.html">Client Portal Demo</a>
      <a href="resources.html">Free Resources</a>
      <a href="pricing.html#partnership" class="partner">★ Partner Programme</a>
      <a href="dashboard.php" class="active">Client Dashboard</a>
    </div>
    <div class="nav-right">
      <button class="logout-btn" onclick="doLogout()">Log out</button>
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
      <div class="fg-row">
        <div class="fg"><label>Company / Trading Name</label><input type="text" id="addCompany" placeholder="Smith Ltd"></div>
        <div class="fg"><label>Entity Type</label>
          <select id="addType">
            <option>Ltd Company</option><option>Sole Trader</option><option>Partnership</option>
            <option>LLP</option><option>Individual</option><option>Charity</option><option>Trust</option>
          </select>
        </div>
      </div>
      <div class="fg-row">
        <div class="fg"><label>Service</label>
          <select id="addService">
            <option>Self Assessment Tax Return</option>
            <option>Company Accounts &amp; Corporation Tax</option>
            <option>VAT Returns</option><option>Bookkeeping</option><option>Payroll</option>
            <option>Making Tax Digital (MTD) Support</option>
            <option>Management Accounts</option><option>Full Accountancy Package</option>
          </select>
        </div>
        <div class="fg"><label>Phone</label><input type="tel" id="addPhone" placeholder="07700 900000"></div>
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
}

function switchTab(tab, el) {
  curTab = tab;
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  if (el) el.classList.add('active');
  const titles = {all:'All Clients', pending:'Awaiting Signature', signed:'Signed Clients', overdue:'⚠️ Overdue — Action Required', mtd:'📊 MTD Tracker', archive:'📁 Signed Document Archive'};
  // Hide all views
  ['viewClients','viewAml','viewMtd','viewArchive'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  if (tab === 'aml') {
    document.getElementById('viewAml').style.display = 'block';
  } else if (tab === 'mtd') {
    document.getElementById('viewMtd').style.display = 'block';
    renderMtd();
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
    <td style="font-size:12px">${e(c.service)}</td>
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
  a+=`<button class="btn btn-red" onclick="delClient('${c.id}')">✕</button>`;
  return a;
}
function e(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function openM(id)  { document.getElementById(id).classList.add('open'); }
function closeM(id) { document.getElementById(id).classList.remove('open'); }

// ── Add Client ──────────────────────────────────
function openAddClient() {
  ['addName','addEmail','addCompany','addPhone'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('addAlert').innerHTML='';
  openM('addModal');
}
async function addClient() {
  const name=document.getElementById('addName').value.trim();
  const email=document.getElementById('addEmail').value.trim();
  if (!name||!email) { document.getElementById('addAlert').innerHTML='<div class="alert a-err">Name and email are required.</div>'; return; }
  const d = await api('add_client',{name,email,
    company:document.getElementById('addCompany').value.trim(),
    type:document.getElementById('addType').value,
    service:document.getElementById('addService').value,
    phone:document.getElementById('addPhone').value.trim()
  });
  if (d.success) { clients.unshift(d.client); renderAll(); closeM('addModal'); toast('Client added'); }
  else document.getElementById('addAlert').innerHTML=`<div class="alert a-err">${d.error}</div>`;
}

// ── Send Link with letter editor ─────────────────
function buildDefaultLetter(c) {
  const today = new Date().toLocaleDateString('en-GB',{day:'numeric',month:'long',year:'numeric'});
  const feeLine = c.fee ? `\n\nFEES\nOur agreed fee for the above service is: ${c.fee}\n` : '\n\nFEES\nOur fees will be agreed and confirmed separately in writing.\n';
  return `CLIENT ENGAGEMENT LETTER

Date: ${today}

To:      ${c.name}
Company: ${c.company||'N/A'} (${c.type})

Dear ${c.name.split(' ')[0]},

Thank you for choosing The Practice. We are pleased to confirm our appointment as your accountants. This letter sets out the basis on which we will act for you.

SERVICES
We will provide the following services:
• ${c.service}
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
The Practice processes your personal data as Data Controller in accordance with UK GDPR and the Data Protection Act 2018. ICO Registration: ZC112776.

GOVERNING LAW
This engagement is governed by the laws of England and Wales.

Yours sincerely,

The Practice — A Finaccord Professional Services Programme
info@kafs-ltd.com | +44 7939 823988
practice.finaccord.pro`;
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
  openM('mtdModal');
}

async function saveMtd() {
  const d = await api('save_mtd', {
    id:             curMtdId,
    mtd_threshold:  document.getElementById('mtdThreshold').value,
    mtd_status:     document.getElementById('mtdStatus').value,
    mtd_software:   document.getElementById('mtdSoftware').value,
    mtd_enrol_date: document.getElementById('mtdEnrolDate').value,
    mtd_next_sub:   document.getElementById('mtdNextSubmission').value,
    mtd_notes:      document.getElementById('mtdNotes').value.trim()
  });
  if (d.success) {
    const idx = clients.findIndex(c=>c.id===curMtdId);
    Object.assign(clients[idx], {
      mtd_threshold:  document.getElementById('mtdThreshold').value,
      mtd_status:     document.getElementById('mtdStatus').value,
      mtd_software:   document.getElementById('mtdSoftware').value,
      mtd_enrol_date: document.getElementById('mtdEnrolDate').value,
      mtd_next_sub:   document.getElementById('mtdNextSubmission').value,
      mtd_notes:      document.getElementById('mtdNotes').value
    });
    renderAll();
    closeM('mtdModal');
    toast('MTD record saved');
  } else {
    document.getElementById('mtdAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
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
    <td style="font-size:12px">${e(c.service)}</td>
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
