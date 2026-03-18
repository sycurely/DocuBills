@extends('layouts.app')

@section('title', 'Add Expense')

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

    .btn-primary:hover {
      background: var(--secondary);
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <h1 class="page-title">Add New Expense</h1>

    <div class="card">
      <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
          <label for="expense_date">Date *</label>
          <input type="date" name="expense_date" id="expense_date" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-group">
          <label for="vendor">Vendor *</label>
          <input type="text" name="vendor" id="vendor" required>
        </div>

        <div class="form-group">
          <label for="amount">Amount *</label>
          <input type="number" step="0.01" name="amount" id="amount" min="0" required>
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <input type="text" name="category" id="category" placeholder="e.g., Office Supplies, Travel, Meals">
        </div>

        <div class="form-group">
          <label for="client_id">Client</label>
          <select name="client_id" id="client_id">
            <option value="">-- Select Client --</option>
            @foreach($clients as $client)
              <option value="{{ $client->id }}">{{ $client->company_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select name="status" id="status">
            <option value="Unpaid">Unpaid</option>
            <option value="Paid">Paid</option>
          </select>
        </div>

        <div class="form-group">
          <label for="payment_method">Payment Method</label>
          <select name="payment_method" id="payment_method">
            <option value="">-- Select Method --</option>
            <option value="Cash">Cash</option>
            <option value="Check">Check</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Credit Card">Credit Card</option>
            <option value="Debit Card">Debit Card</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" name="is_recurring" value="1">
            Recurring Expense
          </label>
        </div>

        <div class="form-group">
          <label for="notes">Notes</label>
          <textarea name="notes" id="notes" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="receipt">Receipt</label>
          <input type="file" name="receipt" id="receipt" accept="image/*,application/pdf">
          <small style="color: #6c757d;">Max 5MB. Supported: JPG, PNG, PDF</small>
        </div>

        <div class="form-group">
          <label for="payment_proof">Payment Proof</label>
          <input type="file" name="payment_proof" id="payment_proof" accept="image/*,application/pdf">
          <small style="color: #6c757d;">Max 5MB. Supported: JPG, PNG, PDF</small>
        </div>

        <div class="form-group">
          <label for="email_cc">Email CC</label>
          <input type="text" name="email_cc" id="email_cc" placeholder="comma-separated emails">
        </div>

        <div class="form-group">
          <label for="email_bcc">Email BCC</label>
          <input type="text" name="email_bcc" id="email_bcc" placeholder="comma-separated emails">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Expense
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
