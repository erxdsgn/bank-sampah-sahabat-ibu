@extends('admin.layouts.app')

@section('title', 'Profil & Sesi Perangkat')
@section('active', 'pengaturan')
@section('crumbs', 'Konfigurasi | Profil & Sesi Perangkat')

@push('styles')
    @vite('resources/css/pages/profil-sesi.css')
@endpush

@section('content')

    <div class="profil-page">

        {{-- =========================================================
             HERO
             ========================================================= --}}
        <section class="hero">
            <div class="hero-text">
                <span class="eyebrow">KONFIGURASI</span>

                <h1 class="hero-title">
                    Profil & <span class="accent">Sesi Perangkat</span>
                </h1>

                <p class="hero-sub">
                    Kelola informasi profil admin dan perangkat yang terhubung ke akun.
                </p>
            </div>
        </section>


        {{-- =========================================================
             SETTINGS GRID
             ========================================================= --}}
        <div class="settings-grid">

            {{-- =====================================================
                 KOLOM 1: PROFIL ADMIN
                 ===================================================== --}}
            <div class="settings-col">

                <div class="section-heading">
                    <h2 class="section-heading-title">
                        Profil Admin & Pengaturan
                    </h2>
                </div>


                <section class="card">

                    <form
                        id="formProfil"
                        action="{{ route('admin.pengaturan.profil.update') }}"
                        method="POST"
                    >
                        @csrf
                        @method('PUT')


                        <div class="card-body">

                            {{-- PROFIL ADMIN --}}
                            <div class="section-title">

                                <div class="section-title-icon">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="7" r="4"></circle>
                                        <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                                    </svg>
                                </div>

                                <div>
                                    <strong>Informasi Admin</strong>
                                    <span>
                                        Data akun yang digunakan untuk masuk ke sistem.
                                    </span>
                                </div>

                            </div>


                            <div class="form-grid">

                                {{-- NAMA --}}
                                <div class="form-group form-group--full">

                                    <label for="profilNama">
                                        Nama Admin
                                    </label>

                                    <input
                                        type="text"
                                        id="profilNama"
                                        name="nama"
                                        value="{{ old('nama', $admin->nama) }}"
                                        placeholder="Masukkan nama admin"
                                        required
                                    >

                                </div>


                                {{-- USERNAME --}}
                                <div class="form-group form-group--full">

                                    <label for="profilUsername">
                                        Username
                                    </label>

                                    <input
                                        type="text"
                                        id="profilUsername"
                                        name="username"
                                        value="{{ old('username', $admin->username) }}"
                                        placeholder="Masukkan username"
                                        required
                                    >

                                </div>


                                {{-- PASSWORD --}}
                                <div class="form-group form-group--full">

                                    <label for="profilPassword">
                                        Password Baru
                                        <span class="optional">(Opsional)</span>
                                    </label>

                                    <input
                                        type="password"
                                        id="profilPassword"
                                        name="password"
                                        autocomplete="new-password"
                                        placeholder="Kosongkan jika tidak ingin mengubah password"
                                    >

                                    <span class="field-sub">
                                        Kosongkan jika password tidak ingin diubah.
                                    </span>

                                </div>

                            </div>

                        </div>


                        <div class="card-foot">

                            <button
                                class="btn btn--primary"
                                type="submit"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path d="M5 4h11l3 3v13H5z"></path>
                                    <path d="M8 4v6h8V4"></path>
                                    <path d="M8 20v-6h8v6"></path>
                                </svg>

                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </section>

            </div>


            {{-- =====================================================
                 KOLOM 2: SESI PERANGKAT
                 ===================================================== --}}
            <div class="settings-col">

                <div class="section-heading">
                    <h2 class="section-heading-title">
                        Sesi Perangkat
                    </h2>
                </div>


                <section class="card">

                    <div class="device-list">

                        @forelse ($devices as $device)

                            <div class="device-row">

                                <div class="device-info">

                                    <div class="device-icon">

                                        @if ($device->is_desktop)

                                            <svg viewBox="0 0 24 24">
                                                <rect
                                                    x="2"
                                                    y="4"
                                                    width="20"
                                                    height="13"
                                                    rx="2"
                                                ></rect>

                                                <path d="M8 21h8M12 17v4"></path>
                                            </svg>

                                        @else

                                            <svg viewBox="0 0 24 24">
                                                <rect
                                                    x="6"
                                                    y="2"
                                                    width="12"
                                                    height="20"
                                                    rx="2"
                                                ></rect>

                                                <path d="M12 18h.01"></path>
                                            </svg>

                                        @endif

                                    </div>


                                    <div class="device-content">

                                        <div class="device-name">

                                            <span>
                                                {{ $device->platform ?: 'Sistem Tak Dikenal' }}

                                                &mdash;

                                                {{ $device->browser ?: 'Browser' }}
                                            </span>


                                            @if ($device->is_current_device)

                                                <span class="status-badge status-badge--approved">
                                                    Sesi ini
                                                </span>

                                            @endif

                                        </div>


                                        <p class="device-meta">

                                            IP:
                                            <span class="mono">
                                                {{ $device->ip_address }}
                                            </span>

                                            <span class="separator">
                                                &bull;
                                            </span>

                                            Aktif terakhir:
                                            {{ $device->last_activity }}

                                        </p>

                                    </div>

                                </div>


                                <div class="device-action">

                                    @if (!$device->is_current_device)

                                        <form
                                            action="{{ route('admin.devices.logout', $device->id) }}"
                                            method="POST"
                                            class="form-logout-device"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn--danger-soft"
                                            >
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M10 17l5-5-5-5"></path>
                                                    <path d="M15 12H3"></path>
                                                    <path d="M21 19V5a2 2 0 0 0-2-2h-6"></path>
                                                </svg>

                                                Keluarkan
                                            </button>

                                        </form>

                                    @else

                                        <span class="current-device">
                                            Perangkat saat ini
                                        </span>

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="empty-state">

                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24">
                                        <rect
                                            x="3"
                                            y="4"
                                            width="18"
                                            height="13"
                                            rx="2"
                                        ></rect>

                                        <path d="M8 21h8M12 17v4"></path>
                                    </svg>
                                </div>

                                <p class="empty-title">
                                    Tidak Ada Sesi Aktif
                                </p>

                                <p class="empty-desc">
                                    Belum ada perangkat yang terhubung ke akun ini.
                                </p>

                            </div>

                        @endforelse

                    </div>

                </section>

            </div>

        </div>


        {{-- =========================================================
             TOAST
             ========================================================= --}}
        <div
            class="toast-wrap"
            id="toastWrap"
        ></div>

    </div>

@endsection

@push('scripts')
    <script>
        window.__PROFIL_SESI_DATA__ = {
            flashSuccess: @json(session('success')),
            flashError: @json(session('error')),
            firstError: @json($errors->first())
        };
    </script>
    @vite('resources/js/pages/profil-sesi.js')
@endpush
