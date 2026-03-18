<h2 class="modal-title">Edit User</h2>
<form method="POST" action="{{ route('users.update', $user) }}" id="editUserForm" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <input type="hidden" name="user_id" value="{{ $user->id }}">

  <div class="form-group">
    <label for="full_name">Full Name</label>
    <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $user->full_name) }}" required>
  </div>

  <div class="form-group">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required>
    <small id="username-help" style="font-size: 0.9em;"></small>
  </div>

  <div class="form-group">
    <label for="email">Email Address</label>
    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
    <small id="email-help" style="font-size: 0.9em;"></small>
  </div>

  <div class="form-group">
    <label for="role_id">Role</label>
    @if($canAssignRoles)
      <select name="role_id" id="role_id" required>
        @foreach($roles as $role)
          <option value="{{ $role->id }}" {{ $role->id == $user->role_id ? 'selected' : '' }}>
            {{ ucwords(str_replace('_', ' ', $role->name)) }}
          </option>
        @endforeach
      </select>
    @else
      <input type="text" value="{{ $user->role ? ucwords(str_replace('_', ' ', $user->role->name)) : 'Unassigned' }}" disabled>
      <input type="hidden" name="role_id" value="{{ $user->role_id }}">
    @endif
  </div>

  <div class="form-group">
    <label for="password">New Password (optional)</label>
    <input type="password" name="password" id="password" placeholder="Leave blank to keep unchanged" minlength="8">
  </div>

  <div class="form-group">
    <label for="avatar">Avatar (Optional)</label>
    <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
    @if($user->avatar)
      <small>Current: <img src="{{ $user->avatar }}" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%;"></small>
    @endif
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
  #editUserForm input.is-valid,
  #editUserForm input.is-valid:focus {
    border: 2px solid #4cc9f0 !important;
    outline: none !important;
    box-shadow: none !important;
  }

  #editUserForm input.is-invalid,
  #editUserForm input.is-invalid:focus {
    border: 2px solid #f72585 !important;
    outline: none !important;
    box-shadow: none !important;
  }

  #editUserForm #username-help,
  #editUserForm #email-help {
    display: block;
    margin-top: 4px;
    font-weight: 500;
    font-size: 0.9em;
  }
</style>
