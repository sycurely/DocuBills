<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
ob_start(); // Start output buffering early to allow headers later

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$activeMenu = 'settings';
$activeTab = 'email_templates';
$activeSub = 'existing_templates';

require_once 'config.php';
require_once 'middleware.php'; // ✅ Add middleware

// ✅ Check permission
if (!has_permission('access_email_templates_page')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}
$canAddTemplate = has_permission('add_email_template');
$canEditTemplate = has_permission('edit_email_template');
$canDeleteTemplate = has_permission('delete_email_template');
$canManageNotifTypes = has_permission('manage_notification_categories');

// -------------------------------
// Notification Types (Categories)
// -------------------------------
$notifTypes = [];
$notifTypeMap = [];

try {
    $notifTypes = $pdo->query("
        SELECT id, slug, label, created_at
        FROM notification_types
        WHERE deleted_at IS NULL
           OR deleted_at = ''
           OR deleted_at = '0000-00-00 00:00:00'
        ORDER BY label ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifTypes as $nt) {
        $notifTypeMap[$nt['slug']] = $nt['label'];
    }
} catch (Exception $e) {
    // If table doesn't exist yet, page should still work
    error_log("⚠️ notification_types fetch failed: " . $e->getMessage());
}

function label_from_slug($slug) {
    $label = str_replace(["_", "-"], " ", (string)$slug);
    $label = preg_replace('/\s+/', ' ', trim($label));
    return $label === '' ? '' : ucwords($label);
}

function normalize_slug_value($value) {
    $value = strtolower(trim((string)$value));
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    $value = trim($value, '_');
    $value = preg_replace('/_+/', '_', $value);
    return $value;
}

$slugKeyMap = [];
foreach ($notifTypes as $nt) {
    $key = normalize_slug_value($nt['slug'] ?? '');
    if ($key !== '') {
        $slugKeyMap[$key] = true;
    }
}

// If templates reference a missing category, include it in the list so it shows up here.
try {
    $extraSlugs = $pdo->query("
        SELECT DISTINCT assigned_notification_type
        FROM email_templates
        WHERE (deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')
          AND assigned_notification_type <> ''
    ")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($extraSlugs as $slug) {
        $slug = trim((string)$slug);
        $slugKey = normalize_slug_value($slug);
        if ($slugKey === '' || isset($slugKeyMap[$slugKey])) {
            continue;
        }
        $label = label_from_slug($slug);
        $notifTypes[] = [
            'id' => null,
            'slug' => $slugKey,
            'label' => $label ?: $slug,
            'created_at' => null,
        ];
        $notifTypeMap[$slugKey] = $label ?: $slug;
        $slugKeyMap[$slugKey] = true;
    }
} catch (Exception $e) {
    error_log("notification_types merge failed: " . $e->getMessage());
}

usort($notifTypes, function ($a, $b) {
    return strcasecmp((string)($a['label'] ?? ''), (string)($b['label'] ?? ''));
});

// ✅ Only this block should remain

// ✅ Add Notification Category (Type)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notif_type'])) {
    if (!$canManageNotifTypes) {
        $_SESSION['access_denied'] = true;
        header('Location: access-denied.php');
        exit;
    }

    $label = trim($_POST['notif_label'] ?? '');

    if ($label === '') {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Category name is required."));
        exit;
    }

    // slugify (safe + consistent)
    $slug = strtolower($label);
    $slug = preg_replace('/[^a-z0-9]+/i', '_', $slug);
    $slug = trim($slug, '_');

    if ($slug === '') {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Invalid category name."));
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO notification_types (slug, label, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
              label = VALUES(label),
              deleted_at = NULL,
              updated_at = NOW()
        ");
        $stmt->execute([$slug, $label]);

        header("Location: settings-email-templates-list.php?success=" . urlencode("Category added successfully!"));
        exit;
    } catch (Exception $e) {
        error_log("❌ Add category failed: " . $e->getMessage());
        header("Location: settings-email-templates-list.php?error=" . urlencode("Failed to add category."));
        exit;
    }
}

// ✅ Edit Notification Category (Type)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_notif_type'])) {
    if (!$canManageNotifTypes) {
        $_SESSION['access_denied'] = true;
        header('Location: access-denied.php');
        exit;
    }

    $notif_id = intval($_POST['notif_id'] ?? 0);
    $label = trim($_POST['notif_label'] ?? '');

    if ($notif_id <= 0 || $label === '') {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Invalid category update request."));
        exit;
    }

    // slugify (safe + consistent)
    $slug = strtolower($label);
    $slug = preg_replace('/[^a-z0-9]+/i', '_', $slug);
    $slug = trim($slug, '_');

    if ($slug === '') {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Invalid category name."));
        exit;
    }

    try {
        // Fetch old slug (needed to update templates assigned to this category)
        $stmt = $pdo->prepare("
            SELECT slug
            FROM notification_types
            WHERE id = ?
              AND (deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')
            LIMIT 1
        ");
        $stmt->execute([$notif_id]);
        $oldSlug = $stmt->fetchColumn();

        if (!$oldSlug) {
            header("Location: settings-email-templates-list.php?error=" . urlencode("Category not found."));
            exit;
        }

        $pdo->beginTransaction();

        // Update category
        $stmt = $pdo->prepare("
            UPDATE notification_types
               SET slug = ?, label = ?, updated_at = NOW()
             WHERE id = ?
               AND deleted_at IS NULL
        ");
        $stmt->execute([$slug, $label, $notif_id]);

        // IMPORTANT: if slug changed, update templates so assignments remain intact
        if ($oldSlug !== $slug) {
            $stmt = $pdo->prepare("
                UPDATE email_templates
                   SET assigned_notification_type = ?
                 WHERE assigned_notification_type = ?
            ");
            $stmt->execute([$slug, $oldSlug]);
        }

        $pdo->commit();

        header("Location: settings-email-templates-list.php?success=" . urlencode("Category updated successfully!"));
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("❌ Edit category failed: " . $e->getMessage());
        header("Location: settings-email-templates-list.php?error=" . urlencode("Failed to update category."));
        exit;
    }
}

// ✅ Delete Notification Category (Type) (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notif_type'])) {
    if (!$canManageNotifTypes) {
        $_SESSION['access_denied'] = true;
        header('Location: access-denied.php');
        exit;
    }

    $notif_id = intval($_POST['notif_id'] ?? 0);
    if ($notif_id <= 0) {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Invalid category delete request."));
        exit;
    }

    try {
        // Get slug so we can unassign templates
        $stmt = $pdo->prepare("
            SELECT slug
            FROM notification_types
            WHERE id = ?
              AND (deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')
            LIMIT 1
        ");
        $stmt->execute([$notif_id]);
        $slug = $stmt->fetchColumn();

        if (!$slug) {
            header("Location: settings-email-templates-list.php?error=" . urlencode("Category not found."));
            exit;
        }

        $pdo->beginTransaction();

        // Soft delete category
        $stmt = $pdo->prepare("UPDATE notification_types SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$notif_id]);

        // Unassign templates (so they don't point to deleted slug)
        $stmt = $pdo->prepare("
            UPDATE email_templates
               SET assigned_notification_type = ''
             WHERE assigned_notification_type = ?
        ");
        $stmt->execute([$slug]);

        $pdo->commit();

        header("Location: settings-email-templates-list.php?success=" . urlencode("Category deleted successfully!"));
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("❌ Delete category failed: " . $e->getMessage());
        header("Location: settings-email-templates-list.php?error=" . urlencode("Failed to delete category."));
        exit;
    }
}
    
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del = intval($_POST['delete_id']);

    try {
        $stmt = $pdo->prepare("UPDATE email_templates SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$del]);

        if ($stmt->rowCount()) {
            error_log("✅ Deleted template ID $del");
            header("Location: settings-email-templates-list.php?success=1");
            exit;
        } else {
            error_log("❌ FAILED to delete template ID $del");
            header("Location: settings-email-templates-list.php?error=1");
            exit;
        }
    } catch (Exception $e) {
        error_log("❌ Exception deleting template: " . $e->getMessage());
        header("Location: settings-email-templates-list.php?error=1");
        exit;
    }
}

// ✅ Rename Template (template_name)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_template_name'])) {
    if (!$canEditTemplate) {
        $_SESSION['access_denied'] = true;
        header('Location: access-denied.php');
        exit;
    }

    $template_id = intval($_POST['template_id'] ?? 0);
    $template_name = trim($_POST['template_name'] ?? '');

    if ($template_id <= 0 || $template_name === '') {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Template name is required."));
        exit;
    }

    // Optional safety: keep it reasonable
    if (mb_strlen($template_name) > 120) {
        header("Location: settings-email-templates-list.php?error=" . urlencode("Template name is too long (max 120 characters)."));
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE email_templates
               SET template_name = ?, updated_at = NOW()
             WHERE id = ?
               AND deleted_at IS NULL
        ");
        $stmt->execute([$template_name, $template_id]);

        // If rowCount is 0, it might just be the same name; confirm template still exists
        if ($stmt->rowCount() === 0) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM email_templates WHERE id = ? AND deleted_at IS NULL");
            $chk->execute([$template_id]);
            if ((int)$chk->fetchColumn() === 0) {
                header("Location: settings-email-templates-list.php?error=" . urlencode("Template not found."));
                exit;
            }
        }

        header("Location: settings-email-templates-list.php?success=" . urlencode("Template name updated successfully!"));
        exit;

    } catch (Exception $e) {
        error_log("❌ Rename template failed: " . $e->getMessage());
        header("Location: settings-email-templates-list.php?error=" . urlencode("Failed to update template name."));
        exit;
    }
}

// Fetch categories WITH their latest assigned template (if any)
$rows = [];
try {
    $tplRows = $pdo->query("
        SELECT id, template_name, assigned_notification_type, created_at
        FROM email_templates
        WHERE (deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')
          AND assigned_notification_type <> ''
        ORDER BY assigned_notification_type ASC, created_at DESC, id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $latestBySlug = [];
    $templateCount = [];
    foreach ($tplRows as $tpl) {
        $rawSlug = trim((string)($tpl['assigned_notification_type'] ?? ''));
        $slugKey = normalize_slug_value($rawSlug);
        if ($slugKey === '') {
            continue;
        }
        $templateCount[$slugKey] = ($templateCount[$slugKey] ?? 0) + 1;
        if (!isset($latestBySlug[$slugKey])) {
            $latestBySlug[$slugKey] = $tpl;
        }
    }

    foreach ($notifTypes as $nt) {
        $slug = (string)($nt['slug'] ?? '');
        $slugKey = normalize_slug_value($slug);
        $tpl = $latestBySlug[$slugKey] ?? null;
        $rows[] = [
            'notif_id' => $nt['id'] ?? null,
            'slug' => $slug,
            'label' => $nt['label'] ?? $slug,
            'category_created_at' => $nt['created_at'] ?? null,
            'template_id' => $tpl['id'] ?? null,
            'template_name' => $tpl['template_name'] ?? null,
            'template_created_at' => $tpl['created_at'] ?? null,
            'template_count' => $templateCount[$slugKey] ?? 0,
        ];
    }

    error_log("Categories fetched: " . count($rows));
} catch (Exception $e) {
    error_log("Category+template list query failed: " . $e->getMessage());
    $rows = [];
}

error_log("Rows fetched: " . count($rows));

// Handle messages from redirect
$success = null;
$successIcon = 'fa-check-circle';

if (isset($_GET['success'])) {
    $msg = $_GET['success'];

    if (stripos($msg, 'preview') !== false) {
        $success = htmlspecialchars($msg);
        $successIcon = 'fa-paper-plane'; // ✉️ icon
    } elseif (stripos($msg, 'deleted') !== false || $msg == '1') {
        $success = "Template deleted successfully!";
        $successIcon = 'fa-trash-alt'; // 🗑️ icon
    } else {
        $success = htmlspecialchars($msg);
        $successIcon = 'fa-check-circle'; // ✅ fallback
    }
}

if (isset($_GET['error'])) {
    $error = is_string($_GET['error']) && $_GET['error'] !== '1'
        ? htmlspecialchars($_GET['error'])
        : "Failed to delete template.";
}

// ✅ Do not include sidebar/styles here (they output markup before <!DOCTYPE>)
// They are already included inside the HTML below.
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Templates</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php require 'styles.php'; ?>
  <style>
    /* Matching styles from history.php */
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
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      padding: 0;
      vertical-align: middle;
    }

    .btn-edit {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .btn-delete {
      background: rgba(247, 37, 133, 0.2);
      color: var(--danger);
    }

    .btn-preview {
      background: rgba(248, 150, 30, 0.2);
      color: var(--warning);
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
      text-align: center;
      vertical-align: middle;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.95rem;
      border-bottom: 1px solid var(--border);
    }
    
    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      font-size: 1rem;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      text-align: center;
      padding: 1rem;
      line-height: 1.6rem;
      height: 58px;
    }

    
    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }
    
    .actions-cell {
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
      gap: 0.5rem;
      min-width: 120px;
      min-height: 48px; /* ✅ Force row height even if no content */
      border-bottom: 1px solid var(--border); /* ✅ force visible line */
    }

    .preview-cell {
      text-align: center;
      vertical-align: middle;
      padding: 1rem;
    }

    .search-bar {
      position: relative;
      margin-bottom: 1.5rem;
      max-width: 300px;
    }
    
    .search-bar input {
      height: 40px;
      padding: 0.6rem 1rem 0.6rem 2.25rem;
      font-size: 1rem;
      font-family: 'Inter', sans-serif;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      width: 100%;
      box-sizing: border-box;
    }
    
    .search-bar i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      color: var(--primary); /* Use primary or gradient icon if possible */
      pointer-events: none;
    }
    
    .search-bar input::placeholder {
      color: var(--gray);
      opacity: 1;
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
      padding: 1rem;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.95rem;
      border-bottom: 1px solid var(--border);
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

    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .page-actions {
        flex-wrap: wrap;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .col-span-2 {
        grid-column: span 1;
      }
    }
    
    /* 🔥 Fully remove DataTables default borders and controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      display: none !important;
    }
    
    table.dataTable {
      width: 100% !important;
      border-collapse: collapse !important;
      background: transparent !important;
      margin: 0 !important;
      border: none !important;
    }
    
    table.dataTable thead th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      font-size: 1rem;
      text-align: center !important;
      border-bottom: 2px solid var(--border) !important;
    }
    
    table.dataTable tbody td {
      text-align: center;
      vertical-align: middle;
      border-bottom: 1px solid var(--border) !important;
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
      <i class="fas <?= $successIcon ?>"></i> <?= $success ?>
    </div>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title">Email Templates</h1>
      <div class="page-actions">
          <?php if ($canManageNotifTypes): ?>
            <button type="button" class="btn btn-outline" id="openNotifTypeModal">
              <i class="fas fa-tags"></i> Add Category
            </button>
          <?php endif; ?>
        
          <?php if ($canAddTemplate): ?>
            <a href="manage-email-templates.php" class="btn btn-primary">
              <i class="fas fa-plus"></i> Create Template
            </a>
          <?php endif; ?>
        </div>
    </div>

    <div class="table-container">
      <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom: 1.2rem;">
          <input type="text" id="templateSearch" placeholder="🔍 Search templates..." class="form-control" style="max-width: 300px;">
        
          <select id="categoryFilter" class="form-control" style="max-width: 280px;">
            <option value="">All Categories</option>
            <?php foreach ($notifTypes as $nt): ?>
              <option value="<?= htmlspecialchars($nt['slug']) ?>">
                <?= htmlspecialchars($nt['label']) ?>
              </option>
            <?php endforeach; ?>
          </select>
      </div>
    
      <table id="historyTable">
            <thead>
              <tr>
                <th data-sort="number" style="text-align: center;"><span class="header-text"># </span><span class="sort-indicator"></span></th>
                <th data-sort="string" style="text-align: center;">Name <span class="sort-indicator"></span></th>
                <th data-sort="string" style="text-align: center;">Type <span class="sort-indicator"></span></th>
                <th data-sort="date" style="text-align: center;">Created <span class="sort-indicator"></span></th>
                <th style="text-align: center;">Preview</th>
                <th style="text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $i => $r): ?>
                <?php
                  $displayName = trim((string)($r['template_name'] ?? ''));
                  if ($displayName === '' && !empty($r['template_id'])) {
                    $displayName = 'Template #' . (int)$r['template_id'];
                  }
                ?>
                <tr data-cat="<?= htmlspecialchars($r['slug']) ?>">
            
                  <td data-value="<?= $i+1 ?>"><?= $i+1 ?></td>
            
                  <td>
                    <?php if (!empty($r['template_id'])): ?>
                      <?= htmlspecialchars($displayName) ?>
                      <?php if ((int)$r['template_count'] > 1): ?>
                        <div style="font-size:.85rem;color:var(--gray);margin-top:4px;">
                          (<?= (int)$r['template_count'] ?> templates in this category — showing latest)
                        </div>
                      <?php endif; ?>
                    <?php else: ?>
                      <span style="color: var(--gray); font-style: italic;">Unassigned</span>
                    <?php endif; ?>
                  </td>
            
                  <td><?= htmlspecialchars($r['label']) ?></td>
            
                  <td><?= !empty($r['category_created_at']) ? date('Y-m-d', strtotime($r['category_created_at'])) : '-' ?></td>
            
                  <td class="preview-cell">
                    <?php if (!empty($r['template_id'])): ?>
                      <a href="preview_email_template.php?id=<?= (int)$r['template_id'] ?>"
                         class="btn-icon btn-preview" title="Preview Template">
                        <i class="fas fa-eye"></i>
                      </a>
                    <?php else: ?>
                      <span style="color: var(--gray);">—</span>
                    <?php endif; ?>
                  </td>
            
                  <td class="actions-cell">

                  <?php if (!empty($r['template_id'])): ?>
                
                  <?php if ($canEditTemplate): ?>
                      <button
                        type="button"
                        class="btn-icon btn-edit edit-template-name-btn"
                        title="Rename Template"
                        data-template-id="<?= (int)$r['template_id'] ?>"
                        data-template-name="<?= htmlspecialchars($displayName ?? '', ENT_QUOTES) ?>"
                      >
                        <i class="fas fa-pen-to-square"></i>
                      </button>
                   <?php endif; ?>
                
                   <?php if ($canDeleteTemplate): ?>
                      <button class="btn-icon btn-delete delete-template-btn" title="Delete Template"
                              data-id="<?= (int)$r['template_id'] ?>"
                              data-name="<?= htmlspecialchars($displayName ?? '') ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                   <?php endif; ?>
                
                  <?php else: ?>
                
                    <?php if ($canAddTemplate): ?>
                      <a href="manage-email-templates.php?type=<?= urlencode($r['slug']) ?>"
                         class="btn btn-outline" style="padding:.45rem .75rem; border-radius:10px;">
                        <i class="fas fa-plus"></i> Assign
                      </a>
                    <?php else: ?>
                      <span style="color: var(--gray);">—</span>
                    <?php endif; ?>
                
                  <?php endif; ?>
                
                  <?php if ($canManageNotifTypes && !empty($r['notif_id'])): ?>
                    <!-- Edit Category (always visible) -->
                    <button
                      type="button"
                      class="btn-icon btn-edit edit-category-btn"
                      title="Edit Category"
                      data-notif-id="<?= (int)$r['notif_id'] ?>"
                      data-notif-label="<?= htmlspecialchars($r['label'], ENT_QUOTES) ?>"
                    >
                      <i class="fas fa-pen"></i>
                    </button>
                
                    <!-- Delete Category (always visible) -->
                    <button
                      type="button"
                      class="btn-icon btn-delete delete-category-btn"
                      title="Delete Category"
                      data-notif-id="<?= (int)$r['notif_id'] ?>"
                      data-notif-label="<?= htmlspecialchars($r['label'], ENT_QUOTES) ?>"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  <?php endif; ?>
                
                </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
    </div>
  </div>
</div>

<?php require 'scripts.php'; ?>

<script>
// Live search
const searchInput = document.getElementById('templateSearch');
const categoryFilter = document.getElementById('categoryFilter');

function applyFilters() {
  const q = (searchInput?.value || '').toLowerCase();
  const cat = categoryFilter?.value || '';

  document.querySelectorAll('#historyTable tbody tr').forEach(tr => {
    const rowText = tr.innerText.toLowerCase();
    const rowCat = tr.getAttribute('data-cat') || '';

    const matchText = rowText.includes(q);
    const matchCat = !cat || rowCat === cat;

    tr.style.display = (matchText && matchCat) ? '' : 'none';
  });
}

searchInput?.addEventListener('input', applyFilters);
categoryFilter?.addEventListener('change', applyFilters);

// Sorting functionality
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
        
        if (type === 'number') return isAsc ? aVal - bVal : bVal - aVal;
        if (type === 'date') return isAsc ? new Date(aVal) - new Date(bVal) : new Date(bVal) - new Date(aVal);

        return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    rows.forEach(row => tbody.appendChild(row));
  });
});

// Delete confirmation modal
const deleteModal = document.createElement('div');
deleteModal.className = 'modal';
deleteModal.id = 'deleteModal';
deleteModal.innerHTML = `
  <div class="modal-content">
    <span class="close-modal" id="closeDeleteModal">&times;</span>
    <h2 class="modal-title">Confirm Deletion</h2>
    <div class="confirmation-message">
      Are you sure you want to delete template "<span class="highlight" id="templateName"></span>"?
    </div>
    <p>This action will move the template to Trash Bin and can be restored.</p>
    <form id="deleteForm" method="POST" action="settings-email-templates-list.php">
      <input type="hidden" name="delete_id" id="delete_id" value="">
      <div class="btn-group">
        <button type="button" class="btn btn-cancel" id="cancelDelete">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-trash"></i> Delete Template
        </button>
      </div>
    </form>
  </div>
`;
document.body.appendChild(deleteModal);

// ✅ Edit Category modal
const editCategoryModal = document.createElement('div');
editCategoryModal.className = 'modal';
editCategoryModal.id = 'editCategoryModal';
editCategoryModal.innerHTML = `
  <div class="modal-content">
    <span class="close-modal" id="closeEditCategoryModal">&times;</span>
    <h2 class="modal-title">Edit Category</h2>
    <p style="margin-top:.5rem;color:#6c757d;">Renaming will also update assigned templates automatically.</p>

    <form method="POST" action="settings-email-templates-list.php" style="margin-top: 1.25rem;">
      <input type="hidden" name="edit_notif_type" value="1" />
      <input type="hidden" name="notif_id" id="edit_notif_id" value="" />

      <input
        type="text"
        name="notif_label"
        id="edit_notif_label"
        required
        style="width: 100%; height: 44px; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 1rem; color:#212529; background:#fff;"
      />

      <div class="btn-group">
        <button type="button" class="btn btn-cancel" id="cancelEditCategory">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
`;
document.body.appendChild(editCategoryModal);

// ✅ Rename Template modal
const editTemplateNameModal = document.createElement('div');
editTemplateNameModal.className = 'modal';
editTemplateNameModal.id = 'editTemplateNameModal';
editTemplateNameModal.innerHTML = `
  <div class="modal-content">
    <span class="close-modal" id="closeEditTemplateNameModal">&times;</span>
    <h2 class="modal-title">Rename Template</h2>
    <p style="margin-top:.5rem;color:#6c757d;">This only changes the template display name.</p>

    <div style="margin-top:.75rem; font-size:.9rem;">
      <a href="#" target="_blank" id="openTemplateEditorLink" style="color: var(--primary); text-decoration: none;">
        Open full editor
      </a>
    </div>

    <form method="POST" action="settings-email-templates-list.php" style="margin-top: 1.25rem;">
      <input type="hidden" name="edit_template_name" value="1" />
      <input type="hidden" name="template_id" id="edit_template_id" value="" />

      <input
        type="text"
        name="template_name"
        id="edit_template_name"
        required
        style="width: 100%; height: 44px; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 1rem; color:#212529; background:#fff;"
      />

      <div class="btn-group">
        <button type="button" class="btn btn-cancel" id="cancelEditTemplateName">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
`;
document.body.appendChild(editTemplateNameModal);

// ✅ Delete Category modal
const deleteCategoryModal = document.createElement('div');
deleteCategoryModal.className = 'modal';
deleteCategoryModal.id = 'deleteCategoryModal';
deleteCategoryModal.innerHTML = `
  <div class="modal-content">
    <span class="close-modal" id="closeDeleteCategoryModal">&times;</span>
    <h2 class="modal-title">Delete Category</h2>
    <div class="confirmation-message">
      Are you sure you want to delete category "<span class="highlight" id="deleteCategoryName"></span>"?
    </div>
    <p>This will unassign any templates currently using this category.</p>

    <form method="POST" action="settings-email-templates-list.php" style="margin-top: 1.25rem;">
      <input type="hidden" name="delete_notif_type" value="1" />
      <input type="hidden" name="notif_id" id="delete_notif_id" value="" />

      <div class="btn-group">
        <button type="button" class="btn btn-cancel" id="cancelDeleteCategory">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-trash"></i> Delete Category
        </button>
      </div>
    </form>
  </div>
`;
document.body.appendChild(deleteCategoryModal);
document.addEventListener('click', function (e) {
  // ✅ Delete Template (DataTables-safe)
  const delTplBtn = e.target.closest('.delete-template-btn');
  if (delTplBtn) {
    const templateId = delTplBtn.getAttribute('data-id');
    const templateName = delTplBtn.getAttribute('data-name') || '';

    document.getElementById('delete_id').value = templateId;
    document.getElementById('templateName').textContent = templateName;
    document.getElementById('deleteModal').style.display = 'flex';
    return;
  }
  
  // ✅ Rename Template
  const renameTplBtn = e.target.closest('.edit-template-name-btn');
  if (renameTplBtn) {
    const modal = document.getElementById('editTemplateNameModal');
    if (!modal) return;

    const id = renameTplBtn.dataset.templateId || renameTplBtn.getAttribute('data-template-id') || '';
    let name = renameTplBtn.dataset.templateName || renameTplBtn.getAttribute('data-template-name') || '';
    name = (name || '').trim();

    const idEl = modal.querySelector('#edit_template_id');
    const nameEl = modal.querySelector('#edit_template_name');
    const editorLink = modal.querySelector('#openTemplateEditorLink');

    if (idEl) idEl.value = id;

    if (editorLink) {
      editorLink.href = 'manage-email-templates.php?id=' + encodeURIComponent(id);
    }

    modal.style.display = 'flex';

    requestAnimationFrame(() => {
      if (nameEl) {
        nameEl.value = name;
        nameEl.focus();
        nameEl.select();
      }
    });

    return;
  }

  // ✅ Edit Category (robust: reads label from the table cell if attribute fails)
    const editBtn = e.target.closest('.edit-category-btn');
    if (editBtn) {
      const modal = document.getElementById('editCategoryModal');
      if (!modal) return;
    
      const row = editBtn.closest('tr');
    
      const id =
        editBtn.dataset.notifId ||
        editBtn.getAttribute('data-notif-id') ||
        '';
    
      // Prefer dataset/attribute, but FALLBACK to the "Type" column text (3rd column)
      let label =
        editBtn.dataset.notifLabel ||
        editBtn.getAttribute('data-notif-label') ||
        (row && row.children && row.children[2] ? row.children[2].textContent : '') ||
        '';
    
      label = (label || '').trim();
    
      const idEl = modal.querySelector('#edit_notif_id');
      const labelEl = modal.querySelector('#edit_notif_label');
    
      if (idEl) idEl.value = id;
    
      // Show modal first, then set value on next frame (prevents weird timing issues)
      modal.style.display = 'flex';
    
      requestAnimationFrame(() => {
        if (labelEl) {
          labelEl.value = label;
          labelEl.focus();
          labelEl.select();
        }
      });
    
      return;
    }


  // ✅ Delete Category
  const delCatBtn = e.target.closest('.delete-category-btn');
  if (delCatBtn) {
    document.getElementById('delete_notif_id').value = delCatBtn.getAttribute('data-notif-id');
    document.getElementById('deleteCategoryName').textContent = delCatBtn.getAttribute('data-notif-label') || '';
    document.getElementById('deleteCategoryModal').style.display = 'flex';
    return;
  }

});

// Close handlers (Edit Category)
document.getElementById('cancelEditCategory')?.addEventListener('click', () => {
  document.getElementById('editCategoryModal').style.display = 'none';
});
document.getElementById('closeEditCategoryModal')?.addEventListener('click', () => {
  document.getElementById('editCategoryModal').style.display = 'none';
});
window.addEventListener('click', (e) => {
  if (e.target === document.getElementById('editCategoryModal')) {
    document.getElementById('editCategoryModal').style.display = 'none';
  }
});

// Close handlers (Delete Category)
document.getElementById('cancelDeleteCategory')?.addEventListener('click', () => {
  document.getElementById('deleteCategoryModal').style.display = 'none';
});
document.getElementById('closeDeleteCategoryModal')?.addEventListener('click', () => {
  document.getElementById('deleteCategoryModal').style.display = 'none';
});
window.addEventListener('click', (e) => {
  if (e.target === document.getElementById('deleteCategoryModal')) {
    document.getElementById('deleteCategoryModal').style.display = 'none';
  }
});

// Close handlers (Rename Template)
document.getElementById('cancelEditTemplateName')?.addEventListener('click', () => {
  document.getElementById('editTemplateNameModal').style.display = 'none';
});
document.getElementById('closeEditTemplateNameModal')?.addEventListener('click', () => {
  document.getElementById('editTemplateNameModal').style.display = 'none';
});
window.addEventListener('click', (e) => {
  if (e.target === document.getElementById('editTemplateNameModal')) {
    document.getElementById('editTemplateNameModal').style.display = 'none';
  }
});

// Add Notification Type modal
const notifTypeModal = document.createElement('div');
notifTypeModal.className = 'modal';
notifTypeModal.id = 'notifTypeModal';
notifTypeModal.innerHTML = `
  <div class="modal-content">
    <span class="close-modal" id="closeNotifTypeModal">&times;</span>
    <h2 class="modal-title">Add Notification Category</h2>
    <p style="margin-top: .5rem; color: #6c757d;">This will become available to assign to templates.</p>

    <form method="POST" action="settings-email-templates-list.php" style="margin-top: 1.25rem;">
      <input type="hidden" name="add_notif_type" value="1" />
      <input
        type="text"
        name="notif_label"
        placeholder="e.g., Invoice Overdue - 7 Days"
        required
        style="width: 100%; height: 44px; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 1rem;"
      />

      <div class="btn-group">
        <button type="button" class="btn btn-cancel" id="cancelNotifType">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add Category
        </button>
      </div>
    </form>
  </div>
`;
document.body.appendChild(notifTypeModal);

const openNotifTypeBtn = document.getElementById('openNotifTypeModal');
if (openNotifTypeBtn) {
  openNotifTypeBtn.addEventListener('click', () => {
    document.getElementById('notifTypeModal').style.display = 'flex';
  });
}

document.getElementById('cancelNotifType')?.addEventListener('click', () => {
  document.getElementById('notifTypeModal').style.display = 'none';
});

document.getElementById('closeNotifTypeModal')?.addEventListener('click', () => {
  document.getElementById('notifTypeModal').style.display = 'none';
});

window.addEventListener('click', (e) => {
  if (e.target === document.getElementById('notifTypeModal')) {
    document.getElementById('notifTypeModal').style.display = 'none';
  }
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

document.getElementById('deleteForm').addEventListener('submit', function(e) {
  const id = document.getElementById('delete_id').value;
  if (!id) {
    e.preventDefault();
    alert('Missing template ID.');
    return;
  }
});

// ✅ Moved outside the submit handler
setTimeout(() => {
  const success = document.getElementById('successAlert');
  if (success) success.style.display = 'none';
}, 5000);
</script>

<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
  // ✅ Clean DataTable - no ugly UI
  $('#historyTable').DataTable({
    paging: false,
    info: false,
    searching: false,
    ordering: true,
    destroy: true,
    columnDefs: [
      { targets: 0, orderable: true },
      { targets: -1, orderable: false }
    ]
  });
});
</script>

</body>
</html>
