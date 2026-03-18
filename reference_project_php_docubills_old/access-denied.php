<?php
session_start();
$activeMenu = '';
require_once 'config.php';
require 'styles.php';
require 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Access Denied</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php require 'styles.php'; ?>
  <style>
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }
    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 2rem;
      margin: 2rem auto;
      max-width: 600px;
      text-align: center;
    }
    .btn-primary {
      background: var(--primary);
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: var(--radius);
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 1.5rem;
    }
    .btn-primary:hover {
      background: var(--secondary);
    }
  </style>
</head>
<body>
<?php require 'header.php'; ?>
<div class="app-container">
  <?php require 'sidebar.php'; ?>
  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-ban"></i> Access Denied</h1>
    </div>
    <div class="card">
      <p>You do not have permission to access this page.</p>
      <a href="index.php" class="btn-primary"><i class="fas fa-home"></i> Go to Dashboard</a>
    </div>
  </div>
</div>
<?php require 'scripts.php'; ?>
</body>
</html>
