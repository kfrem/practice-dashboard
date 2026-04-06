<?php
// ============================================================
// MTD AUTOMATIC REMINDER CRON SCRIPT
// The Practice — FirmReady
//
// Run daily via Hostinger Cron Jobs:
//   Command: php /home/u609349519/public_html/practice/mtd_cron.php
//   Schedule: 0 8 * * *  (every day at 8:00 AM)
//
// Sends reminder emails to BOTH the client and the accountant
// when a client's MTD quarterly submission is approaching.
// ============================================================

// Only allow CLI execution (not browser access)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script runs via cron only.');
}

require_once __DIR__ . '/config.php';

// ── helpers ─────────────────────────────────────────────────

function load_clients_cron() {
    $path = FR_DATA_DIR . 'clients.json';
    if (!file_exists($path)) return [];
    $fp = fopen($path, 'r+');
    flock($fp, LOCK_EX);
    $data = json_decode(stream_get_contents($fp), true) ?: [];
    flock($fp, LOCK_UN);
    fclose($fp);
    return $data;
}

function save_clients_cron(array $clients) {
    $path = FR_DATA_DIR . 'clients.json';
    $fp   = fopen($path, 'c+');
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode(array_values($clients), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($fp, LOCK_UN);
    fclose($fp);
}

function log_msg(string $msg) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    $logPath = FR_DATA_DIR . 'mtd_cron.log';
    file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
}

function send_reminder_email(array $client, int $daysLeft, string $recipient, string $toEmail, string $toName) {
    $firmName    = FR_FIRM_NAME;
    $firmEmail   = FR_FIRM_EMAIL;
    $firmPhone   = FR_FIRM_PHONE;
    $subDate     = date('j F Y', strtotime($client['mtd_next_sub']));
    $clientName  = $client['name'];
    $software    = $client['mtd_software'] ?: 'your MTD-compatible software';

    if ($daysLeft === 0) {
        $urgency = 'TODAY is';
        $subject = "⚠️ MTD Submission Due TODAY — {$clientName}";
    } elseif ($daysLeft === 1) {
        $urgency = 'TOMORROW is';
        $subject = "⚠️ MTD Submission Due TOMORROW — {$clientName}";
    } else {
        $urgency = "in {$daysLeft} days is";
        $subject = "MTD Reminder: Quarterly Submission Due in {$daysLeft} Days — {$clientName}";
    }

    if ($recipient === 'client') {
        $greeting = "Dear {$toName},";
        $intro    = "This is a reminder from {$firmName} that your quarterly MTD (Making Tax Digital) submission is due {$urgency} <strong>{$subDate}</strong>.";
        $action   = "Please ensure your records are up to date in {$software} and that your quarterly update has been submitted to HMRC before the deadline.";
        $closing  = "If you have any questions or need help with your submission, please contact us and we will be happy to assist.";
    } else {
        $greeting = "Dear {$firmName} Team,";
        $intro    = "This is an automated reminder that <strong>{$clientName}</strong>'s quarterly MTD submission is due {$urgency} <strong>{$subDate}</strong>.";
        $action   = "Please review their records in {$software} and ensure the quarterly update is submitted to HMRC on time.";
        $closing  = "Client email: <a href=\"mailto:{$client['email']}\">{$client['email']}</a>" .
                    ($client['phone'] ? " | Phone: {$client['phone']}" : '');
    }

    $html = "<!DOCTYPE html><html><body style=\"font-family:'Segoe UI',Arial,sans-serif;color:#1a2433;background:#f8f7f4;margin:0;padding:0\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f8f7f4\"><tr><td>
<table width=\"600\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#fff;border:1px solid #ddd8cf;margin:32px auto;border-radius:4px;overflow:hidden\">
  <tr><td style=\"background:#1a3558;padding:24px 32px\">
    <span style=\"font-size:20px;font-weight:800;color:#fff\">{$firmName}</span>
    <span style=\"font-size:13px;color:#c9a84c;display:block;margin-top:4px\">MTD Compliance Reminder</span>
  </td></tr>
  <tr><td style=\"padding:32px\">
    <p style=\"margin:0 0 16px\">{$greeting}</p>
    <p style=\"margin:0 0 16px\">{$intro}</p>
    <div style=\"background:#fff7ed;border-left:4px solid #c9a84c;padding:14px 18px;margin:20px 0;border-radius:0 4px 4px 0\">
      <strong>📅 Submission Due: {$subDate}</strong>
    </div>
    <p style=\"margin:0 0 16px\">{$action}</p>
    <p style=\"margin:0 0 24px;color:#555;font-size:13px\">{$closing}</p>
    <p style=\"margin:0;color:#888;font-size:12px;border-top:1px solid #eee;padding-top:16px\">
      {$firmName} &bull; {$firmEmail} &bull; {$firmPhone}<br>
      Automated reminder sent by FirmReady — The Practice
    </p>
  </td></tr>
</table>
</td></tr></table>
</body></html>";

    $boundary = md5(uniqid());
    $headers  = implode("\r\n", [
        'From: ' . $firmName . ' <' . FR_FROM_EMAIL . '>',
        'Reply-To: ' . FR_FROM_EMAIL,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'X-Mailer: FirmReady-MTD-Cron',
    ]);

    $plain = strip_tags(str_replace(['<br>', '<br/>'], "\n", $html));
    $body  = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$plain}\r\n" .
             "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n" .
             "--{$boundary}--";

    return mail($toEmail, $subject, $body, $headers);
}

// ── main ─────────────────────────────────────────────────────

log_msg('MTD cron started');

$clients   = load_clients_cron();
$today     = new DateTimeImmutable('today');
$sent      = 0;
$checked   = 0;
$modified  = false;

foreach ($clients as &$client) {
    // Must have a submission date and reminder days configured
    if (empty($client['mtd_next_sub']) || empty($client['mtd_reminder_days'])) continue;
    if (!is_array($client['mtd_reminder_days'])) continue;

    // Skip exempt clients
    if (($client['mtd_status'] ?? '') === 'exempt') continue;

    // Parse submission date
    $subDate = DateTimeImmutable::createFromFormat('Y-m-d', $client['mtd_next_sub']);
    if (!$subDate) continue;

    $checked++;
    $daysLeft = (int) $today->diff($subDate)->days;
    // diff->days is always positive; check if subDate is in the past
    if ($subDate < $today) continue; // deadline passed, skip

    // Check if today matches any selected reminder interval
    if (!in_array($daysLeft, $client['mtd_reminder_days'], true)) continue;

    // Build a unique key for this reminder so we don't send it twice
    $remKey = $client['mtd_next_sub'] . ':' . $daysLeft;
    $sentLog = $client['mtd_reminders_sent'] ?? [];
    if (in_array($remKey, $sentLog, true)) {
        log_msg("SKIP {$client['name']} — reminder {$remKey} already sent");
        continue;
    }

    // Send to client
    $clientOk = false;
    if (!empty($client['email'])) {
        $firstName = explode(' ', trim($client['name']))[0];
        $clientOk  = send_reminder_email($client, $daysLeft, 'client', $client['email'], $firstName);
        log_msg($clientOk
            ? "SENT client reminder to {$client['email']} ({$client['name']}, {$daysLeft}d)"
            : "FAIL client reminder to {$client['email']} ({$client['name']})"
        );
    }

    // Send to accountant
    $firmOk = send_reminder_email($client, $daysLeft, 'accountant', FR_FIRM_EMAIL, FR_FIRM_NAME);
    log_msg($firmOk
        ? "SENT accountant reminder for {$client['name']}, {$daysLeft}d"
        : "FAIL accountant reminder for {$client['name']}"
    );

    if ($clientOk || $firmOk) {
        $sentLog[]                       = $remKey;
        $client['mtd_reminders_sent']    = $sentLog;
        $modified = true;
        $sent++;
    }
}
unset($client);

if ($modified) {
    save_clients_cron($clients);
    log_msg("Saved updated reminder log");
}

log_msg("MTD cron complete — checked: {$checked}, reminders sent: {$sent}");
