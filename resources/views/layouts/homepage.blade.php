<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DocuBills | Generate Custom Invoices in 3 Simple Steps')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,700,1,0" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('homepage/css/landing.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')

    <script src="{{ asset('homepage/js/landing.js') }}"></script>
    @stack('scripts')
</body>
</html>
