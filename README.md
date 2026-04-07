# FirmReady — The Practice Dashboard

A complete client engagement and compliance platform for UK accounting firms. Built with plain PHP and flat-file JSON — no framework, no database server, no per-document fees.

---

## What Is This?

**FirmReady** is a web-based dashboard built for UK accounting practices (sole traders and small firms with 1–5 staff). It solves three specific compliance problems that every UK accountant faces:

1. **Engagement letters** — clients must sign before work begins; chasing them manually wastes hours
2. **AML records** — Anti-Money Laundering Customer Due Diligence must be kept per client
3. **MTD tracking** — Making Tax Digital for Income Tax is rolling out in 2026–2027; accountants need to know which clients are affected and where they stand

**Price point:** £12/month flat fee. No per-document charges. Beats Xero Sign (£60/100 documents) for firms sending more than 20 letters a year.

**What the system does:**
- Send engagement letters to clients via a unique signed link
- Clients read, draw or type their signature, and submit — both parties get a PDF by email
- Stores AML CDD records per client (ID type, reference, risk level, verification date)
- Tracks each client's MTD status, income threshold, software, and next submission date
- Sends automated MTD reminder emails via a daily cron job
- Tracks all statutory deadlines (CT600, Companies House filing, VAT, Payroll RTI, Self Assessment)
- Sends professional correspondence letters via a separate hub with 16 built-in templates
- Lets clients view and acknowledge letters online via a unique token URL
- Supports a firm letterhead designer (logo, colours, tagline, footer, disclaimer)
- Saves and displays a firm signature on all letters and engagement documents
- Provides a public subscription page with Stripe Checkout integration (keys required)

**Owner:** Godfred Frimpong — KAFS Limited, 5 Brayford Square, London, E1 0SG
**ICO Registration:** ZC112776
**GitHub:** https://github.com/kfrem/practice-dashboard

---

## Live URLs

| Page | URL |
|---|---|
| Dashboard (login required) | https://practice.finaccord.pro/dashboard.php |
| Client signing page | https://practice.finaccord.pro/client.php?token=TOKEN |
| Subscription / pricing | https://practice.finaccord.pro/subscribe.php |
| Correspondence hub | https://practice.finaccord.pro/letters.php |
| Letter viewing (public) | https://practice.finaccord.pro/letter_view.php?token=TOKEN |
| Post-payment success | https://practice.finaccord.pro/success.php |
| Post-payment cancel | https://practice.finaccord.pro/cancel.php |

> `TOKEN` is a 64-character hex string generated when a link is sent. Tokens are stored in `fr_data/clients.json` or `fr_data/letters.json`.

---

## Infrastructure

| Item | Detail |
|---|---|
| Hosting | Hostinger shared hosting |
| Domain | finaccord.pro |
| Server path | `/home/u609349519/public_html/practice/` |
| Language | PHP 8+ — plain PHP, no framework (required for Hostinger shared hosting) |
| Database | Flat-file JSON in `fr_data/` — no MySQL or SQLite needed |
| Email | PHP `mail()` via Hostinger's built-in mail server |
| PDF generation | jsPDF (client-side JavaScript, runs in the browser) |
| Auto-deploy | GitHub Actions → FTP to Hostinger on every push to `main` |
| Running cost | ~£3/month (within existing Hostinger plan) |

---

## File Structure

Every file in the repository and what it does:

```
practice-dashboard/
│
├── config.php              All configuration constants — firm details, password hash,
│                           base URL, Stripe keys, Companies House API key.
│                           Auto-creates fr_data/ directory and .htaccess on first load.
│
├── dashboard.php           The main accountant dashboard. Requires PHP session auth.
│                           Contains all tabs: clients, AML, MTD, Deadlines,
│                           Correspondence, Archive. All data loaded via api.php.
│
├── api.php                 All backend operations — 34 actions covering clients,
│                           signing, AML, MTD, deadlines, letters, templates,
│                           firm signature, and letterhead. See full action list below.
│
├── client.php              Public client-facing signing page. Accessed via token URL.
│                           Shows the engagement letter, signature pad (draw or type),
│                           consent checkbox. Generates PDF client-side via jsPDF,
│                           posts to api.php?action=sign.
│
├── letters.php             Correspondence hub — requires dashboard session.
│                           Compose letters using 16 system templates or custom templates.
│                           View all sent letters with status (sent / read / acknowledged).
│                           Includes letterhead designer and firm signature manager.
│
├── letter_view.php         Public letter viewing page. Accessed via token URL.
│                           Recipient views the letter with full firm letterhead and
│                           signature. Auto-marks letter as read on open.
│                           Shows Acknowledge button if letter requires acknowledgement.
│
├── subscribe.php           Public pricing and signup page. Shows features, comparison
│                           table vs competitors. Contains signup form that posts to
│                           stripe_checkout.php. No session required.
│
├── stripe_checkout.php     Receives POST from subscribe.php. Calls Stripe API to create
│                           a Checkout Session with 14-day trial, then redirects to
│                           Stripe's hosted payment page. Requires live Stripe keys.
│
├── success.php             Post-payment confirmation page. Shown after successful Stripe
│                           Checkout. Explains next steps to new subscriber.
│
├── cancel.php              Post-payment cancel page. Shown when user abandons Stripe
│                           Checkout. Lets them return to subscribe.php.
│
├── mtd_cron.php            CLI-only cron script. Reads all clients, checks mtd_next_sub
│                           against today's date, sends reminder emails at configured
│                           intervals. Logs to fr_data/mtd_cron.log. Must be run via
│                           Hostinger cron — blocked from browser access.
│
├── CLAUDE.md               Project bible for Claude Code. Contains architecture notes,
│                           known issues, and roadmap. Not deployed to Hostinger.
│
├── .github/
│   └── workflows/
│       └── deploy.yml      GitHub Actions workflow. Triggers on push to main.
│                           Deploys via FTP to Hostinger, excluding CLAUDE.md,
│                           fr_data/, .git/, and development-only files.
│
└── fr_data/                Auto-created on first dashboard visit. Never committed to git.
    ├── .htaccess           Blocks all direct browser access to this directory.
    ├── clients.json        All client records — the primary data store.
    ├── letters.json        All sent correspondence letters.
    ├── letterhead.json     Firm letterhead settings (logo, colours, footer text).
    ├── custom_templates.json  User-saved custom letter templates.
    ├── firm_sig.b64        Firm signature image stored as base64 data URI.
    ├── mtd_cron.log        Log file written by mtd_cron.php on each run.
    └── pdfs/
        ├── .htaccess       Blocks direct browser access to PDF files.
        └── {client_id}.pdf Signed engagement letter PDFs, one per client.
```

---

## Configuration (config.php)

Open `config.php` and fill in or verify every constant before deploying.

```php
// ── Firm identity ──────────────────────────────────────────────
define('FR_FIRM_NAME',    'The Practice');
define('FR_FIRM_EMAIL',   'info@kafs-ltd.com');
define('FR_FIRM_PHONE',   '+44 7939 823988');
define('FR_FIRM_ADDRESS', '5 Brayford Square, London, E1 0SG');
define('FR_FIRM_WEBSITE', 'https://practice.finaccord.pro');
define('FR_ICO_NUMBER',   'ZC112776');

// ── Dashboard access ───────────────────────────────────────────
// This is a bcrypt hash. The current password is: Lo2355
// To change it: php -r "echo password_hash('NewPassword', PASSWORD_DEFAULT);"
define('FR_PASSWORD_HASH', '$2y$10$fjuF9qoPdg1N2BAbkSnL1OnRW.tsXLFGpCZ1Y6vXsIMWsTfkFCBaG');

// ── URLs and paths ─────────────────────────────────────────────
define('FR_BASE_URL',   'https://practice.finaccord.pro'); // no trailing slash
define('FR_FROM_EMAIL', 'info@kafs-ltd.com');              // must match Hostinger domain
define('FR_DATA_DIR',   __DIR__ . '/fr_data/');

// ── Stripe (Phase 3 billing — placeholder keys) ────────────────
define('FR_STRIPE_SECRET_KEY',     'sk_test_YOUR_SECRET_KEY_HERE');
define('FR_STRIPE_PUBLIC_KEY',     'pk_test_YOUR_PUBLIC_KEY_HERE');
define('FR_STRIPE_PRICE_ID',       'price_YOUR_PRICE_ID_HERE');
define('FR_STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');

// ── Companies House API (free) ─────────────────────────────────
define('FR_CH_API_KEY', 'YOUR_COMPANIES_HOUSE_API_KEY_HERE');
```

The bottom of `config.php` auto-creates `fr_data/`, its `.htaccess`, and `clients.json` if they do not exist. This runs on every page load — no manual setup needed on a new server.

### Changing the dashboard password

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
```

Copy the output hash and replace the value of `FR_PASSWORD_HASH` in `config.php`.

---

## Local Development Setup

Prerequisites: PHP 8.0 or higher installed and available on your PATH.

```bash
# 1. Clone the repository
git clone https://github.com/kfrem/practice-dashboard.git
cd practice-dashboard

# 2. Start the built-in PHP development server
php -S localhost:8080

# 3. Visit the dashboard in your browser
# http://localhost:8080/dashboard.php

# 4. Log in with the dashboard password
# Password: Lo2355
```

**What works locally:**
- Adding clients, editing records, AML, MTD, deadlines, notes
- All tab views and search
- CSV export
- The letters.php correspondence hub (reading and composing)
- The letter_view.php public page

**What does NOT work locally:**
- Sending emails (no local mail server — PHP `mail()` needs a live SMTP relay)
- Saving signed PDFs to disk (paths may differ, and the signing flow needs email confirmation)
- Stripe checkout (requires public HTTPS URL for redirect)
- `mtd_cron.php` runs fine locally from CLI: `php mtd_cron.php`

Test the full email and signing flow by deploying to Hostinger and testing there.

---

## GitHub Actions Auto-Deploy

Every push to the `main` branch automatically deploys all files to Hostinger via FTP. The workflow is defined in `.github/workflows/deploy.yml`.

**What it does:**
1. Checks out the repository
2. Uses `SamKirkland/FTP-Deploy-Action@v4.3.4` to upload files to `/public_html/practice/` on Hostinger
3. Excludes files that must not be on the live server

**Files excluded from deployment:**
- `.git/` and `.github/` directories
- `CLAUDE.md` (developer context only)
- `fr_data/` (live data — never overwrite from git)
- `tg_php_final.php` and `wa_php_final.php` (internal scripts)
- All `*.md` files including this README

**Required GitHub Secrets**

Go to the repository on GitHub → Settings → Secrets and variables → Actions → New repository secret. Add these three:

| Secret name | Value |
|---|---|
| `FTP_SERVER` | `145.14.153.34` |
| `FTP_USERNAME` | `u609349519.finaccord.pro` |
| `FTP_PASSWORD` | The Hostinger FTP password (found in Hostinger hPanel → FTP Accounts) |

Once secrets are set, any `git push origin main` will deploy within about 90 seconds. Check the Actions tab on GitHub to see the deployment log.

---

## All Features — What They Do and How to Test

### 1. Dashboard Login

**What it does:** Password-protected access using PHP sessions. The session persists until the browser is closed or the logout button is clicked. Client data auto-refreshes every 60 seconds via `setInterval`.

**How to test:**
1. Visit `/dashboard.php`
2. Enter password `Lo2355` and click Login
3. You should land on the main dashboard with stats bars visible
4. Click Logout — you should return to the login screen
5. Try a wrong password — you should see an error message

---

### 2. Add Client

**What it does:** Creates a new client record in `fr_data/clients.json` with status `pending`. The record gets a unique ID (`c_` + 12 hex chars) and all blank fields pre-initialised.

**How to test:**
1. Click the `+ Add Client` button in the dashboard toolbar
2. Fill in: Name (required), Email (required), Company name, Entity type (Ltd Company / Sole Trader / Partnership / LLP / Charity), Services (tick one or more), Phone number
3. Click Add Client
4. The client should appear immediately in the All Clients tab with status badge "Pending"
5. Check `fr_data/clients.json` — a new object should be appended

**Fields created:** `id`, `name`, `email`, `company`, `type`, `service`, `phone`, `status=pending`, `created_at`, plus all AML and MTD fields set to empty/default values.

---

### 3. Companies House Lookup

**What it does:** Searches the free Companies House API by company name and auto-fills company details (registered name, company number, registered address) into the Add Client form.

**Setup required:**
1. Register free at: https://developer.company-information.service.gov.uk
2. Create an application and copy the API key
3. Paste it into `config.php`: `define('FR_CH_API_KEY', 'your-key-here');`
4. Deploy to Hostinger

**How to test:**
1. Open the Add Client modal
2. Type a company name (e.g. "Tesco PLC") in the Company field
3. Click the search icon / CH Lookup button
4. A dropdown of matching companies should appear
5. Select one — company name, number, and registered address should auto-fill
6. If the API key is a placeholder, the lookup will fail silently (no auto-fill)

---

### 4. Send Engagement Letter

**What it does:** Sends the client a unique link to read and sign their engagement letter. The letter is either auto-generated from their client record or uses a custom version the accountant has edited. The link sets a deadline and is emailed to the client.

**How to test:**
1. Add a client with a real email address you can access
2. Click the **Send Link** button on that client's row
3. In the modal (Step 1): review or edit the letter text, set the fee, set the deadline (e.g. 48 hours)
4. Click **Send Link** — a confirmation screen (Step 2) shows the signing URL
5. Check the client's email inbox — they should receive an email with a "Review & Sign Document" button
6. Open the link (or copy it from Step 2) — you should see the engagement letter on a white card
7. Scroll to the bottom, draw or type a signature, tick the consent checkbox, click **Sign**
8. Both the accountant email (`info@kafs-ltd.com`) and the client email receive a confirmation email with the signed PDF attached
9. The client's status in the dashboard changes to **Signed** (may take up to 60 seconds for auto-refresh)

**Signing audit trail:** The system generates a SHA-256 hash from: client name + client ID + full letter text + timestamp + client IP address. This hash is stored on the record and displayed in the Archive tab.

---

### 5. Multi-Service Engagement Letters

**What it does:** When adding a client or editing their letter, you can tick multiple services. All selected services appear in the engagement letter body with their own scope of work paragraph.

**How to test:**
1. Add a new client, tick: Self Assessment + VAT Returns + Payroll
2. Click Send Link — the letter preview should contain separate sections for each service
3. Edit the letter in the modal to confirm you can change the text before sending

---

### 6. Firm Signature on Letters

**What it does:** The accountant draws, types, or uploads a firm signature in the Send Link modal. Once saved, it is stored as a base64 image in `fr_data/firm_sig.b64` and appears on every client signing page below the engagement letter.

**How to test:**
1. Click Send Link on any client
2. Scroll to the Firm Signature section in the modal
3. Draw a signature on the canvas, OR click Upload and select a PNG or JPG image
4. Click Save Signature
5. Open the client signing link — the firm signature should appear at the bottom of the letter
6. The signature persists for all future letters — you only need to set it once

---

### 7. Send Reminder

**What it does:** Sends a follow-up email to an unsigned client. The tone escalates automatically based on how many reminders have been sent: 1st = gentle, 2nd = firm, 3rd+ = urgent. The reminder count and timestamp are stored on the client record.

**How to test:**
1. Add a client, send their signing link, do not sign it
2. Click the bell icon (Remind) on that client's row
3. The client receives a reminder email. Check the `reminders_sent` field in the JSON — it should increment
4. Click Remind again — the email subject and tone should be more assertive
5. Click a third time — the subject line should say "Final reminder — urgent action required"

---

### 8. Remind All (Bulk Reminders)

**What it does:** When one or more clients have passed their signing deadline, a red alert bar appears at the top of the dashboard. A single button sends a reminder to every overdue client at once.

**How to test:**
1. Add a client, send their link with a 1-hour deadline
2. Wait for the deadline to pass, or manually edit `deadline_at` in `clients.json` to a past timestamp
3. Refresh the dashboard — the red "X clients overdue" bar should appear
4. Click **Send All Reminders** — all overdue clients receive reminder emails simultaneously

---

### 9. AML Records (Anti-Money Laundering)

**What it does:** Stores a Customer Due Diligence (CDD) record per client. Covers ID type, reference number (last 4 digits only), verification date, risk level, and notes. Required under the Money Laundering Regulations 2017. The tool assists recordkeeping — the accountant remains legally responsible.

**How to test:**
1. Click the **AML** button on any client row
2. Fill in: ID Type (e.g. UK Passport), last 4 digits of reference number, verification date, risk level (Low / Medium / High), AML notes
3. Click Save
4. The AML badge on the client row should change to green "Complete"
5. Click the **AML Records** tab — the full AML table shows all clients with CDD records

---

### 10. MTD Tracker (Making Tax Digital)

**What it does:** Tracks each client's MTD for Income Tax status. Records their income threshold (which determines when they must comply), current status, software they use, enrolment date, next quarterly submission date, and reminder intervals.

**MTD income threshold schedule:**
- Over £50,000: in scope from April 2026
- £30,000–£50,000: in scope from April 2027
- £20,000–£30,000: in scope from April 2028
- Under £20,000: not currently in scope

**How to test:**
1. Click the **MTD Tracker** tab
2. Click **Update** on any client row
3. Fill in: Income threshold, Status (Not Started / In Progress / Enrolled / Compliant / Exempt), Software (Xero / QuickBooks / Sage / Other), Enrolment date, Next submission date
4. Tick the reminder day intervals you want automated emails sent (e.g. 7 days before, 3 days before, day before)
5. Click Save — the MTD badge updates on the row
6. Clients with in-scope thresholds and non-compliant status show a red status badge

---

### 11. Automated MTD Reminders (Cron Job)

**What it does:** `mtd_cron.php` runs daily via Hostinger's cron scheduler. It checks every client's `mtd_next_sub` date, compares it to today, and sends reminder emails to both the client and the accountant firm when a configured interval is reached (e.g. 7 days before). Once a reminder is sent for a specific date+interval combination, it is not sent again (deduplication via `mtd_reminders_sent` array).

**Hostinger cron setup:**
1. Log in to Hostinger hPanel
2. Navigate to Advanced → Cron Jobs
3. Click Create New Cron Job
4. Set the command:
   ```
   php /home/u609349519/public_html/practice/mtd_cron.php
   ```
5. Set the schedule: `0 8 * * *` (every day at 8:00 AM server time)
6. Save

**How to test:**
1. Set a client's `mtd_next_sub` to tomorrow's date and tick "1 day before" in their MTD record
2. Run the cron script manually from CLI:
   ```bash
   php mtd_cron.php
   ```
3. Check output — it should log "SENT client reminder" and "SENT accountant reminder"
4. Check `fr_data/mtd_cron.log` — entries should appear with timestamps
5. Run the script again — it should log "SKIP ... already sent" (deduplication working)

**Log file location:** `fr_data/mtd_cron.log` — tailed output shows every check, send, skip, and error.

---

### 12. Statutory Deadline Tracker

**What it does:** Auto-calculates all statutory filing deadlines for a client based on their year end date and the services they use. Deadlines are displayed in a dedicated Deadlines tab with RAG (Red/Amber/Green) colour coding. Each deadline can be individually marked as Filed or Paid.

**Deadlines calculated:**
- **CT600 Corporation Tax Return** — year end + 12 months
- **Corporation Tax Payment** — year end + 9 months + 1 day
- **Companies House Annual Accounts** — year end + 9 months
- **Confirmation Statement** — set manually from the Companies House filing date
- **VAT Returns** — based on quarter group (Jan/Apr/Jul/Oct, Feb/May/Aug/Nov, or Mar/Jun/Sep/Dec), due 1 month + 7 days after quarter end
- **Payroll RTI** — 19th of each month (if payroll is monthly or weekly)
- **Self Assessment Tax Return** — 31 January each year
- **Payment on Account** — 31 January and 31 July

**How to test:**
1. Click the calendar icon on any client row (or the Deadlines button)
2. Set: Year end date = `2025-03-31`, VAT quarter group = Mar/Jun/Sep/Dec, Payroll = Monthly, tick Self Assessment
3. Save
4. Click the **Deadlines** tab — all deadlines should appear with calculated dates and colour coding
5. Click **Filed** on a deadline row — it moves to the Completed section
6. The Deadlines tab badge shows the count of overdue deadlines

---

### 13. Correspondence Hub (letters.php)

**What it does:** A separate page for sending professional letters and notices to clients. Separate from engagement letters — this handles ongoing correspondence such as fee proposals, service upsells, MTD notices, HMRC updates, and general communication. 16 built-in system templates are included.

**How to test:**
1. Navigate to `/letters.php` (or click Correspondence in the dashboard nav)
2. Click **Compose Letter**
3. **Step 1 — Template:** Select a system template (e.g. "Payroll Services Upsell" or "MTD Awareness Notice")
4. **Step 2 — Edit:** Select a linked client (optional) or enter recipient name and email manually. Edit the letter body. Set subject. Optionally tick "Requires Acknowledgement"
5. **Step 3 — Send:** Review and confirm. Click Send
6. The recipient receives an email with a "View Letter" button linking to `/letter_view.php?token=TOKEN`
7. The letter appears in the Sent Letters list with status "Sent"

**Stats shown:** Total sent, Read, Acknowledged, Unread count — all visible at the top of letters.php.

---

### 14. Letter Viewing and Acknowledgement

**What it does:** The letter_view.php page renders the letter with the firm's letterhead, logo, colours, tagline, firm signature, and footer. Opening the page automatically marks the letter as Read in the system. If the letter required acknowledgement, an Acknowledge button is shown.

**How to test:**
1. Send a letter from letters.php with "Requires Acknowledgement" ticked
2. Copy the link from the sent letter row (or open the email)
3. Open the link — the letter renders with full letterhead
4. In letters.php, the status should now show "Read"
5. Click the Acknowledge button on the letter_view page
6. In letters.php, the status should change to "Acknowledged"

---

### 15. Letterhead Designer

**What it does:** Allows the accountant to design their firm letterhead — logo, primary brand colour, accent colour, tagline, footer text, legal disclaimer, and social media links. All changes are saved to `fr_data/letterhead.json` and applied to every future letter sent via letters.php.

**How to test:**
1. Visit `/letters.php`
2. Click the settings icon (Letterhead) in the top right
3. Upload a logo (PNG or JPG), set a primary colour, type a tagline, set footer text and disclaimer
4. A live preview updates as you type
5. Click Save
6. Send a new letter and open the letter_view link — the letterhead should reflect your saved settings

---

### 16. Custom Letter Templates

**What it does:** When composing a letter in letters.php, the accountant can edit the body and save it as a named custom template. Custom templates appear alongside the 16 system templates in the template picker.

**How to test:**
1. Start composing a letter, select a system template, edit the body
2. Tick "Save as custom template" and enter a name (e.g. "My Payroll Intro")
3. Click Save Template
4. Start composing a new letter — your custom template should appear in the template grid with a gold left border
5. To delete it, open the template card menu and click Delete

Custom templates are stored in `fr_data/custom_templates.json`.

---

### 17. Download Signed PDF

**What it does:** Once a client has signed their engagement letter, the PDF is stored in `fr_data/pdfs/{client_id}.pdf`. The accountant can download it at any time from the Archive tab.

**How to test:**
1. Complete a signing flow (see Feature 4)
2. Click the **Archive** tab
3. Find the signed client — the entry shows signature method, timestamp, client IP, and a hash snippet
4. Click the download button — the PDF downloads to your browser
5. Open the PDF — it should show the full engagement letter with the client's signature and the signing timestamp

---

### 18. CSV Export

**What it does:** Downloads all client data as a `.csv` file including every field — engagement status, AML data, MTD data, deadline settings, and notes. Useful for backup, audit, or import into another system.

**How to test:**
1. Add at least two clients with different statuses
2. Click the **Export CSV** button in the dashboard toolbar
3. A file named `firmready-clients-YYYY-MM-DD.csv` downloads
4. Open in Excel or Google Sheets — every field should appear as a column, one row per client

---

### 19. Notes (Internal)

**What it does:** Saves free-text internal notes against any client record. Notes are not visible to the client on their signing page. The notes icon turns yellow when notes have been added.

**How to test:**
1. Click the notepad icon on any client row
2. Type some notes in the text area
3. Click Save
4. The icon should now appear yellow/gold
5. Click it again — your notes should still be there
6. Open the client signing link — notes are not shown to the client

---

### 20. WhatsApp Quick Message

**What it does:** If a client has a phone number stored, a WhatsApp icon appears on their row. Clicking it opens WhatsApp Web (or the mobile app) with a pre-written reminder message ready to send.

**How to test:**
1. Add a client with a UK mobile number (e.g. `07700900000`)
2. The WhatsApp icon should appear on their row
3. Click it — WhatsApp should open with a pre-filled message
4. A client without a phone number should not show the icon

---

### 21. Subscription Page

**What it does:** A public-facing pricing page at `/subscribe.php`. Shows the £12/month feature list, a competitor comparison table, and a signup form. The form POSTs to `stripe_checkout.php` which creates a Stripe Checkout Session and redirects to Stripe's hosted page. The plan includes a 14-day free trial.

**How to test (without live Stripe keys):**
1. Visit `/subscribe.php` — the page should load and display the pricing features
2. Fill in the signup form and click Subscribe
3. With placeholder keys, you will see a payment error (expected — Stripe rejects test key placeholders)

**How to test (with real Stripe test keys — see Stripe Setup section below):**
1. Add real Stripe test keys to `config.php`
2. Fill in the form and click Subscribe
3. You should be redirected to Stripe's hosted checkout page
4. Use Stripe test card: `4242 4242 4242 4242`, any future expiry, any CVC
5. After payment, you should be redirected to `/success.php`

---

## API Reference — All Actions

All requests go to `api.php`. Authenticated actions require an active PHP session (set by `login`).

JSON body format: `Content-Type: application/json`
Query string format for simple GETs: `?action=export_csv`

| Action | Auth | Method | Input | Output |
|---|---|---|---|---|
| `login` | No | POST JSON | `{password}` | `{success: true}` or 401 |
| `logout` | No | POST | — | `{success: true}` |
| `check` | No | POST | — | `{auth: true/false}` |
| `clients` | Yes | POST | — | `{success, clients: [...]}` |
| `add_client` | Yes | POST JSON | `{name, email, company, type, service, phone}` | `{success, client: {...}}` |
| `send_link` | Yes | POST JSON | `{id, deadline_hours}` | `{success, sent, link, deadline}` |
| `send_reminder` | Yes | POST JSON | `{id}` | `{success}` |
| `remind_all` | Yes | POST | — | `{success, sent: N}` |
| `get_by_token` | No | POST JSON | `{token}` | `{success, client: {...}}` |
| `sign` | No | POST JSON | `{token, sig_data_url, sig_method, pdf_b64, timestamp}` | `{success}` |
| `update_aml` | Yes | POST JSON | `{id, aml_id_type, aml_id_ref, aml_verified_date, aml_risk, aml_notes}` | `{success}` |
| `save_mtd` | Yes | POST JSON | `{id, mtd_threshold, mtd_status, mtd_software, mtd_enrol_date, mtd_next_sub, mtd_notes, mtd_reminder_days[]}` | `{success}` |
| `update_letter` | Yes | POST JSON | `{id, custom_letter, fee}` | `{success}` |
| `save_notes` | Yes | POST JSON | `{id, notes}` | `{success}` |
| `export_csv` | Yes | GET | — | CSV file download |
| `download_pdf` | Yes | GET | `?action=download_pdf&id=c_xxx` | PDF file download |
| `delete_client` | Yes | POST JSON | `{id}` | `{success}` |
| `save_deadlines` | Yes | POST JSON | `{id, dl_year_end, dl_confirmation_due, dl_vat, dl_payroll, dl_self_assessment}` | `{success}` |
| `mark_deadline` | Yes | POST JSON | `{id, key, status}` — key format: `word:YYYY-MM-DD`, status: `pending/filed/paid` | `{success}` |
| `get_letterhead` | Yes | POST | — | `{success, letterhead: {...}}` |
| `save_letterhead` | Yes | POST JSON | `{logo, tagline, brand_colour, accent_colour, footer_text, disclaimer, social_linkedin, social_twitter}` | `{success}` |
| `get_letters` | Yes | POST | — | `{success, letters: [...]}` |
| `send_letter` | Yes | POST JSON | `{subject, body, recipient_name, recipient_email, category, linked_client_id, require_acknowledgement}` | `{success, sent, id, link}` |
| `get_letter_by_token` | No | POST JSON | `{token}` | `{success, letter, letterhead, firm_sig}` |
| `mark_letter_read` | No | POST JSON | `{token}` | `{success}` |
| `acknowledge_letter` | No | POST JSON | `{token}` | `{success}` |
| `delete_letter` | Yes | POST JSON | `{id}` | `{success}` |
| `get_custom_templates` | Yes | POST | — | `{success, templates: [...]}` |
| `save_custom_template` | Yes | POST JSON | `{id (optional), name, category, subject, body}` | `{success, id}` |
| `delete_custom_template` | Yes | POST JSON | `{id}` | `{success}` |
| `save_firm_sig` | Yes | POST JSON | `{sig}` — base64 data URI (PNG or JPG) | `{success}` |
| `get_firm_sig` | Yes | POST | — | `{success, sig}` |

**Error format:** All errors return `{success: false, error: "message"}` with an appropriate HTTP status code (400, 401, 404).

---

## Client Data Structure

Each record in `fr_data/clients.json`:

```json
{
  "id":                 "c_abc123def456",
  "name":               "Jane Smith",
  "email":              "jane@example.co.uk",
  "company":            "Smith Consulting Ltd",
  "type":               "Ltd Company",
  "service":            "Self Assessment Tax Return, VAT Returns",
  "phone":              "07700900000",
  "fee":                "£350 per annum + VAT",
  "notes":              "Internal notes — not visible to client",
  "custom_letter":      "Full text of the edited engagement letter (if accountant edited it)",

  "status":             "pending | sent | signed",
  "sign_token":         "64-char hex string — used in client.php?token=",
  "signed_at":          "2025-04-01T14:32:00+00:00",
  "signed_ip":          "82.45.123.99",
  "sig_method":         "draw | type",
  "doc_hash":           "SHA-256 hash of name+id+letter+timestamp+IP",
  "sent_at":            "ISO 8601 datetime",
  "sent_by_ip":         "IP of accountant when Send Link was clicked",
  "letter_sent_hash":   "SHA-256 hash of letter content at time of sending",
  "deadline_at":        "ISO 8601 datetime",
  "deadline_hours":     48,
  "reminders_sent":     0,
  "last_reminder_at":   "ISO 8601 datetime",

  "aml_status":         "pending | complete",
  "aml_id_type":        "UK Passport | UK Driving Licence | EU Passport | Other",
  "aml_id_ref":         "last 4 digits only — e.g. 4521",
  "aml_verified_date":  "YYYY-MM-DD",
  "aml_risk":           "Low | Medium | High",
  "aml_notes":          "Free text AML due diligence notes",
  "aml_completed_date": "ISO 8601 datetime",

  "mtd_threshold":      "over50k | 30k-50k | 20k-30k | under20k",
  "mtd_status":         "not_started | in_progress | enrolled | compliant | exempt",
  "mtd_software":       "Xero | QuickBooks | Sage | FreeAgent | Other",
  "mtd_enrol_date":     "YYYY-MM-DD",
  "mtd_next_sub":       "YYYY-MM-DD — next quarterly submission due date",
  "mtd_notes":          "MTD-specific notes",
  "mtd_reminder_days":  [7, 3, 1],
  "mtd_reminders_sent": ["2025-06-30:7", "2025-06-30:3"],

  "dl_year_end":          "YYYY-MM-DD",
  "dl_confirmation_due":  "YYYY-MM-DD — Companies House Confirmation Statement due date",
  "dl_vat":               "none | jan | feb | mar",
  "dl_payroll":           "none | monthly | weekly",
  "dl_self_assessment":   true,
  "dl_statuses": {
    "ct600:2025-03-31":   "filed",
    "ct_payment:2025-01-01": "paid"
  },

  "pdf_b64":    "",
  "created_at": "ISO 8601 datetime"
}
```

**Notes on specific fields:**

- `service` — comma-separated if multiple services were selected
- `sign_token` — generated once with `random_bytes(32)` and reused if the link is resent
- `doc_hash` — generated client-side using the Web Crypto API (SHA-256); the server stores it but does not re-verify it
- `aml_id_ref` — only the last 4 digits are stored; full ID numbers are never recorded
- `mtd_reminder_days` — array of integers (days before deadline); matched against `daysLeft` in cron
- `mtd_reminders_sent` — array of strings in format `"YYYY-MM-DD:N"` used for deduplication
- `dl_vat` — `jan` means quarters end in January/April/July/October; `feb` = Feb/May/Aug/Nov; `mar` = Mar/Jun/Sep/Dec
- `dl_statuses` — object keyed by `"deadline_type:YYYY-MM-DD"`, values are `pending`, `filed`, or `paid`

---

## Letter Data Structure

Each record in `fr_data/letters.json`:

```json
{
  "id":                     "l_abc123def456",
  "template_id":            "payroll_upsell",
  "category":               "Business Development",
  "subject":                "Payroll Services — Could We Help?",
  "recipient_name":         "Jane Smith",
  "recipient_email":        "jane@example.co.uk",
  "recipient_type":         "client",
  "linked_client_id":       "c_abc123def456",
  "body":                   "Full HTML letter body",
  "status":                 "sent | read | acknowledged",
  "token":                  "64-char hex string — used in letter_view.php?token=",
  "require_acknowledgement": true,
  "sent_at":                "ISO 8601 datetime",
  "read_at":                "ISO 8601 datetime or null",
  "acknowledged_at":        "ISO 8601 datetime or null",
  "sent_by_ip":             "IP of accountant when letter was sent",
  "letter_hash":            "SHA-256 hash of body+email+sent_at",
  "created_at":             "ISO 8601 datetime"
}
```

---

## Cron Jobs (Hostinger Setup)

### MTD Reminder Cron

This is the only cron job in Phase 2. Set it up once and it runs forever.

**Hostinger steps:**
1. Log in to hPanel at hpanel.hostinger.com
2. Go to Advanced → Cron Jobs
3. Click Create New Cron Job
4. Command:
   ```
   php /home/u609349519/public_html/practice/mtd_cron.php
   ```
5. Schedule: `0 8 * * *` (daily at 8:00 AM server time)
6. Save

**Testing the cron manually:**
```bash
# From the Hostinger File Manager terminal, or via SSH if enabled:
php /home/u609349519/public_html/practice/mtd_cron.php

# Locally:
php mtd_cron.php
```

**Log output example:**
```
[2026-04-06 08:00:01] MTD cron started
[2026-04-06 08:00:01] SENT client reminder to jane@example.co.uk (Jane Smith, 7d)
[2026-04-06 08:00:01] SENT accountant reminder for Jane Smith, 7d
[2026-04-06 08:00:01] SKIP John Brown — reminder 2026-04-15:7 already sent
[2026-04-06 08:00:01] Saved updated reminder log
[2026-04-06 08:00:01] MTD cron complete — checked: 3, reminders sent: 2
```

**What triggers a reminder:**
- Client has `mtd_next_sub` set to a future date
- `mtd_reminder_days` array includes today's `daysLeft` value
- `mtd_status` is not `exempt`
- The specific `date:daysLeft` combination has not been sent before

---

## Stripe Setup (When Ready)

Stripe is integrated but inactive — placeholder keys are in `config.php`. Follow these steps to activate payments.

### Step 1: Create a Stripe account
Go to https://stripe.com and create an account. Complete identity verification.

### Step 2: Create the product and price
1. Go to Stripe Dashboard → Products → Add product
2. Name: `FirmReady`
3. Pricing: Recurring, £12.00 GBP, per month
4. Save the product
5. Copy the **Price ID** (starts with `price_`) — paste into `FR_STRIPE_PRICE_ID` in `config.php`

### Step 3: Get API keys
1. Go to Stripe Dashboard → Developers → API keys
2. Copy the **Secret key** (starts with `sk_`) → paste into `FR_STRIPE_SECRET_KEY`
3. Copy the **Publishable key** (starts with `pk_`) → paste into `FR_STRIPE_PUBLIC_KEY`
4. Use test keys (`sk_test_...`) while building; swap for live keys (`sk_live_...`) when ready to charge

### Step 4: Set up webhook (optional but recommended)
1. Go to Stripe Dashboard → Developers → Webhooks → Add endpoint
2. Endpoint URL: `https://practice.finaccord.pro/stripe_webhook.php`
3. Select events: `checkout.session.completed`, `customer.subscription.deleted`, `invoice.payment_failed`
4. Copy the **Signing secret** → paste into `FR_STRIPE_WEBHOOK_SECRET`

> Note: `stripe_webhook.php` is not yet built. The Stripe checkout flow (subscribe → Stripe → success.php) works without a webhook. The webhook is needed for subscription management (cancellations, failed payments).

### Step 5: Test with Stripe test cards
- Successful payment: `4242 4242 4242 4242` — any future date, any CVC
- Declined card: `4000 0000 0000 0002`
- 3D Secure required: `4000 0025 0000 3155`

### Step 6: Go live
1. Swap test keys for live keys in `config.php`
2. Deploy to Hostinger
3. Test with a real card for £0.00 (use a trial coupon) or accept the first charge

---

## Companies House API Setup

The Companies House API is free for UK companies. It provides company names, numbers, registered addresses, and SIC codes.

### Step 1: Register
Go to: https://developer.company-information.service.gov.uk
Create an account, then create a new application.

### Step 2: Get your API key
After creating the application, you receive an API key (a long alphanumeric string).

### Step 3: Add to config.php
```php
define('FR_CH_API_KEY', 'your-api-key-here');
```

### Step 4: Deploy and test
1. Deploy to Hostinger
2. Open the Add Client modal in the dashboard
3. Type a company name in the Company field and click the search icon
4. Results from Companies House should appear in a dropdown
5. Selecting a result auto-fills the company name, number, and registered address

**API limits:** Free tier supports up to 600 requests per 5 minutes per key. This is more than sufficient for normal use.

---

## Design System

All pages use the same CSS custom properties and typography.

### Colour palette

```css
:root {
  --navy:      #1a3558;  /* Primary brand colour — nav, headings, buttons */
  --navy-dark: #0f2238;  /* Darker navy — hero sections, email headers */
  --gold:      #c9a84c;  /* Accent colour — highlights, badges, logo */
  --gold-dark: #a07830;  /* Gold hover state */
  --cream:     #f8f7f4;  /* Page background */
  --border:    #ddd8cf;  /* All borders and dividers */
  --muted:     #64748b;  /* Secondary text, labels */
  --success:   #2d6a4f;  /* Green — signed, complete, AML done */
  --danger:    #c0392b;  /* Red — overdue, errors, delete */
}
```

### Typography

```css
font-family: 'Segoe UI', Georgia, sans-serif;
```

Segoe UI is the system font on Windows. Georgia is the fallback for Mac/Linux. This matches the existing The Practice marketing website.

### Design principle

Professional, trust-building, conservative. This is practice management software for accountants — not a startup SaaS landing page. No gradients on content areas, no animations beyond subtle hover states, no bright colours beyond the gold accent.

---

## Security Notes

| Area | Implementation |
|---|---|
| Dashboard access | PHP session (`$_SESSION['fr_auth']`), bcrypt password hash |
| Public tokens | 64-character hex generated with `random_bytes(32)` — brute-force infeasible |
| Document integrity | SHA-256 hash stored on record at signing time |
| Data directory | `fr_data/.htaccess` with `Deny from all` blocks direct browser access |
| File writes | `flock(LOCK_EX)` on all JSON writes prevents corruption under concurrent load |
| Input sanitisation | All user input passed through `htmlspecialchars(strip_tags(trim()))` |
| Email validation | `filter_var($email, FILTER_VALIDATE_EMAIL)` on all email fields |
| PDF base64 | Only base64 decoded and written to disk — not parsed or executed |
| Cron script | `php_sapi_name() !== 'cli'` check — returns 403 if accessed via browser |
| Stripe keys | Stored in `config.php` server-side only — never exposed to the browser |

**Known security limitations to address before multi-tenant production use:**
- Single shared password — no per-user accounts
- No CSRF tokens on POST requests (mitigated by session-based auth)
- No `session_regenerate_id()` on login (low risk at current scale)
- PDF base64 from client.php is not filtered beyond base64 decode (low risk — just binary data)

---

## Known Issues and Limitations

**1. Email deliverability (Outlook / Hotmail)**
PHP `mail()` via Hostinger may land in spam for Outlook and Hotmail recipients. Fix: add SPF and DKIM DNS records for `finaccord.pro` in Hostinger's DNS management panel.
- SPF: `v=spf1 include:hostinger.com ~all`
- DKIM: generate in Hostinger hPanel → Email → Email Settings

**2. Flat file JSON scaling limit**
`clients.json` and `letters.json` are loaded fully into memory on every request. Performance is fine up to approximately 2,000 clients per accountant. Above that, migrate to MySQL (Hostinger includes MySQL on paid plans) or SQLite. File locking (`flock`) prevents corruption under concurrent requests.

**3. Auto-refresh is not real-time**
The dashboard polls `api.php?action=clients` every 60 seconds via `setInterval`. There is up to a 60-second delay between a client signing and the dashboard showing it. Acceptable at current scale — fix with WebSockets or Server-Sent Events if real-time is needed.

**4. Single-password authentication**
The current login system has one shared password for the whole dashboard. There is no per-user access, no role separation (admin / staff / read-only), and no audit trail of who did what. This is planned for Phase 3 (Multi-User / Team Access).

**5. Stripe webhook not yet built**
The `subscribe.php → stripe_checkout.php → success.php` flow works. But there is no `stripe_webhook.php` to handle post-payment events (subscription cancellation, failed renewal, trial ending). Until the webhook is built, subscription management must be done manually via the Stripe Dashboard.

**6. No client portal**
Clients access their engagement letter via a one-time token URL. There is no client login where they can view their signing history or access past letters. This is on the Phase 3 roadmap.

**7. onboard.php not yet built**
The digital client onboarding flow (sending a prospect a form to fill in their own details) is listed in the CLAUDE.md roadmap but `onboard.php` does not yet exist in the codebase. The related API actions (`create_onboard`, `get_onboard_by_token`, `submit_onboard`, `activate_onboard`) are also not yet implemented.

**8. DPA signing not yet built**
The Data Processing Agreement flow (required under UK GDPR Article 28) is planned but not yet implemented. The `send_dpa` API action does not exist yet.

---

## Phase 3 Roadmap — What's Next

### Complete

- Correspondence hub (`letters.php` + `letter_view.php`) with 16 system templates
- Automated MTD reminders (`mtd_cron.php`) with per-client intervals and deduplication
- Statutory Deadline Tracker (CT600, Corporation Tax, Companies House, VAT, Payroll, Self Assessment)
- Companies House API auto-lookup in Add Client modal
- Firm letterhead designer (logo, colours, tagline, footer, disclaimer)
- Firm signature — draw, type, or upload; persists across all letters
- Custom letter template saving
- Stripe Checkout integration — subscribe.php + stripe_checkout.php + success.php + cancel.php
- 14-day free trial with Stripe subscription_data

### Pending

- **Digital Client Onboarding** — send prospect a link to fill in their own details
- **GDPR / DPA signing** — auto-generate and send Data Processing Agreement for client to sign
- **Stripe webhook handler** — handle subscription cancellations, failed payments, trial endings
- **Multi-user / team access** — per-user accounts with role-based permissions
- **Client portal login** — clients log in to view their signing history
- **Xero API integration** — push signed client data to Xero as a new contact
- **QuickBooks API integration** — same as Xero
- **Mobile PWA** — Progressive Web App wrapper with push notifications
- **ICAEW Technology Accreditation** — apply when 50+ paying subscribers (costs ~£6,000/year)
- **White-label version** — resell to accountancy software companies with their own branding
- **AI-powered letter generation** — use Claude API to draft custom engagement letters (£29/month tier)

---

## Testing Checklist for New Developers

Work through this list top to bottom to verify every feature is working end-to-end.

```
[ ] Dashboard login works with password Lo2355
[ ] Wrong password shows error and does not grant access
[ ] Add client works — client appears in All Clients tab immediately
[ ] Client record saved correctly in fr_data/clients.json
[ ] Companies House lookup returns results (requires API key in config.php)
[ ] Selecting CH result auto-fills company name and address
[ ] Send engagement letter — client receives email with signing link
[ ] Signing link opens correctly and shows engagement letter
[ ] Client can sign using draw method — PDF generated and sent to both parties
[ ] Client can sign using type method — PDF generated and sent to both parties
[ ] Client status changes to Signed in dashboard after signing (within 60 seconds)
[ ] Signed PDF downloads correctly from Archive tab
[ ] Downloaded PDF contains the letter text and signature
[ ] AML record saves and AML badge changes to Complete
[ ] All AML records visible in AML Records tab
[ ] MTD record saves and MTD badge updates in MTD Tracker tab
[ ] MTD cron script runs without errors: php mtd_cron.php
[ ] MTD cron log created/appended at fr_data/mtd_cron.log
[ ] MTD cron does not re-send the same reminder twice (deduplication)
[ ] Deadline settings save correctly
[ ] Correct deadline dates calculated and shown in Deadlines tab
[ ] RAG colour coding correct (red = overdue, amber = soon, green = ok)
[ ] Mark Deadline as Filed works — moves to Completed section
[ ] Deadlines tab badge shows overdue count
[ ] Send reminder — client receives reminder email, reminders_sent increments
[ ] Second reminder has a firmer tone in subject and body
[ ] Third+ reminder has urgent tone
[ ] Remind All button appears when any client is overdue
[ ] Remind All sends emails to all overdue clients
[ ] CSV export downloads a properly formatted file with all client fields
[ ] Compose and send a letter from letters.php
[ ] Recipient receives the letter email
[ ] Opening letter_view.php link marks letter as Read in letters.php
[ ] Acknowledge button works — status changes to Acknowledged in letters.php
[ ] Letterhead designer saves logo, colours, tagline, footer
[ ] Saved letterhead appears on new letters in letter_view.php
[ ] Firm signature saves and appears on client signing page
[ ] Custom letter template saves and appears in compose template picker
[ ] Custom letter template can be deleted
[ ] Notes save against a client — icon turns gold/yellow
[ ] Notes are not visible on the client signing page
[ ] WhatsApp button appears for clients with phone numbers
[ ] WhatsApp button opens message pre-filled with reminder text
[ ] Subscribe page loads at /subscribe.php
[ ] Subscribe form shows validation error on empty fields
[ ] Subscribe form redirects to Stripe (requires live keys) or shows error (placeholder keys)
[ ] Auto-deploy: push a minor change to main → check GitHub Actions → confirm change live on Hostinger within 2 minutes
[ ] fr_data/ directory is NOT deployed by GitHub Actions (confirm no client data in git)
```

---

*Last updated: April 2026 — Phase 2 complete, Phase 3 in progress.*
