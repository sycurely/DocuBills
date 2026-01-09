<?php
require_once 'config.php';
require_once 'mailer.php';
require_once 'libs/dompdf/autoload.inc.php';

try {
    $invoice_id = null;

    if (isset($_GET['invoice_id'])) {
        // Explicit invoice_id param – always treat as numeric ID
        $invoice_id = intval($_GET['invoice_id']);

    } elseif (isset($_GET['invoice'])) {
        // This param can be **either**:
        //  - numeric ID (new Stripe success_url: ?invoice=420)
        //  - invoice_number string (old links: ?invoice=FIN-ABC-01)
        $invoiceParam = $_GET['invoice'];

        if (ctype_digit($invoiceParam)) {
            // New behaviour: Stripe sends invoices.id as ?invoice=420
            $invoice_id = (int)$invoiceParam;
        } else {
            // Legacy behaviour: old links pass invoice_number
            $invoice_number = $invoiceParam;
            $stmt = $pdo->prepare("SELECT id FROM invoices WHERE invoice_number = ?");
            $stmt->execute([$invoice_number]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new Exception("Invoice not found.");
            $invoice_id = (int)$row['id'];
        }

    } else {
        throw new Exception("Missing invoice reference.");
    }
    
    // Fetch invoice
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
    $stmt->execute([$invoice_id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$invoice) throw new Exception("Invoice not found.");

    $invoice_number = $invoice['invoice_number'];
    $client_id = $invoice['client_id'];

    // Fetch client
    $stmt = $pdo->prepare("SELECT company_name, email FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client) throw new Exception("Client not found.");

    $client_email = $client['email'];
    $client_name = $client['company_name'];

    // Update invoice to Paid + set payment method
    $update = $pdo->prepare("
        UPDATE invoices 
           SET status = 'Paid',
               payment_method = 'Stripe',
               updated_at = NOW()
         WHERE id = ?
    ");
    $update->execute([$invoice_id]);

    // Load and update invoice HTML
    $html_path = __DIR__ . "/invoices/{$invoice_number}.html";
    if (!file_exists($html_path)) throw new Exception("Invoice HTML not found.");
    $html = file_get_contents($html_path);
    
    // Remove existing Pay Now button and add PAID watermark
    $html = preg_replace('/<a[^>]*class="pay-button"[^>]*>.*?<\/a>/is', '', $html);
    $html = str_replace(
        '<body>',
        '<body>
        <div style="position: fixed; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 100px; color: rgba(0, 128, 0, 0.15); font-weight: bold; z-index: 999;">
            PAID
        </div>',
        $html
    );
    
    // 🔄 NEW: change all "unpaid red" amounts to "paid green"
    $html = preg_replace('/color:\s*#dc3545/i', 'color:#28a745', $html);
    
    file_put_contents($html_path, $html);

    // Generate updated PDF
    $pdf_path = __DIR__ . "/invoices/{$invoice_number}.pdf";
    $dompdf = new Dompdf\Dompdf();
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    file_put_contents($pdf_path, $dompdf->output());

    // Email client
    // Load email template for payment_successful
    $stmt = $pdo->prepare("SELECT template_name, html_content FROM email_templates WHERE assigned_notification_type = 'payment_success' AND deleted_at IS NULL LIMIT 1");
    $stmt->execute();
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($template) {
    $subject = str_replace('{{invoice_number}}', $invoice_number, $template['template_name']);

    $totalAmount   = number_format(
        (float)($invoice['total_amount'] ?? $invoice['total'] ?? 0),
        2
    );
    $paymentMethod = $invoice['payment_method'] ?? 'Card';
    $dueDate       = $invoice['due_date'] ?? 'N/A';
    $invoiceLink   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['REQUEST_URI'])
        . '/view-invoice.php?invoice=' . urlencode($invoice_number);
        
        // Final body replacement
        $body = str_replace(
            [
                '{{client_name}}',
                '{{invoice_number}}',
                '{{total_amount}}',
                '{{payment_method}}',
                '{{due_date}}',
                '{{invoice_link}}'
            ],
            [
                $client_name,
                $invoice_number,
                'CA$' . $totalAmount,
                $paymentMethod,
                $dueDate,
                $invoiceLink
            ],
            $template['html_content']
        );

    } else {
        // Fallback if no template found
        $subject = "Thank You for Your Payment – Invoice {$invoice_number}";
        $body = "
            <p>Dear <strong>{$client_name}</strong>,</p>
            <p>We have received your payment for invoice <strong>{$invoice_number}</strong>.</p>
            <p>Thank you for your prompt payment. Please find your paid invoice attached.</p>
            <p>Best regards,<br>Your Company</p>
        ";
    }


    if (!file_exists($pdf_path)) {
    file_put_contents('email_debug.log', "⚠️ PDF file missing: {$pdf_path}\n", FILE_APPEND);
    }

    file_put_contents('email_debug.log', "➡️ Sending email to: {$client_email}\n", FILE_APPEND);
    sendInvoiceEmail($client_email, $client_name, $subject, $body, $pdf_path);
    // ✅ Redirect to the paid invoice view with celebration
    header("Location: view-invoice.php?invoice={$invoice_number}&celebrate=1");
    exit;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
