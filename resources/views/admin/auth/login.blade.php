@extends('admin.layouts.guest')

@section('title', 'Sign in')

@section('content')
<aside class="auth-aside">
    <div class="auth-brand">
        <div class="logo">
            <svg viewbox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                <path d="M14.747 9.125c.527-1.426 1.736-2.573 3.317-2.573c1.643 0 2.792 1.085 3.318 2.573l6.077 16.867c.186.496.248.931.248 1.147c0 1.209-.992 2.046-2.139 2.046c-1.303 0-1.954-.682-2.264-1.611l-.931-2.915h-8.62l-.93 2.884c-.31.961-.961 1.642-2.232 1.642c-1.24 0-2.294-.93-2.294-2.17c0-.496.155-.868.217-1.023l6.233-16.867zm.34 11.256h5.891l-2.883-8.992h-.062l-2.946 8.992z" fill="#fff"></path>
            </svg>
        </div>
        <div class="name">Bank Sampah</div>
    </div>
    <div class="auth-aside-body">
        <span class="auth-aside-eyebrow">2026 · Panel Administrator</span>
        <h1>Sistem Pengelolaan Bank Sampah Terpadu.</h1>
        <p>Akses manajemen data warga, verifikasi setoran, pencairan saldo, dan laporan keuangan dalam satu dashboard resmi.</p>
        <div class="auth-quote">
            "Kelola operasional dan keuangan bank sampah dengan akurat, cepat, dan transparan."
            <div class="auth-quote-author">
                <div class="av">SI</div>
                <div>Sahabat Ibu · Tim Pengelola</div>
            </div>
        </div>
    </div>
    <div class="auth-aside-footer">
        <span>© 2026</span>
        <span>BANK SAMPAH SAHABAT IBU</span>
    </div>
</aside>

<main class="auth-main">
    <div class="auth-main-top">
        <!-- Tautan atas opsional -->
    </div>

    <div class="auth-card">
        <h2>Selamat Datang</h2>
        <p class="sub">Masukkan kredensial administrator Anda untuk masuk ke sistem.</p>

        {{-- Pesan Error Validasi Login --}}
        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Login Resmi Terhubung ke Laravel Route --}}
        <form class="auth-form" action="{{ route('login') }}" method="POST">
            @csrf

            {{-- Input Username --}}
            <div class="field">
                <label class="field-label" for="username">Username</label>
                <div class="input-icon">
                    <span class="ico">
                        <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <input autocomplete="username" class="input" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus type="text"/>
                </div>
            </div>

            {{-- Input Password --}}
            <div class="field">
                <div class="field-row">
                    <label class="field-label" for="password">Password</label>
                </div>
                <div class="input-icon">
                    <span class="ico">
                        <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect height="11" rx="2" width="18" x="3" y="11"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input autocomplete="current-password" class="input" id="password" name="password" placeholder="••••••••" required type="password"/>
                </div>
            </div>

            {{-- Checkbox Remember Me --}}
            <label class="check">
                <input name="remember" type="checkbox" id="remember" />
                <span class="box"></span> Ingat saya di perangkat ini
            </label>

            {{-- Tombol Submit --}}
            <button class="btn btn--primary auth-submit" type="submit">
                Masuk Sistem
                <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M13 5l7 7-7 7"></path>
                </svg>
            </button>
        </form>
    </div>

    <div class="auth-main-bottom">
        Akses khusus petugas dan administrator terdaftar.
    </div>
</main>
@endsection
