<?php
session_start();
require 'config.php';
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


if (!isset($_SESSION['user_id'])) exit;

$userId = $_SESSION['user_id'];

if (isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && $file['size'] < 2 * 1024 * 1024) {
        $folder = 'uploads/avatars';
        if (!is_dir($folder)) mkdir($folder, 0755, true);
        $filename = "$folder/user_{$userId}." . $ext;
        move_uploaded_file($file['tmp_name'], $filename);
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$filename, $userId]);
        $_SESSION['avatar'] = $filename;
        echo json_encode(['success' => true, 'src' => $filename]);
        exit;
    }
}
echo json_encode(['success' => false]);
