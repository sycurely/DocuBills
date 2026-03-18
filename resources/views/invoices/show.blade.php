@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

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
      max-width: 1200px;
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

    .invoice-html {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1rem;
      background: white;
    }
    .invoice-preview-frame {
      width: 100%;
      min-height: 1100px;
      border: none;
      background: #fff;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Invoice {{ $invoice->invoice_number }}</h1>
      <div>
        @if(has_permission('download_invoice_pdf'))
          <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-primary">
            <i class="fas fa-download"></i> Download PDF
          </a>
        @endif
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <div class="card">
      @if($invoice->html)
        <div class="invoice-html">
          <iframe
            class="invoice-preview-frame"
            title="Invoice Preview {{ $invoice->invoice_number }}"
            srcdoc="{{ $invoice->html }}"
          ></iframe>
        </div>
      @else
        <p>Invoice HTML content not available.</p>
      @endif
    </div>
  </div>

@endsection

@push('scripts')
<script>
</script>
@endpush
