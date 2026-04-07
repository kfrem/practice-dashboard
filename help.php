<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>How to Use FirmReady — Help Guide</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a2433;min-height:100vh}
a{text-decoration:none;color:inherit}
:root{
  --navy:#1a3558;--navy-dark:#0f2238;--gold:#c9a84c;--gold-light:#fffbeb;
  --cream:#f8f7f4;--border:#ddd8cf;--muted:#64748b;
  --success:#2d6a4f;--danger:#c0392b;--warn:#b45309
}

/* NAV */
nav{background:#fff;border-bottom:1px solid var(--border);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.logo{font-size:19px;font-weight:800;color:var(--navy)}
.logo span{color:var(--gold)}
.nav-right a{font-size:13px;color:var(--muted);margin-left:20px;font-weight:600}
.nav-right a:hover{color:var(--navy)}
.nav-right a.btn{background:var(--navy);color:#fff;padding:8px 18px;border-radius:4px}
.nav-right a.btn:hover{background:var(--navy-dark)}

/* HERO */
.hero{background:var(--navy);padding:48px 24px 40px;text-align:center}
.hero h1{font-size:30px;font-weight:800;color:#fff;margin-bottom:10px}
.hero p{font-size:15px;color:#94a3b8;max-width:560px;margin:0 auto}

/* LAYOUT */
.wrap{max-width:960px;margin:0 auto;padding:40px 24px 80px}

/* SECTION TITLES */
.section-head{display:flex;align-items:center;gap:12px;margin:48px 0 24px}
.section-icon{width:44px;height:44px;border-radius:8px;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.section-title{font-size:20px;font-weight:800;color:var(--navy-dark)}
.section-sub{font-size:13px;color:var(--muted);margin-top:2px}

/* FLOW DIAGRAM */
.flow{display:flex;align-items:center;flex-wrap:wrap;gap:0;margin-bottom:32px;background:#fff;border:1px solid var(--border);border-radius:8px;padding:28px 24px;overflow-x:auto}
.flow-step{display:flex;flex-direction:column;align-items:center;text-align:center;min-width:110px;flex:1}
.flow-circle{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:10px;border:2px solid}
.flow-circle.c1{background:#e8edf5;border-color:#1a3558}
.flow-circle.c2{background:#fef3c7;border-color:#c9a84c}
.flow-circle.c3{background:#d1fae5;border-color:#2d6a4f}
.flow-circle.c4{background:#dbeafe;border-color:#1e40af}
.flow-circle.c5{background:#f3e8ff;border-color:#7c3aed}
.flow-label{font-size:12px;font-weight:700;color:var(--navy-dark);margin-bottom:3px}
.flow-desc{font-size:11px;color:var(--muted);line-height:1.4;max-width:100px}
.flow-arrow{font-size:22px;color:var(--border);margin:0 4px;padding-bottom:24px;flex-shrink:0}

/* STEP CARDS */
.steps-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px}
@media(max-width:640px){.steps-grid{grid-template-columns:1fr}}
.step-card{background:#fff;border:1px solid var(--border);border-radius:8px;padding:20px 22px;display:flex;gap:16px;align-items:flex-start}
.step-num{width:32px;height:32px;border-radius:50%;background:var(--navy);color:#fff;font-size:14px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.step-body h4{font-size:13px;font-weight:700;color:var(--navy-dark);margin-bottom:4px}
.step-body p{font-size:12px;color:var(--muted);line-height:1.5}
.step-body .tag{display:inline-block;background:#f0f4f8;border:1px solid var(--border);border-radius:3px;font-size:11px;font-weight:700;color:var(--navy);padding:2px 7px;margin-top:5px}

/* TIP BOX */
.tip{background:var(--gold-light);border:1px solid #fde68a;border-left:4px solid var(--gold);border-radius:4px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#92400e;line-height:1.6}
.tip strong{color:#78350f}

/* INFO CARDS */
.info-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
@media(max-width:700px){.info-grid{grid-template-columns:1fr}}
.info-card{background:#fff;border:1px solid var(--border);border-radius:8px;padding:20px;text-align:center}
.info-card .icon{font-size:32px;margin-bottom:10px}
.info-card h4{font-size:13px;font-weight:700;color:var(--navy-dark);margin-bottom:6px}
.info-card p{font-size:12px;color:var(--muted);line-height:1.5}

/* STATUS BADGES */
.badge-row{display:flex;gap:10px;flex-wrap:wrap;margin:10px 0}
.bdg{display:inline-block;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:700}
.bdg-pending{background:#fef3c7;color:#92400e}
.bdg-sent{background:#dbeafe;color:#1e40af}
.bdg-signed{background:#d1fae5;color:#065f46}
.bdg-overdue{background:#fee2e2;color:#991b1b}
.bdg-aml-ok{background:#d1fae5;color:#065f46}
.bdg-aml-pend{background:#fef9c3;color:#854d0e}

/* KEYBOARD SHORTCUT STYLE */
.screen-label{background:var(--navy);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:3px 10px;border-radius:3px;display:inline-block;margin-bottom:10px}

/* DIVIDER */
hr{border:none;border-top:2px solid var(--border);margin:40px 0}

/* QUICK REF TABLE */
.ref-table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;border:1px solid var(--border)}
.ref-table th{background:var(--navy);color:#fff;font-size:11px;letter-spacing:.8px;text-transform:uppercase;padding:12px 16px;text-align:left}
.ref-table td{padding:11px 16px;font-size:13px;border-bottom:1px solid #f0ede6}
.ref-table tr:last-child td{border-bottom:none}
.ref-table tr:hover td{background:#fafaf7}
.ref-table td:first-child{font-weight:700;color:var(--navy-dark);width:200px}
</style>
</head>
<body>

<nav>
  <div class="logo"><?= htmlspecialchars(FR_FIRM_NAME) ?> <span>FirmReady</span></div>
  <div class="nav-right">
    <a href="dashboard.php">← Back to Dashboard</a>
    <a href="dashboard.php" class="btn">Open Dashboard</a>
  </div>
</nav>

<div class="hero">
  <h1>How to Use FirmReady</h1>
  <p>Your complete visual guide — from adding a client to getting documents signed, tracking AML, MTD and statutory deadlines</p>
</div>

<div class="wrap">

  <!-- ══════════════════════════════════════════════════════ -->
  <!-- QUICK START -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">🚀</div>
    <div>
      <div class="section-title">Quick Start — Your First Client in 5 Minutes</div>
      <div class="section-sub">Follow these steps in order the first time you use FirmReady</div>
    </div>
  </div>

  <div class="flow">
    <div class="flow-step">
      <div class="flow-circle c1">➕</div>
      <div class="flow-label">1. Add Client</div>
      <div class="flow-desc">Click "+ Add Client" button</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c2">✍️</div>
      <div class="flow-label">2. Send Letter</div>
      <div class="flow-desc">Click "Send Link" to edit and send</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c3">✅</div>
      <div class="flow-label">3. Client Signs</div>
      <div class="flow-desc">Client clicks link, reads and signs</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c4">📧</div>
      <div class="flow-label">4. PDF Emailed</div>
      <div class="flow-desc">Both parties get signed PDF instantly</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c5">🗄️</div>
      <div class="flow-label">5. Archived</div>
      <div class="flow-desc">Stored in Archive tab forever</div>
    </div>
  </div>

  <div class="tip">
    <strong>Before you start:</strong> Go to <a href="setup.php" style="color:#92400e;font-weight:700">Settings (⚙)</a> and upload your firm logo and confirm your address — it appears on every engagement letter your clients receive.
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- ADDING CLIENTS -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">👤</div>
    <div>
      <div class="section-title">Adding a New Client</div>
      <div class="section-sub">Two ways to add clients — manually or via digital onboarding</div>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Click "+ Add Client" in the dashboard</h4>
        <p>Top right of the screen. A form will appear.</p>
        <span class="tag">Dashboard → top right button</span>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>Use Companies House lookup</h4>
        <p>Click the 🔍 search button next to "Company Name" to auto-fill the company details from the official Companies House register. Saves time and avoids errors.</p>
        <span class="tag">Free — powered by Companies House API</span>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>Fill in client details</h4>
        <p>Name, email, company, entity type (Ltd, Sole Trader, Partnership etc.), and the services you are providing.</p>
        <span class="tag">Name and email are required</span>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Click "Add Client"</h4>
        <p>The client appears instantly in your dashboard with status <span class="bdg bdg-pending">Pending</span>. They are now ready to receive their engagement letter.</p>
      </div>
    </div>
  </div>

  <div class="tip">
    <strong>Tip — Digital Onboarding:</strong> Instead of adding clients manually, send them a branded onboarding form. Click <strong>"📋 Send Onboarding Link"</strong> in the top nav, enter their name and email, and they fill in their own details. Their record is created automatically when they submit.
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- ENGAGEMENT LETTERS -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">✍️</div>
    <div>
      <div class="section-title">Sending an Engagement Letter</div>
      <div class="section-sub">The letter is auto-generated. You review, edit if needed, then send with one click</div>
    </div>
  </div>

  <div class="flow">
    <div class="flow-step">
      <div class="flow-circle c1">🖱️</div>
      <div class="flow-label">Click "Send Link"</div>
      <div class="flow-desc">Blue button on client row</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c2">📝</div>
      <div class="flow-label">Review Letter</div>
      <div class="flow-desc">Edit text, set fee, set deadline</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c2">📨</div>
      <div class="flow-label">Send</div>
      <div class="flow-desc">Client gets email with signing link</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c3">✅</div>
      <div class="flow-label">Client Signs</div>
      <div class="flow-desc">Draws or types signature on any device</div>
    </div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">
      <div class="flow-circle c4">📄</div>
      <div class="flow-label">PDF to Both</div>
      <div class="flow-desc">Signed PDF emailed to you and client</div>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Click "Send Link" on the client row</h4>
        <p>A pop-up appears with the auto-generated engagement letter. Review the text — it's already personalised with the client's name, company and services.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>Edit the letter if needed</h4>
        <p>You can change any text in the letter. Set the <strong>fee</strong> (e.g. "£350 per annum + VAT") and the <strong>deadline</strong> for signing (48 hours is the default).</p>
        <span class="tag">Changes are saved for this client</span>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>Click "Send Engagement Letter"</h4>
        <p>The client receives an email with a secure signing link. The link is unique to them — no login needed. Status changes to <span class="bdg bdg-sent">Sent</span>.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Client signs — you both get the PDF</h4>
        <p>When the client signs (drawn or typed signature), both you and the client receive a signed PDF by email immediately. Status changes to <span class="bdg bdg-signed">Signed</span>.</p>
      </div>
    </div>
  </div>

  <div class="tip">
    <strong>Overdue reminders:</strong> If a client hasn't signed by the deadline, they appear in the <strong>⚠️ Overdue</strong> tab. Click <strong>🔔 Remind</strong> to send a reminder — the tone escalates automatically (gentle → firm → urgent). Use <strong>"Remind All"</strong> to send reminders to all overdue clients at once.
  </div>

  <!-- CLIENT STATUS KEY -->
  <div style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:20px 24px;margin-bottom:8px">
    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:12px">Client Status Meanings</div>
    <div class="badge-row">
      <span class="bdg bdg-pending">Pending</span><span style="font-size:13px;color:var(--muted)"> — Added but no letter sent yet</span>
    </div>
    <div class="badge-row">
      <span class="bdg bdg-sent">Sent</span><span style="font-size:13px;color:var(--muted)"> — Letter sent, waiting for signature</span>
    </div>
    <div class="badge-row">
      <span class="bdg bdg-overdue">Overdue</span><span style="font-size:13px;color:var(--muted)"> — Deadline has passed, not yet signed</span>
    </div>
    <div class="badge-row">
      <span class="bdg bdg-signed">Signed</span><span style="font-size:13px;color:var(--muted)"> — Document signed, PDF archived</span>
    </div>
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- AML -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">🛡️</div>
    <div>
      <div class="section-title">AML — Anti-Money Laundering Records</div>
      <div class="section-sub">Record your Customer Due Diligence checks per client</div>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Click "AML" button on the client row</h4>
        <p>Or go to the <strong>AML Records</strong> tab and find the client. A form opens.</p>
        <span class="tag">All Clients tab → AML button</span>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>Fill in the CDD record</h4>
        <p>Select the ID type (UK Passport, Driving Licence, etc.), enter the last 4 digits of the ID reference, the verification date, and the risk rating.</p>
        <span class="tag">Never store full ID numbers</span>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>Set the risk rating</h4>
        <p>Choose <strong>Low</strong> (standard client), <strong>Medium</strong> (enhanced checks needed), or <strong>High</strong> (requires MLRO sign-off). Add notes for your records.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Save — status updates to Complete</h4>
        <p>AML status changes to <span class="bdg bdg-aml-ok">Complete</span>. The AML Records tab shows a full table of all your CDD records, exportable as CSV.</p>
      </div>
    </div>
  </div>

  <div class="tip">
    <strong>Important:</strong> FirmReady helps you record and store AML checks — but you remain legally responsible for the checks themselves. Always verify identity documents in person or via an approved digital ID service before recording them here.
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- MTD -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">📊</div>
    <div>
      <div class="section-title">MTD — Making Tax Digital Tracker</div>
      <div class="section-sub">Track every client's MTD for Income Tax status and set automated reminders</div>
    </div>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="icon">📅</div>
      <h4>Who is in scope — NOW</h4>
      <p>Self-employed or landlords with income over £50,000 from April 2026</p>
    </div>
    <div class="info-card">
      <div class="icon">📅</div>
      <h4>Who is in scope — 2027</h4>
      <p>Income over £30,000 from April 2027</p>
    </div>
    <div class="info-card">
      <div class="icon">📅</div>
      <h4>Who is in scope — 2028</h4>
      <p>Income over £20,000 from April 2028</p>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Click the 📊 MTD button on the client row</h4>
        <p>Or go to the <strong>📊 MTD Tracker</strong> tab. The MTD record form opens for that client.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>Set the income threshold</h4>
        <p>Select whether the client is in scope Now / 2027 / 2028 / Not in scope. This determines the urgency shown in the tracker.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>Set MTD status and software</h4>
        <p>Record their current status (Not Started / In Progress / Enrolled / Compliant / Exempt) and which accounting software they use (Xero, QuickBooks, Sage, etc.).</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Set reminder intervals</h4>
        <p>Tick which days before the next submission date to send automatic reminders. Reminders go to <strong>both the client and you</strong>. Options: 28, 14, 7, 3, 1 days, and on the day.</p>
        <span class="tag">Requires cron job to be active</span>
      </div>
    </div>
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- DEADLINES -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">📅</div>
    <div>
      <div class="section-title">Statutory Deadline Tracker</div>
      <div class="section-sub">All key filing deadlines calculated automatically — Red, Amber, Green status</div>
    </div>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="icon" style="color:#c0392b">🔴</div>
      <h4>Red — Due within 30 days</h4>
      <p>Urgent — action required immediately</p>
    </div>
    <div class="info-card">
      <div class="icon" style="color:#b45309">🟡</div>
      <h4>Amber — Due in 31–60 days</h4>
      <p>Coming up — plan ahead</p>
    </div>
    <div class="info-card">
      <div class="icon" style="color:#2d6a4f">🟢</div>
      <h4>Green — Due in 60+ days</h4>
      <p>Comfortable — no immediate action</p>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Click 📅 on the client row to set their year end</h4>
        <p>Enter the company's year end date, VAT quarter end, and other dates. The system calculates all deadlines automatically.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>View all deadlines in the 📅 Deadlines tab</h4>
        <p>See every deadline for every client in one table, sorted by urgency. CT600, CT Payment, Companies House, VAT (×4), Payroll RTI, and Self Assessment.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>Mark deadlines as filed or paid</h4>
        <p>Click any deadline to mark it as <strong>Filed</strong> or <strong>Paid</strong>. It moves out of the active list so you can focus on what's still outstanding.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Red items need immediate attention</h4>
        <p>Any deadline in red is due within 30 days. Review these first thing every morning using the Deadlines tab.</p>
        <span class="tag">Check deadlines tab daily</span>
      </div>
    </div>
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- CORRESPONDENCE -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">✉️</div>
    <div>
      <div class="section-title">Correspondence — Professional Letters</div>
      <div class="section-sub">16 ready-made templates for common accountant communications</div>
    </div>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="icon">📋</div>
      <h4>How to send a letter</h4>
      <p>Go to <strong>✉ Correspondence</strong> in the nav → select client → choose template → edit if needed → Send</p>
    </div>
    <div class="info-card">
      <div class="icon">📬</div>
      <h4>Tracking opens</h4>
      <p>Letters show as "Sent" then update to "Read" when the client opens their email. Full audit trail.</p>
    </div>
    <div class="info-card">
      <div class="icon">📁</div>
      <h4>Archive</h4>
      <p>Every letter sent is stored in the correspondence archive. Search by client or date.</p>
    </div>
  </div>

  <div class="tip">
    <strong>Available templates include:</strong> GDPR data subject requests · Companies House filing reminders · VAT registration letters · Payroll setup · MTD enrolment notices · Client welcome letters · Fee increase notices · and more.
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- GDPR DPA -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div class="section-head">
    <div class="section-icon">🔒</div>
    <div>
      <div class="section-title">GDPR Data Processing Agreement (DPA)</div>
      <div class="section-sub">Required by UK GDPR Article 28 — auto-generated and sent for online acknowledgement</div>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Click "DPA" button on the client row</h4>
        <p>Available in the All Clients tab. The system generates a complete UK GDPR Article 28 Data Processing Agreement personalised for that client.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>Client receives and acknowledges online</h4>
        <p>The client gets an email with a link to read and acknowledge the DPA. Their acknowledgement is recorded with timestamp and IP address.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>DPA status updates automatically</h4>
        <p>Once acknowledged, the client row shows <span class="bdg bdg-signed">DPA Signed</span>. You now have a compliant record of data processing consent.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Send DPA to all new clients</h4>
        <p>Best practice: send the engagement letter and DPA together for every new client. It takes 30 seconds and protects your practice.</p>
        <span class="tag">UK GDPR Article 28 requirement</span>
      </div>
    </div>
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- DASHBOARD TABS QUICK REF -->
  <!-- ══════════════════════════════════════════════════════ -->
  <hr>
  <div class="section-head">
    <div class="section-icon">🗂️</div>
    <div>
      <div class="section-title">Dashboard Tabs — Quick Reference</div>
      <div class="section-sub">What each tab shows and when to use it</div>
    </div>
  </div>

  <table class="ref-table">
    <thead>
      <tr><th>Tab</th><th>What it shows</th><th>When to use it</th></tr>
    </thead>
    <tbody>
      <tr><td>All Clients</td><td>Every client with all action buttons</td><td>Daily use — your main working view</td></tr>
      <tr><td>Awaiting Signature</td><td>Clients who have been sent a letter but not yet signed</td><td>Morning check — who still needs to sign?</td></tr>
      <tr><td>⚠️ Overdue</td><td>Clients past their signing deadline (shown in red)</td><td>Immediately — send reminders</td></tr>
      <tr><td>Signed</td><td>All completed, signed clients</td><td>Reference — confirm a client has signed</td></tr>
      <tr><td>AML Records</td><td>Full AML CDD table for all clients</td><td>MLRO review, regulatory audit</td></tr>
      <tr><td>📊 MTD Tracker</td><td>MTD status, threshold and dates for all clients</td><td>Monthly — who needs to enrol or submit?</td></tr>
      <tr><td>📅 Deadlines</td><td>All statutory deadlines RAG status</td><td>Daily — check Red items first</td></tr>
      <tr><td>📁 Archive</td><td>All signed PDFs with download links</td><td>When a client asks for a copy of their signed letter</td></tr>
    </tbody>
  </table>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- BUTTONS QUICK REF -->
  <!-- ══════════════════════════════════════════════════════ -->
  <div style="margin-top:32px">
  <table class="ref-table">
    <thead>
      <tr><th>Button</th><th>What it does</th></tr>
    </thead>
    <tbody>
      <tr><td>Send Link</td><td>Opens the engagement letter editor — edit, set fee, set deadline, then send to client</td></tr>
      <tr><td>🔔 Remind</td><td>Sends an escalating reminder email to the client (tone gets firmer each time)</td></tr>
      <tr><td>💬 WhatsApp</td><td>Opens WhatsApp with a pre-written message and signing link (only shows if phone number is saved)</td></tr>
      <tr><td>📝 Notes</td><td>Add internal notes about this client — not visible to the client. Yellow when notes exist.</td></tr>
      <tr><td>AML</td><td>Open the AML CDD record form for this client</td></tr>
      <tr><td>DPA</td><td>Generate and send a GDPR Data Processing Agreement to this client</td></tr>
      <tr><td>📊 MTD</td><td>Open the MTD tracker record for this client</td></tr>
      <tr><td>📅 Deadlines</td><td>Set the client's year end and VAT dates — all deadlines auto-calculated</td></tr>
      <tr><td>⬇ PDF</td><td>Download the signed PDF for this client (only appears after signing)</td></tr>
      <tr><td>✕ Delete</td><td>Permanently remove this client and all their records</td></tr>
    </tbody>
  </table>
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- SETTINGS -->
  <!-- ══════════════════════════════════════════════════════ -->
  <hr>
  <div class="section-head">
    <div class="section-icon">⚙️</div>
    <div>
      <div class="section-title">Settings — Firm Details &amp; Branding</div>
      <div class="section-sub">Go to ⚙ Settings in the top nav</div>
    </div>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="icon">🖼️</div>
      <h4>Upload Your Logo</h4>
      <p>Your logo appears on every engagement letter. PNG or JPG, max 500KB. Accepted formats: PNG, JPG, GIF, WebP.</p>
    </div>
    <div class="info-card">
      <div class="icon">🏢</div>
      <h4>Firm Details</h4>
      <p>Name, email, phone, address, website and ICO number. These appear on all letters and emails sent to clients.</p>
    </div>
    <div class="info-card">
      <div class="icon">🔑</div>
      <h4>Change Password</h4>
      <p>Enter a new password and save. Minimum 8 characters. Leave blank to keep your current password.</p>
    </div>
  </div>

  <div class="tip">
    <strong>Important:</strong> Always fill in your <strong>ICO registration number</strong> in Settings. It must appear on all client-facing documents under UK data protection law.
  </div>


  <!-- ══════════════════════════════════════════════════════ -->
  <!-- RECOMMENDED DAILY ROUTINE -->
  <!-- ══════════════════════════════════════════════════════ -->
  <hr>
  <div class="section-head">
    <div class="section-icon">📆</div>
    <div>
      <div class="section-title">Recommended Daily Routine</div>
      <div class="section-sub">5 minutes every morning keeps your practice compliant and nothing slips through</div>
    </div>
  </div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">1</div>
      <div class="step-body">
        <h4>Check the ⚠️ Overdue tab</h4>
        <p>Anyone overdue? Click "Remind All" to send reminders to everyone at once.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">2</div>
      <div class="step-body">
        <h4>Check the 📅 Deadlines tab</h4>
        <p>Look at all Red items (due within 30 days). Take action on anything urgent.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">3</div>
      <div class="step-body">
        <h4>Process any new clients</h4>
        <p>Add new clients, send their engagement letter, and send their DPA.</p>
      </div>
    </div>
    <div class="step-card">
      <div class="step-num">4</div>
      <div class="step-body">
        <h4>Update AML for new clients</h4>
        <p>Record the CDD check as soon as you have verified their identity. Don't leave it more than a day.</p>
      </div>
    </div>
  </div>

  <div style="text-align:center;margin-top:40px;padding:32px;background:#fff;border:1px solid var(--border);border-radius:8px">
    <div style="font-size:32px;margin-bottom:12px">💬</div>
    <div style="font-size:16px;font-weight:700;color:var(--navy-dark);margin-bottom:8px">Need help?</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:16px">Contact The Practice support team — we typically respond within a few hours</div>
    <a href="mailto:<?= htmlspecialchars(FR_FIRM_EMAIL) ?>" style="background:var(--navy);color:#fff;padding:12px 28px;border-radius:4px;font-weight:700;font-size:14px;display:inline-block"><?= htmlspecialchars(FR_FIRM_EMAIL) ?></a>
  </div>

</div><!-- /wrap -->
</body>
</html>
