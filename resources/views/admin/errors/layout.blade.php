{{-- resources/views/errors/layout.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') · Gemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            light: '#A3E635',
                            lime: '#84CC16',
                            DEFAULT: '#22A83B',
                            dark: '#15803D',
                            forest: '#0F4C1D'
                        }
                    },
                    boxShadow: {
                        'green-glow': '0 10px 25px -5px rgba(34, 168, 59, 0.45)',
                        'card-soft': '0 20px 50px rgba(15, 76, 29, 0.08)',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#F4F9F4] text-slate-700 antialiased min-h-screen flex flex-col justify-between px-4 py-6">
    <header class="max-w-4xl w-full mx-auto flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-white border border-green-200/70 p-1.5 flex items-center justify-center shadow-xs">
                <img src="{{ asset('assets/admin/img/icon-app.png') }}" alt="Logo Gemi" class="w-full h-full object-contain">
            </div>
            <div>
                <span class="font-display font-bold text-lg text-brand leading-none block">Gemi</span>
                <span class="text-[10px] font-semibold text-brand-forest/75">Bank Sampah Sahabat Ibu</span>
            </div>
        </a>

        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-green-50 border border-green-200 text-brand-dark text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-brand"></span>
            HTTP @yield('code')
        </span>
    </header>

    <main class="w-full max-w-lg mx-auto my-auto py-8">
        <div class="bg-white border border-green-100 rounded-[2rem] p-7 sm:p-10 shadow-card-soft text-center relative overflow-hidden">
            <div class="w-16 h-16 mx-auto rounded-2xl border flex items-center justify-center text-2xl mb-4 @yield('icon_style', 'bg-green-50 border-green-200/80 text-brand')">
                <i class="@yield('icon', 'fa-solid fa-circle-exclamation')"></i>
            </div>

            <span class="inline-block px-3 py-1 rounded-full bg-[#F6FBF6] border border-green-100 text-[11px] font-bold uppercase tracking-wider text-brand-dark mb-2">
                @yield('tag')
            </span>

            <h1 class="font-display font-extrabold text-6xl sm:text-7xl tracking-tight text-brand-forest leading-none my-1">
                @yield('code')
            </h1>

            <h2 class="font-display font-bold text-xl sm:text-2xl text-brand-forest mt-3">
                @yield('title')
            </h2>

            <p class="text-slate-600 text-sm leading-relaxed mt-2.5">
                @yield('message')
            </p>

            @hasSection('hint')
                <div class="mt-5 p-3.5 rounded-2xl bg-[#F6FBF6] border border-green-100 text-left flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-brand text-sm mt-0.5 shrink-0"></i>
                    <p class="text-xs text-slate-600 leading-relaxed">@yield('hint')</p>
                </div>
            @endif

            <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
                @yield('actions')
            </div>
        </div>
    </main>

    <footer class="text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} <strong class="text-brand-forest">Gemi</strong> &middot; Bank Sampah Sahabat Ibu
    </footer>
</body>
</html>
