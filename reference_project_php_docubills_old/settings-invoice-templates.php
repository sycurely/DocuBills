<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$activeMenu = 'settings';
$activeTab = 'invoice_templates';
$activeSub = '';
require_once 'config.php';
require_once 'middleware.php'; // ✅ Add middleware

// ✅ Check permission
if (!has_permission('manage_invoice_templates')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}
$canEditTemplate = has_permission('edit_email_template');
$canAddTemplate = has_permission('add_email_template');

$isEdit = isset($_GET['id']);
$canManageTemplates = $isEdit ? $canEditTemplate : $canAddTemplate;


require 'styles.php';
require 'sidebar.php';

// Handle edit param
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$template = [
    'template_name' => '',
    'assigned_notification_type' => '',
    'html_content' => '',
    'design_json' => ''
];
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE id = ?");
    $stmt->execute([$edit_id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC) ?: $template;
}

// Success / error messages
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Invoice Templates</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require 'styles.php'; ?>
    <style>
        /* CSS vars and styling identical to settings-basic.php */
        :root { /* ... same variables ... */ }
        .alert {
          padding: 1rem;
          border-radius: var(--radius);
          margin-bottom: 1.5rem;
          font-weight: 600;
          display: flex;
          align-items: center;
          gap: 0.6rem;
        }
        
        :root {
          --success-rgb: 76, 201, 240;
        }
        body.dark-mode {
          --success-rgb: 94, 213, 249;
        }
        .alert-success {
          background: rgba(var(--success-rgb), 0.15);
        }
        
        .alert-danger {
          background: rgba(247, 37, 133, 0.15);
          border: 1px solid var(--danger);
          color: var(--danger);
        }

        .app-container { display:flex; min-height:100vh; }
        .main-content { flex:1; padding:calc(var(--header-height)+1.5rem) 1.5rem 1.5rem; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
        .page-title { font-size:1.8rem; font-weight:700; color:var(--primary); }
        .card { background:var(--card-bg); border-radius:var(--radius); box-shadow:var(--shadow); padding:2rem; margin-bottom:1.5rem; }
        .form-section { border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:1.5rem; }
        .form-section-title { font-weight:600; color:var(--primary); margin-bottom:1rem; display:flex; align-items:center; gap:10px; }
        .form-group { margin-bottom:1.2rem; }
        .form-control, select.form-control { width:100%; padding:0.8rem 1rem; border:1px solid var(--border); border-radius:var(--radius); background:var(--card-bg); color:var(--dark); font-size:1rem; }
        .form-control:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(67,97,238,0.15); }
        .btn { padding:0.8rem 1.5rem; border-radius:var(--radius); border:none; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:var(--transition); font-size:1rem; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--secondary); box-shadow:var(--shadow-hover); }
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
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger" id="errorAlert">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['debug'])) echo '<script>document.body.classList.add("dark-mode")</script>'; ?>

            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-cog"></i> Settings <span style="font-size:0.9rem;color:#888;">/ Invoice Templates</span></h1>
            </div>

            <!-- Template Form -->
            <div class="card">
                <?php if ($canManageTemplates): ?>
                    <form method="post" id="templateForm" action="<?= $edit_id ? 'update_invoice_template.php?id='.$edit_id : 'save_invoice_template.php' ?>">
                <?php else: ?>
                    <div class="alert alert-danger"><i class="fas fa-lock"></i> You do not have permission to create email templates.</div>
                <?php endif; ?>

                    <div class="form-section">
                        <h2 class="form-section-title"><i class="fas fa-envelope"></i> <?= $edit_id ? 'Edit' : 'Create' ?> Template</h2>
                        <div class="form-group">
                            <label class="form-label">Template Name</label>
                            <input type="text" name="template_name" class="form-control" required value="<?= htmlspecialchars($template['template_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notification Type</label>
                            <select name="assigned_notification_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <?php foreach([
                                    'invoice_available'=>'Invoice Available',
                                    'payment_success'=>'Payment Successful',
                                    'payment_failed'=>'Payment Declined',
                                    'marked_paid'=>'Invoice Marked Paid',
                                    'marked_unpaid'=>'Invoice Marked Unpaid'
                                ] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $template['assigned_notification_type']=== $val?'selected':'' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Design Email</label>
                            <div id="emailEditor" style="height:400px;border:1px solid var(--border);"></div>
                            <input type="hidden" name="html_content" id="emailHtmlContent" value="<?= htmlspecialchars($template['html_content']) ?>">
                            <input type="hidden" name="design_json" id="designJson" value="<?= htmlspecialchars($template['design_json'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                          <label class="form-label">Paste Unlayer JSON (Import)</label>
                          <textarea id="importJson" class="form-control" rows="6" placeholder="Paste Unlayer JSON here to load design..."></textarea>
                          <?php if ($canManageTemplates): ?>
                              <button type="button" class="btn btn-outline" style="margin-top:10px;" onclick="loadImportedDesign()">Load Design</button>
                          <?php endif; ?>
                        </div>
                    </div>
                  <?php if ($canManageTemplates): ?>
                      <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $edit_id ? 'Update' : 'Save' ?> Template
                      </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://editor.unlayer.com/embed.js"></script>
    <script>
        const emailEditor = unlayer.createEditor({
          id: 'emailEditor',
          displayMode: 'email',
          projectId: 12345, // ← Optional, but can be anything for local mode
          appearance: {
            theme: 'light',
            panels: {
              tools: { dock: 'right' }
            }
          },
          features: {
            export: true,
            save: true
          },
          customCSS: [],
          editor: {
            displayToolbar: true,        // ✅ THIS enables ⚙️ toolbar menu
            minHeight: '400px',
            maxHeight: '100%',
            preview: true
          }
        });
        emailEditor.addEventListener('editor:ready', function() {
          const raw = document.getElementById('designJson').value;
          if (raw) {
            try {
              const design = JSON.parse(raw);
              emailEditor.loadDesign(design);
            } catch (e) { console.error('Invalid design JSON', e); }
          }
        });
        
        document.querySelector('form[action*="email_template.php"]').addEventListener('submit', function(e) {
          e.preventDefault();
          emailEditor.exportHtml(function(data) {
            document.getElementById('emailHtmlContent').value = data.html;
            document.getElementById('designJson').value = JSON.stringify(data.design);
            e.target.submit();
          });
        });
        
        document.getElementById('templateForm').addEventListener('submit', function(e) {
          e.preventDefault();
          emailEditor.exportHtml(function(data) {
            document.getElementById('emailHtmlContent').value = data.html;
            document.getElementById('designJson').value = JSON.stringify(data.design);
            e.target.submit(); // ← Now it will submit only after setting both fields
          });
        });
        
    function loadImportedDesign() {
      try {
        const raw = document.getElementById('importJson').value;
        const json = JSON.parse(raw);
        emailEditor.loadDesign(json);
        alert("✅ Design imported successfully!");
      } catch (e) {
        alert("⚠️ Invalid JSON format.");
        console.error(e);
      }
    }

    </script>

<script>
  const editor = unlayer; // Ensure this is the Unlayer editor instance

  function exportHtmlBeforeSubmit(e) {
    e.preventDefault(); // Prevent normal submit

    editor.exportHtml(data => {
      const html = data.html;
      document.querySelector('textarea[name="html_content"]').value = html;
      e.target.submit(); // Now submit with HTML content injected
    });
  }

  document.querySelector('form').addEventListener('submit', exportHtmlBeforeSubmit);
</script>

<script>
setTimeout(() => {
  const success = document.getElementById('successAlert');
  const error = document.getElementById('errorAlert');
  if (success) success.style.display = 'none';
  if (error) error.style.display = 'none';
}, 5000);
</script>

<?php require 'scripts.php'; ?>
</body>
</html>