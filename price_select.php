<?php
session_start();
$activeMenu = 'create-invoice';
require_once 'config.php';
require_once 'middleware.php';

// 🔐 If session expired, force login again
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?error=' . urlencode('Your session has expired. Please log in again.'));
    exit;
}

$headers = $_SESSION['invoice_data']['headers'];
$items   = $_SESSION['invoice_data']['items'] ?? [];

// 🔹 Error message to show on this page
$error = isset($_GET['error']) ? trim($_GET['error']) : '';

/**
 * Normalize a value from Excel into a float.
 * Handles:
 * - numbers stored as strings
 * - spaces / non-breaking spaces
 * - commas vs dots
 * - extra currency symbols / text
 */
function parseAmount($value): float {
    if ($value === null || $value === '') {
        return 0.0;
    }

    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }

    $str = (string) $value;

    // Remove regular spaces + non-breaking spaces
    $str = str_replace(["\xC2\xA0", ' '], '', $str);

    // If we have both comma and dot, treat comma as thousands sep and remove it
    if (strpos($str, ',') !== false && strpos($str, '.') !== false) {
        $str = str_replace(',', '', $str);
    } else {
        // Otherwise treat comma as decimal separator
        $str = str_replace(',', '.', $str);
    }

    // Strip everything except digits, dot and minus
    $str = preg_replace('/[^0-9.\-]/', '', $str);

    return is_numeric($str) ? (float) $str : 0.0;
}

// ─────────────────────────────────────────────
// Handle form submission on THIS page
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priceMode   = $_POST['price_mode']   ?? 'column';
    $priceColumn = $_POST['price_column'] ?? null;
    $includeCols = isset($_POST['include_cols']) ? (array)$_POST['include_cols'] : [];

    if ($priceMode === 'column') {
        if (!$priceColumn) {
            $error = 'Please select a price column for automatic pricing.';
        } else {
            // 🔍 Calculate total for the selected price column
            $sum = 0.0;
            foreach ($items as $row) {
                $sum += parseAmount($row[$priceColumn] ?? '');
            }

            if ($sum <= 0) {
                // ❌ Column is "bad" from our point of view
                $error = 'The selected price column did not produce a valid total amount. '
                       . 'Please choose a different column (for example, "Sub Total") '
                       . 'or verify your data before continuing.';
            } else {
                // ✅ Column looks good – store choices and go to preview
                $_SESSION['price_config'] = [
                    'price_mode'   => $priceMode,
                    'price_column' => $priceColumn,
                    'include_cols' => $includeCols,
                    'pre_total'    => $sum,
                ];

                // Go to preview; preview will read config from session
                header('Location: generate_invoice.php');
                exit;
            }
        }
    } else {
        // Manual pricing: we don't need a numeric column, just remember columns
        $_SESSION['price_config'] = [
            'price_mode'   => $priceMode,
            'price_column' => null,
            'include_cols' => $includeCols,
            'pre_total'    => 0,
        ];

        header('Location: generate_invoice.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Price Column</title>
  <?php require 'styles.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
      .app-container { display: flex; min-height: 100vh; }
      .main-content {
        flex: 1;
        padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
        transition: var(--transition);
        background: var(--body-bg);
      }
      .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
      }
      .page-title { font-size: 1.8rem; font-weight: 700; color: var(--primary); }
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
        background: var(--card-bg);
      }
      .form-section-title {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .form-group { margin-bottom: 1.2rem; }
      .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
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
      .price-option {
        padding: 1rem;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 1rem;
        cursor: pointer;
        transition: var(--transition);
      }
      .price-option:hover {
        border-color: var(--primary-light);
        background-color: rgba(67, 97, 238, 0.05);
      }
      .price-option.active {
        border-color: var(--primary);
        background-color: rgba(67, 97, 238, 0.1);
      }
      
      /* always hide the column-picker unless its .price-option has .active */
        .price-option .column-options {
          display: none;
      }
      
        .price-option.active .column-options {
          display: block;
      }
      
      .column-options {
        padding: 1rem;
        background: rgba(0,0,0,0.03);
        border-radius: var(--radius);
        margin-top: 1rem;
      }

    .manual-notice {
      background-color: #fff8e6;
      border-left: 4px solid var(--warning);
      padding: 1rem;
      margin-top: 1rem;
      border-radius: 0 var(--radius) var(--radius) 0;
      color: var(--dark);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 14px;
    }
    
    .dark-mode .manual-notice {
      background-color: #2d3748;
      color: #e2e8f0;
      border-left: 4px solid var(--warning);
    }

    .hidden { display: none; }
    
    .alert {
      padding: 0.9rem 1rem;
      border-radius: var(--radius);
      margin-bottom: 1rem;
      border-left: 4px solid var(--danger);
      background-color: #ffe5ea;
      color: #721c24;
      font-size: 0.95rem;
    }

    .dark-mode .alert {
      background-color: #4a1f2a;
      color: #f8d7da;
      border-left-color: var(--danger);
    }
    </style>
</head>
<body>
  <div class="app-container">
    <?php require 'sidebar.php'; ?>
    <?php require 'header.php'; ?>
    <div class="main-content">
      <div class="page-header">
        <h1 class="page-title">Configure Invoice Pricing</h1>
      </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert">
          <i class="fas fa-exclamation-triangle"></i>
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

      <div class="card">
      <form method="post" action="" id="invoiceForm">
        <div class="form-section">
          <h2 class="form-section-title"><i class="fas fa-money-bill-wave"></i> Pricing Method</h2>
          
          <div class="price-option active" id="autoPriceOption">
            <label>
              <input type="radio" name="price_mode" value="column" checked>
              <strong>Automatic Pricing</strong> - Use a column from my data
            </label>
            <div class="column-options">
              <p style="margin-bottom: 1.5rem;">Select which column contains item prices:</p>
              <?php foreach ($headers as $idx => $col): ?>
                <div class="form-group">
                  <label>
                    <input type="radio" name="price_column" value="<?= htmlspecialchars($col) ?>" required>
                    Column: <strong><?= htmlspecialchars($col) ?></strong>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="price-option" id="manualPriceOption">
            <label>
              <input type="radio" name="price_mode" value="manual">
              <strong>Manual Pricing</strong> - I'll enter the total invoice amount myself
            </label>
            <div class="manual-notice">
              <i class="fas fa-info-circle"></i> You'll enter the total amount on the next screen
            </div>
          </div>
        </div>
        
        <hr>

        <h2 class="form-section-title">
          <i class="fas fa-columns"></i> Columns to Include
          <small style="font-weight:400; margin-left:1rem; color:var(--gray);">
            (max 15)
          </small>
        </h2>
        <div id="columnPicker" class="form-group" style="max-height:300px; overflow:auto;">
          <?php foreach ($headers as $idx => $col): ?>
            <label style="display:block; margin-bottom:0.5rem;">
              <input type="checkbox" name="include_cols[]" value="<?= $idx ?>" checked>
              <?= htmlspecialchars($col) ?>
            </label>
          <?php endforeach; ?>
        </div>
        
        <button type="submit" class="btn btn-primary">
          Continue to Invoice Preview
        </button>
      </form>
      </div>
    </div>
  </div>
  <?php require 'scripts.php'; ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const autoOption = document.getElementById('autoPriceOption');
    const manualOption = document.getElementById('manualPriceOption');
    const priceColumnRadios = document.querySelectorAll('input[name="price_column"]');
    const priceModeRadios = document.querySelectorAll('input[name="price_mode"]');
    
    // Set initial required state
    priceColumnRadios.forEach(radio => radio.required = true);
    
    // Toggle pricing options
  function handlePriceOptionClick(option) {
  // toggle the blue “active” card
  autoOption.classList.remove('active');
  manualOption.classList.remove('active');
  option.classList.add('active');

  // if we're in Manual mode, un‐require + disable the column radios
  const isManual = option === manualOption;
  priceColumnRadios.forEach(r => {
    r.required = !isManual;
    r.disabled = isManual;
  });
}
    
    // Add click handlers to pricing options
    autoOption.addEventListener('click', function(e) {
      if (e.target.tagName !== 'INPUT') {
        handlePriceOptionClick(this);
        document.querySelector('input[name="price_mode"][value="column"]').checked = true;
      }
    });
    
    manualOption.addEventListener('click', function(e) {
      if (e.target.tagName !== 'INPUT') {
        handlePriceOptionClick(this);
        document.querySelector('input[name="price_mode"][value="manual"]').checked = true;
      }
    });
    
    // Add change handlers to radio buttons
    priceModeRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        handlePriceOptionClick(
          this.value === 'column' ? autoOption : manualOption
        );
      });
    });
    
    // Column picker limit enforcement
    const chkboxes = Array.from(document.querySelectorAll('#columnPicker input[type=checkbox]'));
    const max = 15;
    
    function enforceColumnLimit() {
      const checkedCount = chkboxes.filter(c => c.checked).length;
      chkboxes.forEach(c => {
        if (!c.checked && checkedCount >= max) {
          c.disabled = true;
        } else {
          c.disabled = false;
        }
      });
    }
    
    chkboxes.forEach(c => c.addEventListener('change', enforceColumnLimit));
    enforceColumnLimit();
    
    // Form validation
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
      const selectedMode = document.querySelector('input[name="price_mode"]:checked').value;
      const columnSelected = document.querySelector('input[name="price_column"]:checked');
      
      if (selectedMode === 'column' && !columnSelected) {
        e.preventDefault();
        alert('Please select a price column for automatic pricing');
        autoOption.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Highlight the required section
        autoOption.style.borderColor = 'var(--danger)';
        setTimeout(() => {
          autoOption.style.borderColor = '';
        }, 2000);
      }
    });
  });
  </script>
</body>
</html>