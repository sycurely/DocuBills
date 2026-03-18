<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';
require_once 'middleware.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

if (!has_any_setting_permission()) {
  echo json_encode(['success' => false, 'message' => 'Access denied']);
  exit;
}

$action = $_POST['action'] ?? '';
if ($action === '') {
  echo json_encode(['success' => false, 'message' => 'Missing action']);
  exit;
}

try {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS taxes (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      percentage DECIMAL(5,2) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Database error']);
  exit;
}

function ensure_tax_columns(PDO $pdo) {
  $cols = $pdo->query("SHOW COLUMNS FROM taxes")->fetchAll(PDO::FETCH_COLUMN, 0);
  if (!in_array('tax_type', $cols, true)) {
    $pdo->exec("ALTER TABLE taxes ADD COLUMN tax_type VARCHAR(20) NOT NULL DEFAULT 'line'");
  }
  if (!in_array('calc_order', $cols, true)) {
    $pdo->exec("ALTER TABLE taxes ADD COLUMN calc_order INT NOT NULL DEFAULT 1");
  }
}

try {
  ensure_tax_columns($pdo);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Database error']);
  exit;
}

function clean_name($value) {
  $name = trim((string)$value);
  if ($name === '') {
    return '';
  }
  if (strlen($name) > 100) {
    $name = substr($name, 0, 100);
  }
  return $name;
}

function clean_percentage($value) {
  $raw = trim((string)$value);
  if ($raw === '') {
    return null;
  }
  $num = (float)$raw;
  if (!is_numeric($raw) || $num < 0 || $num > 100) {
    return null;
  }
  return $num;
}

function clean_tax_type($value) {
  $raw = strtolower(trim((string)$value));
  return $raw === 'invoice' ? 'invoice' : 'line';
}

function clean_calc_order($value) {
  if ($value === null || $value === '') {
    return 1;
  }
  $num = (int)$value;
  if ($num < 1) {
    $num = 1;
  }
  if ($num > 100) {
    $num = 100;
  }
  return $num;
}

try {
  if ($action === 'create') {
    $name = clean_name($_POST['name'] ?? '');
    $percentage = clean_percentage($_POST['percentage'] ?? '');
    $tax_type = clean_tax_type($_POST['tax_type'] ?? 'line');
    $calc_order = clean_calc_order($_POST['calc_order'] ?? 1);

    if ($name === '' || $percentage === null) {
      echo json_encode(['success' => false, 'message' => 'Invalid name or percentage']);
      exit;
    }

    $stmt = $pdo->prepare("INSERT INTO taxes (name, percentage, tax_type, calc_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $percentage, $tax_type, $calc_order]);

    echo json_encode([
      'success' => true,
      'id' => (int)$pdo->lastInsertId(),
      'name' => $name,
      'percentage' => $percentage,
      'tax_type' => $tax_type,
      'calc_order' => $calc_order
    ]);
    exit;
  }

  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $name = clean_name($_POST['name'] ?? '');
    $percentage = clean_percentage($_POST['percentage'] ?? '');
    $tax_type = clean_tax_type($_POST['tax_type'] ?? 'line');
    $calc_order = clean_calc_order($_POST['calc_order'] ?? 1);

    if ($id <= 0 || $name === '' || $percentage === null) {
      echo json_encode(['success' => false, 'message' => 'Invalid data']);
      exit;
    }

    $stmt = $pdo->prepare("UPDATE taxes SET name = ?, percentage = ?, tax_type = ?, calc_order = ? WHERE id = ?");
    $stmt->execute([$name, $percentage, $tax_type, $calc_order, $id]);

    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid ID']);
      exit;
    }

    $stmt = $pdo->prepare("DELETE FROM taxes WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
    exit;
  }

  echo json_encode(['success' => false, 'message' => 'Unknown action']);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Server error']);
}
