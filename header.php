<?php 
require_once 'config.php';
error_log('👤 SESSION in header: ' . print_r($_SESSION, true));

// Add this at the top of header.php
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET last_activity = NOW() 
        WHERE session_id = ?
    ");
    $stmt->execute([session_id()]);
}
?>
<div class="header">
    <div class="logo">
    <?php
      // ✅ Application logo from settings-basic.php (stored in settings.key_name = 'app_logo_url')
      $appLogo = trim(get_setting('app_logo_url', ''));
      $appLogoSrc = '';

      if ($appLogo !== '') {
          // Absolute URL already?
          if (preg_match('#^https?://#i', $appLogo)) {
              $appLogoSrc = $appLogo;
          } elseif (defined('BASE_URL')) {
              // Your app usually defines BASE_URL in config.php (recommended)
              $appLogoSrc = rtrim(BASE_URL, '/') . '/' . ltrim($appLogo, '/');
          } else {
              // Fallback: relative path
              $appLogoSrc = ltrim($appLogo, '/');
          }
      }
    ?>

    <?php if (!empty($appLogoSrc)): ?>
      <img
          src="<?= htmlspecialchars($appLogoSrc) ?>"
          alt="<?= htmlspecialchars(APP_NAME) ?>"
          width="208"
          height="56"
          style="width:208px;height:56px;object-fit:contain;background:transparent;padding:0;border:0;border-radius:0;display:block;"
        >
    <?php else: ?>
      <i class="fas fa-file-invoice"></i>
      <span><?= APP_NAME ?></span>
    <?php endif; ?>
  </div>
  
  <div class="header-actions">
    <button class="theme-toggle" id="themeToggle">
      <i class="fas fa-moon"></i>
    </button>
    <?php
      $userId     = $_SESSION['user_id'] ?? null;
      $userName   = '';
      $avatarPath = 'uploads/avatars/default.png';
      $initials   = '';

      if ($userId) {
          // 1️⃣ First preference: session avatar set by profile.php upload
          if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])) {
              $avatarPath = $_SESSION['avatar'];
          }

          // 2️⃣ Always fetch fresh user data from DB
          $stmt = $pdo->prepare("SELECT full_name, avatar FROM users WHERE id = ?");
          $stmt->execute([$userId]);
          $userHeader = $stmt->fetch(PDO::FETCH_ASSOC);

          $userName = $userHeader['full_name'] ?? 'Unknown User';

          foreach (explode(' ', $userName) as $word) {
              $word = trim($word);
              if ($word !== '') {
                  $initials .= strtoupper(substr($word, 0, 1));
              }
          }

          // 3️⃣ If session avatar was not set, but DB avatar exists on disk,
          //    use it and also update the session, so subsequent pages are faster.
          if (empty($_SESSION['avatar']) && !empty($userHeader['avatar']) && file_exists($userHeader['avatar'])) {
              $avatarPath         = $userHeader['avatar'];
              $_SESSION['avatar'] = $avatarPath;
          }
      }
    ?>
    <div class="user-profile" id="userProfileTrigger">
      <div class="user-avatar">
          <img id="headerAvatar" src="<?= htmlspecialchars($avatarPath) ?>?v=<?= time() ?>" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
      </div>
      <span class="user-name"><?= htmlspecialchars($userName) ?></span>
      <div class="profile-menu" id="profileMenu">
        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
        <a href="#" onclick="openHeaderPasswordModal(); return false;"><i class="fas fa-key"></i> Change Password</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>
  
<!-- 🔐 Change Password Modal -->
<div id="headerPasswordModal" class="modal-overlay" style="display:none;">
  <div class="modal-card">
    <h2 class="modal-title">Change Password</h2>
    <form id="headerPasswordForm">
      <div class="form-group">
        <label for="header_current_password">Current Password</label>
        <div class="input-wrapper">
          <input type="password" name="current_password" id="header_current_password" class="form-control" required>
        </div>
        <div class="error-msg" id="header_current_password_error"></div>
      </div>

      <div class="form-group">
        <label for="header_new_password">New Password</label>
        <div class="input-wrapper">
          <input type="password" name="new_password" id="header_new_password" class="form-control" required>
        </div>
        <div class="error-msg" id="header_new_password_error"></div>
      </div>

      <div class="form-group">
        <label for="header_confirm_password">Confirm New Password</label>
        <div class="input-wrapper">
          <input type="password" name="confirm_password" id="header_confirm_password" class="form-control" required>
        </div>
        <div class="error-msg" id="header_confirm_password_error"></div>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn-primary" id="headerPasswordSubmitBtn">Save</button>
        <button type="button" class="btn btn-outline" onclick="closeHeaderPasswordModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ Password Changed Success Modal -->
<div id="passwordSuccessModal" class="modal-overlay" style="display:none;">
  <div class="modal-card" style="text-align: center;">
    <h2 class="modal-title">Password Updated</h2>
    <p>Your password has been successfully changed.</p>
    <div class="modal-actions" style="justify-content: center; margin-top: 1.5rem;">
      <button class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
    </div>
  </div>
</div>

</div>

<script>
document.getElementById('headerPasswordForm')?.addEventListener('submit', function(e) {
  e.preventDefault();

  const current = document.getElementById('header_current_password');
  const newPass = document.getElementById('header_new_password');
  const confirm = document.getElementById('header_confirm_password');
  const submitBtn = document.getElementById('headerPasswordSubmitBtn');

  if (newPass.value !== confirm.value) {
    document.getElementById('header_new_password_error').textContent     = 'Passwords do not match.';
    document.getElementById('header_confirm_password_error').textContent = 'Passwords do not match.';
    return;
  }

  submitBtn.disabled = true;
  submitBtn.textContent = 'Saving...';

  fetch('ajax-update-password.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      current_password: current.value,
      new_password:     newPass.value,
      confirm_password: confirm.value
    })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) {
      document.getElementById('header_current_password_error').textContent = data.message || 'Current password is incorrect.';
      return;
    }
    closeHeaderPasswordModal();
    showSuccessModal();
    // Optionally show a success toast or modal
  })
  .finally(() => {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save';
  });
});
</script>