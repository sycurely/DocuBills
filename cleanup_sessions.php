<?php
require_once 'config.php';

// Default if setting not found
$days = 30;

// Fetch value from settings table
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'session_retention_days'");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && is_numeric($row['key_value'])) {
    $days = max(1, min(365, intval($row['key_value'])));
}

// Delete old sessions
$delete = $pdo->prepare("DELETE FROM user_sessions WHERE last_activity < NOW() - INTERVAL {$days} DAY");
$delete->execute();

echo "✅ Cleanup complete. Sessions older than {$days} days were deleted. Total: " . $delete->rowCount();
