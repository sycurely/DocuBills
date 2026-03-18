<?php
require_once 'config.php';
session_start();
require_once 'middleware.php';

// ✅ Block access if user lacks permission
if (!has_permission('edit_email_template')) {
  $_SESSION['access_denied'] = true;
  header('Location: access-denied.php');
  exit;
}


// ✅ Basic request check
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['id'])) {
    header('Location: manage-email-templates.php');
    exit;
}

function normalize_email_list_or_error($raw, $label, &$errors, $max = 10) {
    $raw = (string)$raw;

    // Allow commas, semicolons, new lines
    $raw = str_replace(["\r", "\n", "\t", ";"], [",", ",", ",", ","], $raw);

    $parts = array_filter(array_map('trim', explode(',', $raw)));

    // If user left it blank, that's OK
    if (empty($parts)) return '';

    $unique = [];
    foreach ($parts as $email) {
        if ($email === '') continue;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "{$label} has an invalid email: {$email}";
            continue;
        }

        // Deduplicate case-insensitively
        $unique[strtolower($email)] = $email;
    }

    $emails = array_values($unique);

    if (count($emails) > $max) {
        $errors[] = "{$label} supports up to {$max} email addresses only.";
        return '';
    }

    return implode(',', $emails);
}

// ✅ Fetch and sanitize
$id = intval($_GET['id']);
// ✅ Ensure template exists + not deleted
$check = $pdo->prepare("SELECT id FROM email_templates WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$check->execute([$id]);
if (!$check->fetchColumn()) {
    $msg = urlencode("Template not found or has been deleted.");
    header("Location: manage-email-templates.php?error=$msg");
    exit;
}

$template_name = trim($_POST['template_name'] ?? '');
$notification_type = trim($_POST['assigned_notification_type'] ?? '');
$html_content = $_POST['html_content'] ?? '';
$design = $_POST['design_json'] ?? '';

// ------------------------------------
// ✅ Handle "Create New Notification"
// (must run BEFORE validation below)
// ------------------------------------
$newNotifLabel = trim($_POST['new_notification_label'] ?? '');
$newNotifSlug  = trim($_POST['new_notification_slug'] ?? '');

if ($newNotifLabel !== '' && $newNotifSlug !== '') {

    // normalize slug
    $newNotifSlug = strtolower($newNotifSlug);
    $newNotifSlug = preg_replace('/[^a-z0-9_]+/', '_', $newNotifSlug);
    $newNotifSlug = trim($newNotifSlug, '_');
    $newNotifSlug = preg_replace('/_+/', '_', $newNotifSlug);

    try {
        // Check if slug already exists (even if deleted)
        $stmt = $pdo->prepare("SELECT id FROM notification_types WHERE slug = ? LIMIT 1");
        $stmt->execute([$newNotifSlug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['id'])) {
            // Restore + update label
            try {
                $pdo->prepare("
                    UPDATE notification_types
                    SET label = ?, deleted_at = NULL, updated_at = NOW()
                    WHERE id = ?
                ")->execute([$newNotifLabel, (int)$row['id']]);
            } catch (Exception $e) {
                // fallback if updated_at doesn't exist
                $pdo->prepare("
                    UPDATE notification_types
                    SET label = ?, deleted_at = NULL
                    WHERE id = ?
                ")->execute([$newNotifLabel, (int)$row['id']]);
            }
        } else {
            // Insert new
            try {
                $pdo->prepare("
                    INSERT INTO notification_types (slug, label, created_at, updated_at)
                    VALUES (?, ?, NOW(), NOW())
                ")->execute([$newNotifSlug, $newNotifLabel]);
            } catch (Exception $e) {
                // fallback if timestamps don't exist
                $pdo->prepare("
                    INSERT INTO notification_types (slug, label)
                    VALUES (?, ?)
                ")->execute([$newNotifSlug, $newNotifLabel]);
            }
        }

        // ✅ Force this update to use the new slug
        $notification_type = $newNotifSlug;

    } catch (Exception $e) {
        error_log("⚠️ Create notification failed (update): " . $e->getMessage());
        // Let normal validation below catch it if needed
    }
}

// CC / BCC (comma-separated)
$cc_raw  = $_POST['cc_emails'] ?? '';
$bcc_raw = $_POST['bcc_emails'] ?? '';

// ✅ Validate
$errors = [];
$cc_emails  = normalize_email_list_or_error($cc_raw, 'CC', $errors, 10);
$bcc_emails = normalize_email_list_or_error($bcc_raw, 'BCC', $errors, 10);
if ($template_name === '') {
    $errors[] = 'Template Name is required.';
}
// ✅ Validate notification type against DB instead of a hard-coded list
if ($notification_type === '') {
    $errors[] = 'Please select a notification type.';
} else {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM notification_types
            WHERE slug = ?
              AND deleted_at IS NULL
        ");
        $stmt->execute([$notification_type]);
        $exists = (int)$stmt->fetchColumn();

        if ($exists === 0) {
            $errors[] = 'Please select a valid notification type.';
        }
    } catch (Exception $e) {
        error_log("❌ Notification type validation failed: " . $e->getMessage());
        $errors[] = 'Could not validate notification type.';
    }
}

if ($html_content === '') {
    $errors[] = 'Email content cannot be empty.';
}

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header("Location: manage-email-templates.php?id={$id}&error={$msg}");
    exit;
}

// ?. Update record (support html_content OR template_html)
$updated = false;

try {
    $stmt = $pdo->prepare("
        UPDATE email_templates
           SET template_name = ?,
               html_content = ?,
               design_json = ?,
               assigned_notification_type = ?,
               cc_emails = ?,
               bcc_emails = ?,
               updated_at = NOW()
         WHERE id = ?
           AND deleted_at IS NULL
    ");
    $stmt->execute([$template_name, $html_content, $design, $notification_type, $cc_emails, $bcc_emails, $id]);
    $updated = true;
} catch (PDOException $e) {
    try {
        // Fallback if your table uses template_html instead of html_content
        $stmt = $pdo->prepare("
            UPDATE email_templates
               SET template_name = ?,
                   template_html = ?,
                   design_json = ?,
                   assigned_notification_type = ?,
                   cc_emails = ?,
                   bcc_emails = ?,
                   updated_at = NOW()
             WHERE id = ?
               AND deleted_at IS NULL
        ");
        $stmt->execute([$template_name, $html_content, $design, $notification_type, $cc_emails, $bcc_emails, $id]);
        $updated = true;
    } catch (PDOException $e2) {
        try {
            // Fallback if updated_at is not available
            $stmt = $pdo->prepare("
                UPDATE email_templates
                   SET template_name = ?,
                       html_content = ?,
                       design_json = ?,
                       assigned_notification_type = ?,
                       cc_emails = ?,
                       bcc_emails = ?
                 WHERE id = ?
                   AND deleted_at IS NULL
            ");
            $stmt->execute([$template_name, $html_content, $design, $notification_type, $cc_emails, $bcc_emails, $id]);
            $updated = true;
        } catch (PDOException $e3) {
            $stmt = $pdo->prepare("
                UPDATE email_templates
                   SET template_name = ?,
                       template_html = ?,
                       design_json = ?,
                       assigned_notification_type = ?,
                       cc_emails = ?,
                       bcc_emails = ?
                 WHERE id = ?
                   AND deleted_at IS NULL
            ");
            $stmt->execute([$template_name, $html_content, $design, $notification_type, $cc_emails, $bcc_emails, $id]);
            $updated = true;
        }
    }
}

if (!$updated) {
    $msg = urlencode('Failed to update template.');
    header("Location: manage-email-templates.php?id={$id}&error={$msg}");
    exit;
}
$success = urlencode('Template updated successfully!');
header("Location: manage-email-templates.php?id={$id}&success={$success}");
exit;


