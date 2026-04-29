@extends('layouts.app')

@php
  $activeMenu = 'settings';
  $activeTab = 'permissions';

  $permissionGroups = [
    'Dashboard & Reports' => [
      'view_dashboard',
      'access_reports',
      'access_history',
    ],
    'Invoices Page' => [
      'create_invoice',
      'delete_invoice',
      'edit_invoice',
      'save_invoice',
      'view_invoices',
      'mark_invoice_paid',
      'download_invoice_pdf',
      'email_invoice',
      'restore_invoices',
      'view_invoice_payment_info',
      'delete_forever',
      'view_invoice_history',
      'view_invoice_logs',
      'add_invoice_field',
      'show_due_date',
      'show_due_time',
      'show_invoice_date',
      'show_invoice_time',
      'show_invoice_checkboxes',
      'toggle_bank_details',
      'manage_recurring_invoices',
    ],
    'Clients Page' => [
      'access_clients_tab',
      'view_clients',
      'view_all_clients',
      'add_client',
      'edit_client',
      'delete_client',
      'restore_clients',
      'undo_recent_client',
      'undo_all_clients',
      'export_clients',
      'search_clients',
    ],
    'Expenses Page' => [
      'access_expenses_tab',
      'view_expenses',
      'view_all_expenses',
      'add_expense',
      'edit_expense',
      'delete_expense',
      'undo_recent_expense',
      'undo_all_expenses',
      'change_expense_status',
      'view_expense_details',
      'search_expenses',
      'export_expenses',
      'view_expenses_trashbin',
      'view_all_expenses_trashbin',
      'delete_expense_forever',
    ],
    'Settings Page' => [
      'update_basic_settings',
      'access_basic_settings',
      'assign_roles',
      'manage_users',
      'manage_users_page',
      'add_user',
      'edit_user',
      'delete_user',
      'suspend_users',
      'manage_permissions',
      'manage_payment_methods',
      'manage_card_payments',
      'manage_bank_details',
      'manage_reminder_settings',
      'access_email_templates_page',
      'add_email_template',
      'edit_email_template',
      'delete_email_template',
      'manage_notification_categories',
      'manage_role_viewable',
    ],
    'Trash Bin Page' => [
      'access_trashbin',
      'view_all_trash',
      'restore_deleted_items',
    ],
    'Support Page' => [
      'access_support',
    ],
    'Login Logs Page' => [
      'view_login_logs',
      'terminate_sessions',
      'terminate_own_session',
    ],
  ];

  $groupedPermissionNames = collect($permissionGroups)->flatten()->all();
@endphp

@section('title', 'Settings - Permissions')

@push('styles')
<style>
  .permissions-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
  }
  .permission-search {
    position: relative;
    width: min(420px, 100%);
  }
  .permission-search .material-icons-outlined {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    pointer-events: none;
  }
  .permission-search-input {
    width: 100%;
    min-height: 46px;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--card-bg);
    color: var(--dark);
    box-shadow: var(--shadow);
  }
  .permission-search-input:focus {
    outline: 3px solid color-mix(in srgb, var(--primary) 22%, transparent);
    border-color: var(--primary);
  }
  .permission-matrix-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
  }
  .permission-matrix-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border);
  }
  .matrix-help {
    color: var(--gray);
    font-size: 0.92rem;
  }
  .permission-table-wrap {
    overflow: auto;
    max-height: calc(100vh - 260px);
  }
  .permission-matrix {
    width: 100%;
    min-width: 860px;
    border-collapse: separate;
    border-spacing: 0;
  }
  .permission-matrix th,
  .permission-matrix td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }
  .permission-matrix thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: var(--card-bg);
    color: var(--dark);
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
  }
  .permission-matrix thead th:first-child,
  .permission-name-cell {
    position: sticky;
    left: 0;
    z-index: 2;
    background: var(--card-bg);
    text-align: left;
    min-width: 300px;
  }
  .permission-matrix thead th:first-child {
    z-index: 4;
  }
  .role-heading {
    display: grid;
    gap: 0.45rem;
    justify-items: center;
  }
  .role-heading-title {
    color: var(--primary);
    font-weight: 700;
  }
  .role-heading .btn-sm {
    padding: 0.35rem 0.7rem;
    font-size: 0.78rem;
  }
  .permission-name {
    display: block;
    color: var(--dark);
    font-weight: 600;
    line-height: 1.25;
  }
  .permission-toggle-cell {
    text-align: center;
  }
  .permission-group-row th {
    position: sticky;
    left: 0;
    z-index: 2;
    padding: 0.7rem 1rem;
    background: color-mix(in srgb, var(--card-bg) 88%, var(--primary) 12%);
    color: var(--dark);
    border-bottom: 1px solid var(--border);
    text-align: left;
  }
  .permission-group-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 800;
  }
  .permission-empty-state {
    display: none;
    padding: 1rem;
    color: var(--gray);
    border-top: 1px solid var(--border);
  }
  .permission-readonly-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }
  .permission-readonly-table th,
  .permission-readonly-table td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--border);
    text-align: left;
  }
  .permission-readonly-table th {
    background: color-mix(in srgb, var(--card-bg) 88%, var(--primary) 12%);
    color: var(--dark);
    font-weight: 800;
  }
  .permission-readonly-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.12);
    color: #15803d;
    font-size: 0.82rem;
    font-weight: 700;
  }
  .permission-readonly-pill .material-icons-outlined {
    font-size: 1rem;
  }
  .perm-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
  }
  .perm-switch input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
  }
  .perm-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: var(--border);
    cursor: pointer;
    transition: background 0.18s ease;
  }
  .perm-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.25);
    transition: transform 0.18s ease;
  }
  .perm-switch input:checked + .perm-slider {
    background: var(--primary);
  }
  .perm-switch input:checked + .perm-slider::after {
    transform: translateX(22px);
  }
  .perm-switch input:focus-visible + .perm-slider {
    outline: 3px solid color-mix(in srgb, var(--primary) 35%, transparent);
    outline-offset: 2px;
  }
  .btn-sm {
    padding: 0.45rem 0.9rem;
    font-size: 0.9rem;
  }
  @media (max-width: 720px) {
    .permissions-page-header,
    .permission-matrix-toolbar {
      flex-direction: column;
      align-items: stretch;
    }
    .permission-table-wrap {
      max-height: none;
    }
    .permission-matrix {
      min-width: 760px;
    }
  }
</style>
@endpush

@section('content')
  <div class="page-header permissions-page-header">
    <div>
      <h1 class="page-title">Permission Matrix</h1>
      <p class="page-subtitle">
        {{ $canManagePermissions ? 'Assign permissions to roles.' : 'View the permissions assigned to your role.' }}
      </p>
    </div>
    <div class="permission-search">
      <span class="material-icons-outlined">search</span>
      <input type="search" id="permissionSearch" class="permission-search-input" placeholder="Search permissions or pages..." aria-label="Search permissions or pages">
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
    </div>
  @endif

  @if(!$canManagePermissions)
    <section class="permission-matrix-card">
      <div class="permission-matrix-toolbar">
        <div class="matrix-help">
          These permissions are assigned to your role. You can view them only; changes are restricted to admin users.
        </div>
      </div>

      <div class="permission-table-wrap">
        <table class="permission-readonly-table">
          @foreach($permissionGroups as $groupName => $permissionNames)
            @php
              $groupKey = \Illuminate\Support\Str::slug($groupName);
              $groupPermissions = $permissions->whereIn('name', $permissionNames)->sortBy(function ($permission) use ($permissionNames) {
                return array_search($permission->name, $permissionNames);
              });
            @endphp

            @if($groupPermissions->isNotEmpty())
              <tbody data-permission-group="{{ $groupKey }}">
                <tr class="permission-group-row" data-group-row="{{ $groupKey }}">
                  <th colspan="2">
                    <span class="permission-group-label">
                      <span class="material-icons-outlined">folder_open</span>
                      {{ $groupName }}
                    </span>
                  </th>
                </tr>
                @foreach($groupPermissions as $permission)
                  @php
                    $permissionLabel = $permission->description ?: ucwords(str_replace('_', ' ', $permission->name));
                  @endphp
                  <tr data-permission-row data-group-key="{{ $groupKey }}" data-search-text="{{ strtolower($permissionLabel . ' ' . $permission->name . ' ' . $groupName) }}">
                    <td>
                      <span class="permission-name">{{ $permissionLabel }}</span>
                    </td>
                    <td style="width: 170px;">
                      <span class="permission-readonly-pill">
                        <span class="material-icons-outlined">visibility</span>
                        View Only
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            @endif
          @endforeach

          @php
            $otherPermissions = $permissions->whereNotIn('name', $groupedPermissionNames);
          @endphp

          @if($otherPermissions->isNotEmpty())
            <tbody data-permission-group="other-permissions">
              <tr class="permission-group-row" data-group-row="other-permissions">
                <th colspan="2">
                  <span class="permission-group-label">
                    <span class="material-icons-outlined">folder_open</span>
                    Other Permissions
                  </span>
                </th>
              </tr>
              @foreach($otherPermissions as $permission)
                @php
                  $permissionLabel = $permission->description ?: ucwords(str_replace('_', ' ', $permission->name));
                @endphp
                <tr data-permission-row data-group-key="other-permissions" data-search-text="{{ strtolower($permissionLabel . ' ' . $permission->name . ' Other Permissions') }}">
                  <td>
                    <span class="permission-name">{{ $permissionLabel }}</span>
                  </td>
                  <td style="width: 170px;">
                    <span class="permission-readonly-pill">
                      <span class="material-icons-outlined">visibility</span>
                      View Only
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          @endif
        </table>
      </div>

      @if($permissions->isEmpty())
        <div class="permission-empty-state" style="display:block;">No permissions are assigned to your role.</div>
      @endif
      <div class="permission-empty-state" id="permissionEmptyState">No permissions match your search.</div>
    </section>
  @else
    <form method="POST" action="{{ route('settings.permissions.matrix.update') }}" class="permission-matrix-card">
    @csrf
    <div class="permission-matrix-toolbar">
      <div class="matrix-help">Permissions are listed on the left. Role toggles are shown across each row.</div>
      <button type="submit" class="btn btn-primary btn-sm">Save All</button>
    </div>

    <div class="permission-table-wrap">
      <table class="permission-matrix">
        <thead>
          <tr>
            <th>Permission</th>
            @foreach($roles as $role)
              <th>
                <div class="role-heading">
                  <span class="role-heading-title">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                  <button type="button" class="btn btn-outline btn-sm" onclick="applyRecommended(@js($role->name), @js($role->id))">Recommended</button>
                </div>
              </th>
            @endforeach
          </tr>
        </thead>
        @foreach($permissionGroups as $groupName => $permissionNames)
          @php
            $groupKey = \Illuminate\Support\Str::slug($groupName);
            $groupPermissions = $permissions->whereIn('name', $permissionNames)->sortBy(function ($permission) use ($permissionNames) {
              return array_search($permission->name, $permissionNames);
            });
          @endphp

          @if($groupPermissions->isNotEmpty())
            <tbody data-permission-group="{{ $groupKey }}">
              <tr class="permission-group-row" data-group-row="{{ $groupKey }}">
                <th colspan="{{ $roles->count() + 1 }}">
                  <span class="permission-group-label">
                    <span class="material-icons-outlined">folder_open</span>
                    {{ $groupName }}
                  </span>
                </th>
              </tr>
              @foreach($groupPermissions as $permission)
                @php
                  $permissionLabel = $permission->description ?: ucwords(str_replace('_', ' ', $permission->name));
                @endphp
                <tr data-permission-row data-group-key="{{ $groupKey }}" data-search-text="{{ strtolower($permissionLabel . ' ' . $permission->name . ' ' . $groupName) }}">
                  <td class="permission-name-cell">
                    <span class="permission-name">{{ $permissionLabel }}</span>
                  </td>
                  @foreach($roles as $role)
                    <td class="permission-toggle-cell">
                      <label class="perm-switch" aria-label="{{ $permissionLabel }} for {{ ucwords(str_replace('_', ' ', $role->name)) }}">
                        <input type="checkbox" name="role_permissions[{{ $role->id }}][{{ $permission->id }}]" value="1" data-role-id="{{ $role->id }}" data-permission-name="{{ $permission->name }}"
                          {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                        <span class="perm-slider"></span>
                      </label>
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          @endif
        @endforeach

        @php
          $otherPermissions = $permissions->whereNotIn('name', $groupedPermissionNames);
        @endphp

        @if($otherPermissions->isNotEmpty())
          <tbody data-permission-group="other-permissions">
            <tr class="permission-group-row" data-group-row="other-permissions">
              <th colspan="{{ $roles->count() + 1 }}">
                <span class="permission-group-label">
                  <span class="material-icons-outlined">folder_open</span>
                  Other Permissions
                </span>
              </th>
            </tr>
            @foreach($otherPermissions as $permission)
              @php
                $permissionLabel = $permission->description ?: ucwords(str_replace('_', ' ', $permission->name));
              @endphp
              <tr data-permission-row data-group-key="other-permissions" data-search-text="{{ strtolower($permissionLabel . ' ' . $permission->name . ' Other Permissions') }}">
                <td class="permission-name-cell">
                  <span class="permission-name">{{ $permissionLabel }}</span>
                </td>
                @foreach($roles as $role)
                  <td class="permission-toggle-cell">
                    <label class="perm-switch" aria-label="{{ $permissionLabel }} for {{ ucwords(str_replace('_', ' ', $role->name)) }}">
                      <input type="checkbox" name="role_permissions[{{ $role->id }}][{{ $permission->id }}]" value="1" data-role-id="{{ $role->id }}" data-permission-name="{{ $permission->name }}"
                        {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                      <span class="perm-slider"></span>
                    </label>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        @endif
      </table>
    </div>

    <div class="permission-empty-state" id="permissionEmptyState">No permissions match your search.</div>
    </form>
  @endif
@endsection

@push('scripts')
<script>
  function filterPermissions() {
    const searchInput = document.getElementById('permissionSearch');
    const emptyState = document.getElementById('permissionEmptyState');
    if (!searchInput) return;

    const term = searchInput.value.trim().toLowerCase();
    let visibleRowCount = 0;

    document.querySelectorAll('[data-permission-row]').forEach(function(row) {
      const isVisible = term === '' || (row.dataset.searchText || '').includes(term);
      row.style.display = isVisible ? '' : 'none';
      if (isVisible) visibleRowCount += 1;
    });

    document.querySelectorAll('[data-group-row]').forEach(function(groupRow) {
      const groupKey = groupRow.dataset.groupRow;
      const hasVisibleRows = Array.from(document.querySelectorAll('[data-permission-row][data-group-key="' + groupKey + '"]'))
        .some(function(row) {
          return row.style.display !== 'none';
        });
      groupRow.style.display = hasVisibleRows ? '' : 'none';
    });

    if (emptyState) {
      emptyState.style.display = visibleRowCount === 0 ? 'block' : 'none';
    }
  }

  async function applyRecommended(roleName, roleId) {
    try {
      const url = '{{ route("api.settings.recommended-permissions") }}' + '?role=' + encodeURIComponent(roleName);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Failed to load recommended permissions');
      const data = await res.json();

      const selected = new Set(data || []);
      document.querySelectorAll('input[type="checkbox"][data-role-id="' + roleId + '"]').forEach(function(cb) {
        cb.checked = selected.has(cb.dataset.permissionName);
      });
    } catch (e) {
      alert('Error: ' + e.message);
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('permissionSearch');
    if (searchInput) {
      searchInput.addEventListener('input', filterPermissions);
    }
  });
</script>
@endpush
