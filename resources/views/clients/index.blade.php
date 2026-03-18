@extends('layouts.app')

@php
  $activeMenu = 'clients';
  $perPage = (int) request('per_page', 10);
  $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
@endphp

@section('title', 'Manage Clients')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
  .material-icons { font-size: var(--icon-size); vertical-align: middle; }
  .action-icon .material-icons { font-size: var(--icon-size); }
  .search-wrap .material-icons { font-size: var(--icon-size); }
  .clients-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
  }
  .clients-toolbar .search-wrap {
    flex: 1;
    min-width: 200px;
  }
  .search-input {
    width: 100%;
    padding: 0.6rem 0.75rem 0.6rem 2.25rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
  }
  .search-wrap { position: relative; }
  .search-wrap .material-icons { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: var(--icon-size); }
  .pagination-info { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; }
  .pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
  }
  .pagination-controls select {
    padding: 0.4rem 0.5rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--card-bg);
  }
  .actions-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: flex-end;
    align-items: center;
  }
  .filter-buttons { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
  .filter-btn {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border);
    background: var(--card-bg);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    color: var(--dark);
  }
  .filter-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
  .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
  .modal-backdrop.show { display: flex; }
  .modal-card { background: var(--card-bg); border-radius: var(--radius); padding: 2rem; width: 90%; max-width: 600px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
  .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
  .modal-title { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
  .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray); }
  .action-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 50%; border: none; cursor: pointer; text-decoration: none;
    color: white; transition: var(--transition);
  }
  .action-icon.view { background: #a8c8ff; color: #1f3a8a; }
  .action-icon.edit { background: #b9e6ff; color: #0b4f6c; }
  .action-icon.delete { background: #ffbcbc; color: #7f1d1d; }
  .action-icon:hover { opacity: 0.9; transform: scale(1.05); }
  .actions-cell { display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap; }
  table { width: 100%; border-collapse: collapse; }
  table th, table td { padding: 0.6rem 0.75rem; text-align: left; border-bottom: 1px solid var(--border); }
  table th { font-weight: 600; color: var(--text-muted); font-size: 0.9rem; }
  .view-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1.5rem; margin-bottom: 1rem; }
  .view-grid .full-span { grid-column: 1 / -1; }
  .view-grid label { font-weight: 600; color: var(--text-muted); font-size: 0.9rem; display: block; margin-bottom: 0.25rem; }
  .view-grid div:not(:first-child) { margin-top: 0; }
</style>
@endpush

@section('content')
  <div class="page-header">
    <h1 class="page-title">Manage Clients</h1>
    <div class="actions-row">
      <form action="{{ route('clients.undo-recent') }}" method="POST" style="display:inline;" onsubmit="return confirm('Restore the most recently deleted client?');">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">undo</span> <span>Undo Recent Delete</span></button>
      </form>
      <form action="{{ route('clients.delete-all') }}" method="POST" style="display:inline;" onsubmit="return confirm('Soft-delete ALL active clients? You can restore them using Undo buttons.');">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">person_off</span> <span>Delete All</span></button>
      </form>
      <form action="{{ route('clients.restore-all') }}" method="POST" style="display:inline;" onsubmit="return confirm('Restore all deleted clients?');">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">history</span> <span>Undo All Deletes</span></button>
      </form>
      <a href="{{ route('clients.export') }}" class="btn btn-outline btn-sm"><span class="material-icons">file_download</span> <span>Export to Excel</span></a>
      @if($canAddClient ?? can_add_client())
      <button type="button" class="btn btn-primary" id="newClientBtn" onclick="openModal()"><span class="material-icons">add</span> <span>New Client</span></button>
      @endif
    </div>
  </div>

  <div class="card">
    <form method="GET" action="{{ route('clients.index') }}" id="filterForm">
      <input type="hidden" name="filter" id="filterInput" value="{{ request('filter', 'active') }}">

      <div class="clients-toolbar">
        <div class="search-wrap">
          <span class="material-icons">search</span>
          <input type="text" class="search-input" name="search" id="searchInput" placeholder="Search clients..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-primary"><span class="material-icons">search</span> Search</button>
      </div>

      <div class="pagination-info" id="paginationInfo">
        @if($clients->total() > 0)
          Showing {{ $clients->firstItem() }}-{{ $clients->lastItem() }} of {{ $clients->total() }} clients (Page {{ $clients->currentPage() }} of {{ $clients->lastPage() }})
        @else
          No clients found.
        @endif
      </div>

      <div class="pagination-controls">
        <label for="perPageSelect">Rows per page:</label>
        <select name="per_page" id="perPageSelect" onchange="this.form.submit()">
          @foreach([10, 25, 50, 100] as $n)
            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-buttons">
        <button type="button" class="filter-btn {{ request('filter', 'active') === 'active' ? 'active' : '' }}" onclick="setFilter('active')">Active</button>
        <button type="button" class="filter-btn {{ request('filter') === 'deleted' ? 'active' : '' }}" onclick="setFilter('deleted')">Deleted</button>
      </div>
    </form>

    <div style="overflow-x: auto;">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Company</th>
            <th>Representative</th>
            <th>Phone</th>
            <th>Email</th>
            <th>User</th>
            <th>Last Invoice</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Unpaid</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="clientsTableBody">
          @forelse($clients as $index => $client)
          <tr>
            <td>{{ $clients->firstItem() + $index }}</td>
            <td>{{ $client->company_name }}</td>
            <td>{{ $client->representative ?? '-' }}</td>
            <td>{{ $client->phone ?? '-' }}</td>
            <td>{{ $client->email }}</td>
            <td>{{ $client->creator ? ($client->creator->full_name ?? $client->creator->username) : '-' }}</td>
            <td>{{ $client->invoices_max_invoice_date ? \Carbon\Carbon::parse($client->invoices_max_invoice_date)->format('Y-m-d') : '-' }}</td>
            <td>{{ $client->total_invoices ?? 0 }}</td>
            <td>{{ $client->paid_invoices ?? 0 }}</td>
            <td>{{ $client->unpaid_invoices ?? 0 }}</td>
            <td class="actions-cell" style="white-space: nowrap;">
              <button type="button" class="action-icon view btn-view" title="View" data-id="{{ $client->id }}"><span class="material-icons">visibility</span></button>
              @if($canEditClient ?? can_edit_client())
              <button type="button" class="action-icon edit btn-edit" title="Edit" data-id="{{ $client->id }}"><span class="material-icons">edit</span></button>
              @endif
              <button type="button" class="action-icon delete btn-delete" title="Delete" data-id="{{ $client->id }}" data-name="{{ e($client->company_name) }}"><span class="material-icons">delete</span></button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="11" style="text-align: center; padding: 2rem; color: var(--gray);">No clients found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination-controls" id="paginationControls" style="margin-top: 1rem; @if(!$clients->hasPages()) display:none; @endif">
      @if($clients->hasPages())
        <a href="{{ $clients->url(1) }}" class="btn btn-outline btn-sm" @if($clients->onFirstPage()) style="pointer-events:none;opacity:0.6" @endif>&laquo; First</a>
        <a href="{{ $clients->previousPageUrl() }}" class="btn btn-outline btn-sm" @if(!$clients->previousPageUrl()) style="pointer-events:none;opacity:0.6" @endif>&lsaquo; Prev</a>
        <span class="btn btn-outline btn-sm" style="cursor:default;">Page {{ $clients->currentPage() }} of {{ $clients->lastPage() }}</span>
        <a href="{{ $clients->nextPageUrl() }}" class="btn btn-outline btn-sm" @if(!$clients->nextPageUrl()) style="pointer-events:none;opacity:0.6" @endif>Next &rsaquo;</a>
        <a href="{{ $clients->url($clients->lastPage()) }}" class="btn btn-outline btn-sm" @if($clients->currentPage() >= $clients->lastPage()) style="pointer-events:none;opacity:0.6" @endif>Last &raquo;</a>
      @endif
    </div>
  </div>

  <!-- View Client Modal (reference: Client Details) -->
  <div class="modal-backdrop" id="viewModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title">Client Details</h3>
        <button type="button" class="modal-close" id="closeViewModal">&times;</button>
      </div>
      <div id="clientDetails">
        <!-- Populated by JavaScript -->
      </div>
      <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
        <button type="button" class="btn btn-secondary" id="closeViewBtn">Close</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal (reference: Confirm Deletion) -->
  <div class="modal-backdrop" id="deleteModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title">Confirm Deletion</h3>
        <button type="button" class="modal-close" id="closeDeleteModal">&times;</button>
      </div>
      <p>Are you sure you want to delete <strong id="deleteClientName"></strong>?</p>
      <p style="color: var(--text-muted); font-size: 0.9rem;">This action cannot be undone.</p>
      <form id="deleteClientForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
          <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
          <button type="submit" class="btn btn-danger"><span class="material-icons">delete</span> Delete Client</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div class="modal-backdrop" id="clientModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">Add Client</h3>
        <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      <form id="clientForm" method="POST" action="{{ route('clients.store') }}">
        @csrf
        <input type="hidden" id="formMethod" name="_method" value="POST">
        <div class="form-group">
          <label for="companyName">Company Name</label>
          <input type="text" id="companyName" name="company_name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="representative">Representative Name</label>
          <input type="text" id="representative" name="representative" class="form-control">
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="text" id="phone" name="phone" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="address">Mailing Address</label>
          <textarea id="address" name="address" class="form-control" required></textarea>
        </div>
        <div class="form-group">
          <label for="gstHst">GST/HST Number</label>
          <input type="text" id="gstHst" name="gst_hst" class="form-control">
        </div>
        <div class="form-group">
          <label for="notes">Internal Notes</label>
          <textarea id="notes" name="notes" class="form-control"></textarea>
        </div>
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Client</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  let currentFilter = '{{ request("filter", "active") }}';

  function setFilter(f) {
    currentFilter = f;
    document.getElementById('filterInput').value = f;
    document.getElementById('filterForm').submit();
  }

  function openModal(client) {
    document.getElementById('clientModal').classList.add('show');
    const form = document.getElementById('clientForm');
    if (client) {
      document.getElementById('modalTitle').textContent = 'Edit Client';
      form.action = '{{ route("clients.update", ":id") }}'.replace(':id', client.id);
      document.getElementById('formMethod').value = 'PUT';
      document.getElementById('companyName').value = client.company_name || '';
      document.getElementById('representative').value = client.representative || '';
      document.getElementById('phone').value = client.phone || '';
      document.getElementById('email').value = client.email || '';
      document.getElementById('address').value = client.address || '';
      document.getElementById('gstHst').value = client.gst_hst || '';
      document.getElementById('notes').value = client.notes || '';
    } else {
      document.getElementById('modalTitle').textContent = 'Add Client';
      form.action = '{{ route("clients.store") }}';
      form.reset();
      document.getElementById('formMethod').value = 'POST';
    }
  }

  function closeModal() {
    document.getElementById('clientModal').classList.remove('show');
    document.getElementById('clientForm').reset();
  }

  async function editClient(id) {
    try {
      const r = await fetch('{{ route("api.clients.show", ":id") }}'.replace(':id', id));
      if (r.ok) {
        const client = await r.json();
        openModal(client);
      } else {
        alert('Client not found');
      }
    } catch (e) {
      alert('Error loading client: ' + e.message);
    }
  }

  document.getElementById('clientModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });

  // ─── View Client (reference: View modal with client details) ───
  document.querySelectorAll('.btn-view').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      fetch('{{ route("api.clients.show", ":id") }}'.replace(':id', id))
        .then(function(r) { return r.json(); })
        .then(function(client) {
          const esc = function(s) { return (s == null ? '' : String(s)).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };
          document.getElementById('clientDetails').innerHTML =
            '<div class="view-grid">' +
            '<div><label>Company</label><div>' + esc(client.company_name) + '</div></div>' +
            '<div><label>Representative</label><div>' + (client.representative ? esc(client.representative) : '—') + '</div></div>' +
            '<div><label>Phone</label><div>' + esc(client.phone || '') + '</div></div>' +
            '<div><label>Email</label><div>' + esc(client.email || '') + '</div></div>' +
            '<div><label>Address</label><div>' + esc(client.address || '') + '</div></div>' +
            '<div><label>GST/HST</label><div>' + (client.gst_hst ? esc(client.gst_hst) : '—') + '</div></div>' +
            '<div class="full-span"><label>Notes</label><div>' + (client.notes ? esc(client.notes) : '—') + '</div></div>' +
            '</div>';
          document.getElementById('viewModal').classList.add('show');
        })
        .catch(function() { alert('Failed to load client details'); });
    });
  });
  document.getElementById('closeViewModal').addEventListener('click', function() { document.getElementById('viewModal').classList.remove('show'); });
  document.getElementById('closeViewBtn').addEventListener('click', function() { document.getElementById('viewModal').classList.remove('show'); });
  document.getElementById('viewModal').addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });

  // ─── Edit Client (reference: .btn-edit loads data and opens form) ───
  document.querySelectorAll('.btn-edit').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      if (id) editClient(id);
    });
  });

  // ─── Delete Client (reference: confirmation modal with client name) ───
  var deleteModalEl = document.getElementById('deleteModal');
  var deleteFormEl = document.getElementById('deleteClientForm');
  var deleteNameEl = document.getElementById('deleteClientName');
  document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var name = this.getAttribute('data-name') || 'this client';
      deleteNameEl.textContent = name;
      deleteFormEl.action = '{{ url("/clients") }}/' + id;
      deleteModalEl.classList.add('show');
    });
  });
  document.getElementById('cancelDeleteBtn').addEventListener('click', function() { deleteModalEl.classList.remove('show'); });
  document.getElementById('closeDeleteModal').addEventListener('click', function() { deleteModalEl.classList.remove('show'); });
  deleteModalEl.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });

  // Open edit modal if ?edit=id in URL
  (function() {
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    if (editId) editClient(editId);
  })();

  // Live search: update table as user types (debounced)
  (function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('clientsTableBody');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationControls = document.getElementById('paginationControls');
    if (!searchInput || !tableBody || !paginationInfo) return;

    let timer = null;
    let inFlight = null;

    function updateFromHtml(html) {
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const newBody = doc.getElementById('clientsTableBody');
      const newInfo = doc.getElementById('paginationInfo');
      const newControls = doc.getElementById('paginationControls');

      if (newBody) tableBody.innerHTML = newBody.innerHTML;
      if (newInfo) paginationInfo.innerHTML = newInfo.innerHTML;
      if (paginationControls) {
        if (newControls) {
          paginationControls.innerHTML = newControls.innerHTML;
          paginationControls.style.display = '';
        } else {
          paginationControls.innerHTML = '';
          paginationControls.style.display = 'none';
        }
      }

      // Re-bind action buttons after table update
      document.querySelectorAll('.btn-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          fetch('{{ route("api.clients.show", ":id") }}'.replace(':id', id))
            .then(function(r) { return r.json(); })
            .then(function(client) {
              const esc = function(s) { return (s == null ? '' : String(s)).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };
              document.getElementById('clientDetails').innerHTML =
                '<div class="view-grid">' +
                '<div><label>Company</label><div>' + esc(client.company_name) + '</div></div>' +
                '<div><label>Representative</label><div>' + (client.representative ? esc(client.representative) : '—') + '</div></div>' +
                '<div><label>Phone</label><div>' + esc(client.phone || '') + '</div></div>' +
                '<div><label>Email</label><div>' + esc(client.email || '') + '</div></div>' +
                '<div><label>Address</label><div>' + esc(client.address || '') + '</div></div>' +
                '<div><label>GST/HST</label><div>' + (client.gst_hst ? esc(client.gst_hst) : '—') + '</div></div>' +
                '<div class="full-span"><label>Notes</label><div>' + (client.notes ? esc(client.notes) : '—') + '</div></div>' +
                '</div>';
              document.getElementById('viewModal').classList.add('show');
            })
            .catch(function() { alert('Failed to load client details'); });
        });
      });
      document.querySelectorAll('.btn-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          if (id) editClient(id);
        });
      });
      document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var id = this.getAttribute('data-id');
          var name = this.getAttribute('data-name') || 'this client';
          deleteNameEl.textContent = name;
          deleteFormEl.action = '{{ url("/clients") }}/' + id;
          deleteModalEl.classList.add('show');
        });
      });
    }

    function fetchResults(query) {
      const params = new URLSearchParams(window.location.search);
      if (query) {
        params.set('search', query);
      } else {
        params.delete('search');
      }
      params.delete('page');
      const url = '{{ route("clients.index") }}' + (params.toString() ? ('?' + params.toString()) : '');

      if (inFlight) inFlight.abort();
      inFlight = new AbortController();

      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: inFlight.signal })
        .then(function(r) { return r.text(); })
        .then(function(html) {
          updateFromHtml(html);
          window.history.replaceState({}, '', url);
        })
        .catch(function(e) {
          if (e.name !== 'AbortError') {
            console.error(e);
          }
        });
    }

    searchInput.addEventListener('input', function() {
      const value = this.value.trim();
      if (timer) clearTimeout(timer);
      timer = setTimeout(function() { fetchResults(value); }, 250);
    });
  })();
</script>
@endpush
