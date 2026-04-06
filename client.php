<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Your Engagement Letter — The Practice</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a1a2e;min-height:100vh}
:root{
  --navy:#1a3558;--navy-dark:#0f2238;--navy-mid:#1e3d66;
  --gold:#c9a84c;--gold-light:#e8d5a0;--gold-dark:#a07830;
  --cream:#f8f7f4;--border:#ddd8cf;--text:#2c2c3e;--muted:#64748b;
  --success:#2d6a4f;--danger:#c0392b
}
a{text-decoration:none;color:inherit}

/* NAV */
nav{border-bottom:1px solid var(--border);padding:16px 28px;background:#fff;position:sticky;top:0;z-index:100;display:flex;align-items:center;justify-content:space-between}
.logo{font-size:20px;font-weight:800;color:var(--navy)}.logo span{color:var(--gold)}
.nav-links{display:flex;gap:22px;font-size:13px}
.nav-links a{color:#374151;transition:color .2s}.nav-links a:hover{color:var(--navy)}
.nav-links a.active{color:var(--navy);font-weight:600}
.nav-links a.partner{color:var(--gold);font-weight:600}
.nav-cta{font-size:13px;font-weight:700;background:var(--navy);color:#fff;padding:9px 20px;border-radius:4px}
.secure-tag{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px}

/* LAYOUT */
.container{max-width:720px;margin:0 auto;padding:40px 24px 80px}

/* CARD */
.card{background:#fff;border-radius:8px;border:1px solid var(--border);padding:36px;margin-bottom:20px;animation:fadeUp .35s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

.section-lbl{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:12px}
h2{font-size:24px;font-weight:800;color:var(--navy-dark);margin-bottom:6px;letter-spacing:-.3px}
.sub{font-size:14px;color:var(--muted);margin-bottom:24px;line-height:1.6}

/* STEPS */
.steps-bar{display:flex;align-items:center;margin-bottom:32px}
.s-step{display:flex;align-items:center;gap:7px}
.s-num{width:28px;height:28px;border-radius:50%;background:#e8edf5;color:var(--muted);font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .3s}
.s-step.active .s-num{background:var(--navy);color:#fff}
.s-step.done .s-num{background:var(--success);color:#fff}
.s-label{font-size:12px;color:var(--muted);font-weight:500}
.s-step.active .s-label{color:var(--navy-dark);font-weight:600}
.s-line{flex:1;height:1px;background:var(--border);margin:0 8px}

/* LETTER */
.letter-box{background:var(--cream);border:1px solid var(--border);border-radius:6px;padding:24px;font-size:14px;line-height:1.85;color:var(--text);white-space:pre-wrap;max-height:360px;overflow-y:auto;font-family:'Segoe UI',Georgia,sans-serif}

/* SIG TABS */
.sig-tabs{display:flex;border:1px solid var(--border);border-radius:5px;overflow:hidden;margin-bottom:14px}
.sig-tab{flex:1;padding:10px;background:var(--cream);border:none;font-family:'Segoe UI',Georgia,sans-serif;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;transition:all .2s}
.sig-tab.active{background:var(--navy);color:var(--gold-light)}

.pad-wrap{position:relative;border:1px solid var(--border);border-radius:6px;overflow:hidden;background:#fafaf8}
#sigCanvas{display:block;width:100%;height:175px;cursor:crosshair;touch-action:none}
.sig-line{position:absolute;bottom:40px;left:5%;right:5%;height:1px;background:var(--border);pointer-events:none}
.sig-x{position:absolute;bottom:44px;left:5%;font-size:16px;color:var(--muted);pointer-events:none}
.clear-btn{position:absolute;top:8px;right:10px;background:#fff;border:1px solid var(--border);border-radius:4px;padding:3px 10px;font-size:11px;color:var(--muted);cursor:pointer;font-family:'Segoe UI',Georgia,sans-serif}
.clear-btn:hover{color:var(--danger);border-color:var(--danger)}

.type-sig{display:none}
.type-input{width:100%;font-family:Georgia,serif;font-size:34px;color:var(--navy-dark);border:none;border-bottom:2px solid var(--navy);background:transparent;padding:12px 0;text-align:center;outline:none}
.type-input::placeholder{color:#ddd}

/* CONSENT */
.consent-box{background:#fffcf2;border:1px solid var(--gold-light);border-radius:6px;padding:18px;margin:20px 0}
.consent-label{display:flex;gap:10px;cursor:pointer;align-items:flex-start}
.consent-label input[type=checkbox]{width:17px;height:17px;accent-color:var(--navy);flex-shrink:0;margin-top:3px}
.consent-text{font-size:13px;color:var(--text);line-height:1.6}
.consent-disc{font-size:11px;color:var(--muted);margin-top:10px;padding-top:10px;border-top:1px solid var(--gold-light);font-style:italic}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:14px 24px;border-radius:5px;font-family:'Segoe UI',Georgia,sans-serif;font-size:15px;font-weight:700;cursor:pointer;border:none;transition:all .2s;letter-spacing:.2px}
.btn-primary{background:var(--navy);color:#fff}.btn-primary:hover{background:var(--navy-dark);transform:translateY(-1px)}
.btn-gold{background:var(--gold);color:#fff}.btn-gold:hover{background:var(--gold-dark)}
.btn:disabled{opacity:.4;cursor:not-allowed;transform:none!important}

/* STATES */
.error-wrap{text-align:center;padding:60px 24px}
.error-title{font-size:24px;font-weight:800;color:var(--danger);margin-bottom:10px}
.error-sub{font-size:14px;color:var(--muted);line-height:1.6}
.success-wrap{text-align:center;padding:48px 24px}
.tick-circle{width:72px;height:72px;background:var(--success);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:32px}
.success-title{font-size:26px;font-weight:800;color:var(--navy-dark);margin-bottom:8px}
.success-sub{font-size:15px;color:var(--muted);line-height:1.6}

/* FOOTER */
footer{border-top:1px solid var(--border);padding:20px 28px;text-align:center;font-size:12px;color:var(--muted);background:#fff;margin-top:40px}

@media(max-width:600px){
  .card{padding:20px}
  .s-label{display:none}
  nav{padding:14px 16px}
  .nav-links{display:none}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">The <span>Practice</span></div>
  <div class="nav-links">
    <a href="index.html">Home</a>
    <a href="pricing.html">Pricing</a>
    <a href="portal.html">Client Portal Demo</a>
    <a href="resources.html">Free Resources</a>
    <a href="dashboard.php" class="active">Client Dashboard</a>
  </div>
  <div class="secure-tag">🔒 Secure Document Signing</div>
</nav>

<div class="container">

  <!-- LOADING -->
  <div id="loading" class="card" style="text-align:center;padding:60px 24px">
    <div style="color:var(--muted);font-size:15px;">Loading your document…</div>
  </div>

  <!-- ERROR -->
  <div id="errorState" class="card" style="display:none">
    <div class="error-wrap">
      <div style="font-size:40px;margin-bottom:16px">⚠️</div>
      <div class="error-title" id="errorTitle">Link Not Found</div>
      <div class="error-sub" id="errorMsg">This signing link is invalid or has already been used.<br>Please contact The Practice at <?= htmlspecialchars(FR_FIRM_EMAIL) ?></div>
    </div>
  </div>

  <!-- SIGNING FORM -->
  <div id="signForm" style="display:none">

    <div class="steps-bar">
      <div class="s-step active" id="s1"><div class="s-num">1</div><div class="s-label">Read Document</div></div>
      <div class="s-line"></div>
      <div class="s-step" id="s2"><div class="s-num">2</div><div class="s-label">Sign</div></div>
      <div class="s-line"></div>
      <div class="s-step" id="s3"><div class="s-num">3</div><div class="s-label">Complete</div></div>
    </div>

    <!-- STEP 1 -->
    <div id="step1" class="card">
      <div class="section-lbl">Client Engagement Letter</div>
      <h2 id="docTitle">Engagement Letter</h2>
      <p class="sub">Please read this document carefully before signing below.</p>
      <div class="letter-box" id="letterContent"></div>
      <br>
      <button class="btn btn-primary" onclick="goStep2()">I have read this document — Continue →</button>
    </div>

    <!-- STEP 2 -->
    <div id="step2" class="card" style="display:none">
      <div class="section-lbl">Your Signature</div>
      <h2>Sign the Document</h2>
      <p class="sub">Draw your signature or type your full name below.</p>

      <div class="sig-tabs">
        <button class="sig-tab active" onclick="switchMode('draw',this)">✍️ Draw Signature</button>
        <button class="sig-tab" onclick="switchMode('type',this)">Aa  Type Name</button>
      </div>

      <div id="drawWrap">
        <div class="pad-wrap">
          <canvas id="sigCanvas"></canvas>
          <div class="sig-line"></div>
          <div class="sig-x">✕</div>
          <button class="clear-btn" onclick="clearSig()">Clear</button>
        </div>
        <p style="font-size:12px;color:var(--muted);text-align:center;margin-top:8px">Draw using your mouse or finger</p>
      </div>

      <div class="type-sig" id="typeWrap">
        <input type="text" class="type-input" id="typedName" placeholder="Type your full name">
        <p style="font-size:12px;color:var(--muted);text-align:center;margin-top:8px">Your typed name serves as your electronic signature</p>
      </div>

      <div class="consent-box">
        <label class="consent-label">
          <input type="checkbox" id="consentBox">
          <span class="consent-text">
            I, <strong id="clientNameDisplay">—</strong>, confirm I have read and agree to the terms of this engagement letter. I authorise this as my electronic signature and binding agreement with <strong>The Practice</strong>.
          </span>
        </label>
        <div class="consent-disc">
          By signing, an audit record will be generated including your name, IP address, timestamp and a SHA-256 document hash. This constitutes a Simple Electronic Signature under the Electronic Communications Act 2000. The Practice is registered with the ICO and processes your data in accordance with UK GDPR.
        </div>
      </div>

      <button class="btn btn-gold" id="signBtn" onclick="submitSignature()">⚡ Sign &amp; Submit Document</button>
    </div>

  </div>

  <!-- SUCCESS -->
  <div id="successState" class="card" style="display:none">
    <div class="success-wrap">
      <div class="tick-circle">✓</div>
      <div class="success-title">Document Signed</div>
      <div class="success-sub">
        Thank you. A confirmation email has been sent to you and to The Practice.<br>
        Please keep the email for your records.
      </div>
      <p style="margin-top:20px;font-size:12px;color:var(--muted)">Reference: <span id="refHash" style="font-family:monospace"></span></p>
      <div style="margin-top:24px">
        <a href="index.html" style="font-size:14px;color:var(--navy);font-weight:600">← Return to The Practice website</a>
      </div>
    </div>
  </div>

</div>

<footer>
  <strong>The Practice</strong> · practice.finaccord.pro · <?= htmlspecialchars(FR_FIRM_EMAIL) ?> · GDPR-compliant · UK-hosted · Strictly confidential<br>
  <span style="margin-top:6px;display:block">© 2026 · All rights reserved · A Finaccord professional services programme</span>
</footer>

<script>
const TOKEN = new URLSearchParams(location.search).get('token') || '';
let clientData = null, sigMode = 'draw', sigData = null, docText = '';

// Canvas
const canvas = document.getElementById('sigCanvas');
const ctx = canvas.getContext('2d');
let drawing = false, lx = 0, ly = 0;

function resizeCanvas() {
  const r = canvas.parentElement.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;
  canvas.width = r.width * dpr; canvas.height = 175 * dpr;
  canvas.style.width = r.width + 'px'; canvas.style.height = '175px';
  ctx.scale(dpr, dpr);
  ctx.strokeStyle = '#0f2238'; ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
}
function getPos(e) {
  const r = canvas.getBoundingClientRect();
  const s = e.touches ? e.touches[0] : e;
  return { x: s.clientX - r.left, y: s.clientY - r.top };
}
canvas.addEventListener('mousedown', e => { drawing=true; const p=getPos(e); lx=p.x; ly=p.y; });
canvas.addEventListener('mousemove', e => { if(!drawing)return; const p=getPos(e); ctx.beginPath(); ctx.moveTo(lx,ly); ctx.lineTo(p.x,p.y); ctx.stroke(); lx=p.x; ly=p.y; });
canvas.addEventListener('mouseup', () => drawing=false);
canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing=true; const p=getPos(e); lx=p.x; ly=p.y; }, {passive:false});
canvas.addEventListener('touchmove', e => { e.preventDefault(); if(!drawing)return; const p=getPos(e); ctx.beginPath(); ctx.moveTo(lx,ly); ctx.lineTo(p.x,p.y); ctx.stroke(); lx=p.x; ly=p.y; }, {passive:false});
canvas.addEventListener('touchend', () => drawing=false);
function clearSig() { ctx.clearRect(0,0,canvas.width,canvas.height); }
function switchMode(mode, btn) {
  sigMode = mode;
  document.querySelectorAll('.sig-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('drawWrap').style.display = mode==='draw' ? 'block' : 'none';
  document.getElementById('typeWrap').style.display = mode==='type' ? 'block' : 'none';
}

async function sha256(msg) {
  const buf = new TextEncoder().encode(msg);
  const hash = await crypto.subtle.digest('SHA-256', buf);
  return Array.from(new Uint8Array(hash)).map(b=>b.toString(16).padStart(2,'0')).join('');
}
async function getIP() {
  try { const r = await fetch('https://api.ipify.org?format=json'); const d = await r.json(); return d.ip; } catch { return 'unavailable'; }
}

function buildLetter(c) {
  const today = new Date().toLocaleDateString('en-GB',{day:'numeric',month:'long',year:'numeric'});
  return `CLIENT ENGAGEMENT LETTER

Date: ${today}

To:      ${c.name}
Company: ${c.company || 'N/A'} (${c.type})

Dear ${c.name.split(' ')[0]},

Thank you for choosing The Practice. We are pleased to confirm our appointment as your accountants. This letter sets out the basis on which we will act for you.

SERVICES
We will provide the following services:
• ${c.service}

Any additional services requested will be confirmed in a separate letter of engagement.

OUR RESPONSIBILITIES
We will:
• Carry out the agreed services with reasonable skill, care and diligence
• Maintain the confidentiality of your affairs and comply with UK GDPR
• Communicate clearly on all matters affecting you
• Advise you of any issues that arise in the course of our work

YOUR RESPONSIBILITIES
You agree to:
• Provide us with complete, accurate and timely information
• Inform us promptly of any changes to your circumstances
• Review all documents before submission or publication
• Pay our fees within the agreed payment terms

FEES
Our fees will be agreed and confirmed in writing separately. Invoices are payable within 30 days of issue. We reserve the right to charge interest on overdue amounts in accordance with the Late Payment of Commercial Debts Act 1998.

ANTI-MONEY LAUNDERING (AML)
We are required to comply with the Money Laundering, Terrorist Financing and Transfer of Funds Regulations 2017. We may need to verify your identity and the identity of beneficial owners. You agree to provide all information we reasonably request to satisfy our AML obligations.

MAKING TAX DIGITAL (MTD)
Where applicable, we will assist you in meeting your obligations under HMRC's Making Tax Digital programme. You remain responsible for the accuracy of all information submitted to HMRC.

DATA PROTECTION
The Practice processes your personal data as Data Controller in accordance with UK GDPR and the Data Protection Act 2018. Full details are set out in our Privacy Notice, available on request or at ${<?= json_encode(FR_FIRM_WEBSITE) ?>}.

COMPLAINTS
If you have concerns about our service, please contact us in writing at ${<?= json_encode(FR_FIRM_EMAIL) ?>}. We are committed to resolving all complaints fairly and promptly.

LIMITATION OF LIABILITY
Our liability in connection with the services provided under this engagement shall be limited to the amount of fees paid by you in respect of the specific matter giving rise to the claim.

GOVERNING LAW
This engagement letter is governed by the laws of England and Wales. Any disputes shall be subject to the exclusive jurisdiction of the courts of England and Wales.

Please sign below to confirm your acceptance of these terms.

Yours sincerely,

The Practice
A Finaccord Professional Services Programme
${<?= json_encode(FR_FIRM_EMAIL) ?>} | ${<?= json_encode(FR_FIRM_PHONE) ?>}
${<?= json_encode(FR_FIRM_WEBSITE) ?>}`;
}

async function buildPDF(c, hash, signedAt, ip, method) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit:'mm', format:'a4' });

  doc.setFillColor(26,53,88); doc.rect(0,0,210,26,'F');
  doc.setFont('helvetica','bold'); doc.setFontSize(15); doc.setTextColor(201,168,76);
  doc.text('The Practice', 14, 12);
  doc.setFontSize(9); doc.setTextColor(148,163,184);
  doc.text('Electronic Signature Audit Report — ' + c.service, 14, 20);
  doc.setTextColor(100,116,139);
  doc.text(new Date().toLocaleString('en-GB'), 148, 20);

  doc.setFontSize(11); doc.setFont('helvetica','bold'); doc.setTextColor(15,34,56);
  doc.text('SIGNER INFORMATION', 14, 40);
  doc.setDrawColor(201,168,76); doc.setLineWidth(0.4); doc.line(14,43,196,43);

  const rows = [['Client',c.name],['Company',c.company||'—'],['Entity',c.type],
    ['Signed At',signedAt],['IP Address',ip],['Method',method==='draw'?'Hand drawn':'Typed name']];
  doc.setFont('helvetica','normal'); doc.setFontSize(10);
  let y = 52;
  rows.forEach(([l,v]) => {
    doc.setTextColor(100,116,139); doc.text(l+':',14,y);
    doc.setTextColor(15,34,56); doc.text(String(v),60,y); y+=8;
  });

  y+=4; doc.setDrawColor(200,200,200); doc.line(14,y,196,y); y+=8;
  doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(30,61,102);
  doc.text('SHA-256 DOCUMENT HASH', 14, y); y+=7;
  doc.setFont('courier','normal'); doc.setFontSize(8); doc.setTextColor(15,34,56);
  doc.text(doc.splitTextToSize(hash,182), 14, y); y+=14;

  doc.setDrawColor(200,200,200); doc.line(14,y,196,y); y+=8;
  doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(30,61,102);
  doc.text('SIGNED DOCUMENT', 14, y); y+=7;
  doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(60,70,80);
  const lines = doc.splitTextToSize(docText, 182);
  doc.text(lines.slice(0, Math.floor((265-y)/4)), 14, y);

  const pg = doc.internal.getNumberOfPages();
  for (let i=1;i<=pg;i++) {
    doc.setPage(i); doc.setFillColor(26,53,88); doc.rect(0,285,210,12,'F');
    doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(100,116,139);
    doc.text('The Practice — Simple Electronic Signature Audit Report | A Finaccord Professional Services Programme', 14, 292);
    doc.text('Page '+i+' of '+pg, 190, 292, {align:'right'});
  }
  return doc.output('datauristring').split(',')[1];
}

function setStep(n) {
  for (let i=1;i<=3;i++) {
    const el = document.getElementById('s'+i);
    el.classList.remove('active','done');
    if (i < n) { el.classList.add('done'); el.querySelector('.s-num').textContent = '✓'; }
    else if (i === n) { el.classList.add('active'); }
    else { el.querySelector('.s-num').textContent = String(i); }
  }
}

function goStep2() {
  document.getElementById('step1').style.display = 'none';
  document.getElementById('step2').style.display = 'block';
  setStep(2);
  setTimeout(resizeCanvas, 80);
}

async function submitSignature() {
  if (!document.getElementById('consentBox').checked) {
    alert('Please tick the consent box to confirm your agreement.'); return;
  }
  if (sigMode === 'draw') {
    const blank = !ctx.getImageData(0,0,canvas.width,canvas.height).data.some(x=>x!==0);
    if (blank) { alert('Please draw your signature before submitting.'); return; }
    sigData = canvas.toDataURL('image/png');
  } else {
    const typed = document.getElementById('typedName').value.trim();
    if (!typed) { alert('Please type your full name as your signature.'); return; }
    sigData = typed;
  }
  const btn = document.getElementById('signBtn');
  btn.textContent = 'Processing…'; btn.disabled = true;
  setStep(3);

  const ts = new Date().toISOString();
  const ip = await getIP();
  const hash = await sha256(clientData.name+'|'+clientData.id+'|'+docText+'|'+ts+'|'+ip);
  const pdfB64 = await buildPDF(clientData, hash, new Date(ts).toLocaleString('en-GB'), ip, sigMode);

  try {
    const res = await fetch('api.php?action=sign', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({token:TOKEN, sig_data:sigData, sig_method:sigMode, hash, pdf:pdfB64})
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('signForm').style.display = 'none';
      document.getElementById('successState').style.display = 'block';
      document.getElementById('refHash').textContent = hash.substring(0,32)+'…';
    } else {
      alert('Error: '+(data.error||'Could not save. Please try again.'));
      btn.textContent = '⚡ Sign & Submit Document'; btn.disabled = false;
    }
  } catch(e) {
    alert('Network error. Please check your connection and try again.');
    btn.textContent = '⚡ Sign & Submit Document'; btn.disabled = false;
  }
}

async function init() {
  if (!TOKEN) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    return;
  }
  try {
    const res = await fetch('api.php?action=get_by_token', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({token:TOKEN})
    });
    const data = await res.json();
    if (!data.success) {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('errorTitle').textContent = data.error || 'Link Not Found';
      document.getElementById('errorState').style.display = 'block';
      return;
    }
    clientData = data.client;
    // Use custom letter from accountant if they edited it, otherwise auto-generate
    docText = clientData.custom_letter && clientData.custom_letter.length > 100
      ? clientData.custom_letter
      : buildLetter(clientData);
    document.getElementById('letterContent').textContent = docText;
    document.getElementById('docTitle').textContent = clientData.service + ' — Engagement Letter';
    document.getElementById('clientNameDisplay').textContent = clientData.name;
    document.getElementById('loading').style.display = 'none';
    document.getElementById('signForm').style.display = 'block';
  } catch(e) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('errorTitle').textContent = 'Connection Error';
    document.getElementById('errorMsg').textContent = 'Could not load your document. Please try again or contact '+<?= json_encode(FR_FIRM_EMAIL) ?>+'.';
    document.getElementById('errorState').style.display = 'block';
  }
}

window.addEventListener('resize', resizeCanvas);
init();
</script>
</body>
</html>
