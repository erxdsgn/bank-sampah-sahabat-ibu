<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('assets/admin/img/logo-bssi.png') }}"
    >

    <title>
        @yield('title', 'Dashboard') ·
        {{ config('app.name', 'Adminator') }}
    </title>

    {{-- =====================================================
        APPLY SAVED THEME
    ====================================================== --}}
    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('dash26-theme');

                const systemDark =
                    window.matchMedia &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches;

                document.documentElement.setAttribute(
                    'data-theme',
                    savedTheme || (systemDark ? 'dark' : 'light')
                );
            } catch (error) {
                document.documentElement.setAttribute(
                    'data-theme',
                    'light'
                );
            }
        })();
    </script>

    {{-- Main CSS --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/admin/css/style.css') }}"
    >

    @stack('head')
</head>


<body
    data-active="@yield('active', 'dashboard')"
    data-crumbs="@yield('crumbs', 'Dashboard')"
>

    {{-- =====================================================
        APP SHELL
    ====================================================== --}}
    <div class="shell">

        {{-- Sidebar --}}
        <div data-shell-sidebar></div>


        {{-- =================================================
            MAIN AREA
        ================================================== --}}
        <div class="main">

            {{-- Topbar --}}
            <div data-shell-topbar></div>


            {{-- Page Content --}}
            <main class="content">
                @yield('content')
            </main>


            {{-- Footer --}}
            <div data-shell-footer></div>

        </div>

    </div>


    {{-- =====================================================
        VENDORS
    ====================================================== --}}
    <script src="{{ asset('assets/admin/js/runtime.js') }}"></script>

    <script src="{{ asset('assets/admin/vendor/vendors.js') }}"></script>

    <script src="{{ asset('assets/admin/vendor/vendor-fullcalendar.js') }}"></script>

    <script src="{{ asset('assets/admin/vendor/vendor-chartjs.js') }}"></script>


    {{-- =====================================================
        ADMIN MODULES
    ====================================================== --}}
    <script type="module">

        /*
         * Sidebar sudah meng-import navigation.js
         * sendiri, sehingga navigation.js tidak perlu
         * di-import lagi di sini.
         */

        import {
            renderSidebar
        } from "{{ asset('assets/admin/js/sidebar.js') }}";

        import "{{ asset('assets/admin/js/topbar.js') }}";

        import "{{ asset('assets/admin/js/footer.js') }}";

        import "{{ asset('assets/admin/js/layout.js') }}";


        /*
         * Render sidebar setelah DOM tersedia.
         */
        if (document.readyState === 'loading') {

            document.addEventListener(
                'DOMContentLoaded',
                () => {
                    renderSidebar();
                },
                {
                    once: true
                }
            );

        } else {

            renderSidebar();

        }

    </script>


    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>

</html>
