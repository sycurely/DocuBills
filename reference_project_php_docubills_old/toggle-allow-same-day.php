<?php
/**
 * Toggle / check same-day reminder setting.
 * Usage:
 *   /toggle-allow-same-day.php?cron_key=...&value=0|1
 */

if (!isset($_GET['cron_key']) || $_GET['cron_key'] !== 'docubills_reminder_cron_2024') {
    die('Access denied.');
}

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

$value = $_GET['value'] ?? null;
if ($value !== null) {
    if ($value !== '0' && $value !== '1') {
        die("Invalid value. Use 0 or 1.\n");
    }

    $check = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
    $check->execute(['invoice_email_reminders_allow_same_day']);

    if ($check->fetch()) {
        $upd = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
        $upd->execute([$value, 'invoice_email_reminders_allow_same_day']);
    } else {
        $ins = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
        $ins->execute(['invoice_email_reminders_allow_same_day', $value]);
    }
}

$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['invoice_email_reminders_allow_same_day']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$current = $row['key_value'] ?? null;
if ($current === null || $current === '') {
    $current = '(null)';
}

echo "invoice_email_reminders_allow_same_day={$current}\n";
