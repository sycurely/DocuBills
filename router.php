<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path === '/' || $path === '/favicon.ico') {
    http_response_code(404);
    exit;
}

if (is_file($file)) {
    return false; // Serve the requested file as-is.
}

http_response_code(404);
