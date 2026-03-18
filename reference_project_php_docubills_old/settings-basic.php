<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$activeMenu = 'settings';
$activeTab = 'basic';
require_once 'config.php';
require_once 'middleware.php'; // ✅ Add middleware

// ✅ Check permission
if (!has_permission('access_basic_settings')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}
$can_update = has_permission('update_basic_settings'); // ✅ ADD THIS LINE
    
    // Fetch all permissions grouped by module
    $permissions = $pdo->query("SELECT * FROM permissions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch assigned permissions for current role (Super Admin = 1)
    $assigned = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
    $assigned->execute([1]);
    $assigned_ids = array_column($assigned->fetchAll(PDO::FETCH_ASSOC), 'permission_id');

$keys = [
  'company_name'      => '',
  'company_address'   => '',
  'company_phone'     => '',
  'company_email'     => '',
  'gst_number'        => '',
  'invoice_prefix'    => '',
  'company_logo_url'  => '',
  'app_logo_url'      => '',     // ✅ NEW: Application Header Logo
  'invoice_notice'    => '',
  'admin_email'       => '',
  'currency_code'     => 'CAD',  // ✅ NEW
  'currency_symbol'   => '$'     // ✅ NEW
];

// ✅ Currency list (code => ENGLISH label)
// We will DISPLAY + STORE currency as ISO code like: SAR, PKR, INR, KWD, etc.
$currencyOptions = [
  'CAD' => 'CAD — Canadian Dollar',
  'USD' => 'USD — US Dollar',
  'PKR' => 'PKR — Pakistani Rupee',
  'INR' => 'INR — Indian Rupee',
  'SAR' => 'SAR — Saudi Riyal',
  'KWD' => 'KWD — Kuwaiti Dinar',
  'AED' => 'AED — UAE Dirham',
  'GBP' => 'GBP — British Pound',
  'EUR' => 'EUR — Euro',
  'AUD' => 'AUD — Australian Dollar'
];

$stmt = $pdo->query("SELECT key_name, key_value FROM settings");
foreach ($stmt as $row) {
    if (array_key_exists($row['key_name'], $keys)) {
        $keys[$row['key_name']] = $row['key_value'];
    }
}

$success = isset($_GET['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Step 1: Handle logo upload
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/assets/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $originalName = pathinfo($_FILES['logo_file']['name'], PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
    $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalName);
    $fileName = $safeName . '_' . time() . '.png'; // ✅ Force PNG format

    $targetFile = $uploadDir . $fileName;

    // ✅ Load original image
    $sourceMime = mime_content_type($_FILES['logo_file']['tmp_name']);
    if (in_array($sourceMime, ['image/jpeg', 'image/png', 'image/webp'])) {
        if ($sourceMime === 'image/jpeg') {
            $src = imagecreatefromjpeg($_FILES['logo_file']['tmp_name']);
        } elseif ($sourceMime === 'image/png') {
            $src = imagecreatefrompng($_FILES['logo_file']['tmp_name']);
        } elseif ($sourceMime === 'image/webp') {
            $src = imagecreatefromwebp($_FILES['logo_file']['tmp_name']);
        }

        if ($src) {
            // ✅ Force re-encode to RGB PNG
            $width = imagesx($src);
            $height = imagesy($src);
            $newImg = imagecreatetruecolor($width, $height);
            imagefill($newImg, 0, 0, imagecolorallocate($newImg, 255, 255, 255)); // white bg
            imagecopy($newImg, $src, 0, 0, 0, 0, $width, $height);
            imagepng($newImg, $targetFile, 8);
            imagedestroy($src);
            imagedestroy($newImg);

            $keys['company_logo_url'] = 'assets/uploads/' . $fileName;
        }
    }
}

    // ✅ NEW: Application (Header) Logo Upload
    if (isset($_FILES['app_logo_file']) && $_FILES['app_logo_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $originalName = pathinfo($_FILES['app_logo_file']['name'], PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($_FILES['app_logo_file']['name'], PATHINFO_EXTENSION));
        $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalName);

        $fileName = 'app_logo_' . $safeName . '_' . time() . '.png'; // ✅ Force PNG
        $targetFile = $uploadDir . $fileName;

        $sourceMime = mime_content_type($_FILES['app_logo_file']['tmp_name']);
        if (in_array($sourceMime, ['image/jpeg', 'image/png', 'image/webp'])) {

            if ($sourceMime === 'image/jpeg') {
                $src = imagecreatefromjpeg($_FILES['app_logo_file']['tmp_name']);
            } elseif ($sourceMime === 'image/png') {
                $src = imagecreatefrompng($_FILES['app_logo_file']['tmp_name']);
            } elseif ($sourceMime === 'image/webp') {
                $src = imagecreatefromwebp($_FILES['app_logo_file']['tmp_name']);
            }

            if ($src) {
                $width  = imagesx($src);
                $height = imagesy($src);

                $newImg = imagecreatetruecolor($width, $height);
                imagefill($newImg, 0, 0, imagecolorallocate($newImg, 255, 255, 255)); // white bg
                imagecopy($newImg, $src, 0, 0, 0, 0, $width, $height);

                imagepng($newImg, $targetFile, 8);

                imagedestroy($src);
                imagedestroy($newImg);

                $keys['app_logo_url'] = 'assets/uploads/' . $fileName; // ✅ saved in settings
            }
        }
    }
    
    // ✅ Step 2: Pull posted values (IGNORE currency_symbol from browser)
    foreach ($keys as $key => &$val) {
        if (!isset($_POST[$key])) continue;
    
        // Never trust or store any symbol coming from browser
        if ($key === 'currency_symbol') continue;
    
        $val = trim((string)$_POST[$key]);
    }
    unset($val); // good practice after reference loop
    
    // ✅ Force "currency_symbol" to be the ISO code (English)
    // This preserves your existing setting key name without breaking other files.
    $keys['currency_symbol'] = !empty($keys['currency_code']) ? $keys['currency_code'] : 'CAD';
    
    // ✅ Step 2b: Save settings to DB
    foreach ($keys as $key => $val) {
        $stmt = $pdo->prepare("SELECT 1 FROM settings WHERE key_name = ? LIMIT 1");
        $stmt->execute([$key]);
        
        $exists = (bool)$stmt->fetchColumn();
        
        if ($exists) {
            $update = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
            $update->execute([$val, $key]);
        } else {
            $insert = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)");
            $insert->execute([$key, $val]);
        }
    }

    // ✅ Step 3: Handle permissions (optional)
    if (isset($_POST['permissions'])) {
        $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([1]);

        $insert = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($_POST['permissions'] as $pid) {
            $insert->execute([1, intval($pid)]);
        }
    }

    // ✅ Step 4: Redirect after saving to prevent resubmission
    header("Location: settings-basic.php?success=1");
    exit;
} // ✅ This ends the POST handler

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Basic Settings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php require 'styles.php'; ?>
  <style>
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

    .col-span-2 {
      grid-column: span 2;
    }

    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      .col-span-2 {
        grid-column: span 1;
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
      <div class="alert alert-success" id="successAlert" style="background: rgba(76, 201, 240, 0.2); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-cloud-upload-alt" style="font-size: 1.2rem;"></i>
        <strong>Settings updated successfully!</strong>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-cog"></i> Settings</h1>
    </div>

    <div class="card">
      <form method="post" enctype="multipart/form-data">
        <!-- Business Info -->
        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-building"></i> Business Information</h2>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Company Name</label>
              <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($keys['company_name']) ?>" <?= $can_update ? '' : 'readonly' ?>>
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input type="text" name="company_phone" class="form-control" value="<?= htmlspecialchars($keys['company_phone']) ?>" <?= $can_update ? '' : 'readonly' ?>>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($keys['company_email']) ?>" <?= $can_update ? '' : 'readonly' ?>>
            </div>
            <div class="form-group">
              <label class="form-label">GST/HST Number</label>
              <input type="text" name="gst_number" class="form-control" value="<?= htmlspecialchars($keys['gst_number']) ?>" <?= $can_update ? '' : 'readonly' ?>>
            </div>
            <div class="form-group">
              <label class="form-label">Invoice Currency</label>
            
              <select name="currency_code" class="form-control" <?= $can_update ? '' : 'disabled' ?>>
                <?php foreach ($currencyOptions as $code => $label): ?>
                  <option value="<?= htmlspecialchars($code) ?>" <?= ($keys['currency_code'] === $code ? 'selected' : '') ?>>
                    <?= htmlspecialchars($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            
              <!-- keep symbol stored too (server will still enforce it) -->
              <input type="hidden" name="currency_symbol" value="<?= htmlspecialchars($keys['currency_code']) ?>">
            </div>
            <div class="form-group col-span-2">
              <label class="form-label">Address</label>
              <textarea name="company_address" class="form-control" rows="2" <?= $can_update ? '' : 'readonly' ?>><?= htmlspecialchars($keys['company_address']) ?></textarea>
            </div>
            <div class="form-group col-span-2">
              <label class="form-label">Invoice Notice</label>
              <textarea name="invoice_notice" class="form-control" rows="2" <?= $can_update ? '' : 'readonly' ?>><?= htmlspecialchars($keys['invoice_notice']) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Company Logo -->
        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-image"></i> Company Logo</h2>
          <div class="form-group">
            <label class="form-label">Upload Logo</label>
            <input type="file" name="logo_file" class="form-control" accept="image/*" <?= $can_update ? '' : 'disabled' ?>>
            <?php if (!empty($keys['company_logo_url'])): ?>
              <div style="margin-top: 10px;">
                <?php
                $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
                $logoUrl = $baseUrl . ltrim($keys['company_logo_url'], '/');
                ?>
                <img src="<?= $logoUrl ?>" style="max-height: 60px; border-radius: 6px;">
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Add this right after the Logo upload group -->
        <div class="form-group">
          <label for="invoice_prefix">Invoice Prefix</label>
          <input
            type="text"
            id="invoice_prefix"
            name="invoice_prefix"
            class="form-control"
            placeholder="e.g. FIN"
            value="<?php echo htmlspecialchars($keys['invoice_prefix']); ?>"
          >
          <small class="form-text text-muted">
            This will prefix all invoice numbers (e.g. FIN-ACME).
          <small class="form-text text-muted">
        </div>
        
                <!-- Admin Email -->
        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-envelope"></i> Admin Email</h2>
          <div class="form-group">
            <label class="form-label" for="admin_email">Email Address to Receive Preview/Test Emails</label>
            <input type="email" name="admin_email" id="admin_email" class="form-control" value="<?= htmlspecialchars($keys['admin_email']) ?>" placeholder="admin@example.com" <?= $can_update ? '' : 'readonly' ?>>
          </div>
        </div>

        <!-- Application Settings -->
        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-desktop"></i> Application Settings</h2>

          <div class="form-group">
            <label class="form-label">Application Logo (Header)</label>
            <input type="file" name="app_logo_file" class="form-control" accept="image/*" <?= $can_update ? '' : 'disabled' ?>>

            <?php if (!empty($keys['app_logo_url'])): ?>
              <div style="margin-top: 10px;">
                <?php
                  // Works with BASE_URL if defined, otherwise uses relative path
                  if (defined('BASE_URL')) {
                      $logoUrl = rtrim(BASE_URL, '/') . '/' . ltrim($keys['app_logo_url'], '/');
                  } else {
                      $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
                      $logoUrl = $baseUrl . ltrim($keys['app_logo_url'], '/');
                  }
                ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" style="max-height: 45px; border-radius: 10px; background:#fff; padding:6px; border:1px solid #e5e7eb;">
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($can_update): ?>
          <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Settings
            </button>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>
<?php require 'scripts.php'; ?>
<script>
  // Auto-hide success alert after 5 seconds
  setTimeout(() => {
    const success = document.getElementById('successAlert');
    if (success) success.style.display = 'none';
  }, 5000);
</script>
</body>
</html>