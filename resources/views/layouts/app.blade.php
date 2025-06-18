<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Stokku') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> {{-- Or your preferred font --}}

    {{-- Tailwind CSS & Vite --}}
    @vite('resources/css/app.css') {{-- Assuming your Tailwind CSS is compiled into resources/css/app.css --}}

    {{-- STACK FOR SCRIPTS --}}
    {{-- This is where custom JS from child views will be pushed --}}
    @stack('head_scripts') {{-- For scripts that need to be in the head (less common) --}}

</head>
<body class="font-sans antialiased bg-gray-100"> {{-- Apply basic body styling --}}
    <div id="app">
        <nav class="bg-white shadow-sm py-4"> {{-- Simple Navbar with Tailwind classes --}}
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <a class="text-lg font-bold text-gray-800" href="{{ url('/') }}">
                    {{ config('app.name', 'Stokku') }}
                </a>

                <div class="flex items-center space-x-4"> {{-- Right side of Navbar --}}
                    @guest
                        @if (Route::has('login'))
                            <a class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium" href="{{ route('login') }}">{{ __('Login') }}</a>
                        @endif

                        @if (Route::has('register'))
                            <a class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-200" href="{{ route('register') }}">{{ __('Register') }}</a>
                        @endif
                    @else
                        {{-- User Name Dropdown (Tailwind equivalent) --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ Auth::user()->name }}
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
                                <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route('dashboard') }}">Dashboard</a> {{-- Link to dashboard for logged in users --}}
                                <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </nav>

        <main> {{-- No padding here, let child sections manage their own padding --}}
            @yield('content')
        </main>
    </div>

    {{-- STACK FOR SCRIPTS AT THE END OF BODY --}}
    @stack('scripts') {{-- This is where custom JS from child views will be pushed --}}

    {{-- Alpine.js for simple dropdowns, if you want it --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
