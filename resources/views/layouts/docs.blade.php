@php
    $brand = config('aggregpay.brand');
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Documentation API') — {{ $brand }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="{{ asset('theme/portal-docs.css') }}?v=9" rel="stylesheet">
    @stack('styles')
</head>
<body class="docs-body">
    @yield('content')
    <script src="{{ asset('theme/portal-docs.js') }}?v=9" defer></script>
    @stack('scripts')
</body>
</html>
