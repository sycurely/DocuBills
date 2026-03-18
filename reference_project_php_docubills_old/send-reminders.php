<?php
/**
 * Invoice Reminder Email Sender
 * This script should be run via cron job (daily recommended)
 * Example cron: 0 9 * * * /usr/bin/php /path/to/send-reminders.php
 */

// Prevent direct browser access (optional - remove if you want to test via browser)
if (php_sapi_name() !== 'cli' && !isset($_GET['cron_key'])) {
    // Allow access with secret key for web-based cron
    $secret_key = 'docubills_reminder_cron_2024'; // Change this to a secure random string
    if (!isset($_GET['cron_key']) || $_GET['cron_key'] !== $secret_key) {
        die('Access denied. This script must be run via cron or with valid cron_key.');
    }
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

// Log file for debugging
$log_file = __DIR__ . '/reminder_log.txt';

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry; // Also output to console if run via CLI
}

log_message("=== Reminder Processing Started ===");

// Check if database connection exists
if (!isset($pdo)) {
    log_message("FATAL ERROR: Database connection (\$pdo) not available. Check config.php");
    exit(1);
}

try {
    // Load reminder settings
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
    $stmt->execute(['invoice_email_reminders']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row || empty($row['key_value'])) {
        log_message("No reminder settings found. Exiting.");
        exit(0);
    }
    
    $reminders = json_decode($row['key_value'], true);
    if (!is_array($reminders) || empty($reminders)) {
        log_message("Reminder settings are empty. Exiting.");
        exit(0);
    }
    
    // Load template mapping
    $templateMap = [];
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
    $stmt->execute(['invoice_email_reminder_templates']);
    $rowTpl = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rowTpl && !empty($rowTpl['key_value'])) {
        $decodedTpl = json_decode($rowTpl['key_value'], true);
        if (is_array($decodedTpl)) {
            $templateMap = $decodedTpl;
        }
    }
    
    // Load client mapping
    $clientMap = [];
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
    $stmt->execute(['invoice_email_reminder_clients']);
    $rowCli = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rowCli && !empty($rowCli['key_value'])) {
        $decodedCli = json_decode($rowCli['key_value'], true);
        if (is_array($decodedCli)) {
            $clientMap = $decodedCli;
        }
    }

    // Allow same-day repeat reminders (skip per-invoice daily guard when enabled)
    $allowSameDay = false;
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
    $stmt->execute(['invoice_email_reminders_allow_same_day']);
    $rowAllow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rowAllow && $rowAllow['key_value'] !== null) {
        $raw = strtolower(trim((string)$rowAllow['key_value']));
        $allowSameDay = in_array($raw, ['1', 'true', 'yes', 'on'], true);
    }
    log_message("Same-day resend: " . ($allowSameDay ? "enabled" : "disabled"));
    
    // Track sent reminders to avoid duplicates (using a simple table or log)
    // For now, we'll check if reminder was sent today for this invoice+reminder combo
    // You may want to create a table: invoice_reminder_log (invoice_id, reminder_id, sent_at)
    $today_str = date('Y-m-d');
    $cache_dir = __DIR__ . '/.reminder_cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $sent_count = 0;
    $error_count = 0;
    
    foreach ($reminders as $reminder) {
        // Skip disabled reminders
        if (empty($reminder['enabled'])) {
            continue;
        }
        
        $reminder_id = $reminder['id'] ?? '';
        $direction = $reminder['direction'] ?? 'on';
        $days = (int)($reminder['days'] ?? 0);
        $offset_days = (int)($reminder['offset_days'] ?? 0);
        
        // Calculate target date based on reminder settings
        $today = new DateTime('today');
        $target_date = clone $today;
        $used_offset = false;
        
        if ($direction === 'before') {
            // For "before": find invoices where due_date is X days from today
            // Example: If reminder is "3 days before" and today is Jan 10, find invoices due on Jan 13
            $target_date->modify("+{$days} days");
        } elseif ($direction === 'after') {
            // For "after": find invoices where due_date was X days ago
            // Example: If reminder is "7 days after" and today is Jan 17, find invoices due on Jan 10
            $target_date->modify("-{$days} days");
        } elseif ($direction !== 'on' && $offset_days != 0) {
            // Fallback for legacy data that relied on offset_days only
            $target_date->modify(($offset_days > 0 ? '+' : '') . $offset_days . ' days');
            $used_offset = true;
        }
        // For 'on', target_date is today (already set, or offset applied)
        
        $target_date_str = $target_date->format('Y-m-d');
        
        $offset_info = $used_offset ? " (with offset: {$offset_days} days)" : "";
        log_message("Processing reminder: {$reminder_id} - {$direction} {$days} days{$offset_info} (target due_date: {$target_date_str})");
        
        // Build query to find invoices matching this reminder
        // We're looking for invoices where the due_date matches our calculated target date
        // IMPORTANT: Only include invoices where due_date is NOT NULL
        $query = "
            SELECT i.id, i.invoice_number, i.bill_to_name, i.bill_to_json, 
                   i.total_amount, i.due_date, i.payment_link, i.status, i.client_id
            FROM invoices i
            WHERE i.deleted_at IS NULL
              AND LOWER(TRIM(i.status)) = 'unpaid'
              AND i.due_date IS NOT NULL
              AND DATE(i.due_date) = ?
        ";
        
        $params = [$target_date_str];
        
        // Apply client filter if configured
        $clientFilter = $clientMap[$reminder_id] ?? 'all';
        if ($clientFilter !== 'all' && is_array($clientFilter) && !empty($clientFilter)) {
            $placeholders = implode(',', array_fill(0, count($clientFilter), '?'));
            $query .= " AND i.client_id IN ({$placeholders})";
            $params = array_merge($params, $clientFilter);
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        log_message("Found " . count($invoices) . " invoices matching this reminder");
        
        foreach ($invoices as $invoice) {
            $status_norm = strtolower(trim((string)($invoice['status'] ?? '')));
            if ($status_norm !== 'unpaid') {
                log_message("Skipping invoice #{$invoice['invoice_number']} - status is '{$invoice['status']}'");
                continue;
            }
            // Check if we already sent this reminder today (simple duplicate prevention)
            // In production, you might want a proper tracking table
            $reminder_key = "reminder_{$reminder_id}_invoice_{$invoice['id']}_{$today_str}";
            $reminder_cache_file = $cache_dir . '/' . md5($reminder_key) . '.txt';
            $invoice_day_key = "invoice_{$invoice['id']}_{$today_str}";
            $invoice_day_cache_file = $cache_dir . '/' . md5($invoice_day_key) . '.txt';

            if (file_exists($reminder_cache_file)) {
                log_message("Skipping invoice #{$invoice['invoice_number']} - reminder already sent today");
                continue;
            }

            if (!$allowSameDay && file_exists($invoice_day_cache_file)) {
                log_message("Skipping invoice #{$invoice['invoice_number']} - invoice already received a reminder today");
                continue;
            }
            
            // Get client email from bill_to_json
            // Check multiple case variations: 'Email', 'email', 'EMAIL'
            $bill_to = json_decode($invoice['bill_to_json'] ?? '{}', true);
            $client_email = $bill_to['Email'] ?? $bill_to['email'] ?? $bill_to['EMAIL'] ?? '';
            $client_name = $bill_to['Contact Name'] ?? $bill_to['contact name'] ?? $bill_to['contact_name'] ?? $invoice['bill_to_name'] ?? 'Client';
            
            // Debug: log what we found
            log_message("Invoice #{$invoice['invoice_number']} - bill_to_json: " . substr($invoice['bill_to_json'] ?? '{}', 0, 200));
            log_message("  Extracted email: '{$client_email}'");
            log_message("  Extracted name: '{$client_name}'");
            
            if (empty($client_email)) {
                log_message("ERROR: No email found for invoice #{$invoice['invoice_number']}");
                log_message("  bill_to_json keys: " . implode(', ', array_keys($bill_to ?? [])));
                $error_count++;
                continue;
            }
            
            // Validate email format
            if (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
                log_message("ERROR: Invalid email format '{$client_email}' for invoice #{$invoice['invoice_number']}");
                $error_count++;
                continue;
            }
            
            // Get template ID for this reminder
            $template_id = $templateMap[$reminder_id] ?? '';
            
            if (empty($template_id)) {
                log_message("WARNING: No template configured for reminder {$reminder_id}. Skipping invoice #{$invoice['invoice_number']}");
                continue;
            }
            
            // Load email template
            $stmt = $pdo->prepare("
                SELECT html_content, template_html, design_json, cc_emails, bcc_emails
                FROM email_templates
                WHERE id = ?
                  AND (deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')
            ");
            $stmt->execute([$template_id]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$template) {
                log_message("ERROR: Template ID {$template_id} not found. Skipping invoice #{$invoice['invoice_number']}");
                $error_count++;
                continue;
            }
            
            // Get template HTML (try html_content first, then template_html)
            $template_html = $template['html_content'] ?? $template['template_html'] ?? '';
            
            // If still empty and design_json exists, we need to convert it (for now, skip with warning)
            if (empty($template_html) && !empty($template['design_json'])) {
                log_message("WARNING: Template ID {$template_id} only has design_json (Unlayer format). HTML conversion needed. Skipping invoice #{$invoice['invoice_number']}");
                $error_count++;
                continue;
            }
            
            if (empty($template_html)) {
                log_message("ERROR: Template ID {$template_id} has no HTML content. Skipping invoice #{$invoice['invoice_number']}");
                $error_count++;
                continue;
            }
            
            // Prepare replacements
            // Safely format due_date (should not be NULL due to query, but check anyway)
            $due_date_formatted = !empty($invoice['due_date']) 
                ? date('F j, Y', strtotime($invoice['due_date'])) 
                : 'N/A';
            $amount_due = 'CA$' . number_format((float)$invoice['total_amount'], 2);
            $base_url = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' : 'https://www.docubills.com/';
            $public_invoice_link = $base_url . 'view-invoice.php?invoice=' . urlencode($invoice['invoice_number']);

            $payment_link = trim((string)($invoice['payment_link'] ?? ''));
            if ($payment_link === '' || stripos($payment_link, 'view_pdf.php') !== false) {
                $payment_link = $public_invoice_link;
                log_message("  Using public invoice link for invoice #{$invoice['invoice_number']}");
            } elseif (!preg_match('~^https?://~i', $payment_link)) {
                $payment_link = $base_url . ltrim($payment_link, '/');
            }
            
            $replacements = [
                'client_name' => $client_name,
                'invoice_number' => $invoice['invoice_number'],
                'amount_due' => $amount_due,
                'total_amount' => $amount_due,
                'due_date' => $due_date_formatted,
                'payment_link' => $payment_link,
                'invoice_link' => $public_invoice_link,
                'view_invoice_link' => $public_invoice_link,
                'public_invoice_link' => $public_invoice_link,
                'company_name' => 'DocuBills', // You can load this from settings
            ];
            
            // Render template with replacements
            $email_body = render_template_vars($template_html, $replacements);
            
            // Generate subject based on reminder direction
            $direction_text = '';
            if ($direction === 'before') {
                $direction_text = "in {$days} day" . ($days == 1 ? '' : 's');
            } elseif ($direction === 'after') {
                $direction_text = "{$days} day" . ($days == 1 ? '' : 's') . " ago";
            } else {
                $direction_text = "today";
            }
            
            $subject = "Reminder: Invoice {$invoice['invoice_number']} Payment Due {$direction_text}";
            $subject = render_template_vars($subject, $replacements);
            
            // Get CC/BCC from template (if columns exist)
            $cc_emails = [];
            $bcc_emails = [];
            if (isset($template['cc_emails'])) {
                $cc_emails = parse_email_list($template['cc_emails'] ?? '', 10);
            }
            if (isset($template['bcc_emails'])) {
                $bcc_emails = parse_email_list($template['bcc_emails'] ?? '', 10);
            }
            
            // Send email
            $attachment_path = '';
            $attachment_name = '';
            $invoice_pdf_path = __DIR__ . '/invoices/' . $invoice['invoice_number'] . '.pdf';
            if (is_file($invoice_pdf_path)) {
                $attachment_path = $invoice_pdf_path;
                $attachment_name = basename($invoice_pdf_path);
            } else {
                log_message("WARNING: PDF not found for invoice #{$invoice['invoice_number']} at {$invoice_pdf_path}");
            }

            log_message("Sending reminder email to {$client_email} for invoice #{$invoice['invoice_number']}");
            log_message("  Subject: {$subject}");
            log_message("  Template ID: {$template_id}");
            log_message("  Email body length: " . strlen($email_body) . " bytes");
            if (!empty($attachment_path)) {
                log_message("  Attachment: {$attachment_name}");
            }
            
            try {
                $sent = sendInvoiceEmail(
                    $client_email,
                    $client_name,
                    $subject,
                    $email_body,
                    $attachment_path,
                    $attachment_name,
                    $cc_emails,
                    $bcc_emails
                );
                
                if ($sent) {
                    // Mark as sent (create cache file)
                    file_put_contents($reminder_cache_file, date('Y-m-d H:i:s'));
                    file_put_contents($invoice_day_cache_file, date('Y-m-d H:i:s'));
                    log_message("SUCCESS: Reminder sent to {$client_email} for invoice #{$invoice['invoice_number']}");
                    $sent_count++;
                } else {
                    log_message("ERROR: sendInvoiceEmail returned false for {$client_email} (invoice #{$invoice['invoice_number']})");
                    log_message("  Check email_debug.log for more details");
                    $error_count++;
                }
            } catch (Exception $emailEx) {
                log_message("EXCEPTION while sending email: " . $emailEx->getMessage());
                log_message("  Stack: " . $emailEx->getTraceAsString());
                $error_count++;
            }
        }
    }
    
    log_message("=== Reminder Processing Complete ===");
    log_message("Total sent: {$sent_count}, Errors: {$error_count}");
    
} catch (Exception $e) {
    log_message("FATAL ERROR: " . $e->getMessage());
    log_message("Stack trace: " . $e->getTraceAsString());
    exit(1);
}
