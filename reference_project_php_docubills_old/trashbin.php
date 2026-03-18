<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Always use this at top of dynamic pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$success = isset($_GET['success']) ? trim($_GET['success']) : null;
$error   = isset($_GET['error']) ? trim($_GET['error']) : null;


$activeMenu = 'trashbin';
require_once 'config.php';
require_once 'middleware.php';

// 🚫 Block users who don't have access to the Trash Bin at all
if (!has_permission('access_trashbin')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}

// 🔐 Current user + permission
$currentUserId   = $_SESSION['user_id'];
$canViewAllTrash = has_permission('view_all_trash'); // <- give this to Super Admin / others who should see ALL trash

/**
 * Small helper to detect owner column for a table (created_by, user_id, owner_id, etc.)
 * so we can safely filter per user only when the column exists.
 */
function findOwnerColumn($pdo, $tableName, array $candidates) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM {$tableName}")->fetchAll(PDO::FETCH_COLUMN);

        // 1) Exact match (case-insensitive) for preferred candidates
        foreach ($candidates as $wanted) {
            foreach ($cols as $colName) {
                if (strcasecmp($colName, $wanted) === 0) {
                    error_log("TrashBin: using owner column '{$colName}' for table {$tableName}");
                    return $colName;
                }
            }
        }

        // 2) Heuristic fallback: look for anything that *contains* user/owner hints
        foreach ($cols as $colName) {
            $lower = strtolower($colName);
            if (
                strpos($lower, 'user_id')   !== false ||
                strpos($lower, 'created_by')!== false ||
                strpos($lower, 'owner')     !== false
            ) {
                error_log("TrashBin: heuristically picked owner column '{$colName}' for table {$tableName}");
                return $colName;
            }
        }
    } catch (Exception $e) {
        error_log("TrashBin: failed to inspect columns for {$tableName} – " . $e->getMessage());
    }

    error_log("TrashBin: no owner column detected for {$tableName}");
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
// ✅ Permanently delete ALL items currently in the Trash Bin
if (isset($_POST['perm_delete_all'])) {
    if ($canViewAllTrash) {
        // Super Admin / full access: delete everything in trash
        $pdo->exec("DELETE FROM invoices WHERE deleted_at IS NOT NULL");
        $pdo->exec("DELETE FROM clients WHERE deleted_at IS NOT NULL");
        $pdo->exec("DELETE FROM email_templates WHERE deleted_at IS NOT NULL");
        $pdo->exec("DELETE FROM users WHERE deleted_at IS NOT NULL");
    } else {
        // Regular roles: only delete their OWN trashed items
        $stmt = $pdo->prepare("DELETE FROM invoices WHERE deleted_at IS NOT NULL AND created_by = ?");
        $stmt->execute([$currentUserId]);

        $stmt = $pdo->prepare("DELETE FROM clients WHERE deleted_at IS NOT NULL AND created_by = ?");
        $stmt->execute([$currentUserId]);

        $stmt = $pdo->prepare("DELETE FROM email_templates WHERE deleted_at IS NOT NULL AND created_by = ?");
        $stmt->execute([$currentUserId]);

        // 🔒 Never let non-super delete user accounts from trash
    }

    $success = "All deleted items have been permanently removed!";
}

// Permanently delete
if (isset($_POST['delete_id']) && isset($_POST['type'])) {
    $type = $_POST['type'];
    $id = intval($_POST['delete_id']);

    if ($type === 'invoice') {
        $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
    } elseif ($type === 'client') {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    } elseif ($type === 'template') {
        $stmt = $pdo->prepare("DELETE FROM email_templates WHERE id = ?");
    }
      elseif ($type === 'user') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    }


    if (isset($stmt)) {
        $stmt->execute([$id]);
        $success = "Item permanently deleted!";
    }
}

    // Restore single item
    if (isset($_POST['restore_id']) && isset($_POST['type'])) {
        $type = $_POST['type'];
        $id   = intval($_POST['restore_id']);
    
        if ($type === 'invoice') {
            $stmt = $pdo->prepare("UPDATE invoices SET deleted_at = NULL, created_at = NOW() WHERE id = ?");
        } elseif ($type === 'client') {
            $stmt = $pdo->prepare("UPDATE clients SET deleted_at = NULL WHERE id = ?");
        } elseif ($type === 'template') {
            $stmt = $pdo->prepare("UPDATE email_templates SET deleted_at = NULL, updated_at = NOW() WHERE id = ?");
        }
           elseif ($type === 'user') {
        // 1) fetch the old data
        $q = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
        $q->execute([$id]);
        list($origUsername, $origEmail) = $q->fetch(PDO::FETCH_NUM);

        // 2) check username collision
        $uCheck = $pdo->prepare("
          SELECT COUNT(*) FROM users 
          WHERE username = ? AND deleted_at IS NULL
        ");
        $uCheck->execute([$origUsername]);
        $safeUsername = $origUsername;
        if ($uCheck->fetchColumn() > 0) {
            $safeUsername = $origUsername . '_' . $id;
        }

        // 3) check email collision
        $eCheck = $pdo->prepare("
          SELECT COUNT(*) FROM users 
          WHERE email = ? AND deleted_at IS NULL
        ");
        $eCheck->execute([$origEmail]);
        $safeEmail = $origEmail;
        if ($eCheck->fetchColumn() > 0) {
            // split local@domain, append _id to local
            list($local, $domain) = explode('@', $origEmail, 2);
            $safeEmail = $local . '_' . $id . '@' . $domain;
        }

        // 4) perform restore with safe username & email
        $stmt = $pdo->prepare("
          UPDATE users 
          SET deleted_at = NULL, username = ?, email = ?
          WHERE id = ?
        ");
        $stmt->execute([$safeUsername, $safeEmail, $id]);
    }

    // 👉 Run the UPDATE for invoices / clients / templates
    if ($type !== 'user' && isset($stmt)) {
        $stmt->execute([$id]);
    }
    
       // **one** redirect after restore
        header("Location: trashbin.php?success=" . urlencode(ucfirst($type) . " restored successfully!"));
        exit;
    }

    // ✅ Restore all deleted items
    elseif (isset($_POST['restore_all'])) {
        if ($canViewAllTrash) {
            $pdo->exec("UPDATE invoices SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
            $pdo->exec("UPDATE clients SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
            $pdo->exec("UPDATE email_templates SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
            $pdo->exec("UPDATE users SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
        } else {
            $stmt = $pdo->prepare("UPDATE invoices SET deleted_at = NULL WHERE deleted_at IS NOT NULL AND created_by = ?");
            $stmt->execute([$currentUserId]);
    
            $stmt = $pdo->prepare("UPDATE clients SET deleted_at = NULL WHERE deleted_at IS NOT NULL AND created_by = ?");
            $stmt->execute([$currentUserId]);
    
            $stmt = $pdo->prepare("UPDATE email_templates SET deleted_at = NULL WHERE deleted_at IS NOT NULL AND created_by = ?");
            $stmt->execute([$currentUserId]);
        }
    
        $success = "All deleted items have been restored!";
    }
}

// Fetch deleted invoices (per-user unless canViewAllTrash)
if ($canViewAllTrash) {
    // Super Admin / roles with view_all_trash see everything
    $deletedInvoices = $pdo->query("
        SELECT * 
        FROM invoices 
        WHERE deleted_at IS NOT NULL 
        ORDER BY deleted_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Regular roles: only invoices they own
    $stmt = $pdo->prepare("
        SELECT * 
        FROM invoices 
        WHERE deleted_at IS NOT NULL 
          AND created_by = :uid
        ORDER BY deleted_at DESC
    ");
    $stmt->execute([':uid' => $currentUserId]);
    $deletedInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch deleted clients (per-user unless canViewAllTrash)
if ($canViewAllTrash) {
    $deletedClients = $pdo->query("
        SELECT * 
        FROM clients 
        WHERE deleted_at IS NOT NULL 
        ORDER BY deleted_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM clients 
        WHERE deleted_at IS NOT NULL 
          AND created_by = :uid
        ORDER BY deleted_at DESC
    ");
    $stmt->execute([':uid' => $currentUserId]);
    $deletedClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Combine both types of deleted items
$trashItems = [];

foreach ($deletedInvoices as $invoice) {
    $trashItems[] = [
        'type' => 'invoice',
        'id' => $invoice['id'],
        'item' => $invoice['invoice_number'],
        'amount' => $invoice['total_amount'],
        'original_date' => $invoice['created_at'],
        'status' => $invoice['status'],
        'deleted_at' => $invoice['deleted_at'],
        'client_name' => $invoice['bill_to_name']
    ];
}

foreach ($deletedClients as $client) {
    $trashItems[] = [
        'type' => 'client',
        'id' => $client['id'],
        'item' => $client['company_name'],
        'amount' => null,
        'original_date' => $client['created_at'],
        'status' => null,
        'deleted_at' => $client['deleted_at'],
        'client_name' => $client['company_name']
    ];
}

// Fetch deleted templates (per-user unless canViewAllTrash)
if ($canViewAllTrash) {
    $deletedTemplates = $pdo->query("
        SELECT * 
        FROM email_templates 
        WHERE deleted_at IS NOT NULL 
        ORDER BY deleted_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM email_templates 
        WHERE deleted_at IS NOT NULL 
          AND created_by = :uid
        ORDER BY deleted_at DESC
    ");
    $stmt->execute([':uid' => $currentUserId]);
    $deletedTemplates = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($deletedTemplates as $tpl) {
    $trashItems[] = [
        'type' => 'template',
        'id' => $tpl['id'],
        'item' => $tpl['template_name'],
        'amount' => null,
        'original_date' => $tpl['created_at'],
        'status' => null,
        'deleted_at' => $tpl['deleted_at'],
        'client_name' => null
    ];
}


// Fetch deleted users
if ($canViewAllTrash) {
    $deletedUsers = $pdo->query("
      SELECT 
        users.id, 
        users.username, 
        users.full_name, 
        users.email, 
        users.created_at, 
        users.deleted_at, 
        roles.name AS role_name
      FROM users
      LEFT JOIN roles ON users.role_id = roles.id
      WHERE users.deleted_at IS NOT NULL
      ORDER BY users.deleted_at DESC
    ")->fetchAll();
} else {
    // Regular users should not see deleted user accounts in Trash
    $deletedUsers = [];
}

foreach ($deletedUsers as $user) {
    $trashItems[] = [
        'type'         => 'user',
        'id'           => $user['id'],
        'item'         => $user['username'],    // what shows in the “Item” column
        'amount'       => null,
        'original_date'=> $user['created_at'],
        'status'       => $user['role_name'],   // role for badge
        'deleted_at'   => $user['deleted_at'],
        // new fields for the modal:
        'full_name'    => $user['full_name'],
        'email'        => $user['email'],
        'username'     => $user['username'],
        'role'         => $user['role_name'],
    ];
}

// ✅ Sort all trash items by deleted_at DESC (real deletion time)
usort($trashItems, function ($a, $b) {
    return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
});

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Trash Bin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require 'styles.php'; ?>
  <style>
    /* Matching styles from clients.php */
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
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
    }

    .app-container {
      display: flex;
      min-height: 100vh;
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

    .page-actions {
      display: flex;
      gap: 15px;
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

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
      box-shadow: var(--shadow-hover);
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

    .btn-outline-danger {
      background: transparent;
      border: 1px solid var(--danger);
      color: var(--danger);
    }

    .btn-outline-danger:hover {
      background: var(--danger);
      color: #fff;
      box-shadow: var(--shadow-hover);
    }

    .btn-icon {
      width: 32px;
      height: 32px;
      padding: 0.4rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }
    
    .btn-danger:hover {
      background: rgba(247, 37, 133, 0.2) !important;
      color: #fff !important;
    }
    
    .actions-cell {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-restore {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
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
    }
    
    td {
      text-align: center !important;
      vertical-align: middle;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      position: relative;
      cursor: pointer;
      user-select: none;
      text-align: center !important;
    }

   th[data-sort]::after {
      content: "⇅";
      margin-left: 6px;
      color: #ffffffb3;
      font-size: 0.8rem;
      position: static;
    }

    
    th.asc::after {
      content: "▲";
      color: #ffffff; /* Solid white for contrast */
    }
    
    th.desc::after {
      content: "▼";
      color: #ffffff;
    }


    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    td, th {
      word-wrap: break-word;
      white-space: normal;
      vertical-align: middle;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
    }

    .status-paid {
      background-color: #e6f7ee;
      color: #28a745;
    }

    .status-unpaid {
      background-color: #fdecea;
      color: #dc3545;
    }

    .type-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
    }
    
    .badge-invoice {
      background-color: #f72585;
      color: #fff;
    }
    
    .badge-client {
      background-color: #5D17EB;
      color: #fff;
    }
    
    .badge-template {
      background-color: #20c997;  /* Teal green for email templates */
      color: #fff;
    }


    .search-container {
      position: relative;
      max-width: 300px;
      margin-bottom: 20px;
    }

    .search-container i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-light);
    }

    .search-container input {
      padding-left: 36px;
    }
    
    .trash-icon {
      color: #f72585;
      font-size: 2rem;
      margin-right: 10px;
    }

    .empty-trash {
      text-align: center;
      padding: 2rem;
    }

    .empty-trash i {
      font-size: 4rem;
      color: #adb5bd;
      margin-bottom: 1rem;
    }

        .empty-trash p {
      color: var(--gray);
      font-size: 1.1rem;
    }

    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .page-actions {
        flex-wrap: wrap;
      }
      
      .table-container {
        overflow-x: auto;
      }
    }

    .btn-view {
      background: rgba(67, 97, 238, 0.15);
      color: var(--primary);
    }
    .btn-view:hover {
      background: rgba(67, 97, 238, 0.25);
    }

    .badge-user {
      background-color: #6c757d;
      color: #fff;
    }

        /* 🔁 Pagination footer styles (same look as history.php / clients.php) */
    .table-footer {
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      font-size: 0.85rem;
      color: var(--gray);
      flex-wrap: wrap;
    }

    /* 🔝 Top toolbar version of the footer */
    .table-footer-top {
      margin-top: 0;
      margin-bottom: 0.75rem;
      padding-top: 0;
      padding-bottom: 0.75rem;
      border-top: none;
      border-bottom: 1px solid var(--border);
    }

    .pagination-info {
      flex: 1;
      text-align: left;
      min-width: 220px;
    }

    .pagination-controls {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      white-space: nowrap;
    }

    .rows-per-page {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      margin-right: 0.5rem;
    }

    .rows-per-page label {
      font-size: 0.85rem;
      color: var(--gray);
    }

    .rows-per-page select {
      padding: 0.28rem 0.65rem;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: var(--card-bg);
      color: var(--dark);
      font-size: 0.85rem;
    }

    .pagination-controls .btn-outline {
      padding: 0.25rem 0.7rem;
      font-size: 0.8rem;
      border-radius: 999px;
      line-height: 1.2;
    }

    .pagination-controls .btn-outline[disabled] {
      opacity: 0.55;
      cursor: not-allowed;
      box-shadow: none;
    }

    .page-indicator {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.85rem;
      margin: 0 0.35rem;
    }

    .page-indicator select {
      min-width: 60px;
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: var(--card-bg);
      text-align: center;
      font-size: 0.85rem;
      appearance: none;
      -moz-appearance: none;
      -webkit-appearance: none;
      background-image: none; /* keep it clean like other pills */
    }

  </style>
</head>
<body>
<?php require 'header.php'; ?>

<div class="app-container">
  <?php require 'sidebar.php'; ?>

   <div class="main-content">
    <?php if (!empty($success)): ?>
      <div class="alert alert-success" id="successAlert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
      <script>
        // ✅ Remove success/error params from URL on page load (so message doesn’t repeat)
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('success');
          url.searchParams.delete('error');
          window.history.replaceState({}, document.title, url.toString());
        }
      </script>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title">
        <i class="fas fa-trash trash-icon"></i> Trash Bin
      </h1>
     <?php if (
      has_permission('restore_clients') ||
      has_permission('restore_invoices') ||
      has_permission('manage_email_templates')
    ): ?>
    <div class="page-actions">
        <!-- 🔥 NEW: Perm. Delete All comes FIRST -->
        <form method="POST" 
              onsubmit="return confirm('⚠️ This will permanently delete ALL items in the Trash Bin. This cannot be undone.\n\nAre you absolutely sure?');">
          <input type="hidden" name="perm_delete_all" value="1">
          <button type="submit" class="btn btn-outline-danger">
            <i class="fas fa-trash-alt"></i> Perm. Delete All
          </button>
        </form>

        <!-- Existing Restore All button -->
        <form method="POST" onsubmit="return confirm('Are you sure you want to restore all deleted items?')">
          <input type="hidden" name="restore_all" value="1">
          <button type="submit" class="btn btn-outline">
            <i class="fas fa-undo"></i> Restore All
          </button>
        </form>
      </div>
    <?php endif; ?>
    </div>

      <input type="text" id="trashSearch" placeholder="🔍 Search trash..." class="form-control" style="max-width: 300px; margin-bottom: 1.2rem;">
        <div class="table-container">
        <?php if (count($trashItems) > 0): ?>

          <!-- 🔝 TOP Pagination / Filters Toolbar for Trash -->
          <div class="table-footer table-footer-top" id="trashTableFooterTop">
            <div class="pagination-info" id="trashPaginationInfoTop">
              <!-- Will show: "Showing 1–10 of 241 items (Page 1 of 25)" -->
            </div>

            <div class="pagination-controls">
              <div class="rows-per-page">
                <label for="trashRowsPerPageTop">Rows per page:</label>
                <select id="trashRowsPerPageTop">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </div>

              <button type="button" class="btn btn-outline" id="trashFirstPageTop" title="First page">
                &laquo; First
              </button>

              <button type="button" class="btn btn-outline" id="trashPrevPageTop" title="Previous page">
                &lsaquo; Prev
              </button>

              <span class="page-indicator">
                Page
                <select id="trashPageSelectTop"></select>
                of <span id="trashTotalPagesTop">1</span>
              </span>

              <button type="button" class="btn btn-outline" id="trashNextPageTop" title="Next page">
                Next &rsaquo;
              </button>

              <button type="button" class="btn btn-outline" id="trashLastPageTop" title="Last page">
                Last &raquo;
              </button>
            </div>
          </div>

          <table id="trashTable">
           <thead>
              <tr>
                <th data-sort="number">#</th>
                <th data-sort="type">Type</th>
                <th data-sort="string">Item</th>
                <th data-sort="number">Amount</th>
                <th data-sort="date">Original Date</th>
                <th data-sort="string">Status</th>
                <th data-sort="date">Deleted On</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $counter = 1; foreach ($trashItems as $item): ?>
                <tr>
                  <td data-value="<?= $counter ?>"><?= $counter++ ?></td>
                  <td>
                    <span class="type-badge badge-<?= $item['type'] ?>">
                      <?= ucfirst($item['type']) ?>
                    </span>
                  </td>
                  <td>
                    <?= htmlspecialchars($item['item']) ?>
                    <?php if ($item['type'] === 'invoice'): ?>
                      <div class="text-muted" style="font-size: 0.9rem; color: var(--gray);">
                        Client: <?= htmlspecialchars($item['client_name']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td data-value="<?= $item['type'] === 'invoice' ? floatval($item['amount']) : 0 ?>">
                      <?php if ($item['type'] === 'invoice'): ?>
                        CA$<?= number_format($item['amount'], 2) ?>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                  <td data-value="<?= $item['original_date'] ?>">
                    <?= date('Y-m-d', strtotime($item['original_date'])) ?>
                  </td>
                  <td>
                    <?php if ($item['type'] === 'invoice'): ?>
                      <span class="status-badge <?= strtolower($item['status']) === 'paid' ? 'status-paid' : 'status-unpaid' ?>">
                        <?= htmlspecialchars($item['status']) ?>
                      </span>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td data-value="<?= $item['deleted_at'] ?>">
                    <?= date('Y-m-d', strtotime($item['deleted_at'])) ?>
                  </td>
                  <td class="actions-cell" style="display: flex; justify-content: center; gap: 0.5rem;">
                      <?php if ($item['type'] === 'invoice'): ?>
                          <a class="btn btn-icon btn-view" href="invoices/<?= htmlspecialchars($item['item']) ?>.pdf" target="_blank" title="View Invoice">
                            <i class="fas fa-eye"></i>
                          </a>
                        <?php elseif ($item['type'] === 'user'): ?>
                          <button type="button" class="btn btn-icon btn-view" title="View User"
                              onclick="openUserModal(
                                '<?= htmlspecialchars($item['full_name']) ?>',
                                '<?= htmlspecialchars($item['email'])     ?>',
                                '<?= htmlspecialchars($item['username'])  ?>',
                                '<?= htmlspecialchars($item['role'])      ?>'
                              )">
                              <i class="fas fa-user"></i>
                            </button>
                            <script>
                            function openUserModal(fullName, email, username, role) {
                              const html = `
                                <div style="padding:1rem;">
                                  <h3 style="margin-bottom:1rem;">User Info</h3>
                                  <p><strong>Name:</strong> ${fullName}</p>
                                  <p><strong>Email:</strong> ${email}</p>
                                  <p><strong>Username:</strong> ${username}</p>
                                  <p><strong>Role:</strong> ${role}</p>
                                </div>
                              `;
                              const modal = document.createElement('div');
                              modal.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:#0008;display:flex;align-items:center;justify-content:center;z-index:9999;";
                              modal.innerHTML = `
                                <div style="background:white;border-radius:10px;max-width:400px;width:90%;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,0.3);">
                                  ${html}
                                  <button onclick="this.closest('div').parentNode.remove()" style="margin-top:1.2rem;padding:8px 12px;background:#4361ee;color:#fff;border:none;border-radius:6px;">
                                    Close
                                  </button>
                                </div>
                              `;
                              document.body.appendChild(modal);
                            }
                            </script>
                          </button>
                        <?php endif; ?>
                      <?php
                          $showRestore = true;
                          if ($item['type'] === 'client' && !has_permission('restore_clients')) {
                            $showRestore = false;
                          }
                          if ($item['type'] === 'invoice' && !has_permission('restore_invoices')) {
                            $showRestore = false;
                          }
                          if ($item['type'] === 'template' && !has_permission('manage_email_templates')) {
                            $showRestore = false;
                          }
                          
                          if ($item['type'] === 'user' && !has_permission('restore_deleted_items')) {
                            $showRestore = false;
                          }
                        ?>
                        
                        <?php if ($showRestore): ?>
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="restore_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="type" value="<?= $item['type'] ?>">
                            <button type="submit" class="btn btn-icon btn-restore" title="Restore">
                              <i class="fas fa-undo"></i>
                            </button>
                          </form>
                        <?php endif; ?>

                      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this item? This cannot be undone.')">
                      <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                      <input type="hidden" name="type" value="<?= $item['type'] ?>">
                      <button type="submit" class="btn btn-icon btn-danger" title="Delete Forever" style="background: rgba(247, 37, 133, 0.1); color: #f72585;">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                    </td>
              <?php endforeach; ?>
            </tbody>
           </table>

          <!-- 🔁 Bottom Pagination footer for Trash -->
          <div class="table-footer" id="trashTableFooter">
            <div class="pagination-info" id="trashPaginationInfo">
              <!-- Will show: "Showing 1–10 of 241 items (Page 1 of 25)" -->
            </div>

            <div class="pagination-controls" id="trashPaginationControls">
              <div class="rows-per-page">
                <label for="trashRowsPerPage">Rows per page:</label>
                <select id="trashRowsPerPage">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </div>

              <button type="button" class="btn btn-outline" id="trashFirstPage" title="First page">
                &laquo; First
              </button>

              <button type="button" class="btn btn-outline" id="trashPrevPage" title="Previous page">
                &lsaquo; Prev
              </button>

              <span class="page-indicator">
                Page
                <select id="trashPageSelect"></select>
                of <span id="trashTotalPages">1</span>
              </span>

              <button type="button" class="btn btn-outline" id="trashNextPage" title="Next page">
                Next &rsaquo;
              </button>

              <button type="button" class="btn btn-outline" id="trashLastPage" title="Last page">
                Last &raquo;
              </button>
            </div>
          </div>

        <?php else: ?>
          <div class="empty-trash">
            <i class="fas fa-trash-alt"></i>
            <h3>Trash Bin is Empty</h3>
            <p>No deleted items found</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php require 'scripts.php'; ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
setTimeout(() => {
  const success = document.getElementById('successAlert');
  if (success) success.style.display = 'none';
}, 10000);
</script>

<script>
  (function () {
    const searchInput = document.getElementById('trashSearch');
    const table       = document.getElementById('trashTable');
    const tbody       = table ? table.querySelector('tbody') : null;

    // If there's no table (empty trash), do nothing
    if (!tbody) return;

    // Bottom controls
    const rowsPerPageBottom = document.getElementById('trashRowsPerPage');
    const infoBottom        = document.getElementById('trashPaginationInfo');
    const firstBottom       = document.getElementById('trashFirstPage');
    const prevBottom        = document.getElementById('trashPrevPage');
    const nextBottom        = document.getElementById('trashNextPage');
    const lastBottom        = document.getElementById('trashLastPage');
    const pageSelectBottom  = document.getElementById('trashPageSelect');
    const totalPagesBottom  = document.getElementById('trashTotalPages');

    // Top controls
    const rowsPerPageTop    = document.getElementById('trashRowsPerPageTop');
    const infoTop           = document.getElementById('trashPaginationInfoTop');
    const firstTop          = document.getElementById('trashFirstPageTop');
    const prevTop           = document.getElementById('trashPrevPageTop');
    const nextTop           = document.getElementById('trashNextPageTop');
    const lastTop           = document.getElementById('trashLastPageTop');
    const pageSelectTop     = document.getElementById('trashPageSelectTop');
    const totalPagesTop     = document.getElementById('trashTotalPagesTop');

    let currentPage    = 1;
    let lastTotalPages = 1;

    function getAllRows() {
      return Array.from(tbody.querySelectorAll('tr'));
    }

    function getFilteredRows(allRows) {
      if (!searchInput || !searchInput.value.trim()) return allRows;
      const q = searchInput.value.trim().toLowerCase();
      return allRows.filter(row => row.innerText.toLowerCase().includes(q));
    }

    function getRowsPerPage() {
      if (rowsPerPageBottom) {
        const v = parseInt(rowsPerPageBottom.value, 10);
        if (!isNaN(v) && v > 0) return v;
      }
      if (rowsPerPageTop) {
        const v = parseInt(rowsPerPageTop.value, 10);
        if (!isNaN(v) && v > 0) return v;
      }
      return 10;
    }

    function syncRowsPerPageControls(perPage) {
      if (rowsPerPageBottom && rowsPerPageBottom.value !== String(perPage)) {
        rowsPerPageBottom.value = String(perPage);
      }
      if (rowsPerPageTop && rowsPerPageTop.value !== String(perPage)) {
        rowsPerPageTop.value = String(perPage);
      }
    }

    function updatePageSelectElement(selectEl, totalPages) {
      if (!selectEl) return;

      // Rebuild options only when count differs
      if (selectEl.options.length !== totalPages) {
        selectEl.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
          const opt = document.createElement('option');
          opt.value = String(i);
          opt.textContent = String(i);
          selectEl.appendChild(opt);
        }
      }

      selectEl.value = String(currentPage);
    }

    function updatePageSelects(totalPages) {
      updatePageSelectElement(pageSelectBottom, totalPages);
      updatePageSelectElement(pageSelectTop, totalPages);
    }

    function updateInfo(from, to, totalRows, totalPages) {
      const text = `Showing ${from}–${to} of ${totalRows} items (Page ${currentPage} of ${totalPages})`;
      if (infoBottom) infoBottom.textContent = text;
      if (infoTop)    infoTop.textContent    = text;
    }

    function updateTotalPages(totalPages) {
      if (totalPagesBottom) totalPagesBottom.textContent = String(totalPages);
      if (totalPagesTop)    totalPagesTop.textContent    = String(totalPages);
    }

    function updateNavButtons(totalPages) {
      const disablePrev = currentPage <= 1;
      const disableNext = currentPage >= totalPages;

      if (firstBottom) firstBottom.disabled = disablePrev;
      if (prevBottom)  prevBottom.disabled  = disablePrev;
      if (nextBottom)  nextBottom.disabled  = disableNext;
      if (lastBottom)  lastBottom.disabled  = disableNext;

      if (firstTop) firstTop.disabled = disablePrev;
      if (prevTop)  prevTop.disabled  = disablePrev;
      if (nextTop)  nextTop.disabled  = disableNext;
      if (lastTop)  lastTop.disabled  = disableNext;
    }

    function renderPage(page) {
      const perPage  = getRowsPerPage();
      const allRows  = getAllRows();
      const filtered = getFilteredRows(allRows);
      const totalRows = filtered.length;
      const totalPages = Math.max(1, Math.ceil(totalRows / perPage));

      lastTotalPages = totalPages;

      if (page < 1) page = 1;
      if (page > totalPages) page = totalPages;
      currentPage = page;

      // Hide all rows
      allRows.forEach(row => {
        row.style.display = 'none';
      });

      // Show only current slice
      const start = (currentPage - 1) * perPage;
      const end   = start + perPage;
      filtered.slice(start, end).forEach(row => {
        row.style.display = '';
      });

      const from = totalRows === 0 ? 0 : start + 1;
      const to   = Math.min(end, totalRows);

      updateInfo(from, to, totalRows, totalPages);
      updateTotalPages(totalPages);
      updatePageSelects(totalPages);
      syncRowsPerPageControls(perPage);
      updateNavButtons(totalPages);
    }

    // Expose if you ever want to re-render after sorting in future
    window.renderTrashPage = renderPage;

    // ── Event bindings ──

    // Rows per page – bottom
    if (rowsPerPageBottom) {
      rowsPerPageBottom.addEventListener('change', () => {
        const perPage = parseInt(rowsPerPageBottom.value, 10) || 10;
        syncRowsPerPageControls(perPage);
        renderPage(1);
      });
    }

    // Rows per page – top
    if (rowsPerPageTop) {
      rowsPerPageTop.addEventListener('change', () => {
        const perPage = parseInt(rowsPerPageTop.value, 10) || 10;
        syncRowsPerPageControls(perPage);
        renderPage(1);
      });
    }

    // Search – integrate with pagination
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        renderPage(1);
      });
    }

    // Bottom nav buttons
    if (firstBottom) firstBottom.addEventListener('click', () => renderPage(1));
    if (prevBottom)  prevBottom.addEventListener('click', () => renderPage(currentPage - 1));
    if (nextBottom)  nextBottom.addEventListener('click', () => renderPage(currentPage + 1));
    if (lastBottom)  lastBottom.addEventListener('click', () => renderPage(lastTotalPages));

    // Top nav buttons
    if (firstTop) firstTop.addEventListener('click', () => renderPage(1));
    if (prevTop)  prevTop.addEventListener('click', () => renderPage(currentPage - 1));
    if (nextTop)  nextTop.addEventListener('click', () => renderPage(currentPage + 1));
    if (lastTop)  lastTop.addEventListener('click', () => renderPage(lastTotalPages));

    // Page selects
    function bindPageSelect(selectEl) {
      if (!selectEl) return;
      selectEl.addEventListener('change', () => {
        const val = parseInt(selectEl.value, 10);
        if (isNaN(val)) return;
        renderPage(val);
      });
    }

    bindPageSelect(pageSelectBottom);
    bindPageSelect(pageSelectTop);

    // Initial render
    const allRows = getAllRows();
    if (allRows.length) {
      const initialPerPage = getRowsPerPage();
      syncRowsPerPageControls(initialPerPage);
      renderPage(1);
    }
  })();
</script>

<script>
function openUserModal(fullName, email, username, role) {
  const html = `
    <div style="padding:1rem;">
      <h3 style="margin-bottom:1rem;">User Info</h3>
      <p><strong>Name:</strong> ${fullName}</p>
      <p><strong>Email:</strong> ${email}</p>
      <p><strong>Username:</strong> ${username}</p>
      <p><strong>Role:</strong> ${role}</p>
    </div>
  `;
  const modal = document.createElement('div');
  modal.style = "position:fixed;top:0;left:0;width:100%;height:100%;background:#0008;display:flex;align-items:center;justify-content:center;z-index:9999;";
  modal.innerHTML = `
    <div style="background:white;border-radius:10px;max-width:400px;width:90%;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,0.3);">
      ${html}
      <button onclick="this.closest('div').parentNode.remove()" style="margin-top:1.2rem;padding:8px 12px;background:#4361ee;color:#fff;border:none;border-radius:6px;">
        Close
      </button>
    </div>
  `;
  document.body.appendChild(modal);
}
</script>

</body>
</html>