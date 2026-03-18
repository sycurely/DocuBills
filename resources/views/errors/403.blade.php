<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Forbidden</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .error-container {
      text-align: center;
      padding: 2rem;
    }

    .error-code {
      font-size: 8rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .error-title {
      font-size: 2rem;
      margin-bottom: 1rem;
    }

    .error-message {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      opacity: 0.9;
    }

    .btn {
      display: inline-block;
      padding: 0.75rem 2rem;
      background: white;
      color: #667eea;
      text-decoration: none;
      border-radius: 50px;
      font-weight: 600;
      transition: transform 0.3s ease;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-code">403</div>
    <h1 class="error-title">Access Forbidden</h1>
    <p class="error-message">You don't have permission to access this resource.</p>
    <p style="margin-bottom: 1.5rem; font-size: 0.95rem; opacity: 0.9;">Use one of the links below to leave this page:</p>
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center;">
      @auth
      <a href="{{ route('clients.index') }}" class="btn">
        <i class="fas fa-users"></i> Clients
      </a>
      <a href="{{ route('dashboard') }}" class="btn">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
      @endauth
      <a href="{{ url('/') }}" class="btn">
        <i class="fas fa-home"></i> Home
      </a>
      @guest
      <a href="{{ route('login') }}" class="btn">
        <i class="fas fa-sign-in-alt"></i> Log in
      </a>
      @endguest
    </div>
  </div>
</body>
</html>
