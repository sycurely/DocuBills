<?php
// update_recurring.php
// Toggle invoice recurring flag + next_run_date (AJAX endpoint)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once 'config.php';
require_once 'middleware.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Please log in again.'
    ]);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$canViewAll    = has_permission('view_invoice_logs');
$canEdit       = has_permission('edit_invoice');

if (!has_permission('view_invoice_history') || !$canEdit) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to change recurring settings.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$invoiceId    = isset($_POST['invoice_id']) ? (int) $_POST['invoice_id'] : 0;
$isRecurring  = isset($_POST['is_recurring']) && $_POST['is_recurring'] === '1' ? 1 : 0;

if ($invoiceId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid invoice ID.'
    ]);
    exit;
}

try {
    // Fetch invoice and check ownership
    if ($canViewAll) {
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$invoiceId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND deleted_at IS NULL AND created_by = ?");
        $stmt->execute([$invoiceId, $currentUserId]);
    }
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        echo json_encode([
            'success' => false,
            'message' => 'Invoice not found or access denied.'
        ]);
        exit;
    }

    $nextRunDate   = null;
    $recurrenceType = null;

    if ($isRecurring) {
        // For now we only support monthly recurrence
        $recurrenceType = 'monthly';

        // Compute next_run_date from invoice_date (fallback to created_at)
        $baseDateStr = $invoice['invoice_date'] ?: $invoice['created_at'];
        if ($baseDateStr) {
            $dt = new DateTime($baseDateStr);
            $dt->modify('+1 month');
            $nextRunDate = $dt->format('Y-m-d');
        }
    }

    $update = $pdo->prepare("
        UPDATE invoices
           SET is_recurring    = ?,
               recurrence_type = ?,
               next_run_date   = ?
         WHERE id = ?
    ");
    $update->execute([
        $isRecurring,
        $recurrenceType,
        $nextRunDate,
        $invoiceId
    ]);

    echo json_encode([
        'success'       => true,
        'is_recurring'  => $isRecurring,
        'next_run_date' => $nextRunDate
    ]);
    exit;

} catch (Exception $e) {
    error_log('update_recurring.php error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error while updating recurring settings.'
    ]);
    exit;
}
