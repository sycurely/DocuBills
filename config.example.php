<?php
// Theme configuration settings
define('APP_NAME', 'DocuBills');
define('THEME_VERSION', '1.1.8');
define('DEFAULT_THEME', 'light'); // Options: 'light' or 'dark'
define('STRIPE_WEBHOOK_SECRET', 'whsec_6IfnWN1LxAPkiY7pu1ztdRoblkfvJ07M');
error_reporting(E_ALL & ~E_DEPRECATED);

// === DYNAMIC BASE PATH & URL (stable across all pages/subfolders) ===
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

$docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$appDir  = realpath(__DIR__);

// Build a web path from document root -> app directory
$relativePath = str_replace($docRoot, '', $appDir);
$relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
$relativePath = '/' . trim($relativePath, '/');

define('BASE_URL', $protocol . $httpHost . $relativePath . '/');   // ✅ stable
define('BASE_PATH', $appDir . '/');                                 // ✅ stable

$host = 'localhost';
$db   = 'docubill_old';
$user = 'your_database_user';
$pass = 'YOUR_DATABASE_PASSWORD';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $pdo;
        $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return ($row && isset($row['key_value'])) ? $row['key_value'] : $default;
    }
}

if (!function_exists('resolve_asset_path')) {
    /**
     * Converts a relative or absolute URL (from DB) to a full local file path for DomPDF, etc.
     */
    function resolve_asset_path($pathOrUrl) {
        // Full URL (e.g. https://domain.com/folder/assets/logo.png)
        if (strpos($pathOrUrl, 'http') === 0) {
            $parsed = parse_url($pathOrUrl);
            return $_SERVER['DOCUMENT_ROOT'] . $parsed['path'];
        }

        // Relative path (e.g. assets/logo.png or /assets/logo.png)
        return realpath(__DIR__ . '/' . ltrim($pathOrUrl, '/'));
    }
}

?>
