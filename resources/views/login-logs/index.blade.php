@extends('layouts.app')

@section('title', 'Login Logs')

@push('styles')
<style>
  .logs-wrap {
    display: grid;
    gap: 1.25rem;
  }

  .logs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .logs-title {
    margin: 0;
    font-size: 1.9rem;
    color: var(--primary);
  }

  .logs-subtitle {
    margin: 0.35rem 0 0;
    color: var(--gray);
    font-size: 0.95rem;
  }

  .logs-stats {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .logs-stat {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem;
  }

  .logs-stat-label {
    color: var(--gray);
    font-size: 0.85rem;
    margin-bottom: 0.55rem;
  }

  .logs-stat-value {
    color: var(--dark);
    font-size: 1.45rem;
    font-weight: 700;
  }

  .logs-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 1rem;
  }

  .logs-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
    margin-bottom: 0.9rem;
  }

  .logs-card-title {
    margin: 0;
    font-size: 1.1rem;
    color: var(--primary);
  }

  .logs-filter-form {
    display: grid;
    gap: 0.6rem;
    grid-template-columns: minmax(220px, 1.8fr) repeat(3, minmax(130px, 1fr)) auto;
    align-items: end;
  }

  .logs-filter-group {
    display: grid;
    gap: 0.2rem;
  }

  .logs-filter-group label {
    font-size: 0.8rem;
    color: var(--gray);
  }

  .logs-input,
  .logs-select {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card-bg);
    color: var(--dark);
    padding: 0.5rem 0.65rem;
  }

  .logs-table-wrap {
    overflow-x: auto;
  }

  .logs-table {
    width: 100%;
    border-collapse: collapse;
  }

  .logs-table th,
  .logs-table td {
    text-align: left;
    border-bottom: 1px solid var(--border);
    padding: 0.72rem;
    white-space: nowrap;
  }

  .logs-table th {
    color: var(--primary);
    font-size: 0.84rem;
    background: rgba(67, 97, 238, 0.09);
  }

  .logs-status {
    display: inline-flex;
    border-radius: 999px;
    padding: 0.2rem 0.6rem;
    font-size: 0.8rem;
    font-weight: 700;
  }

  .logs-status.success {
    color: var(--success);
    background: rgba(76, 201, 240, 0.15);
  }

  .logs-status.failure {
    color: var(--danger);
    background: rgba(247, 37, 133, 0.15);
  }

  .logs-empty {
    text-align: center;
    color: var(--gray);
    padding: 1rem;
  }

  .logs-pagination {
    margin-top: 0.8rem;
  }

  .logs-meta-muted {
    color: var(--gray);
    font-size: 0.85rem;
  }

  @media (max-width: 1100px) {
    .logs-stats {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .logs-filter-form {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (max-width: 720px) {
    .logs-stats {
      grid-template-columns: 1fr;
    }

    .logs-filter-form {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="logs-wrap">
  <section class="logs-header">
    <div>
      <h1 class="logs-title">Login Logs</h1>
      <p class="logs-subtitle">Review authentication attempts and manage active user sessions.</p>
    </div>
  </section>

  <section class="logs-stats">
    <article class="logs-stat">
      <div class="logs-stat-label">Attempts Today</div>
      <div class="logs-stat-value">{{ number_format($stats['total_attempts_today']) }}</div>
    </article>
    <article class="logs-stat">
      <div class="logs-stat-label">Successful Today</div>
      <div class="logs-stat-value">{{ number_format($stats['successful_attempts_today']) }}</div>
    </article>
    <article class="logs-stat">
      <div class="logs-stat-label">Failed Today</div>
      <div class="logs-stat-value">{{ number_format($stats['failed_attempts_today']) }}</div>
    </article>
    <article class="logs-stat">
      <div class="logs-stat-label">Active Sessions</div>
      <div class="logs-stat-value">{{ number_format($stats['active_sessions']) }}</div>
    </article>
  </section>

  <section class="logs-card">
    <div class="logs-card-head">
      <h2 class="logs-card-title">Login Attempt History</h2>
    </div>

    <form method="GET" action="{{ route('login-logs.index') }}" class="logs-filter-form">
      <div class="logs-filter-group">
        <label for="search">Search</label>
        <input id="search" name="search" class="logs-input" value="{{ $filters['search'] }}" placeholder="Username or IP address">
      </div>
      <div class="logs-filter-group">
        <label for="status">Status</label>
        <select id="status" name="status" class="logs-select">
          <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
          <option value="success" {{ $filters['status'] === 'success' ? 'selected' : '' }}>Success</option>
          <option value="failure" {{ $filters['status'] === 'failure' ? 'selected' : '' }}>Failure</option>
        </select>
      </div>
      <div class="logs-filter-group">
        <label for="date_from">From</label>
        <input id="date_from" type="date" name="date_from" class="logs-input" value="{{ $filters['date_from'] }}">
      </div>
      <div class="logs-filter-group">
        <label for="date_to">To</label>
        <input id="date_to" type="date" name="date_to" class="logs-input" value="{{ $filters['date_to'] }}">
      </div>
      <div>
        <button type="submit" class="btn btn-primary">Apply</button>
      </div>
    </form>

    <div class="logs-table-wrap">
      <table class="logs-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Status</th>
            <th>IP Address</th>
            <th>User Agent</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          @forelse($loginLogs as $log)
            <tr>
              <td>
                {{ $log->username ?: ($log->user?->username ?: 'Unknown') }}
                @if($log->user?->full_name)
                  <div class="logs-meta-muted">{{ $log->user->full_name }}</div>
                @endif
              </td>
              <td>
                <span class="logs-status {{ $log->status === 'success' ? 'success' : 'failure' }}">
                  {{ ucfirst($log->status) }}
                </span>
              </td>
              <td>{{ $log->ip_address ?: '-' }}</td>
              <td title="{{ $log->user_agent }}">{{ \Illuminate\Support\Str::limit($log->user_agent, 55) ?: '-' }}</td>
              <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') ?: '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="logs-empty">No login logs found for the selected filters.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="logs-pagination">
      {{ $loginLogs->links() }}
    </div>
  </section>

  <section class="logs-card">
    <div class="logs-card-head">
      <h2 class="logs-card-title">Active Sessions</h2>
    </div>

    <div class="logs-table-wrap">
      <table class="logs-table">
        <thead>
          <tr>
            <th>User</th>
            <th>IP Address</th>
            <th>Last Activity</th>
            <th>Session</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($activeSessions as $session)
            @php
              $isOwnSession = $session->user_id === auth()->id();
              $canTerminate = $canTerminateAnySession || ($canTerminateOwnSession && $isOwnSession);
              $isCurrentSession = $session->session_id === $currentSessionId;
            @endphp
            <tr>
              <td>
                {{ $session->user?->username ?: 'Unknown' }}
                @if($session->user?->full_name)
                  <div class="logs-meta-muted">{{ $session->user->full_name }}</div>
                @endif
              </td>
              <td>{{ $session->ip_address ?: '-' }}</td>
              <td>{{ optional($session->last_activity)->format('Y-m-d H:i:s') ?: '-' }}</td>
              <td>
                {{ \Illuminate\Support\Str::limit($session->session_id, 18) }}
                @if($isCurrentSession)
                  <div class="logs-meta-muted">Current session</div>
                @endif
              </td>
              <td>
                @if($canTerminate)
                  <form method="POST" action="{{ route('login-logs.terminate-session', $session) }}" onsubmit="return confirm('Terminate this session?');">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                      {{ $isCurrentSession ? 'Terminate Current' : 'Terminate' }}
                    </button>
                  </form>
                @else
                  <span class="logs-meta-muted">No permission</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="logs-empty">No active sessions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="logs-pagination">
      {{ $activeSessions->links() }}
    </div>
  </section>
</div>
@endsection
