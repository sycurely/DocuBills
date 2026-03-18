@extends('layouts.app')

@section('title', 'Trash Bin')

@php
  $activeMenu = 'trashbin';
@endphp

@push('styles')
<style>
  .trash-wrap {
    display: grid;
    gap: 1.1rem;
  }

  .trash-header h1 {
    margin: 0;
    color: var(--primary);
    font-size: 1.9rem;
  }

  .trash-header p {
    margin: 0.35rem 0 0;
    color: var(--gray);
  }

  .trash-stats {
    display: grid;
    gap: 0.8rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .trash-stat {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 0.95rem;
  }

  .trash-stat-label {
    font-size: 0.82rem;
    color: var(--gray);
  }

  .trash-stat-value {
    font-size: 1.4rem;
    color: var(--dark);
    font-weight: 700;
    margin-top: 0.3rem;
  }

  .trash-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 1rem;
  }

  .trash-card-head {
    margin-bottom: 0.8rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .trash-card-title {
    margin: 0;
    color: var(--primary);
    font-size: 1.05rem;
  }

  .trash-count {
    color: var(--gray);
    font-size: 0.85rem;
  }

  .trash-table-wrap {
    overflow-x: auto;
  }

  .trash-table {
    width: 100%;
    border-collapse: collapse;
  }

  .trash-table th,
  .trash-table td {
    border-bottom: 1px solid var(--border);
    padding: 0.72rem;
    text-align: left;
    white-space: nowrap;
  }

  .trash-table th {
    background: rgba(67, 97, 238, 0.08);
    color: var(--primary);
    font-size: 0.83rem;
  }

  .trash-muted {
    color: var(--gray);
    font-size: 0.84rem;
  }

  .trash-empty {
    color: var(--gray);
    text-align: center;
    padding: 1rem;
  }

  .trash-actions {
    display: flex;
    gap: 0.4rem;
    align-items: center;
  }

  @media (max-width: 860px) {
    .trash-stats {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="trash-wrap">
  <section class="trash-header">
    <h1>Trash Bin</h1>
    <p>Restore deleted records or permanently remove them from the system.</p>
  </section>

  <section class="trash-stats">
    <article class="trash-stat">
      <div class="trash-stat-label">Total Deleted Items</div>
      <div class="trash-stat-value">{{ number_format($totalDeleted) }}</div>
    </article>
    <article class="trash-stat">
      <div class="trash-stat-label">Can View All Trash</div>
      <div class="trash-stat-value">{{ $canViewAllTrash ? 'Yes' : 'No' }}</div>
    </article>
    <article class="trash-stat">
      <div class="trash-stat-label">Can Restore</div>
      <div class="trash-stat-value">{{ $canRestoreDeletedItems ? 'Yes' : 'No' }}</div>
    </article>
  </section>

  @foreach($resources as $resource)
    <section class="trash-card">
      <div class="trash-card-head">
        <h2 class="trash-card-title">{{ $resource['label'] }}</h2>
        <span class="trash-count">{{ $resource['rows']->count() }} item(s)</span>
      </div>
      <div class="trash-table-wrap">
        <table class="trash-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Deleted At</th>
              <th>Deleted By</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($resource['rows'] as $row)
              <tr>
                <td>
                  @if($resource['restore_route_type'] === 'invoice')
                    {{ $row->invoice_number }} - {{ $row->bill_to_name }}
                  @elseif($resource['restore_route_type'] === 'client')
                    {{ $row->company_name }}
                  @elseif($resource['restore_route_type'] === 'expense')
                    {{ $row->vendor }} ({{ number_format((float) $row->amount, 2) }})
                  @elseif($resource['restore_route_type'] === 'email-template')
                    {{ $row->template_name }}
                  @elseif($resource['restore_route_type'] === 'user')
                    {{ $row->username }}
                  @endif
                </td>
                <td>{{ optional($row->deleted_at)->format('Y-m-d H:i:s') ?: '-' }}</td>
                <td>
                  @if(isset($row->creator))
                    {{ $row->creator?->username ?: '-' }}
                  @elseif($resource['restore_route_type'] === 'user')
                    {{ $row->username }}
                  @else
                    -
                  @endif
                </td>
                <td>
                  <div class="trash-actions">
                    @if($canRestoreDeletedItems)
                      <form method="POST" action="{{ route('trash-bin.restore', ['type' => $resource['restore_route_type'], 'id' => $row->id]) }}" onsubmit="return confirm('Restore this item?');">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Restore</button>
                      </form>
                    @endif

                    @php
                      $canForceDelete = match($resource['force_route_type']) {
                        'invoice' => has_permission('delete_forever'),
                        'client' => has_permission('delete_client'),
                        'expense' => has_permission('delete_expense_forever'),
                        'email-template' => has_permission('delete_email_template'),
                        'user' => has_permission('delete_user'),
                        default => false,
                      };
                    @endphp

                    @if($canForceDelete)
                      <form method="POST" action="{{ route('trash-bin.force-delete', ['type' => $resource['force_route_type'], 'id' => $row->id]) }}" onsubmit="return confirm('Permanently delete this item? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete Forever</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="trash-empty">No deleted {{ strtolower($resource['label']) }} found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endforeach
</div>
@endsection
