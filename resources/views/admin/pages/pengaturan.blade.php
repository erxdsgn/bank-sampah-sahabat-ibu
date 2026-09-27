@extends('admin.layouts.app')

@section('title', 'Profil & Sesi Perangkat')
@section('active', 'pengaturan')
@section('crumbs', 'Konfigurasi | Profil & Sesi Perangkat')

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
                                        Nama Lengkap
                                    </label>

                                    <input
                                        type="text"
                                        id="profilNama"
                                        name="nama"
                                        value="{{ old('nama', $admin->nama) }}"
                                        placeholder="Masukkan nama lengkap"
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


    <style>
        /* =========================================================
           PROFIL & SESI PERANGKAT
           Dashboard Dark Theme
           ========================================================= */

        .profil-page {

            --page-bg: #f5f7fb;

            --surface: #ffffff;
            --surface-secondary: #f8fafc;

            --text: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;

            --border: #e5e7eb;

            --input-bg: #ffffff;
            --input-readonly: #f8fafc;

            --primary-safe: #16a34a;
            --primary-safe-hover: #15803d;

            --primary-soft: #eef2ff;
            --primary-soft-text: #4338ca;

            --success-bg: #dcfce7;
            --success-text: #15803d;

            --danger-bg: #fee2e2;
            --danger-text: #dc2626;
            --danger-border: #fecaca;

            --shadow: 0 8px 25px rgba(15, 23, 42, .06);

            color: var(--text);
        }


        html[data-theme="dark"] .profil-page {

            --page-bg: #0b1220;

            --surface: #151d2f;
            --surface-secondary: #1b2438;

            --text: #f1f5f9;
            --text-secondary: #aab6c8;
            --text-muted: #748198;

            --border: #29364d;

            --input-bg: #111a2c;
            --input-readonly: #111a2c;

            --primary-safe: #22c55e;
            --primary-safe-hover: #16a34a;

            --primary-soft: rgba(99, 102, 241, .14);
            --primary-soft-text: #a5b4fc;

            --success-bg: rgba(34, 197, 94, .14);
            --success-text: #4ade80;

            --danger-bg: rgba(239, 68, 68, .14);
            --danger-text: #f87171;
            --danger-border: rgba(239, 68, 68, .28);

            --shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }


        .profil-page input,
        .profil-page select,
        .profil-page textarea {
            color-scheme: light;
        }


        html[data-theme="dark"] .profil-page input,
        html[data-theme="dark"] .profil-page select,
        html[data-theme="dark"] .profil-page textarea {
            color-scheme: dark;
        }


        /* =========================================================
           SETTINGS GRID
           ========================================================= */

        .profil-page .settings-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr);

            gap: 20px;

            align-items: start;
        }


        .profil-page .settings-col {
            min-width: 0;
        }


        /* =========================================================
           SECTION HEADING
           ========================================================= */

        .profil-page .section-heading {
            margin-bottom: 12px;
        }


        .profil-page .section-heading-title {

            margin: 0;

            color: var(--text);

            font-size: 15px;
            font-weight: 700;

            line-height: 1.4;
        }


        /* =========================================================
           CARD
           ========================================================= */

        .profil-page .card {

            background: var(--surface);

            border: 1px solid var(--border);

            border-radius: 14px;

            overflow: hidden;

            box-shadow: var(--shadow);
        }


        .profil-page .card-body {
            padding: 22px 24px;
        }


        .profil-page .card-foot {

            display: flex;

            justify-content: flex-end;
            align-items: center;

            gap: 10px;

            padding: 16px 24px;

            background: var(--surface-secondary);

            border-top: 1px solid var(--border);
        }


        /* =========================================================
           SECTION TITLE
           ========================================================= */

        .profil-page .section-title {

            display: flex;
            align-items: center;

            gap: 10px;

            margin-bottom: 18px;
        }


        .profil-page .section-title-icon {

            width: 32px;
            height: 32px;

            flex: 0 0 32px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: var(--primary-soft);
            color: var(--primary-soft-text);
        }


        .profil-page .section-title-icon svg {

            width: 16px;
            height: 16px;

            fill: none;

            stroke: currentColor;
            stroke-width: 1.8;

            stroke-linecap: round;
            stroke-linejoin: round;
        }


        .profil-page .section-title strong {

            display: block;

            color: var(--text);

            font-size: 13px;
            font-weight: 700;
        }


        .profil-page .section-title span {

            display: block;

            margin-top: 2px;

            color: var(--text-muted);

            font-size: 11.5px;
            line-height: 1.45;
        }


        /* =========================================================
           FORM
           ========================================================= */

        .profil-page .form-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr);

            gap: 16px 18px;
        }


        .profil-page .form-group {

            display: flex;
            flex-direction: column;

            gap: 6px;
        }


        .profil-page .form-group--full {
            grid-column: 1 / -1;
        }


        .profil-page .form-group label {

            color: var(--text);

            font-size: 12px;
            font-weight: 700;
        }


        .profil-page .form-group input,
        .profil-page .form-group select,
        .profil-page .form-group textarea {

            width: 100%;

            box-sizing: border-box;

            padding: 9px 12px;

            border: 1px solid var(--border);
            border-radius: 8px;

            background: var(--input-bg);

            color: var(--text);

            outline: none;

            font-family: inherit;
            font-size: 13px;

            transition:
                border-color .15s ease,
                box-shadow .15s ease;
        }


        .profil-page .form-group input {
            height: 40px;
        }


        .profil-page .form-group textarea {

            min-height: 85px;

            resize: vertical;
        }


        .profil-page .form-group input:focus,
        .profil-page .form-group select:focus,
        .profil-page .form-group textarea:focus {

            border-color: var(--primary-safe);

            box-shadow:
                0 0 0 3px rgba(34, 197, 94, .10);
        }


        .profil-page .form-group input::placeholder,
        .profil-page .form-group textarea::placeholder {
            color: var(--text-muted);
        }


        .profil-page .form-group input[readonly] {

            background: var(--input-readonly);

            color: var(--text-secondary);

            cursor: not-allowed;
        }


        .profil-page .field-sub {

            color: var(--text-muted);

            font-size: 11.5px;

            line-height: 1.4;
        }


        .profil-page .optional {

            color: var(--text-muted);

            font-size: 10.5px;
            font-weight: 500;
        }


        /* =========================================================
           BUTTON
           ========================================================= */

        .profil-page .btn {

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 8px;

            height: 38px;

            padding: 0 16px;

            border-radius: 8px;

            border: 1px solid transparent;

            font-family: inherit;

            font-size: 13px;
            font-weight: 600;

            cursor: pointer;

            transition:
                background .15s ease,
                border-color .15s ease,
                color .15s ease,
                transform .15s ease;
        }


        .profil-page .btn svg {

            width: 16px;
            height: 16px;

            fill: none;

            stroke: currentColor;
            stroke-width: 1.8;

            stroke-linecap: round;
            stroke-linejoin: round;
        }


        .profil-page .btn--primary {

            background: var(--primary-safe);

            color: #ffffff;

            border-color: var(--primary-safe);
        }


        .profil-page .btn--primary:hover {

            background: var(--primary-safe-hover);

            border-color: var(--primary-safe-hover);
        }


        .profil-page .btn--danger-soft {

            background: var(--danger-bg);

            color: var(--danger-text);

            border-color: var(--danger-border);
        }


        .profil-page .btn--danger-soft:hover {

            background: rgba(239, 68, 68, .20);

            border-color: rgba(239, 68, 68, .38);
        }


        /* =========================================================
           DEVICE LIST
           ========================================================= */

        .profil-page .device-list {
            padding: 4px 24px;
        }


        .profil-page .device-row {

            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 14px;

            padding: 16px 0;

            border-bottom: 1px solid var(--border);
        }


        .profil-page .device-row:last-child {
            border-bottom: none;
        }


        .profil-page .device-info {

            min-width: 0;

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .profil-page .device-icon {

            width: 38px;
            height: 38px;

            flex: 0 0 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: var(--surface-secondary);

            border: 1px solid var(--border);

            color: var(--text-secondary);
        }


        .profil-page .device-icon svg {

            width: 18px;
            height: 18px;

            fill: none;

            stroke: currentColor;
            stroke-width: 1.8;

            stroke-linecap: round;
            stroke-linejoin: round;
        }


        .profil-page .device-content {
            min-width: 0;
        }


        .profil-page .device-name {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 7px;

            color: var(--text);

            font-size: 13px;
            font-weight: 700;
        }


        .profil-page .device-name > span:first-child {
            overflow-wrap: anywhere;
        }


        .profil-page .device-meta {

            margin: 3px 0 0;

            color: var(--text-secondary);

            font-size: 11.5px;

            line-height: 1.5;
        }


        .profil-page .separator {
            color: var(--text-muted);
        }


        .profil-page .mono {

            color: var(--text-secondary);

            font-family: monospace;

            font-size: 11px;
        }


        .profil-page .device-action {
            flex-shrink: 0;
        }


        .profil-page .form-logout-device {
            margin: 0;
        }


        /* =========================================================
           STATUS
           ========================================================= */

        .profil-page .status-badge {

            display: inline-flex;

            align-items: center;

            padding: 4px 9px;

            border-radius: 999px;

            font-size: 10.5px;
            font-weight: 700;

            white-space: nowrap;
        }


        .profil-page .status-badge--approved {

            background: var(--success-bg);

            color: var(--success-text);

            border: 1px solid rgba(34, 197, 94, .20);
        }


        .profil-page .current-device {

            display: inline-flex;

            align-items: center;

            padding: 6px 10px;

            border-radius: 8px;

            background: var(--success-bg);

            color: var(--success-text);

            border: 1px solid rgba(34, 197, 94, .20);

            font-size: 10.5px;
            font-weight: 700;

            white-space: nowrap;
        }


        /* =========================================================
           EMPTY STATE
           ========================================================= */

        .profil-page .empty-state {

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            min-height: 220px;

            padding: 40px 20px;

            text-align: center;
        }


        .profil-page .empty-icon {

            width: 48px;
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 12px;

            border-radius: 12px;

            background: var(--surface-secondary);

            color: var(--text-muted);
        }


        .profil-page .empty-icon svg {

            width: 24px;
            height: 24px;

            fill: none;

            stroke: currentColor;
            stroke-width: 1.7;

            stroke-linecap: round;
            stroke-linejoin: round;
        }


        .profil-page .empty-title {

            margin: 0;

            color: var(--text);

            font-size: 13.5px;
            font-weight: 700;
        }


        .profil-page .empty-desc {

            margin: 5px 0 0;

            color: var(--text-secondary);

            font-size: 12px;
            line-height: 1.5;
        }


        /* =========================================================
           TOAST
           ========================================================= */

        .profil-page .toast-wrap {

            position: fixed;

            top: 20px;
            right: 20px;

            z-index: 1100;

            display: flex;

            flex-direction: column;

            gap: 10px;

            pointer-events: none;
        }


        .profil-page .toast {

            display: flex;

            align-items: flex-start;

            gap: 12px;

            min-width: 300px;
            max-width: 380px;

            padding: 14px 16px;

            background: var(--surface);

            border: 1px solid var(--border);

            border-left: 4px solid var(--success-text);

            border-radius: 10px;

            box-shadow: 0 12px 30px rgba(0, 0, 0, .18);

            pointer-events: auto;

            animation: profilToastIn .18s ease-out;
        }


        .profil-page .toast.toast--error {
            border-left-color: var(--danger-text);
        }


        .profil-page .toast-icon {

            width: 22px;
            height: 22px;

            flex: 0 0 22px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: var(--success-bg);

            color: var(--success-text);
        }


        .profil-page .toast.toast--error .toast-icon {

            background: var(--danger-bg);

            color: var(--danger-text);
        }


        .profil-page .toast-icon svg {

            width: 13px;
            height: 13px;

            fill: none;

            stroke: currentColor;
            stroke-width: 2.4;
        }


        .profil-page .toast-body {
            flex: 1;
            min-width: 0;
        }


        .profil-page .toast-title {

            margin: 0 0 2px;

            color: var(--text);

            font-size: 13px;
            font-weight: 700;
        }


        .profil-page .toast-text {

            margin: 0;

            color: var(--text-secondary);

            font-size: 12.5px;

            line-height: 1.5;

            overflow-wrap: anywhere;
        }


        .profil-page .toast-close {

            flex-shrink: 0;

            padding: 0;

            border: none;

            background: transparent;

            color: var(--text-muted);

            font-size: 18px;

            line-height: 1;

            cursor: pointer;
        }


        .profil-page .toast-close:hover {
            color: var(--text);
        }


        .profil-page .toast.toast--leaving {
            animation: profilToastOut .18s ease-in forwards;
        }


        @keyframes profilToastIn {
            from {
                opacity: 0;
                transform: translateX(16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }


        @keyframes profilToastOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(16px);
            }
        }


        /* =========================================================
           RESPONSIVE
           ========================================================= */

        @media (max-width: 900px) {

            .profil-page .settings-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 640px) {

            .profil-page .form-grid {
                grid-template-columns: 1fr;
            }


            .profil-page .form-group--full {
                grid-column: auto;
            }


            .profil-page .card-body {
                padding: 20px 18px;
            }


            .profil-page .card-foot {
                padding: 14px 18px;
            }


            .profil-page .device-list {
                padding: 4px 18px;
            }

        }


        @media (max-width: 560px) {

            .profil-page .device-row {

                flex-direction: column;

                align-items: flex-start;
            }


            .profil-page .device-info {
                width: 100%;
            }


            .profil-page .device-action {
                width: 100%;
            }


            .profil-page .form-logout-device {
                width: 100%;
            }


            .profil-page .form-logout-device .btn--danger-soft {
                width: 100%;
            }


            .profil-page .current-device {
                width: 100%;

                justify-content: center;

                box-sizing: border-box;
            }

        }


        @media (max-width: 480px) {

            .profil-page .toast-wrap {

                left: 16px;
                right: 16px;
                top: 16px;
            }


            .profil-page .toast {

                min-width: 0;

                width: 100%;
                max-width: none;

                box-sizing: border-box;
            }

        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast(
                    'Berhasil',
                    @json(session('success'))
                );
            @endif


            @if (session('error'))
                showToast(
                    'Gagal',
                    @json(session('error')),
                    'error'
                );
            @endif


            @if ($errors->any())
                showToast(
                    'Gagal',
                    @json($errors->first()),
                    'error'
                );
            @endif


            document
                .querySelectorAll('.profil-page .form-logout-device')
                .forEach(function(form) {

                    form.addEventListener('submit', function(event) {

                        const yakin = confirm(
                            'Apakah Anda yakin ingin mengeluarkan perangkat ini?'
                        );

                        if (!yakin) {
                            event.preventDefault();
                        }

                    });

                });

        });


        function showToast(title, text, type = 'success') {

            const wrap = document.getElementById('toastWrap');

            if (!wrap) return;


            const toast = document.createElement('div');

            toast.className =
                'toast' +
                (type === 'error' ? ' toast--error' : '');


            const iconPath = type === 'error'
                ?
                '<path d="M18 6 6 18M6 6l12 12"></path>'
                :
                '<path d="M20 6 9 17l-5-5"></path>';


            toast.innerHTML = `
                <div class="toast-icon">
                    <svg viewBox="0 0 24 24">
                        ${iconPath}
                    </svg>
                </div>

                <div class="toast-body">
                    <p class="toast-title">${escapeToastHtml(title)}</p>
                    <p class="toast-text">${escapeToastHtml(text)}</p>
                </div>

                <button
                    class="toast-close"
                    type="button"
                    aria-label="Tutup"
                >
                    &times;
                </button>
            `;


            function hapusToast() {

                toast.classList.add('toast--leaving');

                setTimeout(function() {
                    toast.remove();
                }, 180);
            }


            const closeButton =
                toast.querySelector('.toast-close');


            if (closeButton) {
                closeButton.addEventListener(
                    'click',
                    hapusToast
                );
            }


            wrap.appendChild(toast);


            setTimeout(
                hapusToast,
                3500
            );
        }


        function escapeToastHtml(value) {

            const div = document.createElement('div');

            div.textContent =
                value === null || value === undefined
                    ? ''
                    : String(value);

            return div.innerHTML;
        }
    </script>

@endsection
