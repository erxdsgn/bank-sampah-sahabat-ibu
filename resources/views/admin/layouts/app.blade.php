<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="{{ asset('assets/admin/img/logo-bssi.png') }}">

    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Adminator') }}</title>

    {{-- APPLY SAVED THEME --}}
    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('dash26-theme');
                const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', savedTheme || (systemDark ? 'dark' : 'light'));
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    {{-- MAIN VITE CSS --}}
    @vite('resources/css/app.css')

    @stack('styles')
    @stack('head')
</head>

<body data-active="@yield('active', 'dashboard')" data-crumbs="@yield('crumbs', 'Dashboard')">

    <div class="shell">
        <div data-shell-sidebar></div>
        <div class="main">
            <div data-shell-topbar></div>
            <main class="content">
                @yield('content')
            </main>
            <div data-shell-footer></div>
        </div>
    </div>

    {{-- VENDORS (UMD) --}}
    <script src="{{ asset('assets/admin/vendor/chart.umd.min.js') }}"></script>
    @if (file_exists(public_path('assets/admin/vendor/fullcalendar.global.min.js')))
        <script src="{{ asset('assets/admin/vendor/fullcalendar.global.min.js') }}"></script>
    @endif

    {{-- MAIN VITE JS --}}
    @vite('resources/js/app.js')

    @stack('scripts')

</body>
</html>
