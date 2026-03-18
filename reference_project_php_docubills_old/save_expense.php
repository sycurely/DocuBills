<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $expense_date = $_POST['expense_date'] ?? null;
        $vendor = trim($_POST['vendor'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
        $created_at = date('Y-m-d H:i:s');

        $receipt_url = null;

        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/assets/receipts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = basename($_FILES['receipt']['name']);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $newName = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $targetFile = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetFile)) {
                $receipt_url = 'assets/receipts/' . $newName;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO expenses (
            expense_date, vendor, amount, category, notes,
            receipt_url, is_recurring, client_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $expense_date,
            $vendor,
            $amount,
            $category,
            $notes,
            $receipt_url,
            $is_recurring,
            $client_id,
            $created_at
        ]);

        header("Location: expenses.php?success=" . urlencode("Expense saved successfully!"));
        exit;

    } catch (Exception $e) {
        header("Location: expenses.php?error=" . urlencode("Failed to save expense: " . $e->getMessage()));
        exit;
    }
}

header("Location: expenses.php?error=" . urlencode("Invalid request."));
exit;
