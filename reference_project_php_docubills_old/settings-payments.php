<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$activeMenu = 'settings';
$activeTab = 'payments';
require_once 'config.php';
require_once 'middleware.php'; // ✅ Add middleware

// ✅ Permission flags
$can_manage_payment_methods = has_permission('manage_payment_methods');   // Page-level / legacy
$can_manage_card_payments   = has_permission('manage_card_payments');     // Stripe / Square / Test Mode
$can_manage_bank_details    = has_permission('manage_bank_details');      // Banking Details block

// 🚫 Block access if user has *none* of the payment-related permissions
if (
    !$can_manage_payment_methods &&
    !$can_manage_card_payments &&
    !$can_manage_bank_details
) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}

require 'styles.php';

// Load payment settings
$keys = [
    'stripe_publishable'      => '',
    'stripe_secret'           => '',
    'square_token'            => '',
    'test_mode'               => 'off',
    // 🏦 Default banking details (will show on invoice & can be overridden per invoice later)
    'bank_account_name'       => '',
    'bank_name'               => '',
    'bank_account_number'     => '',
    'bank_iban'               => '',
    'bank_swift'              => '',
    'bank_routing'            => '',
    'bank_additional_info'    => '',
];

$stmt = $pdo->query("SELECT key_name, key_value FROM settings");
foreach ($stmt as $row) {
    if (array_key_exists($row['key_name'], $keys)) {
        $keys[$row['key_name']] = $row['key_value'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 💳 Card / Stripe / Square / Test Mode
    if ($can_manage_card_payments) {
        // Toggle test mode
        $keys['test_mode'] = isset($_POST['test_mode']) ? 'on' : 'off';

        foreach ([
            'stripe_publishable',
            'stripe_secret',
            'square_token',
        ] as $k) {
            if (isset($_POST[$k])) {
                $keys[$k] = trim($_POST[$k]);
            }
        }
    }

    // 🏦 Banking details
    if ($can_manage_bank_details) {
        foreach ([
            'bank_account_name',
            'bank_name',
            'bank_account_number',
            'bank_iban',
            'bank_swift',
            'bank_routing',
            'bank_additional_info',
        ] as $k) {
            if (isset($_POST[$k])) {
                $keys[$k] = trim($_POST[$k]);
            }
        }
    }

    // 💾 Save to DB (keys not in allowed sections keep their loaded values)
    foreach ($keys as $key => $val) {
        $check = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
        $check->execute([$key]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?")->execute([$val, $key]);
        } else {
            $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?)")->execute([$key, $val]);
        }
    }

    $success = "✅ Payment settings saved.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Payment Methods</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require 'styles.php'; ?>
    <style>
        :root { /* same CSS variables as settings.php */
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
        .app-container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem; transition: var(--transition); }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; font-weight: 700; color: var(--primary); }
        .card { background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); padding: 2rem; margin-bottom: 1.5rem; }
        .form-section { border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1.5rem; }
        .form-section-title { font-weight: 600; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid var(--border); border-radius: var(--radius); background: var(--card-bg); color: var(--dark); font-size: 1rem; transition: var(--transition); }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15); }
        .btn { padding: 0.8rem 1.5rem; border-radius: var(--radius); border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 1rem; transition: var(--transition); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--secondary); box-shadow: var(--shadow-hover); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .col-span-2 { grid-column: span 2; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .col-span-2 { grid-column: span 1; } }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    <div class="app-container">
        <?php require 'sidebar.php'; ?>
        <div class="main-content">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" id="successAlert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-cog"></i> Settings <span style="font-size:0.9rem;color:#888;">/ Payment Methods</span></h1>
            </div>

            <div class="card">
                                <form method="post">

                    <?php if ($can_manage_card_payments): ?>
                    <div class="form-section">
                        <h2 class="form-section-title"><i class="fas fa-credit-card"></i> Payment Integration</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Stripe Publishable Key</label>
                                <input type="text"
                                       name="stripe_publishable"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['stripe_publishable']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Stripe Secret Key</label>
                                <input type="text"
                                       name="stripe_secret"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['stripe_secret']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Square Access Token</label>
                                <input type="text"
                                       name="square_token"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['square_token']) ?>">
                            </div>
                            <div class="form-group col-span-2">
                                <label class="form-label">
                                    <input type="checkbox"
                                           name="test_mode"
                                           <?= $keys['test_mode'] === 'on' ? 'checked' : '' ?>>
                                    Enable Test Mode
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($can_manage_bank_details): ?>
                    <div class="form-section">
                        <h2 class="form-section-title"><i class="fas fa-university"></i> Banking Details</h2>
                        <p style="font-size:0.9rem;color:#666;margin-bottom:1rem;">
                            These details will appear on your invoices so clients can pay you via bank transfer.
                            You’ll still be able to adjust them on individual invoices if needed.
                        </p>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text"
                                       name="bank_account_name"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['bank_account_name']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Bank Name</label>
                                <input type="text"
                                       name="bank_name"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['bank_name']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Account Number</label>
                                <input type="text"
                                       name="bank_account_number"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['bank_account_number']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">IBAN</label>
                                <input type="text"
                                       name="bank_iban"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['bank_iban']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">SWIFT / BIC</label>
                                <input type="text"
                                       name="bank_swift"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['bank_swift']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Routing / Sort Code</label>
                                <input type="text"
                                       name="bank_routing"
                                       class="form-control"
                                       value="<?= htmlspecialchars($keys['bank_routing']) ?>">
                            </div>

                            <div class="form-group col-span-2">
                                <label class="form-label">Additional Payment Instructions</label>
                                <textarea name="bank_additional_info"
                                          class="form-control"
                                          rows="3"><?= htmlspecialchars($keys['bank_additional_info']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div style="margin-top:2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php require 'scripts.php'; ?>
</body>
</html>