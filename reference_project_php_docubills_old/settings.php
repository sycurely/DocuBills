<?php
$activeMenu = 'settings';
require_once 'config.php';
require 'styles.php';
require 'sidebar.php';
require 'scripts.php';


// Initialize settings keys
$keys = [
  'company_name'      => '',
  'company_address'   => '',
  'company_phone'     => '',
  'company_email'     => '',
  'gst_number'        => '',
  'logo_url'          => '',
  'stripe_publishable'=> '',
  'stripe_secret'     => '',
  'square_token'      => '',
  'test_mode'         => 'off',
  'invoice_notice'    => ''  // ✅ Add this line
];

// Load existing values from DB
$stmt = $pdo->query("SELECT key_name, key_value FROM settings");
foreach ($stmt as $row) {
    if (array_key_exists($row['key_name'], $keys)) {
        $keys[$row['key_name']] = $row['key_value'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle logo upload
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
        $fileName = 'logo_' . time() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $targetFile)) {
            $keys['logo_url'] = 'assets/uploads/' . $fileName;
        }
    }

    // Process form fields
    foreach ($keys as $key => &$val) {
        if ($key === 'test_mode') {
            $val = isset($_POST['test_mode']) ? 'on' : 'off';
        } elseif (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
        }

        $stmt = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
        $stmt->execute([$key]);
        if ($stmt->fetch()) {
            $update = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
            $update->execute([$val, $key]);
        } else {
            $insert = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
            $insert->execute([$key, $val]);
        }
    }

    echo "<p style='color: green;'>✅ Settings saved!</p>";
}
?>

<head>
  <meta charset="UTF-8">
  <title>Settings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php require 'styles.php'; ?>
  <style>
    /* Consistent with other pages */
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --header-height: 70px;
      --sidebar-width: 250px;
      --transition: all 0.3s ease;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
    }

    .app-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }

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
      transition: var(--transition);
      overflow: hidden;
      padding: 2rem;
      margin-bottom: 1.5rem;
    }

    .form-section {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-section-title {
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }

    .form-control {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: var(--card-bg);
      color: var(--dark);
      font-size: 1rem;
      transition: var(--transition);
    }

    .form-control:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .btn {
      padding: 0.8rem 1.5rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 1rem;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
      box-shadow: var(--shadow-hover);
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .upload-container {
      border: 2px dashed var(--border);
      border-radius: var(--radius);
      padding: 2rem;
      text-align: center;
      margin-top: 1rem;
      transition: var(--transition);
      cursor: pointer;
    }

    .upload-container:hover {
      border-color: var(--primary);
      background: rgba(67, 97, 238, 0.05);
    }

    .upload-icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .upload-text {
      color: var(--gray);
      margin-bottom: 1rem;
    }

    .upload-hint {
      font-size: 0.9rem;
      color: var(--gray);
    }

    .or-divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 1.5rem 0;
      color: var(--gray);
    }

    .or-divider::before,
    .or-divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid var(--border);
    }

    .or-divider::before {
      margin-right: 1rem;
    }

    .or-divider::after {
      margin-left: 1rem;
    }

    .required::after {
      content: " *";
      color: var(--danger);
    }

    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
    }
  </style>    
</head>
<body>
<?php require 'header.php'; ?>
<div class="app-container">
  <?php require 'sidebar.php'; ?>

  <div class="main-content">
    <?php if (!empty($success)): ?>
      <div class="alert alert-success" id="successAlert">
        <i class="fas fa-check-circle"></i> <?= $success ?>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-cog"></i> Settings</h1>
    </div>

      <div class="card">
        <form method="post" enctype="multipart/form-data">
          <!-- Business Info -->
          <div class="form-section">
            <h2 class="form-section-title"><i class="fas fa-building"></i> Business Information</h2><br>
        
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($keys['company_name']) ?>">
              </div>
        
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="company_phone" class="form-control" value="<?= htmlspecialchars($keys['company_phone']) ?>">
              </div>
        
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($keys['company_email']) ?>">
              </div>
        
              <div class="form-group">
                <label class="form-label">GST/HST Number</label>
                <input type="text" name="gst_number" class="form-control" value="<?= htmlspecialchars($keys['gst_number']) ?>">
              </div>
        
              <div class="form-group col-span-2">
                <label class="form-label">Address</label>
                <textarea name="company_address" class="form-control" rows="2"><?= htmlspecialchars($keys['company_address']) ?></textarea>
              </div>
        
              <div class="form-group col-span-2">
                <label class="form-label">Invoice Notice</label>
                <textarea name="invoice_notice" class="form-control" rows="2"><?= htmlspecialchars($keys['invoice_notice']) ?></textarea>
              </div>
            </div>
          </div>
        </div>

          <!-- Logo Upload -->
          <div class="form-section">
            <h2 class="form-section-title"><i class="fas fa-image"></i> Company Logo</h2>
            <div class="form-group">
              <label class="form-label">Upload Logo</label>
              <input type="file" name="logo_file" class="form-control" accept="image/*">
              <?php if (!empty($keys['logo_url'])): ?>
                <div style="margin-top: 10px;">
                  <img src="<?= htmlspecialchars($keys['logo_url']) ?>" style="max-height: 60px;">
                </div>
              <?php endif; ?>
            </div>
          </div>
        
          <!-- Payment Section -->
          <div class="form-section">
            <h2 class="form-section-title"><i class="fas fa-credit-card"></i> Payment Integration</h2>
        
            <div class="form-grid">
              <div class="form-group col-span-2">
                <label class="form-label">Stripe Publishable Key</label>
                <input type="text" name="stripe_publishable" class="form-control" value="<?= htmlspecialchars($keys['stripe_publishable']) ?>">
              </div>
        
              <div class="form-group col-span-2">
                <label class="form-label">Stripe Secret Key</label>
                <input type="text" name="stripe_secret" class="form-control" value="<?= htmlspecialchars($keys['stripe_secret']) ?>">
              </div>
        
              <div class="form-group col-span-2">
                <label class="form-label">Square Access Token</label>
                <input type="text" name="square_token" class="form-control" value="<?= htmlspecialchars($keys['square_token']) ?>">
              </div>
        
              <div class="form-group">
                <label class="form-label">
                  <input type="checkbox" name="test_mode" <?= $keys['test_mode'] === 'on' ? 'checked' : '' ?>>
                  Enable Test Mode
                </label>
              </div>
            </div>
          </div>
        
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Settings
          </button>
        </form>

<?php require 'scripts.php'; ?>
</body>
</html>
