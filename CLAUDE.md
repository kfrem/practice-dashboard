# CLAUDE.md — The Practice: FirmReady Dashboard
## Complete Project Context for Claude Code

> **Read this entire file before making any changes.**
> This is the single source of truth for the project — what has been built, what works, what is next, and the full technical architecture.

---

## 1. WHO THIS IS FOR

**Owner:** Godfred Frimpong — Business Coach, AI Ethicist, Trade Economist
**Company:** KAFS Limited, 5 Brayford Square, London, E1 0SG
**ICO Registration:** ZC112776 (Data Protection Officer: Godfred Frimpong)
**Email:** info@kafs-ltd.com | **Phone:** +44 7939 823988
**Website:** practice.finaccord.pro

**Product Name:** FirmReady (marketed under The Practice brand)
**Target Market:** UK accounting firms — sole traders and small limited companies (1–5 staff)
**Core Problem Solved:** UK accountants need client engagement letters signed, AML records kept, and MTD clients tracked — all in one cheap tool
**Price Point:** £12/month flat fee (no per-document charges — beats Xero Sign at £60/100 docs)

---

## 2. INFRASTRUCTURE

| Item | Detail |
|---|---|
| Hosting | Hostinger shared hosting |
| Domain | finaccord.pro |
| Live URL | https://practice.finaccord.pro |
| Dashboard | https://practice.finaccord.pro/dashboard.php |
| Client signing | https://practice.finaccord.pro/client.php?token=TOKEN |
| Language | PHP 8+ (no framework — plain PHP for Hostinger compatibility) |
| Database | Flat file JSON (fr_data/clients.json) — no MySQL needed |
| Email | PHP mail() via Hostinger — no SMTP service needed |
| PDF generation | jsPDF (client-side JavaScript) |
| Cost to run | ~£3/month (existing Hostinger plan) |
| GitHub | https://github.com/kfrem/practice-dashboard |

---

## 3. FILE STRUCTURE

```
practice-dashboard/
├── CLAUDE.md           ← This file — read first
├── config.php          ← ALL configuration — firm details, password, paths
├── dashboard.php       ← Accountant dashboard (login, clients, AML, MTD, archive)
├── api.php             ← All backend actions (16 actions — see Section 5)
├── client.php          ← Client-facing signing page (public, token-based)
├── fr_data/            ← Auto-created on first run
│   ├── clients.json    ← All client data (flat file)
│   ├── .htaccess       ← Blocks direct browser access
│   └── pdfs/           ← Signed PDF files stored here
│       └── {client_id}.pdf
```

---

## 4. CONFIG.PHP — DO NOT CHANGE THESE VALUES

```php
FR_FIRM_NAME    = 'The Practice'
FR_FIRM_EMAIL   = 'info@kafs-ltd.com'
FR_FIRM_PHONE   = '+44 7939 823988'
FR_FIRM_ADDRESS = '5 Brayford Square, London, E1 0SG'
FR_FIRM_WEBSITE = 'https://practice.finaccord.pro'
FR_ICO_NUMBER   = 'ZC112776'
FR_BASE_URL     = 'https://practice.finaccord.pro'
FR_FROM_EMAIL   = 'info@kafs-ltd.com'
FR_DATA_DIR     = __DIR__ . '/fr_data/'
Dashboard password = Lo2355 (stored as bcrypt hash)
```

---

## 5. API.PHP — ALL 16 ACTIONS

| Action | Auth Required | Description |
|---|---|---|
| `login` | No | Password login, sets session |
| `logout` | No | Destroys session |
| `check` | No | Returns auth status |
| `clients` | Yes | Returns all clients array |
| `add_client` | Yes | Creates new client record |
| `send_link` | Yes | Sends signing link email + sets deadline |
| `send_reminder` | Yes | Sends escalating reminder (1=gentle, 2=firm, 3+=urgent) |
| `remind_all` | Yes | Sends reminders to ALL overdue clients at once |
| `get_by_token` | No | Returns client data for signing page (public) |
| `sign` | No | Saves signature, stores PDF, emails both parties |
| `update_aml` | Yes | Saves AML Customer Due Diligence record |
| `save_mtd` | Yes | Saves MTD tracker record |
| `update_letter` | Yes | Saves custom engagement letter + fee |
| `save_notes` | Yes | Saves internal notes for client |
| `export_csv` | Yes | Downloads all client data as CSV |
| `download_pdf` | Yes | Downloads signed PDF for a client |
| `delete_client` | Yes | Removes client record |

---

## 6. CLIENT DATA STRUCTURE

Each client in `clients.json` has these fields:

```json
{
  "id": "c_abc123",
  "name": "Jane Smith",
  "email": "jane@example.co.uk",
  "company": "Smith Ltd",
  "type": "Ltd Company",
  "service": "Self Assessment Tax Return",
  "phone": "07700900000",
  "fee": "£350 per annum + VAT",
  "notes": "Internal notes — not visible to client",
  "custom_letter": "Full text of edited engagement letter",
  "status": "pending|sent|signed",
  "sign_token": "hex token for signing URL",
  "signed_at": "ISO date",
  "signed_ip": "IP address",
  "sig_method": "draw|type",
  "doc_hash": "SHA-256 hash of document + signer + timestamp + IP",
  "sent_at": "ISO date",
  "deadline_at": "ISO date",
  "deadline_hours": 48,
  "reminders_sent": 0,
  "last_reminder_at": "ISO date",
  "mtd_threshold": "over50k|30k-50k|20k-30k|under20k",
  "mtd_status": "not_started|in_progress|enrolled|compliant|exempt",
  "mtd_software": "Xero|QuickBooks|Sage|etc",
  "mtd_enrol_date": "YYYY-MM-DD",
  "mtd_next_sub": "YYYY-MM-DD",
  "mtd_notes": "MTD specific notes",
  "aml_status": "pending|complete",
  "aml_id_type": "UK Passport|etc",
  "aml_id_ref": "last 4 digits only",
  "aml_verified_date": "YYYY-MM-DD",
  "aml_risk": "Low|Medium|High",
  "aml_notes": "AML due diligence notes",
  "aml_completed_date": "ISO date",
  "created_at": "ISO date",
  "pdf_b64": ""
}
```

---

## 7. DASHBOARD FEATURES — WHAT IS BUILT

### Login
- Password-protected with PHP sessions
- Auto-checks session on load
- Auto-refreshes client data every 60 seconds

### Stats Bars
- Monthly stats: Added this month, Signed this month, Outstanding, Avg time to sign
- Main stats: Total clients, Awaiting signature, Signed, AML complete

### Tabs
| Tab | What it shows |
|---|---|
| All Clients | Full client list with search |
| Awaiting Signature | Unsigned only |
| ⚠️ Overdue | Past deadline, turns red |
| Signed | Completed clients |
| AML Records | Full AML CDD table |
| 📊 MTD Tracker | MTD status per client |
| 📁 Archive | All signed PDFs with download |

### Client Row Features
- 📝 Note button (yellow when notes exist)
- Fee shown inline
- Link Sent badge with date
- Deadline badge (red if overdue)
- AML status badge
- **Send Link** button → opens letter editor
- **🔔 Remind** button → escalating tone (1/2/3+)
- **💬** WhatsApp button (if phone number exists)
- **⬇ PDF** button (after signing)
- **AML** button
- **✕** Delete button

### Send Link Modal (2-step)
- Step 1: Edit engagement letter + set fee + set deadline
- Step 2: Confirmation + copyable link shown
- Letter auto-generates from client data or uses saved custom version

### Remind All
- Red alert bar shows when any client is overdue
- Single button sends reminders to ALL overdue clients at once
- Reminder tone escalates: Gentle → Firm → Urgent

### MTD Tracker
- Per-client MTD record
- Threshold (Now/2027/2028/Not in scope)
- Status badges (red for in-scope and not compliant)
- Software, enrolment date, next submission date
- MTD notes

### Archive
- All signed documents sorted newest first
- Signature method, IP, hash snippet
- Direct PDF download

### CSV Export
- All fields including MTD data
- Downloads as dated CSV file

---

## 8. CLIENT SIGNING PAGE (client.php)

The client receives a unique URL: `practice.finaccord.pro/client.php?token=HEX`

Flow:
1. Token validated against clients.json
2. Engagement letter shown (custom if accountant edited it, auto-generated if not)
3. Client reads letter (step 1)
4. Client signs (draw or type) and ticks consent box (step 2)
5. SHA-256 hash generated from: name + id + letter text + timestamp + IP
6. PDF generated client-side (jsPDF)
7. POST to api.php?action=sign
8. PDF saved to fr_data/pdfs/{id}.pdf on server
9. **Accountant gets email with PDF attached**
10. **Client gets email with PDF attached**
11. Success screen shown — client does nothing else

### Email Structure (both parties get PDF)
- Proper MIME multipart/mixed encoding
- quoted_printable_encode for HTML body
- base64 + chunk_split for PDF attachment
- Works with Hostinger PHP mail()

---

## 9. LEGAL & COMPLIANCE

| Item | Status |
|---|---|
| ICO Registration | ✅ ZC112776 — expires 29 March 2027 |
| Terms of Service | ✅ Built (FirmReady_Terms_of_Service.docx) |
| eIDAS Compliance | Simple Electronic Signature (SES) — legally valid for engagement letters under Electronic Communications Act 2000 |
| Disclaimer | Built into consent text and ToS — accountant retains professional responsibility |
| GDPR | Data Processing Agreement needed (not yet built) |
| AML | Tool assists recordkeeping only — accountant remains legally responsible |
| ICAEW Accreditation | Target when 50+ paying subscribers — costs £6,000/year |

---

## 10. WHAT IS NOT YET BUILT — PHASE 3

### High Priority

**A. Subscription & Billing System**
- Stripe payment integration
- Subscription tiers (Free trial 14 days → £12/month)
- Subscriber management in dashboard
- Auto-disable access on non-payment
- Invoice generation

**B. Data Processing Agreement (DPA)**
- Legal document between The Practice (processor) and accountant firm (controller)
- Required under UK GDPR Article 28
- Should be accepted at signup

**C. Client Portal Login**
- Clients log in to see their own signed documents
- Access history of all their signed letters
- No need to save email attachments

### Medium Priority

**D. Multi-User / Team Access**
- Allow accountant firms to add team members
- Role-based access (admin, staff, read-only)
- Currently single-password login only

**E. Xero API Integration**
- When client signs, push data to Xero via their API
- Requires Xero Developer App approval
- Webhook: signed → create contact in Xero

**F. QuickBooks API Integration**
- Same as Xero but for QuickBooks Online
- Larger market in UK (17% share)

**G. Automated MTD Reminders**
- Auto-email client when quarterly submission is due
- Based on mtd_next_sub date
- Triggered by cron job or on-page check

**H. Engagement Letter Template Library**
- Pre-built templates for different services
- Accountant selects template → pre-fills letter
- Services: Self Assessment, Company Accounts, VAT, Payroll, MTD Support, Bookkeeping

### Future / Advanced

**I. ICAEW Technology Accreditation**
- Apply when 50+ paying subscribers
- Requires: financial soundness, customer support process, software evaluation by RSM
- Cost: £6,000/year + evaluator fee
- Benefit: ICAEW logo, listed on their marketplace, trust signal for all UK accountants

**J. White-Label Version**
- Allow accountancy software companies to rebrand FirmReady
- Each white-label gets their own config + domain
- Revenue: £500 one-off licence per firm OR revenue share

**K. AML Identity Verification API**
- Integrate Companies House API (free) for company lookups
- Integrate Jumio or Onfido for digital ID verification
- Makes AML check more robust and legally stronger

**L. Practice Scorecard Integration**
- The Practice website already has a scorecard (scorecard.html)
- Link scorecard results to client records
- Show accountant how their practice scores vs benchmarks

**M. Mobile App (PWA)**
- Progressive Web App wrapper for dashboard
- Push notifications for new signatures
- Works on iOS and Android without App Store

**N. Multi-Industry Expansion**
- Same codebase, different template packs
- Estate agents: tenancy agreements, viewing consents
- HR firms: employment contracts, offer letters
- Solicitors: client care letters
- Each industry = new revenue stream, same infrastructure

**O. AI-Powered Letter Generation**
- Use Claude API to generate custom engagement letters
- Accountant describes the engagement in plain English
- AI writes the letter, accountant reviews and sends
- This is the £29/month premium tier

---

## 11. BRANDING & DESIGN SYSTEM

**Colour Palette:**
```css
--navy:      #1a3558
--navy-dark: #0f2238
--gold:      #c9a84c
--gold-dark: #a07830
--cream:     #f8f7f4
--border:    #ddd8cf
--muted:     #64748b
--success:   #2d6a4f
--danger:    #c0392b
```

**Fonts:** `'Segoe UI', Georgia, sans-serif` — matches existing Practice website

**Design principle:** Professional, trust-building, conservative — this is for accountants, not startups

---

## 12. KNOWN ISSUES & TECHNICAL NOTES

1. **PDF email on Hostinger** — Using proper MIME multipart/mixed with quoted_printable_encode for HTML and base64/chunk_split for PDF. This fixed earlier corruption issue.

2. **Flat file JSON** — Works fine up to ~2,000 clients per accountant. If a subscriber has more, migrate to SQLite or MySQL. File locking is implemented (flock) to prevent corruption on concurrent writes.

3. **PHP mail()** — Works on Hostinger. May go to spam on some email providers (Hotmail/Outlook). Fix: add SPF and DKIM records in Hostinger DNS.

4. **Auto-refresh** — Dashboard refreshes every 60 seconds via setInterval. This means no real-time notification if client signs — up to 60s delay. Acceptable for current stage.

5. **Session security** — Simple PHP session. For production with multiple accountant firms, add session_regenerate_id() on login and CSRF tokens on POST requests.

6. **No input sanitisation on PDF base64** — The pdfB64 field from client.php is large and only base64 decoded on server — not filtered. Low risk but worth noting.

---

## 13. HOW TO RUN LOCALLY

```bash
# Start PHP development server
php -S localhost:8080

# Visit in browser
http://localhost:8080/dashboard.php

# Login password
Lo2355
```

Note: Email sending will not work locally (no mail server). Test email flow on Hostinger directly.

---

## 14. HOW TO DEPLOY TO HOSTINGER

1. Upload `dashboard.php`, `api.php`, `client.php`, `config.php` to `practice.finaccord.pro` folder in Hostinger File Manager
2. Do NOT upload `CLAUDE.md` or any development files
3. `fr_data/` folder is auto-created on first dashboard visit
4. Test at: https://practice.finaccord.pro/dashboard.php

---

## 15. GIT WORKFLOW

```bash
# Check what changed
git status

# Stage all changes
git add .

# Commit with description
git commit -m "Description of what changed"

# Push to GitHub
git push

# Pull latest
git pull
```

Branch: `main`
Remote: `origin` → `https://github.com/kfrem/practice-dashboard.git`

---

## 16. WHEN ASKING CLAUDE CODE TO MAKE CHANGES

Always specify:
- Which file to edit
- What the change should do
- Whether it needs a new API action or just a frontend change
- Whether it should be deployed to Hostinger immediately

Example prompt to Claude Code:
> "Add a Stripe payment integration. Create a new file called `subscribe.php` that handles the checkout. Add a `create_subscription` action to `api.php`. The price is £12/month. Use Stripe Checkout (hosted page). Read CLAUDE.md first for full context."

---

*Last updated: April 2026 — Phase 2 complete. Phase 3 ready to begin.*
