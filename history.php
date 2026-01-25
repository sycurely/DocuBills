<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$activeMenu = 'history';
require_once 'config.php';
require_once 'middleware.php'; // ✅ Load permission functions

// ✅ Check permission to access Invoice History page at all
if (!has_permission('view_invoice_history')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}

// 🔐 NEW: who can see ALL invoices vs only their own
$canViewInvoiceLogs = has_permission('view_invoice_logs'); // this permission sees ALL users' invoices
$currentUserId      = $_SESSION['user_id'];                // current logged in user
$canManageRecurring = has_permission('manage_recurring_invoices'); // or create_recurring_invoice

ob_start(); // 👈 Add this BEFORE any output
require 'styles.php';
require 'header.php';

// ✅ Default currency from Basic Settings (fallback for older invoices)
$defaultCurrencyCode = strtoupper(trim((string) get_setting('currency_code')));
if ($defaultCurrencyCode === '') {
    $defaultCurrencyCode = 'CAD';
}

if ($defaultCurrencyCode === '') {
    $defaultCurrencyCode = 'CAD'; // safe fallback
}
if ($defaultCurrencyDisplay === '') {
    $defaultCurrencyDisplay = $defaultCurrencyCode; // fallback to code if display not set
}

$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : null;
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
$currentUserId = $_SESSION['user_id'];
$canViewInvoiceLogs = has_permission('view_invoice_logs');

// 👇 NEW: 0-based index of the Status column in each table row
// (#, Invoice, Amount, Date, Client, [User], [Recurring], Status, Action)
if ($canViewInvoiceLogs && $canManageRecurring) {
    // #, Invoice, Amount, Date, Client, User, Recurring, Status, Action
    $statusColIndex = 7;
} elseif ($canViewInvoiceLogs && !$canManageRecurring) {
    // #, Invoice, Amount, Date, Client, User, Status, Action
    $statusColIndex = 6;
} elseif (!$canViewInvoiceLogs && $canManageRecurring) {
    // #, Invoice, Amount, Date, Client, Recurring, Status, Action
    $statusColIndex = 6;
} else {
    // #, Invoice, Amount, Date, Client, Status, Action
    $statusColIndex = 5;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['undo_recent'])) {
        $stmt = $pdo->prepare("UPDATE invoices SET deleted_at = NULL WHERE id = (
            SELECT id FROM invoices WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 1
        )");
        $stmt->execute();
        header("Location: history.php?success=" . urlencode("Most recent invoice deletion has been undone!"));
            exit;
        }

    if (isset($_POST['undo_all'])) {
        $stmt = $pdo->prepare("UPDATE invoices SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
        $stmt->execute();
        header("Location: history.php?success=" . urlencode("All deleted invoices have been restored!"));
            exit;
        }
        
        if (isset($_POST['delete_all']) && has_permission('delete_invoice')) {
        try {
            // Soft-delete ALL active invoices (move to Trash Bin)
            $stmt = $pdo->prepare("UPDATE invoices SET deleted_at = NOW() WHERE deleted_at IS NULL");
            $stmt->execute();

            header("Location: history.php?success=" . urlencode("All invoices have been moved to Trash Bin!"));
            exit;
        } catch (Exception $e) {
            header("Location: history.php?error=" . urlencode("Failed to delete all invoices: " . $e->getMessage()));
            exit;
        }
    }
    
    if (isset($_POST['delete_id']) && has_permission('delete_invoice')) {
    try {
        error_log("Delete request received for ID: " . $_POST['delete_id']);
        $stmt = $pdo->prepare("UPDATE invoices SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        if ($stmt->rowCount()) {
            error_log("Invoice ID {$_POST['delete_id']} marked as deleted.");
            header("Location: history.php?success=" . urlencode("Invoice deleted successfully!"));
            exit;
        } else {
            header("Location: history.php?error=" . urlencode("Failed to delete invoice."));
            exit;
        }
    } catch (Exception $e) {
        header("Location: history.php?error=" . urlencode($e->getMessage()));
        exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice History</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    .btn-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    .btn-edit {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .btn-download {
      background: rgba(247, 37, 133, 0.2);
      color: var(--danger);
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

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      font-size: 1rem;
      text-align: center !important;
    }

    td {
      text-align: center;
      vertical-align: middle;
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


    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    .actions-cell {
      display: flex !important;
      justify-content: center !important;
      align-items: center;
      gap: 0.5rem;
      min-width: 120px; /* Optional: prevents column collapse */
      position: relative; /* Add this */
    }
    
        /* Inline action icons in Actions column */
    .action-btn {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: none;
      cursor: pointer;
      font-size: 0.85rem;
      transition: var(--transition);
      text-decoration: none;
    }

    .action-btn.view {
      background: rgba(76, 201, 240, 0.15);
      color: var(--primary);
    }

    .action-btn.download {
      background: rgba(247, 37, 133, 0.12);
      color: var(--danger);
    }

    .action-btn.payment {
      background: rgba(72, 149, 239, 0.12);
      color: var(--secondary);
    }

    .action-btn.delete {
      background: rgba(247, 37, 133, 0.18);
      color: var(--danger);
    }

    .action-btn:hover {
      transform: translateY(-1px);
      box-shadow: var(--shadow-hover);
    }

    .dropdown {
      position: relative;
      display: inline-block;
    }
    
    .dropdown-toggle {
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      background: transparent;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
      color: #333;
      padding: 4px 8px;
      border-radius: 50%;
      transition: background 0.2s;
    }
    
    .dropdown-toggle:hover {
      background: rgba(67, 97, 238, 0.1);
    }
    
    .dropdown-menu {
      z-index: 9999; /* Add this line or increase it if already there */
    }

    .dropdown-menu {
      position: fixed !important;
      max-width: 90vw;
      box-sizing: border-box;
      z-index: 9999;
     /* top: auto !important;
      bottom: auto !important;
      left: auto !important;
      right: auto !important;*/
      display: none;
      background-color: white;
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      border-radius: 8px;
      min-width: 160px;
      overflow: hidden;
      opacity: 0;
      transform: translateY(-5px);
      transition: all 0.2s ease;
      transition: opacity 0.2s ease, transform 0.2s ease;
      overflow-y: auto;
      transform-origin: top right;
    }
    
    .dropdown-menu a,
    .dropdown-menu button {
      display: block;
      width: 100%;
      text-align: left;
      padding: 10px 16px;
      font-size: 14px;
      background: white;
      color: #333;
      border: none;
      cursor: pointer;
      text-decoration: none;
    }
    
    .dropdown-menu a:hover,
    .dropdown-menu button:hover {
      background-color: #eef1f9;
      box-shadow: inset 0 0 5px rgba(67, 97, 238, 0.1);
    }

    .dropdown-menu.show {
      display: block;
      opacity: 1;
      transform: scale(1) !important;
      animation: dropdown-open 0.2s ease-out;
    }
    
    @keyframes dropdown-open {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    
    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
    }

    .status-dropdown {
      appearance: none;
      background-color: var(--card-bg);
      color: var(--primary);
      border: 1px solid var(--primary-light);
      padding: 6px 14px;
      font-weight: 600;
      font-size: 0.85rem;
      border-radius: 50px;
      cursor: pointer;
      transition: var(--transition);
      box-shadow: var(--shadow);
      text-align: center;
      min-width: 100px;
    }
    
    .status-dropdown.paid {
      background-color: #e6f7ee;
      color: #28a745;
      border: 1px solid #28a745;
    }
    
    .status-dropdown.unpaid {
      background-color: #fdecea;
      color: #f72585;
      border: 1px solid #f72585;
    }

    
    .status-dropdown:hover {
      background-color: var(--primary-light);
      color: white;
    }
    
    .status-dropdown:focus {
      outline: none;
      box-shadow: 0 0 0 2px var(--primary-light);
    }
    
    .status-dropdown option {
      background-color: var(--light);
      color: var(--dark);
    }

    .status-paid {
      background-color: #e6f7ee;
      color: #28a745;
    }

    .status-unpaid {
      background-color: #fdecea;
      color: #dc3545;
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

    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .page-actions {
        flex-wrap: wrap;
      }
    }
    
    /* Match clients.php table style */
    #historyTable thead tr th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      font-size: 1rem;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      text-align: center;
    }
    
    #historyTable tbody td {
      text-align: center;
      vertical-align: middle;
    }
    
    /* Sorting indicator styles */
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
      transform: translateY(-1px); /* slight vertical alignment */
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

    .pagination-info {
      font-size: 0.9rem;
      color: var(--gray);
    }

    /* History table pagination */
    .pagination-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 1rem 0;
      flex-wrap: wrap;
      gap: 0.75rem;
    }
    
    #historyTable {
      margin-top: 1rem !important;
    }
    
    .pagination-info {
      font-size: 0.9rem;
      color: var(--gray);
    }
    
    .pagination-buttons {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .btn-sm {
      padding: 0.35rem 0.9rem;
      font-size: 0.8rem;
      border-radius: 999px;
    }
    
    .pagination-controls {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .rows-per-page {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      font-size: 0.85rem;
      color: var(--gray);
    }

    .rows-per-page select,
    .page-select {
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      border: 1px solid var(--border);
      font-size: 0.85rem;
      background: #fff;
      cursor: pointer;
    }

    /* Match clients.php pagination look */
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

    .table-footer-top {
      margin-top: 0;
      padding-top: 0;
      padding-bottom: 0.75rem;
      border-top: none;
      border-bottom: 1px solid var(--border);
    }

    .table-footer .pagination-info {
      flex: 1;
      text-align: left;
      min-width: 220px;
    }

    .table-footer .pagination-controls {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      white-space: nowrap;
    }

    .table-footer .rows-per-page {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      margin-right: 0.5rem;
    }

    .table-footer .rows-per-page select {
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: var(--card-bg);
      font-size: 0.85rem;
    }

    .table-footer .pagination-controls .btn-outline {
      padding: 0.25rem 0.7rem;
      font-size: 0.8rem;
      border-radius: 999px;
      line-height: 1.2;
    }

    .table-footer .pagination-controls .btn-outline[disabled] {
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
      color: var(--dark);
      font-size: 0.85rem;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }
    
    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      width: 100%;
      max-width: 500px;
      text-align: center;
      position: relative;
    }
    
    .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
    }
    
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .btn-danger {
      background: #f72585;
      color: white;
    }
    .btn-cancel {
      background: #adb5bd;
      color: white;
    }

    /* Flip dropdown upward if dropup class is added */
    .dropdown.dropup .dropdown-menu {
        bottom: 100%;
        top: auto !important;
        margin-bottom: 8px;
    }
    
    /* Recurring column styles */
    .recurring-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: flex-start;
      gap: 0.4rem;
      padding: 0.25rem 0.8rem;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: #f8f9ff;
      color: var(--secondary);
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      box-shadow: var(--shadow);
      transition: var(--transition);
    }
    .recurring-toggle.off {
      background: #f8f9fa;
      color: var(--gray);
      border-color: var(--border);
    }
    .recurring-toggle.on {
      background: rgba(67, 97, 238, 0.12);
      color: var(--primary);
      border-color: var(--primary-light);
    }
    .recurring-toggle .toggle-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--gray);
    }
    .recurring-toggle.on .toggle-dot {
      background: #2ecc71;
    }
    .recurring-toggle.off .toggle-dot {
      background: #bbb;
    }
    .recurring-next {
      margin-top: 0.25rem;
      font-size: 0.7rem;
      color: var(--gray);
    }
    .recurring-label {
      font-size: 0.8rem;
      color: var(--gray);
    }

  </style>
</head>
<body>
<div class="app-container">
  <?php require 'sidebar.php'; ?>

  <div class="main-content">
    <?php if ($success): ?>
      <div class="alert alert-success" id="successAlert">
        <i class="fas fa-check-circle"></i> <?= $success ?>
      </div>
      <script>
        // ✅ Remove ?success=... from the URL without reloading
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('success');
          url.searchParams.delete('error');
          window.history.replaceState({}, document.title, url.pathname);
        }
      </script>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title">Invoice History</h1>
        <div class="page-actions">
        <?php if (has_permission('restore_invoices')): ?>
          <button class="btn btn-outline" id="undoRecentBtn">
            <i class="fas fa-undo"></i> Undo Recent Delete
          </button>
        <?php endif; ?>

        <?php if (has_permission('delete_invoice')): ?>
          <button class="btn btn-outline" id="deleteAllBtn">
            <i class="fas fa-trash-alt"></i> Delete All
          </button>
        <?php endif; ?>
            
        <?php if (has_permission('restore_invoices')): ?>
          <button class="btn btn-outline" id="undoAllBtn">
            <i class="fas fa-history"></i> Undo All Deletes
          </button>
        <?php endif; ?>
            
        <?php if (has_permission('download_invoice_pdf')): ?>
          <button class="btn btn-primary" id="exportInvoiceBtn">
            <i class="fas fa-file-export"></i> Export to Excel
          </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-container">
      <input type="text" id="historySearch" placeholder="🔍 Search invoices..." class="form-control" style="max-width: 300px; margin-bottom: 1.2rem;">
                <!-- Top pagination controls (match clients.php) -->
          <div class="table-footer table-footer-top">
            <div class="pagination-info" id="paginationInfoTop"></div>

            <div class="pagination-controls">
              <div class="rows-per-page">
                <label for="rowsPerPageSelectTop">Rows per page:</label>
                <select id="rowsPerPageSelectTop">
                  <option value="10" selected>10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                  <option value="all">All</option>
                </select>
              </div>

              <button type="button"
                      class="btn btn-outline"
                      id="firstPageTop"
                      title="First page">
                &laquo; First
              </button>

              <button type="button"
                      class="btn btn-outline"
                      id="prevPageTop"
                      title="Previous page">
                &lsaquo; Prev
              </button>

              <span class="page-indicator">
                Page
                <select id="pageSelectTop"
                        class="page-select"
                        title="Go to page">
                </select>
                of <span id="totalPagesTop">1</span>
              </span>

              <button type="button"
                      class="btn btn-outline"
                      id="nextPageTop"
                      title="Next page">
                Next &rsaquo;
              </button>

              <button type="button"
                      class="btn btn-outline"
                      id="lastPageTop"
                      title="Last page">
                Last &raquo;
              </button>
            </div>
          </div>
        <table id="historyTable">
            <thead>
              <tr>
                <th data-sort="number" style="text-align: center;"><span class="header-text">#</span><span class="sort-indicator"></span></th>
                <th data-sort="string" style="text-align: center;">Invoice # <span class="sort-indicator"></span></th>
                <th data-sort="string" style="text-align: center;">Amount <span class="sort-indicator"></span></th>
                <th data-sort="date" style="text-align: center;">Date <span class="sort-indicator"></span></th>
                <th data-sort="string" style="text-align: center;">Client <span class="sort-indicator"></span></th>
                
                <?php if ($canViewInvoiceLogs): ?>
                  <th data-sort="string" style="text-align: center;">User <span class="sort-indicator"></span></th>
                <?php endif; ?>
                
                <?php if ($canManageRecurring): ?>
                  <th data-sort="string" style="text-align: center;">Recurring <span class="sort-indicator"></span></th>
                <?php endif; ?>
                
                <th data-sort="string" style="text-align: center;">Status <span class="sort-indicator"></span></th>
                <th style="text-align: center;">Action</th>
              </tr>
            </thead>
          <tbody>
            <?php
                // ✅ Decide which invoices to show based on permission
                if ($canViewInvoiceLogs) {
                    // Show ALL invoices + creator username
                    $stmt = $pdo->query("
                        SELECT i.*, u.username AS created_by_username
                        FROM invoices i
                        LEFT JOIN users u ON i.created_by = u.id
                        WHERE i.deleted_at IS NULL
                        ORDER BY i.id DESC
                    ");
                } else {
                    // Show ONLY invoices created by the logged-in user
                    $stmt = $pdo->prepare("
                        SELECT i.*, u.username AS created_by_username
                        FROM invoices i
                        LEFT JOIN users u ON i.created_by = u.id
                        WHERE i.deleted_at IS NULL
                          AND i.created_by = ?
                        ORDER BY i.id DESC
                    ");
                    $stmt->execute([$currentUserId]);
                }
                
                $counter = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    $status = trim($row['status']);
                ?>
              <tr>
                <td data-value="<?= $counter ?>"><?= $counter++ ?></td>
                <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                <?php
                  // ✅ Per-invoice currency (fallbacks: invoice code → basic settings)
                  // ✅ Always display ISO-like code
                  $cur = strtoupper(trim((string)($row['currency_display'] ?? '')));
                
                  // If currency_display is not ISO-ish, ignore it
                  if ($cur === '' || $cur === '?' || !preg_match('/^[A-Z0-9]{3,10}$/', $cur)) {
                      $cur = strtoupper(trim((string)($row['currency_code'] ?? '')));
                  }
                
                  // Final fallback
                  if ($cur === '' || $cur === '?') {
                      $cur = $defaultCurrencyCode;
                  }
                ?>
                <td><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['total_amount'], 2) ?></td>

                <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                <td><?= htmlspecialchars($row['bill_to_name']) ?></td>
                
                <?php if ($canViewInvoiceLogs): ?>
                  <td><?= htmlspecialchars($row['created_by_username'] ?? '—') ?></td>
                <?php endif; ?>

                <?php
                  $isRecurring = !empty($row['is_recurring']);
                  $recType     = $row['recurrence_type'] ?: 'monthly';
                  $nextRun     = $row['next_run_date'] ?: '';
                ?>
                
                <?php if ($canManageRecurring): ?>
                  <td>
                    <button type="button"
                            class="recurring-toggle <?= $isRecurring ? 'on' : 'off' ?>"
                            data-id="<?= $row['id'] ?>"
                            data-current="<?= $isRecurring ? '1' : '0' ?>">
                      <span class="toggle-dot"></span>
                      <span class="toggle-label">
                        <?= $isRecurring ? htmlspecialchars(ucfirst($recType)) : 'Off' ?>
                      </span>
                    </button>
                    <?php if ($isRecurring && $nextRun): ?>
                      <div class="recurring-next">
                        Next: <?= htmlspecialchars($nextRun) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
                
                <td>
                  <?php if (has_permission('mark_invoice_paid')): ?>
                      <select class="form-select form-select-sm status-dropdown <?= strtolower($status) ?>"
                              data-id="<?= $row['id'] ?>"
                              data-original="<?= $status ?>"
                              
                              <?php if ($status === 'Paid' && $row['payment_method']): ?>
                                  title="Method: <?= htmlspecialchars($row['payment_method']) ?><?= $row['payment_proof'] ? ' | Proof uploaded' : '' ?>"
                              <?php endif; ?>>
                        <option value="Unpaid" <?= $status === 'Unpaid' ? 'selected' : '' ?>>✖ Unpaid</option>
                        <option value="Paid" <?= $status === 'Paid' ? 'selected' : '' ?>>✔ Paid</option>
                      </select>
                    <?php else: ?>
                      <span class="status-badge <?= strtolower($status) ?>" style="
                        <?php if (strtolower($status) === 'paid'): ?>
                          background-color: #e6f7ee; color: #28a745; border: 1px solid #28a745;
                        <?php elseif (strtolower($status) === 'unpaid'): ?>
                          background-color: #fdecea; color: #f72585; border: 1px solid #f72585;
                        <?php endif; ?>
                        padding: 6px 12px; border-radius: 30px; font-weight: bold;">
                        <?= htmlspecialchars($status) ?>
                      </span>
                    <?php endif; ?>
                </td>
                  <td class="actions-cell">
                  <?php $ts = time(); ?>
                    <?php $invoiceNum = rawurlencode($row['invoice_number']); ?>
                    <?php $viewPdfUrl = "view_pdf.php?invoice=" . $invoiceNum . "&t=" . $ts; ?>
                    <?php $downloadPdfUrl = "view_pdf.php?invoice=" . $invoiceNum . "&t=" . $ts . "&download=1"; ?>
                    
                    <?php if (has_permission('view_invoices')): ?>
                      <a href="<?= $viewPdfUrl ?>"
                         target="_blank"
                         class="action-btn view"
                         title="View Invoice">
                        <i class="fas fa-eye"></i>
                      </a>
                    <?php endif; ?>
                    
                    <?php if (has_permission('download_invoice_pdf')): ?>
                      <a href="<?= $downloadPdfUrl ?>"
                         class="action-btn download"
                         title="Download PDF">
                        <i class="fas fa-download"></i>
                      </a>
                    <?php endif; ?>

                  <?php if ($status === 'Paid' && has_permission('view_invoice_payment_info')): ?>
                    <button type="button"
                            class="action-btn payment view-payment-btn"
                            data-method="<?= htmlspecialchars($row['payment_method']) ?>"
                            data-proof="<?= htmlspecialchars($row['payment_proof']) ?>"
                            data-invoice="<?= htmlspecialchars($row['invoice_number']) ?>"
                            title="View Payment Info">
                      <i class="fas fa-info-circle"></i>
                    </button>
                  <?php endif; ?>

                  <?php if (has_permission('delete_invoice')): ?>
                    <button type="button"
                            class="action-btn delete delete-btn"
                            data-id="<?= $row['id'] ?>"
                            data-invoice="<?= htmlspecialchars($row['invoice_number']) ?>"
                            title="Delete Invoice">
                      <i class="fas fa-trash"></i>
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
                </table>

               <!-- Enhanced pagination controls (match clients.php) -->
        <div class="table-footer" id="historyTableFooter">
          <!-- Left side: info text -->
          <div class="pagination-info" id="paginationInfo">
            <!-- Filled by JS -->
          </div>

          <!-- Right side: rows per page + controls -->
          <div class="pagination-controls">
            <div class="rows-per-page">
              <label for="rowsPerPageSelect">Rows per page:</label>
              <select id="rowsPerPageSelect">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
              </select>
            </div>

            <button type="button"
                    class="btn btn-outline"
                    id="firstPage"
                    title="First page">
              &laquo; First
            </button>

            <button type="button"
                    class="btn btn-outline"
                    id="prevPage"
                    title="Previous page">
              &lsaquo; Prev
            </button>

            <span class="page-indicator">
              Page
              <select id="pageSelect"
                      class="page-select"
                      title="Go to page">
              </select>
              of <span id="totalPages">1</span>
            </span>

            <button type="button"
                    class="btn btn-outline"
                    id="nextPage"
                    title="Next page">
              Next &rsaquo;
            </button>

            <button type="button"
                    class="btn btn-outline"
                    id="lastPage"
                    title="Last page">
              Last &raquo;
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require 'scripts.php'; ?>

<script>
  // JS flags for current table layout
  const HAS_VIEW_INVOICE_LOGS = <?= $canViewInvoiceLogs ? 'true' : 'false' ?>;
  const HAS_RECURRING_COLUMN  = <?= $canManageRecurring ? 'true' : 'false' ?>;
  // 0-based index of the Status column (<td>) in each row
  const STATUS_COL_INDEX      = <?= (int)$statusColIndex ?>;
</script>

<script>
  const exportBtn = document.getElementById('exportInvoiceBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      const rows = document.querySelectorAll('#historyTable tbody tr');

      // Header row (skip Action column)
      const headers = Array.from(document.querySelectorAll('#historyTable thead th'))
        .map(th => th.innerText.trim())
        .slice(0, -1);

      // ✅ Fix Excel currency symbols (€, £, ₹, ₨ etc.) by forcing UTF-8 with BOM
        let csvContent = "\uFEFF" + headers.join(",") + "\n";

      rows.forEach(row => {
        // Skip Action column in data as well
        const cols = Array.from(row.querySelectorAll('td')).slice(0, -1);

        const rowData = cols.map((td, i) => {
          // STATUS_COL_INDEX already accounts for User + Recurring visibility
          const statusColIndex = STATUS_COL_INDEX;
        
          if (i === statusColIndex) {
            const select = td.querySelector('select.status-dropdown');
            const val = select ? select.value : td.innerText.trim();
            return '"' + val.replace(/"/g, '""') + '"';
          }
        
          const text = td.innerText.trim();
          return '"' + text.replace(/"/g, '""') + '"';
        }).join(",");

        csvContent += rowData + "\n";
      });

      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.setAttribute("href", url);
      link.setAttribute("download", "invoice_history.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  }
</script>

<script>
const historySearchInput = document.getElementById('historySearch');
if (historySearchInput) {
  historySearchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#historyTable tbody tr');

    // Status column index is calculated in PHP & exposed as STATUS_COL_INDEX
    const statusColIndex   = STATUS_COL_INDEX;
    const lastDataColIndex = STATUS_COL_INDEX; // last non-Action column

    rows.forEach(row => {
      let match = false;

      // If search box is empty, show everything
      if (!query) {
        match = true;
      } else {
        for (let i = 0; i <= lastDataColIndex; i++) {
          const cell = row.cells[i];
          if (!cell) continue;

          let cellText = cell.innerText
            .normalize('NFKD')
            .replace(/\s+/g, ' ')
            .replace(/[^\x20-\x7E]/g, '')
            .toLowerCase()
            .trim();

          if (i === statusColIndex) {
            // Prefer dropdown value ("Paid"/"Unpaid") if user can change status
            const select = cell.querySelector('.status-dropdown');
            if (select) {
              cellText = select.value.toLowerCase(); // "paid" or "unpaid"
            }

            if (query === 'paid' || query === 'unpaid') {
              // Exact match so "paid" doesn't match "unpaid"
              match = (cellText === query);
            } else {
              match = cellText.includes(query);
            }
          } else {
            if (cellText.includes(query)) {
              match = true;
            }
          }

          if (match) break;
        }
      }

      // Mark row as matching / not matching for pagination
      row.dataset.matchesSearch = match ? '1' : '0';
    });

    // Re-render pagination from first page after search
    if (typeof applyPagination === 'function') {
      applyPagination(true);
    }
  });
}
</script>

<script>
  // Enhanced client-side pagination for history table (top + bottom controls)
  let rowsPerPage = 10;   // default
  let currentPage = 1;
  let totalPages = 1;

  function getMatchingRows() {
    const allRows = Array.from(document.querySelectorAll('#historyTable tbody tr'));
    return allRows.filter(row => row.dataset.matchesSearch !== '0');
  }

  function applyPagination(resetToFirst = false) {
    const allRows = Array.from(document.querySelectorAll('#historyTable tbody tr'));
    if (!allRows.length) return;

    if (resetToFirst) {
      currentPage = 1;
    }

    const visibleRows = getMatchingRows();
    const totalRows = visibleRows.length;

    // Determine total pages
    if (rowsPerPage <= 0 || totalRows === 0) {
      totalPages = 1;
      currentPage = 1;
    } else {
      totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));
      if (currentPage > totalPages) {
        currentPage = totalPages;
      }
    }

    // Hide all rows first
    allRows.forEach(row => {
      row.style.display = 'none';
    });

    // Determine slice of rows to show
    let start = 0;
    let end = visibleRows.length;

    if (rowsPerPage > 0 && totalRows > 0) {
      start = (currentPage - 1) * rowsPerPage;
      end = start + rowsPerPage;
    }

    visibleRows.forEach((row, index) => {
      if (totalRows === 0) return;
      if (index >= start && index < end) {
        row.style.display = '';
      }
    });

    // Grab both top & bottom UI elements
    const infoEls      = document.querySelectorAll('#paginationInfo, #paginationInfoTop');
    const prevBtns     = document.querySelectorAll('#prevPage, #prevPageTop');
    const nextBtns     = document.querySelectorAll('#nextPage, #nextPageTop');
    const firstBtns    = document.querySelectorAll('#firstPage, #firstPageTop');
    const lastBtns     = document.querySelectorAll('#lastPage, #lastPageTop');
    const pageSelects  = document.querySelectorAll('#pageSelect, #pageSelectTop');
    const rowsSelects  = document.querySelectorAll('#rowsPerPageSelect, #rowsPerPageSelectTop');

    // Info text
    infoEls.forEach(infoEl => {
      if (totalRows === 0) {
        infoEl.textContent = 'No invoices found';
      } else {
        let startItem, endItem;
        if (rowsPerPage <= 0) {
          startItem = 1;
          endItem   = totalRows;
        } else {
          startItem = start + 1;
          endItem   = Math.min(end, totalRows);
        }
        infoEl.textContent =
          `Showing ${startItem}–${endItem} of ${totalRows} invoices (Page ${currentPage} of ${totalPages})`;
      }
    });

    // Enable/disable nav buttons
    const disableNav = totalRows === 0 || totalPages <= 1;

    prevBtns.forEach(btn => btn.disabled  = disableNav || currentPage === 1);
    nextBtns.forEach(btn => btn.disabled  = disableNav || currentPage === totalPages);
    firstBtns.forEach(btn => btn.disabled = disableNav || currentPage === 1);
    lastBtns.forEach(btn => btn.disabled  = disableNav || currentPage === totalPages);

    // Populate page dropdowns (top & bottom)
    pageSelects.forEach(select => {
      select.innerHTML = '';
      for (let i = 1; i <= totalPages; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Page ${i}`;
        if (i === currentPage) opt.selected = true;
        select.appendChild(opt);
      }
      select.disabled = disableNav;
    });

   // Keep rows-per-page selects in sync
    rowsSelects.forEach(sel => {
      if (rowsPerPage <= 0) {
        sel.value = 'all';
      } else {
        sel.value = String(rowsPerPage);
      }
    });

    // NEW: update "Page X of Y" spans (top & bottom)
    const totalPagesSpans = document.querySelectorAll('#totalPages, #totalPagesTop');
    totalPagesSpans.forEach(span => {
      span.textContent = totalPages;
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const allRows = document.querySelectorAll('#historyTable tbody tr');

    // Default: all rows match search initially
    allRows.forEach(row => {
      if (!row.dataset.matchesSearch) {
        row.dataset.matchesSearch = '1';
      }
    });

    const prevBtns    = document.querySelectorAll('#prevPage, #prevPageTop');
    const nextBtns    = document.querySelectorAll('#nextPage, #nextPageTop');
    const firstBtns   = document.querySelectorAll('#firstPage, #firstPageTop');
    const lastBtns    = document.querySelectorAll('#lastPage, #lastPageTop');
    const rowsSelects = document.querySelectorAll('#rowsPerPageSelect, #rowsPerPageSelectTop');
    const pageSelects = document.querySelectorAll('#pageSelect, #pageSelectTop');

    prevBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        if (currentPage > 1) {
          currentPage--;
          applyPagination();
        }
      });
    });

    nextBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        if (currentPage < totalPages) {
          currentPage++;
          applyPagination();
        }
      });
    });

    firstBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        if (currentPage !== 1) {
          currentPage = 1;
          applyPagination();
        }
      });
    });

    lastBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        if (currentPage !== totalPages) {
          currentPage = totalPages;
          applyPagination();
        }
      });
    });

    rowsSelects.forEach(sel => {
      sel.addEventListener('change', function () {
        const val = this.value;
        if (val === 'all') {
          rowsPerPage = 0; // show all
        } else {
          const parsed = parseInt(val, 10);
          rowsPerPage = (!isNaN(parsed) && parsed > 0) ? parsed : 10;
        }
        currentPage = 1;
        applyPagination(true);
      });
    });

    pageSelects.forEach(sel => {
      sel.addEventListener('change', function () {
        const selected = parseInt(this.value, 10);
        if (!isNaN(selected)) {
          currentPage = selected;
          applyPagination();
        }
      });
    });

    // Initial render
    applyPagination(true);
  });
</script>

<script>
  const undoRecentBtn = document.getElementById('undoRecentBtn');
  if (undoRecentBtn) {
    undoRecentBtn.addEventListener('click', function () {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = '<input type="hidden" name="undo_recent" value="1">';
      document.body.appendChild(form);
      form.submit();
    });
  }

  const deleteAllBtn = document.getElementById('deleteAllBtn');
  if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', function () {
      if (!confirm('Are you sure you want to move ALL invoices to Trash Bin? This can be undone from the Trash Bin using restore options.')) {
        return;
      }
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = '<input type="hidden" name="delete_all" value="1">';
      document.body.appendChild(form);
      form.submit();
    });
  }

  const undoAllBtn = document.getElementById('undoAllBtn');
  if (undoAllBtn) {
    undoAllBtn.addEventListener('click', function () {
      if (confirm('Are you sure you want to restore all deleted invoices?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="undo_all" value="1">';
        document.body.appendChild(form);
        form.submit();
      }
    });
  }

  setTimeout(() => {
    const success = document.getElementById('successAlert');
    if (success) success.style.display = 'none';
  }, 10000);
</script>

<script>
document.querySelectorAll('#historyTable th[data-sort]').forEach((header) => {
  header.addEventListener('click', () => {
    const tbody = header.closest('table').querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const type = header.dataset.sort;
    const isAsc = !header.classList.contains('asc');

    // Reset sort classes
    header.parentElement.querySelectorAll('th').forEach(th => th.classList.remove('asc', 'desc'));
    header.classList.add(isAsc ? 'asc' : 'desc');

    const index = Array.from(header.parentElement.children).indexOf(header);

    rows.sort((a, b) => {
        let aVal = a.children[index].textContent.trim();
        let bVal = b.children[index].textContent.trim();
        
        // Use selected value if it's a dropdown
        const aSelect = a.children[index].querySelector('select');
        const bSelect = b.children[index].querySelector('select');
        if (aSelect && bSelect) {
          aVal = aSelect.value;
          bVal = bSelect.value;
        }

      if (type === 'number') return isAsc ? aVal - bVal : bVal - aVal;
      if (type === 'date') return isAsc ? new Date(aVal) - new Date(bVal) : new Date(bVal) - new Date(aVal);

      return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    rows.forEach(row => tbody.appendChild(row));
    // Re-apply pagination (start from first page after sort)
    if (typeof applyPagination === 'function') {
      applyPagination(true);
    }
  });
});
</script>

<div class="modal" id="deleteModal">
  <div class="modal-content">
    <span class="close-modal" id="closeDeleteModal">&times;</span>
    <h2 class="modal-title">Confirm Deletion</h2>
    <div class="confirmation-message">
      Are you sure you want to delete invoice <span class="highlight" id="invoiceNumber"></span>?
    </div>
    <p>This action will move the invoice to Trash Bin and can be restored.</p>
    <form id="deleteForm" method="POST" onsubmit="return !!document.getElementById('delete_id').value;">
      <input type="hidden" name="delete_id" id="delete_id" value="">
      <div class="btn-group">
        <button type="button" class="btn btn-cancel" id="cancelDelete">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-trash"></i> Delete Invoice
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal" id="markPaidModal">
  <div class="modal-content" style="padding: 2rem; max-width: 650px; background: #f8f9fa;">
    <span class="close-modal" id="closeMarkPaidModal">&times;</span>

    <h3 style="color: #3f37c9; font-weight: 700; margin-bottom: 1.5rem;">
      <i class="fas fa-receipt"></i> Confirm Invoice Payment
    </h3>

    <form id="markPaidForm" enctype="multipart/form-data" style="text-align: left;">
      <input type="hidden" name="invoice_id" id="markPaidInvoiceId">

      <!-- Payment Method Section -->
      <div style="margin-bottom: 1.5rem;">
        <label for="payment_method" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Payment Method</label>
        <select class="form-select" name="payment_method" id="payment_method" required style="padding: 0.75rem; border-radius: 8px;">
          <option value="">-- Select Method --</option>
          <option value="Cheque">Cheque</option>
          <option value="Direct Debit">Direct Debit</option>
          <option value="Bank Transfer">Bank Transfer</option>
          <option value="Cash">Cash</option>
        </select>
      </div>

      <!-- File Upload Section -->
      <div style="margin-bottom: 1.5rem;">
          <label for="payment_proof" style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Upload Proof of Payment (optional)</label>
        
          <label for="payment_proof" style="display: block; border: 2px dashed #4895ef; padding: 2rem; text-align: center; border-radius: 12px; background: #eef4ff; cursor: pointer;">
              <i class="fas fa-file-upload" style="font-size: 2rem; color: #4895ef;"></i>
              <p id="proofLabel" style="margin: 0.5rem 0 0.25rem;">Click to browse or drag a file here</p>
              <input type="file" name="payment_proof" id="payment_proof" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
            </label>
            
            <!-- Add preview below the upload box -->
            <div id="proofPreview" style="margin-top: 1rem;"></div>
        </div>

      <!-- Buttons -->
      <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
        <button type="button" class="btn btn-cancel" id="cancelMarkPaid" style="background: #adb5bd;">Cancel</button>
        <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.8rem;">
          <i class="fas fa-check-circle"></i> Mark as Paid
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function () {
      const invoiceId = this.getAttribute('data-id');
      const invoiceNumber = this.getAttribute('data-invoice');

      console.log("Opening modal for ID:", invoiceId); // ✅ TEST

      document.getElementById('delete_id').value = invoiceId;
      document.getElementById('invoiceNumber').textContent = invoiceNumber;

      document.getElementById('deleteModal').style.display = 'flex';
    });
  });

  document.getElementById('cancelDelete').addEventListener('click', () => {
    document.getElementById('deleteModal').style.display = 'none';
  });

  document.getElementById('closeDeleteModal').addEventListener('click', () => {
    document.getElementById('deleteModal').style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('deleteModal')) {
      document.getElementById('deleteModal').style.display = 'none';
    }
  });
});
</script>

<script>
function updateDropdownColor(dropdown) {
  dropdown.classList.remove('paid', 'unpaid');
  dropdown.classList.toggle('show');
  const val = dropdown.value.toLowerCase();
  if (val === 'paid') dropdown.classList.add('paid');
  if (val === 'unpaid') dropdown.classList.add('unpaid');
}

document.querySelectorAll('.status-dropdown').forEach(drop => {
  updateDropdownColor(drop); // Apply on page load
  drop.addEventListener('change', () => updateDropdownColor(drop));
});
</script>


<script>
document.querySelectorAll('.status-dropdown').forEach(drop => {
  drop.addEventListener('change', function () {
    const invoiceId = this.getAttribute('data-id');
    const newStatus = this.value;
    const originalStatus = this.getAttribute('data-original');
    const dropdown = this;

    if (newStatus === 'Paid') {
      // Reset file label + input
      const proofLabel = document.querySelector('#payment_proof').closest('label');
      if (proofLabel) proofLabel.querySelector('p').textContent = 'Click to browse or drag a file here';
      document.getElementById('payment_method').value = '';
      document.getElementById('payment_proof').value = '';
      document.getElementById('markPaidInvoiceId').value = invoiceId;

      // Store dropdown element to revert if needed
      document.getElementById('markPaidModal').dataset.dropdown = invoiceId;
      dropdown.value = originalStatus; // revert visually until confirmed
      document.getElementById('markPaidModal').style.display = 'flex';
    } else {
      fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          invoice_id: invoiceId,
          status: 'Unpaid'
        })
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.message || 'Failed to update status.');
          dropdown.value = originalStatus;
          updateDropdownColor(dropdown);
          return;
        }

        // find the row we just switched
        const row = dropdown.closest('tr');
        bustPdfLinksForRow(row);
        
        // remove the Payment Info button if it exists
        const payInfoBtn = row.querySelector('.view-payment-btn');
        if (payInfoBtn) payInfoBtn.remove();
        
        // ✅ NEW: bust cached PDF links so Pay Now + layout refreshes
        bustPdfLinksForRow(row);

        // update the dropdown back to Unpaid visually
        dropdown.value = 'Unpaid';
        dropdown.setAttribute('data-original', 'Unpaid');
        updateDropdownColor(dropdown);
      })
      .catch(err => {
          console.error(err);
          alert('Network error, could not mark unpaid.');
          dropdown.value = originalStatus;
          updateDropdownColor(dropdown);
        });
    }
  });
});

document.getElementById('markPaidForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('status', 'Paid');

  const invoiceId = document.getElementById('markPaidInvoiceId').value;
  const dropdown = document.querySelector(`.status-dropdown[data-id="${invoiceId}"]`);

  // Hide Mark Paid modal, show Progress modal
  document.getElementById('markPaidModal').style.display = 'none';
  document.getElementById('progressModal').style.display = 'flex';

  let progress = 0;
  const progressBar = document.getElementById('progressBar');
  const progressPercent = document.getElementById('progressPercent');
  const steps = ['step1', 'step2', 'step3', 'step4'];
  steps.forEach(id => document.getElementById(id).style.opacity = '0.4');

  const updateStep = (index) => {
    steps.forEach((id, i) => {
      document.getElementById(id).style.opacity = i <= index ? '1' : '0.4';
    });
  };

  let currentStep = 0;
  const interval = setInterval(() => {
    progress += Math.floor(Math.random() * 10) + 8;
    if (progress > 100) progress = 100;
    progressBar.style.width = progress + '%';
    progressPercent.textContent = progress + '%';

    if (progress >= 25 && currentStep < 1) updateStep(++currentStep);
    if (progress >= 50 && currentStep < 2) updateStep(++currentStep);
    if (progress >= 75 && currentStep < 3) updateStep(++currentStep);
    if (progress >= 95 && currentStep < 4) updateStep(++currentStep);

    if (progress === 100) clearInterval(interval);
  }, 400);

    fetch('update_status.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (dropdown) {
          dropdown.value = 'Paid';
          dropdown.setAttribute('data-original', 'Paid');
          updateDropdownColor(dropdown);
        }
    
    // ✅ NEW: bust cached PDF links for this row
        const row = dropdown ? dropdown.closest('tr') : null;
        bustPdfLinksForRow(row);
        
        setTimeout(() => {
          document.getElementById('progressModal').style.display = 'none';
          document.getElementById('successMarkPaidModal').style.display = 'flex';
        }, 5500);
      } else {
        alert(data.message || 'Error saving payment info.');
        document.getElementById('progressModal').style.display = 'none';
    
        // revert dropdown safely
        if (dropdown) {
          dropdown.value = dropdown.getAttribute('data-original') || 'Unpaid';
          updateDropdownColor(dropdown);
        }
      }
    })
    .catch(err => {
      console.error(err);
      alert('Network/Server error. Could not mark as paid.');
      document.getElementById('progressModal').style.display = 'none';
    
      // revert dropdown safely
      if (dropdown) {
        dropdown.value = dropdown.getAttribute('data-original') || 'Unpaid';
        updateDropdownColor(dropdown);
      }
    });
});

document.getElementById('cancelMarkPaid').addEventListener('click', () => {
  document.getElementById('markPaidModal').style.display = 'none';
  const dropdownId = document.getElementById('markPaidModal').dataset.dropdown;
  const dropdown = document.querySelector(`.status-dropdown[data-id="${dropdownId}"]`);
  if (dropdown) dropdown.value = dropdown.getAttribute('data-original'); // revert status
  if (dropdown) updateDropdownColor(dropdown);
});

</script>

<script>
document.getElementById('payment_proof').addEventListener('change', function () {
  const label = this.closest('label');
  const preview = document.getElementById('proofPreview');
  const name = this.files.length > 0 ? this.files[0].name : 'Click to browse or drag a file here';
  label.querySelector('p').textContent = name;

  preview.innerHTML = '';

  if (this.files.length > 0) {
    const file = this.files[0];
    const type = file.type;

    if (type.startsWith('image/')) {
      const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.maxWidth = '100%';
        img.style.maxHeight = '200px';
        img.style.objectFit = 'contain';
        img.style.borderRadius = '8px';
        img.style.marginTop = '1rem';
      preview.appendChild(img);
    } else if (type === 'application/pdf') {
      const pdf = document.createElement('iframe');
      pdf.src = URL.createObjectURL(file);
      pdf.width = '100%';
      pdf.height = '350px';
      pdf.style.border = '1px solid #ccc';
      pdf.style.marginTop = '1rem';
      preview.appendChild(pdf);
    } else {
      preview.innerHTML = '<p style="color: red;">Unsupported preview format.</p>';
    }
  }
});
</script>

<script>
document.querySelectorAll('.view-payment-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('paymentInvoiceNum').textContent = btn.dataset.invoice;
    document.getElementById('paymentMethodText').textContent = btn.dataset.method || 'N/A';
    const proof = btn.dataset.proof;
    const link = proof ? `<a href="${proof}" target="_blank">View Proof</a>` : 'No file uploaded';
    document.getElementById('paymentProofLink').innerHTML = link;
    document.getElementById('paymentInfoModal').style.display = 'flex';
  });
});

</script>

<div class="modal" id="paymentInfoModal">
  <div class="modal-content" style="padding: 2rem; max-width: 550px; background: #f8f9fa;">
    <span class="close-modal" id="closePaymentInfo">&times;</span>

    <h3 style="color: #3f37c9; font-weight: 700; margin-bottom: 1.5rem;">
      <i class="fas fa-credit-card"></i> Payment Information
    </h3>

    <div style="font-size: 14px; text-align: left; line-height: 1.6;">
      <p><strong>Invoice:</strong> <span id="paymentInvoiceNum" style="color: #333;"></span></p>
      <p><strong>Payment Method:</strong> <span id="paymentMethodText" style="color: #555;"></span></p>
      <p><strong>Proof:</strong> <span id="paymentProofLink"></span></p>
    </div>

    <div style="margin-top: 1.8rem; text-align: right;">
      <button type="button" class="btn btn-cancel" id="closePaymentInfoBtn">Close</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('paymentInfoModal');
  const closeBtnX = document.getElementById('closePaymentInfo');
  const closeBtnBottom = document.getElementById('closePaymentInfoBtn');

  // ✅ Close when clicking the X
  closeBtnX.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  // ✅ Close when clicking the "Close" button
  closeBtnBottom.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  // ✅ Close when clicking outside the modal box
  window.addEventListener('click', function (event) {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('paymentInfoModal');
  const closeBtnX = document.getElementById('closePaymentInfo');
  const closeBtnBottom = document.getElementById('closePaymentInfoBtn');

  // ✅ Close when clicking the X
  closeBtnX.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  // ✅ Close when clicking the "Close" button
  closeBtnBottom.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  // ✅ Close when clicking outside the modal box
  window.addEventListener('click', function (event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    } // ✅ keep this one only
  }); // ✅ closes window click listener
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (e) {
    const allMenus = document.querySelectorAll('.dropdown-menu');
    const toggleBtn = e.target.closest('.dropdown-toggle');
    const dropdown = e.target.closest('.dropdown');

    // Close all menus when clicking outside
    if (!dropdown) {
      allMenus.forEach(menu => {
        menu.classList.remove('show');
        menu.closest('.dropdown')?.classList.remove('dropup');
      });
      return;
    }

    // Handle toggle button click
    if (toggleBtn && dropdown) {
      const menu = dropdown.querySelector('.dropdown-menu');
      const isOpen = menu.classList.contains('show');

      // Close all menus
      allMenus.forEach(m => {
        if (m !== menu) {
          m.classList.remove('show');
          m.closest('.dropdown')?.classList.remove('dropup');
        }
      });

      // Toggle current menu
      if (!isOpen) {
        menu.classList.add('show');
        positionDropdownMenu(menu, toggleBtn);
      } else {
        menu.classList.remove('show');
        menu.closest('.dropdown')?.classList.remove('dropup');
      }
    }
  });
});
</script>

<!-- Progress Modal -->
<div class="modal" id="progressModal">
  <div class="modal-content" style="padding: 2rem; max-width: 550px; background: #fff; border-radius: 12px; box-shadow: var(--shadow); text-align: center;">
    <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;">
      <i class="fas fa-sync-alt fa-spin"></i> Processing Payment
    </h3>

    <div id="progressSteps" style="text-align: left; margin-bottom: 1.5rem; line-height: 1.6; font-size: 15px;">
      <p id="step1">🧾 Updating invoice status...</p>
      <p id="step2">📁 Uploading proof...</p>
      <p id="step3">📊 Saving payment info...</p>
      <p id="step4">📧 Sending email...</p>
    </div>

    <div style="width: 100%; background: #e9ecef; border-radius: 20px; overflow: hidden;">
      <div id="progressBar" style="width: 0%; height: 20px; background: var(--primary); transition: width 0.3s;"></div>
    </div>
    <p id="progressPercent" style="margin-top: 0.5rem; font-weight: 600; color: #333;">0%</p>
  </div>
</div>

<!-- Success Modal -->
<div class="modal" id="successMarkPaidModal">
  <div class="modal-content" style="padding: 2rem; max-width: 600px; background: #ffffff; border-radius: 12px; box-shadow: var(--shadow); text-align: center; position: relative;">
    <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 1.2rem;">
      <i class="fas fa-check-circle" style="color: #28a745; margin-right: 0.5rem;"></i>
      Invoice Marked as Paid
    </h3>
    <p style="font-size: 1rem; color: #333;">The invoice status has been updated and payment info saved successfully.</p>

    <div style="margin-top: 2.2rem; text-align: center;">
      <button type="button" class="btn btn-primary" id="closeSuccessPaidBtn" style="padding: 0.75rem 1.6rem; font-size: 1rem;">
        OK
      </button>
    </div>
  </div>
</div>

<script>
function positionDropdownMenu(menu, toggle) {
    const toggleRect = toggle.getBoundingClientRect();
    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Calculate available space on right and left
    const spaceRight = viewportWidth - toggleRect.right;
    const spaceLeft = toggleRect.left;
    
    // Default: position below toggle, aligned to left
    let left = toggleRect.left;
    let top = toggleRect.bottom + 5; // Add small gap
    
    // If not enough space on right, align to right of toggle
    if (spaceRight < menuWidth && spaceLeft >= menuWidth) {
        left = toggleRect.right - menuWidth;
    }
    // If not enough space on either side, stick to viewport edge
    else if (spaceRight < menuWidth) {
        left = viewportWidth - menuWidth - 10;
    }
    
    // Adjust vertical position if not enough space below
    if (top + menuHeight > viewportHeight) {
        top = toggleRect.top - menuHeight - 5; // Position above
        // Add dropup class for styling if needed
        menu.closest('.dropdown').classList.add('dropup');
    }
    
    // Apply calculated positions
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
}
</script>

<script>
document.getElementById('closeSuccessPaidBtn').addEventListener('click', () => {
  document.getElementById('successMarkPaidModal').style.display = 'none';
  location.reload();
});

// Auto-hide after 5 seconds if user doesn’t click OK
setTimeout(() => {
  const modal = document.getElementById('successMarkPaidModal');
  if (modal.style.display === 'flex') {
    modal.style.display = 'none';
    location.reload();
  }
}, 5000);
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function attachRecurringHandlers() {
    document.querySelectorAll('.recurring-toggle').forEach(btn => {
      // Avoid double-binding
      if (btn.dataset.bound === '1') return;
      btn.dataset.bound = '1';

      btn.addEventListener('click', function () {
        const invoiceId = this.getAttribute('data-id');
        const current   = this.getAttribute('data-current') === '1';
        const newValue  = current ? '0' : '1';

        // Confirm when turning OFF
        if (current && !confirm('Turn OFF recurring for this invoice?\nNo further automatic invoices will be generated.')) {
          return;
        }

        const button = this;

        // Optimistic UI update
        button.classList.toggle('on', !current);
        button.classList.toggle('off', current);
        button.setAttribute('data-current', newValue);
        const labelEl = button.querySelector('.toggle-label');
        if (labelEl) {
          labelEl.textContent = newValue === '1' ? 'Monthly' : 'Off';
        }
        let nextDiv = button.parentElement.querySelector('.recurring-next');
        if (nextDiv) {
          nextDiv.textContent = '';
        }

        fetch('update_recurring.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            invoice_id: invoiceId,
            is_recurring: newValue
          })
        })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            alert(data.message || 'Unable to update recurring setting.');

            // Revert UI
            button.classList.toggle('on', current);
            button.classList.toggle('off', !current);
            button.setAttribute('data-current', current ? '1' : '0');
            if (labelEl) {
              labelEl.textContent = current ? 'Monthly' : 'Off';
            }
            return;
          }

          // If backend sends next_run_date, show/update it
          if (typeof data.next_run_date !== 'undefined') {
            if (!nextDiv) {
              nextDiv = document.createElement('div');
              nextDiv.className = 'recurring-next';
              button.parentElement.appendChild(nextDiv);
            }
            nextDiv.textContent = data.next_run_date
              ? 'Next: ' + data.next_run_date
              : '';
          }
        })
        .catch(err => {
          console.error(err);
          alert('Network error while saving recurring status.');

          // Revert UI
          button.classList.toggle('on', current);
          button.classList.toggle('off', !current);
          button.setAttribute('data-current', current ? '1' : '0');
          if (labelEl) {
            labelEl.textContent = current ? 'Monthly' : 'Off';
          }
        });
      });
    });
  }

  attachRecurringHandlers();
});
</script>

<script>
function bustPdfLinksForRow(row) {
  if (!row) return;
  const t = Date.now();
  row.querySelectorAll('a[data-pdf-base]').forEach(a => {
    const base = a.getAttribute('data-pdf-base');
    if (base) a.href = base + '?t=' + t;
  });
}

// ✅ Also bust on every click (even if row didn't refresh yet)
document.addEventListener('click', function (e) {
  const a = e.target.closest('a[data-pdf-base]');
  if (!a) return;
  const base = a.getAttribute('data-pdf-base');
  if (base) a.href = base + '?t=' + Date.now();
});
</script>

</body>
<?php ob_end_flush(); ?>
</html>