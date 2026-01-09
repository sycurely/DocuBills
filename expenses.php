<?php
ob_start();
session_start();
require_once 'config.php';
require_once 'middleware.php';

$activeMenu = 'expenses';

// 🔐 Ownership / visibility for expenses
$currentUserId       = $_SESSION['user_id'] ?? null;
$canViewAllExpenses  = has_permission('view_all_expenses');
$canViewAllClients   = has_permission('view_all_clients');

// Show "User" column for everyone (rows are still filtered by created_by)
$showUserColumn = true;

// 🔁 Recurring permissions for expenses
$canViewRecurring   = has_permission('view_recurring_invoice');
$canCreateRecurring = has_permission('create_recurring_invoice');
$canEditRecurring   = has_permission('edit_recurring_invoice');
$canDeleteRecurring = has_permission('delete_recurring_invoice');

if (!has_permission('access_expenses_tab')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}

require 'styles.php';

// Fetch clients for the “Edit Expense” client dropdown
// Respect deleted_at and view_all_clients permission
if ($canViewAllClients) {
    // Super roles: see all active (non-deleted) clients
    $stmtClients = $pdo->prepare("
        SELECT id, company_name
        FROM clients
        WHERE deleted_at IS NULL
        ORDER BY company_name
    ");
    $stmtClients->execute();
} else {
    // Normal roles: see only their own active clients (or system/global ones if created_by IS NULL)
    $stmtClients = $pdo->prepare("
        SELECT id, company_name
        FROM clients
        WHERE deleted_at IS NULL
          AND (created_by = :uid OR created_by IS NULL)
        ORDER BY company_name
    ");
    $stmtClients->execute([':uid' => (int)$currentUserId]);
}

$clients = $stmtClients->fetchAll(PDO::FETCH_ASSOC);

// Undo / delete handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Undo most recent deleted expense (respect ownership)
    if (isset($_POST['undo_recent']) && has_permission('undo_recent_expense')) {
        if ($canViewAllExpenses) {
            // Super roles: undo last deleted expense overall
            $pdo->query("
              UPDATE expenses
                 SET deleted_at = NULL
               WHERE id = (
                 SELECT id FROM expenses
                  WHERE deleted_at IS NOT NULL
                  ORDER BY deleted_at DESC
                  LIMIT 1
               )
            ");
        } else {
            // Normal roles: only undo *their* last deleted expense
            $uid = (int) $currentUserId;
            $pdo->query("
              UPDATE expenses
                 SET deleted_at = NULL
               WHERE id = (
                 SELECT id FROM expenses
                  WHERE deleted_at IS NOT NULL
                    AND created_by = {$uid}
                  ORDER BY deleted_at DESC
                  LIMIT 1
               )
            ");
        }

        header('Location: expenses.php?success=' . urlencode('Last deletion undone!'));
        exit;
    }

    // ✅ Undo ALL deleted expenses (respect ownership)
    if (isset($_POST['undo_all']) && has_permission('undo_all_expenses')) {
        if ($canViewAllExpenses) {
            // Super roles: restore *all* deleted expenses
            $pdo->query("UPDATE expenses SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
        } else {
            // Normal roles: restore only their own deleted expenses
            $stmt = $pdo->prepare("
                UPDATE expenses
                   SET deleted_at = NULL
                 WHERE deleted_at IS NOT NULL
                   AND created_by = ?
            ");
            $stmt->execute([$currentUserId]);
        }

        header('Location: expenses.php?success=' . urlencode('All deleted expenses restored!'));
        exit;
    }

    // ✅ Move a single expense to trash (respect ownership)
    if (isset($_POST['delete_id']) && has_permission('delete_expense')) {
        if ($canViewAllExpenses) {
            // Super roles: can trash any expense
            $stmt = $pdo->prepare("UPDATE expenses SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([ $_POST['delete_id'] ]);
        } else {
            // Normal roles: can only trash their own expense
            $stmt = $pdo->prepare("
                UPDATE expenses
                   SET deleted_at = NOW()
                 WHERE id = ?
                   AND created_by = ?
            ");
            $stmt->execute([ $_POST['delete_id'], $currentUserId ]);
        }

        header('Location: expenses.php?success=' . urlencode('Expense moved to trashbin!'));
        exit;
    }
}

$success = $_GET['success'] ?? null;
$error   = $_GET['error']   ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Expenses</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* =========================
       Variables & Base Styles
       ========================= */
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --body-bg: #f5f7fb;
      --card-bg: #fff;
      --header-height: 70px;
      --sidebar-width: 250px;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0,0,0,.1);
      --shadow-hover: 0 8px 15px rgba(0,0,0,.1);
      --transition: .3s;
    }
    body {
      margin: 0;
      background: var(--body-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* =========================
       Layout
       ========================= */
    .app-container { display: flex; min-height: 100vh; }
    .main-content {
      flex: 1;
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }

    /* =========================
       Page Header
       ========================= */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }
    .page-actions {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    /* =========================
       Buttons
       ========================= */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: .6rem 1.2rem;
      border: none;
      border-radius: var(--radius);
      font-weight: 600;
      font-size: .9rem;
      cursor: pointer;
      transition: var(--transition);
    }
    .btn-primary {
      background: var(--primary);
      color: #fff;
    }
    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }
    .btn-outline:hover {
      background: var(--primary);
      color: #fff;
    }
    .btn-cancel {
      background: #adb5bd;
      color: #fff;
    }
    .btn-danger {
      background: var(--danger);
      color: #fff;
    }

     /* Icon buttons */
    .btn-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      cursor: pointer;
      transition: background .2s, color .2s;
      border: none;
      font-size: 1rem;
    }
    
    /* Edit (same as clients.php) */
    .btn-edit {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }
    .btn-edit:hover {
      background: rgba(76, 201, 240, 0.3);
    }
    
    /* View (same as clients.php) */
    .btn-view {
      background: rgba(67, 97, 238, 0.2);
      color: var(--primary);
    }
    .btn-view:hover {
      background: rgba(67, 97, 238, 0.3);
    }
    
    /* Delete (same as clients.php) */
    .btn-delete {
      background: rgba(247, 37, 133, 0.2);
      color: var(--danger);
    }
    .btn-delete:hover {
      background: rgba(247, 37, 133, 0.3);
    }
    /* Shared btn-group */
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 1.5rem;
    }

    /* =========================
       Alerts
       ========================= */
    .alert {
      padding: 1rem;
      border-radius: var(--radius);
      margin-bottom: 1.2rem;
    }
    .alert-success {
      background: rgba(76,201,240,.2);
      border: 1px solid var(--success);
      color: var(--success);
    }
    .alert-danger {
      background: rgba(247,37,133,.15);
      border: 1px solid var(--danger);
      color: var(--danger);
    }

    /* =========================
       Search
       ========================= */
    
    input.form-control {
      padding: 0.6rem;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      font-size: 0.95rem;
      background: var(--light);
      color: var(--dark);
      width: 100%;
      transition: var(--transition);
    }
    input.form-control:focus {
      outline: none;
      border-color: var(--primary);
    }


    /* inline action icons */
    .action-btn:hover {
      color: var(--primary);
    }


    /* =========================
       Table
       ========================= */

    .table-container {
      overflow-x: auto;
      margin-top: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: var(--radius);
      overflow: hidden;
    }

    th, td {
      padding: 1rem;
      border-bottom: 1px solid var(--border);
      text-align: center;
      vertical-align: middle;
    }
    th {
      background: rgba(67,97,238,.1);
      color: var(--primary);
      font-weight: 600;
    }
    tbody tr:hover { background: rgba(67,97,238,.05); }
    
    
    /* ────────────────────────────────────────────────────────────────
       Sorting indicator styles (from history.php)
    ───────────────────────────────────────────────────────────────── */
    th[data-sort] {
      cursor: pointer;
      user-select: none;
      text-align: center;
    }
    th[data-sort] .header-text {
      display: inline-block;
      margin-right: 4px;
    }
    .sort-indicator {
      display: inline-block;
      font-size: 0.75rem;
      color: var(--gray);
      opacity: 0.7;
      transform: translateY(-1px);
    }
    th.asc .sort-indicator::after {
      content: "▲";
      color: var(--primary);
      opacity: 1;
    }
    th.desc .sort-indicator::after {
      content: "▼";
      color: var(--primary);
      opacity: 1;
    }
    th:not(.asc):not(.desc) .sort-indicator::after {
      content: "⇅";
    }

    /* ─────────────────────────────────────────────────────────────────────────
   Force the same header background, text color, and rounded corners
   as history.php’s table
    ───────────────────────────────────────────────────────────────────────── */
    .table-container table thead th {
      background: rgba(67,97,238,.1) !important;
      color: var(--primary) !important;
      font-weight: 600;
    }
    .table-container table thead th:first-child {
      border-top-left-radius: var(--radius) !important;
    }
    .table-container table thead th:last-child {
      border-top-right-radius: var(--radius) !important;
    }

    /* =========================
       Status Dropdown (Pill)
       ========================= */
    .status-dropdown {
      appearance: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 14px;
      min-width: 100px;
      border-radius: 50px;
      font-weight: 600;
      font-size: .85rem;
      line-height: 1.2;
      border: 1px solid var(--primary-light);
      box-shadow: var(--shadow);
      cursor: pointer;
      transition: var(--transition);
      background: var(--card-bg);
      text-align: center;
    }
    .status-dropdown.paid {
      background-color: #e6f7ee;
      color: #28a745;
      border-color: #28a745;
    }
    .status-dropdown.unpaid {
      background-color: #fdecea;
      color: #f72585;
      border-color: #f72585;
    }
    .status-dropdown:hover {
      background: var(--primary-light);
      color: #fff;
    }
    /* Option styling */
    .status-dropdown option[value="Paid"] {
      background-color: #e6f7ee !important;
      color:            #28a745 !important;
    }
    .status-dropdown option[value="Unpaid"] {
      background-color: #fdecea !important;
      color:            #f72585 !important;
    }

    /* =========================
       Action Dropdown
       ========================= */
    /* Make the Actions cell a horizontal flex container */
    .actions-cell {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      white-space: nowrap;
      padding: 1rem;
    }
    
    .dropdown-menu.show { display: block; }
    .dropdown-menu a,
    .dropdown-menu button {
      display: block;
      width: 100%;
      text-align: left;
      padding: 10px 16px;
      font-size: 14px;
      background: #fff;
      border: none;
      cursor: pointer;
      color: #333;
      text-decoration: none;
    }
    .dropdown-menu a:hover,
    .dropdown-menu button:hover {
      background: #eef1f9;
    }

    /* =========================
       Modals
       ========================= */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.5);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    .modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: var(--radius);
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
      transition: background .2s, border .2s;
    }
    /* highlight on dragover */
    .modal-content.dragover {
      background: #eef4ff;
      border: 2px dashed var(--primary);
    }
    .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.4rem;
      cursor: pointer;
      color: var(--gray);
    }

    /* Modal inputs same styling as selects */
    .modal-content input[type="text"],
    .modal-content input[type="email"] {
      width: 100%;
      padding: 0.65rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 0.9rem;
      background: var(--light);
      color: var(--dark);
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
      transition: var(--transition);
    }
    .modal-content input[type="text"]:focus,
    .modal-content input[type="email"]:focus {
      outline: none;
      border-color: var(--primary);
    }
    .small-hint {
      font-size: 12px;
      color: var(--gray);
      margin-top: 6px;
    }
    
    /* Modal‐specific form elements */
    .modal-content select {
      width: 100%;
      padding: 0.65rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 0.9rem;
      background: var(--light);
      color: var(--dark);
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
      transition: var(--transition);
    }
    .modal-content select:focus {
      outline: none;
      border-color: var(--primary);
    }
    /* drag-drop hover on label */
    label[for="payment_proof"]:hover {
      background: #e6f7ff;
      border-color: var(--primary);
    }

    /* Progress bar & preview */
    #progressBar {
      height: 20px;
      background: var(--primary);
      width: 0;
      transition: width .3s;
    }
    #progressLogs {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: var(--dark);
      max-height: 150px;
      overflow-y: auto;
    }
    
    #progressLogs p {
      margin: 0.25rem 0;
      opacity: 0;
      animation: fadeInSlideUp 0.5s forwards;
    }
    
    @keyframes fadeInSlideUp {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    #proofPreview img {
      max-width: 100%;
      max-height: 300px;
      object-fit: contain;
    }
  </style>
</head>
<body>
  <?php require 'header.php'; ?>
  <div class="app-container">
    <?php require 'sidebar.php'; ?>
    <div class="main-content">

      <?php if ($success): ?>
        <div class="alert alert-success" id="successAlert">
          <i class="fas fa-check-circle"></i>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger" id="errorAlert">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="page-header">
        <h1 class="page-title">Expenses</h1>
        <div class="page-actions">
          <?php if (has_permission('add_expense')): ?>
            <a href="add-expense.php" class="btn btn-primary">
              <i class="fas fa-plus-circle"></i> Add Expense
            </a>
          <?php endif; ?>
          <?php if (has_permission('export_expenses')): ?>
            <form method="POST" action="export_expenses.php" style="display:inline;">
              <button type="submit" class="btn btn-outline">
                <i class="fas fa-file-export"></i> Export
              </button>
            </form>
            <?php endif; ?>
          <?php if (has_permission('undo_recent_expense')): ?>
            <form method="POST" style="display:inline;">
              <button name="undo_recent" class="btn btn-outline">
                <i class="fas fa-undo"></i> Undo Recent
              </button>
            </form>
            <?php if (has_permission('undo_all_expenses')): ?>
              <form method="POST" style="display:inline;">
                <button name="undo_all" class="btn btn-outline">
                  <i class="fas fa-history"></i> Undo All
                </button>
              </form>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

    <?php if (has_permission('search_expenses')): ?>
          <div style="max-width: 300px; margin-bottom: 1.2rem;">
            <input
              type="text"
              id="expenseSearch"
              class="form-control"
              placeholder="🔍 Search expenses…"
              style="width: 100%;"
            >
          </div>
        <?php endif; ?>

      <div class="table-container">
        <table id="expenseTable" class="table">
          <thead>
              <tr>
                <th data-sort="number">
                  <span class="header-text">#</span><span class="sort-indicator"></span>
                </th>
                
                <th data-sort="date">
                  <span class="header-text">Date</span><span class="sort-indicator"></span>
                </th>
                
                <th data-sort="string">
                  <span class="header-text">Vendor</span><span class="sort-indicator"></span>
                </th>
                
                <th data-sort="string">
                  <span class="header-text">Client</span><span class="sort-indicator"></span>
                </th>

                <?php if ($showUserColumn): ?>
                  <th data-sort="string">
                    <span class="header-text">User</span><span class="sort-indicator"></span>
                  </th>
                <?php endif; ?>

                <th data-sort="string">
                  <span class="header-text">Category</span><span class="sort-indicator"></span>
                </th>
                
                <th data-sort="number">
                  <span class="header-text">Amount</span><span class="sort-indicator"></span>
                </th>

                <?php if ($canViewRecurring || $canCreateRecurring || $canEditRecurring): ?>
                  <th data-sort="string">
                    <span class="header-text">Recurring</span><span class="sort-indicator"></span>
                  </th>
                <?php endif; ?>

                <th data-sort="string">
                  <span class="header-text">Status</span><span class="sort-indicator"></span>
                </th>
                <th>
                  <span class="header-text">Action</span>
                </th>

              </tr>
            </thead>
            <tbody>
                        <?php
            try {
               if ($canViewAllExpenses) {
                // ✅ Super roles: see ALL non-deleted expenses + show creator email
                $sql = "
                    SELECT 
                        e.*,
                        c.company_name,
                        u.email AS owner_email
                    FROM expenses e
                    LEFT JOIN clients c ON c.id = e.client_id
                    LEFT JOIN users   u ON u.id = e.created_by
                    WHERE e.deleted_at IS NULL
                    ORDER BY e.expense_date DESC, e.created_at DESC, e.id DESC
                ";
                $stmt = $pdo->query($sql);
            } else {
            // ✅ Normal roles: see ONLY their own expenses + still show their email
                $sql = "
                    SELECT 
                        e.*,
                        c.company_name,
                        u.email AS owner_email
                    FROM expenses e
                    LEFT JOIN clients c ON c.id = e.client_id
                    LEFT JOIN users   u ON u.id = e.created_by
                    WHERE e.deleted_at IS NULL
                      AND e.created_by = :uid
                    ORDER BY e.expense_date DESC, e.created_at DESC, e.id DESC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uid' => (int)$currentUserId]);
            }
            
                if (!$stmt) {
                    // If the query failed, show SQL error in the table
                    $errorInfo = $pdo->errorInfo();
                    echo '<tr><td colspan="10" style="color:#fff; background:#e03131; text-align:center;">'
                       . 'SQL error: ' . htmlspecialchars($errorInfo[2] ?? 'Unknown error')
                       . '</td></tr>';
                } else {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($rows)) {
                        // Query succeeded but no rows for this user / role
                        echo '<tr><td colspan="10" style="color:#555; background:#fff; text-align:center;">'
                           . 'No expenses found.'
                           . '</td></tr>';
                    } else {
                        $i = 1;
                        foreach ($rows as $row):
                            $paid  = trim(strtolower($row['status'] ?? '')) === 'paid';
                            $proof = htmlspecialchars($row['payment_proof'] ?? '');
                        ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td><?= !empty($row['expense_date']) ? date('Y-m-d', strtotime($row['expense_date'])) : '—' ?></td>
                          <td><?= htmlspecialchars($row['vendor'] ?? '—') ?></td>
                          <td><?= htmlspecialchars($row['company_name'] ?? '—') ?></td>

                          <?php if ($showUserColumn): ?>
                            <td><?= htmlspecialchars($row['owner_email'] ?? '—') ?></td>
                          <?php endif; ?>

                          <td><?= htmlspecialchars($row['category'] ?? '—') ?></td>
                          <td>CA$<?= isset($row['amount']) ? number_format($row['amount'], 2) : '0.00' ?></td>

                          <?php if ($canViewRecurring || $canCreateRecurring || $canEditRecurring): ?>
                            <td>
                              <?php if ($canViewRecurring): ?>
                                <?= !empty($row['is_recurring']) && (int)$row['is_recurring'] === 1
                                      ? '<span style="color:#28a745;">✔ Yes</span>'
                                      : '<span style="color:#999;">No</span>' ?>
                              <?php else: ?>
                                <span style="color:#999;">—</span>
                              <?php endif; ?>
                            </td>
                          <?php endif; ?>

                          <td>
                            <?php if (has_permission('change_expense_status')): ?>
                              <select
                                class="status-dropdown <?= $paid ? 'paid' : 'unpaid' ?>"
                                data-id="<?= $row['id'] ?>"
                                data-original="<?= $paid ? 'Paid' : 'Unpaid' ?>">
                                <option value="Unpaid" <?= !$paid ? 'selected' : '' ?>>✖ Unpaid</option>
                                <option value="Paid"   <?=  $paid ? 'selected' : '' ?>>✔ Paid</option>
                              </select>
                            <?php else: ?>
                              <span class="status-dropdown <?= $paid ? 'paid' : 'unpaid' ?>" style="pointer-events:none;">
                                <?= $paid ? 'Paid' : 'Unpaid' ?>
                              </span>
                            <?php endif; ?>
                          </td>

                          <td class="actions-cell">
                            <?php if (has_permission('edit_expense')): ?>
                              <button
                                class="btn btn-icon btn-edit"
                                title="Edit"
                                data-id="<?= $row['id'] ?>"
                                data-date="<?= htmlspecialchars($row['expense_date'] ?? '') ?>"
                                data-vendor="<?= htmlspecialchars($row['vendor'] ?? '') ?>"
                                data-category="<?= htmlspecialchars($row['category'] ?? '') ?>"
                                data-amount="<?= isset($row['amount']) ? number_format($row['amount'],2,'.','') : '0.00' ?>"
                                data-recurring="<?= (int)($row['is_recurring'] ?? 0) ?>"
                                data-client="<?= $row['client_id'] ?? '' ?>"
                              >
                                <i class="fas fa-edit"></i>
                              </button>
                            <?php endif; ?>

                            <?php if (has_permission('view_expenses')): ?>
                              <button
                                  class="btn btn-icon btn-view"
                                  title="View Details"
                                  data-method="<?= htmlspecialchars($row['payment_method'] ?? '') ?>"
                                  data-proof="<?= htmlspecialchars($row['payment_proof'] ?? '') ?>"
                                  data-cc="<?= htmlspecialchars($row['email_cc'] ?? '') ?>"
                                  data-bcc="<?= htmlspecialchars($row['email_bcc'] ?? '') ?>"
                                >

                                <i class="fas fa-info-circle"></i>
                              </button>
                            <?php endif; ?>

                            <?php
                              $isRecurring   = !empty($row['is_recurring']) && (int)$row['is_recurring'] === 1;
                              $canDeleteThis = (!$isRecurring && has_permission('delete_expense'))
                                               || ($isRecurring && $canDeleteRecurring);
                            ?>

                            <?php if ($canDeleteThis): ?>
                              <button
                                class="btn btn-icon btn-delete"
                                title="Delete"
                                data-id="<?= $row['id'] ?>"
                              >
                                <i class="fas fa-trash"></i>
                              </button>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php
                        endforeach;
                    }
                }
            } catch (PDOException $e) {
                echo '<tr><td colspan="10" style="color:#fff; background:#e03131; text-align:center;">'
                   . 'PDO Exception: ' . htmlspecialchars($e->getMessage())
                   . '</td></tr>';
            }
            ?>
            </tbody>
        </table>
      </div>

      <!-- Modals -->
      <div id="markPaidModal" class="modal">
        <div class="modal-content" id="markPaidContent">
          <span class="close-modal" onclick="closeModal('markPaidModal')">&times;</span>
          <h3 style="color: #3f37c9; font-weight: 700; margin-bottom: 1.5rem;">
            <i class="fas fa-receipt"></i> Confirm Expense Payment
          </h3>
          <p style="margin-bottom:1rem; font-weight:600; color:var(--dark);">
            Please provide payment details:
          </p>
          <form id="markPaidForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="expense_id" id="markPaidExpenseId">
            <div style="margin-bottom:1.5rem;">
              <label for="payment_method" style="font-weight:600; margin-bottom:0.5rem; display:block;">
                Payment Method
              </label>
              <select name="payment_method" id="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Check">Check</option>
                <option value="PayPal">PayPal</option>
                <option value="Other">Other</option>
              </select>
            </div>
            
            <div style="margin-bottom:1rem;">
              <label for="payment_proof" style="cursor:pointer; text-align:center; display:block;">
                <i class="fas fa-file-upload" style="font-size:2rem; color:#4895ef;"></i>
                <p id="proofLabel" style="margin:0.5rem 0 0.25rem;">
                  Click or drag a file here
                </p>
                <input type="file" name="payment_proof" id="payment_proof" accept="image/*,.pdf" style="display:none;">
              </label>
              <div id="proofPreview" style="margin-top:1rem;"></div>
            </div>
            <div class="btn-group" style="justify-content:flex-end;">
              <button type="button" class="btn btn-cancel" onclick="closeModal('markPaidModal')">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Payment</button>
            </div>
          </form>
        </div>
      </div>

      <div id="progressModal" class="modal">
        <div class="modal-content">
          <h2 style="margin-top:0; font-size: 1.5rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 10px;">
              <i class="fas fa-spinner fa-spin"></i> Processing Payment
            </h2>
            <p style="color: var(--dark); font-size: 0.95rem;">Please wait while we save your payment information...</p>
          <div style="height:20px; background:#eee; border-radius:var(--radius); margin:1.5rem 0; overflow:hidden;">
            <div id="progressBar"></div>
          </div>
          <div id="progressLogs"></div>
        </div>
      </div>

      <div id="successModal" class="modal">
        <div class="modal-content" style="text-align:center; padding:2rem;">
          <div style="margin-bottom: 1.5rem;">
              <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--success);"></i>
          </div>
          <h2 style="margin: 0; font-size: 1.6rem; font-weight: 700; color: var(--primary);">
              Payment Saved!
          </h2>
          <p style="color: var(--dark); font-size: 1rem; margin-top: 0.5rem;">
              Expense marked as paid successfully.
          </p>
          <button class="btn btn-primary" style="margin-top:1.5rem;" onclick="closeModal('successModal')">Close</button>
        </div>
      </div>

      <div id="paymentInfoModal" class="modal">
        <div class="modal-content" style="padding:2rem; max-width:550px;">
          <span class="close-modal" onclick="closeModal('paymentInfoModal')">&times;</span>
          <h3 style="color:#3f37c9; font-weight:700; margin-bottom:1.5rem;">
            <i class="fas fa-credit-card"></i> Payment Information
          </h3>
          <div style="font-size:14px; text-align:left; line-height:1.6;">
            <p><strong>Payment Method:</strong> <span id="payMethod">N/A</span></p>
            <p><strong>Proof:</strong> <span id="payProofLink">No file</span></p>
            <p><strong>CC:</strong> <span id="payCC">N/A</span></p>
            <p><strong>BCC:</strong> <span id="payBCC">N/A</span></p>
          </div>
          <div class="btn-group" style="justify-content:flex-end;">
            <button type="button" class="btn btn-cancel" onclick="closeModal('paymentInfoModal')">Close</button>
          </div>
        </div>
      </div>
      
      <div id="deleteExpenseModal" class="modal">
      <div class="modal-content" style="text-align:center; padding:2rem;">
        <span class="close-modal" onclick="closeModal('deleteExpenseModal')">&times;</span>
        <i class="fas fa-exclamation-triangle" style="font-size:3rem; color:var(--danger); margin-bottom:1rem;"></i>
        <h2 style="margin:0; font-size:1.5rem; color:var(--primary); font-weight:700;">
          Confirm Deletion
        </h2>
        <p style="color:var(--dark); margin:1rem 0;">Are you sure you want to delete this expense?</p>
        <form method="POST" id="deleteExpenseForm">
          <input type="hidden" name="delete_id" id="deleteExpenseId">
          <div class="btn-group" style="justify-content:center;">
            <button type="button" class="btn btn-cancel" onclick="closeModal('deleteExpenseModal')">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
    
    <div id="editExpenseModal" class="modal">
      <div class="modal-content" style="max-width:600px;">
        <span class="close-modal" onclick="closeModal('editExpenseModal')">&times;</span>
        <h3 style="color: #3f37c9; font-weight: 700; margin-bottom: 1.5rem;">
          <i class="fas fa-edit"></i> Edit Expense
        </h3>
        <form method="POST" action="update-expense.php">
          <input type="hidden" name="expense_id" id="editExpenseId">
    
          <label for="editDate">Date</label>
          <input type="date" name="expense_date" id="editDate" required style="width:100%; margin-bottom:1rem; padding:0.6rem; border-radius:var(--radius); border:1px solid var(--border);">
    
          <label for="editVendor">Vendor</label>
          <input type="text" name="vendor" id="editVendor" required style="width:100%; margin-bottom:1rem; padding:0.6rem; border-radius:var(--radius); border:1px solid var(--border);">
          
          <label for="editClient">Client</label>
            <select
              name="client_id"
              id="editClient"
              required
              style="width:100%; margin-bottom:1rem; padding:0.6rem; border-radius:var(--radius); border:1px solid var(--border);"
            >
            <option value="">— Select client —</option>
              <?php foreach($clients as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <!-- ↑ end insertion -->

          <label for="editCategory">Category</label>
          <input type="text" name="category" id="editCategory" required style="width:100%; margin-bottom:1rem; padding:0.6rem; border-radius:var(--radius); border:1px solid var(--border);">
    
          <label for="editAmount">Amount</label>
          <input type="number" step="0.01" name="amount" id="editAmount" required style="width:100%; margin-bottom:1.5rem; padding:0.6rem; border-radius:var(--radius); border:1px solid var(--border);">
    
          <?php if ($canCreateRecurring || $canEditRecurring): ?>
          <label>
            <input type="checkbox" name="is_recurring" id="editRecurring">
            Mark as Recurring
          </label>
          <?php endif; ?>
        
          <div class="btn-group" style="justify-content:flex-end;">
            <button type="button" class="btn btn-cancel" onclick="closeModal('editExpenseModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>

      <?php require 'scripts.php'; ?>
      <script>
        // globally available
        function closeModal(id) {
          document.getElementById(id).style.display = 'none';
        }
        
        // ✅ Step 1 helper (paste this here)
        function handleSessionExpired(j) {
          if (j && j.error === 'session_expired' && j.redirect) {
            window.location.href = j.redirect;
            return true;
          }
          return false;
        }
        
        // click backdrop to close
        window.addEventListener('click', e => {
          if (e.target.classList.contains('modal')) closeModal(e.target.id);
        });

        document.addEventListener('DOMContentLoaded', function() {
          // dropdown logic
          document.addEventListener('click', e => {
            if (!e.target.closest('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
              document.querySelectorAll('.dropdown-menu.show')
                      .forEach(m => m.classList.remove('show'));
            }
          });
          
          // ────────────── click-to-sort on any <th data-sort> ──────────────
            document.querySelectorAll('th[data-sort]').forEach(header => {
              header.addEventListener('click', () => {
                const table  = header.closest('table');
                const tbody  = table.querySelector('tbody');
                const index  = Array.from(header.parentNode.children).indexOf(header);
                const type   = header.getAttribute('data-sort');
                const currentDir = header.classList.contains('asc') ? 'desc' : 'asc';
                // clear classes on all headers
                table.querySelectorAll('th').forEach(th => th.classList.remove('asc','desc'));
                header.classList.add(currentDir);
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a,b) => {
                 /* let aText = a.children[index].textContent.trim();
                  let bText = b.children[index].textContent.trim();*/
                  
                  let aText, bText;
                  const cellA = a.children[index];
                  const cellB = b.children[index];
                  if (cellA.querySelector('select')) {
                    aText = cellA.querySelector('select').value;
                    bText = cellB.querySelector('select').value;
                  } else {
                    aText = cellA.textContent.trim();
                    bText = cellB.textContent.trim();
                  }
                  if (type === 'number') {
                    aText = parseFloat(aText.replace(/[^0-9.-]/g,'')) || 0;
                    bText = parseFloat(bText.replace(/[^0-9.-]/g,'')) || 0;
                  }
                  if (type === 'date') {
                    aText = new Date(aText);
                    bText = new Date(bText);
                  }
                  return currentDir === 'asc'
                    ? (aText > bText ? 1 : aText < bText ? -1 : 0)
                    : (aText < bText ? 1 : aText > bText ? -1 : 0);
                });
                rows.forEach(r => tbody.appendChild(r));
              });
            });

          document.querySelectorAll('.dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
              e.stopPropagation();
              document.querySelectorAll('.dropdown-menu.show')
                      .forEach(m => m.classList.remove('show'));
              this.closest('.dropdown')
                  .querySelector('.dropdown-menu')
                  .classList.add('show');
            });
          });
          document.querySelectorAll('.dropdown-menu a, .dropdown-menu button')
                  .forEach(item => item.addEventListener('click', e => {
                    e.stopPropagation();
                    document.querySelectorAll('.dropdown-menu.show')
                            .forEach(m => m.classList.remove('show'));
                  }));

          // status dropdown paint
            function paint(selectEl) {
              // only for <select>, not spans
              if (!selectEl || selectEl.tagName !== 'SELECT') return;
            
              selectEl.classList.remove('paid', 'unpaid');
              selectEl.classList.add(selectEl.value.toLowerCase());
            }
            
            // paint on load (only selects)
            document.querySelectorAll('select.status-dropdown').forEach(paint);
            
            // handle status changes (delegated)
            document.addEventListener('change', function (e) {
              const sel = e.target;
            
              if (!sel || !sel.matches('select.status-dropdown')) return;
            
              const id   = sel.dataset.id;
              const newS = sel.value;         // "Paid" or "Unpaid"
              const orig = sel.dataset.original;
            
              paint(sel);
            
              // Paid => open modal and revert dropdown until confirmed
              if (newS === 'Paid') {
                document.getElementById('markPaidExpenseId').value = id;
                document.getElementById('proofLabel').textContent = 'Click or drag a file here';
                document.getElementById('proofPreview').innerHTML = '';
                document.getElementById('payment_proof').value = '';

                sel.value = orig;
                paint(sel);
            
                document.getElementById('markPaidModal').style.display = 'flex';
                return;
              }
            
              // Unpaid => AJAX update
              fetch('update_expense_status.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ expense_id: id, status: 'Unpaid' })
              })
              .then(r => r.json())
              .then(j => {
                if (handleSessionExpired(j)) return;
            
                if (j.success) {
                  setTimeout(() => location.reload(), 600);
                } else {
                  alert(j.error || 'Failed to update status');
                  sel.value = orig;
                  paint(sel);
                }
              })
              .catch(() => {
                window.location.href = 'login.php?error=' + encodeURIComponent('Your session has expired. Please log in again.');
              });
            });
            
            // drag & drop on markPaidModal

          const paidModal    = document.getElementById('markPaidModal');
          const contentDiv   = document.getElementById('markPaidContent');
          const fileInput    = document.getElementById('payment_proof');
          const proofLabel   = document.getElementById('proofLabel');
          const previewDiv   = document.getElementById('proofPreview');

          function preventDefaults(e){
            e.preventDefault();
            e.stopPropagation();
          }
          ['dragenter','dragover','dragleave','drop'].forEach(evt => {
            paidModal.addEventListener(evt, preventDefaults, false);
          });
          ['dragenter','dragover'].forEach(evt => {
            paidModal.addEventListener(evt, ()=> contentDiv.classList.add('dragover'), false);
          });
          ['dragleave','drop'].forEach(evt => {
            paidModal.addEventListener(evt, ()=> contentDiv.classList.remove('dragover'), false);
          });
          paidModal.addEventListener('drop', e => {
            let dt = e.dataTransfer;
            let file = dt.files[0];
            if (!file) return;
            // feed into input
            const data = new DataTransfer();
            data.items.add(file);
            fileInput.files = data.files;
            // preview
            proofLabel.textContent = file.name;
            previewDiv.innerHTML = '';
            if (file.type.startsWith('image/')) {
              const img = new Image();
              img.src = URL.createObjectURL(file);
              img.style.maxWidth = '100%';
              img.style.marginTop = '1rem';
              previewDiv.appendChild(img);
            } else if (file.type === 'application/pdf') {
              const fr = document.createElement('iframe');
              fr.src = URL.createObjectURL(file);
              fr.width = '100%'; fr.height = 300;
              fr.style.border = '1px solid #ccc';
              previewDiv.appendChild(fr);
            } else {
              previewDiv.textContent = 'Preview not supported.';
            }
          });

          // file-input change (preview)
          fileInput.addEventListener('change', function() {
            const f = this.files[0];
            if (!f) return;
            proofLabel.textContent = f.name;
            previewDiv.innerHTML = '';
            if (f.type.startsWith('image/')) {
              const img = new Image();
              img.src = URL.createObjectURL(f);
              img.style.maxWidth = '100%';
              img.style.marginTop = '1rem';
              previewDiv.appendChild(img);
            } else if (f.type === 'application/pdf') {
              const fr = document.createElement('iframe');
              fr.src = URL.createObjectURL(f);
              fr.width = '100%'; fr.height = 300;
              fr.style.border = '1px solid #ccc';
              previewDiv.appendChild(fr);
            } else {
              previewDiv.textContent = 'Preview not supported.';
            }
          });

          // progress logs helper
          // slower animated log queue
            const logQueue = [];
            let logInProgress = false;
            let progress = 0;
            const progressBar = document.getElementById('progressBar');

            function addLogQueuedWithProgress(msg) {
              logQueue.push(msg);
              if (!logInProgress) processLogQueue();
            }
            
            function processLogQueue() {
              if (logQueue.length === 0) {
                logInProgress = false;
                return;
              }
            
              logInProgress = true;
              const msg = logQueue.shift();
              const lg = document.getElementById('progressLogs');
              const p  = document.createElement('p');
            
              let icon = '🟢'; // default

                if (msg.includes('Initializing upload')) icon = '📤';
                else if (msg.includes('Validating form and file data')) icon = '🧪';
                else if (msg.includes('Saving payment method')) icon = '💾';
                else if (msg.includes('Uploading payment proof')) icon = '📎';
                else if (msg.includes('Generating updated invoice preview')) icon = '🧾';
                else if (msg.includes('Sending email notification')) icon = '✉️';
                else if (msg.toLowerCase().includes('error')) icon = '❌';
                else if (msg.toLowerCase().includes('success')) icon = '✅';
            
              p.textContent = `${icon} ${msg}`;
              lg.appendChild(p);
              lg.scrollTop = lg.scrollHeight;
            
              // ⏳ Animate progress bar
              progress += Math.round(100 / 6); // 6 real steps now
              if (progress > 100) progress = 100;
              progressBar.style.width = progress + '%';
            
              setTimeout(processLogQueue, 1200);
            }

          // mark-paid form + XHR upload with logs
          document.getElementById('markPaidForm')
                  .addEventListener('submit', e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            fd.append('status','Paid');

            // clear logs & reset bar
            document.getElementById('progressBar').style.width = '0%';
            progress = 0;
            progressBar.style.width = '0%';
            document.getElementById('progressLogs').innerHTML = '';
            progress = 0;
            progressBar.style.width = '0%';
            
            // Realistic and backend-mapped steps:
            addLogQueuedWithProgress('Initializing upload…');                         // step 1
            addLogQueuedWithProgress('Validating form and file data…');               // step 2
            addLogQueuedWithProgress('Saving payment method in database…');           // step 3
            addLogQueuedWithProgress('Uploading payment proof to server…');           // step 4
            addLogQueuedWithProgress('Generating updated invoice preview…');          // step 5
            addLogQueuedWithProgress('Sending email notification to client…');        // step 6


            closeModal('markPaidModal');
            document.getElementById('progressModal').style.display = 'flex';

            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', ev => {
            });
            xhr.addEventListener('load', () => {
              addLogQueuedWithProgress('Upload complete. Processing response…');
              let j = {};
             // If session expired, middleware returns 401 JSON
                if (xhr.status === 401) {
                  window.location.href = 'login.php?error=' + encodeURIComponent('Your session has expired. Please log in again.');
                  return;
                }
                
                try { j = JSON.parse(xhr.responseText); }
                catch (err) { addLogQueuedWithProgress('Invalid server response'); }
                
                // ✅ session expired JSON
                if (handleSessionExpired(j)) return;
                
                if (j.success) {
                  addLogQueuedWithProgress('Server processed successfully.');
                  
                  // Wait until all logs are shown before continuing
                  const checkLogQueueFinished = setInterval(() => {
                    if (logQueue.length === 0 && !logInProgress) {
                      clearInterval(checkLogQueueFinished);
                
                      // Now show success modal after all logs are animated
                      setTimeout(() => {
                        document.getElementById('progressModal').style.display = 'none';
                        document.getElementById('successModal').style.display = 'flex';
                
                        // After showing modal, wait again before reload
                        setTimeout(() => {
                          location.reload();
                        }, 3000);
                      }, 1000); // Optional pause after last log
                    }
                  }, 300); // check every 300ms
                }
                
                else {
                addLogQueuedWithProgress('Error from server.');
                setTimeout(()=>{
                  alert('Error saving payment info');
                  document.getElementById('progressModal').style.display = 'none';
                },300);
              }
            });
            xhr.open('POST', 'update_expense_status.php');
            
            // ✅ Make middleware treat it as AJAX + JSON
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.send(fd);

          });

          // view-details modal open
            // Actions (Edit / View / Delete) — delegated so it keeps working after sort
            document.addEventListener('click', function (e) {
              const editBtn = e.target.closest('.btn-edit');
              const viewBtn = e.target.closest('.btn-view');
              const delBtn  = e.target.closest('.btn-delete');
            
              // only handle clicks inside the expenses table
              if (!e.target.closest('#expenseTable')) return;
            
              if (editBtn) {
                document.getElementById('editExpenseId').value = editBtn.dataset.id;
                document.getElementById('editDate').value      = editBtn.dataset.date;
                document.getElementById('editVendor').value    = editBtn.dataset.vendor;
                document.getElementById('editCategory').value  = editBtn.dataset.category;
                document.getElementById('editAmount').value    = editBtn.dataset.amount;
            
                const recurringCheckbox = document.getElementById('editRecurring');
                if (recurringCheckbox) {
                  recurringCheckbox.checked = editBtn.dataset.recurring === '1';
                }
            
                document.getElementById('editClient').value = editBtn.dataset.client || '';
                document.getElementById('editExpenseModal').style.display = 'flex';
                return;
              }
            
              if (viewBtn) {
                document.getElementById('payMethod').textContent = viewBtn.dataset.method || 'N/A';
                document.getElementById('payCC').textContent  = (viewBtn.dataset.cc || '').trim()  || 'N/A';
                document.getElementById('payBCC').textContent = (viewBtn.dataset.bcc || '').trim() || 'N/A';
            
                const proof = (viewBtn.dataset.proof || '').trim();
                const isValid = proof && proof.toLowerCase() !== 'null';
            
                document.getElementById('payProofLink').innerHTML = isValid
                  ? `<a href="${proof}" target="_blank">View Proof</a>`
                  : 'N/A';
            
                document.getElementById('paymentInfoModal').style.display = 'flex';
                return;
              }
            
              if (delBtn) {
                confirmDeleteExpense(delBtn.dataset.id);
                return;
              }
            });

          // auto-hide alerts
          setTimeout(()=>{
            ['successAlert','errorAlert'].forEach(id=>{
              const a = document.getElementById(id);
              if (a) a.style.display = 'none';
            });
          // remove ?success (and ?error) from the URL without reloading
            window.history.replaceState(null, document.title, window.location.pathname);
            },5000);
            
        // ——————— Table Search (exactly like history.php) ———————
        if (document.getElementById('expenseSearch')) {
          document
            .getElementById('expenseSearch')
            .addEventListener('input', function () {
              const q = this.value.toLowerCase();
              document
                .querySelectorAll('#expenseTable tbody tr')
                .forEach(row => {
                  const matches = [...row.cells]
                    .some(td => td.textContent.toLowerCase().includes(q));
                  row.style.display = matches ? '' : 'none';
                });
            });
        }
            
        }); // DOMContentLoaded
        
        function confirmDeleteExpense(id) {
          document.getElementById('deleteExpenseId').value = id;
          document.getElementById('deleteExpenseModal').style.display = 'flex';
        }

      </script>
    </div>
  </div>
<?php ob_end_flush(); ?>
</body>
</html>
