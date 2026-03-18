@php
  $user = Auth::user();
  $appLogo = setting('app_logo_url');
  $appName = setting('company_name', 'DocuBills');
  
  $appLogoSrc = '';
  if ($appLogo) {
    if (preg_match('#^https?://#i', $appLogo)) {
      $appLogoSrc = $appLogo;
    } else {
      $localPath = ltrim($appLogo, '/');
      if (file_exists(public_path($localPath))) {
        $appLogoSrc = asset($appLogo);
      }
    }
  }
  
  $userName = $user->full_name ?? $user->username ?? 'Unknown User';
  $defaultAvatarUrl = asset('uploads/avatars/default.png');
  $avatarPath = $defaultAvatarUrl;
  if ($user->avatar) {
    if (preg_match('#^https?://#i', $user->avatar)) {
      $avatarPath = $user->avatar;
    } else {
      $localPath = ltrim($user->avatar, '/');
      if (file_exists(public_path($localPath))) {
        $avatarPath = asset($user->avatar);
      }
    }
  }
  $initials = '';
  foreach (explode(' ', $userName) as $word) {
    $word = trim($word);
    if ($word !== '') {
      $initials .= strtoupper(substr($word, 0, 1));
    }
  }
@endphp

<div class="header">
  <a href="{{ route('dashboard') }}" class="logo">
    @if(!empty($appLogoSrc))
      <img src="{{ $appLogoSrc }}" alt="{{ $appName }}" width="208" height="56" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
      <span class="logo-text-fallback is-hidden">{{ $appName }}</span>
    @else
      <span class="material-icons-outlined">receipt_long</span>
      <span>{{ $appName }}</span>
    @endif
  </a>
  
  <div class="header-actions">
    <button class="theme-toggle" id="themeToggle">
      <span class="material-icons-outlined">dark_mode</span>
    </button>
    
    <div class="user-profile" id="userProfileTrigger">
      <div class="user-avatar">
        <img src="{{ $avatarPath }}?v={{ time() }}" alt="Avatar" onerror="this.onerror=null; this.src='{{ $defaultAvatarUrl }}';">
        <span class="is-hidden">{{ $initials }}</span>
      </div>
      <span class="user-name">{{ $userName }}</span>
      <div class="profile-menu" id="profileMenu">
        <a href="{{ route('users.show', $user) }}"><span class="material-icons-outlined">person</span> My Profile</a>
        <a href="#" onclick="openHeaderPasswordModal(); return false;"><span class="material-icons-outlined">key</span> Change Password</a>
        <form method="POST" action="{{ route('logout') }}" class="inline-form">
          @csrf
          <button type="submit" class="unstyled-button full-width text-left profile-menu-link">
            <span class="material-icons-outlined">logout</span> Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Change Password Modal -->
<div id="headerPasswordModal" class="modal-overlay">
  <div class="modal-card">
    <h2 class="modal-title">Change Password</h2>
    <form id="headerPasswordForm">
      @csrf
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
          <input type="password" name="new_password_confirmation" id="header_confirm_password" class="form-control" required>
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

<!-- Password Changed Success Modal -->
<div id="passwordSuccessModal" class="modal-overlay">
  <div class="modal-card text-center">
    <h2 class="modal-title">Password Updated</h2>
    <p>Your password has been successfully changed.</p>
    <div class="modal-actions justify-center mt-lg">
      <button class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
    </div>
  </div>
</div>

<script>
function openHeaderPasswordModal() {
  document.getElementById('headerPasswordModal').style.display = 'flex';
}

function closeHeaderPasswordModal() {
  document.getElementById('headerPasswordModal').style.display = 'none';
  document.getElementById('headerPasswordForm').reset();
  document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');
}

function closeSuccessModal() {
  document.getElementById('passwordSuccessModal').style.display = 'none';
  closeHeaderPasswordModal();
}

function showSuccessModal() {
  document.getElementById('passwordSuccessModal').style.display = 'flex';
}

document.getElementById('headerPasswordForm')?.addEventListener('submit', function(e) {
  e.preventDefault();

  const current = document.getElementById('header_current_password');
  const newPass = document.getElementById('header_new_password');
  const confirm = document.getElementById('header_confirm_password');
  const submitBtn = document.getElementById('headerPasswordSubmitBtn');

  if (newPass.value !== confirm.value) {
    document.getElementById('header_new_password_error').textContent = 'Passwords do not match.';
    document.getElementById('header_confirm_password_error').textContent = 'Passwords do not match.';
    return;
  }

  submitBtn.disabled = true;
  submitBtn.textContent = 'Saving...';

  fetch('{{ route("api.users.update-password") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
    },
    body: JSON.stringify({
      current_password: current.value,
      new_password: newPass.value,
      new_password_confirmation: confirm.value
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
  })
  .catch(err => {
    console.error('Error:', err);
    document.getElementById('header_current_password_error').textContent = 'An error occurred. Please try again.';
  })
  .finally(() => {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save';
  });
});
</script>
