<?php
/**
 * Check Reminders for Nora Mobiles
 * This script checks if reminders are configured and should be sent to Nora Mobiles
 * Access: https://www.docubills.com/check-nora-reminders.php?cron_key=docubills_reminder_cron_2024
 */

if (!isset($_GET['cron_key']) || $_GET['cron_key'] !== 'docubills_reminder_cron_2024') {
    die('Access denied.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Checking Reminders for Nora Mobiles ===\n\n";

// Step 1: Find invoices for Nora Mobiles
echo "Step 1: Finding invoices for 'Nora Mobiles'...\n";
$stmt = $pdo->prepare("
    SELECT i.id, i.invoice_number, i.bill_to_name, i.bill_to_json, 
           i.total_amount, i.due_date, i.payment_link, i.status, i.client_id,
           DATE(i.due_date) as due_date_only
    FROM invoices i
    WHERE i.deleted_at IS NULL
      AND (i.bill_to_name LIKE ? OR i.bill_to_json LIKE ?)
    ORDER BY i.due_date DESC
");
$searchTerm = '%Nora Mobiles%';
$stmt->execute([$searchTerm, $searchTerm]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($invoices) . " invoice(s) for Nora Mobiles:\n";
foreach ($invoices as $inv) {
    $bill_to = json_decode($inv['bill_to_json'] ?? '{}', true);
    $email = $bill_to['Email'] ?? 'NO EMAIL';
    echo "  - Invoice #{$inv['invoice_number']}: Status={$inv['status']}, Due Date={$inv['due_date_only']}, Email={$email}\n";
}
echo "\n";

// Step 2: Check reminder settings
echo "Step 2: Checking reminder settings...\n";
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['invoice_email_reminders']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['key_value'])) {
    die("ERROR: No reminder settings found!\n");
}

$reminders = json_decode($row['key_value'], true);
if (!is_array($reminders) || empty($reminders)) {
    die("ERROR: Reminder settings are empty!\n");
}

echo "Found " . count($reminders) . " reminder(s):\n";
$today = new DateTime('today');
foreach ($reminders as $reminder) {
    $reminder_id = $reminder['id'] ?? '';
    $direction = $reminder['direction'] ?? 'on';
    $days = (int)($reminder['days'] ?? 0);
    $enabled = !empty($reminder['enabled']);
    
    $target_date = clone $today;
    if ($direction === 'before') {
        $target_date->modify("+{$days} days");
    } elseif ($direction === 'after') {
        $target_date->modify("-{$days} days");
    }
    
    echo "  - Reminder ID: {$reminder_id}\n";
    echo "    Direction: {$direction}, Days: {$days}, Enabled: " . ($enabled ? 'YES' : 'NO') . "\n";
    echo "    Target due_date: {$target_date->format('Y-m-d')}\n";
    
    // Check if any Nora Mobiles invoices match this reminder
    if ($enabled) {
        $matching = 0;
        foreach ($invoices as $inv) {
            if ($inv['status'] === 'Unpaid' && $inv['due_date_only'] === $target_date->format('Y-m-d')) {
                $matching++;
            }
        }
        echo "    Matching invoices: {$matching}\n";
    }
    echo "\n";
}

// Step 3: Check template mapping
echo "Step 3: Checking template mapping...\n";
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['invoice_email_reminder_templates']);
$rowTpl = $stmt->fetch(PDO::FETCH_ASSOC);
$templateMap = [];
if ($rowTpl && !empty($rowTpl['key_value'])) {
    $decodedTpl = json_decode($rowTpl['key_value'], true);
    if (is_array($decodedTpl)) {
        $templateMap = $decodedTpl;
    }
}

if (empty($templateMap)) {
    echo "WARNING: No template mapping found!\n";
} else {
    echo "Template mappings:\n";
    foreach ($templateMap as $reminder_id => $template_id) {
        echo "  - Reminder {$reminder_id} -> Template ID: {$template_id}\n";
        
        // Check if template exists
        $stmt = $pdo->prepare("SELECT template_name, html_content, template_html FROM email_templates WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$template_id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($template) {
            $has_html = !empty($template['html_content']) || !empty($template['template_html']);
            echo "    Template: {$template['template_name']}, Has HTML: " . ($has_html ? 'YES' : 'NO') . "\n";
        } else {
            echo "    ERROR: Template not found!\n";
        }
    }
}
echo "\n";

// Step 4: Check client filter
echo "Step 4: Checking client filter...\n";
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['invoice_email_reminder_clients']);
$rowCli = $stmt->fetch(PDO::FETCH_ASSOC);
$clientMap = [];
if ($rowCli && !empty($rowCli['key_value'])) {
    $decodedCli = json_decode($rowCli['key_value'], true);
    if (is_array($decodedCli)) {
        $clientMap = $decodedCli;
    }
}

if (empty($clientMap)) {
    echo "No client filter (all clients included)\n";
} else {
    echo "Client filters:\n";
    foreach ($clientMap as $reminder_id => $clientFilter) {
        echo "  - Reminder {$reminder_id}: ";
        if ($clientFilter === 'all') {
            echo "ALL clients\n";
        } else {
            echo "Specific clients: " . (is_array($clientFilter) ? implode(', ', $clientFilter) : $clientFilter) . "\n";
        }
    }
}
echo "\n";

// Step 5: Check which invoices should receive reminders TODAY
echo "Step 5: Checking which invoices should receive reminders TODAY ({$today->format('Y-m-d')})...\n";
$should_send = [];

foreach ($reminders as $reminder) {
    if (empty($reminder['enabled'])) {
        continue;
    }
    
    $reminder_id = $reminder['id'] ?? '';
    $direction = $reminder['direction'] ?? 'on';
    $days = (int)($reminder['days'] ?? 0);
    
    $target_date = clone $today;
    if ($direction === 'before') {
        $target_date->modify("+{$days} days");
    } elseif ($direction === 'after') {
        $target_date->modify("-{$days} days");
    }
    
    $target_date_str = $target_date->format('Y-m-d');
    
    foreach ($invoices as $invoice) {
        if ($invoice['status'] !== 'Unpaid') {
            continue;
        }
        
        if ($invoice['due_date_only'] === $target_date_str) {
            $bill_to = json_decode($invoice['bill_to_json'] ?? '{}', true);
            $client_email = $bill_to['Email'] ?? '';
            
            // Check client filter
            $clientFilter = $clientMap[$reminder_id] ?? 'all';
            $should_include = true;
            if ($clientFilter !== 'all' && is_array($clientFilter) && !empty($clientFilter)) {
                $should_include = in_array($invoice['client_id'], $clientFilter);
            }
            
            if ($should_include) {
                $should_send[] = [
                    'invoice' => $invoice,
                    'reminder_id' => $reminder_id,
                    'email' => $client_email,
                    'template_id' => $templateMap[$reminder_id] ?? null
                ];
            }
        }
    }
}

if (empty($should_send)) {
    echo "No reminders should be sent TODAY for Nora Mobiles invoices.\n";
    echo "This could mean:\n";
    echo "  1. No invoices match the reminder date criteria\n";
    echo "  2. All matching invoices are already paid\n";
    echo "  3. Client filter excludes these invoices\n";
} else {
    echo "Found " . count($should_send) . " reminder(s) that SHOULD be sent:\n";
    foreach ($should_send as $item) {
        $inv = $item['invoice'];
        echo "  - Invoice #{$inv['invoice_number']} (Due: {$inv['due_date_only']})\n";
        echo "    Reminder ID: {$item['reminder_id']}\n";
        echo "    Email: {$item['email']}\n";
        echo "    Template ID: " . ($item['template_id'] ?? 'NOT SET') . "\n";
        
        if (empty($item['email'])) {
            echo "    ERROR: No email address found!\n";
        }
        if (empty($item['template_id'])) {
            echo "    ERROR: No template assigned!\n";
        }
        echo "\n";
    }
}

echo "\n=== Check Complete ===\n";
echo "\nTo test sending a reminder, run:\n";
echo "https://www.docubills.com/test-reminder-email.php?cron_key=docubills_reminder_cron_2024\n";
echo "\nTo run the actual reminder script:\n";
echo "https://www.docubills.com/send-reminders.php?cron_key=docubills_reminder_cron_2024\n";
