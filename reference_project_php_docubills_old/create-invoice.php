<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$activeMenu = 'create-invoice'; // âœ… Required for active tab highlighting
require_once 'config.php';
require_once 'middleware.php'; // âœ… Add middleware file

// âœ… Check permission
if (!has_permission('create_invoice')) {
    die('Access Denied');
}

require 'styles.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Invoice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    .position-relative {
      position: relative;
    }

    .autocomplete-list {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-top: none;
      border-radius: 0 0 var(--radius) var(--radius);
      box-shadow: var(--shadow);
      max-height: 220px;
      overflow-y: auto;
      z-index: 1000;
    }

    .autocomplete-item {
      padding: 0.5rem 0.75rem;
      cursor: pointer;
      font-size: 0.95rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.5rem;
    }

    .autocomplete-item:hover {
      background: rgba(67, 97, 238, 0.08);
    }

    .autocomplete-company {
      font-weight: 500;
      color: var(--dark);
    }

    .autocomplete-rep {
      font-size: 0.85rem;
      color: var(--gray);
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
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 1.2rem;
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

    .required::after {
      content: " *";
      color: var(--danger);
    }
    
    .error-text {
      margin-top: 0.4rem;
      font-size: 0.85rem;
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
    
    /* Custom-table builder styles */
    #custom-table-container table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    #custom-table-container th,
    #custom-table-container td {
      border: 1px solid var(--border);
      padding: 0.5rem;
      min-height: 2rem;
    }
  </style>
</head>
<body>
<?php require 'header.php'; ?>
<?php if (isset($_GET['debug'])) echo '<script>document.body.classList.add("dark-mode")</script>'; ?>

<div class="app-container">
  <?php require 'sidebar.php'; ?>

  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Create New Invoice</h1>
    </div>

      <form id="invoiceForm" method="post" enctype="multipart/form-data">
        <!-- Hidden field to carry custom table HTML -->
          <input type="hidden" name="custom_table_html" id="custom_table_html" value="">
        <!-- Bill To Section -->
         <div class="form-section">
          <h2 class="form-section-title">
            <i class="fas fa-building"></i> Bill To Information
          </h2><br>

          <div class="form-grid">
            <div class="form-group position-relative">
              <label for="bill_to_name" class="form-label required">Company Name</label>
              <input type="text" id="bill_to_name" name="bill_to_name" class="form-control" placeholder="Enter company name" required autocomplete="off">
              
              <!-- ðŸ”½ Autocomplete suggestions will appear here -->
              <div id="clientSuggestions" class="autocomplete-list" style="display:none;"></div>
            </div>
            
            <div class="form-group">
              <label for="bill_to_rep" class="form-label">Contact Name</label>
              <input type="text" id="bill_to_rep" name="bill_to_rep" class="form-control" placeholder="Contact person's name">
            </div>
            
            <div class="form-group">
              <label for="bill_to_address" class="form-label">Address</label>
              <input type="text" id="bill_to_address" name="bill_to_address" class="form-control" placeholder="Full address">
            </div>
            
            <div class="form-group">
              <label for="bill_to_phone" class="form-label">Phone</label>
              <input type="text" id="bill_to_phone" name="bill_to_phone" class="form-control" placeholder="Phone number">
            </div>
            
            <div class="form-group">
              <label for="bill_to_email" class="form-label required">Email</label>
              <input type="email" id="bill_to_email" name="bill_to_email" class="form-control" placeholder="Email address" required>
            </div>
          </div>
        </div>
        
        <!-- Data Source Section -->
        <div class="form-section">
          <h2 class="form-section-title">
            <i class="fas fa-database"></i> Invoice Data Source
          </h2>
          
          <!-- START: Data Source Options -->
          <div class="form-group">
            <label class="form-label">Choose Invoice Source:</label><br>
            <label style="margin-right:1em">
              <input type="radio" name="invoice_source" value="google" checked>
              Google Sheet URL
            </label>
            <label style="margin-right:1em">
              <input type="radio" name="invoice_source" value="upload">
              Upload Excel File
            </label>
          </div>
          <!-- END: Data Source Options -->

          <!-- Google Section -->
          <div id="google-section">
            <div class="form-group">
              <label for="google_sheet_url" class="form-label">Google Sheet URL</label>
              <input type="url" id="google_sheet_url" name="google_sheet_url" class="form-control" placeholder="https://docs.google.com/spreadsheets/...">
              <p class="upload-hint">Make sure the Google Sheet is set to "Anyone with the link can view"</p>
            </div>
          </div>
          
          <!-- Upload Section (hidden by default) -->
          <div id="upload-section" style="display:none;">
            <div class="form-group">
              <label class="form-label">Upload Excel File</label>
              <div class="upload-container" id="uploadArea">
                <div class="upload-icon">
                  <i class="fas fa-file-excel"></i>
                </div>
                <p class="upload-text">Drag & drop your Excel file here or click to browse</p>
                <p class="upload-hint">Supports .xls and .xlsx formats</p>
                <input type="file" id="excel_file" name="excel_file" accept=".xls,.xlsx" style="display: none;">
              </div>
              <div id="fileName" style="margin-top: 10px; font-size: 0.9rem; color: var(--primary); display: none;"></div>
                <div id="fileName" style="margin-top: 10px; font-size: 0.9rem; color: var(--primary); display: none;"></div>
               <div id="fileError" class="error-text" style="display:none;"></div>
            </div>
          </div>
          
          <!-- Custom Table Builder (hidden by default) -->
          <div id="custom-table-builder" style="display:none; margin-top:1.5rem;">
            <!-- 3A. Column count selector + Generate button -->
            <div class="form-group">
              <label for="custom-col-count" class="form-label">Number of columns (1â€“7):</label>
              <select id="custom-col-count" class="form-control" style="width: auto; display: inline-block; margin-left:0.5rem;">
                <?php for($i = 1; $i <= 7; $i++): ?>
                  <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
              </select>
              <button type="button" id="generate-custom-table" class="btn btn-secondary btn-sm" style="margin-left:1rem;">
                Generate Table
              </button>
            </div>
            <!-- 3B. Container where the editable table will appear -->
            <div id="custom-table-container"></div>
          </div>
          
        </div> <!-- .form-section -->
        
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-file-invoice"></i> Create Invoice
        </button>
      </form>
    </div>
  </div>

<script>
  // File upload handling + front-end validation (.xls / .xlsx only)
  const uploadArea       = document.getElementById('uploadArea');
  const fileInput        = document.getElementById('excel_file');
  const fileNameDisplay  = document.getElementById('fileName');
  const fileErrorDisplay = document.getElementById('fileError');

  function clearFileMessages() {
    if (fileErrorDisplay) {
      fileErrorDisplay.textContent = '';
      fileErrorDisplay.style.display = 'none';
    }
  }

  function showFileError(msg) {
    if (fileErrorDisplay) {
      fileErrorDisplay.textContent = msg;
      fileErrorDisplay.style.display = 'block';
    } else {
      alert(msg);
    }
  }

  function isValidExcelFile(file) {
    if (!file) return false;
    const allowedExtensions = ['xls', 'xlsx'];
    const name = file.name || '';
    const ext  = name.split('.').pop().toLowerCase();
    return allowedExtensions.includes(ext);
  }

  function handleFileSelection(file) {
    clearFileMessages();

    if (!file) {
      fileNameDisplay.style.display = 'none';
      return;
    }

    if (!isValidExcelFile(file)) {
      // Reset the input so nothing invalid is submitted
      fileInput.value = '';
      fileNameDisplay.style.display = 'none';
      showFileError('Only .xls or .xlsx Excel files are allowed.');
      return;
    }

    fileNameDisplay.textContent = 'Selected file: ' + file.name;
    fileNameDisplay.style.display = 'block';
  }

  if (uploadArea && fileInput) {
    uploadArea.addEventListener('click', () => {
      clearFileMessages();
      fileInput.click();
    });

    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = '#4361ee';
      uploadArea.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.borderColor = 'var(--border)';
      uploadArea.style.backgroundColor = '';
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = 'var(--border)';
      uploadArea.style.backgroundColor = '';

      const file = e.dataTransfer.files && e.dataTransfer.files[0];
      if (!file) return;

      if (!isValidExcelFile(file)) {
        showFileError('Only .xls or .xlsx Excel files are allowed.');
        return;
      }

      // Keep only the valid file
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      fileInput.files = dataTransfer.files;

      handleFileSelection(file);
    });

    fileInput.addEventListener('change', function () {
      const file = fileInput.files && fileInput.files[0];
      handleFileSelection(file);
    });
  }
</script>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
  const form   = this;
  const source = form.querySelector('input[name="invoice_source"]:checked').value;

  if (source === 'custom') {
    // 1) Capture the custom table HTML
    const tbl = document.querySelector('#custom-table-container table');
    document.getElementById('custom_table_html').value = tbl ? tbl.outerHTML : '';

    // 2) Route to the preview page
    form.action = 'generate_invoice.php';
  } else {
    // Route to the Excel/Google parser
    form.action = 'parse_excel.php';
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form        = document.getElementById('invoiceForm');
  const fileInput   = document.getElementById('excel_file');

  if (!form) return;

  form.addEventListener('submit', function(e) {
    const source = form.querySelector('input[name="invoice_source"]:checked').value;

    // Always clear any old file error
    if (typeof clearFileMessages === 'function') {
      clearFileMessages();
    }

    // ✅ Validate upload source: must be .xls or .xlsx, and must be selected
    if (source === 'upload') {
      const file = fileInput.files && fileInput.files[0];

      if (!file) {
        e.preventDefault();
        if (typeof showFileError === 'function') {
          showFileError('Please choose an Excel file before continuing.');
        } else {
          alert('Please choose an Excel file before continuing.');
        }
        return;
      }

      if (typeof isValidExcelFile === 'function' && !isValidExcelFile(file)) {
        e.preventDefault();
        if (typeof showFileError === 'function') {
          showFileError('Only .xls or .xlsx Excel files are allowed.');
        } else {
          alert('Only .xls or .xlsx Excel files are allowed.');
        }
        return;
      }
    }

    // ✅ Custom source: send to preview
    if (source === 'custom') {
      const tbl = document.querySelector('#custom-table-container table');
      document.getElementById('custom_table_html').value = tbl ? tbl.outerHTML : '';
      form.action = 'generate_invoice.php';
    } else {
      // ✅ Google Sheet or Upload both go to parse_excel
      form.action = 'parse_excel.php';
    }
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const companyInput     = document.getElementById('bill_to_name');
  const repInput         = document.getElementById('bill_to_rep');
  const addressInput     = document.getElementById('bill_to_address');
  const phoneInput       = document.getElementById('bill_to_phone');
  const emailInput       = document.getElementById('bill_to_email');
  const suggestionsBox   = document.getElementById('clientSuggestions');

  let lastQuery = '';

  if (!companyInput || !suggestionsBox) {
    return; // safety
  }

  function clearSuggestions() {
    suggestionsBox.innerHTML = '';
    suggestionsBox.style.display = 'none';
  }

  document.addEventListener('click', function (e) {
    if (!suggestionsBox.contains(e.target) && e.target !== companyInput) {
      clearSuggestions();
    }
  });

  companyInput.addEventListener('input', function () {
    const q = this.value.trim();

    if (q.length < 1) {
      clearSuggestions();
      return;
    }

    if (q === lastQuery) {
      return;
    }
    lastQuery = q;

    fetch('search_clients.php?q=' + encodeURIComponent(q))
      .then(response => response.json())
      .then(data => {
        suggestionsBox.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {
          clearSuggestions();
          return;
        }

        data.forEach(client => {
          const item = document.createElement('div');
          item.className = 'autocomplete-item';
          item.dataset.company_name   = client.company_name || '';
          item.dataset.representative = client.representative || '';
          item.dataset.address        = client.address || '';
          item.dataset.phone          = client.phone || '';
          item.dataset.email          = client.email || '';

          const left = document.createElement('div');
          left.className = 'autocomplete-company';
          left.textContent = client.company_name || '';

          const right = document.createElement('div');
          right.className = 'autocomplete-rep';
          right.textContent = client.representative ? ('Contact: ' + client.representative) : '';

          item.appendChild(left);
          item.appendChild(right);

          item.addEventListener('click', function () {
            companyInput.value = this.dataset.company_name || '';
            if (repInput)     repInput.value     = this.dataset.representative || '';
            if (addressInput) addressInput.value = this.dataset.address || '';
            if (phoneInput)   phoneInput.value   = this.dataset.phone || '';
            if (emailInput)   emailInput.value   = this.dataset.email || '';
            clearSuggestions();
          });

          suggestionsBox.appendChild(item);
        });

        suggestionsBox.style.display = 'block';
      })
      .catch(err => {
        console.error('Client autocomplete error:', err);
        clearSuggestions();
      });
  });

  companyInput.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      clearSuggestions();
    }
  });
});
</script>

<?php require 'scripts.php'; ?>
</body>
</html>