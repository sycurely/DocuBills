<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Authentication - DocuBills')</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    // Add dark-mode class early before CSS is evaluated
    if (localStorage.getItem('darkMode') === '1') {
      document.documentElement.classList.add('dark-mode');
    }
  </script>
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --transition: all 0.3s ease;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    html.dark-mode {
      --primary: #5a7dff;
      --primary-light: #6e8fff;
      --secondary: #4d45d1;
      --success: #5ed5f9;
      --danger: #ff3d96;
      --dark: #e9ecef;
      --light: #212529;
      --gray: #adb5bd;
      --border: #495057;
      --card-bg: #2d3748;
      --body-bg: #1a202c;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.3);
    }

    body {
      background: var(--body-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      transition: var(--transition);
    }

    .auth-card {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 400px;
      transition: var(--transition);
    }

    .auth-title {
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
      color: var(--primary);
      text-align: center;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    label {
      font-weight: 600;
      display: block;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    input[type="text"],
    input[type="password"],
    input[type="email"] {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
      transition: var(--transition);
      box-sizing: border-box;
      background: var(--card-bg);
      color: var(--dark);
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="email"]:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
    }

    .btn {
      display: inline-block;
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.75rem;
      width: 100%;
      border-radius: var(--radius);
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      font-size: 1rem;
    }

    .btn:hover {
      background: var(--secondary);
      transform: translateY(-2px);
      box-shadow: var(--shadow-hover);
    }

    .error-message {
      background: rgba(247, 37, 133, 0.1);
      border: 1px solid var(--danger);
      color: var(--danger);
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: var(--radius);
      text-align: center;
    }

    .success-message {
      background: rgba(76, 201, 240, 0.1);
      border: 1px solid var(--success);
      color: var(--success);
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: var(--radius);
      text-align: center;
    }
  </style>
  @stack('styles')
</head>
<body>
  <div class="auth-card">
    @if(session('success'))
      <div class="success-message">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="error-message">
        @foreach ($errors->all() as $error)
          <i class="fas fa-exclamation-circle"></i>
          {{ $error }}
        @endforeach
      </div>
    @endif

    @yield('content')
  </div>

  @stack('scripts')
</body>
</html>
