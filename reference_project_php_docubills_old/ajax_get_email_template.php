<?php
session_start();
require_once 'config.php';
require_once 'middleware.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Not logged in']);
  exit;
}

if (!has_permission('access_email_templates_page')) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'Access denied']);
  exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid template id']);
  exit;
}

try {
  $row = null;

   // ---------------------------------------------------------
  // Robust fetch:
  // - supports template_html OR html_content
  // - supports deleted_at column existing OR not existing
  // - returns cc_emails / bcc_emails for your new UI fields
  // ---------------------------------------------------------
  $queries = [
    // deleted_at exists + template_html exists
    "
      SELECT id, template_name, assigned_notification_type,
             cc_emails, bcc_emails,
             template_html AS template_html, design_json
      FROM email_templates
      WHERE id = ? AND deleted_at IS NULL
      LIMIT 1
    ",
    // deleted_at exists + html_content exists
    "
      SELECT id, template_name, assigned_notification_type,
             cc_emails, bcc_emails,
             html_content AS template_html, design_json
      FROM email_templates
      WHERE id = ? AND deleted_at IS NULL
      LIMIT 1
    ",
    // deleted_at does NOT exist + template_html exists
    "
      SELECT id, template_name, assigned_notification_type,
             cc_emails, bcc_emails,
             template_html AS template_html, design_json
      FROM email_templates
      WHERE id = ?
      LIMIT 1
    ",
    // deleted_at does NOT exist + html_content exists
    "
      SELECT id, template_name, assigned_notification_type,
             cc_emails, bcc_emails,
             html_content AS template_html, design_json
      FROM email_templates
      WHERE id = ?
      LIMIT 1
    ",
  ];

  foreach ($queries as $sql) {
    try {
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) break;
    } catch (PDOException $e) {
      // try next variant
      continue;
    }
  }


  if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Template not found']);
    exit;
  }

  echo json_encode(['ok' => true, 'template' => $row], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Exception $e) {
  error_log("ajax_get_email_template.php error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
  exit;
}
