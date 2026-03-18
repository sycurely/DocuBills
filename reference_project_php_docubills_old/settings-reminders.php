<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$activeMenu = 'settings';
$activeTab  = 'reminder_settings';

require_once 'config.php';
require_once 'middleware.php';

// ✅ Page access
if (!has_permission('manage_reminder_settings')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}

$success = isset($_GET['success']) ? trim($_GET['success']) : null;
$error   = isset($_GET['error']) ? trim($_GET['error']) : null;

// ✅ Load reminders dynamically (fully customizable) with migration support
// We store reminders as an ARRAY of objects: [{id, name, enabled, direction, days, offset_days}]
$reminders = [];
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['invoice_email_reminders']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

function rr_uuid() {
  return bin2hex(random_bytes(8)); // short random id for reminder rows
}

function rr_label($direction, $days) {
    $days = (int)$days;
    if ($direction === 'before') return $days . " day" . ($days === 1 ? "" : "s") . " before the due date";
    if ($direction === 'after')  return $days . " day" . ($days === 1 ? "" : "s") . " after the due date";
    return "On the due date";
}

if ($row && !empty($row['key_value'])) {
  $decoded = json_decode($row['key_value'], true);

  if (is_array($decoded)) {
    // Migration: old format was associative keys (before_due, on_due, after_7, ...)
    $looksLikeOldMap = array_keys($decoded) !== range(0, count($decoded)-1);
    if ($looksLikeOldMap) {
      foreach ($decoded as $oldKey => $v) {
        $dir  = $v['direction'] ?? 'on';
        $days = (int)($v['days'] ?? 0);
        $off  = (int)($v['offset_days'] ?? 0);
        $enabled = (int)($v['enabled'] ?? 1);

        // auto label if no friendly name existed before
        $label = ($dir === 'before') ? "$days day(s) before"
               : (($dir === 'after') ? "$days day(s) after" : "On due date");

        $reminders[] = [
          'id'          => rr_uuid(),
          'name'        => $label,
          'enabled'     => $enabled,
          'direction'   => in_array($dir, ['before','on','after'], true) ? $dir : 'on',
          'days'        => max(0, min(365, $days)),
          'offset_days' => $off
        ];
      }
    } else {
      // Already new format (array of objects)
      foreach ($decoded as $r) {
        if (!is_array($r)) continue;
        $reminders[] = [
          'id'          => $r['id']          ?? rr_uuid(),
          'name'        => trim((string)($r['name'] ?? '')),
          'enabled'     => (int)($r['enabled'] ?? 1),
          'direction'   => in_array(($r['direction'] ?? 'on'), ['before','on','after'], true) ? $r['direction'] : 'on',
          'days'        => max(0, min(365, (int)($r['days'] ?? 0))),
          'offset_days' => (int)($r['offset_days'] ?? 0),
        ];
      }
    }
  }
}

// First-time install: show ONE clean row (no hardcoded cadence)
if (empty($reminders)) {
  $reminders = [[
    'id' => rr_uuid(),
    'name' => 'On due date',
    'enabled' => 1,
    'direction' => 'on',
    'days' => 0,
    'offset_days' => 0
  ]];
}

// ✅ Load available email templates (exclude deleted)
$emailTemplates = $pdo->query("
    SELECT id, template_name
    FROM email_templates
    WHERE deleted_at IS NULL
       OR deleted_at = ''
       OR deleted_at = '0000-00-00 00:00:00'
    ORDER BY template_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Dynamic template mapping keyed by reminder ID (no fixed keys)
$templateMap = []; // ['<reminder_id>' => <template_id>]
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmt->execute(['invoice_email_reminder_templates']);
$rowTpl = $stmt->fetch(PDO::FETCH_ASSOC);

if ($rowTpl && !empty($rowTpl['key_value'])) {
  $decodedTpl = json_decode($rowTpl['key_value'], true);
  if (is_array($decodedTpl)) {
    // keep only scalar ids
    foreach ($decodedTpl as $rid => $tplId) {
      $rid = (string)$rid;
      $templateMap[$rid] = (string)$tplId;
    }
  }
}

// ✅ Load clients list for "Clients" column (multi-select)
$clients = $pdo->query("
    SELECT id, company_name
    FROM clients
    WHERE deleted_at IS NULL
    ORDER BY company_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Load client mapping per reminder (key: reminder_id → 'all' OR array of client IDs)
$clientMap = [];
$stmtCli = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmtCli->execute(['invoice_email_reminder_clients']);
$rowCli = $stmtCli->fetch(PDO::FETCH_ASSOC);

if ($rowCli && !empty($rowCli['key_value'])) {
    $decodedCli = json_decode($rowCli['key_value'], true);
    if (is_array($decodedCli)) {
        foreach ($decodedCli as $rid => $val) {
            $rid = (string)$rid;
            if ($val === 'all' || $val === '' || $val === null) {
                $clientMap[$rid] = 'all';
            } elseif (is_array($val)) {
                $clientMap[$rid] = array_map('intval', $val);
            } else {
                // fallback for older formats like "1,2,3"
                $ids = array_filter(array_map('intval', explode(',', (string)$val)));
                $clientMap[$rid] = $ids ?: 'all';
            }
        }
    }
}

// ✅ Allow same-day repeat reminders (global toggle)
$allowSameDay = false;
$stmtAllow = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
$stmtAllow->execute(['invoice_email_reminders_allow_same_day']);
$rowAllow = $stmtAllow->fetch(PDO::FETCH_ASSOC);
if ($rowAllow && $rowAllow['key_value'] !== null) {
  $raw = strtolower(trim((string)$rowAllow['key_value']));
  $allowSameDay = in_array($raw, ['1', 'true', 'yes', 'on'], true);
}
$limitSameDay = !$allowSameDay;

// ✅ Save handler
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
      $posted = $_POST['reminders'] ?? []; // posted as reminders[ROWID][field]
      $newReminders = [];
    
      foreach ($posted as $rowId => $r) {
        $id   = trim((string)($r['id'] ?? ''));
        $name = trim((string)($r['name'] ?? ''));
        if ($id === '') $id = bin2hex(random_bytes(8));
        if ($name === '') $name = rr_label($direction, $days);
    
        $enabled   = isset($r['enabled']) ? 1 : 0;
        $direction = in_array(($r['direction'] ?? 'on'), ['before','on','after'], true) ? $r['direction'] : 'on';
        $days      = max(0, min(365, (int)($r['days'] ?? 0)));
    
        // compute offset_days
        if ($direction === 'before') {
          $offset = -abs($days);
        } elseif ($direction === 'after') {
          $offset = abs($days);
        } else {
          $days = 0;
          $offset = 0;
        }
    
        $newReminders[] = [
          'id'          => $id,
          'name'        => $name,
          'enabled'     => $enabled,
          'direction'   => $direction,
          'days'        => (int)$days,
          'offset_days' => (int)$offset
        ];
      }
    
      $json = json_encode($newReminders, JSON_UNESCAPED_SLASHES);
    
      // upsert settings row
      $check = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
      $check->execute(['invoice_email_reminders']);
    
      if ($check->fetch()) {
        $upd = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
        $upd->execute([$json, 'invoice_email_reminders']);
      } else {
        $ins = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
        $ins->execute(['invoice_email_reminders', $json]);
      }
    
      // ✅ Save template mapping (dynamic by reminder id)
      $postedTpl = $_POST['template_map'] ?? []; // ['reminder_id' => 'template_id']
      $newTplMap = [];
    
      // allowed template IDs (security)
      $allowedTemplateIds = array_map(fn($t) => (int)$t['id'], $emailTemplates);
    
      foreach ($postedTpl as $rid => $val) {
        $rid = trim((string)$rid);
        $val = trim((string)$val);
    
        if ($val === '' || $val === '0') {
          $newTplMap[$rid] = '';
          continue;
        }
    
        $id = (int)$val;
        if (in_array($id, $allowedTemplateIds, true)) {
          $newTplMap[$rid] = (string)$id;
        } else {
          $newTplMap[$rid] = '';
        }
      }
    
      $jsonTpl = json_encode($newTplMap, JSON_UNESCAPED_SLASHES);
    
      // upsert invoice_email_reminder_templates
      $checkTpl = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
      $checkTpl->execute(['invoice_email_reminder_templates']);
    
      if ($checkTpl->fetch()) {
        $updTpl = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
        $updTpl->execute([$jsonTpl, 'invoice_email_reminder_templates']);
      } else {
        $insTpl = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
        $insTpl->execute(['invoice_email_reminder_templates', $jsonTpl]);
      }
      
      // ✅ Save client mapping per reminder (multi-select)
      $postedClients  = $_POST['client_map'] ?? []; // ['reminder_id' => [client_ids...]]
      $clientMapData  = [];

      foreach ($postedClients as $rid => $vals) {
        $rid = trim((string)$rid);

        if (!is_array($vals)) {
          $vals = [$vals];
        }

        // Normalize values
        $vals = array_unique(array_map('trim', $vals));

        // If “All Clients” chosen or nothing selected → treat as 'all'
        if (empty($vals) || in_array('all', $vals, true)) {
          $clientMapData[$rid] = 'all';
        } else {
          $ids = [];
          foreach ($vals as $v) {
            if (ctype_digit($v)) {
              $ids[] = (int)$v;
            }
          }
          $clientMapData[$rid] = empty($ids) ? 'all' : array_values($ids);
        }
      }

      $jsonClients = json_encode($clientMapData, JSON_UNESCAPED_SLASHES);

      $checkCli = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
      $checkCli->execute(['invoice_email_reminder_clients']);

      if ($checkCli->fetch()) {
        $updCli = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
        $updCli->execute([$jsonClients, 'invoice_email_reminder_clients']);
      } else {
        $insCli = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
        $insCli->execute(['invoice_email_reminder_clients', $jsonClients]);
      }

      // ✅ Save allow same-day resend setting
      $limitSameDayValue = (($_POST['limit_same_day'] ?? '0') === '1');
      $allowSameDayValue = $limitSameDayValue ? '0' : '1';
      $checkAllow = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
      $checkAllow->execute(['invoice_email_reminders_allow_same_day']);

      if ($checkAllow->fetch()) {
        $updAllow = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
        $updAllow->execute([$allowSameDayValue, 'invoice_email_reminders_allow_same_day']);
      } else {
        $insAllow = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
        $insAllow->execute(['invoice_email_reminders_allow_same_day', $allowSameDayValue]);
      }
    
      header("Location: settings-reminders.php?success=1");
      exit;
    }

function reminder_label($direction, $days) {
    return rr_label($direction, $days);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reminder Settings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php require 'styles.php'; ?>
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
    }

    .app-container { display: flex; min-height: 100vh; }
    .main-content {
      flex: 1;
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }
    .page-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 2rem;
    }
    .page-title { font-size: 1.8rem; font-weight: 700; color: var(--primary); }
    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
      padding: 2rem;
      margin-bottom: 1.5rem;
    }
    .form-section {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    .form-section-title {
      font-weight: 600; color: var(--primary);
      margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;
    }
    .btn {
      padding: 0.8rem 1.5rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 1rem;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--secondary); box-shadow: var(--shadow-hover); }

    .reminders-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: visible;
    }
    .reminders-table th, .reminders-table td {
      border-bottom: 1px solid var(--border);
      padding: 0.9rem;
      vertical-align: middle;
    }
    .reminders-table th {
      background: rgba(67,97,238,0.08);
      color: var(--dark);
      font-weight: 700;
      text-align: center;
    }
    .reminders-table td small { color: var(--gray); display: block; margin-top: 6px; }
    .toggle {
      width: 18px; height: 18px;
      transform: translateY(2px);
    }
    .select, .number {
      width: 100%;
      padding: 0.7rem 0.9rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: var(--card-bg);
      color: var(--dark);
      font-size: 1rem;
      transition: var(--transition);
    }
    
        /* Hide the real multi-select – we'll control it via a nice fake dropdown */
    .clients-select {
      position: absolute;
      left: -9999px;
      width: 1px;
      height: 1px;
      opacity: 0;
      pointer-events: none;
    }

    /* Wrapper for pretty Clients dropdown */
    .clients-dropdown {
      position: relative;
      width: 100%;
    }

    .clients-dropdown-toggle {
      width: 100%;
      padding: 0.7rem 0.9rem;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      background: var(--card-bg);
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.95rem;
      color: var(--dark);
      cursor: pointer;
      transition: var(--transition);
    }

    .clients-dropdown-toggle:hover {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67,97,238,0.12);
    }

    .clients-dropdown-toggle i {
      font-size: 0.8rem;
      color: var(--gray);
    }

    .clients-dropdown-menu {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      margin-top: 4px;
      max-height: 220px;
      overflow-y: auto;
      box-shadow: var(--shadow);
      z-index: 50;
      padding: 0.4rem 0;
      display: none;
    }

    .clients-dropdown.open .clients-dropdown-menu {
      display: block;
    }

    .clients-dropdown-option {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 0.35rem 0.8rem;
      cursor: pointer;
      font-size: 0.9rem;
    }

    .clients-dropdown-option:hover {
      background: rgba(67,97,238,0.06);
    }

    .clients-dropdown-option input {
      margin: 0;
    }

    .clients-dropdown-label-text {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }


    .select:focus, .number:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    .badge-preview {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 0.35rem 0.65rem;
      border-radius: 999px;
      background: rgba(76, 201, 240, 0.18);
      border: 1px solid rgba(76, 201, 240, 0.5);
      color: #067a92;
      font-weight: 600;
      font-size: 0.9rem;
      margin-top: 6px;
    }
    
    /* ✅ Templates table: show same pill as Preview */
    .template-reminder-wrap {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    
    .template-reminder-wrap .badge-preview {
      margin-top: 0 !important;   /* keep it tight in the Reminder column */
      width: fit-content;
    }
    
    /* ✅ Reduce dropdown width so Reminder pill has more space */
    .template-select {
      max-width: 420px;   /* adjust if you want more/less */
      width: 100%;
    }
    /* ✅ Templates table: balanced spacing + centered Template column */
    .templates-table {
      table-layout: fixed;
    }
    
    /* Even, comfortable gap between the two columns */
    .templates-table th:first-child,
    .templates-table td:first-child {
      padding-right: 0.9rem !important;
    }
    
    .templates-table th:last-child,
    .templates-table td:last-child {
      padding-left: 0.9rem !important;
    }
    
    /* Center-align the Template column (header + content) */
    .templates-table th:last-child,
    .templates-table td:last-child {
      text-align: center !important;
    }
    
    /* Center the dropdown itself */
    .templates-table td:last-child .template-select {
      display: block;
      margin: 0 auto;
    }
    
    /* Center the helper text under the dropdown */
    .templates-table td:last-child small {
      text-align: center;
    }
    @media (max-width: 900px) {
      .template-select { max-width: 100%; }
    }
    
    @media (max-width: 900px) {
      .reminders-table, .reminders-table thead, .reminders-table tbody, .reminders-table th, .reminders-table td, .reminders-table tr {
        display: block;
      }
      .reminders-table thead { display: none; }
      .reminders-table tr { border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 12px; overflow: visible; }
      .reminders-table td { border: none; border-bottom: 1px solid var(--border); }
      .reminders-table td:last-child { border-bottom: none; }
      .cell-label { font-weight: 700; color: var(--gray); display: block; margin-bottom: 6px; }
    }
    
    /* Keep preview + remove in one line and vertically centered */
    .preview-inline { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    
    /* Remove accidental vertical push from previous margins */
    .preview-inline .badge-preview { margin-top: 0 !important; }
    .preview-inline .btn { margin-top: 0 !important; }
    
    /* Hard-center the Preview cell vertically */
    .reminders-table td.preview-cell { 
      display: flex; 
      align-items: center; 
    }
    
    /* Ensure the whole row prefers middle alignment (override any global) */
    .reminders-table th,
    .reminders-table td { 
      vertical-align: middle !important; 
    }
    
    /* Keep preview content inline and tidy */
    .preview-inline { 
      display: inline-flex; 
      align-items: center; 
      gap: 10px; 
    }
    .preview-inline .badge-preview,
    .preview-inline .btn { 
      margin-top: 0 !important; 
    }

    /* ✅ Compact, nicer Remove button (Preview column) */
    .btn.btn-remove {
      padding: 0 !important;
      width: 36px;
      height: 36px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0 !important;
    
      background: rgba(247, 37, 133, 0.10);
      border: 1px solid rgba(247, 37, 133, 0.28);
      color: var(--danger);
    
      font-size: 0.95rem;
      line-height: 1;
    }
    
    .btn.btn-remove i { margin: 0 !important; }
    
    .btn.btn-remove:hover {
      background: rgba(247, 37, 133, 0.16);
      border-color: rgba(247, 37, 133, 0.38);
      box-shadow: var(--shadow-hover);
      transform: translateY(-1px);
    }
    
    .btn.btn-remove:active { transform: translateY(0); }
    
    /* ✅ Prevent the button from squeezing the pill */
    .preview-inline .badge-preview {
      flex: 1 1 auto;
      min-width: 0;
    }
    .preview-inline .btn-remove { flex: 0 0 auto; }
    
    /* Slightly smaller on mobile */
    @media (max-width: 600px) {
      .btn.btn-remove { width: 32px; height: 32px; }
    }

    /* 1) Remove per-cell separators */
    .reminders-table td,
    .reminders-table th {
      border-bottom: 0 !important;
    }
    
    /* Keep a clear line under the header */
    .reminders-table thead th {
      box-shadow: inset 0 -1px 0 var(--border);
    }
    
    /* 2) One unbroken separator per body row */
    .reminders-table tbody tr {
      box-shadow: inset 0 -1px 0 var(--border);
    }
    
    /* Optional: no extra line under the last row */
    .reminders-table tbody tr:last-child {
      box-shadow: none;
    }

  </style>
</head>

<body>
<?php require 'header.php'; ?>
<div class="app-container">
  <?php require 'sidebar.php'; ?>

  <div class="main-content">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" id="successAlert" style="background: rgba(76, 201, 240, 0.2); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
        <strong>Reminder settings updated successfully!</strong>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="background: rgba(247, 37, 133, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 1rem; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
        <strong><?= htmlspecialchars($error) ?></strong>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-bell"></i> Reminder Settings</h1>
    </div>

    <div class="card">
      <form method="post">

        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-envelope-open-text"></i> Email Reminders (Cadence)</h2>

          <table class="reminders-table">
            <thead>
              <tr>
                <th style="width: 110px;">Enabled</th>
                <th style="width: 220px;">When</th>
                <th style="width: 120px;">Days</th>
                <th style="width: 260px;">Clients</th>
                <th>Preview</th>
              </tr>
            </thead>
           <tbody id="reminderRows">
              <?php foreach ($reminders as $r):
                $rid = (string)$r['id'];
                $name = (string)$r['name'];
                $dir = (string)$r['direction'];
                $days = (int)$r['days'];
                $enabled = (int)$r['enabled'];
                $preview = reminder_label($dir, $days);
                $preview = reminder_label($dir, $days);
                $selectedClients = $clientMap[$rid] ?? 'all';
              ?>
                <tr data-reminder-row="<?= htmlspecialchars($rid) ?>">
                    <td>
                      <span class="cell-label" style="display:none;">Enabled</span>
                      <input class="toggle" type="checkbox"
                        name="reminders[<?= htmlspecialchars($rid) ?>][enabled]"
                        <?= $enabled ? 'checked' : '' ?>>
                      <input type="hidden" name="reminders[<?= htmlspecialchars($rid) ?>][id]" value="<?= htmlspecialchars($rid) ?>">
                    </td>
                
                    <td>
                      <span class="cell-label" style="display:none;">When</span>
                      <select class="select reminder-direction" name="reminders[<?= htmlspecialchars($rid) ?>][direction]">
                          <option value="before" <?= $dir === 'before' ? 'selected' : '' ?>>Before due date</option>
                          <option value="on"     <?= $dir === 'on'     ? 'selected' : '' ?>>On due date</option>
                          <option value="after"  <?= $dir === 'after'  ? 'selected' : '' ?>>After due date</option>
                       </select>
                      <small class="reminder-when-label"><?= htmlspecialchars($preview) ?></small>
                        <input type="hidden"
                               class="reminder-name-input"
                               name="reminders[<?= htmlspecialchars($rid) ?>][name]"
                               value="<?= htmlspecialchars($preview) ?>">
                    </td>
                
                    <td>
                      <span class="cell-label" style="display:none;">Days</span>
                      <input class="number reminder-days" type="number" min="0" max="365"
                        name="reminders[<?= htmlspecialchars($rid) ?>][days]"
                        value="<?= htmlspecialchars((string)$days) ?>">
                      <small>0–365</small>
                    </td>
                
                    <td>
                      <span class="cell-label" style="display:none;">Clients</span>

                      <!-- Pretty dropdown UI (built from JS using the hidden select below) -->
                      <div class="clients-dropdown" data-reminder-id="<?= htmlspecialchars($rid) ?>"></div>

                      <!-- Hidden real multi-select used for form submission -->
                      <select class="select clients-select"
                              name="client_map[<?= htmlspecialchars($rid) ?>][]"
                              multiple>
                        <option value="all" <?= ($selectedClients === 'all') ? 'selected' : '' ?>>
                          All Clients
                        </option>
                        <?php foreach ($clients as $c):
                          $cid = (int)$c['id'];
                          $isSelected = is_array($selectedClients) && in_array($cid, $selectedClients, true);
                        ?>
                          <option value="<?= $cid ?>" <?= $isSelected ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['company_name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>

                      <small>“All Clients” use this cadence globally.</small>
                    </td>
                
                    <td class="preview-cell">
                      <span class="cell-label" style="display:none;">Preview</span>
                      <div class="preview-inline">
                        <div class="badge-preview">
                          <i class="fas fa-clock"></i>
                          <span class="reminder-preview-text"><?= htmlspecialchars($preview) ?></span>
                        </div>
                        <button type="button"
                            class="btn btn-remove"
                            onclick="removeReminderRow('<?= htmlspecialchars($rid) ?>')"
                            title="Remove reminder"
                            aria-label="Remove reminder">
                        <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div style="margin-top:10px;">
              <button type="button" class="btn btn-primary" onclick="addReminderRow()">
                <i class="fas fa-plus"></i> Add Reminder
              </button>
            </div>

          <div style="margin-top:12px; display:flex; align-items:center; gap:8px;">
            <input type="hidden" name="limit_same_day" value="0">
            <input type="checkbox" id="limit_same_day" name="limit_same_day" value="1" <?= $limitSameDay ? 'checked' : '' ?>>
            <label for="limit_same_day">Allow only one reminder email per invoice per day</label>
          </div>
          <small style="color: var(--gray); display:block; margin-top:6px;">
            When enabled, the system limits reminders to one email per invoice per day.
          </small>
        </div>
        
        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-layer-group"></i> Email Reminder Templates</h2>
        
          <p style="margin-top:-6px; color: var(--gray);">
            Select which saved template should be used for each reminder.<br><br>
          </p>
        
          <table class="reminders-table templates-table">
            <thead>
              <tr>
                  <th style="width: 340px;">Reminder</th>
                  <th>Template</th>
                </tr>
            </thead>
            <tbody id="templateRows">
              <?php foreach ($reminders as $r):
                $rid = (string)$r['id'];
                $label = reminder_label((string)$r['direction'], (int)$r['days']);
                $selectedId = $templateMap[$rid] ?? '';
              ?>
              <tr data-template-row="<?= htmlspecialchars($rid) ?>">
                <td>
                  <div class="template-reminder-wrap">
                    <div class="badge-preview">
                      <i class="fas fa-clock"></i>
                      <span class="template-reminder-label"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <small>Template used when this reminder triggers.</small>
                  </div>
                </td>
                <td>
                  <select class="select template-select" name="template_map[<?= htmlspecialchars($rid) ?>]">
                    <option value="">— Select a template —</option>
                    <?php foreach ($emailTemplates as $tpl): ?>
                      <option value="<?= (int)$tpl['id'] ?>" <?= ((string)$selectedId === (string)$tpl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tpl['template_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <small>Manage templates in: <strong>Settings → Existing Templates</strong></small>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div style="margin-top: 2rem;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Reminder Settings
          </button>
        </div>

      </form>
    </div>

  </div>
</div>

<?php require 'scripts.php'; ?>

<script>
  function buildPreview(direction, days) {
    days = parseInt(days || "0", 10);
    if (direction === 'before') return `${days} day${days === 1 ? '' : 's'} before the due date`;
    if (direction === 'after')  return `${days} day${days === 1 ? '' : 's'} after the due date`;
    return `On the due date`;
  }

  function syncRow(row) {
    const dirEl = row.querySelector('.reminder-direction');
    const daysEl = row.querySelector('.reminder-days');
    const previewEl = row.querySelector('.reminder-preview-text');

    const dir = dirEl.value;
    if (dir === 'on') {
      daysEl.value = 0;
      daysEl.disabled = true;
      daysEl.style.opacity = "0.7";
    } else {
      daysEl.disabled = false;
      daysEl.style.opacity = "1";
    }

    const labelText = buildPreview(dir, daysEl.value);

    // 1) Update preview badge
    previewEl.textContent = labelText;
    
    // 2) Update small text under "When" + hidden posted name
    const underWhen = row.querySelector('.reminder-when-label');
    if (underWhen) underWhen.textContent = labelText;
    
    const nameInput = row.querySelector('.reminder-name-input');
    if (nameInput) nameInput.value = labelText;
    
    // 3) Update the Reminder label inside the Templates table (live sync)
    const rid = row.getAttribute('data-reminder-row');
    if (rid) {
      const tplLabel = document.querySelector(`tr[data-template-row="${rid}"] .template-reminder-label`);
      if (tplLabel) tplLabel.textContent = labelText;
    }
  }

  // Initialize existing reminder rows (preview text)
  document.querySelectorAll('tr[data-reminder-row]').forEach(row => {
    const dirEl = row.querySelector('.reminder-direction');
    const daysEl = row.querySelector('.reminder-days');

    // init
    syncRow(row);

    dirEl.addEventListener('change', () => syncRow(row));
    daysEl.addEventListener('input', () => syncRow(row));
  });

  // Auto-hide success alert after 5 seconds
  setTimeout(() => {
    const success = document.getElementById('successAlert');
    if (success) success.style.display = 'none';
  }, 5000);
  
  function uid() {
    // simple client id to avoid collisions; server will recheck
    return 'r' + Math.random().toString(16).slice(2);
  }
  
  const TEMPLATE_OPTIONS_HTML = <?php
  ob_start();
  foreach ($emailTemplates as $tpl) {
    $id = (int)$tpl['id'];
    $name = htmlspecialchars($tpl['template_name'], ENT_QUOTES, 'UTF-8');
    echo "<option value=\"{$id}\">{$name}</option>";
  }
  $options = ob_get_clean();
  echo json_encode($options);
?>;

  // -------------------------------
  // Clients dropdown (nice UI)
  // -------------------------------
  function initClientsDropdown(selectEl) {
    const dropdown = selectEl.previousElementSibling;
    if (!dropdown || !dropdown.classList.contains('clients-dropdown')) return;

    const options = Array.from(selectEl.options);

    dropdown.innerHTML = `
      <button type="button" class="clients-dropdown-toggle">
        <span class="clients-dropdown-label-text"></span>
        <i class="fas fa-chevron-down"></i>
      </button>
      <div class="clients-dropdown-menu"></div>
    `;

    const toggle     = dropdown.querySelector('.clients-dropdown-toggle');
    const menu       = dropdown.querySelector('.clients-dropdown-menu');
    const labelSpan  = dropdown.querySelector('.clients-dropdown-label-text');

    // Build checkbox list from <select> options
    options.forEach(function (opt) {
      const row = document.createElement('div');
      row.className = 'clients-dropdown-option';
      row.innerHTML = `
        <label style="display:flex;align-items:center;gap:6px;width:100%;">
          <input type="checkbox" value="${opt.value}">
          <span>${opt.textContent}</span>
        </label>
      `;
      const cb = row.querySelector('input');
      cb.checked = opt.selected;
      menu.appendChild(row);

      cb.addEventListener('change', function () {
        if (opt.value === 'all') {
          // If "All Clients" checked → uncheck all others
          if (cb.checked) {
            menu.querySelectorAll('input[type="checkbox"]').forEach(function (other) {
              if (other !== cb) other.checked = false;
            });
          }
        } else {
          // If a specific client checked → uncheck "All Clients"
          const allCb = menu.querySelector('input[type="checkbox"][value="all"]');
          if (allCb && cb.checked) {
            allCb.checked = false;
          }
        }
        syncClientsSelectFromMenu(selectEl, menu);
        updateClientsLabel(selectEl, menu, labelSpan);
      });
    });

    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = dropdown.classList.contains('open');
      // Close any other open dropdowns
      document.querySelectorAll('.clients-dropdown.open').forEach(function (d) {
        if (d !== dropdown) d.classList.remove('open');
      });
      if (!isOpen) {
        dropdown.classList.add('open');
      } else {
        dropdown.classList.remove('open');
      }
    });

    // Initial sync (in case DB already has 'all' or specific clients)
    syncMenuFromClientsSelect(selectEl, menu);
    updateClientsLabel(selectEl, menu, labelSpan);
  }

  function syncClientsSelectFromMenu(selectEl, menu) {
    const checkedValues = Array.from(menu.querySelectorAll('input[type="checkbox"]:checked'))
      .map(cb => cb.value);

    Array.from(selectEl.options).forEach(opt => {
      opt.selected = checkedValues.includes(opt.value);
    });
  }

  function syncMenuFromClientsSelect(selectEl, menu) {
    const selectedValues = Array.from(selectEl.options)
      .filter(opt => opt.selected)
      .map(opt => opt.value);

    Array.from(menu.querySelectorAll('input[type="checkbox"]')).forEach(cb => {
      cb.checked = selectedValues.includes(cb.value);
    });

    // If nothing selected, default to "all"
    if (!selectedValues.length) {
      const allCb = menu.querySelector('input[type="checkbox"][value="all"]');
      if (allCb) allCb.checked = true;
    }
  }

  function updateClientsLabel(selectEl, menu, labelSpan) {
    const allCb = menu.querySelector('input[type="checkbox"][value="all"]');
    if (allCb && allCb.checked) {
      labelSpan.textContent = 'All Clients';
      return;
    }

    const selected = Array.from(menu.querySelectorAll('input[type="checkbox"]:checked'))
      .filter(cb => cb.value !== 'all');

    if (!selected.length) {
      labelSpan.textContent = 'All Clients';
      if (allCb) allCb.checked = true;
      return;
    }

    const names = selected.map(cb => cb.parentElement.querySelector('span').textContent);

    if (names.length === 1) {
      labelSpan.textContent = names[0];
    } else if (names.length === 2) {
      labelSpan.textContent = `${names[0]}, ${names[1]}`;
    } else {
      labelSpan.textContent = `${names[0]}, ${names[1]} + ${names.length - 2} more`;
    }
  }

  // Close all clients dropdowns when clicking outside
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.clients-dropdown.open').forEach(function (drop) {
      if (!drop.contains(e.target)) {
        drop.classList.remove('open');
      }
    });
  });

  // Initialize dropdowns for existing rows
  document.querySelectorAll('.clients-select').forEach(function (selectEl) {
    initClientsDropdown(selectEl);
  });

  // -------------------------------
  // Add / remove reminder rows
  // -------------------------------
  function addReminderRow() {
    const rid = uid();
    const tbody = document.getElementById('reminderRows');
    const tr = document.createElement('tr');
    tr.setAttribute('data-reminder-row', rid);
    tr.innerHTML = `
      <td>
        <input class="toggle" type="checkbox" name="reminders[${rid}][enabled]" checked>
        <input type="hidden" name="reminders[${rid}][id]" value="${rid}">
      </td>
      <td>
        <select class="select reminder-direction" name="reminders[${rid}][direction]">
          <option value="before">Before due date</option>
          <option value="on" selected>On due date</option>
          <option value="after">After due date</option>
        </select>
        <small class="reminder-when-label">On the due date</small>
            <input type="hidden" class="reminder-name-input" name="reminders[${rid}][name]" value="On the due date">
      </td>
      <td>
        <input class="number reminder-days" type="number" min="0" max="365"
               name="reminders[${rid}][days]" value="0">
        <small>0–365</small>
      </td>
      <td>
        <div class="clients-dropdown" data-reminder-id="${rid}"></div>
        <select class="select clients-select"
                name="client_map[${rid}][]"
                multiple>
          <option value="all" selected>All Clients</option>
<?php foreach ($clients as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option>
<?php endforeach; ?>
        </select>
        <small>“All Clients” use this cadence globally.</small>
      </td>
      <td class="preview-cell">
        <div class="preview-inline">
          <div class="badge-preview">
            <i class="fas fa-clock"></i>
            <span class="reminder-preview-text">On the due date</span>
          </div>
          <button type="button"
                    class="btn btn-remove"
                    onclick="removeReminderRow('${rid}')"
                    title="Remove reminder"
                    aria-label="Remove reminder">
              <i class="fas fa-times"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
    
    // Also add matching row in the Templates table
    const tplTbody = document.getElementById('templateRows');
    if (tplTbody) {
      const tplTr = document.createElement('tr');
      tplTr.setAttribute('data-template-row', rid);
      tplTr.innerHTML = `
      <td>
        <div class="template-reminder-wrap">
          <div class="badge-preview">
            <i class="fas fa-clock"></i>
            <span class="template-reminder-label">On the due date</span>
          </div>
          <small>Template used when this reminder triggers.</small>
        </div>
      </td>
      <td>
        <select class="select template-select" name="template_map[${rid}]">
          <option value="">— Select a template —</option>
          ${TEMPLATE_OPTIONS_HTML}
        </select>
        <small>Manage templates in: <strong>Settings → Existing Templates</strong></small>
      </td>
    `;
      tplTbody.appendChild(tplTr);
    }

    // Wire up preview behavior
    const dirEl = tr.querySelector('.reminder-direction');
    const daysEl = tr.querySelector('.reminder-days');
    function sync() { syncRow(tr); }
    dirEl.addEventListener('change', sync);
    daysEl.addEventListener('input', sync);
    sync();

    // Initialize the Clients dropdown for the new row
    const clientsSelect = tr.querySelector('.clients-select');
    if (clientsSelect) {
      initClientsDropdown(clientsSelect);
    }
  }

  function removeReminderRow(rid) {
  const row = document.querySelector(`tr[data-reminder-row="${rid}"]`);
  if (row) row.remove();

  const tplRow = document.querySelector(`tr[data-template-row="${rid}"]`);
  if (tplRow) tplRow.remove();
  }
</script>
</body>
</html>
