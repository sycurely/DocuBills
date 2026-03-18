<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$activeMenu = 'profile';
require_once 'config.php';
require_once 'middleware.php';

// 🖼 Handle Avatar Upload via fetch() request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    $userId = $_SESSION['user_id'];

    // Always respond as JSON for this branch
    header('Content-Type: application/json');

    if ($avatar['error'] === UPLOAD_ERR_OK && $avatar['size'] < 2 * 1024 * 1024) {
        $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext ?: 'png'); // small safety default

        // Ensure uploads/avatars exists (once you’ve created the folder on disk)
        $newName = "uploads/avatars/user_" . $userId . "." . $ext;

        if (!is_dir('uploads/avatars')) {
            @mkdir('uploads/avatars', 0755, true);
        }

        if (move_uploaded_file($avatar['tmp_name'], $newName)) {
            // Save to DB
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$newName, $userId]);

            // ✅ Update session for live header updates
            $_SESSION['avatar'] = $newName;

            echo json_encode(['success' => true, 'src' => $newName]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not move uploaded file.']);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid image or file too large (max 2MB).'
        ]);
        exit;
    }
}

$userId = $_SESSION['user_id'];
$stmt   = $pdo->prepare("SELECT username, full_name, email, avatar FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user   = $stmt->fetch(PDO::FETCH_ASSOC);
$avatarPath = (!empty($user['avatar']) && file_exists($user['avatar']))
    ? $user['avatar']
    : 'uploads/avatars/default.png';

$errName = $errEmail = $errUsername = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['full_name']);
    $mail = trim($_POST['email']);

    $username = trim($_POST['username']);

    // ── Username validation ───────────────────────────────
    if ($username === '') {
        $errUsername = 'Username cannot be empty.';
    } else {
        // Check uniqueness, case-insensitive
        $uq = $pdo->prepare("
            SELECT COUNT(*) 
            FROM users 
            WHERE LOWER(username) = LOWER(?) AND id <> ?
        ");
        $uq->execute([$username, $userId]);
        if ($uq->fetchColumn() > 0) {
            $errUsername = 'That username is already taken.';
        }
    }

    if (!$name) $errName = 'Full name cannot be empty.';
    if (!$mail) $errEmail = 'Email cannot be empty.';
    elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL))
           $errEmail = 'Please enter a valid email address.';

    if (!$errName && !$errEmail && !$errUsername) {
        $pdo->prepare("
            UPDATE users
            SET username = ?, full_name = ?, email = ?
            WHERE id = ?
        ")->execute([$username, $name, $mail, $userId]);
        $user['username'] = $username;   // keep array in sync
        $_SESSION['profile_success'] = 'Profile updated successfully!';
        $user['full_name']=$name; $user['email']=$mail;
        header("Location: profile.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Profile</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
  --body-bg: #f5f7fb;
  --card-bg: #fff;
  --radius: 10px;
  --header-height: 70px;
  --sidebar-width: 250px;
  --transition: all 0.3s ease;
}
body {
  background-color: var(--body-bg);
  color: var(--dark);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 1rem;
  min-height: 100vh;
}
.app { display: flex; min-height: 100vh; }
.main {
  flex: 1;
  padding: calc(var(--header-height) + 1.5rem) 2.5rem 1.5rem calc(var(--sidebar-width) + 28px); /* <-- added gap */
  width: 100%;
  max-width: none;
  margin: 0;
  box-sizing: border-box;
  transition: padding-left .2s;
}
/* FULL WIDTH FIELDS */
input.form-control,
textarea.form-control {
  width: 100%;
  max-width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--card-bg);
  color: var(--dark);
  font-size: 1rem;
  font-family: inherit;
  transition: var(--transition);
  box-sizing: border-box;
  display: block;
  margin: 0;
}
.page-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 2rem;
  font-family: inherit;
}
.card-title {
  font-size: 1.12rem;
  font-weight: 600;
  margin: 2rem 0 1rem;
  color: var(--primary);
  font-family: inherit;
}
.form-group { margin-bottom: 1.3rem; }
label {
  font-weight: 500;
  font-size: 1rem;
  margin-bottom: 0.4rem;
  color: var(--dark);
  display: block;
  font-family: inherit;
}
input.form-control:focus, textarea.form-control:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.13);
}
/* Validation colors */
.error  > .form-control,.form-control.error {
  border-color: var(--danger)!important;
  box-shadow: 0 0 0 .2rem rgba(247,37,133,.17)!important;
}
.valid  > .form-control,.form-control.valid {
  border-color: var(--success)!important;
  box-shadow: 0 0 0 .2rem rgba(76,201,240,.15)!important;
}
.invalid-feedback {
  font-size: .97rem;
  color: var(--danger);
  margin-top: .23rem;
}
.btn {
  padding: 0.58rem 1.2rem;
  border-radius: var(--radius);
  border: none;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.96rem;
  font-family: inherit;
}
.btn-primary {
  background: var(--primary);
  color: white;
}
.btn-primary:hover {
  background: var(--secondary);
}

.btn[disabled] {
  opacity: 0.65;
  cursor: not-allowed;
  pointer-events: none;
}

.toggle-eye {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  cursor: pointer;
  color: var(--gray);
  transition: color 0.2s ease;
}
.toggle-eye:hover {
  color: var(--primary);
}

.password-wrapper {
  position: relative;
}

.password-wrapper .toggle-eye {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  cursor: pointer;
  color: var(--gray);
  transition: color 0.2s ease;
  z-index: 2;
}

.password-wrapper .toggle-eye:hover {
  color: var(--primary);
}

/* Modal Popup */
.modal {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.29);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: opacity .23s;
  z-index: 9999;
}
.modal.show {
  opacity: 1;
  visibility: visible;
}
.modal .modal-box {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: 0 6px 40px 0 rgba(67,97,238,0.17);
  border: 1.2px solid var(--success);
  min-width: 310px;
  padding: 2.1rem 2rem 1.5rem 2rem;
  text-align: center;
  position: relative;
}
.modal .modal-box .modal-ok {
  margin-top: 1.4rem;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  padding: 0.55rem 1.3rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background .18s;
}
.modal .modal-box .modal-ok:hover {
  background: var(--secondary);
}
.modal .modal-box .modal-close {
  position: absolute;
  top: 11px;
  right: 13px;
  font-size: 1.15rem;
  color: var(--gray);
  background: none;
  border: none;
  cursor: pointer;
}
.modal .modal-box .modal-close:hover {
  color: var(--danger);
}
.modal .success-msg {
  color: #16b072;
  font-weight: 500;
  font-size: 1.04rem;
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}
hr {
  border: none;
  border-top: 1.1px solid #d3d8ea;
  margin: 2rem 0;
}
@media (max-width: 1200px) {
  .main { padding-left: calc(var(--sidebar-width) + 12px); }
}
@media (max-width: 900px) {
  .main { padding-left: calc(var(--sidebar-width) + 4px); }
}
@media (max-width: 768px) {
  .main { padding-left: 1rem; padding-right: 1rem; }
}
</style>
</head>
<body>

<?php require 'header.php'; ?>
<div class="app">
<?php require 'sidebar.php'; ?>

<div class="main">
    <?php
    // Flash message logic (display as modal)
    $showProfileSuccess = false;
    if (!empty($_SESSION['profile_success'])) {
        $profileSuccessMsg = $_SESSION['profile_success'];
        unset($_SESSION['profile_success']); // Clear after showing
        $showProfileSuccess = true;
    }
    ?>

  <h1 class="page-title">My Profile</h1>

    <!-- ============  ACCOUNT DETAILS  ============ -->
    <h2 class="card-title">Account Details</h2>
    <form id="profileForm" method="POST" autocomplete="off">
      <input type="hidden" name="update_profile" value="1">
      
      <!-- Avatar -->
      <div class="form-group">
          <label for="avatar">Avatar</label>
          <div style="display:flex;align-items:center;gap:12px;">
            <img id="avatarPreview" src="<?= htmlspecialchars($avatarPath) ?>?v=<?= time() ?>" alt="Avatar" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
            <input type="file" id="avatarInput" accept="image/*">
          </div>
        </div>
        
      <!-- Full Name -->      
      <div class="form-group <?= $errName?'error':'' ?>">
        <label for="full_name">Full Name</label>
        <input id="full_name" name="full_name" class="form-control"
               value="<?= htmlspecialchars($user['full_name']) ?>">
        <?php if($errName):?><div class="invalid-feedback"><?= $errName ?></div><?php endif;?>
      </div>
      
      <!-- Username -->
        <div class="form-group <?= $errUsername ? 'error' : '' ?>">
          <label for="username">Username</label>
          <input
            id="username"
            name="username"
            class="form-control"
            value="<?= htmlspecialchars(isset($_POST['username']) ? $_POST['username'] : $user['username']) ?>"
          >
          <div class="invalid-feedback"><?= $errUsername ?></div>
        </div>

      <div class="form-group <?= $errEmail?'error':'' ?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="form-control"
               value="<?= htmlspecialchars($user['email']) ?>">
        <?php if($errEmail):?><div class="invalid-feedback"><?= $errEmail ?></div><?php endif;?>
      </div>
      <button id="saveProfileBtn" class="btn btn-primary"><i class="fas fa-save"></i> Save Profile</button>
    </form>

    <hr>

    <!-- ============  CHANGE PASSWORD  ============ -->
    <h2 class="card-title">Change Password</h2>
    <form id="pw_passwordForm" autocomplete="off">
      <div class="form-group input-wrapper">
          <div class="form-group <?= $errCur ? 'error' : '' ?>">
          <label for="pw_current_password">Current Password</label>
          <div class="input-wrapper">
            <div class="password-wrapper">
              <input id="pw_current_password" name="current_password" type="password" class="form-control" style="padding-right: 40px;">
              <i class="fa-solid fa-eye toggle-eye"
                 data-target="pw_current_password"
                 tabindex="0"
                 role="button"
                 aria-label="Toggle Password Visibility">
              </i>
            </div>
          </div>
          <div id="pw_current_password_error" class="invalid-feedback"><?= $errCur ?? '' ?></div>
        </div>

      <div class="form-group <?= $errNew ? 'error' : '' ?>">
      <label for="pw_new_password">New Password</label>
      <div class="input-wrapper">
        <div class="password-wrapper">
          <input id="pw_new_password" name="new_password" type="password" class="form-control" style="padding-right: 40px;">
          <i class="fa-solid fa-eye toggle-eye"
             data-target="pw_new_password"
             tabindex="0"
             role="button"
             aria-label="Toggle Password Visibility">
          </i>
        </div>
      </div>
      <div id="pw_new_password_error" class="invalid-feedback"><?= $errNew ?? '' ?></div>
    </div>


    <div class="form-group <?= $errConf ? 'error' : '' ?>">
      <label for="pw_confirm_password">Confirm New Password</label>
      <div class="input-wrapper">
        <div class="password-wrapper">
          <input id="pw_confirm_password" name="confirm_password" type="password" class="form-control" style="padding-right: 40px;">
          <i class="fa-solid fa-eye toggle-eye"
             data-target="pw_confirm_password"
             tabindex="0"
             role="button"
             aria-label="Toggle Password Visibility">
          </i>
        </div>
      </div>
      <div id="pw_confirm_password_error" class="invalid-feedback"><?= $errConf ?? '' ?></div>
    </div>

      <button id="pw_passwordSubmitBtn" class="btn btn-primary">
        <i class="fas fa-key"></i> Change Password
      </button>
    </form>

    <!-- Profile update success modal -->
    <div id="profileSuccessModal" class="modal">
      <div class="modal-box">
        <button class="modal-close" onclick="closeProfileModal()" title="Close">&times;</button>
        <div class="success-msg">
          <i class="fas fa-check-circle"></i>
          <span id="profileSuccessMsg"><?= isset($profileSuccessMsg) ? $profileSuccessMsg : '' ?></span>
        </div>
        <button class="modal-ok" onclick="closeProfileModal()">OK</button>
      </div>
    </div>
</div><!-- /main -->
</div><!-- /app -->

<!-- success modal for password change -->
<div id="pwModal" class="modal">
  <div class="modal-box" style="border-color:var(--success);">
    <div class="success-msg">
      <i class="fas fa-check-circle"></i>
      Password updated successfully!
    </div>
    <button class="modal-ok" onclick="closePasswordModal()">OK</button>
  </div>
</div>

<?php require 'scripts.php'; ?>
<script>
const form  = document.getElementById('pw_passwordForm');
const cur   = document.getElementById('pw_current_password');
const np    = document.getElementById('pw_new_password');
const cp    = document.getElementById('pw_confirm_password');
const btn   = document.getElementById('pw_passwordSubmitBtn');
const saveProfileBtn = document.getElementById('saveProfileBtn');

const errCur  = document.getElementById('pw_current_password_error');
const errNew  = document.getElementById('pw_new_password_error');
const errConf = document.getElementById('pw_confirm_password_error');

function setState(el, state){
  const wrap = el.closest('.input-wrapper');
  ['valid','error'].forEach(c=>{
     el.classList.toggle(c,state===c);
     if(wrap) wrap.classList.toggle(c,state===c);
  });
}
function checkMatch () {
  if (!np.value || !cp.value) {
    errNew.textContent = errConf.textContent = '';
    setState(np,'neutral'); setState(cp,'neutral'); return;
  }
  if (np.value === cp.value) {
    errNew.textContent = errConf.textContent = '';
    setState(np,'valid'); setState(cp,'valid');
  } else {
    errNew.textContent = errConf.textContent = 'Passwords do not match.';
    setState(np,'error'); setState(cp,'error');
  }
}
np.addEventListener('input',  checkMatch);
cp.addEventListener('input',  checkMatch);

let debounce;

cur.addEventListener('input', () => {
  clearTimeout(debounce);

  errCur.textContent = '';
  setState(cur, 'neutral');

  const value = cur.value.trim();
  if (value.length < 3) return;

  debounce = setTimeout(() => {
    fetch('ajax-check-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ current_password: value })
    })
    .then(r => r.json())
    .then(d => {
      if (d.valid) {
        setState(cur, 'valid');
        errCur.textContent = '';
      } else {
        setState(cur, 'error');
        errCur.textContent = 'Current password is incorrect.';
      }
    })
    .catch(() => {
      setState(cur, 'error');
      errCur.textContent = 'Could not verify password.';
    });
  }, 500);
});

form.addEventListener('submit', e => {
  e.preventDefault();

  const curVal = cur.value.trim();
  const npVal  = np.value.trim();
  const cpVal  = cp.value.trim();

  // Only proceed if any password field has been touched
  const anyTouched = curVal || npVal || cpVal;

  if (!anyTouched) return; // Don't submit if user didn't use Change Password

  checkMatch();
  if (cp.classList.contains('error')) return;

  btn.disabled = true;
  btn.textContent = 'Saving…';

  fetch('ajax-update-password.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      current_password: curVal,
      new_password:     npVal,
      confirm_password: cpVal
    })
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      [cur,np,cp].forEach(i => { i.value=''; setState(i,'neutral'); });
      showPasswordModal();
    } else {
      setState(cur, 'error');
      errCur.textContent = d.message || 'Current password is incorrect.';
    }
  })
  .catch(() => alert('Network error – please try again.'))
  .finally(() => {
    btn.disabled = false;
    btn.textContent = 'Change Password';
  });
});

// ── Username Availability Check ──
const usernameField = document.getElementById('username');
const usernameGroup = usernameField.closest('.form-group');
const usernameFeedback = usernameGroup.querySelector('.invalid-feedback');

let usernameDebounce;

usernameField.addEventListener('input', () => {
  clearTimeout(usernameDebounce);
  usernameGroup.classList.remove('error', 'valid');
  usernameField.classList.remove('error', 'valid');
  usernameFeedback.textContent = '';
  saveProfileBtn.disabled = false;

  const val = usernameField.value.trim();
  if (val.length < 3) return;

  usernameDebounce = setTimeout(() => {
    fetch('ajax-check-username.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ username: val })
    })
    .then(res => res.json())
    .then(data => {
      if (data.valid) {
        usernameGroup.classList.add('valid');
        usernameField.classList.add('valid');
        usernameFeedback.textContent = '';
        saveProfileBtn.disabled = false;
      } else {
        usernameGroup.classList.add('error');
        usernameField.classList.add('error');
        usernameFeedback.textContent = data.message;
        saveProfileBtn.disabled = true;
      }
    })
    .catch(() => {
      usernameGroup.classList.add('error');
      usernameField.classList.add('error');
      usernameFeedback.textContent = 'Error checking username.';
    });
  }, 400);
});

const profileForm = document.getElementById('profileForm');

profileForm.addEventListener('submit', function(e) {
  if (usernameField.classList.contains('error')) {
    e.preventDefault();
    saveProfileBtn.disabled = true;
    usernameFeedback.textContent = 'Please choose a different username before saving.';
  }
});

function showProfileModal() {
  var modal = document.getElementById('profileSuccessModal');
  if (!modal) return;
  modal.classList.add('show');
  window.profileModalTimeout = setTimeout(closeProfileModal, 5000);
}
function closeProfileModal() {
  var modal = document.getElementById('profileSuccessModal');
  if (!modal) return;
  modal.classList.remove('show');
  if (window.profileModalTimeout) clearTimeout(window.profileModalTimeout);
}
function showPasswordModal() {
  var modal = document.getElementById('pwModal');
  if (!modal) return;
  modal.classList.add('show');
  window.pwModalTimeout = setTimeout(closePasswordModal, 5000);

  // Also clear header modal fields if they exist
  ['header_current_password', 'header_new_password', 'header_confirm_password'].forEach(id => {
    const field = document.getElementById(id);
    if (field) field.value = '';
  });

  // Also reset states and helper texts (optional but recommended)
  ['header_current_password_error', 'header_new_password_error', 'header_confirm_password_error'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = '';
  });
}

function closePasswordModal() {
  var modal = document.getElementById('pwModal');
  if (!modal) return;
  modal.classList.remove('show');
  if (window.pwModalTimeout) clearTimeout(window.pwModalTimeout);
}
document.addEventListener("DOMContentLoaded", function() {
    <?php if (!empty($showProfileSuccess)): ?>
        showProfileModal();
    <?php endif; ?>
});

const avatarInput = document.getElementById('avatarInput');
const avatarPreview = document.getElementById('avatarPreview');

avatarInput.addEventListener('change', () => {
  const file = avatarInput.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = e => { avatarPreview.src = e.target.result; };
  reader.readAsDataURL(file);

  const formData = new FormData();
  formData.append('avatar', file);

  fetch('profile.php', {
  method: 'POST',
  body: formData
    })

    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Update avatar in profile form preview
        avatarPreview.src = data.src + '?v=' + Date.now();
    
        // Update avatar in top-right header (if present)
        const headerAvatarEl = document.getElementById('headerAvatar');
        if (headerAvatarEl) {
          headerAvatarEl.src = data.src + '?v=' + Date.now();
        }
      } else {
        alert("Upload failed. Use image < 2MB");
      }
    });
});

// Password toggle visibility 👁️
document.querySelectorAll('.toggle-eye').forEach(icon => {
  icon.addEventListener('click', () => {
    const fieldId = icon.dataset.target;
    const field = document.getElementById(fieldId);
    if (!field) return;

    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';

    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });
});

document.querySelectorAll('.toggle-eye').forEach(icon => {
  icon.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      icon.click();
    }
  });
});

</script>
</body>
</html>
