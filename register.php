<?php
session_start();
require_once 'config.php';
// ─── Real-time availability checks ─────────────────────
if ($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['username'])) {
    $stmt = $pdo->prepare(
      "SELECT COUNT(*) FROM users WHERE LOWER(username)=LOWER(?) AND deleted_at IS NULL"
    );
    $stmt->execute([ $_GET['username'] ]);
    // ── Prevent caching ──────────────────────
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    // ─────────────────────────────────────────
    echo json_encode(['available' => $stmt->fetchColumn()==0]);
    exit;
}

if ($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['email'])) {
    // count matching emails, case-insensitive
    $stmt = $pdo->prepare(
      "SELECT COUNT(*) FROM users WHERE LOWER(email)=LOWER(?) AND deleted_at IS NULL"
    );
    $stmt->execute([ $_GET['email'] ]);

    // ── Prevent caching ──────────────────────
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    // ─────────────────────────────────────────

    echo json_encode(['available' => $stmt->fetchColumn()==0]);
    exit;
}

// ─── Flash messages (for display after redirect) ───────────
$error   = $_SESSION['error']   ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
// ──────────────

// initialize form-values
$username  = '';
$full_name = '';
$email     = '';


    // Handle registration
    // Handle registration (and immediately redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // grab submitted values
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm'];
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);

    // ─── validations ─────────────────────────────────────
    if (empty($username)) {
        $error = "Please enter a username.";
    }
    elseif (empty($full_name) || empty($email)) {
        $error = "Please enter both your full name and email.";
    }
    elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    else {
        // check existing username (case-insensitive)
        $stmt = $pdo->prepare(
          "SELECT id FROM users 
             WHERE LOWER(username)=LOWER(?) 
               AND deleted_at IS NULL"
    );
        $stmt->execute([$username]);
    if ($stmt->fetch()) {
            $error = "Username already exists.";
    }
    // check existing email (case-insensitive)
    else {
        $stmt2 = $pdo->prepare(
          "SELECT id FROM users 
             WHERE LOWER(email)=LOWER(?) 
               AND deleted_at IS NULL"
        );
        $stmt2->execute([$email]);
    if ($stmt2->fetch()) {
        $error = "Email already registered.";
            }
        }
    }

    // ─────────────────────────────────────────────────────

    if ($error) {
        // store the error in session and redirect to clear POST
        $_SESSION['error'] = $error;
        header('Location: register.php');
        exit;
    }

    // ─── if no error: do the insert ─────────────────────
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
      "INSERT INTO users 
         (username, password, full_name, email, role_id)
       VALUES (?,        ?,        ?,         ?,     ?)"
    );
    $role_id = $pdo
       ->query("SELECT id FROM roles WHERE name = 'viewer'")
       ->fetchColumn();
        try {
          $stmt->execute([
            $username,
            $hashed,
            $full_name,
            $email,
            $role_id
          ]);
        } catch (\PDOException $e) {
          // 1062 = duplicate key
          if ($e->errorInfo[1] === 1062) {
            $_SESSION['error'] = "Username or email already exists.";
            header('Location: register.php');
            exit;
          }
          // rethrow anything else
          throw $e;
        }

    // success flash + redirect
    $_SESSION['success'] = "Account created! Please <a href=\"login.php\">log in</a>.";
    header('Location: register.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - WomenFirst</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Canvas-Confetti for celebration -->
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

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

    .register-card {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 420px;
    }

    .register-title {
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
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
      transition: var(--transition);
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
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

    .success-message {
      background: rgba(76, 201, 240, 0.2);
      border: 1px solid var(--success);
      color: var(--success);
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: var(--radius);
      text-align: center;
    }

    .form-footer {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }

    .form-footer a {
      color: var(--primary);
      text-decoration: none;
    }
  </style>
</head>
<body>

  <div class="register-card">
    <h2 class="register-title">Create Account</h2>

    <?php if ($success): ?>
      <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input
        type="text"
        name="username"
        id="username"
        required
        autofocus
        value="<?= htmlspecialchars($username) ?>"
      >
      <small id="usernameHelp" class="form-text"></small>
      </div>

      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input
          type="text"
          name="full_name"
          id="full_name"
          required
          value="<?= htmlspecialchars($full_name) ?>"
        >
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input
          type="email"
          name="email"
          id="email"
          required
          value="<?= htmlspecialchars($email) ?>"
        >
        <small id="emailHelp" class="form-text"></small>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <input type="password" name="confirm" id="confirm" required>
      </div>

      <button type="submit" class="btn">Register</button>
    </form>

    <div class="form-footer">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // grab elements
      const userIn    = document.getElementById('username');
      const userHelp  = document.getElementById('usernameHelp');
      const emailIn   = document.getElementById('email');
      const emailHelp = document.getElementById('emailHelp');
      const btn       = document.querySelector('button[type="submit"]');
    
      // helper to toggle Register button
      function updateSubmitState() {
        const uok = userHelp.textContent.startsWith('✓');
        const eok = emailHelp.textContent.startsWith('✓');
        btn.disabled = !(uok && eok);
      }
    
      // username availability
      userIn.addEventListener('input', function() {
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        const v = this.value.trim();
        if (!v) {
          userHelp.textContent = '';
          updateSubmitState();
          return;
        }
        fetch(`register.php?username=${encodeURIComponent(v)}&ts=${Date.now()}`)
          .then(r => r.json())
          .then(d => {
            userHelp.textContent = d.available
              ? '✓ Username is available.'
              : '✗ Username is already taken.';
            userHelp.style.color = d.available ? 'green' : 'var(--danger)';
            updateSubmitState();
          })
          .catch(console.error);
      });
    
      // email availability
      emailIn.addEventListener('input', function() {
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        const v = this.value.trim();
        if (!v) {
          emailHelp.textContent = '';
          updateSubmitState();
          return;
        }
        fetch(`register.php?email=${encodeURIComponent(v)}&ts=${Date.now()}`)
          .then(r => r.json())
          .then(d => {
            emailHelp.textContent = d.available
              ? '✓ Email is available.'
              : '✗ Email is already registered.';
            emailHelp.style.color = d.available ? 'green' : 'var(--danger)';
            updateSubmitState();
          })
          .catch(console.error);
      });
    
      // initial state
      updateSubmitState();
    
      // confetti + redirect when success-message exists
      const successEl = document.querySelector('.success-message');
      if (successEl) {
        confetti({ particleCount: 150, spread: 60, origin: { y: 0.6 } });
        setTimeout(() => window.location.href = 'login.php', 3000);
      }
    });
    </script>

</body>
</html>
