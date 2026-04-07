<?php
require_once __DIR__ . '/config.php';
$token = trim($_GET['token'] ?? '');
if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(404);
    die('Invalid onboarding link.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Client Details — <?= htmlspecialchars(FR_FIRM_NAME) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Georgia, sans-serif; background: #f8f7f4; color: #1a2433; min-height: 100vh; }
.wrap  { max-width: 600px; margin: 0 auto; padding: 48px 16px 80px; }
.card  { background: #fff; border: 1px solid #ddd8cf; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.card-head { background: #1a3558; padding: 28px 32px; }
.card-head h1 { font-size: 22px; font-weight: 800; color: #fff; }
.card-head p  { font-size: 13px; color: #c9a84c; margin-top: 4px; }
.card-body { padding: 32px; }
.fg { margin-bottom: 18px; }
.fg label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-bottom: 6px; }
.fg input, .fg select, .fg textarea {
  width: 100%; padding: 10px 12px; border: 1.5px solid #ddd8cf; border-radius: 4px;
  font-family: 'Segoe UI', Georgia, sans-serif; font-size: 14px; color: #1a2433;
  background: #fafaf8; outline: none; transition: border-color .15s;
}
.fg input:focus, .fg select:focus, .fg textarea:focus { border-color: #1a3558; background: #fff; }
.fg textarea { min-height: 90px; resize: vertical; }
.fg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.svc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.svc-cb { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; padding: 8px 10px; border: 1px solid #ddd8cf; border-radius: 4px; background: #fafaf8; }
.svc-cb:hover { background: #f0f4f8; }
.svc-cb input { width: 15px; height: 15px; flex-shrink: 0; }
.btn { display: block; width: 100%; padding: 14px; border-radius: 4px; font-family: 'Segoe UI', Georgia, sans-serif; font-size: 15px; font-weight: 700; cursor: pointer; border: none; text-align: center; margin-top: 8px; }
.btn-primary { background: #1a3558; color: #fff; }
.btn-primary:hover { background: #0f2238; }
.btn-primary:disabled { opacity: .5; cursor: default; }
.alert { padding: 12px 16px; border-radius: 4px; font-size: 13px; margin-bottom: 16px; }
.alert-err { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.success-screen { text-align: center; padding: 48px 32px; }
.success-icon { font-size: 56px; margin-bottom: 16px; }
.success-title { font-size: 22px; font-weight: 800; color: #1a3558; margin-bottom: 10px; }
.success-msg { font-size: 14px; color: #64748b; line-height: 1.6; }
#loading { text-align: center; padding: 60px; color: #888; }
@media (max-width: 540px) { .fg-row, .svc-grid { grid-template-columns: 1fr; } .card-body { padding: 24px 20px; } }
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="card-head">
      <h1><?= htmlspecialchars(FR_FIRM_NAME) ?></h1>
      <p>New Client Details Form</p>
    </div>
    <div class="card-body" id="cardBody">
      <div id="loading">Loading your form…</div>
    </div>
  </div>
</div>

<script>
const TOKEN = <?= json_encode($token) ?>;
const API   = <?= json_encode(FR_BASE_URL . '/api.php') ?>;

async function post(action, body) {
  const r = await fetch(API + '?action=' + action, {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
  });
  return r.json();
}

async function init() {
  const d = await post('get_onboard_by_token', { token: TOKEN });
  const body = document.getElementById('cardBody');
  if (!d.ok) {
    body.innerHTML = '<div class="alert alert-err">This onboarding link is not found or has already been used. Please contact your accountant for a new link.</div>';
    return;
  }
  const { name, email } = d.client;
  body.innerHTML = `
    <p style="font-size:14px;color:#555;margin-bottom:24px">
      Hello <strong>${esc(name)}</strong>, please complete the short form below so we have everything we need to get started.
      This takes less than 2 minutes.
    </p>
    <div id="formAlert"></div>

    <div class="fg">
      <label>Your Full Name *</label>
      <input type="text" id="fName" value="${esc(name)}" required>
    </div>
    <div class="fg">
      <label>Your Email Address</label>
      <input type="email" id="fEmail" value="${esc(email)}" readonly style="background:#f1f1ee;color:#888">
    </div>
    <div class="fg-row">
      <div class="fg">
        <label>Company / Trading Name</label>
        <input type="text" id="fCompany" placeholder="Smith Ltd or your name if sole trader">
      </div>
      <div class="fg">
        <label>Phone Number</label>
        <input type="tel" id="fPhone" placeholder="07700 900000">
      </div>
    </div>
    <div class="fg">
      <label>Entity Type *</label>
      <select id="fType">
        <option>Ltd Company</option>
        <option>Sole Trader</option>
        <option>Partnership</option>
        <option>LLP</option>
        <option>Individual</option>
        <option>Charity</option>
        <option>Trust</option>
      </select>
    </div>
    <div class="fg">
      <label>Services Required <span style="text-transform:none;font-weight:400;font-size:11px">(tick all that apply)</span></label>
      <div class="svc-grid">
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Self Assessment Tax Return"> Self Assessment Tax Return</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Company Accounts &amp; Corporation Tax"> Company Accounts &amp; Corp Tax</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="VAT Returns"> VAT Returns</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Bookkeeping"> Bookkeeping</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Payroll"> Payroll</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Making Tax Digital (MTD) Support"> MTD Support</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Management Accounts"> Management Accounts</label>
        <label class="svc-cb"><input type="checkbox" name="osvc" value="Full Accountancy Package"> Full Accountancy Package</label>
      </div>
    </div>
    <div class="fg">
      <label>Anything else you'd like us to know? <span style="text-transform:none;font-weight:400;font-size:11px">(optional)</span></label>
      <textarea id="fNotes" placeholder="e.g. previous accountant, tax year end, specific deadlines, current software used…"></textarea>
    </div>
    <button class="btn btn-primary" id="submitBtn" onclick="submitForm()">Submit My Details</button>
  `;
}

async function submitForm() {
  const name    = document.getElementById('fName').value.trim();
  const company = document.getElementById('fCompany').value.trim();
  const phone   = document.getElementById('fPhone').value.trim();
  const type    = document.getElementById('fType').value;
  const notes   = document.getElementById('fNotes').value.trim();
  const svcs    = [...document.querySelectorAll('input[name="osvc"]:checked')].map(cb => cb.value);
  const alert   = document.getElementById('formAlert');

  if (!name) { alert.innerHTML = '<div class="alert alert-err">Please enter your full name.</div>'; return; }

  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Submitting…';

  const d = await post('submit_onboard', {
    token: TOKEN,
    company, phone, type, notes,
    service: svcs.join('\n')
  });

  if (d.success) {
    document.getElementById('cardBody').innerHTML = `
      <div class="success-screen">
        <div class="success-icon">✅</div>
        <div class="success-title">Thank you, ${esc(name)}!</div>
        <div class="success-msg">
          Your details have been submitted to <strong><?= htmlspecialchars(FR_FIRM_NAME) ?></strong>.<br><br>
          We will review your information and be in touch shortly to confirm next steps,
          including sending you an engagement letter to review and sign digitally.<br><br>
          If you have any urgent questions, please contact us at
          <a href="mailto:<?= FR_FIRM_EMAIL ?>" style="color:#1a3558"><?= FR_FIRM_EMAIL ?></a>
          or call <?= FR_FIRM_PHONE ?>.
        </div>
      </div>`;
  } else {
    btn.disabled = false; btn.textContent = 'Submit My Details';
    document.getElementById('formAlert').innerHTML = '<div class="alert alert-err">' + esc(d.error || 'Something went wrong. Please try again.') + '</div>';
  }
}

function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

init();
</script>
</body>
</html>
