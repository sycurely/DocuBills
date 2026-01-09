<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
require_once 'middleware.php';

// Check permission (optional: only allow if user has a permission like 'manage_users')
if (!has_permission('manage_users')) {
    exit("❌ You do not have permission to add users.");
}

// Validate POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit;
}

// Sanitize inputs
$full_name = trim($_POST['full_name'] ?? '');
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role_id   = (int) ($_POST['role_id'] ?? 0);

// Validate fields
if (empty($full_name) || empty($username) || empty($email) || empty($password) || !$role_id) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: users.php");
    exit;
}

// Check for duplicate username
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "Username is already taken.";
    header("Location: users.php");
    exit;
}

// Check for duplicate email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "Email is already registered.";
    header("Location: users.php");
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $pdo->prepare("INSERT INTO users (username, email, full_name, password, role_id, created_at)
                       VALUES (?, ?, ?, ?, ?, NOW())");
$success = $stmt->execute([$username, $email, $full_name, $hashedPassword, $role_id]);

if ($success) {
    $_SESSION['success'] = "✅ New user added successfully.";
} else {
    $_SESSION['error'] = "Something went wrong. Please try again.";
}

header("Location: users.php");
exit;
