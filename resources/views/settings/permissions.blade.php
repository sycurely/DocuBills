@extends('layouts.app')

@php
  $activeMenu = 'settings';
  $activeTab = 'permissions';
@endphp

@section('title', 'Settings - Permissions')

@push('styles')
<style>
  .role-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  .role-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
  }
  .permission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 0.75rem 1rem;
  }
  .perm-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.5rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--card-bg);
  }
  .perm-item input {
    margin: 0;
  }
  .perm-item span {
    font-size: 0.95rem;
  }
  .role-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }
  .btn-sm {
    padding: 0.45rem 0.9rem;
    font-size: 0.9rem;
  }
</style>
@endpush

@section('content')
  <div class="page-header">
    <h1 class="page-title">Permission Matrix</h1>
    <p class="page-subtitle">Assign permissions to roles.</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
    </div>
  @endif

  @foreach($roles as $role)
    <div class="role-card">
      <form method="POST" action="{{ route('settings.permissions.update', $role) }}">
        @csrf
        <div class="role-header">
          <div class="role-title">{{ ucwords(str_replace('_', ' ', $role->name)) }}</div>
          <div class="role-actions">
            <button type="button" class="btn btn-outline btn-sm" onclick="applyRecommended('{{ $role->name }}', '{{ $role->id }}')">Apply Recommended</button>
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
          </div>
        </div>

        <div class="permission-grid" id="role-{{ $role->id }}">
          @foreach($permissions as $permission)
            <label class="perm-item">
              <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
              <span>{{ $permission->name }}</span>
            </label>
          @endforeach
        </div>
      </form>
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  async function applyRecommended(roleName, roleId) {
    try {
      const url = '{{ route("api.settings.recommended-permissions") }}' + '?role=' + encodeURIComponent(roleName);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Failed to load recommended permissions');
      const data = await res.json();

      const grid = document.getElementById('role-' + roleId);
      if (!grid) return;

      const selected = new Set(data || []);
      grid.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
        const label = cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : '';
        cb.checked = selected.has(label);
      });
    } catch (e) {
      alert('Error: ' + e.message);
    }
  }
</script>
@endpush
