@extends('layouts.app')

@section('title', 'Expense Management')

@push('styles')
<style>
.container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
    }

    .btn-success {
      background: var(--success);
      color: white;
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }

    .search-filters {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      background: var(--card-bg);
      padding: 1.5rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
    }

    .search-input, .filter-select {
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
    }

    .search-input {
      flex: 1;
      min-width: 200px;
    }

    .filter-select {
      min-width: 150px;
    }

    .table-container {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
    }

    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
    }

    .badge-success {
      background: var(--success);
      color: white;
    }

    .badge-danger {
      background: var(--danger);
      color: white;
    }

    .btn-sm {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Expense Management</h1>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        @if(has_permission('export_expenses'))
          <a href="{{ route('expenses.export', request()->all()) }}" class="btn btn-success">
            <i class="fas fa-download"></i> Export CSV
          </a>
        @endif
        @if(has_permission('add_expense'))
          <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Expense
          </a>
        @endif
        @if(has_permission('undo_recent_expense'))
          <form method="POST" action="{{ route('expenses.undo-recent') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">
              <i class="fas fa-undo"></i> Undo Recent
            </button>
          </form>
        @endif
        @if(has_permission('undo_all_expenses'))
          <form method="POST" action="{{ route('expenses.restore-all') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">
              <i class="fas fa-redo"></i> Restore All
            </button>
          </form>
        @endif
      </div>
    </div>

    @if(session('success'))
      <div style="background: var(--success); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div style="background: var(--danger); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        {{ session('error') }}
      </div>
    @endif

    <form method="GET" action="{{ route('expenses.index') }}" class="search-filters">
      <input type="text" name="search" class="search-input" placeholder="Search vendor, category, notes..." value="{{ request('search') }}">
      <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid</option>
        <option value="Unpaid" {{ request('status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
      </select>
      <select name="category" class="filter-select">
        <option value="">All Categories</option>
        @foreach($categories as $category)
          <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
        @endforeach
      </select>
      <select name="client_id" class="filter-select">
        <option value="">All Clients</option>
        @foreach($clients as $client)
          <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
        @endforeach
      </select>
      <input type="date" name="date_from" class="filter-select" placeholder="From Date" value="{{ request('date_from') }}">
      <input type="date" name="date_to" class="filter-select" placeholder="To Date" value="{{ request('date_to') }}">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Filter
      </button>
      <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
        <i class="fas fa-times"></i> Clear
      </a>
    </form>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Vendor</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Client</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Recurring</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($expenses as $expense)
            <tr>
              <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
              <td><strong>{{ $expense->vendor }}</strong></td>
              <td>${{ number_format($expense->amount, 2) }}</td>
              <td>{{ $expense->category ?? 'N/A' }}</td>
              <td>{{ $expense->client->company_name ?? 'N/A' }}</td>
              <td>
                <span class="badge {{ $expense->status === 'Paid' ? 'badge-success' : 'badge-danger' }}">
                  {{ $expense->status }}
                </span>
              </td>
              <td>{{ $expense->payment_method ?? 'N/A' }}</td>
              <td>{{ $expense->is_recurring ? 'Yes' : 'No' }}</td>
              <td>
                <div style="display: flex; gap: 0.5rem;">
                  <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm" style="padding: 0.5rem; background: var(--primary); color: white;">
                    <i class="fas fa-eye"></i>
                  </a>
                  @if(has_permission('edit_expense'))
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm" style="padding: 0.5rem; background: #f8961e; color: white;">
                      <i class="fas fa-edit"></i>
                    </a>
                  @endif
                  @if(has_permission('delete_expense'))
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm" style="padding: 0.5rem; background: var(--danger); color: white;">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  @endif
                  @if(has_permission('change_expense_status'))
                    <form method="POST" action="{{ route('expenses.change-status', $expense) }}" style="display: inline;">
                      @csrf
                      <input type="hidden" name="status" value="{{ $expense->status === 'Paid' ? 'Unpaid' : 'Paid' }}">
                      <button type="submit" class="btn btn-sm" style="padding: 0.5rem; background: var(--success); color: white;" title="Toggle Status">
                        <i class="fas fa-toggle-{{ $expense->status === 'Paid' ? 'off' : 'on' }}"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="text-align: center; padding: 2rem;">
                No expenses found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top: 1.5rem;">
      {{ $expenses->links() }}
    </div>
  </div>

@endsection

@push('scripts')
<script>
</script>
@endpush

