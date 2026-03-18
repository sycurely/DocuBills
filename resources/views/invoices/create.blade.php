@extends('layouts.app')

@section('title', 'Create Invoice')

@push('styles')
<style>
  .form-section {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    background: var(--card-bg);
    box-shadow: var(--shadow);
  }
  .form-section-title {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 1rem;
    display: grid;
    grid-template-columns: auto 1fr;
    align-items: center;
    gap: 10px;
    line-height: 1.2;
  }
  .form-section-title .material-icons-outlined {
    width: 1.2em;
    text-align: center;
    font-size: 1.35rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
  }
  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.2rem;
  }
  .form-row {
    margin-bottom: 1rem;
  }
  .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
  }
  .form-control {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--card-bg);
    color: var(--dark);
    font-size: 1rem;
    transition: var(--transition);
  }
  .form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
  }
  .upload-container {
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    text-align: center;
    margin-top: 1rem;
    transition: var(--transition);
    cursor: pointer;
  }
  .upload-container:hover {
    border-color: var(--primary);
    background: rgba(67, 97, 238, 0.05);
  }
  .upload-icon {
    font-size: 2.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
  }
  .upload-text {
    color: var(--gray);
    margin-bottom: 1rem;
  }
  .upload-hint {
    font-size: 0.9rem;
    color: var(--gray);
  }
  .source-picker {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    margin-top: 0.5rem;
  }
  .source-option {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: color-mix(in srgb, var(--card-bg) 98%, #d5dbe3 2%);
    padding: 0.75rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }
  .source-option > span {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.15rem;
    min-width: 0;
  }
  .source-option input[type="radio"] {
    width: auto;
    margin: 0;
    accent-color: var(--primary);
    cursor: pointer;
  }
  .source-option.is-selected {
    border-color: var(--primary);
    background: rgba(67, 97, 238, 0.08);
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.12);
  }
  .source-option:hover {
    border-color: var(--primary-light);
  }
  .source-option .source-title {
    font-weight: 600;
    color: var(--dark);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }
  .source-option .source-desc {
    display: block;
    color: var(--gray);
    font-size: 0.85rem;
    line-height: 1.3;
  }
  .source-panel {
    margin-top: 1rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: color-mix(in srgb, var(--card-bg) 97%, #d5dbe3 3%);
    padding: 1rem;
  }
  .source-panel.is-hidden {
    display: none;
  }
  .source-error {
    margin-top: 0.65rem;
  }
  .file-name {
    margin-top: 10px;
    font-size: 0.9rem;
    color: var(--primary);
    display: none;
  }
  .actions-bar {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
  }
  .required::after {
    content: ' *';
    color: var(--danger);
  }
  .error-text {
    margin-top: 0.4rem;
    font-size: 0.85rem;
    color: var(--danger);
  }
  .form-control.is-invalid {
    border-color: var(--danger);
    box-shadow: 0 0 0 2px rgba(247, 37, 133, 0.12);
  }
  .position-relative {
    position: relative;
  }
  .autocomplete-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    box-shadow: var(--shadow);
    max-height: 220px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
  }
  .autocomplete-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
  }
  .autocomplete-item:hover {
    background: rgba(67, 97, 238, 0.08);
  }
  .autocomplete-company {
    font-weight: 500;
    color: var(--dark);
  }
  .autocomplete-rep {
    font-size: 0.85rem;
    color: var(--gray);
  }
  @media (max-width: 900px) {
    .source-picker {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
  @php
    $validationMessages = \App\Services\InvoiceValidationContract::uiMessages();
  @endphp
  <div class="page-header">
    <h1 class="page-title">Create New Invoice</h1>
  </div>

  <form id="invoiceForm" method="POST" action="{{ route('invoices.import-source') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-section">
      <h2 class="form-section-title"><span class="material-icons-outlined">apartment</span> Bill To Information</h2>
      <div class="form-grid">
        <div class="form-row position-relative">
          <label for="companyName" class="form-label required">Company Name</label>
          <input type="text" id="companyName" name="bill_to[Company Name]" class="form-control @error('bill_to.Company Name') is-invalid @enderror" placeholder="Enter company name" required autocomplete="off" value="{{ old('bill_to.Company Name') }}">
          <div id="clientSuggestions" class="autocomplete-list"></div>
          @error('bill_to.Company Name')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-row">
          <label for="contactName" class="form-label">Contact Name</label>
          <input type="text" id="contactName" name="bill_to[Contact Name]" class="form-control @error('bill_to.Contact Name') is-invalid @enderror" placeholder="Contact person's name" value="{{ old('bill_to.Contact Name') }}">
          @error('bill_to.Contact Name')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-row">
          <label for="billAddress" class="form-label">Address</label>
          <input type="text" id="billAddress" name="bill_to[Address]" class="form-control @error('bill_to.Address') is-invalid @enderror" placeholder="Full address" value="{{ old('bill_to.Address') }}">
          @error('bill_to.Address')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-row">
          <label for="billPhone" class="form-label">Phone</label>
          <input type="text" id="billPhone" name="bill_to[Phone]" class="form-control @error('bill_to.Phone') is-invalid @enderror" placeholder="Phone number" value="{{ old('bill_to.Phone') }}">
          @error('bill_to.Phone')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-row">
          <label for="billEmail" class="form-label required">Email</label>
          <input type="email" id="billEmail" name="bill_to[Email]" class="form-control @error('bill_to.Email') is-invalid @enderror" placeholder="Email address" required value="{{ old('bill_to.Email') }}">
          @error('bill_to.Email')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="form-section">
      <h2 class="form-section-title"><span class="material-icons-outlined">storage</span> Invoice Data Source</h2>
      <div class="form-row">
        <label class="form-label">Choose Invoice Source</label>
        <div class="source-picker" id="sourcePicker">
          <label class="source-option" data-source-card data-source="google">
            <input type="radio" name="invoice_source" value="google" {{ old('invoice_source', session('import_error') ? 'upload' : 'google') === 'google' ? 'checked' : '' }}>
            <span>
              <span class="source-title"><span class="material-icons-outlined">link</span> Google Sheet URL</span>
              <span class="source-desc">Use a shared Google Sheet as invoice data source.</span>
            </span>
          </label>
          <label class="source-option" data-source-card data-source="upload">
            <input type="radio" name="invoice_source" value="upload" {{ old('invoice_source', session('import_error') ? 'upload' : 'google') === 'upload' ? 'checked' : '' }}>
            <span>
              <span class="source-title"><span class="material-icons-outlined">upload_file</span> Upload Excel File</span>
              <span class="source-desc">Upload local `.xls`, `.xlsx`, or `.csv` data.</span>
            </span>
          </label>
        </div>
        @error('invoice_source')
          <div class="error-text">{{ $message }}</div>
        @enderror
        <div id="sourceError" class="error-text source-error is-hidden"></div>
      </div>

      <div id="google-section" class="source-panel">
        <div class="form-row">
          <label for="google_sheet_url" class="form-label required">Google Sheet URL</label>
          <input type="url" id="google_sheet_url" name="google_sheet_url" class="form-control @error('google_sheet_url') is-invalid @enderror" placeholder="https://docs.google.com/spreadsheets/..." value="{{ old('google_sheet_url') }}">
          <p class="upload-hint">Make sure the sheet is shared as "Anyone with the link can view".</p>
          @error('google_sheet_url')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div id="upload-section" class="source-panel is-hidden">
        @if(session('import_error'))
          <div class="alert alert-danger">
            <span class="material-icons-outlined">error</span>
            {{ session('import_error') }}
          </div>
        @endif
        <div class="form-row">
          <label class="form-label required">Upload Excel File</label>
          <div class="upload-container" id="uploadArea">
            <div class="upload-icon"><span class="material-icons-outlined">table_view</span></div>
            <p class="upload-text">Drag and drop your Excel file here or click to browse</p>
            <p class="upload-hint">
              @if($zipAvailable)
                Supports .xls, .xlsx, .csv
              @else
                XLSX requires PHP Zip. Please upload .csv
              @endif
            </p>
            <input type="file" id="excel_file" name="file" accept="{{ $zipAvailable ? '.xls,.xlsx,.csv' : '.csv' }}" class="is-hidden">
          </div>
          <div id="fileName" class="file-name"></div>
          <div id="fileError" class="error-text is-hidden"></div>
          @error('file')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="actions-bar">
      <button type="submit" class="btn btn-primary"><span class="material-icons-outlined">receipt_long</span> Create Invoice</button>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  const VALIDATION_MSG = @json($validationMessages);
  const uploadArea = document.getElementById('uploadArea');
  const fileInput = document.getElementById('excel_file');
  const fileNameDisplay = document.getElementById('fileName');
  const fileErrorDisplay = document.getElementById('fileError');
  const sourceErrorDisplay = document.getElementById('sourceError');
  const zipAvailable = @json($zipAvailable);

  function clearFileMessages() {
    if (fileErrorDisplay) {
      fileErrorDisplay.textContent = '';
      fileErrorDisplay.classList.add('is-hidden');
    }
  }

  function showFileError(msg) {
    if (fileErrorDisplay) {
      fileErrorDisplay.textContent = msg;
      fileErrorDisplay.classList.remove('is-hidden');
    } else {
      alert(msg);
    }
  }

  function clearSourceError() {
    if (!sourceErrorDisplay) return;
    sourceErrorDisplay.textContent = '';
    sourceErrorDisplay.classList.add('is-hidden');
  }

  function showSourceError(msg) {
    if (!sourceErrorDisplay) return;
    sourceErrorDisplay.textContent = msg;
    sourceErrorDisplay.classList.remove('is-hidden');
  }

  function getGoogleUrlError(value) {
    const raw = String(value || '').trim();
    if (!raw) {
      return VALIDATION_MSG.google_url_required;
    }

    let parsed;
    try {
      parsed = new URL(raw);
    } catch (_) {
      return VALIDATION_MSG.google_url_format;
    }

    if (parsed.protocol !== 'https:') {
      return VALIDATION_MSG.google_url_https;
    }

    if (!parsed.hostname.includes('docs.google.com')) {
      return VALIDATION_MSG.google_url_format;
    }

    if (!parsed.pathname.includes('/spreadsheets/')) {
      return VALIDATION_MSG.google_url_format;
    }

    return '';
  }

  function isValidExcelFile(file) {
    if (!file) return false;
    const allowedExtensions = zipAvailable ? ['xls', 'xlsx', 'csv'] : ['csv'];
    const name = file.name || '';
    const ext = name.split('.').pop().toLowerCase();
    return allowedExtensions.includes(ext);
  }

  function handleFileSelection(file) {
    clearFileMessages();
    if (!file) {
      fileNameDisplay.style.display = 'none';
      return;
    }

    if (!isValidExcelFile(file)) {
      fileInput.value = '';
      fileNameDisplay.style.display = 'none';
      showFileError(zipAvailable ? VALIDATION_MSG.allowed_upload_types : VALIDATION_MSG.upload_csv_required);
      return;
    }

    fileNameDisplay.textContent = 'Selected file: ' + file.name;
    fileNameDisplay.style.display = 'block';
  }

  if (uploadArea && fileInput) {
    uploadArea.addEventListener('click', () => {
      clearFileMessages();
      fileInput.click();
    });

    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = '#4361ee';
      uploadArea.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.borderColor = 'var(--border)';
      uploadArea.style.backgroundColor = '';
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = 'var(--border)';
      uploadArea.style.backgroundColor = '';

      const file = e.dataTransfer.files && e.dataTransfer.files[0];
      if (!file) return;
      if (!isValidExcelFile(file)) {
        showFileError(zipAvailable ? VALIDATION_MSG.allowed_upload_types : VALIDATION_MSG.upload_csv_required);
        return;
      }

      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      fileInput.files = dataTransfer.files;
      handleFileSelection(file);
    });

    fileInput.addEventListener('change', function () {
      const file = fileInput.files && fileInput.files[0];
      handleFileSelection(file);
    });
  }

  function setSourceVisibility(source) {
    const googleSection = document.getElementById('google-section');
    const uploadSection = document.getElementById('upload-section');
    const googleInput = document.getElementById('google_sheet_url');

    googleSection.classList.toggle('is-hidden', source !== 'google');
    uploadSection.classList.toggle('is-hidden', source !== 'upload');

    document.querySelectorAll('[data-source-card]').forEach((card) => {
      card.classList.toggle('is-selected', card.getAttribute('data-source') === source);
    });

    if (source === 'google') {
      googleInput.required = true;
      if (fileInput) fileInput.required = false;
    } else {
      googleInput.required = false;
      if (fileInput) fileInput.required = true;
    }
    clearSourceError();
  }

  document.querySelectorAll('input[name="invoice_source"]').forEach(radio => {
    radio.addEventListener('change', () => setSourceVisibility(radio.value));
  });

  const selectedSourceRadio = document.querySelector('input[name="invoice_source"]:checked');
  const initialSource = selectedSourceRadio ? selectedSourceRadio.value : 'google';
  setSourceVisibility(initialSource);

  document.getElementById('invoiceForm').addEventListener('submit', function (e) {
    const source = document.querySelector('input[name="invoice_source"]:checked').value;
    clearSourceError();
    clearFileMessages();

    if (source === 'google') {
      const googleInput = document.getElementById('google_sheet_url');
      const err = getGoogleUrlError(googleInput ? googleInput.value : '');
      if (err) {
        e.preventDefault();
        showSourceError(err);
        if (googleInput) googleInput.focus();
        return;
      }
    } else if (source === 'upload') {
      const file = fileInput.files && fileInput.files[0];
      if (!file) {
        e.preventDefault();
        showFileError(zipAvailable ? VALIDATION_MSG.upload_file_required : VALIDATION_MSG.upload_csv_required);
        return;
      }
      if (!isValidExcelFile(file)) {
        e.preventDefault();
        showFileError(zipAvailable ? VALIDATION_MSG.allowed_upload_types : VALIDATION_MSG.upload_csv_required);
      }
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    const companyInput = document.getElementById('companyName');
    const repInput = document.getElementById('contactName');
    const addressInput = document.getElementById('billAddress');
    const phoneInput = document.getElementById('billPhone');
    const emailInput = document.getElementById('billEmail');
    const suggestionsBox = document.getElementById('clientSuggestions');
    let lastQuery = '';

    function clearSuggestions() {
      suggestionsBox.innerHTML = '';
      suggestionsBox.style.display = 'none';
    }

    document.addEventListener('click', function (e) {
      if (!suggestionsBox.contains(e.target) && e.target !== companyInput) {
        clearSuggestions();
      }
    });

    companyInput.addEventListener('input', function () {
      const q = this.value.trim();
      if (q.length < 1) {
        clearSuggestions();
        return;
      }
      if (q === lastQuery) return;
      lastQuery = q;

      fetch('{{ route("api.clients.search") }}?q=' + encodeURIComponent(q))
        .then(response => response.json())
        .then(data => {
          suggestionsBox.innerHTML = '';
          if (!Array.isArray(data) || data.length === 0) {
            clearSuggestions();
            return;
          }

          data.forEach(client => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.dataset.company_name = client.company_name || '';
            item.dataset.representative = client.representative || '';
            item.dataset.address = client.address || '';
            item.dataset.phone = client.phone || '';
            item.dataset.email = client.email || '';

            const left = document.createElement('div');
            left.className = 'autocomplete-company';
            left.textContent = client.company_name || '';

            const right = document.createElement('div');
            right.className = 'autocomplete-rep';
            right.textContent = client.representative ? ('Contact: ' + client.representative) : '';

            item.appendChild(left);
            item.appendChild(right);

            item.addEventListener('click', function () {
              companyInput.value = this.dataset.company_name || '';
              if (repInput) repInput.value = this.dataset.representative || '';
              if (addressInput) addressInput.value = this.dataset.address || '';
              if (phoneInput) phoneInput.value = this.dataset.phone || '';
              if (emailInput) emailInput.value = this.dataset.email || '';
              clearSuggestions();
            });

            suggestionsBox.appendChild(item);
          });
          suggestionsBox.style.display = 'block';
        })
        .catch(() => clearSuggestions());
    });

    companyInput.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') clearSuggestions();
    });
  });
</script>
@endpush
