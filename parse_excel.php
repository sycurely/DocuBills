<?php
// Start output buffering BEFORE loading config.php
// so any accidental output (echo, whitespace, notices) is captured
if (!ob_get_level()) {
    ob_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once __DIR__ . '/assets/SimpleXLSX.php';

// Optional XLS support for legacy .xls files
$xlsLib = __DIR__ . '/assets/SimpleXLS.php';
if (file_exists($xlsLib)) {
    require_once $xlsLib;
}

use Shuchkin\SimpleXLSX;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request');
}

$rows = [];

// 1) Google Sheet URL
if (!empty($_POST['google_sheet_url'])) {
    $googleUrl = trim($_POST['google_sheet_url']);

    // Basic validation so user can't paste random non-Google URLs
    if (!filter_var($googleUrl, FILTER_VALIDATE_URL)) {
        exit('Invalid Google Sheet URL. Please paste a full https://docs.google.com/spreadsheets/... link.');
    }

    if (strpos($googleUrl, 'docs.google.com') === false) {
        exit('Please provide a valid Google Sheets URL (https://docs.google.com/spreadsheets/...).');
    }

    // Convert /edit to CSV export URL
    $csvUrl = preg_replace('/\/edit.*$/', '/export?format=csv', $googleUrl);

    $csvContent = @file($csvUrl);
    if ($csvContent === false) {
        exit('Unable to download data from the Google Sheet. Please ensure it is shared as "Anyone with the link can view".');
    }

    $rows = array_map('str_getcsv', $csvContent);

    // 2) Excel file upload (.xls / .xlsx only)
} elseif (!empty($_FILES['excel_file']['tmp_name'])) {

    $upload = $_FILES['excel_file'];

    if ($upload['error'] !== UPLOAD_ERR_OK) {
        exit('File upload error (code ' . $upload['error'] . '). Please try again.');
    }

    $originalName = $upload['name'] ?? '';
    $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExt = ['xls', 'xlsx'];
    if (!$ext || !in_array($ext, $allowedExt, true)) {
        exit('Invalid file type. Please upload a .xls or .xlsx Excel file.');
    }

    // Ensure uploads directory exists
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $targetPath = $uploadDir . '/' . basename($originalName);

    if (!move_uploaded_file($upload['tmp_name'], $targetPath)) {
        exit('Failed to move uploaded file.');
    }

    if ($ext === 'xlsx') {
        // ✅ Handle .xlsx via SimpleXLSX
        $xlsx = SimpleXLSX::parse($targetPath);
        if (!$xlsx) {
            exit('Failed to read the .xlsx file. Please make sure it is a valid Excel file.');
        }
        $rows = $xlsx->rows();

    } else {
        // ✅ Handle .xls via SimpleXLS (if available)
        $xls = null;

        if (class_exists('\Shuchkin\SimpleXLS')) {
            $xls = \Shuchkin\SimpleXLS::parse($targetPath);
        } elseif (class_exists('SimpleXLS')) {
            $xls = \SimpleXLS::parse($targetPath);
        } else {
            exit('The server is not configured to read .xls files yet. Please contact the administrator.');
        }

        if (!$xls) {
            exit('Failed to read the .xls file. Please make sure it is a valid Excel file.');
        }

        $rows = $xls->rows();
    }

} else {
    exit('No data source provided. Please either paste a Google Sheet URL or upload an Excel file.');
}

if (empty($rows) || count($rows) < 2) {
    exit('No invoice data found. Please check your file / Google Sheet contents.');
}

// 3) Dynamic headers (trim)
$rawHeaders = $rows[0];
$headers    = array_map('trim', $rawHeaders);
$colCount   = count($headers);

// 3a) Decide which columns to keep
//     We REMOVE any column where:
//     - Header is empty AND
//     - All data rows are empty for that column
$keepIndexes = [];
for ($col = 0; $col < $colCount; $col++) {
    $header = $headers[$col];

    // If header has any text, we keep this column regardless of data
    if ($header !== '') {
        $keepIndexes[$col] = true;
        continue;
    }

    // Header is empty, check if any data row has a non-empty value
    $hasData = false;
    for ($rowIndex = 1; $rowIndex < count($rows); $rowIndex++) {
        if (!array_key_exists($col, $rows[$rowIndex])) {
            continue;
        }

        $cell = $rows[$rowIndex][$col];
        if (!is_string($cell)) {
            $cell = (string)$cell;
        }
        $cell = trim($cell);

        if ($cell !== '') {
            $hasData = true;
            break;
        }
    }

    $keepIndexes[$col] = $hasData; // true if any data, false if completely empty
}

// Build filtered headers list
$filteredHeaders = [];
foreach ($keepIndexes as $col => $keep) {
    if ($keep) {
        $filteredHeaders[] = $headers[$col];
    }
}

// 4) Build items: each subsequent row, skip fully empty, using only kept columns
$items = [];
for ($i = 1; $i < count($rows); $i++) {
    $row = $rows[$i];

    // Build a filtered row using only kept columns
    $filteredRow = [];
    foreach ($keepIndexes as $col => $keep) {
        if (!$keep) {
            continue;
        }

        $val = array_key_exists($col, $row) ? $row[$col] : '';
        if (!is_string($val)) {
            $val = (string)$val;
        }
        $val = trim($val);
        $filteredRow[] = $val;
    }

    // Skip fully empty rows (after filtering columns)
    if (!array_filter($filteredRow)) {
        continue;
    }

    // Combine into assoc array: header => value
    $items[] = array_combine($filteredHeaders, $filteredRow);
}

// Make sure $headers matches the filtered columns
$headers = $filteredHeaders;

// 5) Total (set to 0 for now; calculated later)
$total = 0;

// 6) Store in session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['invoice_data'] = [
    'bill_to' => [
        'Company Name'   => $_POST['bill_to_name']    ?? '',
        'Contact Name'   => $_POST['bill_to_rep']     ?? '',
        'Address'        => $_POST['bill_to_address'] ?? '',
        'Phone'          => $_POST['bill_to_phone']   ?? '',
        'Email'          => $_POST['bill_to_email']   ?? '',
    ],
    'headers' => $headers,
    'items'   => $items,
    'total'   => $total,
];

// 7) Redirect to price‐column selection
// Clear any buffered output (for example, from config.php) before redirect
if (ob_get_level()) {
    ob_end_clean();
}

header('Location: price_select.php');
exit;
