<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes (adjust paths if needed)
require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/libs/PHPMailer/Exception.php';

// Parse CC/BCC (comma/semicolon/newline separated) into a clean array (max 10)
function parse_email_list($raw, $max = 10) : array {
    if (is_array($raw)) {
        $parts = $raw;
    } else {
        $raw = (string)$raw;
        $raw = str_replace(["\r", "\n", "\t", ";"], [",", ",", ",", ","], $raw);
        $parts = array_map('trim', explode(',', $raw));
    }

    $unique = [];
    foreach ($parts as $email) {
        $email = trim((string)$email);
        if ($email === '') continue;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
        $unique[strtolower($email)] = $email;
    }

    $emails = array_values($unique);
    if (count($emails) > $max) $emails = array_slice($emails, 0, $max);

    return $emails;
}

// ✅ Supports replacements passed as:
//   ['recipient_name' => 'Najju'] OR ['{{recipient_name}}' => 'Najju']
// Also supports template tags with spaces: {{ recipient_name }}
function normalize_template_vars(array $replacements): array {
    $vars = [];

    foreach ($replacements as $k => $v) {
        $key = trim((string)$k);

        // If key is like {{ something }}, extract the inner name
        if (preg_match('/^\{\{\s*([a-zA-Z0-9_\-]+)\s*\}\}$/', $key, $m)) {
            $key = $m[1];
        }

        $vars[$key] = $v;
    }

    return $vars;
}

function render_template_vars(string $html, array $replacements): string {
    $vars = normalize_template_vars($replacements);

    return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\-]+)\s*\}\}/', function ($m) use ($vars) {
        $key = $m[1];
        return array_key_exists($key, $vars) ? (string)$vars[$key] : $m[0];
    }, $html);
}

// Main function to send email
function sendInvoiceEmail($to_email, $to_name, $subject, $body_html, $attachment_path = '', $attachment_name = '', $cc_emails = [], $bcc_emails = [], $attachment_data = null) {
    $mail = new PHPMailer(true);

    // 🔍 Extra SMTP debug logging (goes into email_smtp_debug.log)
    $mail->SMTPDebug = 0; // 0 = off, 1 = client, 2 = client+server
    $mail->Debugoutput = function($str, $level) {
        file_put_contents(__DIR__ . '/email_smtp_debug.log', date('Y-m-d H:i:s') . " [{$level}] {$str}\n", FILE_APPEND);
    };

    try {
        // SMTP Configuration (Host mail server instead of Gmail)
        $mail->isSMTP();

        // 🔧 Use your hosting SMTP details (from cPanel → “Configure Mail Client”)
        $mail->Host       = 'mail.docubills.com';        // e.g. mail.docubills.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@docubills.com';    // full email address
        $mail->Password   = '9CEzX2vfwRgZbP6';   // password you set in cPanel

        // Usually SSL on 465 OR TLS on 587 — check your host’s settings
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // for SSL
        $mail->Port       = 465;

        // If your host instead says "TLS on 587", then use these two lines instead:
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port       = 587;


                // 🔹 Normalize and log recipient before adding
        $to_email = trim((string)$to_email);
        $to_name  = trim((string)$to_name);

        file_put_contents(__DIR__ . '/email_debug.log',
            "🔹 Normalized to_email: '{$to_email}' | to_name: '{$to_name}'\n",
            FILE_APPEND
        );

        // If no email passed in, abort early
        if ($to_email === '') {
            file_put_contents(__DIR__ . '/email_debug.log',
                "❌ sendInvoiceEmail called with EMPTY to_email - aborting before send()\n",
                FILE_APPEND
            );
            return false;
        }

        // Sender Info (use your domain mailbox; replies go to Gmail)
        $mail->setFrom('no-reply@docubills.com', 'DocuBills Invoices');

        // When client hits "Reply", it will go to your regular Gmail inbox
        $mail->addReplyTo('docubills@gmail.com', 'DocuBills');

        // Recipient - check if PHPMailer accepts it
        if (!$mail->addAddress($to_email, $to_name)) {
            file_put_contents(__DIR__ . '/email_debug.log',
                "❌ PHPMailer rejected recipient address: {$to_email}\n",
                FILE_APPEND
            );
            return false;
        }
        
        // ✅ Apply CC/BCC (from template)
        $ccList  = parse_email_list($cc_emails, 10);
        $bccList = parse_email_list($bcc_emails, 10);
        
        // Prevent duplicates / self-dup
        $toLower = strtolower($to_email);
        
        foreach ($ccList as $cc) {
            if (strtolower($cc) === $toLower) continue;
            $mail->addCC($cc);
        }
        foreach ($bccList as $bcc) {
            if (strtolower($bcc) === $toLower) continue;
            $mail->addBCC($bcc);
        }
        
        // Optional logging
        file_put_contents(__DIR__ . '/email_debug.log',
            "🔹 CC: " . implode(', ', $ccList) . " | BCC: " . implode(', ', $bccList) . "\n",
            FILE_APPEND
        );

        // Log what PHPMailer actually has in its "To" list
        if (method_exists($mail, 'getToAddresses')) {
            $recipients = $mail->getToAddresses();
            file_put_contents(__DIR__ . '/email_debug.log',
                "🔹 PHPMailer to list: " . print_r($recipients, true) . "\n",
                FILE_APPEND
            );
        }


        // Attach invoice PDF
        $attachment_path = trim((string)$attachment_path);
        if ($attachment_path !== '') {
            $resolved_path = realpath($attachment_path);
            $attach_path = $resolved_path !== false ? $resolved_path : $attachment_path;

            if (is_file($attach_path) && is_readable($attach_path)) {
                $attach_name = $attachment_name ?: basename($attach_path);
                $added = $mail->addAttachment($attach_path, $attach_name);
                if (!$added) {
                    error_log("Attachment add failed: {$attach_path}");
                    file_put_contents(__DIR__ . '/email_debug.log',
                        "Attachment add failed: {$attach_path}\n",
                        FILE_APPEND
                    );
                } else {
                    error_log("Attachment added: {$attach_path} (" . filesize($attach_path) . " bytes)");
                    file_put_contents(__DIR__ . '/email_debug.log',
                        "Attachment added: {$attach_path} (" . filesize($attach_path) . " bytes)\n",
                        FILE_APPEND
                    );
                }
            } else {
                error_log("Attachment missing or unreadable: {$attach_path}");
                file_put_contents(__DIR__ . '/email_debug.log',
                    "Attachment missing or unreadable: {$attach_path}\n",
                    FILE_APPEND
                );
            }
        } else {
            if (!empty($attachment_data)) {
                $attach_name = $attachment_name ?: 'invoice.pdf';
                $added = $mail->addStringAttachment($attachment_data, $attach_name);
                if (!$added) {
                    error_log("String attachment failed for {$attach_name}");
                    file_put_contents(__DIR__ . '/email_debug.log',
                        "String attachment failed for {$attach_name}\n",
                        FILE_APPEND
                    );
                } else {
                    file_put_contents(__DIR__ . '/email_debug.log',
                        "String attachment added: {$attach_name} (" . strlen($attachment_data) . " bytes)\n",
                        FILE_APPEND
                    );
                }
            } else {
                file_put_contents(__DIR__ . '/email_debug.log',
                    "Attachment path empty, skipping addAttachment\n",
                    FILE_APPEND
                );
            }
        }

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;

        // 📩 Log debug info
        file_put_contents(__DIR__ . '/email_debug.log', "🟡 Email subject: {$subject}\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/email_debug.log', "🟡 Attachment: {$attachment_path}\n", FILE_APPEND);

        if (!$mail->send()) {
            file_put_contents(__DIR__ . '/email_debug.log',
                "❌ PHPMailer failed to send email to: {$to_email} — Error: {$mail->ErrorInfo}\n",
                FILE_APPEND
            );
            return false;
        } else {
            file_put_contents(__DIR__ . '/email_debug.log',
                "✅ Email sent successfully to: {$to_email}\n",
                FILE_APPEND
            );
            return true;
        }

    } catch (Exception $e) {
        $err = "❌ Exception while sending to {$to_email} — {$e->getMessage()}";
        file_put_contents(__DIR__ . '/email_debug.log', $err . "\n", FILE_APPEND);
        return false;
    }
}

// Helper function to load and populate an email template 
function getEmailTemplateBody($pdo, $notificationType, $replacements = [], &$ccList = [], &$bccList = []) {
    try {
        $ccList = [];
        $bccList = [];
        
        // Try html_content first
        try {
            $stmt = $pdo->prepare("
                SELECT html_content AS template_html, cc_emails, bcc_emails
                FROM email_templates
                WHERE assigned_notification_type = ?
                  AND deleted_at IS NULL
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$notificationType]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $row = null;
        }
        
        if (empty($row)) {
            // Fallback: template_html column
            try {
                $stmt = $pdo->prepare("
                    SELECT template_html AS template_html, cc_emails, bcc_emails
                    FROM email_templates
                    WHERE assigned_notification_type = ?
                      AND deleted_at IS NULL
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $stmt->execute([$notificationType]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $row = null;
            }
        }
        
        if (!empty($row) && !empty($row['template_html'])) {
            $ccList  = parse_email_list($row['cc_emails'] ?? '', 10);
            $bccList = parse_email_list($row['bcc_emails'] ?? '', 10);
        
            $html = (string)$row['template_html'];
            return render_template_vars($html, $replacements);
        }

    } catch (Exception $e) {
        error_log("Email Template Error: " . $e->getMessage());
    }

    // Final fallback (hardcoded basic email)
    $fallback = "
        <p>Dear <strong>{{client_name}}</strong>,</p>
        <p>This is a notification regarding invoice <strong>{{invoice_number}}</strong>.</p>
        <p><strong>Total Due:</strong> {{total_amount}}</p>
        <p><a href=\"{{payment_link}}\">Pay Now</a></p>
        <p>Thank you,<br>{{company_name}}</p>
    ";
    return render_template_vars($fallback, $replacements);

}

function convertDesignJsonToHtml($designJson) {
    // Use Unlayer's public embed renderer
    $html = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="utf-8"><title>Email</title></head>
    <body>
    <div id="editor"></div>
    <script src="https://editor.unlayer.com/embed.js"></script>
    <script>
      unlayer.init({
        id: "editor",
        displayMode: "email",
        projectId: 1234
      });
      unlayer.loadDesign(' . json_encode(json_decode($designJson, true)) . ');
      unlayer.exportHtml(function(data) {
        window.parent.postMessage(data.html, "*");
      });
    </script>
    </body>
    </html>';

    return $html; // This would be captured server-side in a real renderer
}

    /**
     * Standard app mail sender used by cron scripts.
     * Returns true on success, false on failure.
     *
     * IMPORTANT:
     * If you already have a send function (ex: send_email / sendMail / send_invoice_email),
     * map it here.
     */
    /**
     * Standard app mail sender used by cron scripts.
     * Returns true on success, false on failure.
     */
    function app_send_email(string $to, string $subject, string $html) : bool {
    
        // ✅ Use your existing PHPMailer sender
        if (function_exists('sendInvoiceEmail')) {
    
            // Name is optional for reminders
            $to_name = '';
    
            return (bool) sendInvoiceEmail($to, $to_name, $subject, $html);
        }
    
        // If none exist, fail loudly so you don’t think it’s sending
        throw new Exception("sendInvoiceEmail() not found in mailer.php. Cannot send reminder emails.");
    }

?>
