<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Stokku') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('img/favicon.png') }}">

    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet">
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    <!-- Popper.js -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>

    <!-- Argon CSS -->
    <link href="{{ asset('css/argon-dashboard-tailwind.css?v=1.0.1') }}" rel="stylesheet">
    @stack('head')
</head>
<body class="bg-gray-50 text-slate-700">
    <div class = 'flex'>
        @include ('components.sidenav')
        <div class="flex-1">
            @yield('content')
        </div>
    </div>
    <!-- JS if needed -->
    <script src="{{ asset('js/argon-dashboard-tailwind.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
