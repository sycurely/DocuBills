@extends('layouts.app')

@php
  $activeMenu = 'settings';
  $activeTab = 'users';
  $isOwnProfile = auth()->id() === $user->id;
  $displayName = $user->full_name ?: $user->username;
  $defaultAvatar = asset('uploads/avatars/default.png');
  $avatarSrc = $defaultAvatar;
  if ($user->avatar) {
    if (preg_match('#^https?://#i', $user->avatar)) {
      $avatarSrc = $user->avatar;
    } else {
      $avatarSrc = asset($user->avatar);
    }
  }
@endphp

@section('title', $isOwnProfile ? 'My Profile' : 'User Profile')

@push('styles')
<style>
  .profile-page {
    max-width: 980px;
    margin: 0 auto;
  }

  .profile-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
  }

  .profile-top {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--border);
  }

  .profile-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(67, 97, 238, 0.35);
    background: #fff;
  }

  .profile-name {
    margin: 0;
    font-size: 1.35rem;
    color: var(--dark);
  }

  .profile-username {
    margin: 0.25rem 0 0;
    color: var(--gray);
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 999px;
    padding: 0.3rem 0.65rem;
    margin-top: 0.5rem;
  }

  .status-badge.active {
    color: #0f8a61;
    background: rgba(15, 138, 97, 0.12);
  }

  .status-badge.suspended {
    color: #b42318;
    background: rgba(180, 35, 24, 0.12);
  }

  .profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }

  .profile-field {
    background: var(--body-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0.85rem 1rem;
  }

  .profile-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--gray);
    margin-bottom: 0.35rem;
  }

  .profile-value {
    margin: 0;
    color: var(--dark);
    word-break: break-word;
  }
</style>
@endpush

@section('content')
  <div class="profile-page">
    <div class="page-header">
      <h1 class="page-title">{{ $isOwnProfile ? 'My Profile' : 'User Profile' }}</h1>
      <div class="page-actions">
        @if(has_permission('manage_users_page'))
          <a href="{{ route('users.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Users
          </a>
        @endif
        @if(has_permission('edit_user'))
          <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
          </a>
        @endif
      </div>
    </div>

    <div class="profile-card">
      <div class="profile-top">
        <img src="{{ $avatarSrc }}" alt="{{ $displayName }} Avatar" class="profile-avatar" onerror="this.onerror=null; this.src='{{ $defaultAvatar }}';">
        <div>
          <h2 class="profile-name">{{ $displayName }}</h2>
          <p class="profile-username">{{ '@' . $user->username }}</p>
          <span class="status-badge {{ $user->is_suspended ? 'suspended' : 'active' }}">
            {{ $user->is_suspended ? 'Suspended' : 'Active' }}
          </span>
        </div>
      </div>

      <div class="profile-grid">
        <div class="profile-field">
          <span class="profile-label">Full Name</span>
          <p class="profile-value">{{ $user->full_name ?: '-' }}</p>
        </div>
        <div class="profile-field">
          <span class="profile-label">Username</span>
          <p class="profile-value">{{ $user->username }}</p>
        </div>
        <div class="profile-field">
          <span class="profile-label">Email</span>
          <p class="profile-value">{{ $user->email }}</p>
        </div>
        <div class="profile-field">
          <span class="profile-label">Role</span>
          <p class="profile-value">{{ $user->role ? ucwords(str_replace('_', ' ', $user->role->name)) : 'Unassigned' }}</p>
        </div>
        <div class="profile-field">
          <span class="profile-label">Created At</span>
          <p class="profile-value">{{ $user->created_at ? $user->created_at->format('M d, Y h:i A') : '-' }}</p>
        </div>
      </div>
    </div>
  </div>
@endsection
