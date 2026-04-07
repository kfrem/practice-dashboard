<?php
require_once __DIR__ . '/config.php';
$error_msg = isset($_GET['error']) ? htmlspecialchars(urldecode($_GET['error'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subscribe — FirmReady by The Practice</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Georgia,sans-serif;background:#f8f7f4;color:#1a1a2e;min-height:100vh}
:root{
  --navy:#1a3558;--navy-dark:#0f2238;--navy-mid:#1e3d66;
  --gold:#c9a84c;--gold-light:#e8d5a0;--gold-dark:#a07830;
  --cream:#f8f7f4;--border:#ddd8cf;--muted:#64748b;
  --success:#2d6a4f;--danger:#c0392b
}
a{text-decoration:none;color:inherit}

/* NAV */
nav{border-bottom:1px solid var(--border);padding:16px 28px;background:#fff;display:flex;align-items:center;justify-content:space-between}
.logo{font-size:20px;font-weight:800;color:var(--navy)}.logo span{color:var(--gold)}
.nav-links{display:flex;gap:22px;font-size:13px}
.nav-links a{color:#374151;transition:color .2s}.nav-links a:hover{color:var(--navy)}
.nav-links a.active{color:var(--navy);font-weight:700;border-bottom:2px solid var(--gold);padding-bottom:2px}
.nav-links a.partner{color:var(--gold);font-weight:600}
.nav-right a{font-size:13px;color:var(--muted)}
.nav-right a:hover{color:var(--navy)}

/* HERO */
.hero{background:var(--navy-dark);background-image:radial-gradient(circle at 20% 60%,rgba(201,168,76,.08) 0%,transparent 55%),radial-gradient(circle at 80% 20%,rgba(201,168,76,.05) 0%,transparent 45%);padding:64px 24px 56px;text-align:center}
.hero-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:16px}
.hero h1{font-size:44px;font-weight:800;color:#fff;letter-spacing:-.5px;line-height:1.15;margin-bottom:16px}
.hero h1 span{color:var(--gold)}
.hero-sub{font-size:17px;color:#94a3b8;max-width:580px;margin:0 auto 40px;line-height:1.65}
.trust-bar{display:flex;gap:28px;justify-content:center;flex-wrap:wrap;margin-top:16px}
.trust-item{display:flex;align-items:center;gap:7px;font-size:12px;color:#94a3b8}
.trust-dot{width:7px;height:7px;border-radius:50%;background:var(--gold);flex-shrink:0}

/* MAIN LAYOUT */
.main{max-width:960px;margin:0 auto;padding:52px 24px 80px;display:grid;grid-template-columns:1fr 400px;gap:44px;align-items:start}
@media(max-width:768px){.main{grid-template-columns:1fr;gap:32px}}

/* FEATURES SIDE */
.features-head{font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold);margin-bottom:20px}
.feature-list{display:flex;flex-direction:column;gap:18px;margin-bottom:36px}
.feature{display:flex;gap:14px;align-items:flex-start}
.feature-icon{width:40px;height:40px;border-radius:8px;background:rgba(26,53,88,.08);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px}
.feature-title{font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:3px}
.feature-desc{font-size:13px;color:var(--muted);line-height:1.55}

.compare{background:#fff;border:1px solid var(--border);border-radius:8px;padding:22px;margin-top:4px}
.compare-title{font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:14px}
.compare-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0ece6;font-size:13px}
.compare-row:last-child{border-bottom:none}
.compare-label{color:#374151}
.compare-them{color:var(--danger);font-weight:600}
.compare-us{color:var(--success);font-weight:700}

/* PRICING CARD */
.pricing-card{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(26,53,88,.07);position:sticky;top:24px}
.card-header{background:var(--navy);padding:28px 28px 24px;text-align:center}
.plan-badge{display:inline-block;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.35);border-radius:20px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold);padding:4px 14px;margin-bottom:14px}
.plan-name{font-size:22px;font-weight:800;color:#fff;margin-bottom:8px}
.price-row{display:flex;align-items:baseline;justify-content:center;gap:4px;margin-bottom:6px}
.price-currency{font-size:22px;font-weight:700;color:var(--gold);align-self:flex-start;margin-top:6px}
.price-amount{font-size:52px;font-weight:800;color:#fff;line-height:1;font-family:Georgia,serif}
.price-period{font-size:14px;color:#94a3b8;margin-bottom:4px}
.price-vat{font-size:11px;color:#64748b;margin-bottom:2px}
.trial-badge{display:inline-block;background:rgba(45,106,79,.25);border:1px solid rgba(45,106,79,.4);border-radius:4px;font-size:12px;font-weight:700;color:#86efac;padding:4px 12px;margin-top:10px}

.card-body{padding:28px}

.form-group{margin-bottom:16px}
.form-label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;letter-spacing:.3px}
.form-input{width:100%;border:1.5px solid var(--border);border-radius:5px;padding:12px 14px;font-family:'Segoe UI',Georgia,sans-serif;font-size:14px;color:#1a1a2e;background:#fff;outline:none;transition:border-color .2s}
.form-input:focus{border-color:var(--navy)}
.form-input::placeholder{color:#94a3b8}

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}

.btn-subscribe{width:100%;background:var(--gold);color:var(--navy-dark);border:none;border-radius:6px;padding:16px;font-family:'Segoe UI',Georgia,sans-serif;font-size:16px;font-weight:800;cursor:pointer;transition:all .2s;letter-spacing:.2px;margin-top:6px}
.btn-subscribe:hover{background:var(--gold-dark);transform:translateY(-1px);box-shadow:0 4px 12px rgba(201,168,76,.35)}
.btn-subscribe:active{transform:translateY(0)}
.btn-subscribe:disabled{opacity:.6;cursor:not-allowed;transform:none}

.card-includes{margin:18px 0 0;padding:18px 0 0;border-top:1px solid var(--border)}
.includes-title{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:12px}
.include-item{display:flex;align-items:center;gap:9px;font-size:13px;color:#374151;margin-bottom:9px}
.include-check{width:18px;height:18px;border-radius:50%;background:rgba(45,106,79,.1);border:1.5px solid rgba(45,106,79,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:10px;color:var(--success);font-weight:900}

.card-footer{padding:18px 28px;background:var(--cream);border-top:1px solid var(--border);text-align:center}
.secure-note{font-size:11px;color:var(--muted);display:flex;align-items:center;justify-content:center;gap:6px}
.secure-note svg{opacity:.5}

.error-msg{background:#fef2f2;border:1px solid #fecaca;border-radius:5px;padding:12px 14px;font-size:13px;color:var(--danger);margin-top:12px;display:none}

/* TESTIMONIAL */
.testimonial{background:rgba(26,53,88,.04);border:1px solid var(--border);border-left:3px solid var(--gold);border-radius:0 6px 6px 0;padding:18px 20px;margin-top:28px}
.testimonial-text{font-size:14px;color:#374151;line-height:1.6;font-style:italic;margin-bottom:10px}
.testimonial-author{font-size:12px;font-weight:700;color:var(--navy)}
.testimonial-role{font-size:11px;color:var(--muted)}

/* FOOTER */
footer{border-top:1px solid var(--border);background:#fff;padding:24px;text-align:center}
.footer-links{display:flex;gap:24px;justify-content:center;margin-bottom:10px;flex-wrap:wrap}
.footer-links a{font-size:12px;color:var(--muted)}.footer-links a:hover{color:var(--navy)}
.footer-copy{font-size:11px;color:#94a3b8}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">The Practice <span>FirmReady</span></div>
  <div class="nav-links">
    <a href="https://practice.finaccord.pro">Home</a>
    <a href="#features">Features</a>
    <a href="#" class="active">Pricing</a>
  </div>
  <div class="nav-right">
    <a href="dashboard.php">Already subscribed? Sign in →</a>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-eyebrow">FirmReady — Built for UK Accounting Practices</div>
  <h1>Everything your practice needs.<br><span>One flat price.</span></h1>
  <p class="hero-sub">Engagement letters, e-signatures, AML records, MTD tracking, statutory deadlines, correspondence and client onboarding — built specifically for UK accounting firms of all sizes.</p>
  <div class="trust-bar">
    <div class="trust-item"><div class="trust-dot"></div> No per-document charges</div>
    <div class="trust-item"><div class="trust-dot"></div> ICO Registered ZC112776</div>
    <div class="trust-item"><div class="trust-dot"></div> UK GDPR compliant</div>
    <div class="trust-item"><div class="trust-dot"></div> Cancel anytime</div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="main" id="features">

  <!-- LEFT: FEATURES -->
  <div>
    <div class="features-head">Everything included — no add-ons, no extras</div>
    <div class="feature-list">
      <div class="feature">
        <div class="feature-icon">✍️</div>
        <div>
          <div class="feature-title">Engagement Letters &amp; E-Signatures</div>
          <div class="feature-desc">Send legally compliant engagement letters in seconds. Auto-generated from client data. Clients sign on any device — draw or type their name. No account needed. SHA-256 audit trail on every document.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">🛡️</div>
        <div>
          <div class="feature-title">AML Customer Due Diligence Records</div>
          <div class="feature-desc">Record ID type, reference number, risk rating (Low/Medium/High), verified date and notes per client. Full CDD table with status badges. Meets MLRO record-keeping requirements for UK accountants.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">📊</div>
        <div>
          <div class="feature-title">MTD for Income Tax Tracker</div>
          <div class="feature-desc">Track every client's Making Tax Digital status, income threshold band (Now/2027/2028/Not in scope), software, enrolment date and next submission date. Automated reminders sent to client and accountant at 28, 14, 7, 3 and 1 day intervals.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">📅</div>
        <div>
          <div class="feature-title">Statutory Deadline Tracker</div>
          <div class="feature-desc">Auto-calculates CT600, Corporation Tax payment, Companies House accounts, VAT returns (all 4 quarters), Payroll RTI and Self Assessment deadlines per client. RAG status — Red, Amber, Green — so you never miss a deadline.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">✉️</div>
        <div>
          <div class="feature-title">Professional Correspondence Hub</div>
          <div class="feature-desc">16 ready-made letter templates — GDPR data requests, Companies House reminders, VAT registration, Payroll setup, MTD enrolment and more. Send, track opens, and archive all client correspondence in one place.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">📋</div>
        <div>
          <div class="feature-title">Digital Client Onboarding</div>
          <div class="feature-desc">Send a branded onboarding form to new prospects. They fill in their details — company, services needed, entity type — and the system creates their client record automatically. No manual data entry.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">📄</div>
        <div>
          <div class="feature-title">GDPR Data Processing Agreements</div>
          <div class="feature-desc">Auto-generate a UK GDPR Article 28 compliant Data Processing Agreement for each client. Sent by email, acknowledged online. Full audit trail. Required under UK data protection law.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">🔍</div>
        <div>
          <div class="feature-title">Companies House Auto-Lookup</div>
          <div class="feature-desc">Search Companies House instantly when adding a client. Auto-fills company name, registration number and registered address. No typing errors. Free — powered by the official Companies House API.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">🔔</div>
        <div>
          <div class="feature-title">Escalating Automated Reminders</div>
          <div class="feature-desc">Gentle, firm, then urgent reminder emails sent automatically to unsigned clients. "Remind All" button sends to every overdue client in one click. WhatsApp follow-up link included for clients with a phone number.</div>
        </div>
      </div>
      <div class="feature">
        <div class="feature-icon">📁</div>
        <div>
          <div class="feature-title">Signed PDF Archive &amp; CSV Export</div>
          <div class="feature-desc">Every signed document stored as a PDF. Both accountant and client receive an emailed copy instantly at signing. Download any time from the archive. Export your full client database — including AML and MTD data — as a dated CSV.</div>
        </div>
      </div>
    </div>

    <!-- COMPARISON -->
    <div class="compare">
      <div class="compare-title">How we compare</div>
      <div class="compare-row">
        <span class="compare-label">Monthly cost</span>
        <span><span class="compare-them">Xero Sign £60+</span> &nbsp;vs&nbsp; <span class="compare-us">FirmReady £12</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">Per-document charge</span>
        <span><span class="compare-them">Yes (most providers)</span> &nbsp;vs&nbsp; <span class="compare-us">Never</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">AML records built in</span>
        <span><span class="compare-them">No</span> &nbsp;vs&nbsp; <span class="compare-us">Yes</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">MTD tracker built in</span>
        <span><span class="compare-them">No</span> &nbsp;vs&nbsp; <span class="compare-us">Yes</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">MTD deadline tracker</span>
        <span><span class="compare-them">No</span> &nbsp;vs&nbsp; <span class="compare-us">Yes — all deadlines auto-calculated</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">GDPR DPA auto-generation</span>
        <span><span class="compare-them">No</span> &nbsp;vs&nbsp; <span class="compare-us">Yes — Article 28 compliant</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">Client onboarding portal</span>
        <span><span class="compare-them">No</span> &nbsp;vs&nbsp; <span class="compare-us">Yes — digital forms, auto-creates records</span></span>
      </div>
      <div class="compare-row">
        <span class="compare-label">UK-specific compliance</span>
        <span><span class="compare-them">Generic global tools</span> &nbsp;vs&nbsp; <span class="compare-us">Built exclusively for UK practices</span></span>
      </div>
    </div>

    <!-- TESTIMONIAL -->
    <div class="testimonial">
      <div class="testimonial-text">"We were spending £60+ a month just on e-signatures and still tracking AML in a spreadsheet. FirmReady replaced both for £12. The MTD tracker and deadline alerts alone are worth three times the price."</div>
      <div class="testimonial-author">Early Access Subscriber</div>
      <div class="testimonial-role">Practice Manager, Accounting Firm, Greater Manchester</div>
    </div>
  </div>

  <!-- RIGHT: PRICING CARD -->
  <div>
    <div class="pricing-card">
      <div class="card-header">
        <div class="plan-badge">Professional Plan</div>
        <div class="plan-name">FirmReady</div>
        <div class="price-row">
          <div class="price-currency">£</div>
          <div class="price-amount">12</div>
        </div>
        <div class="price-period">per month</div>
        <div class="price-vat">+ VAT &nbsp;·&nbsp; billed monthly &nbsp;·&nbsp; cancel anytime</div>
        <div class="trial-badge">✓ 14-day free trial — no charge today</div>
      </div>

      <div class="card-body">
        <form id="subscribeForm" action="stripe_checkout.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(16))); ?>">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="first_name">First name</label>
              <input class="form-input" type="text" id="first_name" name="first_name" placeholder="Jane" required autocomplete="given-name">
            </div>
            <div class="form-group">
              <label class="form-label" for="last_name">Last name</label>
              <input class="form-input" type="text" id="last_name" name="last_name" placeholder="Smith" required autocomplete="family-name">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="firm_name">Firm name</label>
            <input class="form-input" type="text" id="firm_name" name="firm_name" placeholder="Smith &amp; Co Accountants" required autocomplete="organization">
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Work email address</label>
            <input class="form-input" type="email" id="email" name="email" placeholder="jane@smithandco.co.uk" required autocomplete="email">
          </div>

          <button type="submit" class="btn-subscribe" id="submitBtn">
            Start Free Trial — £12/mo after 14 days
          </button>

          <div class="error-msg" id="errorMsg"<?php if ($error_msg): ?> style="display:block"<?php endif; ?>>
            <?php echo $error_msg; ?>
          </div>
        </form>

        <div class="card-includes">
          <div class="includes-title">Everything included — one price</div>
          <div class="include-item"><div class="include-check">✓</div> Engagement letters &amp; e-signatures</div>
          <div class="include-item"><div class="include-check">✓</div> AML CDD records &amp; risk ratings</div>
          <div class="include-item"><div class="include-check">✓</div> MTD tracker with automated reminders</div>
          <div class="include-item"><div class="include-check">✓</div> Statutory deadline tracker (CT, VAT, CH, SA)</div>
          <div class="include-item"><div class="include-check">✓</div> GDPR Data Processing Agreements</div>
          <div class="include-item"><div class="include-check">✓</div> Digital client onboarding forms</div>
          <div class="include-item"><div class="include-check">✓</div> Companies House auto-lookup</div>
          <div class="include-item"><div class="include-check">✓</div> Professional correspondence — 16 templates</div>
          <div class="include-item"><div class="include-check">✓</div> Signed PDF archive &amp; CSV export</div>
          <div class="include-item"><div class="include-check">✓</div> Email support from The Practice team</div>
        </div>
      </div>

      <div class="card-footer">
        <div class="secure-note">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Secure payment by Stripe &nbsp;·&nbsp; Cancel anytime in your dashboard
        </div>
      </div>
    </div>
  </div>

</div><!-- /main -->

<!-- CROSS-SELL: FULL PACKAGES -->
<div style="background:var(--navy-dark);padding:48px 24px;margin-top:0">
  <div style="max-width:860px;margin:0 auto;text-align:center">
    <div style="display:inline-block;background:var(--gold);color:var(--navy-dark);font-size:11px;font-weight:800;padding:5px 14px;border-radius:20px;margin-bottom:14px;letter-spacing:.5px;text-transform:uppercase">Looking for more?</div>
    <h2 style="font-size:26px;font-weight:800;color:#fff;margin-bottom:10px;letter-spacing:-.2px">Practice Transformation Packages</h2>
    <p style="font-size:14px;color:#94a3b8;max-width:600px;margin:0 auto 28px;line-height:1.75">FirmReady gives you the tools. Our transformation packages give you the tools <em style="color:#e2e8f0">plus</em> a dedicated specialist team, bespoke consultancy hours, cash flow models, board packs, and full practice systems — built and maintained for you.</p>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;max-width:700px;margin-left:auto;margin-right:auto">
      <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin-bottom:8px">Essentials</div>
        <div style="font-size:22px;font-weight:800;color:#fff">£79<span style="font-size:13px;font-weight:400;color:#94a3b8">/mo</span></div>
        <div style="font-size:11px;color:#64748b;margin-top:4px">+ £1,200 setup</div>
        <div style="font-size:12px;color:#94a3b8;margin-top:10px">2 hrs/mo consultancy</div>
      </div>
      <div style="background:rgba(201,168,76,.12);border:2px solid rgba(201,168,76,.4);border-radius:9px;padding:20px;text-align:center">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gold);margin-bottom:6px">★ Most Popular</div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin-bottom:8px">Standard</div>
        <div style="font-size:22px;font-weight:800;color:#fff">£149<span style="font-size:13px;font-weight:400;color:#94a3b8">/mo</span></div>
        <div style="font-size:11px;color:#64748b;margin-top:4px">+ £2,200 setup</div>
        <div style="font-size:12px;color:#94a3b8;margin-top:10px">5 hrs/mo consultancy</div>
      </div>
      <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin-bottom:8px">Premium</div>
        <div style="font-size:22px;font-weight:800;color:#fff">£249<span style="font-size:13px;font-weight:400;color:#94a3b8">/mo</span></div>
        <div style="font-size:11px;color:#64748b;margin-top:4px">+ £3,500 setup</div>
        <div style="font-size:12px;color:#94a3b8;margin-top:10px">10 hrs/mo consultancy</div>
      </div>
    </div>
    <a href="https://practice.finaccord.pro/pricing.html" style="display:inline-block;background:var(--gold);color:var(--navy-dark);font-weight:700;padding:13px 32px;border-radius:6px;font-size:14px">View full packages &rarr;</a>
    <div style="font-size:12px;color:#475569;margin-top:12px">Includes FirmReady capabilities + dedicated specialist team + bespoke consultancy</div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-links">
    <a href="https://practice.finaccord.pro">Home</a>
    <a href="#">Terms of Service</a>
    <a href="#">Privacy Policy</a>
    <a href="mailto:info@kafs-ltd.com">Contact</a>
  </div>
  <div class="footer-copy">© <?php echo date('Y'); ?> KAFS Limited &nbsp;·&nbsp; 5 Brayford Square, London, E1 0SG &nbsp;·&nbsp; ICO Registration ZC112776 &nbsp;·&nbsp; FirmReady is a trading name of The Practice</div>
</footer>

<script>
document.getElementById('subscribeForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Redirecting to secure checkout…';
});
</script>

</body>
</html>
