<?php
require_once __DIR__ . '/config.php';

// â”€â”€ Subscription guard (only for provisioned firm instances) â”€â”€
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
  <link rel="stylesheet" href="style.css?v=1.1">
</head>

<body>

  <!-- LOGIN -->
  <div id="loginScreen">
    <div class="login-box">
      <div class="login-logo">The <span>Practice</span></div>
      <div class="login-sub">Client Dashboard â€” Secure Access</div>
      <input type="password" id="pwdInput" placeholder="Enter your password"
        onkeydown="if(event.key==='Enter')doLogin()">
      <button class="btn-login" onclick="doLogin()">Sign In â†’</button>
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
        <a href="pricing.html#partnership" class="partner">â˜… Partner Programme</a>
        <a href="dashboard.php" class="active">Client Dashboard</a>
        <a href="letters.php">âœ‰ Correspondence</a>
        <a href="setup.php">âš™ Settings</a>
        <a href="help.php">â“ Help</a>
      </div>
      <div class="nav-right">
        <span id="clientCountBadge" style="font-size:12px;color:#64748b;display:none"></span>
        <button class="logout-btn" onclick="doLogout()">Log out</button>
        <button class="btn btn-outline" style="font-size:13px;padding:8px 16px" onclick="openM('onboardModal')">ðŸ“‹
          Send Onboarding Link</button>
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
          <div class="overdue-bar-text">âš ï¸ <span id="overdueCount">0</span> client(s) have not signed past their
            deadline</div>
          <div class="overdue-bar-sub">Send reminders individually or use Remind All to chase everyone at once</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn btn-red" onclick="remindAll()">ðŸ”” Remind All Overdue</button>
          <button class="btn btn-outline" style="color:#991b1b;border-color:#fca5a5"
            onclick="switchTab('overdue', document.getElementById('tabOverdue'))">View Overdue â†’</button>
        </div>
      </div>

      <!-- MONTHLY STATS -->
      <div class="mstats" id="monthlyStats"></div>

      <!-- STATS -->
      <div class="stats">
        <div class="stat s-total">
          <div class="stat-n" id="stTotal">0</div>
          <div class="stat-l">Total Clients</div>
        </div>
        <div class="stat s-sent">
          <div class="stat-n" id="stSent">0</div>
          <div class="stat-l">Awaiting Signature</div>
        </div>
        <div class="stat s-signed">
          <div class="stat-n" id="stSigned">0</div>
          <div class="stat-l">Signed</div>
        </div>
        <div class="stat s-aml">
          <div class="stat-n" id="stAml">0</div>
          <div class="stat-l">AML Complete</div>
        </div>
      </div>

      <!-- TABS -->
      <div class="tabs">
        <div class="tab active" onclick="switchTab('all',this)">All Clients</div>
        <div class="tab" id="tabKanban" onclick="switchTab('kanban',this)">📋 Workflow Board</div>
        <div class="tab" onclick="switchTab('pending',this)">Awaiting Signature</div>
        <div class="tab" id="tabOverdue" onclick="switchTab('overdue',this)">âš ï¸ Overdue</div>
        <div class="tab" onclick="switchTab('signed',this)">Signed</div>
        <div class="tab" onclick="switchTab('aml',this)">AML Records</div>
        <div class="tab" onclick="switchTab('mtd',this)">ðŸ“Š MTD Tracker</div>
        <div class="tab" id="tabDeadlines" onclick="switchTab('deadlines',this)">ðŸ“… Deadlines</div>
        <div class="tab" onclick="switchTab('archive',this)">ðŸ“ Archive</div>
      </div>

      <!-- KANBAN BOARD -->
      <div id="viewKanban" style="display:none">
        <div class="table-top"
          style="border:1px solid var(--border);border-bottom:none;border-radius:6px 6px 0 0;background:#fff;margin-bottom:16px;">
          <div class="table-ttl">Practice Workflow</div>
          <div style="font-size:12px;color:var(--muted)">Drag and drop clients between active stages</div>
        </div>
        <div class="kanban-board" id="kanbanBoard"></div>
      </div>

      <!-- CLIENTS TABLE -->
      <div id="viewClients">
        <div class="table-wrap">
          <div class="table-top">
            <div class="table-ttl" id="tabTitle">All Clients</div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <input type="text" id="searchBox" placeholder="Searchâ€¦"
                style="padding:7px 12px;border:1px solid var(--border);border-radius:4px;font-size:13px;outline:none;width:180px"
                oninput="renderTable()">
              <a href="api.php?action=export_csv" class="btn btn-outline" title="Export to Excel/CSV">â¬‡ CSV</a>
            </div>
          </div>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Type</th>
                <th>Service</th>
                <th>Signature</th>
                <th>Deadline</th>
                <th>AML</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="clientTbody"></tbody>
          </table>
        </div>
      </div>

      <!-- AML TABLE -->
      <div id="viewAml" style="display:none">
        <div class="table-wrap">
          <div class="table-top">
            <div class="table-ttl">AML Customer Due Diligence Records</div>
          </div>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>ID Type</th>
                <th>ID Reference</th>
                <th>Risk</th>
                <th>Verified</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="amlTbody"></tbody>
          </table>
        </div>
      </div>

      <!-- MTD TRACKER -->
      <div id="viewMtd" style="display:none">
        <div class="table-wrap">
          <div class="table-top">
            <div class="table-ttl">ðŸ“Š Making Tax Digital â€” Client Tracker</div>
            <div style="font-size:12px;color:var(--muted)">MTD for Income Tax mandatory from April 2026 for income over
              Â£50,000</div>
          </div>
          <div style="padding:16px 20px;background:#fffbeb;border-bottom:1px solid var(--border)">
            <div style="font-size:13px;color:#92400e;font-weight:600;margin-bottom:4px">âš ï¸ MTD for Income Tax is now
              live â€” 6 April 2026</div>
            <div style="font-size:12px;color:#b45309">Clients earning over Â£50,000 from self-employment or property
              must now keep digital records and submit quarterly updates. Threshold drops to Â£30,000 in April 2027.
            </div>
          </div>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Income Threshold</th>
                <th>MTD Status</th>
                <th>Software</th>
                <th>Enrolled</th>
                <th>Next Submission</th>
                <th>Notes</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="mtdTbody"></tbody>
          </table>
        </div>
      </div>

      <!-- DEADLINE TRACKER -->
      <div id="viewDeadlines" style="display:none">
        <div class="table-wrap">
          <div class="table-top">
            <div class="table-ttl">ðŸ“… Statutory Deadline Tracker</div>
            <div class="dl-filters">
              <button class="dl-filter active" onclick="dlFilter('all',this)">All</button>
              <button class="dl-filter" onclick="dlFilter('overdue',this)">ðŸ”´ Overdue <span id="dlCountOverdue"
                  class="dl-count" style="display:none"></span></button>
              <button class="dl-filter" onclick="dlFilter('soon',this)">ðŸŸ¡ Due Soon</button>
              <button class="dl-filter" onclick="dlFilter('upcoming',this)">ðŸŸ¢ Upcoming</button>
              <button class="dl-filter" onclick="dlFilter('done',this)">âœ“ Completed</button>
            </div>
          </div>
          <div
            style="padding:12px 20px;background:#eff6ff;border-bottom:1px solid #bfdbfe;font-size:12px;color:#1e40af">
            â„¹ï¸ Set up deadline tracking per client using the <strong>ðŸ“…</strong> button on any client row.
            Deadlines auto-calculate from year end dates.
          </div>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Deadline Type</th>
                <th>Due Date</th>
                <th>Days Left</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="dlTbody"></tbody>
          </table>
        </div>
      </div>

      <!-- DOCUMENT ARCHIVE -->
      <div id="viewArchive" style="display:none">
        <div class="table-wrap">
          <div class="table-top">
            <div class="table-ttl">ðŸ“ Signed Document Archive</div>
            <div style="font-size:12px;color:var(--muted)">All signed engagement letters with audit trails</div>
          </div>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Service</th>
                <th>Signed</th>
                <th>Method</th>
                <th>IP Address</th>
                <th>SHA-256 Hash</th>
                <th>Download</th>
              </tr>
            </thead>
            <tbody id="archiveTbody"></tbody>
          </table>
        </div>
      </div>

    </div>

    <footer>
      <strong>The Practice</strong> Â· practice.finaccord.pro Â· info@kafs-ltd.com Â· GDPR-compliant Â· UK-hosted Â·
      Strictly confidential &nbsp;|&nbsp; Â© 2026 A Finaccord Professional Services Programme
    </footer>
  </div>

  <!-- ADD CLIENT MODAL -->
  <div class="overlay" id="addModal">
    <div class="modal">
      <div class="m-head">
        <div class="m-title">Add New Client</div><button class="m-close" onclick="closeM('addModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="addAlert"></div>
        <div class="fg-row">
          <div class="fg"><label>Full Name *</label><input type="text" id="addName" placeholder="Jane Smith"></div>
          <div class="fg"><label>Email Address *</label><input type="email" id="addEmail"
              placeholder="jane@example.co.uk"></div>
        </div>
        <div class="fg">
          <label>Company / Trading Name
            <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px;color:var(--muted)"> â€”
              type name then click Lookup to auto-fill from Companies House</span>
          </label>
          <div style="display:flex;gap:8px">
            <input type="text" id="addCompany" placeholder="Smith Ltd" style="flex:1">
            <button type="button" class="btn btn-outline" onclick="chLookup()"
              style="white-space:nowrap;padding:8px 14px;font-size:13px">ðŸ” CH Lookup</button>
          </div>
          <div id="chResults"
            style="display:none;border:1px solid var(--border);border-radius:4px;margin-top:4px;max-height:200px;overflow-y:auto;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.1)">
          </div>
        </div>
        <div class="fg-row">
          <div class="fg"><label>Registered Address</label><input type="text" id="addAddress"
              placeholder="Auto-filled from Companies House"></div>
          <div class="fg"><label>Company Number</label><input type="text" id="addCompanyNumber"
              placeholder="e.g. 12345678"></div>
        </div>
        <div class="fg-row">
          <div class="fg" style="display:none"><label>_</label></div>
          <div class="fg"><label>Entity Type</label>
            <select id="addType">
              <option>Ltd Company</option>
              <option>Sole Trader</option>
              <option>Partnership</option>
              <option>LLP</option>
              <option>Individual</option>
              <option>Charity</option>
              <option>Trust</option>
            </select>
          </div>
        </div>
        <div class="fg">
          <label>Services <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(tick
              all that apply â€” add custom below)</span></label>
          <div class="svc-picker">
            <div class="svc-presets">
              <label class="svc-cb"><input type="checkbox" name="svc" value="Self Assessment Tax Return"> Self
                Assessment Tax Return</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="Company Accounts &amp; Corporation Tax">
                Company Accounts &amp; Corp Tax</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="VAT Returns"> VAT Returns</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="Bookkeeping"> Bookkeeping</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="Payroll"> Payroll</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="Making Tax Digital (MTD) Support"> MTD
                Support</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="Management Accounts"> Management
                Accounts</label>
              <label class="svc-cb"><input type="checkbox" name="svc" value="Full Accountancy Package"> Full Accountancy
                Package</label>
            </div>
            <div class="svc-custom-row">
              <input type="text" id="addCustomSvc" placeholder="Add a custom service not listed aboveâ€¦"
                onkeydown="if(event.key==='Enter'){event.preventDefault();addCustomSvc()}">
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

  <!-- SEND LINK MODAL â€” with letter editor -->
  <div class="overlay" id="linkModal">
    <div class="modal modal-wide">
      <div class="m-head">
        <div class="m-title" id="linkModalTitle">Review &amp; Send Engagement Letter</div>
        <button class="m-close" onclick="closeM('linkModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="linkAlert"></div>

        <!-- STEP 1: EDIT LETTER -->
        <div id="linkStep1">
          <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Sending to: <strong id="linkEmail"></strong>
            â€” Edit the letter below before sending. The client will see exactly this.</p>
          <div class="fg-row" style="margin-bottom:16px">
            <div class="fg">
              <label>Agreed Fee (appears in letter)</label>
              <input type="text" id="linkFee" placeholder="e.g. Â£350 per annum + VAT">
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
            <label>Engagement Letter â€” Edit as needed</label>
            <textarea class="letter-editor" id="letterEditor"></textarea>
            <p style="font-size:11px;color:var(--muted);margin-top:5px">You can edit any part of this letter. The client
              will sign exactly what you see here.</p>
          </div>
          <div class="fg" style="margin-top:4px">
            <label>Your Firm Signature <span
                style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(appears on the letter
                the client sees)</span></label>
            <div class="firm-sig-section">
              <div class="fsig-tabs">
                <button type="button" class="fsig-tab active" id="fsigTabSaved" onclick="switchFSigTab('saved')">Saved
                  Signature</button>
                <button type="button" class="fsig-tab" id="fsigTabDraw" onclick="switchFSigTab('draw')">Draw
                  New</button>
                <button type="button" class="fsig-tab" id="fsigTabUpload" onclick="switchFSigTab('upload')">Upload
                  Image</button>
              </div>
              <div class="fsig-panel" id="fsigPanelSaved">
                <div class="fsig-preview-box">
                  <span id="fsigNoSig" style="font-size:12px;color:var(--muted)">No signature saved yet â€” use Draw or
                    Upload to add one.</span>
                  <img id="fsigPreview" src="" alt="Firm signature" style="max-height:70px;max-width:100%;display:none">
                </div>
              </div>
              <div class="fsig-panel" id="fsigPanelDraw" style="display:none">
                <div style="position:relative;border:1px solid var(--border);border-radius:4px;background:#fafaf8">
                  <canvas id="fsigCanvas"
                    style="display:block;width:100%;height:110px;cursor:crosshair;touch-action:none"></canvas>
                  <button type="button" onclick="clearFSig()"
                    style="position:absolute;top:6px;right:8px;background:#fff;border:1px solid var(--border);border-radius:4px;padding:2px 8px;font-size:11px;color:var(--muted);cursor:pointer">Clear</button>
                </div>
                <button type="button" class="btn-save-sig" onclick="saveFirmSig('draw')">ðŸ’¾ Save &amp; Use This
                  Signature</button>
                <span class="sig-saved-ok" id="drawSavedOk">âœ“ Saved</span>
              </div>
              <div class="fsig-panel" id="fsigPanelUpload" style="display:none">
                <p style="font-size:12px;color:var(--muted);margin-bottom:8px">Upload a PNG or JPG of your signature
                  (white or transparent background works best).</p>
                <input type="file" id="fsigFile" accept="image/png,image/jpeg" onchange="loadFSigFile(this)"
                  style="font-size:12px;margin-bottom:8px;display:block">
                <img id="fsigUploadPreview" src="" alt=""
                  style="max-height:70px;display:none;border:1px solid var(--border);border-radius:4px;padding:4px;margin-bottom:8px">
                <button type="button" class="btn-save-sig" onclick="saveFirmSig('upload')">ðŸ’¾ Save &amp; Use This
                  Signature</button>
                <span class="sig-saved-ok" id="uploadSavedOk">âœ“ Saved</span>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 2: SENT CONFIRMATION -->
        <div id="linkStep2" style="display:none">
          <div class="alert a-ok" style="font-size:14px;padding:14px">âœ“ Email sent successfully. The client will
            receive their signing link shortly.</div>
          <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Signing deadline set to: <strong
              id="linkDeadlineConfirm"></strong></p>
          <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Or share this link manually:</p>
          <div class="link-box" id="linkBox" onclick="copyLink()">
            <span id="linkText"></span>
            <span class="link-copied" id="linkCopied">Copied!</span>
          </div>
        </div>
      </div>
      <div class="m-foot" id="linkFoot">
        <button class="btn btn-outline" onclick="closeM('linkModal')">Cancel</button>
        <button class="btn btn-gold" id="sendBtn" onclick="sendLink()">ðŸ“§ Save Letter &amp; Send â†’</button>
      </div>
    </div>
  </div>

  <!-- MTD MODAL -->
  <div class="overlay" id="mtdModal">
    <div class="modal">
      <div class="m-head">
        <div class="m-title">MTD Record â€” <span id="mtdClientName"></span></div><button class="m-close"
          onclick="closeM('mtdModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="mtdAlert"></div>
        <div class="fg-row">
          <div class="fg"><label>Annual Income Threshold</label>
            <select id="mtdThreshold">
              <option value="under20k">Under Â£20,000 â€” not yet in scope</option>
              <option value="20k-30k">Â£20,000â€“Â£30,000 â€” in scope April 2028</option>
              <option value="30k-50k">Â£30,000â€“Â£50,000 â€” in scope April 2027</option>
              <option value="over50k">Over Â£50,000 â€” in scope NOW (April 2026)</option>
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
          <label style="display:block;margin-bottom:10px;font-weight:700;color:var(--navy-dark)">ðŸ”” Automatic
            Submission Reminders</label>
          <div style="font-size:12px;color:var(--muted);margin-bottom:10px">Select when reminders are sent before the
            submission date. Reminders go to both the client and the accountant.</div>
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
          <div style="font-size:12px;color:var(--muted)">ðŸ’¡ Requires cron job to be active on the server â€” see setup
            guide.</div>
        </div>

        <div class="fg"><label>MTD Notes</label>
          <textarea id="mtdNotes"
            placeholder="e.g. Client signed up to Xero on 1 April. First quarterly submission due July. Bank feed connected."></textarea>
        </div>
        <div class="alert a-warn" style="font-size:12px">
          MTD for Income Tax requires clients earning over Â£50,000 from self-employment or property to keep digital
          records and submit quarterly updates to HMRC using compatible software from 6 April 2026.
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
      <div class="m-head">
        <div class="m-title">ðŸ“‹ Send Client Onboarding Link</div><button class="m-close"
          onclick="closeM('onboardModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="onboardAlert"></div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Send a link to a new or prospective client so
          they fill in their own details. Their information arrives directly in your dashboard ready to activate.</p>
        <div class="fg-row">
          <div class="fg"><label>Client Full Name *</label><input type="text" id="obName" placeholder="Jane Smith">
          </div>
          <div class="fg"><label>Client Email Address *</label><input type="email" id="obEmail"
              placeholder="jane@smithltd.co.uk"></div>
        </div>
        <div id="onboardLink" style="display:none;margin-top:12px">
          <div class="alert"
            style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:4px;padding:14px;font-size:13px">
            âœ… Onboarding link sent to <span id="obSentEmail"></span><br>
            <span style="font-size:11px;color:var(--muted)">You can also copy the link below:</span><br>
            <input type="text" id="obLinkCopy" readonly
              style="margin-top:6px;width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:4px;font-size:12px;background:#f8f7f4">
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
      <div class="m-head">
        <div class="m-title">ðŸ“… Deadline Setup â€” <span id="dlClientName"></span></div><button class="m-close"
          onclick="closeM('dlModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="dlAlert"></div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Configure which deadlines apply to this client.
          Dates auto-calculate. You can mark individual deadlines as filed or paid from the Deadlines tab.</p>

        <!-- Year End -->
        <div class="fg-row">
          <div class="fg">
            <label>Accounting Year End Date</label>
            <input type="date" id="dlYearEnd">
            <div style="font-size:11px;color:var(--muted);margin-top:4px">Used to calculate CT return, CT payment, and
              Companies House accounts due dates.</div>
          </div>
          <div class="fg">
            <label>Confirmation Statement Due</label>
            <input type="date" id="dlConfirmation">
            <div style="font-size:11px;color:var(--muted);margin-top:4px">Find on Companies House. Recurs annually.
            </div>
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
          <div style="font-size:11px;color:var(--muted);margin-top:4px">VAT returns due 1 month + 7 days after quarter
            end (online filing).</div>
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
            <div style="font-size:11px;color:var(--muted);margin-top:4px">RTI submissions due by 19th of each month.
            </div>
          </div>
          <div class="fg"
            style="display:flex;align-items:flex-start;flex-direction:column;justify-content:center;padding-top:8px">
            <label style="margin-bottom:12px">Self Assessment</label>
            <label
              style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;text-transform:none;letter-spacing:0;font-weight:400">
              <input type="checkbox" id="dlSa" style="width:16px;height:16px">
              Client submits a Self Assessment tax return
            </label>
            <div style="font-size:11px;color:var(--muted);margin-top:6px">SA deadline: 31 Jan (online). Payment on
              account: 31 Jan + 31 Jul.</div>
          </div>
        </div>

        <div class="alert a-warn" style="font-size:12px;margin-top:4px">
          <strong>Note:</strong> Deadlines are calculated automatically. Use the Deadlines tab to mark each deadline as
          Filed, Paid, or Pending.
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
      <div class="m-head">
        <div class="m-title">Internal Notes</div><button class="m-close" onclick="closeM('notesModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="notesAlert"></div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:12px">Notes for: <strong
            id="notesClientName"></strong><br><span style="font-size:11px">Internal only â€” not visible to
            client</span></p>
        <div class="fg">
          <label>Notes</label>
          <textarea id="notesText" style="min-height:160px"
            placeholder="e.g. Client called 3x. Waiting for P60. Referred by John Smith. Chased 4 April..."></textarea>
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
      <div class="m-head">
        <div class="m-title">AML â€” Customer Due Diligence</div><button class="m-close"
          onclick="closeM('amlModal')">Ã—</button>
      </div>
      <div class="m-body">
        <div id="amlAlert"></div>
        <p style="font-size:13px;color:var(--navy-dark);font-weight:600;margin-bottom:16px" id="amlName"></p>
        <div class="fg-row">
          <div class="fg"><label>ID Document Type</label>
            <select id="amlIdType">
              <option>UK Passport</option>
              <option>UK Driving Licence</option>
              <option>Non-UK Passport</option>
              <option>National Identity Card</option>
              <option>Biometric Residence Permit</option>
              <option>Other Government ID</option>
            </select>
          </div>
          <div class="fg"><label>ID Reference / Last 4 digits</label><input type="text" id="amlIdRef"
              placeholder="e.g. last 4 digits only"></div>
        </div>
        <div class="fg-row">
          <div class="fg"><label>Date of Verification</label><input type="date" id="amlDate"></div>
          <div class="fg"><label>Risk Assessment</label>
            <select id="amlRisk">
              <option>Low</option>
              <option>Medium</option>
              <option>High</option>
            </select>
          </div>
        </div>
        <div class="fg"><label>CDD Notes</label>
          <textarea id="amlNotes"
            placeholder="Record how identity was verified, any enhanced due diligence conducted, and any concerns notedâ€¦"></textarea>
        </div>
        <div class="alert a-warn">
          âš ï¸ You remain legally responsible for client identity verification under the Money Laundering Regulations
          2017. This record assists your compliance recordkeeping â€” it does not constitute verification itself.
        </div>
      </div>
      <div class="m-foot">
        <button class="btn btn-outline" onclick="closeM('amlModal')">Cancel</button>
        <button class="btn btn-navy" onclick="saveAml()">Save AML Record</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script src="app.js?v=1.1"></script>
</body>

</html>