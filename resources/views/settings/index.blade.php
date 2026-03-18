@extends('layouts.app')

@php
  $activeMenu = 'settings';
  $activeTab = 'basic';
@endphp

@section('title', 'Settings - Basic')

@section('content')
  <div class="page-header">
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Update core configuration for your company, invoicing, and email.</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
    </div>
  @endif

  <form method="POST" action="{{ route('settings.update') }}">
    @csrf

    <div class="settings-grid">
      <div class="settings-card">
        <h3>Company</h3>
        <div class="form-row">
          <label for="company_name">Company Name</label>
          <input id="company_name" type="text" name="settings[company_name]" value="{{ setting('company_name') }}">
        </div>
        <div class="form-row">
          <label for="company_email">Company Email</label>
          <input id="company_email" type="email" name="settings[company_email]" value="{{ setting('company_email') }}">
        </div>
        <div class="form-row">
          <label for="company_phone">Company Phone</label>
          <input id="company_phone" type="text" name="settings[company_phone]" value="{{ setting('company_phone') }}">
        </div>
        <div class="form-row">
          <label for="company_address">Company Address</label>
          <textarea id="company_address" rows="3" name="settings[company_address]">{{ setting('company_address') }}</textarea>
        </div>
        <div class="form-row">
          <label for="company_logo">Company Logo URL</label>
          <input id="company_logo" type="text" name="settings[company_logo]" value="{{ setting('company_logo', 'homepage/images/docubills-logo.png') }}">
          <small>Store a URL or a storage path for the logo.</small>
        </div>
      </div>

      <div class="settings-card">
        <h3>Invoice</h3>
        <div class="form-row">
          <label for="invoice_prefix">Invoice Prefix</label>
          <input id="invoice_prefix" type="text" name="settings[invoice_prefix]" value="{{ setting('invoice_prefix', 'INV') }}">
        </div>
        <div class="form-row">
          <label for="currency_code">Currency Code</label>
          <input id="currency_code" type="text" name="settings[currency_code]" value="{{ setting('currency_code', 'USD') }}">
        </div>
        <div class="form-row">
          <label for="currency_symbol">Currency Symbol</label>
          <input id="currency_symbol" type="text" name="settings[currency_symbol]" value="{{ setting('currency_symbol', '$') }}">
        </div>
        <div class="form-row">
          <label for="invoice_footer">Invoice Footer</label>
          <textarea id="invoice_footer" rows="3" name="settings[invoice_footer]">{{ setting('invoice_footer') }}</textarea>
        </div>
      </div>

      <div class="settings-card">
        <h3>Email (SMTP)</h3>
        <div class="form-row">
          <label for="smtp_host">SMTP Host</label>
          <input id="smtp_host" type="text" name="settings[smtp_host]" value="{{ setting('smtp_host') }}">
        </div>
        <div class="form-row">
          <label for="smtp_port">SMTP Port</label>
          <input id="smtp_port" type="number" name="settings[smtp_port]" value="{{ setting('smtp_port', '587') }}">
        </div>
        <div class="form-row">
          <label for="smtp_username">SMTP Username</label>
          <input id="smtp_username" type="text" name="settings[smtp_username]" value="{{ setting('smtp_username') }}">
        </div>
        <div class="form-row">
          <label for="smtp_password">SMTP Password</label>
          <input id="smtp_password" type="password" name="settings[smtp_password]" value="{{ setting('smtp_password') }}">
        </div>
        <div class="form-row">
          <label for="email_from_name">From Name</label>
          <input id="email_from_name" type="text" name="settings[email_from_name]" value="{{ setting('email_from_name', 'DocuBills') }}">
        </div>
        <div class="form-row">
          <label for="email_from_address">From Address</label>
          <input id="email_from_address" type="email" name="settings[email_from_address]" value="{{ setting('email_from_address') }}">
        </div>
      </div>

      <div class="settings-card">
        <h3>Security</h3>
        <div class="form-row">
          <label for="cron_secret">Cron Secret</label>
          <input id="cron_secret" type="text" name="settings[cron_secret]" value="{{ setting('cron_secret') }}">
        </div>
        <div class="form-row">
          <label for="session_timeout">Session Timeout (minutes)</label>
          <input id="session_timeout" type="number" name="settings[session_timeout]" value="{{ setting('session_timeout', '120') }}">
        </div>
      </div>

      <div class="settings-card">
        <h3>Reminders</h3>
        <div class="form-row">
          <label for="reminder_before_due">Days Before Due</label>
          <input id="reminder_before_due" type="number" name="settings[reminder_before_due]" value="{{ setting('reminder_before_due', '0') }}">
        </div>
        <div class="form-row">
          <label for="reminder_on_due">On Due Date</label>
          <select id="reminder_on_due" name="settings[reminder_on_due]">
            <option value="0" {{ setting('reminder_on_due', '0') === '0' ? 'selected' : '' }}>Disabled</option>
            <option value="1" {{ setting('reminder_on_due', '0') === '1' ? 'selected' : '' }}>Enabled</option>
          </select>
        </div>
        <div class="form-row">
          <label for="reminder_after_3">3 Days After Due</label>
          <select id="reminder_after_3" name="settings[reminder_after_3]">
            <option value="0" {{ setting('reminder_after_3', '0') === '0' ? 'selected' : '' }}>Disabled</option>
            <option value="1" {{ setting('reminder_after_3', '0') === '1' ? 'selected' : '' }}>Enabled</option>
          </select>
        </div>
        <div class="form-row">
          <label for="reminder_after_7">7 Days After Due</label>
          <select id="reminder_after_7" name="settings[reminder_after_7]">
            <option value="0" {{ setting('reminder_after_7', '0') === '0' ? 'selected' : '' }}>Disabled</option>
            <option value="1" {{ setting('reminder_after_7', '0') === '1' ? 'selected' : '' }}>Enabled</option>
          </select>
        </div>
        <div class="form-row">
          <label for="reminder_after_14">14 Days After Due</label>
          <select id="reminder_after_14" name="settings[reminder_after_14]">
            <option value="0" {{ setting('reminder_after_14', '0') === '0' ? 'selected' : '' }}>Disabled</option>
            <option value="1" {{ setting('reminder_after_14', '0') === '1' ? 'selected' : '' }}>Enabled</option>
          </select>
        </div>
        <div class="form-row">
          <label for="reminder_after_21">21 Days After Due</label>
          <select id="reminder_after_21" name="settings[reminder_after_21]">
            <option value="0" {{ setting('reminder_after_21', '0') === '0' ? 'selected' : '' }}>Disabled</option>
            <option value="1" {{ setting('reminder_after_21', '0') === '1' ? 'selected' : '' }}>Enabled</option>
          </select>
        </div>
      </div>
    </div>

    <div class="settings-actions">
      <button type="submit" class="btn btn-primary">Save Settings</button>
    </div>
  </form>
@endsection
