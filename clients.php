<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$activeMenu = 'clients';
$activeTab = '';
$activeSub = '';
require_once 'config.php';
require_once 'middleware.php'; // ✅ Add this after config 
if (
  !has_permission('view_clients') &&
  !has_permission('add_client') &&
  !has_permission('edit_client') &&
  !has_permission('delete_client') &&
  !has_permission('restore_clients') &&
  !has_permission('view_all_clients') // 👈 NEW: allow users who can view all clients
) {
  die('Access Denied');
}

require 'styles.php';   // keep global CSS only

// 🔐 Ownership / visibility
$currentUserId      = $_SESSION['user_id'];
$canViewAllClients  = has_permission('view_all_clients'); // we'll wire this in settings-permissions later

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Undo recent
    if (isset($_POST['undo_recent'])) {
        $stmt = $pdo->prepare("UPDATE clients SET deleted_at = NULL WHERE id = (
            SELECT id FROM clients WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 1
        )");
        $stmt->execute();
        $success = "Most recent deletion has been undone!";
    }

    // Undo all
    elseif (isset($_POST['undo_all'])) {
        $stmt = $pdo->prepare("UPDATE clients SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
        $stmt->execute();
        $success = "All deleted clients have been restored!";
    }

    // Delete ALL clients (soft delete)
    elseif (isset($_POST['delete_all_clients'])) {
        try {
            $stmt = $pdo->prepare("UPDATE clients SET deleted_at = NOW() WHERE deleted_at IS NULL");
            $stmt->execute();
            $success = "All clients have been deleted (soft delete). You can restore them using Undo buttons.";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Delete a client (soft delete)
    elseif (isset($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE clients SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $success = "Client deleted successfully!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Add/edit client
    elseif (isset($_POST['company_name']) || isset($_POST['client_id'])) {
        try {
            // Validate required fields
            $required = ['company_name', 'phone', 'email', 'address'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill all required fields");
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Sanitize inputs
            $company_name = htmlspecialchars($_POST['company_name']);
            $representative = htmlspecialchars($_POST['representative'] ?? '');
            $phone = htmlspecialchars($_POST['phone']);
            $email = htmlspecialchars($_POST['email']);
            $address = htmlspecialchars($_POST['address']);
            $gst_hst = htmlspecialchars($_POST['gst_hst'] ?? '');
            $notes = htmlspecialchars($_POST['notes'] ?? '');

            // Update client
            if (!empty($_POST['client_id'])) {
                $stmt = $pdo->prepare("
                    UPDATE clients SET 
                        company_name = ?, 
                        representative = ?, 
                        phone = ?, 
                        email = ?, 
                        address = ?, 
                        gst_hst = ?, 
                        notes = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $company_name,
                    $representative,
                    $phone,
                    $email,
                    $address,
                    $gst_hst,
                    $notes,
                    $_POST['client_id']
                ]);
                $success = "Client updated successfully!";
            }

            // Create new client
            else {
                $stmt = $pdo->prepare("
                    INSERT INTO clients (
                        company_name,
                        representative,
                        phone,
                        email,
                        address,
                        gst_hst,
                        notes,
                        created_by
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");
                $stmt->execute([
                    $company_name,
                    $representative,
                    $phone,
                    $email,
                    $address,
                    $gst_hst,
                    $notes,
                    $currentUserId   // 🔐 owner of this client
                ]);
                $success = "Client added successfully!";
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch existing clients
$clients = [];
try {
    if ($canViewAllClients) {
        // 👑 Roles with "view_all_clients" see ALL clients
        $sql = "
            SELECT 
                c.*, 
                MAX(i.created_at) AS last_invoice_date,
                COUNT(i.id) AS total_invoices,
                SUM(CASE WHEN i.status = 'Paid' THEN 1 ELSE 0 END) AS paid_invoices,
                SUM(CASE WHEN i.status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid_invoices,
                u.full_name AS user_name
            FROM clients c
            LEFT JOIN invoices i ON c.company_name = i.bill_to_name
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ";
        $stmt = $pdo->query($sql);
    } else {
        // 👤 Normal roles: only their own clients
        //     🔐 Strict privacy: only show rows they own
        $sql = "
            SELECT 
                c.*, 
                MAX(i.created_at) AS last_invoice_date,
                COUNT(i.id) AS total_invoices,
                SUM(CASE WHEN i.status = 'Paid' THEN 1 ELSE 0 END) AS paid_invoices,
                SUM(CASE WHEN i.status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid_invoices,
                u.full_name AS user_name
            FROM clients c
            LEFT JOIN invoices i ON c.company_name = i.bill_to_name
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.deleted_at IS NULL
              AND c.created_by = :uid
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $currentUserId]);
    }

    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Optional: you can log if needed
    // error_log('Clients query failed: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Clients</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
      // Ensure dark mode is applied immediately
      if (localStorage.getItem('darkMode') === '1') {
        document.documentElement.classList.add('dark-mode');
      }
    </script>
  <style>
    /* All CSS styles from the properly styled version */
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

    .dark-mode {
      --primary: #5a7dff;
      --primary-light: #6e8fff;
      --secondary: #4d45d1;
      --success: #5ed5f9;
      --danger: #ff3d96;
      --warning: #ffaa45;
      --dark: #e9ecef;
      --light: #212529;
      --gray: #adb5bd;
      --border: #495057;
      --card-bg: #2d3748;
      --body-bg: #1a202c;
      --sidebar-bg: #1e293b;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.3);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--body-bg);
      color: var(--dark);
      transition: var(--transition);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .app-container {
      display: flex;
      min-height: 100vh;
    }

    /* Header Styles */
    .header {
      background: var(--primary);
      color: white;
      padding: 0 1.5rem;
      height: var(--header-height);
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .logo i {
      font-size: 1.8rem;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .theme-toggle {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .theme-toggle:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.2rem;
    }

    /* Sidebar Styles */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      color: white;
      height: calc(100vh - var(--header-height));
      position: fixed;
      top: var(--header-height);
      left: 0;
      overflow-y: auto;
      transition: var(--transition);
      z-index: 90;
    }

    .sidebar-menu {
      padding: 1.5rem 0;
    }

    .menu-item {
      padding: 0.8rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
      transition: var(--transition);
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-weight: 500;
    }

    .menu-item:hover, .menu-item.active {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 4px solid var(--primary-light);
    }

    .menu-item i {
      width: 24px;
      text-align: center;
    }

    /* Main Content Styles */
    .main-content {
      flex: 1;
      margin-left: var(--sidebar-width);
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

    /* Client Form Styles */
    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }

    .form-label.required::after {
      content: " *";
      color: var(--danger);
    }

    .form-control {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: var(--card-bg);
      color: var(--dark);
      font-size: 1rem;
      transition: var(--transition);
    }

    .form-control:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    
    input.form-control[type="text"] {
      padding: 0.6rem 1rem;
      font-size: 1rem;
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }

    .view-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 1.5rem 2rem;
      margin-top: 1rem;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    .view-grid label {
      display: block;
      font-weight: 600;
      font-size: 0.9rem;
      color: var(--primary);
      margin-bottom: 0.25rem;
    }
    
    .view-grid div > div {
      background: rgba(67, 97, 238, 0.05);
      padding: 0.75rem 1rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      color: var(--dark);
    }
    
    .view-grid .full-span {
      grid-column: 1 / -1;
    }

    .modal-content::-webkit-scrollbar {
      width: 8px;
    }
    .modal-content::-webkit-scrollbar-thumb {
      background-color: rgba(67, 97, 238, 0.3);
      border-radius: 10px;
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

    .alert-error {
      background: rgba(247, 37, 133, 0.2);
      border: 1px solid var(--danger);
      color: var(--danger);
    }

    #clientsTable td:first-child,
    #clientsTable th:first-child {
      width: 60px;
      min-width: 60px;
      padding-left: 0;
      padding-right: 0;
      text-align: center;
    }

    .table-container {
      overflow-x: auto;
      margin-top: 2rem;
      border-radius: var(--radius);    /* Keep beautiful shape */
      background: transparent;         /* No background box */
      box-shadow: none;                /* No drop shadow */
      border: none;                    /* ✅ Remove weird border */
      width: 100%;                     /* 👈 NEW: don't grow wider than page */
      box-sizing: border-box;          /* 👈 ensures padding/border are included */
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: var(--radius); /* Keep header’s nice shape */
      overflow: hidden;             /* Enforce round corners */
    }

    th, td {
      word-break: break-word;
      /*max-width: 220px;*/
      vertical-align: middle;
      padding: 1rem;
      /*border-bottom: 1px solid rgba(222, 226, 230, 0.6);*/ /* soft gray, like original 
      border-right: none;
      border-left: none;
    }

    th {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.95rem;
      font-weight: 600;
      background-color: rgba(67, 97, 238, 0.08); /* ✅ slightly lighter */
      color: #4361ee; /* ✅ exact match from history.php */
      white-space: normal;  /* 👈 allow wrapping instead of forcing one long line */
      text-align: center;
      padding: 0.95rem 1rem;
    }
    
    td {
      text-align: center;
      vertical-align: middle;
      border-bottom: 1px solid var(--border);  /* Should match all other columns */
    }


    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
      transition: background 0.3s ease;
    }

    tbody tr td:last-child {
      border-bottom: 1px solid var(--border) !important;
    }

/*    #clientsTable td:last-child {
      border-bottom: 1px solid rgba(222, 226, 230, 0.6) !important;
    }
*/

    .actions-cell::before {
      content: "";
      display: block;
      height: 100%;
      min-height: 1px;
    }

    .actions-cell {
      display: table-cell;
      vertical-align: middle;
      text-align: center;
      padding: 0.4rem 0.25rem;      /* a bit tighter vertically */
      white-space: nowrap !important; /* 👈 keep all buttons on one line */
    }
    
    .actions-cell .btn-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px;   /* back to normal circle */
      height: 30px;
      margin: 0 2px;
      border-radius: 50%;
      padding: 0;
      flex-shrink: 0;
    }
    
    #clientsTable tr {
      height: auto;
    }

    .btn-icon {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      flex-shrink: 0;
      margin: 0 2px;
    }
    
    .btn-icon i {
      font-size: 0.9rem;
    }

    .btn-edit {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .btn-delete {
      background: rgba(247, 37, 133, 0.2);
      color: var(--danger);
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    
    .modal-content {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow-hover);
      width: 90%;
      max-width: 800px;        /* 🔁 Wider modal */
      max-height: 90vh;        /* ✅ Prevent huge height */
      overflow-y: auto;        /* ✅ Scroll internally if too tall */
      padding: 2rem;
      position: relative;
    }
    
    .close-modal {
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--gray);
    }
    
    .modal-title {
      margin-bottom: 1.5rem;
      color: var(--primary);
      font-size: 1.5rem;
    }
    
    .btn-group {
      display: flex;
      gap: 10px;
      margin-top: 1.5rem;
      justify-content: flex-end;
    }
    
    .btn-danger {
      background: var(--danger);
      color: white;
    }
    
    .btn-danger:hover {
      background: #e01a4f;
    }
    
    .btn-cancel {
      background: var(--gray);
      color: white;
    }
    
    .btn-cancel:hover {
      background: #5a6268;
    }
    
    .confirmation-message {
      font-size: 1.1rem;
      margin: 1rem 0;
      line-height: 1.6;
    }
    
    .highlight {
      color: var(--primary);
      font-weight: bold;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .sidebar {
        width: 70px;
      }
      
      .sidebar .menu-text {
        display: none;
      }
      
      .main-content {
        margin-left: 70px;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .header {
        padding: 0 1rem;
      }
      
      .user-name {
        display: none;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .btn-group {
        flex-direction: column;
      }
    }
    
    th[data-sort] {
      cursor: pointer;
      user-select: none;
      position: relative;
      padding-right: 0.8rem; /* tighter spacing */
      white-space: nowrap;
    }
    
    th[data-sort] .sort-indicator {
      display: inline-block;
      margin-left: 3px;
      font-size: 0.65rem;
      color: var(--gray);
      opacity: 0.6;
      position: relative;
      top: -1px;
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
      content: "⇅"; /* Neutral state icon */
    }
    
    #clientsTable thead th {
      background-color: rgba(67, 97, 238, 0.1) !important;
      color: #4361ee !important;
      font-size: 0.95rem !important;
      font-weight: 600 !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    }
    
    #clientsTable {
      width: 100%;
    }
    
    table {
      table-layout: fixed;       /* 👈 force table to fit inside width */
      width: 100%;
      border-collapse: collapse;
    }

    td, th {
      word-break: break-word;
      padding: 1rem;
      vertical-align: middle;
      text-align: center;
      border-bottom: 1px solid var(--border);
    }
    
    .btn-view {
      background: rgba(67, 97, 238, 0.2);
      color: var(--primary);
    }
    
    /* 🔁 Pagination footer styles (same look as history.php) */
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

    /* Tighter Total / Paid / Unpaid columns to free space for Actions */
    #clientsTable th:nth-last-child(4),
    #clientsTable th:nth-last-child(3),
    #clientsTable th:nth-last-child(2),
    #clientsTable td:nth-last-child(4),
    #clientsTable td:nth-last-child(3),
    #clientsTable td:nth-last-child(2) {
      padding-left: 0.4rem;
      padding-right: 0.4rem;
      width: 70px;
      min-width: 70px;
    }

    /* Make the Actions column wide enough for the 3 round buttons */
    #clientsTable th:last-child,
    #clientsTable td:last-child {
      width: 155px;
      min-width: 155px;
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
      flex-wrap: wrap;      /* 👈 allow buttons + selects to go on 2 lines */
      white-space: normal;  /* 👈 don't force one huge line */
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
    <!-- Sidebar -->
   <?php require 'sidebar.php'; ?>
   
    <!-- Main Content -->
    <div class="main-content">
      <div class="page-header">
  <h1 class="page-title">Manage Clients</h1>

  <?php if (
    has_permission('add_client') ||
    has_permission('delete_client') ||
    has_permission('restore_clients') ||
    has_permission('undo_recent_client') ||
    has_permission('undo_all_clients') ||
    has_permission('export_clients')
  ): ?>
    <div class="page-actions">
            <?php if (has_permission('undo_recent_client')): ?>
        <button class="btn btn-outline" id="undoRecentBtn">
          <i class="fas fa-undo"></i>
          <span>Undo Recent Delete</span>
        </button>
      <?php endif; ?>

      <?php if (has_permission('delete_client')): ?>
        <button class="btn btn-outline" id="deleteAllBtn">
          <i class="fas fa-user-slash"></i>
          <span>Delete All</span>
        </button>
      <?php endif; ?>

      <?php if (has_permission('undo_all_clients')): ?>
        <button class="btn btn-outline" id="undoAllBtn">
          <i class="fas fa-history"></i>
          <span>Undo All Deletes</span>
        </button>
      <?php endif; ?>

      <?php if (has_permission('export_clients')): ?>
        <button class="btn btn-outline" id="exportExcelBtn">
          <i class="fas fa-file-export"></i>
          <span>Export to Excel</span>
        </button>
      <?php endif; ?>

      <?php if (has_permission('add_client')): ?>
        <button class="btn btn-primary" id="newClientBtn">
          <i class="fas fa-plus"></i>
          <span>New Client</span>
        </button>
      <?php endif; ?>
    </div> <!-- ✅ This closes .page-actions -->
  <?php endif; ?>
</div> <!-- ✅ This closes .page-header -->


      <?php if (isset($success)): ?>
          <div class="alert alert-success" id="successAlert">
            <i class="fas fa-check-circle"></i> <?= $success ?>
          </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
          <div class="alert alert-error" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
          </div>
        <?php endif; ?>

      <!-- Client Form (initially hidden) -->
      <div class="card" id="clientFormCard" style="display: none;">
        <h2 class="card-title" id="formTitle">Add New Client</h2><br>
        <form id="clientForm" method="POST">
          <input type="hidden" id="client_id" name="client_id" value="">
          <div class="form-grid">
            <div class="form-group">
              <label for="company_name" class="form-label required">Company Name</label>
              <input type="text" id="company_name" name="company_name" class="form-control" required>
            </div>
            
            <div class="form-group">
              <label for="representative" class="form-label">Representative Name</label>
              <input type="text" id="representative" name="representative" class="form-control">
            </div>
            
            <div class="form-group">
              <label for="phone" class="form-label required">Phone Number</label>
              <input type="tel" id="phone" name="phone" class="form-control" required>
            </div>
            
            <div class="form-group">
              <label for="email" class="form-label required">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
              <label for="address" class="form-label required">Mailing Address</label>
              <textarea id="address" name="address" class="form-control" required></textarea>
            </div>
            
            <div class="form-group">
              <label for="gst_hst" class="form-label">GST/HST Number</label>
              <input type="text" id="gst_hst" name="gst_hst" class="form-control">
            </div>
          </div>
          
          <div class="form-group">
            <label for="notes" class="form-label">Internal Notes</label>
            <textarea id="notes" name="notes" class="form-control"></textarea>
          </div>
          
          <div class="btn-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Client
            </button>
            <button type="button" class="btn btn-cancel" id="cancelEdit">
              Cancel
            </button>
          </div>
        </form>
      </div>

      <div class="table-container">
        <!--<h2 class="card-title">Existing Clients<br><br></h2>-->
        <?php if (has_permission('search_clients')): ?>
          <input type="text" id="clientSearch" placeholder="🔍 Search clients..." class="form-control" style="max-width: 300px; margin-bottom: 1.2rem;">
        <?php endif; ?>
        <?php if (empty($clients)): ?>
          <div class="card">
            <p>No clients found. Add your first client using the button above.</p>
          </div>
        <?php else: ?>
        
          <!-- 🔝 TOP Pagination / Filters Toolbar -->
          <div class="table-footer table-footer-top" id="clientsTableFooterTop">
            <div class="pagination-info" id="clientsPaginationInfoTop">
              <!-- Will show: "Showing 1–10 of 241 clients (Page 1 of 25)" -->
            </div>
        
            <div class="pagination-controls">
              <div class="rows-per-page">
                <label for="clientsRowsPerPageTop">Rows per page:</label>
                <select id="clientsRowsPerPageTop">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </div>
        
              <button type="button" class="btn btn-outline" id="clientsFirstPageTop" title="First page">
                &laquo; First
              </button>
        
              <button type="button" class="btn btn-outline" id="clientsPrevPageTop" title="Previous page">
                &lsaquo; Prev
              </button>
        
              <span class="page-indicator">
                Page
                <select id="clientsPageSelectTop"></select>
                of <span id="clientsTotalPagesTop">1</span>
              </span>
        
              <button type="button" class="btn btn-outline" id="clientsNextPageTop" title="Next page">
                Next &rsaquo;
              </button>
        
              <button type="button" class="btn btn-outline" id="clientsLastPageTop" title="Last page">
                Last &raquo;
              </button>
            </div>
          </div>
        
          <table id="clientsTable">
            <thead>
              <tr>
                <th data-sort="number"># <span class="sort-indicator"></span></th>
                <th data-sort="string">Company <span class="sort-indicator"></span></th>
                <th data-sort="string">Representative <span class="sort-indicator"></span></th>
                <th>Phone</th>
                <th>Email</th>
            
                <?php if ($canViewAllClients): ?>
                  <th data-sort="string">User <span class="sort-indicator"></span></th>
                <?php endif; ?>
            
                <th data-sort="date">Last Invoice <span class="sort-indicator"></span></th>
                <th data-sort="number">Total <span class="sort-indicator"></span></th>
                <th data-sort="number">Paid <span class="sort-indicator"></span></th>
                <th data-sort="number">Unpaid <span class="sort-indicator"></span></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clients as $index => $client): ?>
              <tr data-id="<?= $client['id'] ?>">
                <td data-value="<?= $index + 1 ?>"><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($client['company_name']) ?></td>
                <td><?= htmlspecialchars($client['representative'] ?: 'N/A') ?></td>
                <td><?= htmlspecialchars($client['phone']) ?></td>
                <td><?= htmlspecialchars($client['email']) ?></td>
            
                <?php if ($canViewAllClients): ?>
                  <?php $ownerName = $client['user_name'] ?? ''; ?>
                  <td data-value="<?= htmlspecialchars($ownerName) ?>">
                    <?= htmlspecialchars($ownerName ?: '—') ?>
                  </td>
                <?php endif; ?>
            
                <!-- 🆕 New Metrics Columns -->
                <?php
                  $lastInvoiceDate = $client['last_invoice_date'] ? date('Y-m-d', strtotime($client['last_invoice_date'])) : '';
                  $totalInvoices   = intval($client['total_invoices'] ?? 0);
                  $paidInvoices    = intval($client['paid_invoices'] ?? 0);
                  $unpaidInvoices  = intval($client['unpaid_invoices'] ?? 0);
                ?>
                <td data-value="<?= $lastInvoiceDate ?>"><?= $lastInvoiceDate ?: '—' ?></td>
                <td data-value="<?= $totalInvoices ?>"><?= $totalInvoices ?></td>
                <td data-value="<?= $paidInvoices ?>"><?= $paidInvoices ?></td>
                <td data-value="<?= $unpaidInvoices ?>"><?= $unpaidInvoices ?></td>
                <td class="actions-cell" style="white-space: nowrap;">
                  <?php if (has_permission('view_clients')): ?>
                    <button class="btn btn-icon btn-view" title="View" data-id="<?= $client['id'] ?>">
                      <i class="fas fa-eye"></i>
                    </button>
                  <?php endif; ?>
                  
                  <?php if (has_permission('edit_client')): ?>
                    <button class="btn btn-icon btn-edit" title="Edit" data-id="<?= $client['id'] ?>">
                      <i class="fas fa-edit"></i>
                    </button>
                  <?php endif; ?>
                  
                  <?php if (has_permission('delete_client')): ?>
                    <button class="btn btn-icon btn-delete" title="Delete" data-id="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['company_name']) ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <!-- 🔁 NEW: Pagination footer (rows per page + controls) -->
            <div class="table-footer" id="clientsTableFooter">
            <!-- Left side: info text -->
            <div class="pagination-info" id="clientsPaginationInfo">
              <!-- Will show: "Showing 1–10 of 241 clients (Page 1 of 25)" -->
            </div>

            <!-- Right side: rows per page + controls -->
            <div class="pagination-controls" id="clientsPaginationControls">
              <div class="rows-per-page">
                <label for="clientsRowsPerPage">Rows per page:</label>
                <select id="clientsRowsPerPage">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </div>

              <button type="button" class="btn btn-outline" id="clientsFirstPage" title="First page">
                &laquo; First
              </button>

              <button type="button" class="btn btn-outline" id="clientsPrevPage" title="Previous page">
                &lsaquo; Prev
              </button>

              <span class="page-indicator">
                Page
                <select id="clientsPageSelect"></select>
                of <span id="clientsTotalPages">1</span>
              </span>

              <button type="button" class="btn btn-outline" id="clientsNextPage" title="Next page">
                Next &rsaquo;
              </button>

              <button type="button" class="btn btn-outline" id="clientsLastPage" title="Last page">
                Last &raquo;
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- View Client Modal -->
<div class="modal" id="viewModal">
  <div class="modal-content">
    <span class="close-modal" id="closeViewModal">&times;</span>
    <h2 class="modal-title">Client Details</h2>
    <div class="confirmation-message" id="clientDetails">
      <!-- Populated by JavaScript -->
    </div>
    <div class="btn-group" style="justify-content: flex-end;">
      <button type="button" class="btn btn-cancel" id="closeViewBtn">
        Close
      </button>
    </div>
  </div>
</div>

  <!-- Delete Confirmation Modal -->
  <div class="modal" id="deleteModal">
    <div class="modal-content">
      <span class="close-modal" id="closeDeleteModal">&times;</span>
      <h2 class="modal-title">Confirm Deletion</h2>
      <div class="confirmation-message">
        Are you sure you want to delete <span class="highlight" id="clientName"></span>?
      </div>
      <p>This action cannot be undone.</p>
      <form id="deleteForm" method="POST">
        <input type="hidden" name="delete_id" id="delete_id" value="">
        <div class="btn-group">
          <button type="button" class="btn btn-cancel" id="cancelDelete">
            Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete Client
          </button>
        </div>
      </form>
    </div>
  </div>

<?php require 'scripts.php'; ?> 
<script>
  (function() {
    const searchInput         = document.getElementById('clientSearch');
    const table               = document.getElementById('clientsTable');
    const tbody               = table ? table.querySelector('tbody') : null;

    // Bottom controls
    const rowsPerPageBottom   = document.getElementById('clientsRowsPerPage');
    const infoBottom          = document.getElementById('clientsPaginationInfo');
    const firstBottom         = document.getElementById('clientsFirstPage');
    const prevBottom          = document.getElementById('clientsPrevPage');
    const nextBottom          = document.getElementById('clientsNextPage');
    const lastBottom          = document.getElementById('clientsLastPage');
    const pageSelectBottom    = document.getElementById('clientsPageSelect');
    const totalPagesBottom    = document.getElementById('clientsTotalPages');

    // Top controls
    const rowsPerPageTop      = document.getElementById('clientsRowsPerPageTop');
    const infoTop             = document.getElementById('clientsPaginationInfoTop');
    const firstTop            = document.getElementById('clientsFirstPageTop');
    const prevTop             = document.getElementById('clientsPrevPageTop');
    const nextTop             = document.getElementById('clientsNextPageTop');
    const lastTop             = document.getElementById('clientsLastPageTop');
    const pageSelectTop       = document.getElementById('clientsPageSelectTop');
    const totalPagesTop       = document.getElementById('clientsTotalPagesTop');

    let currentPage           = 1;
    let lastTotalPages        = 1;

    function getAllRows() {
      return tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
    }

    function getFilteredRows(allRows) {
      if (!searchInput || !searchInput.value.trim()) return allRows;
      const q = searchInput.value.trim().toLowerCase();
      return allRows.filter(row => row.innerText.toLowerCase().includes(q));
    }

    function getRowsPerPage() {
      if (rowsPerPageBottom) {
        const val = parseInt(rowsPerPageBottom.value, 10);
        if (!isNaN(val) && val > 0) return val;
      }
      if (rowsPerPageTop) {
        const val = parseInt(rowsPerPageTop.value, 10);
        if (!isNaN(val) && val > 0) return val;
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

      // Rebuild only if needed
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
      const text = `Showing ${from}–${to} of ${totalRows} clients (Page ${currentPage} of ${totalPages})`;
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
      if (!tbody) return;

      const perPage   = getRowsPerPage();
      const allRows   = getAllRows();
      const filtered  = getFilteredRows(allRows);
      const totalRows = filtered.length;
      const totalPages = Math.max(1, Math.ceil(totalRows / perPage));

      lastTotalPages = totalPages;

      if (page < 1) page = 1;
      if (page > totalPages) page = totalPages;
      currentPage = page;

      // Hide all rows
      allRows.forEach(row => row.style.display = 'none');

      const start = (currentPage - 1) * perPage;
      const end   = start + perPage;
      filtered.slice(start, end).forEach(row => row.style.display = '');

      const from = totalRows === 0 ? 0 : start + 1;
      const to   = Math.min(end, totalRows);

      updateInfo(from, to, totalRows, totalPages);
      updateTotalPages(totalPages);
      updatePageSelects(totalPages);
      syncRowsPerPageControls(perPage);
      updateNavButtons(totalPages);
    }

    // Expose for sort script (like history.php)
    window.renderClientsPage = renderPage;

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

    // Search
    if (searchInput) {
      searchInput.addEventListener('input', () => renderPage(1));
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

    // Initial render if table exists
    if (tbody && getAllRows().length) {
      // Default rows per page sync (if only one select initially)
      const initialPerPage = getRowsPerPage();
      syncRowsPerPageControls(initialPerPage);
      renderPage(1);
    }
  })();
</script>
  
<script>
(function () {
  const btn = document.getElementById('exportExcelBtn');
  if (!btn) return; // button may not exist if permission is missing

  function csvCell(raw) {
    let v = (raw ?? '').toString();

    // remove line breaks (table text sometimes includes them)
    v = v.replace(/\r?\n|\r/g, ' ').trim();

    // ✅ Excel safety + preserve phone formats:
    // If it starts with =, +, -, @ Excel may interpret it as a formula.
    // Prefixing with ' forces Excel to treat it as TEXT while still displaying the value normally.
    if (/^[=+\-@]/.test(v)) v = "'" + v;

    // escape quotes for CSV
    v = v.replace(/"/g, '""');

    return `"${v}"`;
  }

  btn.addEventListener('click', function () {
    const table = document.getElementById('clientsTable');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');

    // headers (remove Actions column)
    const headers = Array.from(table.querySelectorAll('thead th'))
      .map(th => th.innerText.replace(/\r?\n|\r/g, ' ').trim())
      .slice(0, -1);

    let csvContent = headers.map(csvCell).join(",") + "\n";

    rows.forEach(row => {
      const cols = row.querySelectorAll('td');
      const rowData = Array.from(cols)
        .slice(0, -1) // remove Actions column
        .map(td => csvCell(td.innerText))
        .join(",");
      csvContent += rowData + "\n";
    });

    // ✅ UTF-8 BOM so Excel opens it correctly
    const blob = new Blob(["\ufeff" + csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.download = "clients_export.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
  });
})();
</script>

<script>
document.querySelectorAll('#clientsTable th[data-sort]').forEach((header, index) => {
  header.addEventListener('click', () => {
    const tbody = header.closest('table').querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const type = header.dataset.sort;
    const isAsc = !header.classList.contains('asc');

    // Reset sort classes
    header.parentElement.querySelectorAll('th').forEach(th => th.classList.remove('asc', 'desc'));
    header.classList.add(isAsc ? 'asc' : 'desc');

    rows.sort((a, b) => {
        const ths = header.parentElement.querySelectorAll('th');
        const thArray = Array.from(ths);
        const columnIndex = thArray.indexOf(header);
        
        const aCell = a.children[columnIndex];
        const bCell = b.children[columnIndex];

      const aVal = aCell.dataset.value || aCell.textContent.trim();
      const bVal = bCell.dataset.value || bCell.textContent.trim();

      if (type === 'number') {
      return isAsc 
        ? parseFloat(aVal) - parseFloat(bVal)
        : parseFloat(bVal) - parseFloat(aVal);
      }

      if (type === 'date') {
          const aDate = aVal && !aVal.includes('—') ? new Date(aVal) : new Date(0); // 1970-01-01
          const bDate = bVal && !bVal.includes('—') ? new Date(bVal) : new Date(0);
          return isAsc ? aDate - bDate : bDate - aDate;
        }

      return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    rows.forEach(row => tbody.appendChild(row));

    // 🔁 Re-apply pagination after sorting (bird’s eye view)
    if (typeof window.renderClientsPage === 'function') {
      window.renderClientsPage(1);
    }
  });
});
</script>

<script>
  // Auto-hide alerts after 10 seconds
  setTimeout(() => {
    const success = document.getElementById('successAlert');
    const error = document.getElementById('errorAlert');
    if (success) success.style.display = 'none';
    if (error) error.style.display = 'none';
  }, 10000); // 10 seconds

  // Optionally clear any PHP message via history state (if desired)
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>

<script>
// View Client Functionality
document.querySelectorAll('.btn-view').forEach(button => {
  button.addEventListener('click', function() {
    const clientId = this.getAttribute('data-id');
    fetch(`get-client.php?id=${clientId}`)
      .then(response => response.json())
      .then(client => {
        const html = `
          <div class="view-grid">
            <div><label>Company</label><div>${client.company_name}</div></div>
            <div><label>Representative</label><div>${client.representative || '—'}</div></div>
            <div><label>Phone</label><div>${client.phone}</div></div>
            <div><label>Email</label><div>${client.email}</div></div>
            <div><label>Address</label><div>${client.address}</div></div>
            <div><label>GST/HST</label><div>${client.gst_hst || '—'}</div></div>
            <div class="full-span"><label>Notes</label><div>${client.notes || '—'}</div></div>
          </div>
        `;
        document.getElementById('clientDetails').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
      })
      .catch(err => {
        alert('Failed to load client details');
      });
  });
});

document.getElementById('closeViewModal').addEventListener('click', () => {
  document.getElementById('viewModal').style.display = 'none';
});
document.getElementById('closeViewBtn').addEventListener('click', () => {
  document.getElementById('viewModal').style.display = 'none';
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // ─────────────────────────────────────────────
  // 1) Undo Recent, Undo All, Delete All clients
  // ─────────────────────────────────────────────
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

  const undoAllBtn = document.getElementById('undoAllBtn');
  if (undoAllBtn) {
    undoAllBtn.addEventListener('click', function () {
      if (!confirm('Are you sure you want to restore ALL deleted clients?')) {
        return;
      }
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = '<input type="hidden" name="undo_all" value="1">';
      document.body.appendChild(form);
      form.submit();
    });
  }

  const deleteAllBtn = document.getElementById('deleteAllBtn');
  if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', function () {
      if (!confirm('Are you sure you want to move ALL clients to Trash (soft delete)?')) {
        return;
      }
      const form = document.createElement('form');
      form.method = 'POST';
      // ⚠️ IMPORTANT: name MUST match your PHP: delete_all_clients
      form.innerHTML = '<input type="hidden" name="delete_all_clients" value="1">';
      document.body.appendChild(form);
      form.submit();
    });
  }

  // ─────────────────────────────────────────────
  // 2) Single delete via confirmation modal
  // ─────────────────────────────────────────────
  const deleteModal     = document.getElementById('deleteModal');
  const deleteIdInput   = document.getElementById('delete_id');
  const clientNameSpan  = document.getElementById('clientName');
  const cancelDeleteBtn = document.getElementById('cancelDelete');
  const closeDeleteX    = document.getElementById('closeDeleteModal');

  if (deleteModal && deleteIdInput && clientNameSpan) {
    // Open modal when clicking delete icon
    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', function () {
        const id   = this.getAttribute('data-id');
        const name = this.getAttribute('data-name') || 'this client';

        deleteIdInput.value        = id;
        clientNameSpan.textContent = name;
        deleteModal.style.display  = 'flex';
      });
    });

    // Close modal on Cancel button
    if (cancelDeleteBtn) {
      cancelDeleteBtn.addEventListener('click', function () {
        deleteModal.style.display = 'none';
      });
    }

    // Close modal on X button
    if (closeDeleteX) {
      closeDeleteX.addEventListener('click', function () {
        deleteModal.style.display = 'none';
      });
    }

    // Close modal when clicking outside the box
    window.addEventListener('click', function (e) {
      if (e.target === deleteModal) {
        deleteModal.style.display = 'none';
      }
    });
  }

  // ─────────────────────────────────────────────
  // 3) New Client / Edit Client form behaviour
  // ─────────────────────────────────────────────
  const clientFormCard = document.getElementById('clientFormCard');
  const clientForm     = document.getElementById('clientForm');
  const formTitle      = document.getElementById('formTitle');
  const clientIdInput  = document.getElementById('client_id');
  const newClientBtn   = document.getElementById('newClientBtn');
  const cancelEditBtn  = document.getElementById('cancelEdit');

  function resetClientForm() {
    if (!clientForm) return;
    clientForm.reset();
    if (clientIdInput) clientIdInput.value = '';
  }

  // "New Client" button → open blank form
  if (newClientBtn && clientFormCard && formTitle) {
    newClientBtn.addEventListener('click', function () {
      resetClientForm();
      formTitle.textContent = 'Add New Client';
      clientFormCard.style.display = 'block';

      // Scroll form into view nicely
      clientFormCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  // "Cancel" button in form → hide form
  if (cancelEditBtn && clientFormCard) {
    cancelEditBtn.addEventListener('click', function () {
      clientFormCard.style.display = 'none';
      resetClientForm();
    });
  }

  // ─────────────────────────────────────────────
  // 4) Edit Client buttons → load data via AJAX
  // ─────────────────────────────────────────────
  if (clientForm && clientFormCard && formTitle && clientIdInput) {
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        if (!id) return;

        fetch('get-client.php?id=' + encodeURIComponent(id))
          .then(res => res.json())
          .then(client => {
            // Fill form fields from JSON
            clientIdInput.value = client.id || id;

            const companyField  = document.getElementById('company_name');
            const repField      = document.getElementById('representative');
            const phoneField    = document.getElementById('phone');
            const emailField    = document.getElementById('email');
            const addressField  = document.getElementById('address');
            const gstField      = document.getElementById('gst_hst');
            const notesField    = document.getElementById('notes');

            if (companyField) companyField.value = client.company_name || '';
            if (repField)     repField.value     = client.representative || '';
            if (phoneField)   phoneField.value   = client.phone || '';
            if (emailField)   emailField.value   = client.email || '';
            if (addressField) addressField.value = client.address || '';
            if (gstField)     gstField.value     = client.gst_hst || '';
            if (notesField)   notesField.value   = client.notes || '';

            formTitle.textContent        = 'Edit Client';
            clientFormCard.style.display = 'block';
            clientFormCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
          })
          .catch(err => {
            console.error(err);
            alert('Failed to load client details for editing.');
          });
      });
    });
  }
});
</script>

</body>
</html>