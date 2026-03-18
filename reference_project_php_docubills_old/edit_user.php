<?php
session_start(); // ✅ Required
require_once 'config.php';
require_once 'middleware.php'; // ✅ Moved here immediately after config
if (!has_permission('edit_user')) {
  $_SESSION['access_denied'] = true;
  header("Location: access-denied.php");
  exit;
}

// Get user ID
if (!isset($_GET['id'])) {
  echo "<p>User ID is missing.</p>";
  exit;
}

$userId = (int) $_GET['id'];

// Fetch user
$stmt = $pdo->prepare("
  SELECT users.id, users.username, users.email, users.full_name, users.role_id, roles.name AS role_name
  FROM users
  LEFT JOIN roles ON users.role_id = roles.id
  WHERE users.id = ? AND users.deleted_at IS NULL
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo "<p>User not found.</p>";
  exit;
}

$isSuperAdmin = ($_SESSION['user_role'] ?? '') === 'super_admin';
$canAssignRoles = $isSuperAdmin || has_permission('assign_roles');

// Fetch all roles for dropdown
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ✅ Edit User Modal Content -->
<h2 class="modal-title">Edit User</h2>
<form method="POST" action="update_user.php" id="editUserForm">
  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

  <div class="form-group">
    <label for="full_name">Full Name</label>
    <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
  </div>

  <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
      <small id="username-help" style="font-size: 0.9em;"></small>
    </div>
    
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      <small id="email-help" style="font-size: 0.9em;"></small>
    </div>

  <div class="form-group">
      <label for="role_id">Role</label>
    
      <?php if ($canAssignRoles): ?>
        <select name="role_id" id="role_id" required>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
              <?= ucwords(str_replace('_', ' ', $role['name'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <input type="text" class="form-control" value="<?= ucwords(str_replace('_', ' ', $user['role_name'])) ?>" disabled>
        <input type="hidden" name="role_id" value="<?= $user['role_id'] ?>">
      <?php endif; ?>
    </div>


  <div class="form-group">
    <label for="password">New Password (optional)</label>
    <input type="password" name="password" id="password" placeholder="Leave blank to keep unchanged">
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save"></i> Save Changes
    </button>
    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">
      <i class="fas fa-times"></i> Cancel
    </button>
  </div>
</form>

<style>
  /* green border for available */
  #editUserForm input.is-valid,
  #editUserForm input.is-valid:focus {
    border: 2px solid #4cc9f0 !important;   /* theme success */
    outline: none !important;
    box-shadow: none !important;
  }

  /* red border for taken */
  #editUserForm input.is-invalid,
  #editUserForm input.is-invalid:focus {
    border: 2px solid #f72585 !important;   /* theme danger */
    outline: none !important;
    box-shadow: none !important;
  }

  /* helper text */
  #editUserForm #username-help,
  #editUserForm #email-help {
    display: block;
    margin-top: 4px;
    font-weight: 500;
    font-size: 0.9em;
  }
</style>

<script>
let usernameValid = true;
let emailValid = true;

function setAvailabilityStatus(field, isAvailable) {
  const helpText = document.getElementById(`${field}-help`);
  const input = document.getElementById(field);
  if (!helpText || !input) return;

  input.classList.remove('is-valid', 'is-invalid');

  if (isAvailable) {
    helpText.textContent = `${field === 'email' ? 'Email address' : 'Username'} is available`;
    helpText.style.color = '#4cc9f0';
    input.classList.add('is-valid');

    if (field === 'username') usernameValid = true;
    if (field === 'email') emailValid = true;
  } else {
    helpText.textContent = `${field === 'email' ? 'Email address' : 'Username'} is already taken`;
    helpText.style.color = '#f72585';
    input.classList.add('is-invalid');

    if (field === 'username') usernameValid = false;
    if (field === 'email') emailValid = false;
  }
}

function checkAvailability(field, value, userId = 0) {
  const help  = document.getElementById(`${field}-help`);
  const input = document.getElementById(field);
  if (!help || !input) return;

  /* reset previous visual state */
  help.classList.remove('text-success', 'text-danger');
  input.classList.remove('is-valid', 'is-invalid');

  if (!value) { help.textContent = ''; return; }

  fetch(`check_availability.php?field=${field}&value=${encodeURIComponent(value)}&user_id=${userId}`)
    .then(r => r.json())
    .then(j => {
      if (j.status === 'taken') {
        help.textContent = field === 'email'
          ? 'Email address is already taken'
          : 'Username is already taken';
        help.classList.add('text-danger');   // red text
        input.classList.add('is-invalid');   // red border
      } else if (j.status === 'available') {
        help.textContent = field === 'email'
          ? 'Email address is available'
          : 'Username is available';
        help.classList.add('text-success');  // green text
        input.classList.add('is-valid');     // green border
      } else {
        help.textContent = '';
      }
    });
}

document.addEventListener("DOMContentLoaded", () => {
  const userId = <?= (int)$user['id'] ?>;
  const form = document.getElementById('editUserForm');
  const usernameField = document.getElementById('username');
  const emailField = document.getElementById('email');

  // Run initial checks on load
  if (usernameField && usernameField.value) {
    checkAvailability('username', usernameField.value, userId);
  }
  if (emailField && emailField.value) {
    checkAvailability('email', emailField.value, userId);
  }

  // Username field events
  if (usernameField) {
    usernameField.addEventListener('input', () => {
      checkAvailability('username', usernameField.value, userId);
    });
    usernameField.addEventListener('blur', () => {
      checkAvailability('username', usernameField.value, userId);
    });
  }

  // Email field events
  if (emailField) {
    emailField.addEventListener('input', () => {
      checkAvailability('email', emailField.value, userId);
    });
    emailField.addEventListener('blur', () => {
      checkAvailability('email', emailField.value, userId);
    });
  }

  // Prevent form submission if validation fails
  if (form) {
    form.addEventListener('submit', function (e) {
      if (!usernameValid || !emailValid) {
        e.preventDefault();
        alert("❌ Please fix the username or email errors before saving.");
      }
    });
  }
});
</script>
