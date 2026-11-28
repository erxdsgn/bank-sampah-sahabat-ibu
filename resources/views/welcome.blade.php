<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemi — Aplikasi Bank Sampah Sahabat Ibu</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- FontAwesome -->
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

    <style>
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-animate {
            animation: modalFadeIn 0.22s ease-out forwards;
        }
    </style>
</head>

<body class="bg-[#F4F9F4] text-slate-700 antialiased selection:bg-brand selection:text-white overflow-x-hidden">

    <!-- Top Banner -->
    <div id="top-banner" class="bg-brand-forest text-white text-xs sm:text-sm py-2.5 px-4 text-center relative z-40">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2.5 flex-wrap">
            <span
                class="bg-brand-lime text-brand-forest font-bold px-2.5 py-0.5 rounded-full text-[11px] uppercase tracking-wider">Android
                APK</span>
            <span>Aplikasi mobile <strong>Gemi</strong> untuk nasabah <strong>Bank Sampah Sahabat Ibu</strong> siap
                digunakan.</span>
            <button type="button" onclick="openDownloadModal()"
                class="underline font-semibold text-lime-300 hover:text-white transition ml-1 cursor-pointer">
                Unduh Sekarang &rarr;
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-14">

        <!-- Hero Card Container -->
        <div
            class="relative bg-white rounded-[2rem] shadow-card-soft overflow-hidden border border-green-100/80 min-h-[620px] flex flex-col justify-between">

            <!-- Dekorasi Daun Gantung -->
            <div class="hidden sm:block absolute -top-2 left-6 md:left-10 z-20 pointer-events-none select-none">
                <svg width="86" height="190" viewBox="0 0 86 190" fill="none"
                    xmlns="http://www.w3.org/2000/svg" class="drop-shadow-sm">
                    <rect x="40" y="0" width="5" height="125" rx="2.5" fill="#4A2511" />
                    <path
                        d="M42.5 52C15 52 3 74 3 102C3 130 42.5 186 42.5 186C42.5 186 82 130 82 102C82 74 70 52 42.5 52Z"
                        fill="#95D64B" />
                    <path d="M42.5 52C15 52 3 74 3 102C3 130 42.5 186 42.5 186V52Z" fill="#7AC135" />
                    <path d="M42.5 76L25 63" stroke="#4A2511" stroke-width="4" stroke-linecap="round" />
                    <path d="M42.5 94L60 80" stroke="#4A2511" stroke-width="4" stroke-linecap="round" />
                    <path d="M42.5 108L24 94" stroke="#4A2511" stroke-width="4" stroke-linecap="round" />
                    <path d="M42.5 122L57 110" stroke="#4A2511" stroke-width="4" stroke-linecap="round" />
                </svg>
            </div>

            <!-- Background Wave Hijau -->
            <div class="hidden lg:block absolute top-0 right-0 w-[48%] h-full pointer-events-none z-0">
                <svg viewBox="0 0 600 700" fill="none" preserveAspectRatio="none" class="w-full h-full">
                    <defs>
                        <linearGradient id="greenWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#9BE15D" />
                            <stop offset="100%" stop-color="#38B249" />
                        </linearGradient>
                    </defs>
                    <path
                        d="M130 0C100 85 185 115 150 185C115 255 25 220 35 305C45 390 190 365 185 455C180 545 55 575 95 650C115 685 170 700 220 700H600V0H130Z"
                        fill="#E8F7E8" />
                    <path
                        d="M165 0C135 85 215 115 180 185C145 255 60 225 70 305C80 385 220 365 215 455C210 545 95 575 135 650C155 685 200 700 250 700H600V0H165Z"
                        fill="url(#greenWaveGrad)" />
                </svg>
            </div>

            <!-- Navbar -->
            <header class="relative z-30 px-6 sm:px-12 lg:px-20 pt-7 pb-4 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group pl-0 sm:pl-14 lg:pl-12">
                    <div
                        class="relative w-12 h-12 flex items-center justify-center bg-gradient-to-br from-green-50 to-lime-50/80 rounded-2xl p-1.5 border border-green-200/60 shadow-sm group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                        <img src="{{ asset('assets/admin/img/icon-app.png') }}" alt="Logo Gemi"
                            class="w-full h-full object-contain drop-shadow-xs">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-display font-bold text-2xl tracking-tight text-brand leading-none">Gemi</span>
                        <span class="text-[10px] font-semibold text-brand-forest/80 tracking-wide mt-0.5">Bank Sampah
                            Sahabat Ibu</span>
                    </div>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center gap-2 lg:gap-3 text-[15px] font-medium text-brand-forest">
                    <a href="#beranda"
                        class="px-3.5 py-1.5 rounded-lg hover:bg-green-100/80 hover:text-brand-dark transition">Beranda</a>
                    <a href="#cara-kerja"
                        class="px-3.5 py-1.5 rounded-lg hover:bg-green-100/80 hover:text-brand-dark transition">Alur
                        Setor</a>
                    <a href="#jenis-sampah"
                        class="px-3.5 py-1.5 rounded-lg hover:bg-green-100/80 hover:text-brand-dark transition">Harga
                        Sampah</a>
                    <a href="#unduh-app"
                        class="px-3.5 py-1.5 rounded-lg hover:bg-emerald-950/15 hover:text-emerald-950 transition">Unduh
                        Aplikasi</a>

                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="ml-1.5 bg-brand-forest hover:bg-slate-900 text-white text-xs lg:text-sm font-semibold px-4 py-2.5 rounded-full shadow-md transition flex items-center gap-2 border border-white/20">
                            <i class="fa-solid fa-gauge text-lime-300"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="ml-1.5 bg-brand-forest hover:bg-slate-900 text-white text-xs lg:text-sm font-semibold px-4 py-2.5 rounded-full shadow-md transition flex items-center gap-2 border border-white/20">
                            <i class="fa-solid fa-user-shield text-lime-300"></i>
                            <span>Login Admin</span>
                        </a>
                    @endauth
                </nav>

                <!-- Mobile Menu Button -->
                <div class="flex items-center gap-2 md:hidden">
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="bg-brand-forest text-white text-xs font-semibold px-3 py-2 rounded-full shadow-sm">
                            <i class="fa-solid fa-gauge mr-1 text-lime-300"></i> Admin
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="bg-brand-forest text-white text-xs font-semibold px-3 py-2 rounded-full shadow-sm">
                            <i class="fa-solid fa-user-shield mr-1 text-lime-300"></i> Admin
                        </a>
                    @endauth
                    <button type="button" id="mobile-menu-btn" onclick="toggleMobileMenu()"
                        class="p-2.5 rounded-xl text-brand-forest hover:bg-green-50 transition"
                        aria-label="Buka Menu">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </header>

            <!-- Mobile Menu -->
            <div id="mobile-menu"
                class="hidden md:hidden relative z-30 bg-white/95 backdrop-blur-md border-y border-green-100 px-6 py-4 space-y-2.5">
                <a href="#beranda" onclick="toggleMobileMenu()"
                    class="block py-2 px-3 rounded-lg font-medium text-brand-forest hover:bg-green-50 hover:text-brand">Beranda</a>
                <a href="#cara-kerja" onclick="toggleMobileMenu()"
                    class="block py-2 px-3 rounded-lg font-medium text-brand-forest hover:bg-green-50 hover:text-brand">Alur
                    Setor</a>
                <a href="#jenis-sampah" onclick="toggleMobileMenu()"
                    class="block py-2 px-3 rounded-lg font-medium text-brand-forest hover:bg-green-50 hover:text-brand">Harga
                    Sampah</a>
                <a href="#unduh-app" onclick="toggleMobileMenu()"
                    class="block py-2 px-3 rounded-lg font-medium text-brand-forest hover:bg-green-50 hover:text-brand">Unduh
                    Aplikasi</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                        class="block py-2 px-3 rounded-lg font-semibold text-brand-forest hover:bg-green-50 flex items-center gap-2">
                        <i class="fa-solid fa-gauge text-brand"></i> Dashboard Admin
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="block py-2 px-3 rounded-lg font-semibold text-brand-forest hover:bg-green-50 flex items-center gap-2">
                        <i class="fa-solid fa-user-shield text-brand"></i> Login Admin Bank Sampah
                    </a>
                @endauth
            </div>

            <!-- Hero Content -->
            <div id="beranda"
                class="relative z-20 px-6 sm:px-12 lg:px-20 pt-8 pb-12 lg:py-12 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                <!-- Kolom Kiri -->
                <div class="lg:col-span-5 space-y-6 text-left pl-0 sm:pl-14 lg:pl-12">
                    <h1 class="font-display font-bold text-4xl sm:text-5xl text-brand tracking-tight leading-tight">
                        Bank Sampah Sahabat Ibu
                    </h1>

                    <p class="text-[#569A5D] text-sm sm:text-base leading-relaxed font-normal max-w-md">
                        <strong>Gemi</strong> adalah aplikasi yang bertujuan untuk memberikan solusi sampah di sekitar
                        kita. Antarkan langsung sampah terpilah Anda ke pos <strong>Bank Sampah Sahabat Ibu</strong>
                        untuk ditimbang dan dikonversi menjadi tabungan berharga.
                    </p>

                    <div class="pt-2 flex flex-wrap items-center gap-3.5">
                        <button type="button" onclick="openDownloadModal()"
                            class="bg-gradient-to-r from-[#28B446] to-[#1E9638] hover:from-[#22A83B] hover:to-[#177E2E] text-white font-display font-semibold text-base px-7 py-3.5 rounded-full shadow-green-glow hover:scale-[1.03] active:scale-[0.98] transition-all duration-200 flex items-center gap-2.5 cursor-pointer">
                            <i class="fa-brands fa-android text-xl"></i>
                            <span>Download App</span>
                        </button>

                        @auth
                            <a href="{{ route('admin.dashboard') }}"
                                class="bg-white hover:bg-green-50 text-brand-forest border-2 border-green-200 font-display font-semibold text-sm px-5 py-3.5 rounded-full transition flex items-center gap-2">
                                <i class="fa-solid fa-gauge text-brand"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="bg-white hover:bg-green-50 text-brand-forest border-2 border-green-200 font-display font-semibold text-sm px-5 py-3.5 rounded-full transition flex items-center gap-2">
                                <i class="fa-solid fa-user-shield text-brand"></i>
                                <span>Portal Admin</span>
                            </a>
                        @endauth
                    </div>

                    <div class="pt-1 flex items-center gap-4 text-xs text-slate-500">
                        <span class="font-medium text-slate-600">Link langsung:</span>
                        <a href="{{ $linkGdrive }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 text-brand-dark hover:text-brand font-semibold underline decoration-green-300 underline-offset-4 transition">
                            <i class="fa-brands fa-google-drive"></i> Google Drive
                        </a>
                        <span>•</span>
                        <a href="{{ $linkMediafire }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 font-semibold underline decoration-blue-300 underline-offset-4 transition">
                            <i class="fa-solid fa-fire"></i> MediaFire
                        </a>
                    </div>
                </div>

                <!-- Kolom Kanan: Gambar -->
                <div class="lg:col-span-7 relative flex justify-center lg:justify-end items-end mt-4 lg:mt-0">
                    <div class="w-full max-w-[620px] relative">
                        <img src="{{ asset('assets/admin/img/assetweb.png') }}"
                            alt="Ilustrasi Penyetoran Sampah di Bank Sampah Sahabat Ibu"
                            class="w-full h-auto object-contain rounded-2xl mix-blend-multiply drop-shadow-sm"
                            onerror="this.onerror=null; this.src='https://placehold.co/620x400/E8F7E8/15803D?text=assetweb.png';">
                    </div>
                </div>
            </div>

            <!-- Statistik / Keunggulan Bar -->
            <div
                class="relative z-20 bg-green-50/90 border-t border-green-100 px-6 sm:px-12 lg:px-20 py-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-6">

                <div class="flex items-start gap-3.5 min-w-0">
                    <div
                        class="w-11 h-11 shrink-0 rounded-2xl bg-brand/10 text-brand flex items-center justify-center text-base">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display font-bold text-base leading-snug text-brand-forest">
                            Harga Transparan
                        </p>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Sesuai Ketentuan Mitra
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 min-w-0">
                    <div
                        class="w-11 h-11 shrink-0 rounded-2xl bg-brand/10 text-brand flex items-center justify-center text-base">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display font-bold text-base leading-snug text-brand-forest">
                            Setor Mandiri
                        </p>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Timbang Langsung di Pos
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 min-w-0">
                    <div
                        class="w-11 h-11 shrink-0 rounded-2xl bg-brand/10 text-brand flex items-center justify-center text-base">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display font-bold text-base leading-snug text-brand-forest">
                            Buku Digital
                        </p>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Catatan Tabungan Real-time
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 min-w-0">
                    <div
                        class="w-11 h-11 shrink-0 rounded-2xl bg-brand/10 text-brand flex items-center justify-center text-base">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display font-bold text-base leading-snug text-brand-forest">
                            Pencairan Tahunan
                        </p>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Dicairkan 1 Tahun Sekali
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Alur Setor -->
    <section id="cara-kerja" class="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-brand font-semibold text-sm uppercase tracking-wider">Mudah & Transparan</span>
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-brand-forest mt-2">Alur Menabung di Gemi</h2>
            <p class="text-slate-600 mt-3 text-sm sm:text-base">Ubah tumpukan botol plastik, kardus, dan logam di rumah
                menjadi tabungan tahunan di <strong>Bank Sampah Sahabat Ibu</strong> dalam 3 langkah.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
                class="bg-white rounded-3xl p-8 shadow-sm border border-green-100 hover:shadow-md transition relative">
                <div
                    class="w-12 h-12 rounded-2xl bg-green-100 text-brand font-display font-bold text-xl flex items-center justify-center mb-6">
                    1</div>
                <h3 class="font-display font-bold text-xl text-brand-forest mb-2">Pilah Sampah di Rumah</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Pisahkan sampah anorganik (plastik bersih, kardus,
                    kaleng, besi, dan minyak jelantah) sesuai jenisnya agar nilai jual lebih tinggi.</p>
            </div>
            <div
                class="bg-white rounded-3xl p-8 shadow-sm border border-green-100 hover:shadow-md transition relative">
                <div
                    class="w-12 h-12 rounded-2xl bg-green-100 text-brand font-display font-bold text-xl flex items-center justify-center mb-6">
                    2</div>
                <h3 class="font-display font-bold text-xl text-brand-forest mb-2">Antar ke Bank Sampah Sahabat Ibu</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Bawa sampah terpilah Anda langsung ke pos
                    <strong>Bank Sampah Sahabat Ibu</strong> pada jam operasional untuk ditimbang oleh pengurus.
                </p>
            </div>
            <div
                class="bg-white rounded-3xl p-8 shadow-sm border border-green-100 hover:shadow-md transition relative">
                <div
                    class="w-12 h-12 rounded-2xl bg-green-100 text-brand font-display font-bold text-xl flex items-center justify-center mb-6">
                    3</div>
                <h3 class="font-display font-bold text-xl text-brand-forest mb-2">Tercatat & Cair 1 Tahun Sekali</h3>
                <p class="text-slate-600 text-sm leading-relaxed">Admin mencatat hasil timbangan ke akun
                    <strong>Gemi</strong> Anda. Akumulasi tabungan sampah akan dicairkan secara serentak <strong>1 tahun
                        sekali</strong> oleh mitra.
                </p>
            </div>
        </div>
    </section>

    <!-- Section: Daftar Harga Sampah (Dinamis dari Database) -->
    <section id="jenis-sampah" class="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="bg-white rounded-[2rem] p-8 sm:p-12 border border-green-100 shadow-sm">

            <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                <div>
                    <span class="text-brand font-semibold text-sm uppercase tracking-wider">Transparan &
                        Kompetitif</span>
                    <h2 class="font-display font-bold text-2xl sm:text-3xl text-brand-forest mt-1">Daftar Harga Beli
                        Sampah</h2>
                </div>
                <p class="text-xs text-slate-500">*Harga dapat berubah sewaktu-waktu mengikuti harga pasar pengepul
                    pusat.</p>
            </div>

            @php
                $adaItem = false;
                $totalItems = 0;
                foreach ($kategoriInduk as $induk) {
                    $totalItems += $induk->anak->isNotEmpty() ? $induk->anak->count() : 1;
                }
            @endphp

            <!-- Area Filter & Search (Berkumpul di Kiri) -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-8">

                <!-- Tombol Semua Jenis -->
                <div class="flex flex-wrap gap-2 shrink-0" id="category-filters">
                    <button type="button" onclick="filterWaste('semua', this)"
                        class="filter-btn active px-4 py-2 rounded-full text-xs font-semibold bg-brand text-white shadow-xs transition cursor-pointer">
                        Semua Jenis ({{ $totalItems }})
                    </button>

                    @foreach ($kategoriInduk as $induk)
                        @php $slugInduk = \Illuminate\Support\Str::slug($induk->nama_kategori); @endphp
                        <button type="button" onclick="filterWaste('{{ $slugInduk }}', this)"
                            class="filter-btn hidden px-4 py-2 rounded-full text-xs font-semibold bg-green-50 text-brand-forest hover:bg-green-100 transition cursor-pointer">
                            {{ $induk->nama_kategori }}
                        </button>
                    @endforeach
                </div>

                <!-- Search Bar Tepat di Sampingnya -->
                <div class="w-full sm:w-72 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-brand/70 text-xs"></i>
                    </div>
                    <input type="text" id="searchWaste" onkeyup="searchWaste()" placeholder="Cari nama sampah..."
                        class="w-full pl-9 pr-4 py-2 bg-[#F6FBF6] border border-green-200/80 rounded-full text-xs sm:text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-brand focus:ring-2 focus:ring-brand/15 transition">
                </div>

            </div>

            <!-- Grid Harga -->
            <div id="waste-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($kategoriInduk as $induk)
                    @php
                        $slugInduk = \Illuminate\Support\Str::slug($induk->nama_kategori);
                        $itemsToShow = $induk->anak->isNotEmpty() ? $induk->anak : collect([$induk]);
                    @endphp

                    @foreach ($itemsToShow as $item)
                        @php
                            $adaItem = true;
                            $harga = optional($item->hargaTerbaru)->harga_satuan ?? 0;
                            $searchText = strtolower($induk->nama_kategori . ' ' . $item->nama_kategori);
                        @endphp

                        <div data-category="{{ $slugInduk }}" data-name="{{ $searchText }}"
                            class="waste-item p-4 rounded-2xl bg-[#F6FBF6] border border-green-100 flex items-center gap-3.5 hover:border-brand/40 transition">

                            <div
                                class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-recycle"></i>
                            </div>

                            <div class="min-w-0 w-full">
                                <h4 class="font-display font-semibold text-sm text-brand-forest truncate">
                                    {{ $item->nama_kategori }}
                                </h4>

                                <p class="text-brand font-bold text-sm mt-0.5">
                                    Rp {{ number_format($harga, 0, ',', '.') }}
                                    <span class="text-slate-400 font-normal text-xs">/ {{ $item->satuan }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>

            <!-- Pesan Kosong saat Search -->
            <div id="empty-state" class="hidden text-center py-12 flex-col items-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-2xl"></i>
                </div>
                <h3 class="font-display font-bold text-lg text-slate-700 mb-1">Sampah tidak ditemukan</h3>
                <p class="text-sm text-slate-500">Coba gunakan kata kunci lain untuk mencari jenis sampah.</p>
            </div>

            <!-- Navigasi Pagination -->
            <div id="waste-pagination"
                class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-green-100 pt-5 hidden">
                <p id="pagination-info" class="text-xs text-slate-500"></p>
                <div id="pagination-buttons" class="flex items-center gap-1.5 flex-wrap"></div>
            </div>

            @unless ($adaItem)
                <div class="text-center py-10">
                    <i class="fa-solid fa-inbox text-4xl text-slate-300 mb-3"></i>
                    <p class="text-sm text-slate-500">Belum ada data kategori dan harga sampah yang ditampilkan.</p>
                </div>
            @endunless
        </div>
    </section>

    <!-- Section: Unduh Aplikasi -->
    <section id="unduh-app" class="py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div
            class="bg-gradient-to-br from-brand-forest via-[#166534] to-[#15803D] rounded-[2rem] p-8 sm:p-14 text-white relative overflow-hidden shadow-xl">
            <div class="absolute -right-16 -bottom-16 w-72 h-72 rounded-full bg-brand-lime/20 pointer-events-none">
            </div>
            <div class="absolute right-24 -top-20 w-48 h-48 rounded-full bg-white/5 pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-4">
                    <span
                        class="inline-block bg-white/15 backdrop-blur-sm text-lime-300 text-xs font-semibold px-3.5 py-1 rounded-full">
                        Akses Publik Gratis • Android APK
                    </span>
                    <h2 class="font-display font-bold text-3xl sm:text-4xl leading-tight">
                        Unduh Aplikasi Gemi Sekarang
                    </h2>
                    <p class="text-green-100 text-sm sm:text-base max-w-xl leading-relaxed">
                        Aplikasi resmi untuk nasabah <strong>Bank Sampah Sahabat Ibu</strong>. Silakan pilih salah satu
                        tautan unduhan di bawah ini (Google Drive atau MediaFire).
                    </p>
                    <div class="pt-2 flex flex-wrap gap-3">
                        <a href="{{ $linkGdrive }}" target="_blank" rel="noopener"
                            class="bg-white text-brand-forest hover:bg-lime-50 font-semibold px-5 py-3 rounded-xl shadow-md transition flex items-center gap-2.5 text-sm">
                            <i class="fa-brands fa-google-drive text-lg text-green-600"></i>
                            <span>Download via Google Drive</span>
                        </a>
                        <a href="{{ $linkMediafire }}" target="_blank" rel="noopener"
                            class="bg-[#0070F3] hover:bg-[#0060D1] text-white font-semibold px-5 py-3 rounded-xl shadow-md transition flex items-center gap-2.5 text-sm">
                            <i class="fa-solid fa-fire text-lg"></i>
                            <span>Download via MediaFire</span>
                        </a>
                        <button type="button" onclick="openDownloadModal()"
                            class="bg-white/15 hover:bg-white/25 text-white border border-white/25 font-semibold px-4 py-3 rounded-xl transition flex items-center gap-2 text-sm cursor-pointer">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Panduan Instalasi</span>
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-6 space-y-3 text-sm">
                        <div class="flex justify-between py-1.5 border-b border-white/10">
                            <span class="text-green-200">Platform</span>
                            <span class="font-mono font-semibold">Android (Flutter APK)</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-white/10">
                            <span class="text-green-200">Metode Setor</span>
                            <span class="font-semibold">Setor Mandiri ke Pos</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-white/10">
                            <span class="text-green-200">Mitra Bank Sampah</span>
                            <span class="font-semibold">Bank Sampah Sahabat Ibu</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-green-200">Jadwal Pencairan</span>
                            <span class="text-lime-300 font-semibold flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar-check"></i> 1 Tahun Sekali
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 border-t border-green-200/60 mt-8 gap-4">
        <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-start">
            <span class="font-display font-bold text-base text-brand">Gemi</span>
            <span>&copy; {{ date('Y') }} Mitra <strong>Bank Sampah Sahabat Ibu</strong>. Pencairan tabungan 1 tahun
                sekali.</span>
        </div>
        <div class="flex items-center gap-5">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="text-brand-forest hover:underline font-semibold">
                    <i class="fa-solid fa-gauge mr-1 text-brand"></i> Dashboard Admin
                </a>
            @else
                <a href="{{ route('login') }}" class="text-brand-forest hover:underline font-semibold">
                    <i class="fa-solid fa-user-shield mr-1 text-brand"></i> Login Admin
                </a>
            @endauth
            <a href="#beranda" class="hover:text-brand">Kembali ke Atas &uarr;</a>
        </div>
    </footer>

    <!-- Modal: Download -->
    <div id="download-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-green-100 modal-animate relative">
            <button type="button" onclick="closeDownloadModal()"
                class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-green-100 text-brand flex items-center justify-center text-2xl">
                    <i class="fa-brands fa-android"></i>
                </div>
                <div>
                    <h3 class="font-display font-bold text-xl text-brand-forest">Download Gemi APK</h3>
                    <p class="text-xs text-slate-500">Aplikasi Nasabah Bank Sampah Sahabat Ibu</p>
                </div>
            </div>

            <div class="space-y-3 my-6">
                <a href="{{ $linkGdrive }}" target="_blank" rel="noopener"
                    class="w-full p-4 rounded-2xl border-2 border-green-100 hover:border-brand bg-green-50/40 hover:bg-green-50 transition flex items-center justify-between group text-left">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-xl text-green-600">
                            <i class="fa-brands fa-google-drive"></i>
                        </div>
                        <div>
                            <p class="font-display font-bold text-sm text-brand-forest group-hover:text-brand">Link 1 —
                                Google Drive</p>
                            <p class="text-xs text-slate-500">Unduh cepat melalui Google Drive</p>
                        </div>
                    </div>
                    <span
                        class="px-3.5 py-1.5 rounded-full bg-brand text-white text-xs font-semibold group-hover:scale-105 transition">
                        Unduh
                    </span>
                </a>

                <a href="{{ $linkMediafire }}" target="_blank" rel="noopener"
                    class="w-full p-4 rounded-2xl border-2 border-blue-100 hover:border-blue-500 bg-blue-50/30 hover:bg-blue-50 transition flex items-center justify-between group text-left">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-xl text-blue-600">
                            <i class="fa-solid fa-fire"></i>
                        </div>
                        <div>
                            <p class="font-display font-bold text-sm text-slate-800 group-hover:text-blue-600">Link 2 —
                                MediaFire</p>
                            <p class="text-xs text-slate-500">Unduh langsung tanpa batas kuota</p>
                        </div>
                    </div>
                    <span
                        class="px-3.5 py-1.5 rounded-full bg-blue-600 text-white text-xs font-semibold group-hover:scale-105 transition">
                        Unduh
                    </span>
                </a>
            </div>

            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-900 space-y-1.5">
                <p class="font-bold flex items-center gap-1.5"><i
                        class="fa-solid fa-circle-exclamation text-amber-600"></i> Cara Instalasi di Android:</p>
                <ol class="list-decimal list-inside space-y-1 text-amber-800">
                    <li>Unduh file <strong>.apk</strong> melalui salah satu link di atas.</li>
                    <li>Buka file APK yang telah selesai diunduh.</li>
                    <li>Jika diminta, aktifkan izin <strong>"Instal dari Sumber Tidak Dikenal"</strong> di pengaturan HP
                        Anda.</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        const WASTE_PER_PAGE = 8;
        let currentCategory = 'semua';
        let currentPage = 1;

        function getMatchingCards() {
            const searchInput = document.getElementById('searchWaste');
            const keyword = (searchInput ? searchInput.value : '').toLowerCase().trim();
            const allCards = Array.from(document.querySelectorAll('.waste-item'));

            return allCards.filter(card => {
                const cardCategory = card.getAttribute('data-category');
                const cardName = (card.getAttribute('data-name') || '').toLowerCase();

                const matchCategory = (currentCategory === 'semua') || (cardCategory === currentCategory);
                const matchSearch = !keyword || cardName.includes(keyword);

                return matchCategory && matchSearch;
            });
        }

        function renderWasteGrid(page = 1) {
            const allCards = Array.from(document.querySelectorAll('.waste-item'));
            const matchingCards = getMatchingCards();
            const totalMatched = matchingCards.length;
            const totalPages = Math.max(1, Math.ceil(totalMatched / WASTE_PER_PAGE));

            currentPage = Math.min(Math.max(1, page), totalPages);

            const startIdx = (currentPage - 1) * WASTE_PER_PAGE;
            const endIdx = startIdx + WASTE_PER_PAGE;
            const visibleSet = new Set(matchingCards.slice(startIdx, endIdx));

            allCards.forEach(card => {
                if (visibleSet.has(card)) {
                    card.classList.remove('hidden');
                    card.classList.add('flex');
                } else {
                    card.classList.add('hidden');
                    card.classList.remove('flex');
                }
            });

            const emptyState = document.getElementById('empty-state');
            if (emptyState) {
                if (totalMatched === 0 && allCards.length > 0) {
                    emptyState.classList.remove('hidden');
                    emptyState.classList.add('flex');
                } else {
                    emptyState.classList.add('hidden');
                    emptyState.classList.remove('flex');
                }
            }

            renderPaginationControls(totalMatched, totalPages, startIdx, endIdx);
        }

        function renderPaginationControls(totalMatched, totalPages, startIdx, endIdx) {
            const wrapper = document.getElementById('waste-pagination');
            const infoEl = document.getElementById('pagination-info');
            const buttonsEl = document.getElementById('pagination-buttons');

            if (!wrapper || !infoEl || !buttonsEl) return;

            if (totalMatched <= WASTE_PER_PAGE) {
                wrapper.classList.add('hidden');
                buttonsEl.innerHTML = '';
                return;
            }

            wrapper.classList.remove('hidden');
            const showFrom = startIdx + 1;
            const showTo = Math.min(endIdx, totalMatched);
            infoEl.textContent = `Menampilkan ${showFrom}–${showTo} dari ${totalMatched} jenis sampah`;

            let html = '';

            html += `<button type="button" onclick="renderWasteGrid(${currentPage - 1})"
                ${currentPage === 1 ? 'disabled' : ''}
                class="px-3 py-1.5 rounded-full text-xs font-semibold border border-green-200 transition ${currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-50 text-slate-400' : 'bg-white text-brand-forest hover:bg-green-50 cursor-pointer'}">
                &larr; Prev
            </button>`;

            for (let i = 1; i <= totalPages; i++) {
                const isActive = i === currentPage;
                html += `<button type="button" onclick="renderWasteGrid(${i})"
                    class="w-8 h-8 rounded-full text-xs font-semibold transition cursor-pointer ${isActive ? 'bg-brand text-white shadow-xs' : 'bg-green-50 text-brand-forest hover:bg-green-100'}">
                    ${i}
                </button>`;
            }

            html += `<button type="button" onclick="renderWasteGrid(${currentPage + 1})"
                ${currentPage === totalPages ? 'disabled' : ''}
                class="px-3 py-1.5 rounded-full text-xs font-semibold border border-green-200 transition ${currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-50 text-slate-400' : 'bg-white text-brand-forest hover:bg-green-50 cursor-pointer'}">
                Next &rarr;
            </button>`;

            buttonsEl.innerHTML = html;
        }

        function filterWaste(category, btnElement) {
            currentCategory = category;

            document.querySelectorAll('#category-filters .filter-btn').forEach(btn => {
                btn.classList.remove('bg-brand', 'text-white');
                btn.classList.add('bg-green-50', 'text-brand-forest');
            });
            if (btnElement) {
                btnElement.classList.remove('bg-green-50', 'text-brand-forest');
                btnElement.classList.add('bg-brand', 'text-white');
            }

            const searchInput = document.getElementById('searchWaste');
            if (searchInput) searchInput.value = '';

            renderWasteGrid(1);
        }

        function searchWaste() {
            currentCategory = 'semua';

            const allBtn = document.querySelector('#category-filters .filter-btn');
            if (allBtn) {
                document.querySelectorAll('#category-filters .filter-btn').forEach(btn => {
                    btn.classList.remove('bg-brand', 'text-white');
                    btn.classList.add('bg-green-50', 'text-brand-forest');
                });
                allBtn.classList.remove('bg-green-50', 'text-brand-forest');
                allBtn.classList.add('bg-brand', 'text-white');
            }

            renderWasteGrid(1);
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        function openDownloadModal() {
            const modal = document.getElementById('download-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDownloadModal() {
            const modal = document.getElementById('download-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.addEventListener('click', (e) => {
            const downloadModal = document.getElementById('download-modal');
            if (e.target === downloadModal) closeDownloadModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDownloadModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            renderWasteGrid(1);
        });
    </script>
</body>

</html>
