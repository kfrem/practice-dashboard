<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to FirmReady — The Practice</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a1a2e;min-height:100vh;display:flex;flex-direction:column}
:root{
  --navy:#1a3558;--navy-dark:#0f2238;
  --gold:#c9a84c;--gold-dark:#a07830;
  --cream:#f8f7f4;--border:#ddd8cf;--muted:#64748b;--success:#2d6a4f
}
nav{border-bottom:1px solid var(--border);padding:16px 28px;background:#fff;display:flex;align-items:center;justify-content:space-between}
.logo{font-size:20px;font-weight:800;color:var(--navy)}.logo span{color:var(--gold)}

.hero{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;background:var(--navy-dark);background-image:radial-gradient(circle at 30% 50%,rgba(201,168,76,.08) 0%,transparent 55%)}
.card{background:#fff;border-radius:12px;border:1px solid var(--border);padding:52px 48px;max-width:520px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(26,53,88,.12)}
@media(max-width:560px){.card{padding:36px 24px}}

.success-icon{width:72px;height:72px;border-radius:50%;background:rgba(45,106,79,.12);border:2px solid rgba(45,106,79,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:30px}
.eyebrow{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:12px}
h1{font-size:28px;font-weight:800;color:var(--navy-dark);margin-bottom:12px;letter-spacing:-.3px}
.sub{font-size:15px;color:var(--muted);line-height:1.65;margin-bottom:32px}

.steps{text-align:left;margin-bottom:32px}
.steps-title{font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:14px}
.step{display:flex;gap:14px;align-items:flex-start;margin-bottom:14px}
.step-num{width:26px;height:26px;border-radius:50%;background:var(--navy);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.step-text{font-size:14px;color:#374151;line-height:1.5}
.step-text strong{color:#1a1a2e}

.btn-dashboard{display:inline-block;background:var(--gold);color:var(--navy-dark);border:none;border-radius:6px;padding:15px 36px;font-family:'Segoe UI',Georgia,sans-serif;font-size:15px;font-weight:800;cursor:pointer;text-decoration:none;transition:all .2s;letter-spacing:.2px}
.btn-dashboard:hover{background:var(--gold-dark)}

.help-note{font-size:12px;color:var(--muted);margin-top:20px}
.help-note a{color:var(--navy);font-weight:600}

footer{border-top:1px solid var(--border);background:#fff;padding:18px;text-align:center}
.footer-copy{font-size:11px;color:#94a3b8}
</style>
</head>
<body>

<nav>
  <div class="logo">The Practice <span>FirmReady</span></div>
</nav>

<div class="hero">
  <div class="card">
    <div class="success-icon">✓</div>
    <div class="eyebrow">Payment confirmed</div>
    <h1>Welcome to FirmReady</h1>
    <p class="sub">Your 14-day free trial has started. You have full access to all features — no charge until your trial ends.</p>

    <div class="steps">
      <div class="steps-title">What happens next</div>
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-text"><strong>Check your inbox</strong> — you'll receive a welcome email within 1–2 minutes with your personal dashboard link and login password.</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-text"><strong>Complete your firm setup</strong> — add your firm name, logo and address. Takes less than 2 minutes.</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-text"><strong>Add your first client &amp; send an engagement letter</strong> — your client signs on any device, no account needed.</div>
      </div>
    </div>

    <div style="background:#f0f9f4;border:1px solid #a7f3d0;border-radius:6px;padding:16px 20px;font-size:14px;color:#065f46;margin-bottom:24px">
      📧 <strong>Your login details are on their way.</strong> Please check your email — including your spam/junk folder.
    </div>
    <div class="help-note">Email not arrived after 5 minutes? Contact <a href="mailto:info@kafs-ltd.com">info@kafs-ltd.com</a> and we'll sort it immediately.</div>
  </div>
</div>

<footer>
  <div class="footer-copy">© <?php echo date('Y'); ?> KAFS Limited &nbsp;·&nbsp; ICO Registration ZC112776</div>
</footer>

</body>
</html>
