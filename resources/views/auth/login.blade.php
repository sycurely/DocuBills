@extends('layouts.auth')

@section('title', 'Login - DocuBills')

@section('content')
  <h2 class="auth-title">Login</h2>

  <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>
    </div>

    <button type="submit" class="btn">Login</button>
  </form>
@endsection
