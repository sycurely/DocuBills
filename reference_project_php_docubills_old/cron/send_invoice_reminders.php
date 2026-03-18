<?php
/**
 * cron/send_invoice_reminders.php
 *
 * Sends invoice reminder emails based on:
 *  - settings.invoice_email_reminders (cadence + enabled + offset_days)
 *  - settings.invoice_email_reminder_templates (template id per reminder key)
 *
 * Logs sends into invoice_reminder_logs (prevents duplicates).
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../mailer.php';

// -------------------------
// 1) SECURITY: token check
// -------------------------
function get_cli_arg($name) {
    global $argv;
    foreach ($argv as $arg) {
        if (strpos($arg, "--{$name}=") === 0) {
            return substr($arg, strlen("--{$name}="));
        }
    }
    return null;
}

$token = get_cli_arg('token');
if ($token === null) {
    $token = $_GET['token'] ?? null;
}

$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['cron_secret']);
$cronSecretRow = $stmt->fetch(PDO::FETCH_ASSOC);
$cronSecret = $cronSecretRow['key_value'] ?? '';

if (!$token || !$cronSecret || !hash_equals($cronSecret, $token)) {
    http_response_code(403);
    echo "Forbidden (invalid token)\n";
    exit;
}

// -------------------------
// 2) Load settings JSON
// -------------------------
function load_setting_json(PDO $pdo, string $keyName, array $default = []) : array {
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
    $stmt->execute([$keyName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['key_value'])) return $default;

    $decoded = json_decode($row['key_value'], true);
    return is_array($decoded) ? $decoded : $default;
}

$reminders = load_setting_json($pdo, 'invoice_email_reminders', []);
$templateMap = load_setting_json($pdo, 'invoice_email_reminder_templates', []);

// Reminder keys we support (must match your UI keys)
$reminderKeys = ['before_due','on_due','after_3','after_7','after_14','after_21'];

// -------------------------
// 3) Load templates (cache)
// -------------------------
function fetch_template(PDO $pdo, int $templateId) : ?array {
    $stmt = $pdo->prepare("
        SELECT id, template_name,
               COALESCE(NULLIF(template_html,''), html_content) AS body_html
        FROM email_templates
        WHERE id = ? AND deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$templateId]);
    $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tpl || empty($tpl['body_html'])) return null;
    return $tpl;
}

// -------------------------
// 4) Placeholder rendering
// -------------------------
function render_template(string $html, array $vars) : string {
    foreach ($vars as $k => $v) {
        $html = str_replace('{{'.$k.'}}', (string)$v, $html);
    }
    return $html;
}

function safe_date_fmt(?string $dt, string $fmt='M d, Y') : string {
    if (!$dt) return '';
    try {
        $d = new DateTime($dt);
        return $d->format($fmt);
    } catch (Exception $e) {
        return $dt;
    }
}

// -------------------------
// 5) Recipient resolver
// -------------------------
function extract_email_from_bill_to_json(?string $billToJson) : ?string {
    if (!$billToJson) return null;
    $decoded = json_decode($billToJson, true);
    if (!is_array($decoded)) return null;

    $possibleKeys = ['email', 'bill_to_email', 'recipient_email', 'client_email'];
    foreach ($possibleKeys as $k) {
        if (!empty($decoded[$k]) && filter_var($decoded[$k], FILTER_VALIDATE_EMAIL)) {
            return $decoded[$k];
        }
    }

    // sometimes nested
    if (!empty($decoded['bill_to']['email']) && filter_var($decoded['bill_to']['email'], FILTER_VALIDATE_EMAIL)) {
        return $decoded['bill_to']['email'];
    }

    return null;
}

function reminder_label(string $key) : string {
    return match ($key) {
        'before_due' => 'Reminder (Before Due)',
        'on_due'     => 'Reminder (On Due Date)',
        'after_3'    => 'Reminder (3 Days After Due)',
        'after_7'    => 'Reminder (7 Days After Due)',
        'after_14'   => 'Reminder (14 Days After Due)',
        'after_21'   => 'Reminder (21 Days After Due)',
        default      => 'Invoice Reminder',
    };
}

// -------------------------
// 6) Send wrapper
// -------------------------
// IMPORTANT: this expects mailer.php to provide app_send_email($to,$subject,$html)
// In Phase 3 Step 4 below, you will add that function (small + safe).
function send_html_email(string $to, string $subject, string $html) : array {
    if (!function_exists('app_send_email')) {
        return ['ok' => false, 'error' => 'app_send_email() not found in mailer.php'];
    }
    try {
        $ok = app_send_email($to, $subject, $html);
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'Mailer returned false'];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

// -------------------------
// 7) Main run
// -------------------------
$today = new DateTime('today'); // server timezone
$sentCount = 0;
$failCount = 0;
$skippedNoTemplate = 0;
$skippedDisabled = 0;

foreach ($reminderKeys as $rKey) {

    // Must exist + enabled
    if (empty($reminders[$rKey]) || empty($reminders[$rKey]['enabled'])) {
        $skippedDisabled++;
        continue;
    }

    $offsetDays = (int)($reminders[$rKey]['offset_days'] ?? 0);

    // Compute target due_date date that matches "today" for this reminder:
    // today = due_date + offset  =>  due_date = today - offset
    $dueWanted = (clone $today)->modify((string)(-$offsetDays) . " days")->format('Y-m-d');

    $tplIdRaw = $templateMap[$rKey] ?? '';
    $tplId = (int)$tplIdRaw;

    if ($tplId <= 0) {
        $skippedNoTemplate++;
        continue;
    }

    $template = fetch_template($pdo, $tplId);
    if (!$template) {
        $skippedNoTemplate++;
        continue;
    }

    // Fetch matching invoices (Unpaid only + not deleted + has due_date)
    $stmt = $pdo->prepare("
        SELECT i.*,
               c.email AS client_email,
               c.company_name AS client_company,
               c.representative AS client_rep
        FROM invoices i
        LEFT JOIN clients c ON c.id = i.client_id
        WHERE i.deleted_at IS NULL
          AND i.status = 'Unpaid'
          AND i.due_date IS NOT NULL
          AND DATE(i.due_date) = ?
          AND NOT EXISTS (
              SELECT 1 FROM invoice_reminder_logs l
              WHERE l.invoice_id = i.id
                AND l.reminder_key = ?
          )
        ORDER BY i.id ASC
    ");
    $stmt->execute([$dueWanted, $rKey]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($invoices as $inv) {

        $to = extract_email_from_bill_to_json($inv['bill_to_json'] ?? null);

        if (!$to && !empty($inv['client_email']) && filter_var($inv['client_email'], FILTER_VALIDATE_EMAIL)) {
            $to = $inv['client_email'];
        }

        if (!$to) {
            // Log failure
            $ins = $pdo->prepare("
                INSERT INTO invoice_reminder_logs (invoice_id, reminder_key, template_id, sent_to, status, error_message)
                VALUES (?, ?, ?, ?, 'failed', ?)
            ");
            $ins->execute([(int)$inv['id'], $rKey, $tplId, null, 'No recipient email found (bill_to_json/client email empty)']);
            $failCount++;
            continue;
        }

        // BASE_URL strongly recommended for cron links
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' : '';

        $invoiceLink = $baseUrl ? ($baseUrl . 'view-invoice.php?id=' . (int)$inv['id']) : '';
        $payNowLink  = !empty($inv['payment_link']) ? $inv['payment_link'] : '';

        $clientName = $inv['bill_to_name'] ?: ($inv['client_company'] ?: ($inv['client_rep'] ?: 'Valued Customer'));

        $vars = [
            'client_name'    => $clientName,
            'invoice_number' => $inv['invoice_number'] ?? '',
            'invoice_total'  => $inv['total_amount'] ?? '',
            'amount_due'     => $inv['total_amount'] ?? '',
            'due_date'       => safe_date_fmt($inv['due_date'] ?? ''),
            'invoice_date'   => safe_date_fmt($inv['invoice_date'] ?? ''),
            'invoice_link'   => $invoiceLink,
            'pay_now_link'   => $payNowLink,
            'reminder_type'  => reminder_label($rKey),
        ];

        $body = render_template($template['body_html'], $vars);

        // Subject: template_name + invoice number (you can later add a subject field if you want)
        $subject = trim($template['template_name'] . ' — ' . ($inv['invoice_number'] ?? ''));

        $send = send_html_email($to, $subject, $body);

        if ($send['ok']) {
            $ins = $pdo->prepare("
                INSERT INTO invoice_reminder_logs (invoice_id, reminder_key, template_id, sent_to, status, error_message)
                VALUES (?, ?, ?, ?, 'sent', NULL)
            ");
            $ins->execute([(int)$inv['id'], $rKey, $tplId, $to]);
            $sentCount++;
        } else {
            $ins = $pdo->prepare("
                INSERT INTO invoice_reminder_logs (invoice_id, reminder_key, template_id, sent_to, status, error_message)
                VALUES (?, ?, ?, ?, 'failed', ?)
            ");
            $ins->execute([(int)$inv['id'], $rKey, $tplId, $to, $send['error'] ?? 'Unknown error']);
            $failCount++;
        }
    }
}

echo "DONE\n";
echo "Sent: {$sentCount}\n";
echo "Failed: {$failCount}\n";
echo "Skipped (disabled): {$skippedDisabled}\n";
echo "Skipped (no template): {$skippedNoTemplate}\n";
