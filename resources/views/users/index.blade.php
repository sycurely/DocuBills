@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
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
      --transition: all 0.3s ease;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
    }

    .btn {
      padding: 0.6rem 1.2rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary);
      color: #fff;
    }

    .btn-primary:hover {
      background: var(--secondary);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-sm {
      padding: 0.4rem 0.8rem;
      font-size: 0.9rem;
    }

    .btn-warning {
      background: var(--warning);
      color: #fff;
    }

    .btn-danger {
      background: var(--danger);
      color: #fff;
    }

    .btn-success {
      background: var(--success);
      color: #fff;
    }

    .btn-secondary {
      background: var(--light);
      color: var(--dark);
      border: 1px solid var(--border);
    }

    .btn-icon {
      width: 38px;
      height: 38px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      vertical-align: middle;
      border-radius: var(--radius);
    }

    button[disabled] {
      opacity: 0.55;
      cursor: not-allowed;
    }

    .table-container {
      overflow-x: auto;
      margin-top: 2rem;
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 1rem;
      text-align: center;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
    }

    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    .avatar-initials {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      font-weight: 600;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto;
    }

    .user-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary);
      margin: 0 auto;
    }

    .actions-cell {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    /* Modals */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      width: 100%;
      max-width: 500px;
      text-align: left;
      position: relative;
      max-height: 90vh;
      overflow-y: auto;
    }

    .close, .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--gray);
    }

    .close:hover, .close-modal:hover {
      color: var(--danger);
    }

    .modal-title {
      color: var(--primary);
      font-weight: 700;
      margin-bottom: 1rem;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: var(--dark);
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
    }

    .form-group small {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.9em;
    }

    .form-actions {
      text-align: right;
      margin-top: 1.5rem;
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
    }

    .text-success {
      color: var(--success) !important;
    }

    .text-danger {
      color: var(--danger) !important;
    }

    input.is-valid {
      border: 2px solid var(--success) !important;
    }

    input.is-invalid {
      border: 2px solid var(--danger) !important;
    }

    .confirmation-message {
      font-size: 1rem;
      margin-bottom: 1rem;
    }

    .btn-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 1.5rem;
    }

    .suspend-form {
      display: inline;
      margin-left: 4px;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">User Management</h1>
      @if(has_permission('add_user'))
        <button class="btn btn-primary" onclick="openAddUserModal()">
          <i class="fas fa-plus"></i> New User
        </button>
      @endif
    </div>

    @if(session('success'))
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true
          });
        });
      </script>
    @endif

    @if(session('error'))
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true
          });
        });
      </script>
    @endif

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Avatar</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
            @php
              $initial = strtoupper(substr($user->username, 0, 1));
              if(strpos($user->username, '@') !== false) {
                $p = explode('@', $user->username)[0];
                $parts = explode('.', $p);
                if(count($parts) > 1) {
                  $initial = strtoupper($parts[0][0] . $parts[1][0]);
                }
              }
            @endphp
            <tr>
              <td>
                @if($user->avatar)
                  <img src="{{ $user->avatar }}" class="user-avatar" alt="{{ $user->username }}">
                @else
                  <div class="avatar-initials">{{ $initial }}</div>
                @endif
              </td>
              <td>{{ $user->username }}</td>
              <td>{{ $user->email }}</td>
              <td>{{ $user->role ? ucwords(str_replace('_', ' ', $user->role->name)) : 'Unassigned' }}</td>
              <td>{{ $user->created_at->format('Y-m-d') }}</td>
              <td class="actions-cell">
                <button class="btn btn-sm btn-primary" onclick="openViewModal({{ $user->id }})">
                  <i class="fas fa-eye"></i>
                </button>

                @if(has_permission('edit_user'))
                  <button class="btn btn-sm btn-warning" onclick="openEditModal({{ $user->id }})">
                    <i class="fas fa-edit"></i>
                  </button>
                @endif

                @if(has_permission('delete_user'))
                  <button class="btn btn-sm btn-danger delete-user-btn"
                          data-id="{{ $user->id }}"
                          data-username="{{ $user->username }}">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                @endif

                @if(has_permission('suspend_users'))
                  <form method="POST" action="{{ route('users.toggle-suspend', $user) }}" class="suspend-form">
                    @csrf
                    @if($user->is_suspended)
                      <button type="button" class="btn btn-sm btn-danger btn-icon suspend-toggle" title="Unsuspend user">
                        <span style="color:#f8961e;font-size:1.2em;line-height:1;">ðŸ”’</span>
                      </button>
                    @else
                      <button type="button" class="btn btn-sm btn-success btn-icon suspend-toggle" title="Suspend user">
                        <span style="color:#fff;font-size:1.2em;line-height:1;">ðŸ”“</span>
                      </button>
                    @endif
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add User Modal -->
  <div id="addUserModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeAddUserModal()">&times;</span>
      <h2 class="modal-title">Add New User</h2>

      <form id="addUserForm" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" id="add-username" required>
          <small id="add-username-help" style="font-size:.9em"></small>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" id="add-email" required>
          <small id="add-email-help" style="font-size:.9em"></small>
        </div>

        <div class="form-group">
          <label>Temporary Password</label>
          <input type="password" name="password" required minlength="8">
        </div>

        <div class="form-group">
          <label>Assign Role</label>
          <select name="role_id" required>
            <option value="">Select Role</option>
            @foreach($roles as $role)
              <option value="{{ $role->id }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Avatar (Optional)</label>
          <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" disabled>Create User</button>
          <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- View/Edit Modals (content filled via AJAX) -->
  <div id="viewUserModal" class="modal">
    <div class="modal-content" id="viewUserContent"></div>
  </div>

  <div id="editUserModal" class="modal">
    <div class="modal-content" id="editUserContent"></div>
  </div>

  <!-- Delete User Modal -->
  <div class="modal" id="deleteUserModal">
    <div class="modal-content">
      <span class="close-modal" id="closeDeleteUserModal">&times;</span>
      <h2 class="modal-title">Confirm Deletion</h2>
      <div class="confirmation-message">
        Are you sure you want to delete user <strong id="deleteUsername"></strong>?
      </div>
      <p>This action will move the user to Trash Bin and can be restored.</p>
      <form method="POST" id="deleteUserForm">
        @csrf
        @method('DELETE')
        <input type="hidden" name="user_id" id="delete_user_id" value="">
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" id="cancelDeleteUser">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete User
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const checkUsernameURL = '{{ route('api.users.check-username') }}';
    const checkEmailURL = '{{ route('api.users.check-email') }}';

    // Reusable fetch helper
    function apiCheck(field, val, userId = 0) {
      const url = field === 'username' 
        ? `${checkUsernameURL}?username=${encodeURIComponent(val)}&user_id=${userId}`
        : `${checkEmailURL}?email=${encodeURIComponent(val)}&user_id=${userId}`;
      return fetch(url).then(r => r.json()).catch(() => ({status: 'error'}));
    }

    // Add User Validation
    function attachAddUserValidation() {
      const form = document.getElementById('addUserForm');
      const uField = document.getElementById('add-username');
      const eField = document.getElementById('add-email');
      const uHelp = document.getElementById('add-username-help');
      const eHelp = document.getElementById('add-email-help');
      const submit = form.querySelector('button[type="submit"]');
      let uOK = false, eOK = false;

      const mark = (help, input, ok, msgOK, msgBad) => {
        help.classList.remove('text-success', 'text-danger');
        input.classList.remove('is-valid', 'is-invalid');
        if (ok === null) {
          help.textContent = '';
          update();
          return;
        }
        help.textContent = ok ? msgOK : msgBad;
        help.classList.add(ok ? 'text-success' : 'text-danger');
        input.classList.add(ok ? 'is-valid' : 'is-invalid');
        if (input === uField) uOK = ok;
        if (input === eField) eOK = ok;
        update();
      };

      const update = () => {
        submit.disabled = !(uOK && eOK);
      };

      const check = (fld, val) => {
        const help = fld === 'username' ? uHelp : eHelp;
        const inp = fld === 'username' ? uField : eField;
        if (!val) {
          mark(help, inp, null);
          return;
        }
        apiCheck(fld, val).then(j => mark(
          help, inp,
          j.status === 'available',
          fld === 'email' ? 'Email address is available' : 'Username is available',
          fld === 'email' ? 'Email address is already taken' : 'Username is already taken'
        ));
      };

      uField.addEventListener('input', () => check('username', uField.value));
      eField.addEventListener('input', () => check('email', eField.value));
      update();
    }

    // Modal helpers
    function openAddUserModal() {
      document.getElementById('addUserModal').style.display = 'flex';
      attachAddUserValidation();
    }

    function closeAddUserModal() {
      document.getElementById('addUserModal').style.display = 'none';
      document.getElementById('addUserForm').reset();
    }

    function openViewModal(id) {
      fetch(`{{ url('/users') }}/${id}?modal=1`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(r => r.text())
        .then(html => {
          document.getElementById('viewUserContent').innerHTML = html;
          document.getElementById('viewUserModal').style.display = 'flex';
        });
    }

    function openEditModal(id) {
      fetch(`{{ url('/users') }}/${id}/edit`)
        .then(r => r.text())
        .then(html => {
          const wrap = document.getElementById('editUserContent');
          wrap.innerHTML = html;
          attachEditUserValidation(wrap, id);
          document.getElementById('editUserModal').style.display = 'flex';
        });
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    // Close modal on backdrop click
    window.onclick = e => {
      ['addUserModal', 'viewUserModal', 'editUserModal', 'deleteUserModal'].forEach(id => {
        if (e.target === document.getElementById(id)) {
          closeModal(id);
        }
      });
    };

    // Delete user functionality
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const userId = btn.dataset.id;
          const username = btn.dataset.username;
          document.getElementById('delete_user_id').value = userId;
          document.getElementById('deleteUsername').textContent = username;
          document.getElementById('deleteUserForm').action = `{{ url('/users') }}/${userId}`;
          document.getElementById('deleteUserModal').style.display = 'flex';
        });
      });

      document.getElementById('cancelDeleteUser').onclick = () => {
        document.getElementById('deleteUserModal').style.display = 'none';
      };

      document.getElementById('closeDeleteUserModal').onclick = () => {
        document.getElementById('deleteUserModal').style.display = 'none';
      };
    });

    // Suspend toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.suspend-toggle').forEach(button => {
        button.addEventListener('click', async () => {
          const form = button.closest('.suspend-form');
          const isSuspended = button.title.startsWith('Unsuspend');
          const action = isSuspended ? 'Unsuspend' : 'Suspend';

          const result = await Swal.fire({
            title: `${action} this user?`,
            text: `Are you sure you want to ${action.toLowerCase()} this user?`,
            icon: 'warning',
            width: 400,
            padding: '1.5rem',
            background: '#ffffff',
            iconColor: '#f8961e',
            showCancelButton: true,
            confirmButtonText: action,
            cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {
              popup: 'swal2-custom-popup',
              title: 'swal2-custom-title',
              content: 'swal2-custom-content',
              icon: 'swal2-custom-icon',
              confirmButton: action === 'Suspend' ? 'btn btn-danger' : 'btn btn-success',
              cancelButton: 'btn btn-secondary ml-2'
            }
          });

          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });

    // Edit user validation (will be attached when edit modal loads)
    function attachEditUserValidation(wrapper, userId) {
      const form = wrapper.querySelector('#editUserForm');
      if (!form) return;

      const uField = wrapper.querySelector('#username');
      const eField = wrapper.querySelector('#email');
      const uHelp = wrapper.querySelector('#username-help');
      const eHelp = wrapper.querySelector('#email-help');
      const submit = form.querySelector('button[type="submit"]');
      let uOK = true, eOK = true;

      const mark = (field, ok) => {
        const help = field === 'username' ? uHelp : eHelp;
        const inp = field === 'username' ? uField : eField;
        if (!help || !inp) return;

        help.classList.remove('text-success', 'text-danger');
        inp.classList.remove('is-valid', 'is-invalid');
        help.textContent = ok
          ? (field === 'email' ? 'Email address is available' : 'Username is available')
          : (field === 'email' ? 'Email address is already taken' : 'Username is already taken');
        help.classList.add(ok ? 'text-success' : 'text-danger');
        inp.classList.add(ok ? 'is-valid' : 'is-invalid');
        if (field === 'username') uOK = ok;
        if (field === 'email') eOK = ok;
        if (submit) submit.disabled = !(uOK && eOK);
      };

      const check = (f, v) => {
        if (!v) {
          mark(f, true);
          return;
        }
        apiCheck(f, v, userId).then(j => mark(f, j.status === 'available'));
      };

      if (uField && eField) {
        check('username', uField.value);
        check('email', eField.value);
        if (submit) submit.disabled = !(uOK && eOK);

        uField.addEventListener('input', () => check('username', uField.value));
        eField.addEventListener('input', () => check('email', eField.value));
      }

      if (form) {
        form.addEventListener('submit', e => {
          if (!uOK || !eOK) {
            e.preventDefault();
            alert('âŒ Fix username/email first.');
          }
        });
      }
    }
  </script>

@endsection

@push('scripts')
<script>

        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true
          });
        });

        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true
          });
        });

    const checkUsernameURL = '{{ route('api.users.check-username') }}';
    const checkEmailURL = '{{ route('api.users.check-email') }}';

    // Reusable fetch helper
    function apiCheck(field, val, userId = 0) {
      const url = field === 'username' 
        ? `${checkUsernameURL}?username=${encodeURIComponent(val)}&user_id=${userId}`
        : `${checkEmailURL}?email=${encodeURIComponent(val)}&user_id=${userId}`;
      return fetch(url).then(r => r.json()).catch(() => ({status: 'error'}));
    }

    // Add User Validation
    function attachAddUserValidation() {
      const form = document.getElementById('addUserForm');
      const uField = document.getElementById('add-username');
      const eField = document.getElementById('add-email');
      const uHelp = document.getElementById('add-username-help');
      const eHelp = document.getElementById('add-email-help');
      const submit = form.querySelector('button[type="submit"]');
      let uOK = false, eOK = false;

      const mark = (help, input, ok, msgOK, msgBad) => {
        help.classList.remove('text-success', 'text-danger');
        input.classList.remove('is-valid', 'is-invalid');
        if (ok === null) {
          help.textContent = '';
          update();
          return;
        }
        help.textContent = ok ? msgOK : msgBad;
        help.classList.add(ok ? 'text-success' : 'text-danger');
        input.classList.add(ok ? 'is-valid' : 'is-invalid');
        if (input === uField) uOK = ok;
        if (input === eField) eOK = ok;
        update();
      };

      const update = () => {
        submit.disabled = !(uOK && eOK);
      };

      const check = (fld, val) => {
        const help = fld === 'username' ? uHelp : eHelp;
        const inp = fld === 'username' ? uField : eField;
        if (!val) {
          mark(help, inp, null);
          return;
        }
        apiCheck(fld, val).then(j => mark(
          help, inp,
          j.status === 'available',
          fld === 'email' ? 'Email address is available' : 'Username is available',
          fld === 'email' ? 'Email address is already taken' : 'Username is already taken'
        ));
      };

      uField.addEventListener('input', () => check('username', uField.value));
      eField.addEventListener('input', () => check('email', eField.value));
      update();
    }

    // Modal helpers
    function openAddUserModal() {
      document.getElementById('addUserModal').style.display = 'flex';
      attachAddUserValidation();
    }

    function closeAddUserModal() {
      document.getElementById('addUserModal').style.display = 'none';
      document.getElementById('addUserForm').reset();
    }

    function openViewModal(id) {
      fetch(`{{ url('/users') }}/${id}?modal=1`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(r => r.text())
        .then(html => {
          document.getElementById('viewUserContent').innerHTML = html;
          document.getElementById('viewUserModal').style.display = 'flex';
        });
    }

    function openEditModal(id) {
      fetch(`{{ url('/users') }}/${id}/edit`)
        .then(r => r.text())
        .then(html => {
          const wrap = document.getElementById('editUserContent');
          wrap.innerHTML = html;
          attachEditUserValidation(wrap, id);
          document.getElementById('editUserModal').style.display = 'flex';
        });
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    // Close modal on backdrop click
    window.onclick = e => {
      ['addUserModal', 'viewUserModal', 'editUserModal', 'deleteUserModal'].forEach(id => {
        if (e.target === document.getElementById(id)) {
          closeModal(id);
        }
      });
    };

    // Delete user functionality
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const userId = btn.dataset.id;
          const username = btn.dataset.username;
          document.getElementById('delete_user_id').value = userId;
          document.getElementById('deleteUsername').textContent = username;
          document.getElementById('deleteUserForm').action = `{{ url('/users') }}/${userId}`;
          document.getElementById('deleteUserModal').style.display = 'flex';
        });
      });

      document.getElementById('cancelDeleteUser').onclick = () => {
        document.getElementById('deleteUserModal').style.display = 'none';
      };

      document.getElementById('closeDeleteUserModal').onclick = () => {
        document.getElementById('deleteUserModal').style.display = 'none';
      };
    });

    // Suspend toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.suspend-toggle').forEach(button => {
        button.addEventListener('click', async () => {
          const form = button.closest('.suspend-form');
          const isSuspended = button.title.startsWith('Unsuspend');
          const action = isSuspended ? 'Unsuspend' : 'Suspend';

          const result = await Swal.fire({
            title: `${action} this user?`,
            text: `Are you sure you want to ${action.toLowerCase()} this user?`,
            icon: 'warning',
            width: 400,
            padding: '1.5rem',
            background: '#ffffff',
            iconColor: '#f8961e',
            showCancelButton: true,
            confirmButtonText: action,
            cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {
              popup: 'swal2-custom-popup',
              title: 'swal2-custom-title',
              content: 'swal2-custom-content',
              icon: 'swal2-custom-icon',
              confirmButton: action === 'Suspend' ? 'btn btn-danger' : 'btn btn-success',
              cancelButton: 'btn btn-secondary ml-2'
            }
          });

          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });

    // Edit user validation (will be attached when edit modal loads)
    function attachEditUserValidation(wrapper, userId) {
      const form = wrapper.querySelector('#editUserForm');
      if (!form) return;

      const uField = wrapper.querySelector('#username');
      const eField = wrapper.querySelector('#email');
      const uHelp = wrapper.querySelector('#username-help');
      const eHelp = wrapper.querySelector('#email-help');
      const submit = form.querySelector('button[type="submit"]');
      let uOK = true, eOK = true;

      const mark = (field, ok) => {
        const help = field === 'username' ? uHelp : eHelp;
        const inp = field === 'username' ? uField : eField;
        if (!help || !inp) return;

        help.classList.remove('text-success', 'text-danger');
        inp.classList.remove('is-valid', 'is-invalid');
        help.textContent = ok
          ? (field === 'email' ? 'Email address is available' : 'Username is available')
          : (field === 'email' ? 'Email address is already taken' : 'Username is already taken');
        help.classList.add(ok ? 'text-success' : 'text-danger');
        inp.classList.add(ok ? 'is-valid' : 'is-invalid');
        if (field === 'username') uOK = ok;
        if (field === 'email') eOK = ok;
        if (submit) submit.disabled = !(uOK && eOK);
      };

      const check = (f, v) => {
        if (!v) {
          mark(f, true);
          return;
        }
        apiCheck(f, v, userId).then(j => mark(f, j.status === 'available'));
      };

      if (uField && eField) {
        check('username', uField.value);
        check('email', eField.value);
        if (submit) submit.disabled = !(uOK && eOK);

        uField.addEventListener('input', () => check('username', uField.value));
        eField.addEventListener('input', () => check('email', eField.value));
      }

      if (form) {
        form.addEventListener('submit', e => {
          if (!uOK || !eOK) {
            e.preventDefault();
            alert('âŒ Fix username/email first.');
          }
        });
      }
    }

</script>
@endpush
