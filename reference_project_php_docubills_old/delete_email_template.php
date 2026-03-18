<?php
// delete_email_template.php
require_once 'config.php';

// Ensure an ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: settings-email-templates.php?error=Invalid+template+ID');
    exit;
}

$template_id = intval($_GET['id']);

// Delete the template
$stmt = $pdo->prepare("DELETE FROM email_templates WHERE id = ?");
$stmt->execute([$template_id]);

// Redirect back with success message
header('Location: settings-email-templates.php?success=Template+deleted+successfully');
exit;
?>
