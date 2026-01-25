<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$activeMenu = 'settings';
$activeTab = 'email_templates';
$activeSub = '';
require_once 'config.php';
require_once 'middleware.php'; // ✅ Add middleware

// ✅ Check permission
if (!has_permission('access_email_templates_page')) {
    $_SESSION['access_denied'] = true;
    header('Location: access-denied.php');
    exit;
}
$canEditTemplate = has_permission('edit_email_template');
$canAddTemplate = has_permission('add_email_template');

$isEdit = isset($_GET['id']);
$canManageTemplates = $isEdit ? $canEditTemplate : $canAddTemplate;

// Handle edit param
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$template = [
    'template_name' => '',
    'assigned_notification_type' => '',
    'cc_emails' => '',
    'bcc_emails' => '',
    'template_html' => '',
    'design_json' => ''
];

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE id = ?");
    $stmt->execute([$edit_id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC) ?: $template;
}

// -------------------------------
// Notification Types (Dynamic)
// -------------------------------
$notifTypes = [];
try {
    $notifTypes = $pdo->query("
        SELECT slug, label
        FROM notification_types
        WHERE deleted_at IS NULL
           OR deleted_at = ''
           OR deleted_at = '0000-00-00 00:00:00'
        ORDER BY label ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("⚠️ notification_types fetch failed: " . $e->getMessage());
}

// -------------------------------
// Existing Templates (for dropdown)
// -------------------------------
$templatesList = [];
try {
    // Try with deleted_at first (if exists)
    try {
        $templatesList = $pdo->query("
            SELECT id, template_name
            FROM email_templates
            WHERE deleted_at IS NULL
               OR deleted_at = ''
               OR deleted_at = '0000-00-00 00:00:00'
            ORDER BY template_name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fallback without deleted_at
        $templatesList = $pdo->query("
            SELECT id, template_name
            FROM email_templates
            ORDER BY template_name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("⚠️ email_templates list fetch failed: " . $e->getMessage());
    $templatesList = [];
}

// If coming from list page "Assign" button (preselect)
if (!$edit_id && !empty($_GET['type'])) {
    $pref = trim($_GET['type']);

    // only allow valid slugs
    $valid = false;
    foreach ($notifTypes as $nt) {
        if ($nt['slug'] === $pref) { $valid = true; break; }
    }

    if ($valid) {
        $template['assigned_notification_type'] = $pref;
    }
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
    <title>Settings - Email Templates</title>
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
    <div class="app-container">
        <?php require 'sidebar.php'; ?>
        <div class="main-content">
            <?php require 'header.php'; ?>
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
                <h1 class="page-title"><i class="fas fa-cog"></i> Settings <span style="font-size:0.9rem;color:#888;">/ Email Templates</span></h1>
            </div>

            <!-- Template Form -->
            <div class="card">
              <?php if (!$canManageTemplates): ?>
                <div class="alert alert-danger">
                  <i class="fas fa-lock"></i> You do not have permission to <?= $edit_id ? 'edit' : 'create' ?> email templates.
                </div>
              <?php else: ?>
                <form method="post" id="templateForm"
                      action="<?= $edit_id ? 'update_email_template.php?id='.$edit_id : 'save_email_template.php' ?>">

                    <div class="form-section">
                        <h2 class="form-section-title"><i class="fas fa-envelope"></i> Manage Templates</h2>
                        <div class="form-group">
                          <label class="form-label">Template Name</label>
                        
                          <!-- Dropdown: pick existing OR create new -->
                          <select id="templatePicker" class="form-control">
                            <option value="__new__" <?= $edit_id ? '' : 'selected' ?> <?= $canAddTemplate ? '' : 'disabled' ?>>
                              ➕ Create New Template
                            </option>
                            <option disabled>────────────</option>
                        
                            <?php foreach ($templatesList as $t): ?>
                              <?php
                                $tplName = trim((string)($t['template_name'] ?? ''));
                                if ($tplName === '') {
                                  $tplName = 'Template #' . (int)$t['id'];
                                }
                              ?>
                              <option value="<?= (int)$t['id'] ?>"
                                <?= ($edit_id && (int)$edit_id === (int)$t['id']) ? 'selected' : '' ?>
                                <?= $canEditTemplate ? '' : 'disabled' ?>>
                                <?= htmlspecialchars($tplName) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        
                          <!-- This is what actually gets submitted to save/update scripts -->
                          <input type="hidden" name="template_name" id="templateNameHidden" value="<?= htmlspecialchars($template['template_name']) ?>">
                        
                          <!-- Only shown when "Create New Template" is selected -->
                          <div id="newTemplateNameWrap" style="display:<?= $edit_id ? 'none' : 'block' ?>; margin-top:10px;">
                            <label class="form-label" style="margin-top:10px;">New Template Name</label>
                            <input type="text" id="newTemplateNameInput" class="form-control"
                                   placeholder="Enter template name..."
                                   value="<?= $edit_id ? '' : htmlspecialchars($template['template_name']) ?>">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="form-label">Notification Type</label>
                        
                          <!-- Picker UI (like Template Name picker) -->
                          <select id="notificationTypePicker" class="form-control" required>
                            <option value="">-- Select --</option>
                        
                            <?php if ($canManageTemplates): ?>
                              <option value="__new_notif__">➕ Create New Notification</option>
                              <option disabled>────────────</option>
                            <?php endif; ?>
                        
                            <?php foreach ($notifTypes as $nt): ?>
                              <option value="<?= htmlspecialchars($nt['slug']) ?>"
                                <?= ($template['assigned_notification_type'] === $nt['slug']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nt['label']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        
                          <!-- This is what actually gets submitted -->
                          <input type="hidden"
                                 name="assigned_notification_type"
                                 id="assignedNotifHidden"
                                 value="<?= htmlspecialchars($template['assigned_notification_type'] ?? '') ?>">
                        
                          <!-- Only shown when "Create New Notification" is selected -->
                          <div id="newNotifWrap" style="display:none; margin-top:10px;">
                            <label class="form-label" style="margin-top:10px;">New Notification Name</label>
                            <input type="text"
                                   id="newNotifLabel"
                                   name="new_notification_label"
                                   class="form-control"
                                   placeholder="Example: Invoice Overdue (3 days after)">
                        
                            <label class="form-label" style="margin-top:10px;">Notification Slug</label>
                            <input type="text"
                                   id="newNotifSlug"
                                   name="new_notification_slug"
                                   class="form-control"
                                   placeholder="auto-generated (example: invoice_overdue_3_days_after)">
                        
                            <small style="display:block;margin-top:6px;color:#888;">
                              Tip: Slug should be lowercase and use letters/numbers/underscores only.
                            </small>
                          </div>
                        </div>
                        
                        <div class="form-group">
                          <label class="form-label">CC Emails (optional)</label>
                          <input
                            type="text"
                            id="ccEmails"
                            name="cc_emails"
                            class="form-control"
                            placeholder="comma-separated emails (max 10)"
                            value="<?= htmlspecialchars($template['cc_emails'] ?? '') ?>"
                          >
                          <small style="display:block;margin-top:6px;color:#888;">
                            Example: accounts@company.com, manager@company.com
                          </small>
                        </div>
                        
                        <div class="form-group">
                          <label class="form-label">BCC Emails (optional)</label>
                          <input
                            type="text"
                            id="bccEmails"
                            name="bcc_emails"
                            class="form-control"
                            placeholder="comma-separated emails (max 10)"
                            value="<?= htmlspecialchars($template['bcc_emails'] ?? '') ?>"
                          >
                          <small style="display:block;margin-top:6px;color:#888;">
                            Example: audit@company.com, ceo@company.com
                          </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Design Email</label>
                            <div id="emailEditor" style="height:400px;border:1px solid var(--border);"></div>
                            <input type="hidden" name="html_content" id="emailHtmlContent" value="<?= htmlspecialchars($template['html_content'] ?? $template['template_html'] ?? '') ?>">
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
                      <button type="submit" class="btn btn-primary">
                          <i class="fas fa-save"></i> <?= $edit_id ? 'Update' : 'Save' ?> Template
                        </button>
                      </form>
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
        let editorReady = false;
        let EMPTY_DESIGN = null;
        let pendingDesign = null;
        
        function safeLoadDesign(designObj) {
          if (!designObj) return;
          if (!editorReady) { pendingDesign = designObj; return; }
          try { emailEditor.loadDesign(designObj); } catch (e) { console.error('loadDesign failed', e); }
        }
        
        emailEditor.addEventListener('editor:ready', function() {
          editorReady = true;
        
          // Capture a valid "blank" design once so we can reset editor cleanly
          emailEditor.exportHtml(function(data) {
            if (!EMPTY_DESIGN) EMPTY_DESIGN = data.design;
        
            if (pendingDesign) {
              safeLoadDesign(pendingDesign);
              pendingDesign = null;
              return;
            }
        
            const raw = document.getElementById('designJson').value;
            if (raw) {
              try {
                safeLoadDesign(JSON.parse(raw));
              } catch (e) {
                console.error('Invalid design JSON', e);
              }
            }
          });
        });
        
        const form = document.getElementById('templateForm');
        if (form) {
          form.addEventListener('submit', function(e) {
              e.preventDefault();
            
              // ✅ enforce required fields (because form.submit() bypasses them)
              if (!form.checkValidity()) {
                form.reportValidity();
                return;
              }
            
              emailEditor.exportHtml(function(data) {
                document.getElementById('emailHtmlContent').value = data.html;
                document.getElementById('designJson').value = JSON.stringify(data.design);
                form.submit();
              });
            });
        }
        
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
setTimeout(() => {
  const success = document.getElementById('successAlert');
  const error = document.getElementById('errorAlert');
  if (success) success.style.display = 'none';
  if (error) error.style.display = 'none';
}, 5000);

const CAN_EDIT = <?= json_encode($canEditTemplate) ?>;
const CAN_ADD  = <?= json_encode($canAddTemplate) ?>;

const templatePicker      = document.getElementById('templatePicker');
const newNameWrap         = document.getElementById('newTemplateNameWrap');
const newNameInput        = document.getElementById('newTemplateNameInput');
const templateNameHidden  = document.getElementById('templateNameHidden');

const designJsonInput     = document.getElementById('designJson');
const htmlContentInput    = document.getElementById('emailHtmlContent');
const ccInput             = document.getElementById('ccEmails');
const bccInput            = document.getElementById('bccEmails');
const formEl              = document.getElementById('templateForm');

/* ✅ NEW: Notification picker system (like template name picker) */
const notifPicker         = document.getElementById('notificationTypePicker');
const assignedNotifHidden = document.getElementById('assignedNotifHidden');
const newNotifWrap        = document.getElementById('newNotifWrap');
const newNotifLabel       = document.getElementById('newNotifLabel');
const newNotifSlug        = document.getElementById('newNotifSlug');

function slugifyNotif(str) {
  return (str || '')
    .toLowerCase()
    .trim()
    .replace(/&/g, 'and')
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '')
    .replace(/_+/g, '_')
    .substring(0, 80);
}

function ensureSelectHasValue(selectEl, value, labelText) {
  if (!selectEl || !value) return;
  const has = Array.from(selectEl.options).some(o => o.value === value);
  if (!has) {
    const opt = document.createElement('option');
    opt.value = value;
    opt.textContent = labelText || value;
    selectEl.appendChild(opt);
  }
  selectEl.value = value;
}

function syncAssignedNotifHidden() {
  if (!assignedNotifHidden) return;

  if (notifPicker && notifPicker.value === '__new_notif__') {
    assignedNotifHidden.value = (newNotifSlug?.value || '').trim();
  } else {
    assignedNotifHidden.value = (notifPicker?.value || '').trim();
  }
}

function onNotificationPickerChange() {
  if (!notifPicker) return;

  const isNew = (notifPicker.value === '__new_notif__');

  if (newNotifWrap) newNotifWrap.style.display = isNew ? 'block' : 'none';

  if (newNotifLabel) newNotifLabel.required = isNew;
  if (newNotifSlug)  newNotifSlug.required  = isNew;

  if (!isNew) {
    if (newNotifLabel) newNotifLabel.value = '';
    if (newNotifSlug)  newNotifSlug.value  = '';
  } else {
    if (newNotifLabel && newNotifLabel.value && newNotifSlug && !newNotifSlug.value) {
      newNotifSlug.value = slugifyNotif(newNotifLabel.value);
    }
  }

  syncAssignedNotifHidden();
}

if (newNotifLabel) {
  newNotifLabel.addEventListener('input', function() {
    if (newNotifSlug) newNotifSlug.value = slugifyNotif(newNotifLabel.value);
    syncAssignedNotifHidden();
  });
}

if (newNotifSlug) {
  newNotifSlug.addEventListener('input', function() {
    syncAssignedNotifHidden();
  });
}

if (notifPicker) {
  notifPicker.addEventListener('change', onNotificationPickerChange);
}

async function loadTemplateById(id) {
  const res = await fetch('ajax_get_email_template.php?id=' + encodeURIComponent(id), {
    credentials: 'same-origin'
  });
  const json = await res.json();
  if (!json.ok) throw new Error(json.error || 'Failed to load template');
  return json.template;
}

async function onTemplatePickerChange() {
  const val = templatePicker.value;

  // -----------------------
  // CREATE NEW TEMPLATE MODE
  // -----------------------
  if (val === '__new__') {
    if (!CAN_ADD) {
      alert("🚫 You don't have permission to create new templates.");
      return;
    }

    formEl.action = 'save_email_template.php';

    newNameWrap.style.display = 'block';
    newNameInput.required = true;
    templateNameHidden.value = newNameInput.value.trim();

    // Reset editor to blank
    if (typeof EMPTY_DESIGN !== 'undefined' && EMPTY_DESIGN) safeLoadDesign(EMPTY_DESIGN);
    designJsonInput.value = '';
    htmlContentInput.value = '';
    if (ccInput) ccInput.value = '';
    if (bccInput) bccInput.value = '';

    // keep notification selection as-is (so ?type= prefill stays)
    onNotificationPickerChange();
    return;
  }

  // -----------------------
  // EDIT EXISTING TEMPLATE MODE
  // -----------------------
  const templateId = parseInt(val, 10);
  if (!templateId) return;

  if (!CAN_EDIT) {
    alert("🚫 You don't have permission to edit templates.");
    return;
  }

  formEl.action = 'update_email_template.php?id=' + templateId;

  newNameWrap.style.display = 'none';
  newNameInput.required = false;
  newNameInput.value = '';

  try {
    const t = await loadTemplateById(templateId);

    templateNameHidden.value = t.template_name || '';

    // ✅ Notification type sync (including missing types)
    ensureSelectHasValue(
      notifPicker,
      t.assigned_notification_type || '',
      t.assigned_notification_type ? ('⚠️ Missing: ' + t.assigned_notification_type) : ''
    );
    onNotificationPickerChange();

    if (ccInput) ccInput.value = t.cc_emails || '';
    if (bccInput) bccInput.value = t.bcc_emails || '';

    designJsonInput.value = t.design_json || '';
    htmlContentInput.value = t.html_content || t.template_html || '';

    if (t.design_json) {
      try {
        safeLoadDesign(JSON.parse(t.design_json));
      } catch (e) {
        console.error('Invalid design_json from DB', e);
        if (typeof EMPTY_DESIGN !== 'undefined' && EMPTY_DESIGN) safeLoadDesign(EMPTY_DESIGN);
      }
    } else {
      if (typeof EMPTY_DESIGN !== 'undefined' && EMPTY_DESIGN) safeLoadDesign(EMPTY_DESIGN);
    }

  } catch (err) {
    console.error(err);
    alert("⚠️ Could not load the selected template. Check console + PHP error log.");
  }
}

if (newNameInput) {
  newNameInput.addEventListener('input', function() {
    templateNameHidden.value = newNameInput.value;
  });
}

if (templatePicker) {
  templatePicker.addEventListener('change', onTemplatePickerChange);
}

document.addEventListener('DOMContentLoaded', function() {
  if (templatePicker) onTemplatePickerChange();
  onNotificationPickerChange();
});
</script>

<?php require 'scripts.php'; ?>
</body>
</html>
