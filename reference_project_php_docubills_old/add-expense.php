<?php
session_start();
$activeMenu = 'expenses';
require_once 'config.php';
require_once 'middleware.php';
require 'styles.php';

if (!has_permission('add_expense')) {
  $_SESSION['access_denied'] = true;
  header("Location: access-denied.php");
  exit;
}

$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['expense_date'] ?? date('Y-m-d');
    $vendor = trim($_POST['vendor'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $client_id = intval($_POST['client_id'] ?? 0);
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;

        $stmt = $pdo->prepare("
        INSERT INTO expenses (
            expense_date,
            vendor,
            amount,
            category,
            client_id,
            is_recurring,
            created_by,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $date,
        $vendor,
        $amount,
        $category,
        $client_id ?: null,
        $is_recurring,
        $_SESSION['user_id'] ?? null   // 🔑 who created this expense
    ]);

    $success = "Expense added successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Expense</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php require 'scripts.php'; ?>
  <style>
    /* DASHBOARD THEME STYLES from clients.php — already integrated correctly */
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --header-height: 70px;
      --sidebar-width: 250px;
      --transition: all 0.3s ease;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
    }

    body {
      background-color: var(--body-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--dark);
      margin: 0;
    }

    .app-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      margin-left: var(--sidebar-width);
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
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

    .form-card {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      max-width: 800px;
      margin: 0 auto;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .form-control {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
      background: white;
      color: var(--dark);
    }

    .form-control:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .btn {
      padding: 0.6rem 1.4rem;
      border-radius: var(--radius);
      font-weight: 600;
      font-size: 1rem;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: var(--transition);
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
    }

    .alert-success {
      background: rgba(76, 201, 240, 0.2);
      border: 1px solid var(--success);
      color: var(--success);
      padding: 1rem;
      border-radius: var(--radius);
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
<?php require 'header.php'; ?>

<div class="app-container">
  <?php require 'sidebar.php'; ?>

  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Add New Expense</h1>
    </div>

    <?php if ($success): ?>
      <div class="alert-success" id="successAlert">
        <i class="fas fa-check-circle"></i> <?= $success ?>
      </div>
    <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="expense_date">Date</label>
          <input type="date" name="expense_date" id="expense_date" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
          <label for="vendor">Vendor</label>
          <input type="text" name="vendor" id="vendor" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="amount">Amount</label>
          <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <input type="text" name="category" id="category" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="client_id">Client</label>
          <select name="client_id" id="client_id" class="form-control">
            <option value="">-- Select Client --</option>
            <?php
              $clients = $pdo->query("SELECT id, company_name FROM clients WHERE deleted_at IS NULL ORDER BY company_name ASC")->fetchAll();
              foreach ($clients as $client):
            ?>
              <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['company_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" name="is_recurring"> Recurring Expense
          </label>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add Expense
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  setTimeout(() => {
    const success = document.getElementById('successAlert');
    if (success) success.style.display = 'none';
  }, 8000);
</script>
</body>
</html>
