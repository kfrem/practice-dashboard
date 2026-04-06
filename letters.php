<?php require_once __DIR__ . '/config.php';
session_start();
if (empty($_SESSION['fr_auth'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Correspondence — The Practice</title>
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

nav{border-bottom:1px solid var(--border);padding:14px 28px;background:#fff;position:sticky;top:0;z-index:100;display:flex;align-items:center;justify-content:space-between}
.logo{font-size:20px;font-weight:800;color:var(--navy)}.logo span{color:var(--gold)}
.nav-links{display:flex;gap:22px;font-size:13px}
.nav-links a{color:#374151;transition:color .2s}.nav-links a:hover{color:var(--navy)}
.nav-links a.active{color:var(--navy);font-weight:700;border-bottom:2px solid var(--gold);padding-bottom:2px}
.nav-links a.partner{color:var(--gold);font-weight:600}
.nav-right{display:flex;align-items:center;gap:10px}
.logout-btn{font-size:12px;color:var(--muted);cursor:pointer;background:none;border:1px solid var(--border);border-radius:4px;padding:6px 12px;font-family:'Segoe UI',Georgia,sans-serif}
.logout-btn:hover{border-color:var(--danger);color:var(--danger)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;font-weight:700;cursor:pointer;border:none;transition:all .2s}
.btn-navy{background:var(--navy);color:#fff}.btn-navy:hover{background:var(--navy-dark)}
.btn-gold{background:var(--gold);color:var(--navy-dark)}.btn-gold:hover{background:var(--gold-dark)}
.btn-outline{background:#fff;color:var(--navy);border:1.5px solid var(--border)}.btn-outline:hover{border-color:var(--navy)}
.btn-sm{padding:6px 12px;font-size:12px}
.btn-danger{background:var(--danger);color:#fff}.btn-danger:hover{opacity:.85}

.main{max-width:1180px;margin:0 auto;padding:32px 28px 80px}
.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.page-title{font-size:26px;font-weight:800;color:var(--navy-dark);letter-spacing:-.3px}
.page-sub{font-size:13px;color:var(--muted);margin-top:2px}
.page-actions{display:flex;gap:10px}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:28px}
.stat{background:#fff;border-radius:6px;border:1px solid var(--border);border-top:3px solid var(--border);padding:18px 20px}
.stat.s-sent{border-top-color:var(--navy)}.stat.s-read{border-top-color:var(--success)}.stat.s-ack{border-top-color:var(--gold)}.stat.s-unread{border-top-color:var(--warning)}.stat.s-total{border-top-color:var(--info)}
.stat-n{font-size:32px;font-weight:800;color:var(--navy-dark);font-family:Georgia,serif}
.stat-l{font-size:11px;color:var(--muted);margin-top:2px}

/* TABS */
.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:24px}
.tab{padding:10px 22px;font-size:14px;font-weight:600;cursor:pointer;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s}
.tab:hover{color:var(--navy)}.tab.active{color:var(--navy);border-bottom-color:var(--gold)}

/* TEMPLATE GRID */
.tmpl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.tmpl-card{background:#fff;border:1px solid var(--border);border-radius:8px;padding:20px;cursor:pointer;transition:all .2s;position:relative}
.tmpl-card:hover{border-color:var(--navy);box-shadow:0 4px 16px rgba(26,53,88,.1);transform:translateY(-2px)}
.tmpl-card.custom-tmpl{border-left:3px solid var(--gold)}
.tmpl-cat{font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.tmpl-name{font-size:14px;font-weight:700;color:var(--navy-dark);margin-bottom:6px;line-height:1.4}
.tmpl-desc{font-size:12px;color:var(--muted);line-height:1.5}
.tmpl-actions{position:absolute;top:10px;right:10px;display:flex;gap:4px;opacity:0;transition:opacity .2s}
.tmpl-card:hover .tmpl-actions{opacity:1}
.tmpl-del{background:none;border:none;color:var(--danger);cursor:pointer;font-size:15px;padding:2px 4px}
.cat-section{margin-bottom:32px}
.cat-heading{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

/* LETTERS TABLE */
.table-wrap{background:#fff;border-radius:6px;border:1px solid var(--border);overflow:hidden}
.table-top{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);gap:12px;flex-wrap:wrap}
.search-box{border:1px solid var(--border);border-radius:4px;padding:8px 12px;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;outline:none;background:#fafaf8;min-width:200px}
.search-box:focus{border-color:var(--navy)}
table{width:100%;border-collapse:collapse}
th{background:var(--cream);color:var(--muted);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:10px 16px;text-align:left;border-bottom:1px solid var(--border)}
td{padding:12px 16px;font-size:13px;border-bottom:1px solid #f0ece6;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafaf7}
.badge{display:inline-block;border-radius:3px;padding:2px 8px;font-size:11px;font-weight:700}
.b-sent{background:#dbeafe;color:#1e40af}
.b-read{background:#d1fae5;color:#065f46}
.b-ack{background:#fef3c7;color:#92400e}
.b-draft{background:#f3f4f6;color:#374151}
.b-cat{background:rgba(201,168,76,.12);color:var(--gold-dark);border:1px solid rgba(201,168,76,.25)}
.empty-state{text-align:center;padding:52px 24px;color:var(--muted)}
.empty-icon{font-size:40px;margin-bottom:12px}
.empty-title{font-size:16px;font-weight:700;color:#374151;margin-bottom:6px}
.empty-sub{font-size:13px;line-height:1.6}

/* MODALS */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:16px}
.overlay.open{display:flex}
.modal{background:#fff;border-radius:8px;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:mIn .25s ease}
.modal-wide{max-width:800px!important}
.modal-xl{max-width:1000px!important}
@keyframes mIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.m-head{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:#fff;z-index:10}
.m-title{font-size:16px;font-weight:800;color:var(--navy-dark)}
.m-close{background:none;border:none;font-size:22px;color:var(--muted);cursor:pointer;line-height:1}
.m-body{padding:20px 24px}
.m-foot{padding:14px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;position:sticky;bottom:0;background:#fff}
.fg{margin-bottom:16px}
.fg label{display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.7px;margin-bottom:5px}
.fg input,.fg select,.fg textarea{width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:4px;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;color:var(--text);background:var(--cream);outline:none;transition:border-color .2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--navy);background:#fff}
.fg textarea{min-height:80px;resize:vertical}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.letter-editor{min-height:320px;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;line-height:1.7;background:#fafaf8;border:1px solid var(--border);border-radius:4px;padding:14px;resize:vertical;width:100%;outline:none;color:var(--text);transition:border-color .2s}
.letter-editor:focus{border-color:var(--navy);background:#fff}
.alert{padding:12px 14px;border-radius:5px;font-size:13px;margin-bottom:14px}
.a-ok{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
.a-err{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
.step-indicator{display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--border)}
.step-pill{padding:8px 20px;font-size:13px;font-weight:600;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px}
.step-pill.active{color:var(--navy);border-bottom-color:var(--gold)}
.step-pill.done{color:var(--success)}
.placeholder-hint{background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:10px 12px;font-size:12px;color:#92400e;margin-bottom:14px;line-height:1.6}

/* LETTERHEAD DESIGNER */
.lh-preview{border:1px solid var(--border);border-radius:6px;overflow:hidden;margin-bottom:16px}
.lh-preview-header{padding:20px;display:flex;align-items:flex-start;justify-content:space-between}
.lh-logo-area{max-width:160px;max-height:60px;object-fit:contain}
.lh-preview-body{padding:16px 20px;border-top:1px solid var(--border);font-size:13px;color:var(--muted);background:#fafaf8}
.lh-preview-footer{padding:10px 20px;font-size:11px;color:#94a3b8;border-top:2px solid var(--navy)}
.colour-row{display:flex;gap:12px;align-items:center}
.colour-row input[type=color]{width:44px;height:36px;border:1px solid var(--border);border-radius:4px;cursor:pointer;padding:2px}
.colour-label{font-size:12px;color:var(--text)}

/* TOAST */
.toast{position:fixed;bottom:24px;right:24px;background:var(--navy-dark);color:#fff;padding:12px 20px;border-radius:6px;font-size:13px;font-weight:600;z-index:9999;animation:tIn .3s ease;display:none}
@keyframes tIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

/* VIEW PANEL */
.letter-view-panel{background:#fafaf8;border:1px solid var(--border);border-radius:6px;padding:20px;font-size:13px;line-height:1.8;white-space:pre-wrap;max-height:400px;overflow-y:auto;font-family:'Segoe UI',Georgia,sans-serif;color:var(--text)}

@media(max-width:900px){.stats{grid-template-columns:repeat(3,1fr)}.nav-links{display:none}}
@media(max-width:600px){.stats{grid-template-columns:1fr 1fr}.fg-row{grid-template-columns:1fr}}
</style>
</head>
<body>

<nav>
  <div class="logo">The <span>Practice</span></div>
  <div class="nav-links">
    <a href="dashboard.php">Client Dashboard</a>
    <a href="letters.php" class="active">✉ Correspondence</a>
    <a href="subscribe.php">Billing</a>
  </div>
  <div class="nav-right">
    <button class="btn btn-outline btn-sm" onclick="openLetterhead()">⚙ Letterhead</button>
    <button class="logout-btn" onclick="location.href='api.php?action=logout'">Log out</button>
  </div>
</nav>

<!-- COMPOSE MODAL -->
<div class="overlay" id="composeModal">
  <div class="modal modal-xl">
    <div class="m-head">
      <div class="m-title">✉ New Correspondence</div>
      <button class="m-close" onclick="closeM('composeModal')">×</button>
    </div>
    <div class="m-body">
      <div id="composeAlert"></div>

      <!-- STEP 1: TEMPLATE -->
      <div id="cStep1">
        <p style="font-size:13px;color:var(--muted);margin-bottom:18px">Choose a template to start from, or begin with a blank letter.</p>
        <div id="modalTemplateGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;max-height:420px;overflow-y:auto;padding-right:4px"></div>
      </div>

      <!-- STEP 2: RECIPIENT + EDIT -->
      <div id="cStep2" style="display:none">
        <div class="placeholder-hint">
          💡 Placeholders auto-fill when you select a client: <code>{{client_name}}</code>, <code>{{client_first_name}}</code>, <code>{{client_company}}</code>, <code>{{firm_name}}</code>, <code>{{date}}</code>
        </div>
        <div class="fg-row">
          <div class="fg">
            <label>Recipient Name *</label>
            <input type="text" id="cRecipName" placeholder="Jane Smith">
          </div>
          <div class="fg">
            <label>Recipient Email *</label>
            <input type="email" id="cRecipEmail" placeholder="jane@smithco.co.uk">
          </div>
        </div>
        <div class="fg-row">
          <div class="fg">
            <label>Recipient Type</label>
            <select id="cRecipType">
              <option value="client">Client</option>
              <option value="accountant">Fellow Accountant</option>
              <option value="bank">Bank / Lender</option>
              <option value="hmrc">HMRC</option>
              <option value="solicitor">Solicitor</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="fg">
            <label>Link to Existing Client (optional)</label>
            <select id="cLinkedClient" onchange="prefillFromClient(this.value)">
              <option value="">— Not linked to a client —</option>
            </select>
          </div>
        </div>
        <div class="fg">
          <label>Subject Line *</label>
          <input type="text" id="cSubject" placeholder="Subject of this letter">
        </div>
        <div class="fg">
          <label>Letter Body — edit as needed</label>
          <textarea class="letter-editor" id="cBody"></textarea>
        </div>
        <div class="fg-row">
          <div class="fg">
            <label>Category</label>
            <select id="cCategory">
              <option>Onboarding</option>
              <option>Business Development</option>
              <option>Compliance</option>
              <option>Fee Management</option>
              <option>General</option>
              <option>Third Party</option>
              <option>Custom</option>
            </select>
          </div>
          <div class="fg" style="display:flex;align-items:flex-end;padding-bottom:4px">
            <label style="display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;font-size:13px;font-weight:600;cursor:pointer">
              <input type="checkbox" id="cRequireAck" style="width:16px;height:16px;accent-color:var(--navy)">
              Require acknowledgement from recipient
            </label>
          </div>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px;display:flex;gap:10px;flex-wrap:wrap">
          <button type="button" class="btn btn-outline btn-sm" onclick="saveAsTemplate()">💾 Save as Custom Template</button>
        </div>
      </div>

      <!-- STEP 3: CONFIRM -->
      <div id="cStep3" style="display:none">
        <div class="alert a-ok" style="font-size:14px;padding:14px">✓ Letter sent successfully.</div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:12px">Sent to: <strong id="cConfirmRecip"></strong></p>
        <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Recipient link:</p>
        <div style="background:var(--cream);border:1px solid var(--border);border-radius:4px;padding:10px 14px;font-size:12px;font-family:monospace;word-break:break-all;cursor:pointer" id="cLinkBox" onclick="copyLetterLink()">
          <span id="cLink"></span>
        </div>
        <p style="font-size:11px;color:var(--muted);margin-top:8px">Click the link above to copy it.</p>
      </div>
    </div>
    <div class="m-foot" id="cFoot">
      <button class="btn btn-outline" id="cBackBtn" onclick="cBack()" style="display:none">← Back</button>
      <button class="btn btn-outline" onclick="closeM('composeModal')">Cancel</button>
      <button class="btn btn-navy" id="cNextBtn" onclick="cNext()">Choose Template →</button>
    </div>
  </div>
</div>

<!-- LETTERHEAD MODAL -->
<div class="overlay" id="letterheadModal">
  <div class="modal modal-wide">
    <div class="m-head">
      <div class="m-title">⚙ Letterhead &amp; Brand Settings</div>
      <button class="m-close" onclick="closeM('letterheadModal')">×</button>
    </div>
    <div class="m-body">
      <div id="lhAlert"></div>
      <p style="font-size:13px;color:var(--muted);margin-bottom:18px">Your letterhead appears on every letter recipients open. Upload your logo and set your brand colours to make every communication look professional.</p>

      <!-- LIVE PREVIEW -->
      <div class="lh-preview" id="lhPreview">
        <div class="lh-preview-header" id="lhPreviewHeader" style="background:#1a3558">
          <div>
            <img id="lhPreviewLogo" src="" alt="" style="max-height:54px;max-width:160px;display:none;margin-bottom:6px">
            <div id="lhPreviewName" style="font-size:18px;font-weight:800;color:#c9a84c"><?= htmlspecialchars(FR_FIRM_NAME) ?></div>
            <div id="lhPreviewTagline" style="font-size:12px;color:#94a3b8;margin-top:3px"></div>
          </div>
          <div style="text-align:right;font-size:11px;color:#94a3b8;line-height:1.7">
            <div><?= htmlspecialchars(FR_FIRM_ADDRESS) ?></div>
            <div><?= htmlspecialchars(FR_FIRM_PHONE) ?></div>
            <div><?= htmlspecialchars(FR_FIRM_EMAIL) ?></div>
            <div><?= htmlspecialchars(FR_FIRM_WEBSITE) ?></div>
          </div>
        </div>
        <div class="lh-preview-body">Letter content appears here…</div>
        <div class="lh-preview-footer" id="lhPreviewFooter">ICO Registration <?= htmlspecialchars(FR_ICO_NUMBER) ?></div>
      </div>

      <div class="fg">
        <label>Firm Logo (PNG or JPG recommended)</label>
        <input type="file" id="lhLogoFile" accept="image/*" onchange="loadLhLogo(this)" style="font-size:13px">
        <p style="font-size:11px;color:var(--muted);margin-top:4px">Max recommended size: 400×120px. Will appear top-left of every letter.</p>
      </div>
      <div class="fg">
        <label>Tagline / Strapline</label>
        <input type="text" id="lhTagline" placeholder="e.g. Professional Accountancy Services" oninput="updateLhPreview()">
      </div>
      <div class="fg">
        <label>Brand Colours</label>
        <div class="colour-row">
          <input type="color" id="lhBrandColour" value="#1a3558" oninput="updateLhPreview()">
          <span class="colour-label">Primary (header background)</span>
          <input type="color" id="lhAccentColour" value="#c9a84c" oninput="updateLhPreview()">
          <span class="colour-label">Accent (firm name, buttons)</span>
        </div>
      </div>
      <div class="fg">
        <label>Footer Text</label>
        <input type="text" id="lhFooterText" placeholder="e.g. Registered in England | ICO Registration ZC112776" oninput="updateLhPreview()">
      </div>
      <div class="fg">
        <label>Disclaimer (appears at bottom of every letter)</label>
        <textarea id="lhDisclaimer" rows="2" placeholder="e.g. This communication is confidential..."></textarea>
      </div>
      <div class="fg-row">
        <div class="fg">
          <label>LinkedIn URL</label>
          <input type="text" id="lhLinkedin" placeholder="https://linkedin.com/company/...">
        </div>
        <div class="fg">
          <label>Twitter / X Handle</label>
          <input type="text" id="lhTwitter" placeholder="@YourFirm">
        </div>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('letterheadModal')">Cancel</button>
      <button class="btn btn-navy" onclick="saveLetterhead()">Save Letterhead</button>
    </div>
  </div>
</div>

<!-- VIEW LETTER MODAL -->
<div class="overlay" id="viewModal">
  <div class="modal modal-wide">
    <div class="m-head">
      <div class="m-title" id="viewModalTitle">Letter Details</div>
      <button class="m-close" onclick="closeM('viewModal')">×</button>
    </div>
    <div class="m-body">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px" id="viewMeta"></div>
      <div class="fg"><label>Letter Content</label><div class="letter-view-panel" id="viewBody"></div></div>
    </div>
    <div class="m-foot">
      <button class="btn btn-outline" onclick="closeM('viewModal')">Close</button>
      <button class="btn btn-danger btn-sm" id="viewDeleteBtn" onclick="deleteCurrentLetter()">Delete</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<!-- MAIN -->
<div class="main">
  <div class="page-head">
    <div>
      <div class="page-title">✉ Correspondence</div>
      <div class="page-sub">Send professional letters, track delivery and monitor engagement</div>
    </div>
    <div class="page-actions">
      <button class="btn btn-navy" onclick="openCompose()">+ New Letter</button>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat s-total"><div class="stat-n" id="stTotal">0</div><div class="stat-l">Total sent</div></div>
    <div class="stat s-sent"><div class="stat-n" id="stSentToday">0</div><div class="stat-l">Sent today</div></div>
    <div class="stat s-unread"><div class="stat-n" id="stUnread">0</div><div class="stat-l">Not yet read</div></div>
    <div class="stat s-read"><div class="stat-n" id="stRead">0</div><div class="stat-l">Read</div></div>
    <div class="stat s-ack"><div class="stat-n" id="stAck">0</div><div class="stat-l">Acknowledged</div></div>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <div class="tab active" onclick="switchTab('templates',this)">📋 Templates</div>
    <div class="tab" onclick="switchTab('sent',this)">📬 Sent Letters</div>
  </div>

  <!-- TEMPLATES TAB -->
  <div id="tabTemplates">
    <div id="templateContent"></div>
  </div>

  <!-- SENT TAB -->
  <div id="tabSent" style="display:none">
    <div class="table-wrap">
      <div class="table-top">
        <span style="font-size:14px;font-weight:700;color:var(--navy-dark)">All Sent Correspondence</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <input class="search-box" type="text" placeholder="Search subject or recipient…" oninput="renderLetters(this.value)">
          <select class="search-box" id="filterStatus" onchange="renderLetters()" style="min-width:140px">
            <option value="">All statuses</option>
            <option value="sent">Sent (unread)</option>
            <option value="read">Read</option>
            <option value="acknowledged">Acknowledged</option>
          </select>
          <select class="search-box" id="filterCat" onchange="renderLetters()" style="min-width:160px">
            <option value="">All categories</option>
            <option>Onboarding</option>
            <option>Business Development</option>
            <option>Compliance</option>
            <option>Fee Management</option>
            <option>General</option>
            <option>Third Party</option>
            <option>Custom</option>
          </select>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Recipient</th><th>Subject</th><th>Category</th><th>Status</th><th>Sent</th><th>Read</th><th>Actions</th></tr>
        </thead>
        <tbody id="lettersBody"></tbody>
      </table>
    </div>
  </div>
</div>

<footer style="border-top:1px solid var(--border);background:#fff;padding:18px 28px;text-align:center;font-size:12px;color:var(--muted)">
  <strong><?= htmlspecialchars(FR_FIRM_NAME) ?></strong> &nbsp;·&nbsp; <?= htmlspecialchars(FR_FIRM_ADDRESS) ?> &nbsp;·&nbsp; ICO <?= htmlspecialchars(FR_ICO_NUMBER) ?>
</footer>

<script>
const FIRM_NAME    = <?= json_encode(FR_FIRM_NAME) ?>;
const FIRM_ADDR    = <?= json_encode(FR_FIRM_ADDRESS) ?>;
const FIRM_EMAIL   = <?= json_encode(FR_FIRM_EMAIL) ?>;
const FIRM_PHONE   = <?= json_encode(FR_FIRM_PHONE) ?>;
const FIRM_WEBSITE = <?= json_encode(FR_FIRM_WEBSITE) ?>;
const FIRM_ICO     = <?= json_encode(FR_ICO_NUMBER) ?>;
const TODAY = new Date().toLocaleDateString('en-GB',{day:'numeric',month:'long',year:'numeric'});

let letters = [], clients = [], customTemplates = [], curViewId = '', selectedTemplateId = '', lhLogoData = '';

// ── SYSTEM TEMPLATES ────────────────────────────────────────
const SYSTEM_TEMPLATES = [
  // ONBOARDING
  { id:'tmpl_welcome', cat:'Onboarding', name:'Welcome to the Firm',
    desc:'Warm welcome letter for a new client joining your practice.',
    subject:'Welcome to {{firm_name}}',
    body:`Dear {{client_first_name}},

On behalf of everyone at {{firm_name}}, I would like to extend a very warm welcome. We are delighted to have you on board and look forward to building a long and productive working relationship with you.

We are committed to providing you with a professional, responsive and personalised accountancy service. Over the coming days, a member of our team will be in touch to complete your onboarding, including verification of your identity for Anti-Money Laundering purposes.

In the meantime, if you have any questions at all, please do not hesitate to contact us. We are always happy to help.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}
${FIRM_WEBSITE}` },

  { id:'tmpl_onboard_info', cat:'Onboarding', name:'New Client Information Request',
    desc:'Ask a new client to supply key documents and information.',
    subject:'Information required to complete your onboarding — {{firm_name}}',
    body:`Dear {{client_first_name}},

Thank you for joining {{firm_name}}. To complete your onboarding and ensure we can act on your behalf, we require the following information and documents at your earliest convenience:

1. Proof of Identity — A copy of your current passport or driving licence
2. Proof of Address — A utility bill or bank statement dated within the last 3 months
3. Previous Accountant — Contact details if applicable (we will handle the professional clearance)
4. Relevant Financial Records — As discussed, please forward details of recent financial activity

You can email these to ${FIRM_EMAIL} or drop them into our office.

We look forward to getting everything set up for you.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  // BUSINESS DEVELOPMENT
  { id:'tmpl_upsell_payroll', cat:'Business Development', name:'Payroll Services — Could We Help?',
    desc:'Invite a client to consider adding payroll to their services.',
    subject:'Have you considered letting us manage your payroll?',
    body:`Dear {{client_first_name}},

We hope this letter finds you well.

As your accountants, we are always looking for ways to make running your business easier. We wanted to highlight our payroll services, which may be of benefit to you.

Managing payroll in-house can be time-consuming and the compliance requirements — including Real Time Information (RTI) submissions to HMRC, National Minimum Wage compliance, and auto-enrolment pension obligations — are increasingly complex.

Our payroll service includes:
• Monthly or weekly payroll processing
• PAYE and National Insurance calculations
• RTI submissions to HMRC
• Payslips for all employees
• P60 and P45 preparation
• Auto-enrolment administration

We would be happy to provide a tailored quote based on your employee numbers. Please reply to this letter or call us on ${FIRM_PHONE} to discuss further.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_upsell_vat', cat:'Business Development', name:'VAT Returns — Let Us Handle It',
    desc:'Promote VAT return services to a client.',
    subject:'Let us take care of your VAT returns',
    body:`Dear {{client_first_name}},

We hope you are well.

As your accountants, we would like to bring to your attention the VAT return service we provide to many of our clients.

With the introduction of Making Tax Digital for VAT, returns must now be submitted digitally using compatible software. Our service ensures your VAT is calculated accurately, submitted on time, and fully compliant with HMRC requirements.

Our VAT service includes:
• Quarterly VAT return preparation and submission
• MTD-compliant digital filing
• VAT scheme reviews (Flat Rate, Cash Accounting, Standard)
• VAT registration and deregistration if required

If you are not currently using our VAT service and would like to, or if you would like a review of your current VAT position, please get in touch.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_upsell_bookkeeping', cat:'Business Development', name:'Bookkeeping Services — Save Your Time',
    desc:'Promote bookkeeping services to save a client time.',
    subject:'Let us handle your bookkeeping — free up your time',
    body:`Dear {{client_first_name}},

Running a business is demanding enough without spending evenings catching up on your books.

Our bookkeeping service means you can focus on what you do best — running your business — while we take care of the numbers.

What we offer:
• Monthly or quarterly bookkeeping
• Bank reconciliation
• Purchase and sales ledger management
• Expense recording and categorisation
• Management accounts and profit & loss reporting
• Cloud accounting software setup (Xero, QuickBooks, Sage)

Well-maintained books also mean your year-end accounts are faster and less costly to prepare.

If you would like to discuss this further, we would love to hear from you.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_annual_review', cat:'Business Development', name:'Annual Review & Fee Discussion',
    desc:'Invite a client to their annual review meeting.',
    subject:'Your annual review — let us meet to discuss the year ahead',
    body:`Dear {{client_first_name}},

As we approach the end of another financial year, I would like to invite you to your annual review meeting with {{firm_name}}.

This is an opportunity for us to:
• Review the past year's financial performance
• Discuss your plans and objectives for the year ahead
• Review your current service package and ensure it still meets your needs
• Consider any tax planning opportunities
• Address any questions you may have

Please reply to this letter or call us on ${FIRM_PHONE} to arrange a convenient time. We offer appointments at our office, by phone, or by video call — whichever suits you best.

We look forward to hearing from you.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_fee_proposal', cat:'Business Development', name:'Fee Proposal for New Services',
    desc:'A structured fee proposal for new or additional services.',
    subject:'Fee proposal — {{firm_name}}',
    body:`Dear {{client_first_name}},

Thank you for discussing your requirements with us. I am pleased to set out our proposed fees for the services we discussed:

PROPOSED SERVICES & FEES
[Please add the services and fees agreed during your discussion]

PAYMENT TERMS
Our fees are payable within 30 days of invoice. We can also arrange monthly direct debit instalments if you prefer — please let us know.

WHAT HAPPENS NEXT
If you are happy to proceed, please reply to this letter confirming your agreement, and we will send you a formal engagement letter to sign.

If you have any questions about this proposal, please do not hesitate to contact us.

We look forward to working with you.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  // COMPLIANCE
  { id:'tmpl_mtd', cat:'Compliance', name:'Making Tax Digital — Are You Ready?',
    desc:'Inform and advise a client about their MTD obligations.',
    subject:'Making Tax Digital — important update for your business',
    body:`Dear {{client_first_name}},

Making Tax Digital (MTD) is HMRC's programme to modernise the UK tax system. We want to ensure you are fully aware of how this affects you and that you are prepared.

YOUR MTD POSITION
[Please update with the client's specific MTD timeline and obligations]

WHAT YOU NEED TO DO
• Keep digital records using MTD-compatible software
• Submit your tax information quarterly via compatible software
• Ensure your accounting software is up to date

HOW WE CAN HELP
As your accountants, we can assist with:
• Reviewing and setting up your accounting software
• Training you or your staff on digital record-keeping
• Making quarterly submissions on your behalf

Please contact us to discuss your specific situation and ensure you are ready well ahead of the deadline.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_sa_deadline', cat:'Compliance', name:'Self Assessment Deadline Reminder',
    desc:'Remind a client about the 31 January self assessment deadline.',
    subject:'Self Assessment deadline — action required',
    body:`Dear {{client_first_name}},

This is a reminder that the deadline for submitting your Self Assessment tax return and paying any outstanding tax is 31 January.

To allow us sufficient time to prepare your return, we require all relevant information from you as soon as possible.

INFORMATION WE NEED FROM YOU
• P60 and any other employment income documents
• Details of any rental income and expenses
• Dividend certificates or investment income
• Any other sources of income (freelance, foreign income, etc.)
• Receipts for any business expenses you wish to claim
• Details of any Gift Aid donations

Please send us your records at your earliest convenience. If we do not receive them in time, we cannot guarantee that your return will be filed before the deadline, and late filing penalties start at £100.

Please contact us immediately if you have any concerns.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_renewal', cat:'Compliance', name:'Annual Engagement Renewal',
    desc:'Renew the engagement letter for an existing client each year.',
    subject:'Annual engagement renewal — {{firm_name}}',
    body:`Dear {{client_first_name}},

As we begin a new financial year, we would like to confirm the continuation of our engagement and take this opportunity to review the terms under which we act for you.

Our services, fees, and terms remain as set out in your original engagement letter, subject to any changes noted below:

CHANGES FOR THIS YEAR
• [Note any fee increases, service changes, or updated terms here]
• [Or write "No changes — terms remain as previously agreed"]

We take our professional obligations seriously and are committed to continuing to provide you with an excellent service.

Please reply to confirm your agreement to continue on these terms. If you have any questions or wish to discuss your service package, we would be delighted to hear from you.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  // FEE MANAGEMENT
  { id:'tmpl_overdue1', cat:'Fee Management', name:'Invoice Overdue — Gentle Reminder',
    desc:'A polite first reminder about an outstanding invoice.',
    subject:'Gentle reminder — outstanding invoice',
    body:`Dear {{client_first_name}},

I hope this letter finds you well.

We wanted to send a gentle reminder that we have an invoice outstanding on your account. Details are as follows:

Invoice Reference: [Invoice number]
Invoice Date: [Invoice date]
Amount Due: [Amount]
Payment Due: [Due date]

If you have already arranged payment, please disregard this letter. If not, we would be grateful if you could arrange payment at your earliest convenience.

Payment can be made by bank transfer to the details on your invoice. If you have any queries about this invoice or are experiencing any difficulties, please do not hesitate to contact us — we are always happy to discuss.

Thank you for your continued custom. We value your business greatly.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_overdue2', cat:'Fee Management', name:'Invoice Overdue — Firm Notice',
    desc:'A firmer second reminder for a significantly overdue invoice.',
    subject:'Outstanding invoice — payment required',
    body:`Dear {{client_first_name}},

We refer to our previous communication regarding the outstanding invoice on your account.

As at the date of this letter, the following remains unpaid:

Invoice Reference: [Invoice number]
Amount Due: [Amount]
Now Overdue By: [Number of days]

We would be grateful for your immediate attention to this matter. Please arrange payment within 7 days of the date of this letter.

If there is a query regarding this invoice, or if you are experiencing financial difficulties, please contact us as a matter of urgency so that we can discuss a resolution.

Under the Late Payment of Commercial Debts Act 1998, we reserve the right to charge interest on all overdue amounts at 8% above the Bank of England base rate, together with fixed debt recovery costs.

We look forward to your prompt response.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_overdue_final', cat:'Fee Management', name:'Invoice Overdue — Final Notice',
    desc:'Final notice before formal debt recovery proceedings.',
    subject:'FINAL NOTICE — Outstanding invoice',
    body:`Dear {{client_first_name}},

FINAL NOTICE BEFORE LEGAL ACTION

We refer to our previous correspondence regarding the outstanding amount on your account, which remains unpaid despite our reminders.

Amount Outstanding: [Amount]
Original Due Date: [Date]
Account Reference: [Reference]

Unless payment is received in full within 14 days of the date of this letter, we will have no option but to refer this matter to our debt recovery service, which will result in additional costs being added to the amount owed.

We strongly urge you to contact us immediately on ${FIRM_PHONE} or at ${FIRM_EMAIL} to resolve this matter and avoid further action.

Please note that this letter constitutes formal notice in accordance with the Late Payment of Commercial Debts Act 1998.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  // GENERAL
  { id:'tmpl_referral_thanks', cat:'General', name:'Thank You for Your Referral',
    desc:'Thank a client or contact who referred a new client to you.',
    subject:'Thank you for your referral',
    body:`Dear {{client_first_name}},

I am writing to express our sincere gratitude for referring [Referred person/company] to {{firm_name}}.

Word-of-mouth recommendations from valued clients like you are the greatest compliment we can receive. We will ensure that your contact receives the same high standard of service that we always strive to provide.

As a small token of our appreciation, [mention any referral reward or gift if applicable — or remove this line].

Thank you once again. If there is ever anything we can do for you, please do not hesitate to ask.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_newsletter', cat:'General', name:'Industry Update & Bulletin',
    desc:'Share an industry update, regulatory news or firm news.',
    subject:'Important updates from {{firm_name}}',
    body:`Dear {{client_first_name}},

We hope this letter finds you and your business in good health.

We wanted to take a moment to share some important updates and news from {{firm_name}} and the wider accountancy landscape.

REGULATORY UPDATES
[Add any relevant HMRC announcements, Budget changes, MTD updates, NMW changes, etc.]

IMPORTANT DATES TO REMEMBER
[Add any upcoming key tax or filing deadlines relevant to your clients]

FIRM NEWS
[Add any firm news — new services, new team members, office updates, awards, etc.]

As always, if you have any questions or concerns about how any of these changes might affect you or your business, please do not hesitate to contact us. We are here to help.

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },

  { id:'tmpl_third_party', cat:'Third Party', name:'Third Party Introduction / Reference',
    desc:'Introduce a client to a bank, lender, solicitor or other party.',
    subject:'Introduction and reference — {{client_name}}',
    body:`Dear Sir or Madam,

RE: {{client_name}} — {{client_company}}

We act as accountants to the above-named client and write to introduce them in connection with [state purpose — e.g. mortgage application, business loan, tenancy reference].

We can confirm the following:
• We have acted for {{client_name}} since [date]
• Our client is [employed as / self-employed as / the director of] [company/role]
• Based on the records provided to us, [relevant financial statement — e.g. annual income / turnover is approximately £X]

This letter is provided in good faith based on information supplied to us by our client and should not be relied upon as an audit or verification of financial information.

If you require any further information, please do not hesitate to contact us.

Yours faithfully,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}
${FIRM_WEBSITE}` },

  { id:'tmpl_general', cat:'General', name:'General Correspondence (Blank)',
    desc:'Start from a blank letter with your firm letterhead.',
    subject:'Correspondence from {{firm_name}}',
    body:`Dear {{client_first_name}},

[Write your letter here]

Yours sincerely,

${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}` },
];

// ── UTILITIES ────────────────────────────────────────────────
function e(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
function toast(msg,dur=3000) {
  const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block';
  setTimeout(()=>t.style.display='none',dur);
}
function openM(id) { document.getElementById(id).classList.add('open'); }
function closeM(id) { document.getElementById(id).classList.remove('open'); }
async function api(action, data={}) {
  const r = await fetch('api.php?action='+action, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) });
  return r.json();
}

// ── TABS ─────────────────────────────────────────────────────
function switchTab(tab, el) {
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('tabTemplates').style.display = tab==='templates'?'block':'none';
  document.getElementById('tabSent').style.display = tab==='sent'?'block':'none';
  if (tab==='sent') renderLetters();
}

// ── STATS ────────────────────────────────────────────────────
function renderStats() {
  const today = new Date().toDateString();
  document.getElementById('stTotal').textContent    = letters.length;
  document.getElementById('stSentToday').textContent = letters.filter(l => new Date(l.sent_at).toDateString()===today).length;
  document.getElementById('stUnread').textContent   = letters.filter(l => l.status==='sent').length;
  document.getElementById('stRead').textContent     = letters.filter(l => l.status==='read').length;
  document.getElementById('stAck').textContent      = letters.filter(l => l.status==='acknowledged').length;
}

// ── TEMPLATE RENDERING ───────────────────────────────────────
function renderTemplates() {
  const allTemplates = [...SYSTEM_TEMPLATES, ...customTemplates.map(t=>({...t,cat:t.category,isCustom:true}))];
  const cats = [...new Set(allTemplates.map(t=>t.cat))];
  const container = document.getElementById('templateContent');
  container.innerHTML = cats.map(cat => {
    const tmpls = allTemplates.filter(t=>t.cat===cat);
    return `<div class="cat-section">
      <div class="cat-heading">${e(cat)}</div>
      <div class="tmpl-grid">
        ${tmpls.map(t=>`<div class="tmpl-card${t.isCustom?' custom-tmpl':''}" onclick="selectTemplate('${e(t.id)}')">
          <div class="tmpl-cat">${e(t.cat)}</div>
          <div class="tmpl-name">${e(t.name)}</div>
          <div class="tmpl-desc">${e(t.desc||t.subject||'')}</div>
          ${t.isCustom?`<div class="tmpl-actions"><button class="tmpl-del" onclick="event.stopPropagation();deleteCustomTemplate('${e(t.id)}')">✕</button></div>`:''}
        </div>`).join('')}
      </div>
    </div>`;
  }).join('') + `<div style="margin-top:12px">
    <button class="btn btn-outline btn-sm" onclick="openCompose()">+ Blank Letter</button>
  </div>`;
}

// ── LETTERS TABLE ────────────────────────────────────────────
function renderLetters(search='') {
  const q = (search || document.querySelector('.search-box')?.value || '').toLowerCase();
  const statusF = document.getElementById('filterStatus')?.value || '';
  const catF = document.getElementById('filterCat')?.value || '';
  let rows = letters.filter(l => {
    const matchQ = !q || l.subject.toLowerCase().includes(q) || l.recipient_name.toLowerCase().includes(q) || l.recipient_email.toLowerCase().includes(q);
    const matchS = !statusF || l.status === statusF;
    const matchC = !catF || l.category === catF;
    return matchQ && matchS && matchC;
  }).sort((a,b)=>new Date(b.sent_at)-new Date(a.sent_at));

  const tbody = document.getElementById('lettersBody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No letters found. Click <strong>+ New Letter</strong> to send your first one.</td></tr>`;
    return;
  }
  const statusBadge = s => s==='sent'?'<span class="badge b-sent">Sent</span>':s==='read'?'<span class="badge b-read">Read</span>':s==='acknowledged'?'<span class="badge b-ack">Acknowledged</span>':'<span class="badge b-draft">Draft</span>';
  const fmt = d => d ? new Date(d).toLocaleDateString('en-GB',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) : '—';
  tbody.innerHTML = rows.map(l=>`<tr>
    <td><div style="font-weight:600;font-size:13px">${e(l.recipient_name)}</div><div style="font-size:11px;color:var(--muted)">${e(l.recipient_email)}</div></td>
    <td style="max-width:220px;font-size:13px">${e(l.subject)}</td>
    <td><span class="badge b-cat">${e(l.category)}</span></td>
    <td>${statusBadge(l.status)}</td>
    <td style="font-size:12px;color:var(--muted)">${fmt(l.sent_at)}</td>
    <td style="font-size:12px;color:var(--muted)">${fmt(l.read_at)}</td>
    <td>
      <button class="btn btn-outline btn-sm" onclick="viewLetter('${e(l.id)}')">View</button>
    </td>
  </tr>`).join('');
}

function viewLetter(id) {
  const l = letters.find(x=>x.id===id);
  curViewId = id;
  document.getElementById('viewModalTitle').textContent = l.subject;
  document.getElementById('viewMeta').innerHTML = `
    <div style="background:var(--cream);border-radius:5px;padding:12px">
      <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Recipient</div>
      <div style="font-weight:700">${e(l.recipient_name)}</div>
      <div style="font-size:12px;color:var(--muted)">${e(l.recipient_email)}</div>
    </div>
    <div style="background:var(--cream);border-radius:5px;padding:12px">
      <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Status</div>
      <div style="font-weight:700">${l.status.charAt(0).toUpperCase()+l.status.slice(1)}</div>
      <div style="font-size:12px;color:var(--muted)">${l.read_at?'Read '+new Date(l.read_at).toLocaleDateString('en-GB'):'Not yet read'}</div>
    </div>
    <div style="background:var(--cream);border-radius:5px;padding:12px">
      <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Sent</div>
      <div style="font-weight:700">${new Date(l.sent_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</div>
      <div style="font-size:12px;color:var(--muted)">${new Date(l.sent_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'})}</div>
    </div>`;
  document.getElementById('viewBody').textContent = l.body;
  openM('viewModal');
}

async function deleteCurrentLetter() {
  if (!confirm('Delete this letter from records?')) return;
  const d = await api('delete_letter', {id: curViewId});
  if (d.success) {
    letters = letters.filter(l=>l.id!==curViewId);
    renderStats(); renderLetters();
    closeM('viewModal'); toast('Letter deleted');
  }
}

function copyLetterLink() {
  navigator.clipboard.writeText(document.getElementById('cLink').textContent).then(()=>toast('Link copied'));
}

// ── COMPOSE FLOW ─────────────────────────────────────────────
let cStep = 1;

function openCompose(tmplId='') {
  cStep = tmplId ? 2 : 1;
  document.getElementById('composeAlert').innerHTML = '';
  document.getElementById('cStep1').style.display = tmplId ? 'none' : 'block';
  document.getElementById('cStep2').style.display = tmplId ? 'block' : 'none';
  document.getElementById('cStep3').style.display = 'none';
  document.getElementById('cBackBtn').style.display = tmplId ? 'none' : 'block';
  document.getElementById('cNextBtn').textContent = tmplId ? '📧 Send Letter →' : 'Choose Template →';
  document.getElementById('cNextBtn').style.display = 'inline-flex';

  if (tmplId) {
    selectedTemplateId = tmplId;
    fillTemplate(tmplId);
  } else {
    selectedTemplateId = '';
    renderModalTemplates();
    // Reset step 2 fields
    ['cRecipName','cRecipEmail','cSubject','cBody'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('cRequireAck').checked = false;
  }

  // Populate client dropdown
  document.getElementById('cLinkedClient').innerHTML =
    '<option value="">— Not linked to a client —</option>' +
    clients.map(c=>`<option value="${e(c.id)}">${e(c.name)}${c.company?' — '+e(c.company):''}</option>`).join('');

  openM('composeModal');
}

function renderModalTemplates() {
  const allTemplates = [...SYSTEM_TEMPLATES, ...customTemplates.map(t=>({...t,cat:t.category}))];
  document.getElementById('modalTemplateGrid').innerHTML = allTemplates.map(t=>
    `<div class="tmpl-card" onclick="selectModalTemplate('${e(t.id)}')" style="padding:14px">
      <div class="tmpl-cat">${e(t.cat)}</div>
      <div class="tmpl-name" style="font-size:13px">${e(t.name)}</div>
    </div>`
  ).join('') +
  `<div class="tmpl-card" onclick="selectModalTemplate('')" style="padding:14px;border-style:dashed">
    <div class="tmpl-cat">—</div>
    <div class="tmpl-name" style="font-size:13px">Blank Letter</div>
  </div>`;
}

function selectModalTemplate(id) {
  selectedTemplateId = id;
  document.querySelectorAll('#modalTemplateGrid .tmpl-card').forEach(c=>c.style.borderColor='');
  event.currentTarget.style.borderColor='var(--navy)';
}

function selectTemplate(id) {
  openCompose(id);
}

function fillTemplate(tmplId) {
  const all = [...SYSTEM_TEMPLATES, ...customTemplates];
  const tmpl = all.find(t=>t.id===tmplId);
  if (!tmpl) return;
  document.getElementById('cSubject').value = tmpl.subject || '';
  document.getElementById('cBody').value = tmpl.body || '';
  document.getElementById('cCategory').value = tmpl.cat || tmpl.category || 'General';
}

function cBack() {
  if (cStep === 2) {
    cStep = 1;
    document.getElementById('cStep1').style.display = 'block';
    document.getElementById('cStep2').style.display = 'none';
    document.getElementById('cBackBtn').style.display = 'none';
    document.getElementById('cNextBtn').textContent = 'Choose Template →';
  }
}

async function cNext() {
  if (cStep === 1) {
    // Move to step 2
    if (selectedTemplateId) fillTemplate(selectedTemplateId);
    else { document.getElementById('cSubject').value=''; document.getElementById('cBody').value=''; }
    cStep = 2;
    document.getElementById('cStep1').style.display = 'none';
    document.getElementById('cStep2').style.display = 'block';
    document.getElementById('cBackBtn').style.display = 'inline-flex';
    document.getElementById('cNextBtn').textContent = '📧 Send Letter →';
  } else if (cStep === 2) {
    await sendLetter();
  }
}

function prefillFromClient(clientId) {
  if (!clientId) return;
  const c = clients.find(x=>x.id===clientId);
  if (!c) return;
  if (!document.getElementById('cRecipName').value) document.getElementById('cRecipName').value = c.name;
  if (!document.getElementById('cRecipEmail').value) document.getElementById('cRecipEmail').value = c.email;
  // Apply placeholders to body
  applyPlaceholders(c);
}

function applyPlaceholders(c) {
  let body = document.getElementById('cBody').value;
  let subj = document.getElementById('cSubject').value;
  const firstName = (c.name||'').split(' ')[0];
  const replacements = {
    '{{client_name}}': c.name||'',
    '{{client_first_name}}': firstName,
    '{{client_company}}': c.company||'',
    '{{firm_name}}': FIRM_NAME,
    '{{date}}': TODAY,
  };
  for (const [k,v] of Object.entries(replacements)) {
    body = body.split(k).join(v);
    subj = subj.split(k).join(v);
  }
  document.getElementById('cBody').value = body;
  document.getElementById('cSubject').value = subj;
}

async function sendLetter() {
  const recipName  = document.getElementById('cRecipName').value.trim();
  const recipEmail = document.getElementById('cRecipEmail').value.trim();
  const subject    = document.getElementById('cSubject').value.trim();
  const body       = document.getElementById('cBody').value.trim();
  const category   = document.getElementById('cCategory').value;
  const requireAck = document.getElementById('cRequireAck').checked;
  const linkedId   = document.getElementById('cLinkedClient').value;

  if (!recipName || !recipEmail || !subject || !body) {
    document.getElementById('composeAlert').innerHTML = '<div class="alert a-err">Please fill in recipient name, email, subject, and letter body.</div>';
    return;
  }

  // Apply remaining placeholders if client not selected
  const cLinked = clients.find(x=>x.id===linkedId);
  if (cLinked) applyPlaceholders(cLinked);
  // Apply firm placeholder
  const finalBody = document.getElementById('cBody').value.split('{{firm_name}}').join(FIRM_NAME).split('{{date}}').join(TODAY);
  const finalSubj = document.getElementById('cSubject').value.split('{{firm_name}}').join(FIRM_NAME);

  document.getElementById('cNextBtn').disabled = true;
  document.getElementById('cNextBtn').textContent = 'Sending…';

  const d = await api('send_letter', {
    subject: finalSubj, body: finalBody,
    recipient_name: recipName, recipient_email: recipEmail,
    recipient_type: document.getElementById('cRecipType').value,
    category, require_acknowledgement: requireAck,
    linked_client_id: linkedId, template_id: selectedTemplateId,
  });

  if (d.success) {
    // Reload letters
    const ld = await api('get_letters', {});
    if (ld.success) letters = ld.letters;
    renderStats(); renderLetters();
    cStep = 3;
    document.getElementById('cStep2').style.display = 'none';
    document.getElementById('cStep3').style.display = 'block';
    document.getElementById('cFoot').style.display = 'none';
    document.getElementById('cConfirmRecip').textContent = recipName + ' <' + recipEmail + '>';
    document.getElementById('cLink').textContent = d.link;
  } else {
    document.getElementById('composeAlert').innerHTML = `<div class="alert a-err">${d.error||'Failed to send. Check email settings.'}</div>`;
    document.getElementById('cNextBtn').disabled = false;
    document.getElementById('cNextBtn').textContent = '📧 Send Letter →';
  }
}

async function saveAsTemplate() {
  const name = prompt('Template name:');
  if (!name) return;
  const d = await api('save_custom_template', {
    name,
    category: document.getElementById('cCategory').value,
    subject: document.getElementById('cSubject').value,
    body: document.getElementById('cBody').value,
  });
  if (d.success) {
    const td = await api('get_custom_templates', {});
    if (td.success) customTemplates = td.templates;
    renderTemplates();
    toast('Template saved — it will appear in the Templates tab');
  }
}

async function deleteCustomTemplate(id) {
  if (!confirm('Delete this custom template?')) return;
  const d = await api('delete_custom_template', {id});
  if (d.success) {
    customTemplates = customTemplates.filter(t=>t.id!==id);
    renderTemplates(); toast('Template deleted');
  }
}

// ── LETTERHEAD ───────────────────────────────────────────────
async function openLetterhead() {
  const d = await api('get_letterhead', {});
  if (!d.success) return;
  const lh = d.letterhead;
  document.getElementById('lhTagline').value     = lh.tagline||'';
  document.getElementById('lhBrandColour').value = lh.brand_colour||'#1a3558';
  document.getElementById('lhAccentColour').value= lh.accent_colour||'#c9a84c';
  document.getElementById('lhFooterText').value  = lh.footer_text||'';
  document.getElementById('lhDisclaimer').value  = lh.disclaimer||'';
  document.getElementById('lhLinkedin').value    = lh.social_linkedin||'';
  document.getElementById('lhTwitter').value     = lh.social_twitter||'';
  if (lh.logo) {
    lhLogoData = lh.logo;
    document.getElementById('lhPreviewLogo').src = lh.logo;
    document.getElementById('lhPreviewLogo').style.display = 'block';
  }
  updateLhPreview();
  openM('letterheadModal');
}

function loadLhLogo(input) {
  const file = input.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    lhLogoData = ev.target.result;
    document.getElementById('lhPreviewLogo').src = lhLogoData;
    document.getElementById('lhPreviewLogo').style.display = 'block';
  };
  reader.readAsDataURL(file);
}

function updateLhPreview() {
  const bc = document.getElementById('lhBrandColour').value;
  const ac = document.getElementById('lhAccentColour').value;
  const tagline = document.getElementById('lhTagline').value;
  const footer  = document.getElementById('lhFooterText').value;
  document.getElementById('lhPreviewHeader').style.background = bc;
  document.getElementById('lhPreviewName').style.color = ac;
  document.getElementById('lhPreviewTagline').textContent = tagline;
  document.getElementById('lhPreviewFooter').textContent = footer || ('ICO Registration ' + FIRM_ICO);
  document.getElementById('lhPreviewFooter').style.borderTopColor = bc;
}

async function saveLetterhead() {
  const d = await api('save_letterhead', {
    logo:           lhLogoData,
    tagline:        document.getElementById('lhTagline').value,
    brand_colour:   document.getElementById('lhBrandColour').value,
    accent_colour:  document.getElementById('lhAccentColour').value,
    footer_text:    document.getElementById('lhFooterText').value,
    disclaimer:     document.getElementById('lhDisclaimer').value,
    social_linkedin:document.getElementById('lhLinkedin').value,
    social_twitter: document.getElementById('lhTwitter').value,
  });
  if (d.success) { toast('Letterhead saved'); closeM('letterheadModal'); }
  else toast('Save failed');
}

// ── INIT ─────────────────────────────────────────────────────
async function init() {
  const [ld, cd, td] = await Promise.all([
    api('get_letters', {}),
    api('clients', {}),
    api('get_custom_templates', {}),
  ]);
  if (ld.success) letters = ld.letters;
  if (cd.success) clients = cd.clients || cd;
  if (td.success) customTemplates = td.templates;
  renderStats();
  renderTemplates();
}

init();
</script>
</body>
</html>
