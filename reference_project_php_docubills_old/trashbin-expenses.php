<?php
session_start();
require_once 'config.php';
require_once 'middleware.php';
$activeMenu = 'expenses_trash';
$activeTab  = '';
$activeSub  = '';

// 🔐 Access control: user must be allowed to view expenses trashbin in some way
if (!has_permission('view_expenses_trashbin') && !has_permission('view_all_expenses_trashbin')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}

// Who is the current user & can they see all users' trash?
$currentUserId        = (int)($_SESSION['user_id'] ?? 0);
$canViewAllTrashbin   = has_permission('view_all_expenses_trashbin');

// 1) POST → Redirect (PRG pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // restore one
  if (!empty($_POST['restore_expense_id']) && has_permission('restore_expenses')) {
    $expenseId = (int)$_POST['restore_expense_id'];

    if ($canViewAllTrashbin) {
      // can restore any expense
      $stmt = $pdo->prepare("UPDATE expenses SET deleted_at = NULL WHERE id = ?");
      $stmt->execute([$expenseId]);
    } else {
      // can only restore own trashed expenses
      $stmt = $pdo->prepare("UPDATE expenses SET deleted_at = NULL WHERE id = ? AND created_by = ?");
      $stmt->execute([$expenseId, $currentUserId]);
    }

    header("Location: trashbin-expenses.php?success=" . urlencode("Expense restored successfully!"));
    exit;
  }

  // restore all
  if (!empty($_POST['restore_all_expenses']) && has_permission('restore_expenses')) {

    if ($canViewAllTrashbin) {
      // restore all trashed expenses in the system
      $stmt = $pdo->prepare("UPDATE expenses SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
      $stmt->execute();
    } else {
      // restore only this user's trashed expenses
      $stmt = $pdo->prepare("
        UPDATE expenses
           SET deleted_at = NULL
         WHERE deleted_at IS NOT NULL
           AND created_by = ?
      ");
      $stmt->execute([$currentUserId]);
    }

    header("Location: trashbin-expenses.php?success=" . urlencode("All deleted expenses have been restored!"));
    exit;
  }

  // delete forever
  if (!empty($_POST['delete_forever_expense_id']) && has_permission('delete_expense_forever')) {
    $expenseId = (int)$_POST['delete_forever_expense_id'];

    if ($canViewAllTrashbin) {
      // can permanently delete any expense
      $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
      $stmt->execute([$expenseId]);
    } else {
      // can only permanently delete own trashed expenses
      $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND created_by = ?");
      $stmt->execute([$expenseId, $currentUserId]);
    }

    header("Location: trashbin-expenses.php?success=" . urlencode("Expense permanently deleted!"));
    exit;
  }
}

// 2) Layout includes (only once)
require 'styles.php';
require 'scripts.php';
require 'header.php';

// 3) Pull any flash message
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Trash Bin – Expenses</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --header-height: 70px;
      --sidebar-width: 250px;
      --transition: all 0.3s ease;
      --shadow: 0 4px 6px rgba(0,0,0,.1);
      --shadow-hover: 0 8px 15px rgba(0,0,0,.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
    }

    body {
      background: var(--body-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
    }

    .main-content {
      flex: 1;
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }

    .btn {
      padding: 0.6rem 1.2rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary);
      color: white;
    }

    .btn-edit {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .alert {
      padding: 1rem;
      border-radius: var(--radius);
      margin-bottom: 1.5rem;
    }

    .alert-success {
      background: rgba(76, 201, 240, 0.2);
      border: 1px solid var(--success);
      color: var(--success);
    }

    .table-container {
      overflow-x: auto;
      margin-top: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: var(--radius);
      overflow: hidden;
      background: #fff;
      box-shadow: var(--shadow);
    }

    th, td {
      padding: 1rem;
      text-align: center;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67,97,238,0.1);
      color: var(--primary);
      font-weight: 600;
    }

    tbody tr:hover {
      background: rgba(67,97,238,0.05);
    }

    .actions-cell {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
    }

    #historySearch {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      margin-bottom: 1.2rem;
      max-width: 300px;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="app-container">
    <?php require 'sidebar.php'; ?>
    <div class="main-content">

      <?php if ($success): ?>
        <div class="alert alert-success" id="successAlert">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <div class="page-header">
        <h1 class="page-title">Trash Bin – Expenses</h1>
        <?php if (has_permission('restore_expenses')): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="restore_all_expenses" value="1">
            <button class="btn btn-outline">
              <i class="fas fa-recycle"></i> Restore All
            </button>
          </form>
        <?php endif; ?>
      </div>

      <div class="table-container">
        <input type="text" id="historySearch" placeholder="🔍 Search expenses…" class="form-control">
        <table id="historyTable">
          <thead>
            <tr>
              <th>#</th><th>Date</th><th>Vendor</th>
              <th>Amount</th><th>Category</th>
              <th>Client</th><th>Recurring</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if ($canViewAllTrashbin) {
                // 🔓 See all trashed expenses
                $sql = "
                  SELECT e.*, c.company_name
                    FROM expenses e
                    LEFT JOIN clients c ON e.client_id = c.id
                   WHERE e.deleted_at IS NOT NULL
                   ORDER BY e.deleted_at DESC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
              } else {
                // 🔒 Only see your own trashed expenses
                $sql = "
                  SELECT e.*, c.company_name
                    FROM expenses e
                    LEFT JOIN clients c ON e.client_id = c.id
                   WHERE e.deleted_at IS NOT NULL
                     AND e.created_by = :uid
                   ORDER BY e.deleted_at DESC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uid' => $currentUserId]);
              }

              $i = 1;
              while ($row = $stmt->fetch()):
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['expense_date']) ?></td>
              <td><?= htmlspecialchars($row['vendor']) ?></td>
              <td>CA$<?= number_format($row['amount'], 2) ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td><?= htmlspecialchars($row['company_name'] ?? '-') ?></td>
              <td><?= $row['is_recurring'] ? 'Yes' : 'No' ?></td>
              <td class="actions-cell">
                <?php if (has_permission('restore_expenses')): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="restore_expense_id" value="<?= $row['id'] ?>">
                    <button class="btn btn-edit"><i class="fas fa-undo"></i></button>
                  </form>
                <?php endif; ?>

                <?php if (has_permission('delete_expense_forever')): ?>
                  <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Permanently delete this expense? This cannot be undone.');">
                    <input type="hidden" name="delete_forever_expense_id" value="<?= $row['id'] ?>">
                    <button class="btn btn-danger"><i class="fas fa-times-circle"></i></button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div> <!-- end main-content -->
  </div> <!-- end app-container -->

   <?php require 'scripts.php'; ?>
  <script>
    // 1) On load, strip ?success=… immediately so a reload won’t re-show the toast
    document.addEventListener('DOMContentLoaded', () => {
      if (window.location.search.includes('success=')) {
        window.history.replaceState(null, document.title, window.location.pathname);
      }
    });

    // 2) Auto-hide flash (toast)
    setTimeout(() => {
      const a = document.getElementById('successAlert');
      if (a) a.style.display = 'none';
    }, 8000);

    // 3) Simple table search
    document.getElementById('historySearch').addEventListener('input', function () {
      const q = this.value.toLowerCase();
      document.querySelectorAll('#historyTable tbody tr').forEach(row => {
        row.style.display = [...row.cells]
          .some(td => td.textContent.toLowerCase().includes(q))
          ? '' : 'none';
      });
    });
  </script>
</body>
</html>