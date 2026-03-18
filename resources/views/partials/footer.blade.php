@php
  $appName = setting('company_name', 'DocuBills');
  $currentYear = date('Y');
@endphp

<footer class="app-footer">
  <div class="footer-content">
    <div class="footer-text">
      <p>&copy; {{ $currentYear }} {{ $appName }}. All rights reserved.</p>
    </div>
    <div class="footer-links">
      <a href="{{ route('dashboard') }}">Dashboard</a>
      <span class="separator">|</span>
      <a href="{{ route('settings.index') }}">Settings</a>
    </div>
  </div>
</footer>
