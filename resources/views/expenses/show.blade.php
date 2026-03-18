@extends('layouts.app')

@section('title', 'Expense Details')

@push('styles')
<style>
.container {
      max-width: 1000px;
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
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }

    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 2rem;
      margin-bottom: 1.5rem;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .info-item {
      padding: 1rem;
      background: var(--body-bg);
      border-radius: var(--radius);
    }

    .info-item label {
      display: block;
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
    }

    .info-item strong {
      display: block;
      font-size: 1.125rem;
      color: var(--dark);
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
      display: inline-block;
    }

    .badge-success {
      background: var(--success);
      color: white;
    }

    .badge-danger {
      background: var(--danger);
      color: white;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Expense Details</h1>
      <div>
        @if(has_permission('edit_expense'))
          <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
          </a>
        @endif
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <div class="card">
      <div class="info-grid">
        <div class="info-item">
          <label>Date</label>
          <strong>{{ $expense->expense_date->format('Y-m-d') }}</strong>
        </div>
        <div class="info-item">
          <label>Vendor</label>
          <strong>{{ $expense->vendor }}</strong>
        </div>
        <div class="info-item">
          <label>Amount</label>
          <strong>${{ number_format($expense->amount, 2) }}</strong>
        </div>
        <div class="info-item">
          <label>Category</label>
          <strong>{{ $expense->category ?? 'N/A' }}</strong>
        </div>
        <div class="info-item">
          <label>Client</label>
          <strong>{{ $expense->client->company_name ?? 'N/A' }}</strong>
        </div>
        <div class="info-item">
          <label>Status</label>
          <strong>
            <span class="badge {{ $expense->status === 'Paid' ? 'badge-success' : 'badge-danger' }}">
              {{ $expense->status }}
            </span>
          </strong>
        </div>
        <div class="info-item">
          <label>Payment Method</label>
          <strong>{{ $expense->payment_method ?? 'N/A' }}</strong>
        </div>
        <div class="info-item">
          <label>Recurring</label>
          <strong>{{ $expense->is_recurring ? 'Yes' : 'No' }}</strong>
        </div>
        <div class="info-item">
          <label>Created By</label>
          <strong>{{ $expense->creator->username ?? 'N/A' }}</strong>
        </div>
        <div class="info-item">
          <label>Created At</label>
          <strong>{{ $expense->created_at->format('Y-m-d H:i') }}</strong>
        </div>
      </div>

      @if($expense->notes)
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Notes</label>
          <div style="padding: 1rem; background: var(--body-bg); border-radius: var(--radius);">
            {{ $expense->notes }}
          </div>
        </div>
      @endif

      @if($expense->receipt_url)
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Receipt</label>
          <a href="{{ $expense->receipt_url }}" target="_blank" class="btn btn-primary">
            <i class="fas fa-file"></i> View Receipt
          </a>
        </div>
      @endif

      @if($expense->payment_proof)
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Payment Proof</label>
          <a href="{{ $expense->payment_proof }}" target="_blank" class="btn btn-primary">
            <i class="fas fa-file"></i> View Payment Proof
          </a>
        </div>
      @endif
    </div>
  </div>

@endsection

@push('scripts')
<script>
</script>
@endpush

