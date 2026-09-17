<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title') · {{ config('app.name', 'Adminator') }}</title>

    {{-- Applies saved theme (light/dark) before first paint to avoid a flash --}}
    <script>
        ! function() {
            try {
                var t = localStorage.getItem("dash26-theme"),
                    e = window.matchMedia("(prefers-color-scheme: dark)").matches;
                document.documentElement.setAttribute("data-theme", t || (e ? "dark" : "light"));
            } catch (t) {
                document.documentElement.setAttribute("data-theme", "light");
            }
        }();
    </script>

    <!-- Pindahkan semua JS ke sebelum </body> -->
    <script src="{{ asset('assets/admin/js/runtime.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/vendors.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/vendor-fullcalendar.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/vendor-chartjs.js') }}"></script>

    {{-- 1. Muat data navigasi terlebih dahulu --}}
    <script src="{{ asset('assets/admin/js/navigation.js') }}"></script>

    {{-- 2. Baru muat renderer sidebar dan layout --}}
    <script src="{{ asset('assets/admin/js/sidebar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/topbar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/footer.js') }}"></script>
    <script src="{{ asset('assets/admin/js/layout.js') }}"></script>

    <link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet">
    @stack('head')
</head>

<body data-active="@yield('active')" data-crumbs="@yield('crumbs')">
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

    @stack('scripts')
</body>

</html>
