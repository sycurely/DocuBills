@extends('layouts.app')

@section('title', 'Create Email Template')

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
      width: 100%;
      max-width: 100%;
      margin: 0;
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
      min-height: 220px;
      resize: vertical;
      font-family: monospace;
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

    .help-text {
      font-size: 0.875rem;
      color: #6c757d;
      margin-top: 0.25rem;
    }

    .placeholders {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: var(--radius);
      margin-bottom: 1.5rem;
    }

    .placeholders h4 {
      margin-top: 0;
      color: var(--primary);
    }

    .placeholders code {
      background: white;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.875rem;
    }

    .editor-shell {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      background: #fff;
      width: 100%;
    }

    #emailEditor {
      width: 100%;
      height: 640px;
      min-height: 420px;
    }

    .alert-danger {
      padding: 0.75rem 1rem;
      border-radius: var(--radius);
      background: rgba(247, 37, 133, 0.1);
      border: 1px solid #f72585;
      color: #b51762;
      margin-bottom: 1rem;
    }

</style>
@endpush

@section('content')

  <div class="container">
    <h1 class="page-title">Create Email Template</h1>

    <div class="card">
      @if ($errors->any())
        <div class="alert-danger">
          <strong>Please fix the following errors:</strong>
          <ul style="margin: 0.5rem 0 0 1rem;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="placeholders">
        <h4>Available Placeholders:</h4>
        <p>
          <code>@{{client_name}}</code> - Client name<br>
          <code>@{{invoice_number}}</code> - Invoice number<br>
          <code>@{{total_amount}}</code> - Invoice total<br>
          <code>@{{due_date}}</code> - Due date<br>
          <code>@{{company_name}}</code> - Your company name<br>
          <code>@{{payment_link}}</code> - Payment link<br>
          <code>@{{invoice_date}}</code> - Invoice date<br>
          <code>@{{reminder_type}}</code> - Reminder rule label (for reminder emails)
        </p>
      </div>

      <form method="POST" action="{{ route('email-templates.store') }}" id="emailTemplateForm">
        @csrf

        <div class="form-group">
          <label for="template_name">Template Name *</label>
          <input type="text" name="template_name" id="template_name" required value="{{ old('template_name') }}">
        </div>

        <div class="form-group">
          <label for="subject">Subject *</label>
          <input type="text" name="subject" id="subject" required value="{{ old('subject') }}" placeholder="e.g., Invoice @{{invoice_number}} from @{{company_name}}">
        </div>

        <div class="form-group">
          <label>Drag-and-Drop Email Builder</label>
          <div class="editor-shell">
            <div id="emailEditor"></div>
          </div>
          <div class="help-text" id="builderStatus">Builder loading...</div>
          <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content', old('body')) }}">
          <input type="hidden" name="design_json" id="design_json" value="{{ old('design_json') }}">
          <input type="hidden" name="body" id="body" value="{{ old('body', old('html_content')) }}">
        </div>

        <div class="form-group">
          <label for="cc_emails">CC Emails</label>
          <input type="text" name="cc_emails" id="cc_emails" value="{{ old('cc_emails') }}" placeholder="email1@example.com, email2@example.com">
          <div class="help-text">Comma-separated list of email addresses to CC.</div>
        </div>

        <div class="form-group">
          <label for="bcc_emails">BCC Emails</label>
          <input type="text" name="bcc_emails" id="bcc_emails" value="{{ old('bcc_emails') }}" placeholder="email1@example.com, email2@example.com">
          <div class="help-text">Comma-separated list of email addresses to BCC.</div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
          <button type="submit" class="btn btn-primary" id="saveTemplateBtn">
            <i class="fas fa-save"></i> Save Template
          </button>
          <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script src="https://editor.unlayer.com/embed.js"></script>
<script>
(function () {
  const form = document.getElementById('emailTemplateForm');
  const bodyField = document.getElementById('body');
  const htmlContentField = document.getElementById('html_content');
  const designJsonField = document.getElementById('design_json');
  const statusEl = document.getElementById('builderStatus');

  let editor = null;
  let editorReady = false;
  let exporting = false;

  function setStatus(text) {
    if (statusEl) {
      statusEl.textContent = text;
    }
  }

  function loadInitialDesign() {
    const rawDesign = (designJsonField?.value || '').trim();
    if (!rawDesign || !editor) {
      return;
    }

    try {
      editor.loadDesign(JSON.parse(rawDesign));
      setStatus('Builder ready.');
    } catch (error) {
      console.warn('Invalid stored design_json.', error);
      setStatus('Builder ready (stored design was invalid).');
    }
  }

  function initializeBuilder() {
    if (typeof window.unlayer === 'undefined') {
      setStatus('Builder unavailable. Please reload and try again.');
      return;
    }

    try {
      editor = window.unlayer.createEditor({
        id: 'emailEditor',
        displayMode: 'email',
        appearance: {
          theme: 'light',
          panels: {
            tools: { dock: 'right' }
          }
        },
      });

      editor.addEventListener('editor:ready', function () {
        editorReady = true;
        loadInitialDesign();
        if (!designJsonField.value.trim()) {
          setStatus('Builder ready.');
        }
      });
    } catch (error) {
      console.error('Failed to initialize Unlayer builder', error);
      editor = null;
      setStatus('Builder initialization failed.');
    }
  }

  if (form) {
    form.addEventListener('submit', function (event) {
      if (exporting) {
        event.preventDefault();
        return;
      }

      if (!editor || !editorReady) {
        event.preventDefault();
        setStatus('Builder is not ready yet. Please wait.');
        return;
      }

      event.preventDefault();
      exporting = true;
      setStatus('Exporting builder HTML...');

      editor.exportHtml(function (data) {
        try {
          const html = (data && data.html) ? data.html : '';
          const design = (data && data.design) ? JSON.stringify(data.design) : '';

          if (html.trim() !== '') {
            bodyField.value = html;
            htmlContentField.value = html;
          } else {
            exporting = false;
            setStatus('Could not export builder HTML. Please try again.');
            return;
          }

          designJsonField.value = design;
        } catch (error) {
          console.error('Builder export failed.', error);
          exporting = false;
          setStatus('Builder export failed. Please try again.');
          return;
        }

        exporting = false;
        form.submit();
      });
    });
  }
  initializeBuilder();
})();
</script>
@endpush
