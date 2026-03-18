@extends('layouts.app')

@section('title', 'Manual Pricing')

@push('styles')
<style>
  .card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    max-width: 760px;
  }
  .title {
    color: var(--primary);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
  }
  .hint {
    color: #6c757d;
    margin-bottom: 1rem;
  }
  .field-label {
    font-weight: 600;
    display: block;
    margin-bottom: 0.5rem;
  }
  .field {
    width: 100%;
    max-width: 320px;
    padding: 0.7rem 0.9rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
  }
  .error {
    color: var(--danger);
    font-size: 0.9rem;
    margin-top: 0.35rem;
  }
  .actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 1rem;
  }
  .btn-link {
    text-decoration: none;
  }
</style>
@endpush

@section('content')
  <div class="card">
    <h1 class="title">Manual Pricing</h1>
    <p class="hint">Enter the total invoice amount for <strong>{{ $billTo['Company Name'] ?? 'this client' }}</strong>.</p>

    <form method="POST" action="{{ route('invoices.manual-pricing.save') }}">
      @csrf

      <label for="manual_total" class="field-label">Total Amount</label>
      <input
        id="manual_total"
        type="number"
        name="manual_total"
        class="field"
        step="0.01"
        min="0.01"
        value="{{ old('manual_total') }}"
        required
      >
      @error('manual_total')
        <div class="error">{{ $message }}</div>
      @enderror

      <div class="actions">
        <a href="{{ route('invoices.price-select') }}" class="btn btn-secondary btn-link">Back</a>
        <button type="submit" class="btn btn-primary">Create Invoice</button>
      </div>
    </form>
  </div>
@endsection
