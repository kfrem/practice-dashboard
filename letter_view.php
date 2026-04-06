<?php
require_once __DIR__ . '/config.php';
$token = trim($_GET['token'] ?? '');
if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(404);
    die('Letter not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Letter from <?= htmlspecialchars(FR_FIRM_NAME) ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', Georgia, sans-serif;
    background: #f0ede6;
    color: #1a2433;
    min-height: 100vh;
  }
  .page-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 32px 16px 80px;
  }
  /* ── letterhead ── */
  .letter-card {
    background: #fff;
    border: 1px solid #ddd8cf;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
  }
  .lh-header {
    padding: 36px 48px 28px;
    border-bottom: 3px solid #1a3558;
    display: flex;
    align-items: flex-start;
    gap: 24px;
  }
  .lh-logo { max-height: 72px; max-width: 180px; object-fit: contain; }
  .lh-logo-placeholder {
    font-size: 1.5rem; font-weight: 700;
    color: #1a3558; letter-spacing: .5px;
    flex: 1;
  }
  .lh-firm-details {
    text-align: right;
    font-size: .8rem;
    color: #555;
    line-height: 1.65;
    flex-shrink: 0;
  }
  .lh-tagline {
    font-size: .75rem;
    color: #888;
    margin-top: 4px;
    font-style: italic;
  }

  /* ── letter body ── */
  .letter-body {
    padding: 40px 48px 36px;
  }
  .letter-date {
    text-align: right;
    margin-bottom: 28px;
    color: #555;
    font-size: .9rem;
  }
  .letter-content {
    white-space: pre-wrap;
    line-height: 1.75;
    font-size: .95rem;
    color: #1a2433;
  }

  /* ── firm sig ── */
  .firm-sig-block {
    margin-top: 36px;
    padding-top: 24px;
    border-top: 1px solid #eee;
    display: flex;
    align-items: flex-end;
    gap: 20px;
  }
  .firm-sig-block img {
    max-height: 70px;
    max-width: 200px;
    object-fit: contain;
  }
  .firm-sig-name {
    font-size: .88rem;
    color: #444;
    line-height: 1.5;
  }

  /* ── footer bar ── */
  .lh-footer {
    background: #f8f7f4;
    border-top: 1px solid #ddd8cf;
    padding: 14px 48px;
    font-size: .75rem;
    color: #888;
    line-height: 1.6;
  }

  /* ── actions ── */
  .action-bar {
    margin-top: 28px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
  }
  .btn {
    padding: 11px 24px;
    border-radius: 4px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: opacity .15s;
  }
  .btn:disabled { opacity: .5; cursor: default; }
  .btn-primary { background: #1a3558; color: #fff; }
  .btn-primary:hover:not(:disabled) { background: #0f2238; }
  .btn-gold { background: #c9a84c; color: #fff; }
  .btn-gold:hover:not(:disabled) { background: #a07830; }
  .btn-outline {
    background: transparent;
    border: 1.5px solid #1a3558;
    color: #1a3558;
  }
  .btn-outline:hover { background: #f0f4f8; }

  .status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: .8rem;
    font-weight: 600;
  }
  .badge-read { background: #d1ecf1; color: #0c5460; }
  .badge-acknowledged { background: #d4edda; color: #155724; }

  .ack-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    padding: 14px 20px;
    color: #155724;
    font-size: .92rem;
    margin-top: 16px;
    display: none;
  }

  #loading { text-align: center; padding: 80px 0; color: #888; font-size: 1.1rem; }
  #error-msg { text-align: center; padding: 80px 0; color: #c0392b; }

  @media (max-width: 600px) {
    .lh-header { padding: 24px 20px 20px; flex-direction: column; }
    .lh-firm-details { text-align: left; }
    .letter-body { padding: 28px 20px 24px; }
    .lh-footer { padding: 12px 20px; }
  }
</style>
</head>
<body>

<div class="page-wrap">
  <div id="loading">Loading your letter&hellip;</div>
  <div id="error-msg" style="display:none;">Letter not found or has expired.</div>

  <div id="letter-wrap" style="display:none;">
    <div class="letter-card">
      <!-- Letterhead header -->
      <div class="lh-header" id="lhHeader">
        <div id="logoArea"></div>
        <div class="lh-firm-details" id="firmDetails"></div>
      </div>

      <!-- Letter body -->
      <div class="letter-body">
        <div class="letter-date" id="letterDate"></div>
        <div class="letter-content" id="letterContent"></div>
        <!-- Firm signature -->
        <div class="firm-sig-block" id="firmSigBlock" style="display:none;">
          <div>
            <img id="firmSigImg" src="" alt="Firm Signature">
            <div class="firm-sig-name" id="firmSigName"></div>
          </div>
        </div>
      </div>

      <!-- Letterhead footer -->
      <div class="lh-footer" id="lhFooter" style="display:none;"></div>
    </div>

    <!-- Actions -->
    <div class="action-bar">
      <button class="btn btn-primary" onclick="window.print()">🖨 Print / Save PDF</button>
      <button class="btn btn-gold" id="ackBtn" style="display:none;" onclick="acknowledgeIt()">✓ Acknowledge Receipt</button>
      <span id="statusBadge"></span>
    </div>
    <div class="ack-success" id="ackSuccess">
      ✅ Thank you — your acknowledgement has been recorded.
    </div>
  </div>
</div>

<script>
const TOKEN = <?= json_encode($token) ?>;
const API   = <?= json_encode(FR_BASE_URL . '/api.php') ?>;

let letterData = null;

async function api(action, body = {}) {
  const res = await fetch(API + '?action=' + action, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
  return res.json();
}

function fmt(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function init() {
  // 1. Load letter
  const r = await api('get_letter_by_token', { token: TOKEN });
  if (!r.ok) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('error-msg').style.display = 'block';
    return;
  }

  letterData = r.letter;
  const lh   = r.letterhead || {};
  const sig  = r.firm_sig   || '';

  // 2. Mark as read (fire and forget)
  if (letterData.status === 'sent') {
    api('mark_letter_read', { token: TOKEN });
    letterData.status = 'read';
  }

  // 3. Build letterhead header
  buildHeader(lh);

  // 4. Letter date
  document.getElementById('letterDate').textContent = fmt(letterData.created_at);

  // 5. Letter body
  document.getElementById('letterContent').textContent = letterData.body || '';

  // 6. Firm signature
  if (sig) {
    document.getElementById('firmSigImg').src = sig;
    document.getElementById('firmSigName').innerHTML =
      '<strong>' + esc(r.firm_name || '') + '</strong><br>' +
      esc(r.firm_email || '');
    document.getElementById('firmSigBlock').style.display = 'flex';
  }

  // 7. Footer
  if (lh.footer_text || lh.disclaimer) {
    const ft = document.getElementById('lhFooter');
    ft.style.display = 'block';
    ft.innerHTML = esc(lh.footer_text || '') +
      (lh.disclaimer ? '<br><em>' + esc(lh.disclaimer) + '</em>' : '');
  }

  // 8. Acknowledge button
  if (letterData.requires_ack && letterData.status !== 'acknowledged') {
    document.getElementById('ackBtn').style.display = 'inline-block';
  }
  if (letterData.status === 'acknowledged') {
    showBadge('acknowledged');
  } else if (letterData.status === 'read') {
    showBadge('read');
  }

  // 9. Show
  document.getElementById('loading').style.display = 'none';
  document.getElementById('letter-wrap').style.display = 'block';
}

function buildHeader(lh) {
  const logoArea    = document.getElementById('logoArea');
  const firmDetails = document.getElementById('firmDetails');

  // Primary colour border
  const header = document.getElementById('lhHeader');
  if (lh.primary_colour) {
    header.style.borderBottomColor = lh.primary_colour;
  }

  // Background colour
  if (lh.bg_colour) {
    header.style.background = lh.bg_colour;
  }

  // Logo or firm name
  if (lh.logo_b64) {
    logoArea.innerHTML = '<img class="lh-logo" src="' + lh.logo_b64 + '" alt="Firm Logo">';
  } else {
    logoArea.innerHTML =
      '<div class="lh-logo-placeholder"' +
      (lh.primary_colour ? ' style="color:' + esc(lh.primary_colour) + '"' : '') + '>' +
      esc(lh.firm_name || <?= json_encode(FR_FIRM_NAME) ?>) + '</div>' +
      (lh.tagline ? '<div class="lh-tagline">' + esc(lh.tagline) + '</div>' : '');
  }

  // Right: firm contact details
  const addr    = lh.firm_address || <?= json_encode(FR_FIRM_ADDRESS) ?>;
  const phone   = lh.firm_phone   || <?= json_encode(FR_FIRM_PHONE) ?>;
  const email   = lh.firm_email   || <?= json_encode(FR_FIRM_EMAIL) ?>;
  const website = lh.firm_website || <?= json_encode(FR_FIRM_WEBSITE) ?>;
  const ico     = lh.ico_number   || <?= json_encode(FR_ICO_NUMBER) ?>;

  let html = esc(addr) + '<br>';
  if (phone)   html += 'Tel: ' + esc(phone) + '<br>';
  if (email)   html += 'Email: ' + esc(email) + '<br>';
  if (website) html += '<a href="' + esc(website) + '" style="color:inherit">' + esc(website) + '</a><br>';
  if (ico)     html += 'ICO Reg: ' + esc(ico);

  // Social links
  const socials = [];
  if (lh.social_twitter)  socials.push('<a href="https://twitter.com/'  + esc(lh.social_twitter)  + '" style="color:inherit">Twitter</a>');
  if (lh.social_linkedin) socials.push('<a href="https://linkedin.com/company/' + esc(lh.social_linkedin) + '" style="color:inherit">LinkedIn</a>');
  if (socials.length) html += '<br>' + socials.join(' · ');

  firmDetails.innerHTML = html;
}

function showBadge(status) {
  const el = document.getElementById('statusBadge');
  if (status === 'acknowledged') {
    el.innerHTML = '<span class="status-badge badge-acknowledged">✓ Acknowledged</span>';
  } else if (status === 'read') {
    el.innerHTML = '<span class="status-badge badge-read">👁 Opened</span>';
  }
}

async function acknowledgeIt() {
  const btn = document.getElementById('ackBtn');
  btn.disabled = true;
  btn.textContent = 'Recording…';
  const r = await api('acknowledge_letter', { token: TOKEN });
  if (r.ok) {
    btn.style.display = 'none';
    document.getElementById('ackSuccess').style.display = 'block';
    showBadge('acknowledged');
  } else {
    btn.disabled = false;
    btn.textContent = '✓ Acknowledge Receipt';
    alert('Could not record acknowledgement. Please try again.');
  }
}

function esc(s) {
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}

init();
</script>
</body>
</html>
