@extends('layouts.app')

@php
  $activeMenu = 'clients';
@endphp

@section('title', $client->company_name . ' – Client')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>.material-icons { font-size: var(--icon-size); vertical-align: middle; }</style>
@endpush

@section('content')
  <div class="page-header">
    <h1 class="page-title">{{ $client->company_name }}</h1>
    <div style="display: flex; gap: 0.75rem;">
      <a href="{{ route('clients.index') }}" class="btn btn-secondary">
        <span class="material-icons">arrow_back</span> Back to list
      </a>
      @if($canEditClient ?? can_edit_client())
      <a href="{{ route('clients.index') }}?edit={{ $client->id }}" class="btn btn-primary">
        <span class="material-icons">edit</span> Edit
      </a>
      @endif
    </div>
  </div>

  <div class="card">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem;">Client details</h2>
    <dl style="display: grid; gap: 0.75rem;">
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Company</dt><dd>{{ $client->company_name }}</dd></div>
      @if($client->representative)
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Representative</dt><dd>{{ $client->representative }}</dd></div>
      @endif
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Phone</dt><dd>{{ $client->phone }}</dd></div>
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Email</dt><dd><a href="mailto:{{ $client->email }}">{{ $client->email }}</a></dd></div>
      @if($client->address)
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Address</dt><dd>{{ $client->address }}</dd></div>
      @endif
      @if($client->gst_hst)
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">GST/HST</dt><dd>{{ $client->gst_hst }}</dd></div>
      @endif
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">User</dt><dd>{{ $client->creator ? ($client->creator->full_name ?? $client->creator->username) : '-' }}</dd></div>
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Invoices</dt><dd>Total: {{ $client->invoices_count }}, Paid: {{ $client->paid_invoices }}, Unpaid: {{ $client->unpaid_invoices }}</dd></div>
      @if($client->notes)
      <div><dt style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Notes</dt><dd>{{ $client->notes }}</dd></div>
      @endif
    </dl>
  </div>
@endsection
