<?php
// Start output buffering to prevent "headers already sent" issues
ob_start();

// In production, log errors but don't display them to users
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

session_start();
if (!empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/config.php';

$error = null;

// capture any redirect‐passed error
if (isset($_GET['error']) && ! $error) {
    $error = htmlspecialchars($_GET['error']);
}

if (isset($_GET['terminated']) && $_GET['terminated'] == '1') {
    $error = "Your session was terminated. Please log in again.";
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = null;
        $status = 'failure';

        $stmt = $pdo->prepare(
          "SELECT 
             id,
             username,
             full_name,
             password,
             is_suspended
           FROM users
           WHERE username = ?
             AND deleted_at IS NULL
           LIMIT 1"
        );

        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "User not found.";
        }
        elseif (!password_verify($password, $user['password'])) {
            $error = "Incorrect password.";
        }
        elseif (!empty($user['is_suspended'])) {
            // Account is suspended
            $error = "Your account has been suspended. Please contact an administrator.";
        }
        else {
            $status = 'success';
            $userId = $user['id'];
            session_regenerate_id(true); // generate a new session ID always
            $newSessionId = session_id();
            
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $user['full_name'] ?? $username;
            error_log("✅ Session full_name: " . ($_SESSION['user_name'] ?? 'NOT SET'));
            $_SESSION['session_id'] = $newSessionId;
            
            // Track active session as NEW record (upsert on duplicate session_id)
            $insert = $pdo->prepare(
                "INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   user_id    = VALUES(user_id),
                   ip_address = VALUES(ip_address),
                   user_agent = VALUES(user_agent),
                   created_at = CURRENT_TIMESTAMP"
            );
            $insert->execute([$userId, $newSessionId, $ipAddress, $userAgent]);


            session_write_close(); // flush session data to disk
            usleep(100000);        // small delay (optional)

            // Log successful login
            $logStmt = $pdo->prepare(
                "INSERT INTO login_logs (user_id, username, ip_address, user_agent, status) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $logStmt->execute([$userId, $username, $ipAddress, $userAgent, $status]);

            header("Location: index.php");
            exit;
        }

        // Log failed login
        $logStmt = $pdo->prepare(
            "INSERT INTO login_logs (user_id, username, ip_address, user_agent, status) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $logStmt->execute([$userId, $username, $ipAddress, $userAgent, $status]);
    } catch (PDOException $e) {
        // TEMP: show DB error instead of a blank page
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - DocuBills</title>
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

    .login-card {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 400px;
    }

    .login-title {
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
      color: var(--primary);
      text-align: center;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    label {
      font-weight: 600;
      display: block;
      margin-bottom: 0.5rem;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
      transition: var(--transition);
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
    }

    .btn {
      display: inline-block;
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.75rem;
      width: 100%;
      border-radius: var(--radius);
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }

    .btn:hover {
      background: var(--secondary);
    }

    .error-message {
      background: rgba(247, 37, 133, 0.1);
      border: 1px solid var(--danger);
      color: var(--danger);
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: var(--radius);
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <h2 class="login-title">Login</h2>

    <?php if ($error): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>

      <button type="submit" class="btn">Login</button>
    </form>
  </div>

</body>
</html>
