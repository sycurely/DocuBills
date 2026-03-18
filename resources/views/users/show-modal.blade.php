<h2 class="modal-title">User Details</h2>
<p><strong>Full Name:</strong> {{ $user->full_name ?: '-' }}</p>
<p><strong>Username:</strong> {{ $user->username }}</p>
<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>Role:</strong> {{ $user->role ? ucwords(str_replace('_', ' ', $user->role->name)) : 'Unassigned' }}</p>
<p><strong>Status:</strong> {{ $user->is_suspended ? 'Suspended' : 'Active' }}</p>
<p><strong>Created At:</strong> {{ $user->created_at ? $user->created_at->format('Y-m-d h:i A') : '-' }}</p>

<div class="form-actions" style="text-align: right;">
  <button class="btn btn-secondary" onclick="closeModal('viewUserModal')">
    <i class="fas fa-times"></i> Close
  </button>
</div>
