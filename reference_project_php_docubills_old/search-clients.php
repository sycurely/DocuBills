<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode([]);
  exit;
}

require_once 'config.php';
require_once 'middleware.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
  echo json_encode([]);
  exit;
}

// Keep it sane
if (mb_strlen($q) > 80) $q = mb_substr($q, 0, 80);

function escape_like($str) {
  return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $str);
}

$uid = (int)$_SESSION['user_id'];
$canViewAll = has_permission('view_all_clients');

// PREFIX match only
$prefix = escape_like($q) . '%';

$sql = "
  SELECT id, company_name
  FROM clients
  WHERE deleted_at IS NULL
    AND company_name LIKE :prefix ESCAPE '\\\\'
";

$params = [':prefix' => $prefix];

if (!$canViewAll) {
  $sql .= " AND created_by = :uid";
  $params[':uid'] = $uid;
}

$sql .= " ORDER BY company_name ASC LIMIT 25";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
