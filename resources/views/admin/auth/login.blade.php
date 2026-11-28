@extends('admin.layouts.guest')

@section('title', 'Masuk Admin — Gemi')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />
<style>
    .login-page {
        min-height: 100vh;
        width: 100%;
        background: #F4F9F4;
        font-family: 'Inter', sans-serif;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 24px 16px;
        box-sizing: border-box;
    }

    .login-page h1,
    .login-page .font-display {
        font-family: 'Poppins', sans-serif;
    }

    .login-card-single {
        width: 100%;
        max-width: 400px;
        margin: auto;
        background: #ffffff;
        border: 1px solid #dcfce7;
        border-radius: 28px;
        padding: 32px 28px;
        box-shadow: 0 20px 50px rgba(15, 76, 29, 0.08);
        box-sizing: border-box;
    }

    .login-submit-btn {
        width: 100%;
        height: 46px;
        border: none;
        border-radius: 999px;
        background: linear-gradient(90deg, #28B446 0%, #1E9638 100%);
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 10px 25px -5px rgba(34, 168, 59, 0.45);
        transition: transform 0.18s ease, filter 0.18s ease;
    }

    .login-submit-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        filter: brightness(1.03);
    }

    .login-submit-btn:disabled {
        opacity: 0.75;
        cursor: wait;
    }
</style>
@endpush

@section('content')
<div class="login-page">
    {{-- Bar Atas Ringkas --}}
    <div class="max-w-5xl w-full mx-auto flex items-center justify-between gap-3">
        <a href="{{ route('home') }}"
            class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-[#0F4C1D] bg-white hover:bg-green-50 border border-green-100 px-4 py-2 rounded-full transition shadow-xs">
            <span>&larr;</span>
            <span>Beranda</span>
        </a>

        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#15803D] bg-green-100/80 px-3 py-1.5 rounded-full">
            <span class="w-2 h-2 rounded-full bg-[#22A83B]"></span>
            Admin Gemi
        </span>
    </div>

    {{-- Kartu Login Tunggal di Tengah --}}
    <div class="login-card-single my-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-11 h-11 rounded-2xl bg-green-50 border border-green-200/70 flex items-center justify-center shrink-0">
                <img src="{{ asset('assets/admin/img/icon-app.png') }}" alt="Logo Gemi" class="w-7 h-7 object-contain">
            </div>
            <div>
                <h1 class="text-xl font-bold text-[#0F4C1D] leading-tight">Login Admin</h1>
                <p class="text-xs text-slate-500 mt-0.5">Bank Sampah Sahabat Ibu</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 px-3.5 py-2.5 text-xs text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="loginForm" action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="username" class="block text-xs font-semibold text-[#0F4C1D] mb-1.5">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    required
                    autofocus
                    class="w-full h-11 px-4 rounded-xl bg-[#F6FBF6] border border-green-100 focus:bg-white focus:border-[#22A83B] focus:outline-none focus:ring-3 focus:ring-[#22A83B]/15 text-sm text-slate-800 transition"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-[#0F4C1D] mb-1.5">Password</label>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                        class="w-full h-11 pl-4 pr-11 rounded-xl bg-[#F6FBF6] border border-green-100 focus:bg-white focus:border-[#22A83B] focus:outline-none focus:ring-3 focus:ring-[#22A83B]/15 text-sm text-slate-800 transition"
                    >
                    <button
                        type="button"
                        id="togglePasswordBtn"
                        aria-label="Lihat atau sembunyikan password"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg text-slate-400 hover:text-[#15803D] hover:bg-green-50 flex items-center justify-center transition cursor-pointer"
                    >
                        <i id="eyeIcon" class="fa-regular fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-0.5 pb-1">
                <label for="remember" class="inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-green-300 text-[#22A83B] focus:ring-[#22A83B]"
                    >
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" id="loginSubmitBtn" class="login-submit-btn">
                <span id="loginBtnText">Masuk &rarr;</span>
            </button>
        </form>
    </div>

    {{-- Footer Ringkas --}}
    <p class="text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Gemi &middot; Bank Sampah Sahabat Ibu
    </p>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn && passwordInput && eyeIcon) {
            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                eyeIcon.className = isHidden ? 'fa-regular fa-eye-slash text-sm' : 'fa-regular fa-eye text-sm';
                passwordInput.focus();
            });
        }

        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('loginSubmitBtn');
        const btnText = document.getElementById('loginBtnText');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                if (!loginForm.checkValidity()) return;
                submitBtn.disabled = true;
                if (btnText) btnText.textContent = 'Memproses...';
            });
        }
    });
</script>
@endpush
