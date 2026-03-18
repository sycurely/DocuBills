<?php
// Start output buffering to prevent any accidental output before headers
if (!ob_get_level()) {
    ob_start();
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(403);
    die('Access denied. Please log in.');
}

require_once 'config.php';
require_once 'middleware.php';

// Check permission to view invoices
if (!has_permission('view_invoices')) {
    http_response_code(403);
    die('Access denied. You do not have permission to view invoices.');
}

// Get invoice number from query parameter
$invoiceNumber = isset($_GET['invoice']) ? trim($_GET['invoice']) : '';

if (empty($invoiceNumber)) {
    http_response_code(400);
    die('Invoice number is required.');
}

// Sanitize invoice number (remove any path traversal attempts)
$invoiceNumber = basename($invoiceNumber);
$invoiceNumber = preg_replace('/[^a-zA-Z0-9\-_]/', '', $invoiceNumber);

if (empty($invoiceNumber)) {
    http_response_code(400);
    die('Invalid invoice number.');
}

// Check if user can view this invoice
$currentUserId = $_SESSION['user_id'];
$canViewAllInvoices = has_permission('view_invoice_logs');

// Verify invoice exists and user has access
try {
    // $pdo is available from config.php
    $query = "SELECT id, invoice_number, created_by FROM invoices WHERE invoice_number = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$invoiceNumber]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invoice) {
        http_response_code(404);
        die('Invoice not found.');
    }
    
    // Check access: user can only view their own invoices unless they have view_invoice_logs permission
    if (!$canViewAllInvoices && $invoice['created_by'] != $currentUserId) {
        http_response_code(403);
        die('Access denied. You do not have permission to view this invoice.');
    }
    
} catch (Exception $e) {
    error_log("Error checking invoice access: " . $e->getMessage());
    http_response_code(500);
    die('Error accessing invoice: ' . $e->getMessage());
}

// Define PDF file path
$invoiceDir = __DIR__ . '/invoices';
$pdfPath = $invoiceDir . '/' . $invoiceNumber . '.pdf';

// Check if PDF file exists
if (!file_exists($pdfPath)) {
    // Try to generate PDF on-the-fly if it doesn't exist
    // Load DomPDF
    $dompdfAutoload = __DIR__ . '/libs/dompdf/autoload.inc.php';
    if (file_exists($dompdfAutoload)) {
        require_once $dompdfAutoload;
        
        if (class_exists(\Dompdf\Dompdf::class)) {
            // Get invoice HTML
            $htmlPath = $invoiceDir . '/' . $invoiceNumber . '.html';
            if (file_exists($htmlPath)) {
                $html = file_get_contents($htmlPath);
                
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                
                // Save PDF for future use
                file_put_contents($pdfPath, $dompdf->output());
            } else {
                http_response_code(404);
                die('PDF not found and cannot be generated. Invoice HTML file is missing.');
            }
        } else {
            http_response_code(404);
            die('PDF not found and PDF generation library is not available.');
        }
    } else {
        http_response_code(404);
        die('PDF not found and PDF generation library is not available.');
    }
}

// Verify PDF file exists now
if (!file_exists($pdfPath)) {
    http_response_code(404);
    die('PDF file not found.');
}

// Clean any output buffer before sending PDF
if (ob_get_level()) {
    ob_end_clean();
}

// Check if download is requested
$isDownload = isset($_GET['download']) && $_GET['download'] == '1';

// Set headers for PDF viewing/downloading
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($pdfPath));
header('Content-Disposition: ' . ($isDownload ? 'attachment' : 'inline') . '; filename="' . $invoiceNumber . '.pdf"');
header('Cache-Control: private, max-age=3600');
header('Pragma: cache');

// Output PDF file
readfile($pdfPath);
exit;
