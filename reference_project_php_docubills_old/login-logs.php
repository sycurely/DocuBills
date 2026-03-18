<?php
session_start();
ob_start();

require_once 'config.php';
require_once 'middleware.php';

// UPDATE: Refresh current session's last activity time
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET last_activity = NOW() 
        WHERE session_id = ?
    ");
    $stmt->execute([session_id()]);
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

function get_setting($key) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
  $stmt->execute([$key]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ? $row['key_value'] : null;
}

if (isset($_POST['save_retention_days']) && has_permission('set_session_retention_days')) {
  $days = max(1, min(365, intval($_POST['session_retention_days'])));
  $stmt = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = 'session_retention_days'");
  $stmt->execute([$days]);
  echo "<script>alert('Session retention days updated to {$days}'); location.href='login-logs.php';</script>";
  exit;
}

if (!has_permission('view_login_logs')) {
  $_SESSION['access_denied'] = true;
  header("Location: access-denied.php");
  exit;
}

$activeMenu = 'login-logs';
$canTerminateAny = has_permission('terminate_sessions');
$canTerminateOwn = has_permission('terminate_own_session');

/* ------------------------------------------------------------------
 | Handle "Terminate Session" POST
 |------------------------------------------------------------------*/
if (isset($_POST['terminate_session_id'])) {
    $targetSession = $_POST['terminate_session_id'];
    $isOwn         = ($targetSession === session_id());

    // Decide whether the current user is allowed to kill this session
    $allowed =
        ($canTerminateAny  && !$isOwn) ||      // can kill others
        ($canTerminateOwn  &&  $isOwn) ||      // can kill own
        ($canTerminateAny  &&  $canTerminateOwn); // super-power

    if ($allowed) {
        // 1. Remove the row from the tracking table
        $stmt = $pdo->prepare("
          UPDATE user_sessions 
          SET last_activity = NOW(), 
              terminated_at = NOW(),
              termination_reason = 'terminated'
          WHERE session_id = ?
        ");
        $stmt->execute([$targetSession]);

        // 2. If they killed their *own* session, destroy it completely
        if ($isOwn) {
            // ✅ Don't delete the row — just mark it terminated
            session_write_close(); // Close session safely
            header("Location: login.php?terminated=1");
            exit;
        }

        $_SESSION['success'] = "Session terminated successfully.";
    } else {
        $_SESSION['error']   = "You don't have permission to terminate that session.";
    }

    header("Location: login-logs.php");  // flash-message then reload
    exit;
}


// Fetch sessions with user info
// Get retention in minutes
$retentionMinutes = 60 * 24 * 60; // 60 days in minutes

// FIX: Only remove sessions older than retention period AND not the current session
$cleanup = $pdo->prepare("
  DELETE FROM user_sessions
  WHERE TIMESTAMPDIFF(MINUTE, last_activity, NOW()) > ?
  AND session_id != ?
");
$cleanup->execute([$retentionMinutes, session_id()]);

// UPDATE: Get session timeout from settings
$sessionTimeoutMinutes = (int) (get_setting('session_timeout_minutes') ?: 30);

// FIX: Fetch all sessions within retention period including current session
$stmt = $pdo->prepare("
  SELECT
      us.*,
      u.username,
      u.full_name,
      r.name AS role,
      TIMESTAMPDIFF(MINUTE, us.last_activity, NOW()) AS idle_mins
  FROM user_sessions us
  JOIN users u ON u.id = us.user_id
  JOIN roles r ON r.id = u.role_id
  WHERE 
    (TIMESTAMPDIFF(MINUTE, us.last_activity, NOW()) <= ? OR us.terminated_at IS NOT NULL)
  ORDER BY us.last_activity DESC
");
$stmt->execute([$retentionMinutes]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_start(); // Start output buffering
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require 'styles.php';?>
  <meta charset="UTF-8">
  <title>Login Logs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
      --table-header-bg: rgba(67, 97, 238, 0.08);
      --table-row-hover: rgba(67, 97, 238, 0.05);
      --text-color: #212529;
      --expired-row-bg: #f3f3f3;
      --expired-row-color: #888;
    }

    body {
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 15px;
      color: var(--text-color);
      background-color: var(--body-bg);
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
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background: rgba(247, 37, 133, 0.2);
        border: 1px solid var(--danger);
        color: var(--danger);
    }
    
    .alert i {
        font-size: 1.2rem;
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
      background: var(--card-bg);
      color: var(--text-color);
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
      background: inherit;
      color: inherit;
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
      background: var(--table-row-hover);
    }

    #loginLogsTable thead th {
      background: var(--table-header-bg);
      color: var(--primary);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-weight: 600;
      font-size: 1rem;
      text-align: center;
    }

    #loginLogsTable tbody td {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.95rem;
      text-align: center;
      vertical-align: middle;
    }

    .actions-cell {
      justify-content: center !important;
      align-items: center;
      gap: 0.5rem;
      min-width: 120px; /* Optional: prevents column collapse */
      position: relative;
    }

    .actions-cell form {
      display: inline-block;
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
      color: var(--text-color);
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
      display: none;
      background-color: var(--card-bg);
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
      background: var(--card-bg);
      color: var(--text-color);
      border: none;
      cursor: pointer;
      text-decoration: none;
    }
    
    .dropdown-menu a:hover,
    .dropdown-menu button:hover {
      background-color: var(--table-row-hover);
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

    .status-active {
      background-color: rgba(76, 201, 240, 0.2);
      color: var(--success);
      border: 1px solid var(--success);
    }
    
    .status-expired {
      background-color: rgba(108, 117, 125, 0.2);
      color: var(--gray);
      border: 1px solid var(--gray);
    }

    .status-terminated {
      background-color: rgba(247, 37, 133, 0.2);
      color: var(--danger);
      border: 1px solid var(--danger);
    }

    .search-container {
      position: relative;
      max-width: 300px;
      margin-bottom: 20px;
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
      white-space: nowrap;
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
      background: var(--card-bg);
      padding: 2rem;
      border-radius: 8px;
      width: 100%;
      max-width: 500px;
      text-align: center;
      position: relative;
      color: var(--text-color);
    }
    
    .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--text-color);
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
    
    tr.session-expired td {
      background-color: var(--expired-row-bg) !important;
      color: var(--expired-row-color);
    }

    /* Dark mode overrides */
    body.dark-mode {
      --table-header-bg: rgba(67, 97, 238, 0.2);
      --table-row-hover: rgba(67, 97, 238, 0.1);
      --expired-row-bg: #2d2d2d;
      --expired-row-color: #aaa;
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
    }   <i class="fas fa-check-circle"></i> <?= $success ?>
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

    <?php if (isset($_GET['terminated']) && $_GET['terminated'] == '1'): ?>
      <div class="alert alert-info" style="margin-bottom: 1rem;">
        <i class="fas fa-info-circle"></i> You have terminated your session. Please log in again.
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title">Login Logs</h1>
      <div class="page-actions">
        <?php if (has_permission('restore_invoices')): ?>
              <button class="btn btn-outline" id="undoRecentBtn">
                <i class="fas fa-undo"></i> Undo Recent Delete
              </button>
            <?php endif; ?>
            
            <?php if (has_permission('restore_invoices')): ?>
              <button class="btn btn-outline" id="undoAllBtn">
                <i class="fas fa-history"></i> Undo All Deletes
              </button>
            <?php endif; ?>
            
            <?php if (has_permission('download_invoice_pdf')): ?>
              <button class="btn btn-primary" id="exportBtn">
                  <i class="fas fa-file-export"></i> Export to Excel
              </button>
            <?php endif; ?>

      </div>
    </div>

    <div class="table-container">
      <input type="text" id="loginSearch" placeholder="🔍 Search sessions..." class="form-control" style="max-width: 300px; margin-bottom: 1.2rem;">
       </div>
      
      <!-- TABLE WITH SAME STRUCTURE AS history.php -->
      <table id="loginLogsTable">
        <thead>
          <tr>
            <th data-sort="number" style="text-align: center;"><span class="header-text">#</span><span class="sort-indicator"></span></th>
            <th data-sort="string" style="text-align: center;">User <span class="sort-indicator"></span></th>
            <th data-sort="string" style="text-align: center;">Role <span class="sort-indicator"></span></th>
            <th data-sort="string" style="text-align: center;">IP Address <span class="sort-indicator"></span></th>
            <th data-sort="string" style="text-align: center;">Browser <span class="sort-indicator"></span></th>
            <th data-sort="date" style="text-align: center;">Login Time <span class="sort-indicator"></span></th>
            <th data-sort="date" style="text-align: center;">Last Activity <span class="sort-indicator"></span></th>
            <th style="text-align: center;">Status</th>
            <th style="text-align: center;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sessions as $i => $session): ?>
            <?php
              // 1) Is this the logged-in user's session?
                $isOwn      = ($session['session_id'] === session_id());
      
              // 2) Minutes since last activity
                $idleMins   = (int)$session['idle_mins'];      // ← comes from the query

              // FIXED: Never mark current session as expired
                $isExpired  = !$isOwn && ($idleMins > $sessionTimeoutMinutes);
                
                $isTerminated = !empty($session['terminated_at']);
        
              // 4) Can we show "Terminate"? Only if NOT expired AND permission
              $canTerminateThisSession = false;
              if (!$isExpired && !$isTerminated) {
              if ($canTerminateAny  && !$isOwn)             $canTerminateThisSession = true;
              if (!$canTerminateAny && $canTerminateOwn && $isOwn) $canTerminateThisSession = true;
              if ($canTerminateAny  && $canTerminateOwn && $isOwn) $canTerminateThisSession = true;
              }
            ?>
            <tr class="<?= $isExpired ? 'session-expired' : '' ?>">
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($session['full_name'] ?? $session['username']) ?></td>
              <td><?= htmlspecialchars($session['role']) ?></td>
              <td><?= htmlspecialchars($session['ip_address']) ?></td>
              <td style="max-width:300px; word-break:break-word;"><?= htmlspecialchars($session['user_agent']) ?></td>
              <td class="nowrap"><?= date('Y-m-d h:i A', strtotime($session['created_at'])) ?></td>
              <td class="nowrap"><?= date('Y-m-d h:i A', strtotime($session['last_activity'])) ?></td>
              
              <!-- Status -->
              <td>
                <span class="status-badge <?= $isTerminated ? 'status-terminated' : ($isExpired ? 'status-expired' : 'status-active') ?>">
                  <?= $isTerminated 
                      ? ($session['termination_reason'] === 'logout' ? 'Logged Out' : 'Terminated') 
                      : ($isExpired ? 'Expired' : 'Active') ?>
                </span>
              </td>
        
              <!-- Terminate -->
              <td class="actions-cell">
                <?php if ($canTerminateThisSession): ?>
                  <form method="POST" class="terminate-session-form" data-session="<?= htmlspecialchars($session['session_id']) ?>">
                    <input type="hidden" name="terminate_session_id" value="<?= htmlspecialchars($session['session_id']) ?>">
                    <button class="btn btn-danger" type="submit">
                      <i class="fas fa-power-off"></i> Terminate
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div> <!-- close table-container -->
</div> <!-- close card -->
</div> <!-- close max-width wrapper -->


<?php require 'scripts.php'; ?>

<script>
// EXACT SAME SEARCH FUNCTIONALITY AS history.php
document.getElementById('loginSearch').addEventListener('input', function () {
  const query = this.value.toLowerCase().trim();
  const rows = document.querySelectorAll('#loginLogsTable tbody tr');

  rows.forEach(row => {
    let match = false;
    const cells = row.querySelectorAll('td');

    for (let i = 0; i < cells.length - 1; i++) { // Exclude Action column
      if (!cells[i]) continue;
      const cellText = cells[i].textContent.toLowerCase();
      if (cellText.includes(query)) {
        match = true;
        break;
      }
    }

    row.style.display = match ? '' : 'none';
  });
});

// EXACT SAME SORTING FUNCTIONALITY AS history.php
document.querySelectorAll('#loginLogsTable th[data-sort]').forEach((header) => {
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
        
        if (type === 'number') return isAsc ? aVal - bVal : bVal - aVal;
        if (type === 'date') return isAsc ? new Date(aVal) - new Date(bVal) : new Date(bVal) - new Date(aVal);
        return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    rows.forEach(row => tbody.appendChild(row));
  });
});

// EXACT SAME EXPORT FUNCTIONALITY AS history.php
document.getElementById('exportBtn')?.addEventListener('click', function () {
  const rows = document.querySelectorAll('#loginLogsTable tbody tr');
  const headers = Array.from(document.querySelectorAll('#loginLogsTable thead th'))
    .map(th => th.querySelector('.header-text')?.innerText || '')
    .slice(0, -1); // Remove Action column

  let csvContent = headers.join(",") + "\n";

  rows.forEach(row => {
    const cols = Array.from(row.querySelectorAll('td')).slice(0, -1); // Exclude Action column
    const rowData = cols.map(td => {
      let text = td.textContent.trim();
      // Handle commas in data
      return text.includes(',') ? `"${text}"` : text;
    }).join(",");
    csvContent += rowData + "\n";
  });

  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", "login_sessions.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});
</script>

<!-- Terminate Session Modal -->
<div class="modal" id="terminateModal">
  <div class="modal-content">
    <span class="close-modal" onclick="closeTerminateModal()">&times;</span>
    <h2>Confirm Termination</h2>
    <p>Are you sure you want to terminate this session?</p>
    <form id="terminateModalForm" method="POST">
      <input type="hidden" name="terminate_session_id" id="terminateSessionId">
      <div class="btn-group">
        <button type="submit" class="btn btn-danger">Terminate</button>
        <button type="button" class="btn btn-cancel" onclick="closeTerminateModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.terminate-session-form').forEach(form => {
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const sessionId = this.dataset.session;
    document.getElementById('terminateSessionId').value = sessionId;
    document.getElementById('terminateModal').style.display = 'flex';
  });
});

function closeTerminateModal() {
  document.getElementById('terminateModal').style.display = 'none';
}
</script>

<script>
setTimeout(() => {
  const alert = document.querySelector('.alert');
  if (alert) {
    alert.style.transition = "opacity 0.5s ease";
    alert.style.opacity = 0;
    setTimeout(() => alert.remove(), 500);
  }
}, 5000);
</script>


</body>
</html>
<?php ob_end_flush(); ?>