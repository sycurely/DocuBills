@extends('layouts.app')

@section('title', 'Edit Expense')

@push('styles')
<style>

    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --dark: #212529;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 2rem;
    }

    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 2rem;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: var(--dark);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
      box-sizing: border-box;
    }

    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }

    .form-group input[type="checkbox"] {
      width: auto;
      margin-right: 0.5rem;
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

    .file-preview {
      margin-top: 0.5rem;
      padding: 0.5rem;
      background: #f8f9fa;
      border-radius: var(--radius);
      font-size: 0.875rem;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <h1 class="page-title">Edit Expense</h1>

    <div class="card">
      <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="expense_date">Date *</label>
          <input type="date" name="expense_date" id="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}" required>
        </div>

        <div class="form-group">
          <label for="vendor">Vendor *</label>
          <input type="text" name="vendor" id="vendor" value="{{ $expense->vendor }}" required>
        </div>

        <div class="form-group">
          <label for="amount">Amount *</label>
          <input type="number" step="0.01" name="amount" id="amount" min="0" value="{{ $expense->amount }}" required>
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <input type="text" name="category" id="category" value="{{ $expense->category }}" placeholder="e.g., Office Supplies, Travel, Meals">
        </div>

        <div class="form-group">
          <label for="client_id">Client</label>
          <select name="client_id" id="client_id">
            <option value="">-- Select Client --</option>
            @foreach($clients as $client)
              <option value="{{ $client->id }}" {{ $expense->client_id == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select name="status" id="status">
            <option value="Unpaid" {{ $expense->status === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
            <option value="Paid" {{ $expense->status === 'Paid' ? 'selected' : '' }}>Paid</option>
          </select>
        </div>

        <div class="form-group">
          <label for="payment_method">Payment Method</label>
          <select name="payment_method" id="payment_method">
            <option value="">-- Select Method --</option>
            <option value="Cash" {{ $expense->payment_method === 'Cash' ? 'selected' : '' }}>Cash</option>
            <option value="Check" {{ $expense->payment_method === 'Check' ? 'selected' : '' }}>Check</option>
            <option value="Bank Transfer" {{ $expense->payment_method === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
            <option value="Credit Card" {{ $expense->payment_method === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
            <option value="Debit Card" {{ $expense->payment_method === 'Debit Card' ? 'selected' : '' }}>Debit Card</option>
            <option value="Other" {{ $expense->payment_method === 'Other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" name="is_recurring" value="1" {{ $expense->is_recurring ? 'checked' : '' }}>
            Recurring Expense
          </label>
        </div>

        <div class="form-group">
          <label for="notes">Notes</label>
          <textarea name="notes" id="notes" rows="3">{{ $expense->notes }}</textarea>
        </div>

        <div class="form-group">
          <label for="receipt">Receipt</label>
          <input type="file" name="receipt" id="receipt" accept="image/*,application/pdf">
          @if($expense->receipt_url)
            <div class="file-preview">
              <i class="fas fa-file"></i> Current: <a href="{{ $expense->receipt_url }}" target="_blank">View Receipt</a>
            </div>
          @endif
          <small style="color: #6c757d;">Max 5MB. Supported: JPG, PNG, PDF</small>
        </div>

        <div class="form-group">
          <label for="payment_proof">Payment Proof</label>
          <input type="file" name="payment_proof" id="payment_proof" accept="image/*,application/pdf">
          @if($expense->payment_proof)
            <div class="file-preview">
              <i class="fas fa-file"></i> Current: <a href="{{ $expense->payment_proof }}" target="_blank">View Proof</a>
            </div>
          @endif
          <small style="color: #6c757d;">Max 5MB. Supported: JPG, PNG, PDF</small>
        </div>

        <div class="form-group">
          <label for="email_cc">Email CC</label>
          <input type="text" name="email_cc" id="email_cc" value="{{ $expense->email_cc }}" placeholder="comma-separated emails">
        </div>

        <div class="form-group">
          <label for="email_bcc">Email BCC</label>
          <input type="text" name="email_bcc" id="email_bcc" value="{{ $expense->email_bcc }}" placeholder="comma-separated emails">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Expense
          </button>
          <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
</script>
@endpush
