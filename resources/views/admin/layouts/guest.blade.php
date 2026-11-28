<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('app.name', 'Gemi') }}</title>

    {{-- Set tema sebelum render (mencegah flash) --}}
    <script>
        !function () {
            try {
                var t = localStorage.getItem("dash26-theme"),
                    e = window.matchMedia("(prefers-color-scheme: dark)").matches;
                document.documentElement.setAttribute("data-theme", t || (e ? "dark" : "light"));
            } catch (t) {
                document.documentElement.setAttribute("data-theme", "light");
            }
        }();
    </script>

    <!-- Tailwind CSS (Pastikan ini ada jika Anda menggunakan class Tailwind) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-[#f4f9f4] text-slate-800 antialiased">
    <div class="auth-shell w-full min-h-screen">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
