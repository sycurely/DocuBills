<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
      UPDATE user_sessions 
      SET terminated_at = NOW(), 
          last_activity = NOW(),
          termination_reason = 'logout'
      WHERE session_id = ?
    ");
    $stmt->execute([session_id()]);
}
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logging Out...</title>
  <meta http-equiv="refresh" content="2;url=login.php">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --transition: all 0.3s ease;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    body {
      background: var(--body-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .logout-card {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .logout-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: var(--primary);
    }

    .logout-text {
      font-size: 1rem;
      color: var(--gray);
    }

    .logout-spinner {
      margin-top: 1.5rem;
      font-size: 1.8rem;
      color: var(--primary);
      animation: spin 1.2s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>

  <div class="logout-card">
    <div class="logout-title">Logging you out...</div>
    <div class="logout-text">You are being safely redirected to login.</div>
    <div class="logout-spinner">
      <i class="fas fa-spinner fa-spin"></i>
    </div>
  </div>

</body>
</html>
