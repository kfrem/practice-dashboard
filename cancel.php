<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout Cancelled — FirmReady</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a1a2e;min-height:100vh;display:flex;flex-direction:column}
:root{
  --navy:#1a3558;--navy-dark:#0f2238;
  --gold:#c9a84c;--gold-dark:#a07830;
  --cream:#f8f7f4;--border:#ddd8cf;--muted:#64748b
}
nav{border-bottom:1px solid var(--border);padding:16px 28px;background:#fff;display:flex;align-items:center}
.logo{font-size:20px;font-weight:800;color:var(--navy)}.logo span{color:var(--gold)}

.hero{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;background:var(--navy-dark);background-image:radial-gradient(circle at 30% 50%,rgba(201,168,76,.06) 0%,transparent 55%)}
.card{background:#fff;border-radius:12px;border:1px solid var(--border);padding:52px 48px;max-width:480px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(26,53,88,.12)}
@media(max-width:560px){.card{padding:36px 24px}}

.icon{width:64px;height:64px;border-radius:50%;background:rgba(100,116,139,.1);border:2px solid rgba(100,116,139,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 22px;font-size:26px}
h1{font-size:26px;font-weight:800;color:var(--navy-dark);margin-bottom:10px;letter-spacing:-.3px}
.sub{font-size:15px;color:var(--muted);line-height:1.65;margin-bottom:32px}

.btn-try{display:inline-block;background:var(--gold);color:var(--navy-dark);border:none;border-radius:6px;padding:14px 32px;font-family:'Segoe UI',Georgia,sans-serif;font-size:15px;font-weight:800;cursor:pointer;text-decoration:none;transition:all .2s;margin-bottom:14px;display:block}
.btn-try:hover{background:var(--gold-dark)}
.btn-ghost{display:block;font-size:13px;color:var(--muted);text-decoration:none;margin-top:4px}
.btn-ghost:hover{color:var(--navy)}

.reassure{background:rgba(26,53,88,.04);border:1px solid var(--border);border-radius:6px;padding:16px 18px;margin-top:24px;text-align:left}
.reassure-title{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.reassure-item{font-size:13px;color:#374151;margin-bottom:6px;display:flex;gap:8px;align-items:center}
.r-dot{color:var(--gold);font-weight:900;font-size:16px;line-height:1}

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
    <div class="icon">↩</div>
    <h1>No problem — come back when you're ready</h1>
    <p class="sub">You haven't been charged anything. Your checkout was cancelled and no payment was taken.</p>

    <a href="subscribe.php" class="btn-try">Try Again →</a>
    <a href="https://practice.finaccord.pro" class="btn-ghost">Return to The Practice homepage</a>

    <div class="reassure">
      <div class="reassure-title">A reminder of what you get</div>
      <div class="reassure-item"><span class="r-dot">·</span> 14-day free trial — no charge today</div>
      <div class="reassure-item"><span class="r-dot">·</span> Flat £12/month — no per-document fees</div>
      <div class="reassure-item"><span class="r-dot">·</span> AML records, MTD tracker &amp; e-signatures</div>
      <div class="reassure-item"><span class="r-dot">·</span> Cancel anytime — no contract</div>
      <div class="reassure-item"><span class="r-dot">·</span> Questions? <a href="mailto:info@kafs-ltd.com" style="color:var(--navy);font-weight:600">info@kafs-ltd.com</a></div>
    </div>
  </div>
</div>

<footer>
  <div class="footer-copy">© <?php echo date('Y'); ?> KAFS Limited &nbsp;·&nbsp; ICO Registration ZC112776</div>
</footer>

</body>
</html>
