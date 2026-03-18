<?php
require_once 'config.php';
require_once 'middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('edit_expense')) {
        $_SESSION['access_denied'] = true;
        header('Location: access-denied.php');
        exit;
    }

    // ─────────────────────────────────────────────────────
    // 1) Include client_id in your required fields
    $required = ['expense_id', 'expense_date', 'vendor', 'category', 'amount', 'client_id'];
    // ─────────────────────────────────────────────────────
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            header("Location: expenses.php?error=" . urlencode("Missing field: $field"));
            exit;
        }
    }

    try {
        $id         = intval($_POST['expense_id']);
        $date       = $_POST['expense_date'];
        $vendor     = trim($_POST['vendor']);
        $category   = trim($_POST['category']);
        $amount     = floatval($_POST['amount']);
        $isRecurring= isset($_POST['is_recurring']) ? 1 : 0;

        // ─────────────────────────────────────────────────────
        // 2) Grab the posted client_id
        $clientId   = intval($_POST['client_id']);
        // ─────────────────────────────────────────────────────

        // ─────────────────────────────────────────────────────
        // 3) Include client_id in the UPDATE
        $stmt = $pdo->prepare("
            UPDATE expenses
               SET expense_date = ?,
                   vendor       = ?,
                   category     = ?,
                   amount       = ?,
                   is_recurring = ?,
                   client_id    = ?          -- ← new
             WHERE id = ?
        ");
        $stmt->execute([
            $date,
            $vendor,
            $category,
            $amount,
            $isRecurring,
            $clientId,   // ← new
            $id
        ]);
        // ─────────────────────────────────────────────────────

        // Optional logging
        $log = sprintf(
            "[%s] Expense ID %d edited. Client %d, Amount: CA$%.2f\n",
            date('Y-m-d H:i:s'),
            $id,
            $clientId,
            $amount
        );
        file_put_contents(__DIR__ . '/logs/expense_updates.log', $log, FILE_APPEND);

        header("Location: expenses.php?success=" . urlencode("Expense updated successfully!"));
        exit;

    } catch (Exception $e) {
        header("Location: expenses.php?error=" . urlencode("Update failed: " . $e->getMessage()));
        exit;
    }
}

header("Location: expenses.php");
exit;
