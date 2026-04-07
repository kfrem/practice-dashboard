<?php
// ============================================================
// FIRMREADY — API
// All data operations: save clients, update AML, sign documents
// ============================================================

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// ── Helpers ────────────────────────────────────────────────

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function error($msg, $code = 400) {
    respond(['success' => false, 'error' => $msg], $code);
}

function clean($val) {
    return htmlspecialchars(strip_tags(trim($val ?? '')), ENT_QUOTES, 'UTF-8');
}

function load_clients() {
    $file = FR_DATA_DIR . 'clients.json';
    $json = file_get_contents($file);
    return json_decode($json, true) ?: [];
}

function save_clients($clients) {
    $file = FR_DATA_DIR . 'clients.json';
    $fp = fopen($file, 'c+');
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($clients, JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN);
    fclose($fp);
}

function send_email($to, $subject, $html, $from = null, $pdfB64 = null, $pdfName = 'SignedDocument.pdf') {
    $fromEmail = $from ?? FR_FROM_EMAIL;
    $fromName  = FR_FIRM_NAME;
    $b1        = '----=_Part_' . md5(uniqid('', true));
    $b2        = '----=_Part_' . md5(uniqid('', true));

    $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    if ($pdfB64 && strlen($pdfB64) > 100) {
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$b1}\"\r\n";

        $body  = "This is a multi-part message in MIME format.\r\n\r\n";

        // HTML part
        $body .= "--{$b1}\r\n";
        $body .= "Content-Type: multipart/alternative; boundary=\"{$b2}\"\r\n\r\n";
        $body .= "--{$b2}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= quoted_printable_encode($html) . "\r\n\r\n";
        $body .= "--{$b2}--\r\n\r\n";

        // PDF attachment
        $body .= "--{$b1}\r\n";
        $body .= "Content-Type: application/pdf; name=\"{$pdfName}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$pdfName}\"\r\n\r\n";
        $body .= chunk_split($pdfB64, 76, "\r\n") . "\r\n";
        $body .= "--{$b1}--\r\n";
    } else {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $body = quoted_printable_encode($html);
    }

    return mail($to, $subject, $body, $headers);
}

function save_pdf($clientId, $pdfB64) {
    $dir = FR_DATA_DIR . 'pdfs/';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
        file_put_contents($dir . '.htaccess', "Deny from all\n");
    }
    $bytes = base64_decode($pdfB64);
    if ($bytes && strlen($bytes) > 100) {
        file_put_contents($dir . $clientId . '.pdf', $bytes);
        return true;
    }
    return false;
}

function email_html($title, $body_html, $footer = '') {
    $firm = FR_FIRM_NAME;
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
<body style='font-family:Georgia,serif;background:#f5f3ee;margin:0;padding:0;'>
<div style='max-width:580px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);'>
<div style='background:#1a2940;padding:24px 32px;'>
  <div style='color:#c9a84c;font-size:20px;font-weight:bold;letter-spacing:1px;'>{$firm}</div>
  <div style='color:#8a9ab0;font-size:12px;margin-top:4px;'>Client Compliance Portal</div>
</div>
<div style='padding:32px;color:#1a2940;font-size:15px;line-height:1.7;'>{$body_html}</div>
<div style='background:#1a2940;padding:14px 32px;font-size:11px;color:#4a6070;'>{$footer}</div>
</div></body></html>";
}

// ── Session Auth Check ─────────────────────────────────────

function require_auth() {
    session_start();
    if (empty($_SESSION['fr_auth'])) {
        error('Unauthorised', 401);
    }
}

// ── Route ──────────────────────────────────────────────────

$action = clean($_GET['action'] ?? $_POST['action'] ?? '');
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {

    // LOGIN
    case 'login':
        session_start();
        $pwd = $input['password'] ?? '';
        if (password_verify($pwd, FR_PASSWORD_HASH)) {
            $_SESSION['fr_auth'] = true;
            respond(['success' => true]);
        } else {
            error('Invalid password', 401);
        }
        break;

    // LOGOUT
    case 'logout':
        session_start();
        session_destroy();
        respond(['success' => true]);
        break;

    // CHECK AUTH
    case 'check':
        session_start();
        respond(['auth' => !empty($_SESSION['fr_auth'])]);
        break;

    // GET ALL CLIENTS
    case 'clients':
        require_auth();
        respond(['success' => true, 'clients' => array_values(load_clients())]);
        break;

    // ADD CLIENT
    case 'add_client':
        require_auth();
        $name           = clean($input['name'] ?? '');
        $email          = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $company        = clean($input['company'] ?? '');
        $type           = clean($input['type'] ?? 'Ltd Company');
        $service        = clean($input['service'] ?? 'Self Assessment Tax Return');
        $phone          = clean($input['phone'] ?? '');
        $company_number = clean($input['company_number'] ?? '');
        $address        = clean($input['address'] ?? '');

        if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error('Name and valid email are required');
        }

        $clients = load_clients();

        // Enforce per-firm client limit (only for provisioned instances that have subscription.json)
        $sub_file = FR_DATA_DIR . 'subscription.json';
        if (file_exists($sub_file)) {
            $sub_data    = json_decode(file_get_contents($sub_file), true) ?: [];
            $client_limit = (int)($sub_data['client_limit'] ?? 50);
            // Don't count onboarding prospects toward the limit
            $active_count = count(array_filter($clients, fn($c) => ($c['status'] ?? '') !== 'onboarding'));
            if ($active_count >= $client_limit) {
                error("Client limit reached ({$client_limit}). Please contact support to increase your limit.");
            }
        }

        $id = 'c_' . bin2hex(random_bytes(6));
        $clients[$id] = [
            'id'                => $id,
            'name'              => $name,
            'email'             => $email,
            'company'           => $company,
            'company_number'    => $company_number,
            'address'           => $address,
            'type'              => $type,
            'service'           => $service,
            'phone'             => $phone,
            'fee'               => '',
            'notes'             => '',
            'custom_letter'     => '',
            'mtd_threshold'     => '',
            'mtd_status'        => 'not_started',
            'mtd_software'      => '',
            'mtd_enrol_date'    => '',
            'mtd_next_sub'      => '',
            'mtd_notes'         => '',
            'status'            => 'pending',
            'sign_token'        => '',
            'signed_at'         => '',
            'signed_ip'         => '',
            'sig_method'        => '',
            'doc_hash'          => '',
            'sent_at'           => '',
            'deadline_at'       => '',
            'deadline_hours'    => 48,
            'reminders_sent'    => 0,
            'last_reminder_at'  => '',
            'aml_status'        => 'pending',
            'aml_id_type'       => '',
            'aml_id_ref'        => '',
            'aml_verified_date' => '',
            'aml_risk'          => 'Low',
            'aml_notes'         => '',
            'aml_completed_date'=> '',
            'created_at'        => date('c'),
            'pdf_b64'           => '',
        ];
        save_clients($clients);
        respond(['success' => true, 'client' => $clients[$id]]);
        break;

    // SEND SIGNING LINK
    case 'send_link':
        require_auth();
        $id           = clean($input['id'] ?? '');
        $deadlineHours= intval($input['deadline_hours'] ?? 48);
        if ($deadlineHours < 1) $deadlineHours = 48;
        $clients = load_clients();

        if (!isset($clients[$id])) error('Client not found');

        // Keep same token if resending, generate new one if first time
        $token = $clients[$id]['sign_token'] ?: bin2hex(random_bytes(32));
        $clients[$id]['sign_token']       = $token;
        $clients[$id]['status']           = 'sent';
        $clients[$id]['sent_at']          = date('c');
        $clients[$id]['deadline_at']      = date('c', time() + ($deadlineHours * 3600));
        $clients[$id]['deadline_hours']   = $deadlineHours;
        $clients[$id]['sent_by_ip']       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $letterContent                    = $clients[$id]['custom_letter'] ?? '';
        $clients[$id]['letter_sent_hash'] = hash('sha256', $letterContent);
        save_clients($clients);

        $c    = $clients[$id];
        $link = FR_BASE_URL . '/client.php?token=' . $token;
        $deadline = date('l j F Y \a\t g:ia', strtotime($c['deadline_at']));

        $body = "<p>Dear <strong>{$c['name']}</strong>,</p>
<p>Thank you for choosing <strong>" . FR_FIRM_NAME . "</strong>. Please review and sign your client engagement letter by clicking the button below.</p>
<div style='text-align:center;margin:28px 0;'>
  <a href='{$link}' style='background:#1a3558;color:#c9a84c;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;display:inline-block;'>Review &amp; Sign Document →</a>
</div>
<div style='background:#fff8ed;border:1px solid #f6d860;border-radius:6px;padding:12px 16px;margin:0 0 16px;text-align:center;'>
  <strong style='color:#92400e;'>⏰ Please sign by: {$deadline}</strong>
</div>
<p style='font-size:13px;color:#666;'>This link is unique to you. If you have any questions, contact us at " . FR_FIRM_EMAIL . " or call " . FR_FIRM_PHONE . "</p>";

        $footer = FR_FIRM_NAME . " | " . FR_FIRM_ADDRESS . " | ICO: " . FR_ICO_NUMBER;
        $html   = email_html('Action Required: Please Sign Your Document', $body, $footer);

        $sent = send_email($c['email'], 'Action required: Please sign your document — ' . FR_FIRM_NAME, $html);
        respond(['success' => true, 'sent' => $sent, 'link' => $link, 'deadline' => $c['deadline_at']]);
        break;

    // SEND REMINDER
    case 'send_reminder':
        require_auth();
        $id = clean($input['id'] ?? '');
        $clients = load_clients();

        if (!isset($clients[$id])) error('Client not found');
        $c = $clients[$id];
        if ($c['status'] === 'signed') error('Document already signed');
        if (!$c['sign_token']) error('No signing link found — please send the link first');

        $link     = FR_BASE_URL . '/client.php?token=' . $c['sign_token'];
        $sentDate = $c['sent_at'] ? date('j F Y', strtotime($c['sent_at'])) : 'recently';
        $remCount = intval($c['reminders_sent'] ?? 0) + 1;
        $deadline = $c['deadline_at'] ? date('l j F Y \a\t g:ia', strtotime($c['deadline_at'])) : '';

        // Escalate tone based on reminder number
        if ($remCount === 1) {
            $urgency  = 'This is a gentle reminder';
            $tone     = 'We wanted to follow up on the engagement letter we sent you on ' . $sentDate . '.';
            $colour   = '#1a3558';
        } elseif ($remCount === 2) {
            $urgency  = 'Second reminder — action required';
            $tone     = 'We notice your engagement letter is still unsigned. Could you please take a moment to sign it at your earliest convenience?';
            $colour   = '#b45309';
        } else {
            $urgency  = 'Final reminder — urgent action required';
            $tone     = 'This is our final reminder regarding your unsigned engagement letter. Please sign as soon as possible to avoid any delay to your service.';
            $colour   = '#c0392b';
        }

        $body = "<p>Dear <strong>{$c['name']}</strong>,</p>
<p style='margin-top:10px;'>{$tone}</p>
<div style='text-align:center;margin:24px 0;'>
  <a href='{$link}' style='background:{$colour};color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;display:inline-block;'>Sign Your Document Now →</a>
</div>" .
($deadline ? "<div style='background:#fff8ed;border:1px solid #f6d860;border-radius:6px;padding:12px 16px;margin:0 0 16px;text-align:center;'><strong style='color:#92400e;'>⏰ Deadline: {$deadline}</strong></div>" : "") .
"<p style='font-size:13px;color:#666;margin-top:12px;'>If you have already signed this document, please disregard this message. For any questions, contact us at " . FR_FIRM_EMAIL . " or call " . FR_FIRM_PHONE . "</p>";

        $footer = FR_FIRM_NAME . " | " . FR_FIRM_ADDRESS;
        $html   = email_html($urgency . ': ' . $c['service'], $body, $footer);

        $sent = send_email(
            $c['email'],
            'Reminder ' . $remCount . ': Please sign your document — ' . FR_FIRM_NAME,
            $html
        );

        if ($sent) {
            $clients[$id]['reminders_sent']   = $remCount;
            $clients[$id]['last_reminder_at'] = date('c');
            save_clients($clients);
        }

        respond(['success' => $sent, 'reminder_count' => $remCount]);

    // GET CLIENT BY TOKEN (public — for signing page)
    case 'get_by_token':
        $token = clean($input['token'] ?? '');
        if (!$token) error('Invalid token');

        $clients = load_clients();
        foreach ($clients as $c) {
            if ($c['sign_token'] === $token) {
                if ($c['status'] === 'signed') error('This document has already been signed');
                $firmSigPath = FR_DATA_DIR . 'firm_sig.b64';
                $firmSig     = file_exists($firmSigPath) ? trim(file_get_contents($firmSigPath)) : '';
                respond(['success' => true, 'client' => [
                    'id'            => $c['id'],
                    'name'          => $c['name'],
                    'company'       => $c['company'],
                    'type'          => $c['type'],
                    'service'       => $c['service'],
                    'fee'           => $c['fee'] ?? '',
                    'custom_letter' => $c['custom_letter'] ?? '',
                    'firm_sig'      => $firmSig,
                ]]);
            }
        }
        error('Invalid or expired link', 404);
        break;

    // SAVE SIGNATURE (public — called from client page)
    case 'sign':
        $token     = clean($input['token'] ?? '');
        $sigData   = $input['sig_data'] ?? '';   // base64 canvas or typed name
        $sigMethod = clean($input['sig_method'] ?? 'draw');
        $hash      = clean($input['hash'] ?? '');
        $pdfB64    = $input['pdf'] ?? '';
        $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!$token || !$hash) error('Missing required fields');

        $clients = load_clients();
        $matched = null;
        foreach ($clients as $id => $c) {
            if ($c['sign_token'] === $token) { $matched = $id; break; }
        }
        if (!$matched) error('Invalid token', 404);
        if ($clients[$matched]['status'] === 'signed') error('Already signed');

        $clients[$matched]['status']     = 'signed';
        $clients[$matched]['signed_at']  = date('c');
        $clients[$matched]['signed_ip']  = $ip;
        $clients[$matched]['sig_method'] = $sigMethod;
        $clients[$matched]['doc_hash']   = $hash;
        $clients[$matched]['pdf_b64']    = '';
        save_clients($clients);

        // Save PDF on server as backup
        save_pdf($matched, $pdfB64);

        $c        = $clients[$matched];
        $signedAt = date('D d M Y \a\t H:i', strtotime($c['signed_at']));

        // ── Email YOU (accountant) with PDF attached ──────────
        $abody = "
<p>Good news — a client has just signed their engagement letter.</p>
<table style='width:100%;font-size:14px;border-collapse:collapse;margin-top:16px;'>
  <tr style='border-bottom:1px solid #eee;'><td style='padding:10px 0;color:#666;width:130px;font-weight:600;'>Client</td><td style='padding:10px 0;'><strong>{$c['name']}</strong></td></tr>
  <tr style='border-bottom:1px solid #eee;'><td style='padding:10px 0;color:#666;font-weight:600;'>Company</td><td style='padding:10px 0;'>{$c['company']}</td></tr>
  <tr style='border-bottom:1px solid #eee;'><td style='padding:10px 0;color:#666;font-weight:600;'>Service</td><td style='padding:10px 0;'>{$c['service']}</td></tr>
  <tr style='border-bottom:1px solid #eee;'><td style='padding:10px 0;color:#666;font-weight:600;'>Signed</td><td style='padding:10px 0;'>{$signedAt}</td></tr>
  <tr style='border-bottom:1px solid #eee;'><td style='padding:10px 0;color:#666;font-weight:600;'>IP Address</td><td style='padding:10px 0;'>{$c['signed_ip']}</td></tr>
  <tr><td style='padding:10px 0;color:#666;font-weight:600;'>Method</td><td style='padding:10px 0;'>" . ucfirst($sigMethod) . " signature</td></tr>
</table>
<p style='margin-top:16px;font-size:13px;color:#888;'>The signed audit report PDF is attached to this email.</p>
<p style='font-size:12px;color:#aaa;margin-top:8px;'>SHA-256: <span style='font-family:monospace;'>{$c['doc_hash']}</span></p>";

        send_email(
            FR_FIRM_EMAIL,
            "\xE2\x9C\x85 Signed: {$c['name']} — {$c['service']}",
            email_html('Document Signed', $abody, FR_FIRM_NAME . ' | ' . FR_FIRM_ADDRESS),
            null,
            $pdfB64,
            'SignedAudit_' . preg_replace('/[^a-zA-Z0-9]/', '_', $c['name']) . '.pdf'
        );

        // ── Email CLIENT — confirmation with their signed PDF ─────
        $cbody = "
<p>Dear <strong>{$c['name']}</strong>,</p>
<p style='margin-top:12px;'>This is to confirm that you have successfully signed your document with <strong>The Practice</strong>.</p>
<div style='background:#f0faf5;border:1px solid #a7f3d0;border-radius:6px;padding:16px 20px;margin:20px 0;'>
  <p style='font-size:15px;font-weight:700;color:#065f46;margin-bottom:8px;'>✓ Your signature has been received</p>
  <p style='font-size:13px;color:#374151;'>Document: <strong>{$c['service']}</strong><br>Signed: {$signedAt}</p>
</div>
<p style='font-size:14px;color:#374151;'>Your signed copy is attached to this email as a PDF. Please save it for your records.</p>
<p style='font-size:13px;color:#666;margin-top:12px;'>You do not need to do anything else. If you have any questions, please call us on <strong>" . FR_FIRM_PHONE . "</strong> or email <strong>" . FR_FIRM_EMAIL . "</strong></p>";

        send_email(
            $c['email'],
            'You have successfully signed your document — The Practice',
            email_html('Signature Confirmed', $cbody, FR_FIRM_NAME . ' | ' . FR_FIRM_EMAIL),
            null,
            $pdfB64,
            'SignedDocument_' . preg_replace('/[^a-zA-Z0-9]/', '_', $c['name']) . '.pdf'
        );

        respond(['success' => true]);
        break;

    // UPDATE AML CHECKLIST
    case 'update_aml':
        require_auth();
        $id = clean($input['id'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');

        $clients[$id]['aml_id_type']        = clean($input['aml_id_type'] ?? '');
        $clients[$id]['aml_id_ref']         = clean($input['aml_id_ref'] ?? '');
        $clients[$id]['aml_verified_date']  = clean($input['aml_verified_date'] ?? '');
        $clients[$id]['aml_risk']           = clean($input['aml_risk'] ?? 'Low');
        $clients[$id]['aml_notes']          = clean($input['aml_notes'] ?? '');
        $clients[$id]['aml_status']         = 'complete';
        $clients[$id]['aml_completed_date'] = date('c');
        save_clients($clients);
        respond(['success' => true]);
        break;

    // SAVE MTD RECORD
    case 'save_mtd':
        require_auth();
        $id = clean($input['id'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        $prevSub = $clients[$id]['mtd_next_sub'] ?? '';
        $newSub  = clean($input['mtd_next_sub'] ?? '');
        $clients[$id]['mtd_threshold']    = clean($input['mtd_threshold'] ?? '');
        $clients[$id]['mtd_status']       = clean($input['mtd_status'] ?? 'not_started');
        $clients[$id]['mtd_software']     = clean($input['mtd_software'] ?? '');
        $clients[$id]['mtd_enrol_date']   = clean($input['mtd_enrol_date'] ?? '');
        $clients[$id]['mtd_next_sub']     = $newSub;
        $clients[$id]['mtd_notes']        = htmlspecialchars(strip_tags(trim($input['mtd_notes'] ?? '')), ENT_QUOTES, 'UTF-8');
        // Reminder days — validate array of integers 0,1,3,7,14,28
        $allowed_days = [0, 1, 3, 7, 14, 28];
        $raw_days = is_array($input['mtd_reminder_days'] ?? null) ? $input['mtd_reminder_days'] : [];
        $clients[$id]['mtd_reminder_days'] = array_values(array_filter(
            array_map('intval', $raw_days),
            fn($d) => in_array($d, $allowed_days)
        ));
        // Reset sent log if submission date changed
        if ($prevSub !== $newSub) {
            $clients[$id]['mtd_reminders_sent'] = [];
        }
        save_clients($clients);
        respond(['success' => true]);
        break;

    // UPDATE LETTER & FEE
    case 'update_letter':
        require_auth();
        $id     = clean($input['id'] ?? '');
        $letter = $input['custom_letter'] ?? '';
        $fee    = clean($input['fee'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        $clients[$id]['custom_letter'] = $letter;
        $clients[$id]['fee']           = $fee;
        save_clients($clients);
        respond(['success' => true]);
        break;

    // SAVE NOTES
    case 'save_notes':
        require_auth();
        $id    = clean($input['id'] ?? '');
        $notes = $input['notes'] ?? '';
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        $clients[$id]['notes'] = htmlspecialchars(strip_tags(trim($notes)), ENT_QUOTES, 'UTF-8');
        save_clients($clients);
        respond(['success' => true]);
        break;

    // REMIND ALL OVERDUE
    case 'remind_all':
        require_auth();
        $clients = load_clients();
        $now = time(); $sent = 0; $failed = 0;
        foreach ($clients as $id => $c) {
            if ($c['status'] === 'signed' || empty($c['deadline_at']) || empty($c['sign_token'])) continue;
            if (strtotime($c['deadline_at']) > $now) continue;
            $link     = FR_BASE_URL . '/client.php?token=' . $c['sign_token'];
            $remCount = intval($c['reminders_sent'] ?? 0) + 1;
            $deadline = date('l j F Y \a\t g:ia', strtotime($c['deadline_at']));
            $colour   = $remCount === 1 ? '#1a3558' : ($remCount === 2 ? '#b45309' : '#c0392b');
            $urgency  = $remCount === 1 ? 'Gentle reminder' : ($remCount === 2 ? 'Second reminder — action required' : 'Final reminder — urgent');
            $body = "<p>Dear <strong>{$c['name']}</strong>,</p><p>We are following up on your unsigned engagement letter. Please sign at your earliest convenience.</p>
<div style='text-align:center;margin:24px 0;'><a href='{$link}' style='background:{$colour};color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;display:inline-block;'>Sign Your Document Now →</a></div>
<div style='background:#fff8ed;border:1px solid #f6d860;border-radius:6px;padding:12px 16px;margin:0 0 16px;text-align:center;'><strong style='color:#92400e;'>Deadline was: {$deadline}</strong></div>
<p style='font-size:13px;color:#666;'>Contact us at " . FR_FIRM_EMAIL . " or " . FR_FIRM_PHONE . " if you have questions.</p>";
            $ok = send_email($c['email'], "Reminder {$remCount}: Please sign your document — " . FR_FIRM_NAME,
                email_html($urgency . ': ' . $c['service'], $body, FR_FIRM_NAME . ' | ' . FR_FIRM_ADDRESS));
            if ($ok) { $clients[$id]['reminders_sent'] = $remCount; $clients[$id]['last_reminder_at'] = date('c'); $sent++; }
            else $failed++;
        }
        save_clients($clients);
        respond(['success' => true, 'sent' => $sent, 'failed' => $failed]);
        break;

    // EXPORT CSV
    case 'export_csv':
        require_auth();
        $clients = load_clients();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="clients_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Name','Email','Company','Type','Service','Fee','Status','Signed At','Deadline','Reminders Sent','AML Status','AML Risk','MTD Threshold','MTD Status','MTD Software','MTD Next Submission','Notes','Created']);
        foreach ($clients as $c) {
            fputcsv($out, [
                $c['name'], $c['email'], $c['company']??'', $c['type'], $c['service'], $c['fee']??'', $c['status'],
                $c['signed_at'] ? date('d/m/Y H:i', strtotime($c['signed_at'])) : '',
                $c['deadline_at'] ? date('d/m/Y H:i', strtotime($c['deadline_at'])) : '',
                $c['reminders_sent']??0, $c['aml_status'], $c['aml_risk']??'',
                $c['mtd_threshold']??'', $c['mtd_status']??'', $c['mtd_software']??'',
                $c['mtd_next_sub'] ? date('d/m/Y', strtotime($c['mtd_next_sub'])) : '',
                $c['notes']??'',
                $c['created_at'] ? date('d/m/Y H:i', strtotime($c['created_at'])) : '',
            ]);
        }
        fclose($out); exit;

    // DOWNLOAD PDF (accountant only — requires auth via GET session)
    case 'download_pdf':
        session_start();
        if (empty($_SESSION['fr_auth'])) {
            http_response_code(401);
            echo 'Unauthorised — please log in to your dashboard first.';
            exit;
        }
        $id = clean($_GET['id'] ?? '');
        $file = FR_DATA_DIR . 'pdfs/' . $id . '.pdf';
        if (!$id || !file_exists($file)) {
            http_response_code(404);
            echo 'PDF not found.';
            exit;
        }
        $clients = load_clients();
        $name = isset($clients[$id]) ? $clients[$id]['name'] : 'Audit';
        $name = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $name);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Audit_' . $name . '.pdf"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;

    // DELETE CLIENT
    case 'delete_client':
        require_auth();
        $id = clean($input['id'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        unset($clients[$id]);
        save_clients($clients);
        respond(['success' => true]);
        break;

    // ── DEADLINE TRACKER ────────────────────────────────────────────

    case 'save_deadlines':
        require_auth();
        $id = clean($input['id'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        // Validate year end date format
        $ye = clean($input['dl_year_end'] ?? '');
        if ($ye && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ye)) $ye = '';
        $cs = clean($input['dl_confirmation_due'] ?? '');
        if ($cs && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $cs)) $cs = '';
        $vat_opts = ['none','jan','feb','mar'];
        $pay_opts = ['none','monthly','weekly'];
        $clients[$id]['dl_year_end']         = $ye;
        $clients[$id]['dl_confirmation_due'] = $cs;
        $clients[$id]['dl_vat']              = in_array($input['dl_vat'] ?? '', $vat_opts) ? $input['dl_vat'] : 'none';
        $clients[$id]['dl_payroll']          = in_array($input['dl_payroll'] ?? '', $pay_opts) ? $input['dl_payroll'] : 'none';
        $clients[$id]['dl_self_assessment']  = !empty($input['dl_self_assessment']);
        save_clients($clients);
        respond(['success' => true]);
        break;

    case 'mark_deadline':
        require_auth();
        $id  = clean($input['id'] ?? '');
        $key = trim($input['key'] ?? '');
        $allowed_statuses = ['pending','filed','paid'];
        $status = in_array($input['status'] ?? '', $allowed_statuses) ? $input['status'] : 'pending';
        // Validate key format: word:YYYY-MM-DD or word_word:YYYY-MM-DD
        if (!preg_match('/^[\w_]+:\d{4}-\d{2}-\d{2}$/', $key)) error('Invalid deadline key');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        if (!isset($clients[$id]['dl_statuses']) || !is_array($clients[$id]['dl_statuses'])) {
            $clients[$id]['dl_statuses'] = [];
        }
        $clients[$id]['dl_statuses'][$key] = $status;
        save_clients($clients);
        respond(['success' => true]);
        break;

    // ── COMPANIES HOUSE LOOKUP ──────────────────────────────────────

    case 'ch_lookup':
        require_auth();
        $query = trim($input['query'] ?? '');
        if (strlen($query) < 2) error('Query too short');
        $apiKey = FR_CH_API_KEY;
        if (strpos($apiKey, 'YOUR_') === 0) error('Companies House API key not configured. Add FR_CH_API_KEY to config.php');
        $url = 'https://api.company-information.service.gov.uk/search/companies?q=' . urlencode($query) . '&items_per_page=8';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $apiKey . ':',
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_USERAGENT      => 'FirmReady/1.0',
        ]);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || !$raw) error('Companies House API error — check your API key');
        $data = json_decode($raw, true);
        $results = [];
        foreach (($data['items'] ?? []) as $item) {
            if (($item['company_status'] ?? '') === 'dissolved') continue;
            $addr = $item['registered_office_address'] ?? [];
            $results[] = [
                'title'          => $item['title'] ?? '',
                'company_number' => $item['company_number'] ?? '',
                'type'           => $item['company_type'] ?? '',
                'status'         => $item['company_status'] ?? '',
                'address_line_1' => $addr['address_line_1'] ?? '',
                'address_line_2' => $addr['address_line_2'] ?? '',
                'locality'       => $addr['locality'] ?? '',
                'region'         => $addr['region'] ?? '',
                'postal_code'    => $addr['postal_code'] ?? '',
                'date_of_creation' => $item['date_of_creation'] ?? '',
            ];
        }
        respond(['ok' => true, 'results' => $results]);
        break;

    // ── DIGITAL CLIENT ONBOARDING ────────────────────────────────────

    case 'create_onboard':
        require_auth();
        $name  = htmlspecialchars(strip_tags(trim($input['name']  ?? '')), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$name || !$email) error('Name and email are required');
        $clients = load_clients();
        // Check for duplicate email already onboarding/active
        foreach ($clients as $c) {
            if (strtolower($c['email']) === strtolower($email) && ($c['status'] ?? '') !== 'archived') {
                error('A client with this email already exists');
            }
        }
        $token = bin2hex(random_bytes(32));
        $id    = 'c_' . bin2hex(random_bytes(6));
        $clients[$id] = [
            'id'           => $id,
            'name'         => $name,
            'email'        => $email,
            'company'      => '',
            'type'         => '',
            'service'      => '',
            'phone'        => '',
            'fee'          => '',
            'notes'        => '',
            'status'       => 'onboarding',
            'onboard_token'=> $token,
            'created_at'   => date('c'),
            'aml_status'   => 'pending',
        ];
        save_clients($clients);
        // Send onboarding email
        $link    = FR_BASE_URL . '/onboard.php?token=' . $token;
        $firm    = FR_FIRM_NAME;
        $subject = "Welcome — Please Complete Your Details | {$firm}";
        $html    = "<!DOCTYPE html><html><body style=\"font-family:'Segoe UI',Arial,sans-serif;color:#1a2433;background:#f8f7f4\">
<table width='600' align='center' style='background:#fff;border:1px solid #ddd8cf;margin:32px auto;border-radius:4px;overflow:hidden'>
  <tr><td style='background:#1a3558;padding:24px 32px'>
    <span style='font-size:20px;font-weight:800;color:#fff'>{$firm}</span>
  </td></tr>
  <tr><td style='padding:32px'>
    <p>Dear {$name},</p>
    <p>Thank you for choosing {$firm}. To get started, please take a moment to complete your details using the link below.</p>
    <p style='margin:24px 0'><a href='{$link}' style='background:#1a3558;color:#fff;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:700'>Complete My Details</a></p>
    <p>If the button doesn't work, copy and paste this link:<br><a href='{$link}'>{$link}</a></p>
    <p>This takes less than 2 minutes.</p>
    <p>Kind regards,<br><strong>{$firm}</strong><br>" . FR_FIRM_EMAIL . " | " . FR_FIRM_PHONE . "</p>
  </td></tr>
</table></body></html>";
        $boundary = md5(uniqid());
        $headers  = implode("\r\n", [
            "From: {$firm} <" . FR_FROM_EMAIL . ">",
            'MIME-Version: 1.0',
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        ]);
        $body = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nDear {$name},\n\nPlease complete your details: {$link}\n\nKind regards,\n{$firm}\r\n"
              . "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n--{$boundary}--";
        mail($email, $subject, $body, $headers);
        respond(['success' => true, 'link' => $link]);
        break;

    case 'get_onboard_by_token':
        $token   = clean($input['token'] ?? '');
        $clients = load_clients();
        $found   = null;
        foreach ($clients as $c) {
            if (($c['onboard_token'] ?? '') === $token && ($c['status'] ?? '') === 'onboarding') {
                $found = $c; break;
            }
        }
        if (!$found) error('Onboarding link not found or already completed');
        respond(['ok' => true, 'client' => ['name' => $found['name'], 'email' => $found['email']]]);
        break;

    case 'submit_onboard':
        $token   = clean($input['token'] ?? '');
        $clients = load_clients();
        $id      = null;
        foreach ($clients as $cid => $c) {
            if (($c['onboard_token'] ?? '') === $token && ($c['status'] ?? '') === 'onboarding') {
                $id = $cid; break;
            }
        }
        if (!$id) error('Onboarding link not found or already used');
        $clients[$id]['company']      = htmlspecialchars(strip_tags(trim($input['company'] ?? '')), ENT_QUOTES, 'UTF-8');
        $clients[$id]['type']         = clean($input['type'] ?? '');
        $clients[$id]['phone']        = clean($input['phone'] ?? '');
        $clients[$id]['service']      = htmlspecialchars(strip_tags(trim($input['service'] ?? '')), ENT_QUOTES, 'UTF-8');
        $clients[$id]['onboard_notes']= htmlspecialchars(strip_tags(trim($input['notes'] ?? '')), ENT_QUOTES, 'UTF-8');
        $clients[$id]['onboard_submitted_at'] = date('c');
        $clients[$id]['status']       = 'onboarded'; // waiting for accountant to activate
        save_clients($clients);
        // Notify accountant
        $name = $clients[$id]['name'];
        mail(FR_FIRM_EMAIL, "New Client Onboarding Completed — {$name}", "Client {$name} ({$clients[$id]['email']}) has submitted their onboarding details.\n\nLog in to review and activate: " . FR_BASE_URL . "/dashboard.php", "From: " . FR_FROM_EMAIL);
        respond(['success' => true]);
        break;

    case 'activate_onboard':
        require_auth();
        $id      = clean($input['id'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        if (!in_array($clients[$id]['status'], ['onboarding','onboarded'])) error('Client is not in onboarding status');
        $clients[$id]['status']      = 'pending';
        $clients[$id]['sign_token']  = bin2hex(random_bytes(32));
        $clients[$id]['activated_at']= date('c');
        unset($clients[$id]['onboard_token']);
        save_clients($clients);
        respond(['success' => true]);
        break;

    // ── GDPR DATA PROCESSING AGREEMENT ──────────────────────────────

    case 'send_dpa':
        require_auth();
        $id      = clean($input['id'] ?? '');
        $clients = load_clients();
        if (!isset($clients[$id])) error('Client not found');
        $c       = $clients[$id];
        $firm    = FR_FIRM_NAME;
        $firmAddr= FR_FIRM_ADDRESS;
        $firmEmail= FR_FIRM_EMAIL;
        $ico     = FR_ICO_NUMBER;
        $today   = date('j F Y');
        $clientName = $c['name'];
        $clientCo   = $c['company'] ?: $c['name'];
        // Generate DPA text
        $dpaBody = "DATA PROCESSING AGREEMENT\n"
            . "════════════════════════════════════════════════════════════\n\n"
            . "This Data Processing Agreement (\"Agreement\") is entered into on {$today} between:\n\n"
            . "DATA CONTROLLER:\n"
            . "{$clientCo}\n"
            . "Represented by: {$clientName}\n"
            . "(\"the Controller\")\n\n"
            . "DATA PROCESSOR:\n"
            . "{$firm}\n"
            . "{$firmAddr}\n"
            . "Email: {$firmEmail}\n"
            . "ICO Registration: {$ico}\n"
            . "(\"the Processor\")\n\n"
            . "════════════════════════════════════════════════════════════\n\n"
            . "1. PURPOSE AND SCOPE\n\n"
            . "1.1 The Processor provides accountancy and related professional services to the Controller. In doing so, the Processor processes personal data on behalf of the Controller as described in Schedule 1 of this Agreement.\n\n"
            . "1.2 This Agreement governs the processing of personal data in accordance with the UK General Data Protection Regulation (UK GDPR) and the Data Protection Act 2018, as required by Article 28 UK GDPR.\n\n"
            . "2. PROCESSOR OBLIGATIONS\n\n"
            . "The Processor agrees to:\n\n"
            . "2.1 Process personal data only on documented instructions from the Controller, including with regard to transfers to third countries.\n\n"
            . "2.2 Ensure that all personnel authorised to process personal data are subject to a duty of confidentiality.\n\n"
            . "2.3 Implement appropriate technical and organisational measures to ensure a level of security appropriate to the risk, including:\n"
            . "    (a) Encryption of personal data at rest and in transit\n"
            . "    (b) Ability to ensure ongoing confidentiality, integrity, availability and resilience of processing systems\n"
            . "    (c) Regular testing and evaluation of technical and organisational security measures\n\n"
            . "2.4 Not engage sub-processors without prior specific or general written authorisation from the Controller. Current sub-processors: Hostinger International Ltd (hosting, UK/EU-compliant).\n\n"
            . "2.5 Assist the Controller in responding to data subject rights requests (access, rectification, erasure, portability, restriction, objection).\n\n"
            . "2.6 Delete or return all personal data to the Controller at the end of the provision of services.\n\n"
            . "2.7 Provide all information necessary to demonstrate compliance with this Agreement and allow for audits.\n\n"
            . "2.8 Notify the Controller without undue delay (within 72 hours) of becoming aware of a personal data breach.\n\n"
            . "3. CONTROLLER OBLIGATIONS\n\n"
            . "3.1 The Controller confirms they have a lawful basis for processing under Article 6 UK GDPR for all personal data provided to the Processor.\n\n"
            . "3.2 The Controller is responsible for the accuracy of personal data provided to the Processor.\n\n"
            . "3.3 The Controller shall promptly notify the Processor of any changes to processing instructions.\n\n"
            . "4. DATA DETAILS — SCHEDULE 1\n\n"
            . "Categories of data subjects: Individuals who are clients, employees, or directors of the Controller\n"
            . "Categories of personal data: Names, addresses, dates of birth, National Insurance numbers, tax records, financial information, contact details\n"
            . "Special categories: None (unless otherwise agreed in writing)\n"
            . "Nature of processing: Storage, analysis, preparation of tax returns and accounts, submission to HMRC and Companies House\n"
            . "Duration: For the duration of the engagement and as required by HMRC record-keeping obligations (minimum 6 years)\n\n"
            . "5. GOVERNING LAW\n\n"
            . "This Agreement is governed by the laws of England and Wales. Any disputes shall be subject to the exclusive jurisdiction of the courts of England and Wales.\n\n"
            . "════════════════════════════════════════════════════════════\n\n"
            . "By signing below, the Controller agrees to the terms of this Data Processing Agreement.\n\n"
            . "Signed for and on behalf of {$clientCo}:\n\n"
            . "___________________________\n"
            . "{$clientName}\n"
            . "Date: {$today}";
        // Store as a letter record
        $lettersPath = FR_DATA_DIR . 'letters.json';
        $letters     = file_exists($lettersPath) ? (json_decode(file_get_contents($lettersPath), true) ?: []) : [];
        $lToken      = bin2hex(random_bytes(32));
        $lid         = 'l_' . bin2hex(random_bytes(6));
        $letters[$lid] = [
            'id'               => $lid,
            'type'             => 'dpa',
            'client_id'        => $id,
            'recipient_name'   => $clientName,
            'recipient_email'  => $c['email'],
            'subject'          => 'Data Processing Agreement — ' . $firm,
            'body'             => $dpaBody,
            'category'         => 'Compliance',
            'token'            => $lToken,
            'status'           => 'sent',
            'requires_ack'     => true,
            'created_at'       => date('c'),
            'sent_at'          => date('c'),
        ];
        $fp = fopen($lettersPath, 'c+');
        flock($fp, LOCK_EX);
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($letters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN); fclose($fp);
        // Update client DPA status
        $clients[$id]['dpa_status']  = 'sent';
        $clients[$id]['dpa_sent_at'] = date('c');
        $clients[$id]['dpa_letter_id'] = $lid;
        save_clients($clients);
        // Send email
        $link    = FR_BASE_URL . '/letter_view.php?token=' . $lToken;
        $subject = "Data Processing Agreement — Action Required | {$firm}";
        $html    = "<!DOCTYPE html><html><body style=\"font-family:'Segoe UI',Arial,sans-serif;color:#1a2433;background:#f8f7f4\">
<table width='600' align='center' style='background:#fff;border:1px solid #ddd8cf;margin:32px auto;border-radius:4px;overflow:hidden'>
  <tr><td style='background:#1a3558;padding:24px 32px'>
    <span style='font-size:20px;font-weight:800;color:#fff'>{$firm}</span>
    <span style='display:block;color:#c9a84c;font-size:13px;margin-top:4px'>Data Processing Agreement</span>
  </td></tr>
  <tr><td style='padding:32px'>
    <p>Dear {$clientName},</p>
    <p>As required under UK GDPR Article 28, please review and acknowledge the Data Processing Agreement between <strong>{$clientCo}</strong> and <strong>{$firm}</strong>.</p>
    <p>This agreement sets out how we handle personal data on your behalf as part of our accountancy services.</p>
    <p style='margin:24px 0'><a href='{$link}' style='background:#1a3558;color:#fff;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:700'>Review &amp; Accept DPA</a></p>
    <p style='font-size:12px;color:#888'>Or copy this link: {$link}</p>
    <p>Kind regards,<br><strong>{$firm}</strong></p>
  </td></tr>
</table></body></html>";
        $bnd  = md5(uniqid());
        $hdrs = implode("\r\n", ["From: {$firm} <" . FR_FROM_EMAIL . ">", 'MIME-Version: 1.0', "Content-Type: multipart/alternative; boundary=\"{$bnd}\""]);
        $mbody= "--{$bnd}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nDear {$clientName},\n\nPlease review your Data Processing Agreement: {$link}\n\n{$firm}\r\n"
              . "--{$bnd}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n--{$bnd}--";
        mail($c['email'], $subject, $mbody, $hdrs);
        respond(['success' => true, 'link' => $link]);
        break;

    // ── LETTERHEAD ──────────────────────────────────────────────────

    case 'get_letterhead':
        require_auth();
        $path = FR_DATA_DIR . 'letterhead.json';
        $defaults = [
            'logo'         => '',
            'tagline'      => 'Professional Accountancy Services',
            'brand_colour' => '#1a3558',
            'accent_colour'=> '#c9a84c',
            'footer_text'  => 'Registered in England & Wales | ICO Registration ' . FR_ICO_NUMBER,
            'disclaimer'   => 'This communication is confidential and intended solely for the named recipient(s). If you have received this in error, please notify us immediately and delete it.',
            'social_linkedin' => '',
            'social_twitter'  => '',
        ];
        $lh = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        respond(['success' => true, 'letterhead' => array_merge($defaults, $lh ?: [])]);
        break;

    case 'save_letterhead':
        require_auth();
        $allowed = ['logo','tagline','brand_colour','accent_colour','footer_text','disclaimer','social_linkedin','social_twitter'];
        $data = [];
        foreach ($allowed as $k) {
            if (isset($input[$k])) $data[$k] = $input[$k];
        }
        file_put_contents(FR_DATA_DIR . 'letterhead.json', json_encode($data, JSON_PRETTY_PRINT));
        respond(['success' => true]);
        break;

    // ── CORRESPONDENCE / LETTERS ────────────────────────────────────

    case 'get_letters':
        require_auth();
        $path = FR_DATA_DIR . 'letters.json';
        $letters = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        respond(['success' => true, 'letters' => array_values($letters ?: [])]);
        break;

    case 'send_letter': {
        require_auth();
        $subject    = clean($input['subject'] ?? '');
        $body       = $input['body'] ?? '';
        $recipName  = clean($input['recipient_name'] ?? '');
        $recipEmail = trim($input['recipient_email'] ?? '');
        $recipType  = clean($input['recipient_type'] ?? 'client');
        $category   = clean($input['category'] ?? 'General');
        $templateId = clean($input['template_id'] ?? '');
        $clientId   = clean($input['linked_client_id'] ?? '');
        $requireAck = !empty($input['require_acknowledgement']);

        if (!$subject || !$body || !$recipName || !filter_var($recipEmail, FILTER_VALIDATE_EMAIL)) {
            error('Subject, body, recipient name and valid email are required');
        }

        $lpath   = FR_DATA_DIR . 'letters.json';
        $letters = file_exists($lpath) ? (json_decode(file_get_contents($lpath), true) ?: []) : [];

        $id    = 'l_' . bin2hex(random_bytes(6));
        $token = bin2hex(random_bytes(32));

        $lhPath = FR_DATA_DIR . 'letterhead.json';
        $lh     = file_exists($lhPath) ? (json_decode(file_get_contents($lhPath), true) ?: []) : [];
        $firmSigPath = FR_DATA_DIR . 'firm_sig.b64';
        $firmSig     = file_exists($firmSigPath) ? trim(file_get_contents($firmSigPath)) : '';

        $letters[$id] = [
            'id'                    => $id,
            'template_id'           => $templateId,
            'category'              => $category,
            'subject'               => $subject,
            'recipient_name'        => $recipName,
            'recipient_email'       => $recipEmail,
            'recipient_type'        => $recipType,
            'linked_client_id'      => $clientId,
            'body'                  => $body,
            'status'                => 'sent',
            'token'                 => $token,
            'require_acknowledgement'=> $requireAck,
            'sent_at'               => date('c'),
            'read_at'               => null,
            'acknowledged_at'       => null,
            'sent_by_ip'            => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'letter_hash'           => hash('sha256', $body . $recipEmail . date('c')),
            'created_at'            => date('c'),
        ];

        $fp = fopen($lpath, 'c+'); flock($fp, LOCK_EX);
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($letters, JSON_PRETTY_PRINT));
        flock($fp, LOCK_UN); fclose($fp);

        // Send email
        $viewLink  = FR_BASE_URL . '/letter_view.php?token=' . $token;
        $brandCol  = $lh['brand_colour'] ?? '#1a3558';
        $accentCol = $lh['accent_colour'] ?? '#c9a84c';
        $ackLine   = $requireAck
            ? "<div style='text-align:center;margin:20px 0'><a href='{$viewLink}#acknowledge' style='background:{$brandCol};color:{$accentCol};padding:12px 28px;border-radius:5px;text-decoration:none;font-weight:bold;font-size:14px;display:inline-block'>View &amp; Acknowledge →</a></div>"
            : "<div style='text-align:center;margin:20px 0'><a href='{$viewLink}' style='background:{$brandCol};color:{$accentCol};padding:12px 28px;border-radius:5px;text-decoration:none;font-weight:bold;font-size:14px;display:inline-block'>View Letter →</a></div>";

        $emailBody = "<p>Dear <strong>{$recipName}</strong>,</p>"
            . "<p>Please find correspondence from <strong>" . FR_FIRM_NAME . "</strong> below.</p>"
            . $ackLine
            . "<p style='font-size:12px;color:#666;'>If you cannot click the button, copy this link into your browser:<br>{$viewLink}</p>";

        $footer = FR_FIRM_NAME . " | " . FR_FIRM_ADDRESS . " | ICO: " . FR_ICO_NUMBER;
        $html   = email_html($subject, $emailBody, $footer);
        $sent   = send_email($recipEmail, $subject . ' — ' . FR_FIRM_NAME, $html);

        respond(['success' => true, 'sent' => $sent, 'id' => $id, 'link' => $viewLink]);
        break;
    }

    case 'get_letter_by_token': {
        $token = clean($input['token'] ?? '');
        if (!$token) error('Invalid token');
        $lpath   = FR_DATA_DIR . 'letters.json';
        $letters = file_exists($lpath) ? (json_decode(file_get_contents($lpath), true) ?: []) : [];
        foreach ($letters as $l) {
            if ($l['token'] === $token) {
                $lhPath = FR_DATA_DIR . 'letterhead.json';
                $lh     = file_exists($lhPath) ? (json_decode(file_get_contents($lhPath), true) ?: []) : [];
                $firmSigPath = FR_DATA_DIR . 'firm_sig.b64';
                $firmSig     = file_exists($firmSigPath) ? trim(file_get_contents($firmSigPath)) : '';
                respond(['success' => true, 'letter' => $l, 'letterhead' => $lh, 'firm_sig' => $firmSig]);
            }
        }
        error('Letter not found', 404);
        break;
    }

    case 'mark_letter_read': {
        $token = clean($input['token'] ?? '');
        if (!$token) error('Invalid token');
        $lpath   = FR_DATA_DIR . 'letters.json';
        $letters = file_exists($lpath) ? (json_decode(file_get_contents($lpath), true) ?: []) : [];
        foreach ($letters as $id => $l) {
            if ($l['token'] === $token && !$l['read_at']) {
                $letters[$id]['read_at'] = date('c');
                $letters[$id]['status']  = 'read';
                $fp = fopen($lpath, 'c+'); flock($fp, LOCK_EX);
                ftruncate($fp, 0); rewind($fp);
                fwrite($fp, json_encode($letters, JSON_PRETTY_PRINT));
                flock($fp, LOCK_UN); fclose($fp);
                respond(['success' => true]);
            }
        }
        respond(['success' => true]); // already read or not found — silent ok
        break;
    }

    case 'acknowledge_letter': {
        $token = clean($input['token'] ?? '');
        if (!$token) error('Invalid token');
        $lpath   = FR_DATA_DIR . 'letters.json';
        $letters = file_exists($lpath) ? (json_decode(file_get_contents($lpath), true) ?: []) : [];
        foreach ($letters as $id => $l) {
            if ($l['token'] === $token) {
                $letters[$id]['acknowledged_at'] = date('c');
                $letters[$id]['status']          = 'acknowledged';
                if (empty($letters[$id]['read_at'])) $letters[$id]['read_at'] = date('c');
                $fp = fopen($lpath, 'c+'); flock($fp, LOCK_EX);
                ftruncate($fp, 0); rewind($fp);
                fwrite($fp, json_encode($letters, JSON_PRETTY_PRINT));
                flock($fp, LOCK_UN); fclose($fp);
                // If this was a DPA letter, update the client record
                if (($l['type'] ?? '') === 'dpa' && !empty($l['client_id'])) {
                    $clients = load_clients();
                    if (isset($clients[$l['client_id']])) {
                        $clients[$l['client_id']]['dpa_status']  = 'signed';
                        $clients[$l['client_id']]['dpa_signed_at'] = date('c');
                        save_clients($clients);
                    }
                }
                respond(['success' => true]);
            }
        }
        error('Letter not found', 404);
        break;
    }

    case 'delete_letter':
        require_auth();
        $id = clean($input['id'] ?? '');
        $lpath = FR_DATA_DIR . 'letters.json';
        $letters = file_exists($lpath) ? (json_decode(file_get_contents($lpath), true) ?: []) : [];
        unset($letters[$id]);
        file_put_contents($lpath, json_encode($letters, JSON_PRETTY_PRINT));
        respond(['success' => true]);
        break;

    case 'get_custom_templates':
        require_auth();
        $path = FR_DATA_DIR . 'custom_templates.json';
        $tmpls = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
        respond(['success' => true, 'templates' => array_values($tmpls)]);
        break;

    case 'save_custom_template': {
        require_auth();
        $tId   = clean($input['id'] ?? '') ?: 'ct_' . bin2hex(random_bytes(5));
        $path  = FR_DATA_DIR . 'custom_templates.json';
        $tmpls = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
        $tmpls[$tId] = [
            'id'         => $tId,
            'name'       => clean($input['name'] ?? 'Custom Template'),
            'category'   => clean($input['category'] ?? 'Custom'),
            'subject'    => clean($input['subject'] ?? ''),
            'body'       => $input['body'] ?? '',
            'updated_at' => date('c'),
            'created_at' => $tmpls[$tId]['created_at'] ?? date('c'),
        ];
        file_put_contents($path, json_encode($tmpls, JSON_PRETTY_PRINT));
        respond(['success' => true, 'id' => $tId]);
        break;
    }

    case 'delete_custom_template': {
        require_auth();
        $tId  = clean($input['id'] ?? '');
        $path = FR_DATA_DIR . 'custom_templates.json';
        $tmpls = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
        unset($tmpls[$tId]);
        file_put_contents($path, json_encode($tmpls, JSON_PRETTY_PRINT));
        respond(['success' => true]);
        break;
    }

    // SAVE FIRM SIGNATURE
    case 'save_firm_sig':
        require_auth();
        $sig = $input['sig'] ?? '';
        if (!$sig || !preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $sig)) {
            error('Invalid signature — must be a PNG or JPG image');
        }
        file_put_contents(FR_DATA_DIR . 'firm_sig.b64', $sig);
        respond(['success' => true]);
        break;

    // GET FIRM SIGNATURE
    case 'get_firm_sig':
        require_auth();
        $path = FR_DATA_DIR . 'firm_sig.b64';
        respond(['success' => true, 'sig' => file_exists($path) ? trim(file_get_contents($path)) : '']);
        break;

    default:
        error('Unknown action', 404);
}
