@extends('layouts.app')

@section('title', 'Email Template Details')

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

    .info-item {
      margin-bottom: 1.5rem;
    }

    .info-item label {
      display: block;
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
      font-weight: 600;
    }

    .info-item strong {
      display: block;
      font-size: 1.125rem;
      color: var(--dark);
    }

    .body-preview {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem;
      background: white;
      min-height: 200px;
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
      display: inline-block;
      background: var(--primary);
      color: white;
    }
  
</style>
@endpush

@section('content')

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Email Template Details</h1>
      <div>
        @if(has_permission('edit_email_template'))
          <a href="{{ route('email-templates.edit', $emailTemplate) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
          </a>
        @endif
        <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <div class="card">
      <div class="info-item">
        <label>Template Name</label>
        <strong>{{ $emailTemplate->template_name }}</strong>
      </div>

      <div class="info-item">
        <label>Subject</label>
        <strong>{{ $emailTemplate->subject }}</strong>
      </div>

      @if($emailTemplate->cc_emails)
        <div class="info-item">
          <label>CC Emails</label>
          <strong>{{ $emailTemplate->cc_emails }}</strong>
        </div>
      @endif

      @if($emailTemplate->bcc_emails)
        <div class="info-item">
          <label>BCC Emails</label>
          <strong>{{ $emailTemplate->bcc_emails }}</strong>
        </div>
      @endif

      <div class="info-item">
        <label>Email Body Preview</label>
        <div class="body-preview">
          {!! $emailTemplate->getRenderableBody() !!}
        </div>
      </div>

      <div class="info-item">
        <label>Created By</label>
        <strong>{{ $emailTemplate->creator->username ?? 'N/A' }}</strong>
      </div>

      <div class="info-item">
        <label>Created At</label>
        <strong>{{ $emailTemplate->created_at->format('Y-m-d H:i') }}</strong>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
</script>
@endpush
