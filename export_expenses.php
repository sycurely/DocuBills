<?php
session_start();
require_once 'config.php';
require_once 'middleware.php';

// Optional: Access check
if (!has_permission('view_expenses')) {
  die('Access Denied');
}

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=expenses_export_" . date('Ymd_His') . ".csv");

$output = fopen("php://output", "w");

// Column headers
fputcsv($output, ['#', 'Date', 'Vendor', 'Category', 'Amount', 'Status', 'Payment Method', 'Proof']);

$stmt = $pdo->query("
  SELECT e.*, c.company_name
    FROM expenses e
    LEFT JOIN clients c ON c.id=e.client_id
   WHERE e.deleted_at IS NULL
   ORDER BY e.expense_date DESC
");

$i = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($output, [
    $i++,
    date('Y-m-d', strtotime($row['expense_date'])),
    $row['vendor'],
    $row['category'],
    number_format($row['amount'], 2),
    $row['status'],
    $row['payment_method'] ?? 'N/A',
    $row['payment_proof'] ?? ''
  ]);
}

fclose($output);
exit;
