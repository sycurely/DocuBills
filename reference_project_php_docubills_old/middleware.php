<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─────────────────────────────────────────────
// ✅ IDLE SESSION TIMEOUT (reliable logout)
// Change 1800 to whatever you want (seconds): 900=15m, 1800=30m, 3600=60m
// ─────────────────────────────────────────────
$idleTimeout = 1800;

if (!empty($_SESSION['user_id'])) {
    $now  = time();
    $last = $_SESSION['last_activity'] ?? $now;

    if (($now - $last) > $idleTimeout) {

        // detect ajax/json requests (same logic style as yours)
        $isAjax = (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );

        session_unset();
        session_destroy();

        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'session_expired', 'redirect' => 'login.php']);
            exit;
        }

        header('Location: login.php?error=' . urlencode('Your session has expired. Please log in again.'));
        exit;
    }

    // update activity on every request
    $_SESSION['last_activity'] = $now;
}

// ─────────────────────────────────────────────
// ✅ GLOBAL SESSION EXPIRED GUARD (before permissions)
// ─────────────────────────────────────────────

// Allow specific pages to bypass auth by setting:
// $skipAuth = true; require_once 'middleware.php';
$skipAuth = $skipAuth ?? false;

// Pages that must remain accessible without login
$publicPages = [
    'login.php',
    'register.php',
    'forgot-password.php',
    'reset-password.php',
    'logout.php',
];

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');

// If user is not logged in, redirect to login (NOT access-denied)
if (!$skipAuth && !in_array($currentPage, $publicPages, true)) {
    if (empty($_SESSION['user_id'])) {

        // If this is an AJAX/JSON request, return JSON instead of HTML redirect
        $isAjax = (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );

        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'session_expired', 'redirect' => 'login.php']);
            exit;
        }

        header('Location: login.php?error=' . urlencode('Your session has expired. Please log in again.'));
        exit;
    }
}

// ─── Kill suspended accounts on every request ───
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT is_suspended FROM users WHERE id = ?");
    $stmt->execute([ $_SESSION['user_id'] ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['is_suspended']) {
        // destroy their session and bounce them out
        session_unset();
        session_destroy();
        header("Location: login.php?error=" . urlencode("Your account has been suspended."));
        exit;
    }
}

    // ✅ Check if current session exists in user_sessions AFTER login
    if (isset($_SESSION['user_id'])) {
        $sessionId = $_SESSION['session_id'] ?? session_id(); // fallback
        $stmt = $pdo->prepare("SELECT terminated_at FROM user_sessions WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
    // Session row doesn't exist — maybe not inserted yet, allow access
    // IMPORTANT: do NOT return here because this file also defines functions.
    } else {
        // ✅ If session is marked as terminated
        if (!empty($result['terminated_at'])) {
            session_destroy();
            header("Location: login.php?terminated=1");
            exit;
        }
    }
}

// Check if user still exists
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

function load_permissions_for($role_id) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT p.name 
        FROM permissions p
        INNER JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$role_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_user_permissions_for_current_user() {
    global $pdo;

    // No user logged in? No permissions.
    if (!isset($_SESSION['user_id'])) {
        return [];
    }

    // Simple per-request cache so we only hit DB once
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $userId = (int) $_SESSION['user_id'];

    // 🔍 Load permissions based on the user's current role_id
    $stmt = $pdo->prepare("
        SELECT p.name
        FROM users u
        JOIN role_permissions rp ON rp.role_id = u.role_id
        JOIN permissions p ON p.id = rp.permission_id
        WHERE u.id = ? AND u.deleted_at IS NULL
    ");
    $stmt->execute([$userId]);

    $cache = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    return $cache;
}

function has_permission($permission_name) {
    // Fetch all permission "names" for the current user
    $permissions = get_user_permissions_for_current_user();

    // Check if the requested permission is in that list
    return in_array($permission_name, $permissions, true);
}


function has_any_setting_permission() {
    $permissions = [
    // Basic
    'access_basic_settings',
    'update_basic_settings',

    // Payments
    'manage_payment_methods',
    'manage_card_payments',
    'manage_bank_details',

    // Email templates / reminders
    'access_email_templates_page',
    'add_email_template',
    'edit_email_template',
    'delete_email_template',
    'manage_notification_categories',
    'manage_reminder_settings',

    // Permissions / users
    'manage_permissions',
    'manage_users_page',
    'assign_roles',
    'edit_user',
    'suspend_users',
    'manage_role_viewable'
    ];

    foreach ($permissions as $perm) {
        if (has_permission($perm)) {
            return true;
        }
    }
    return false;
}
