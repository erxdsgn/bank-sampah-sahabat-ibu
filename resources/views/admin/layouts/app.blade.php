<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Adminator') }}</title>

    {{-- Applies saved theme --}}
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

    <link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet">
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

    {{-- Vendors & Runtime --}}
    <script src="{{ asset('assets/admin/js/runtime.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/vendors.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/vendor-fullcalendar.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/vendor-chartjs.js') }}"></script>

    {{-- Panggil modul utama dengan type="module" --}}
    <script type="module" src="{{ asset('assets/admin/js/navigation.js') }}"></script>
    <script type="module" src="{{ asset('assets/admin/js/sidebar.js') }}"></script>
    <script type="module" src="{{ asset('assets/admin/js/topbar.js') }}"></script>
    <script type="module" src="{{ asset('assets/admin/js/footer.js') }}"></script>
    <script type="module" src="{{ asset('assets/admin/js/layout.js') }}"></script>

    @stack('scripts')
</body>
</html>

    {{-- Script Injeksi Rendering Sidebar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarTarget = document.querySelector('[data-shell-sidebar]');
            const activeKey = document.body.getAttribute('data-active') || 'dashboard';

            if (sidebarTarget && window.navigation) {
                let html = '<aside class="sidebar"><div class="sidebar-content">';

                window.navigation.forEach(group => {
                    html += `<div class="nav-group"><div class="nav-label">${group.label}</div><ul class="nav-list">`;

                    group.items.forEach(item => {
                        const isActive = item.key === activeKey ? 'active' : '';
                        html += `
                            <li class="nav-item">
                                <a href="${item.href}" class="nav-link ${isActive}">
                                    <span class="nav-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${item.icon}</svg>
                                    </span>
                                    <span class="nav-text">${item.text}</span>
                                </a>
                            </li>
                        `;
                    });

                    html += `</ul></div>`;
                });

                html += '</div></aside>';
                sidebarTarget.innerHTML = html;
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
