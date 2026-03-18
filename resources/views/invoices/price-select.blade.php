@extends('layouts.app')

@section('title', 'Configure Invoice Pricing')

@push('styles')
<style>
  .card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .page-title {
    color: var(--primary);
    margin-bottom: 1rem;
  }
  .section-title {
    color: var(--primary);
    font-size: 1.9rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pricing-box {
    border: 2px solid #4361ee;
    border-radius: 12px;
    padding: 1rem;
    background: rgba(67, 97, 238, 0.04);
    margin-top: 0.75rem;
  }
  .columns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem 1rem;
    margin-top: 0.75rem;
  }
  .hint {
    color: #6c757d;
    font-size: 0.92rem;
  }
  .warning-note {
    margin-top: 0.75rem;
    background: #f4efdf;
    border-left: 4px solid #f8961e;
    padding: 0.8rem;
    border-radius: 6px;
  }
  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1rem;
  }
  .btn-link {
    text-decoration: none;
  }
  .error {
    color: var(--danger);
    font-size: 0.9rem;
    margin-top: 0.35rem;
  }
</style>
@endpush

@section('content')
  @php
    $validationMessages = \App\Services\InvoiceValidationContract::uiMessages();
    $oldMode = old('price_mode', 'automatic');
    if ($oldMode === 'column') {
      $oldMode = 'automatic';
    }
    $selectedPriceColumn = old('price_column', $recommendedPriceColumn ?? '');
    $selectedIncludeCols = old('include_cols', $recommendedIncludeCols ?? array_slice($headers, 0, 15));
    if (!is_array($selectedIncludeCols)) {
      $selectedIncludeCols = $recommendedIncludeCols ?? array_slice($headers, 0, 15);
    }
  @endphp
  <h1 class="section-title"><i class="fas fa-money-bill-wave"></i> Configure Invoice Pricing</h1>

  <form method="POST" action="{{ route('invoices.price-select.save') }}">
    @csrf

    @if(session('error'))
      <div class="card" style="border-left:4px solid var(--danger);">
        <div class="error" style="margin-top:0;">{{ session('error') }}</div>
      </div>
    @endif

    <div class="card">
      <h2 class="page-title">Pricing Method</h2>

      <label style="display:block; margin-bottom:0.6rem;">
        <input type="radio" name="price_mode" value="automatic" {{ $oldMode === 'automatic' ? 'checked' : '' }}>
        <strong>Automatic Pricing</strong> - Use a column from my data
      </label>

      <div class="pricing-box" id="automatic-box">
        <p>Select which column contains item prices:</p>
        @foreach($headers as $header)
          <label style="display:block; margin:0.45rem 0;">
            <input type="radio" name="price_column" value="{{ $header }}" {{ $selectedPriceColumn === $header ? 'checked' : '' }}>
            Column: <strong>{{ $header }}</strong>
          </label>
        @endforeach
        @error('price_column')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <label style="display:block; margin-top:1.2rem; margin-bottom:0.6rem;">
        <input type="radio" name="price_mode" value="manual" {{ $oldMode === 'manual' ? 'checked' : '' }}>
        <strong>Manual Pricing</strong> - I'll enter the total invoice amount myself
      </label>

      <div class="warning-note" id="manual-box">
        <p class="hint">You'll enter the total amount on the next screen.</p>
      </div>

      @error('price_mode')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="card">
      <h2 class="page-title">Columns to Include <span class="hint">(max 15)</span></h2>
      <div class="columns-grid">
        @foreach($headers as $header)
          <label>
            <input
              type="checkbox"
              name="include_cols[]"
              value="{{ $header }}"
              {{ in_array($header, $selectedIncludeCols, true) ? 'checked' : '' }}
            >
            {{ $header }}
          </label>
        @endforeach
      </div>
      <p class="hint" style="margin-top:0.75rem;">Selected columns will be included in each imported line item description.</p>
      <div id="includeColsError" class="error" style="display:none;"></div>
      @error('include_cols')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="card">
      <h2 class="page-title">Invoice Currency</h2>
      <p class="hint">This locks the invoice display currency for the next step.</p>
      <div style="max-width: 240px;">
        <select name="currency_code" class="form-control">
          @foreach($currencyOptions as $code => $meta)
            <option
              value="{{ $code }}"
              {{ old('currency_code', $defaultCurrencyCode) === $code ? 'selected' : '' }}
            >
              {{ $meta['label'] }}
            </option>
          @endforeach
        </select>
      </div>
      @error('currency_code')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="actions">
      <a href="{{ route('invoices.create') }}" class="btn btn-secondary btn-link">Back</a>
      <button type="submit" class="btn btn-primary">Continue</button>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  const VALIDATION_MSG = @json($validationMessages);
  function setPricingMode(mode) {
    const automaticBox = document.getElementById('automatic-box');
    const manualBox = document.getElementById('manual-box');
    const priceRadios = document.querySelectorAll('input[name="price_column"]');

    const automatic = mode === 'automatic';

    automaticBox.style.opacity = automatic ? '1' : '0.55';
    manualBox.style.opacity = automatic ? '0.55' : '1';

    priceRadios.forEach(el => el.disabled = !automatic);

    if (!automatic) {
      priceRadios.forEach(el => el.checked = false);
    }
  }

  document.querySelectorAll('input[name="price_mode"]').forEach(el => {
    el.addEventListener('change', () => setPricingMode(el.value));
  });

  const initialMode = document.querySelector('input[name="price_mode"]:checked')?.value || 'automatic';
  setPricingMode(initialMode);

  document.querySelector('form').addEventListener('submit', function (e) {
    const includeColsError = document.getElementById('includeColsError');
    if (includeColsError) {
      includeColsError.textContent = '';
      includeColsError.style.display = 'none';
    }

    const checked = document.querySelectorAll('input[name="include_cols[]"]:checked');
    if (checked.length === 0) {
      e.preventDefault();
      if (includeColsError) {
        includeColsError.textContent = VALIDATION_MSG.include_cols_min;
        includeColsError.style.display = 'block';
      }
      return;
    }
    if (checked.length > 15) {
      e.preventDefault();
      if (includeColsError) {
        includeColsError.textContent = VALIDATION_MSG.include_cols_max;
        includeColsError.style.display = 'block';
      }
    }
  });
</script>
@endpush
