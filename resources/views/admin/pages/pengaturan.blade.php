@extends('admin.layouts.app')

@section('title', 'Pengaturan Sistem')
@section('active', 'pengaturan')
@section('crumbs', 'Konfigurasi | Pengaturan Sistem')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">KONFIGURASI</span>

            <h1 class="hero-title">
                Pengaturan <span class="accent">Sistem</span>
            </h1>

            <p class="hero-sub">
                Kelola informasi profil admin, keamanan perangkat, dan pengaturan dasar aplikasi Bank Sampah.
            </p>
        </div>

        <div class="hero-actions">
            <div class="stat-chip">
                <span class="stat-dot stat-dot--green"></span>
                <div>
                    <div class="stat-num">{{ count($devices) }}</div>
                    <div class="stat-label">Sesi Aktif</div>
                </div>
            </div>
        </div>
    </section>


    <div class="settings-grid">

        <!-- ============================================= -->
        <!-- CARD 1: PROFIL ADMIN                           -->
        <!-- ============================================= -->
        <section class="card">

            <div class="card-head">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profil Admin
                </h3>
                <p class="card-sub">Perbarui nama, username, atau password akun Anda.</p>
            </div>

            <form id="formProfil" onsubmit="return simpanPengaturan(event)">

                <div class="card-body">
                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <label for="profilNama">Nama Lengkap</label>
                            <input type="text" id="profilNama" name="nama" value="{{ $admin->nama }}" required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="profilUsername">Username</label>
                            <input type="text" id="profilUsername" name="username" value="{{ $admin->username }}"
                                required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="profilPassword">Password Baru (Opsional)</label>
                            <input type="password" id="profilPassword" name="password" autocomplete="new-password"
                                placeholder="Kosongkan jika tidak ingin mengubah password">
                            <span class="field-sub">Gunakan kombinasi huruf dan angka agar lebih aman.</span>
                        </div>

                    </div>
                </div>

                <div class="card-foot">
                    <button class="btn btn--primary" type="submit">Simpan Profil</button>
                </div>

            </form>
        </section>


        <!-- ============================================= -->
        <!-- CARD 2: PENGATURAN APLIKASI                    -->
        <!-- ============================================= -->
        <section class="card">

            <div class="card-head">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h0a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg>
                    Pengaturan Aplikasi
                </h3>
                <p class="card-sub">Sesuaikan identitas dari sistem Bank Sampah.</p>
            </div>

            <form id="formAplikasi" onsubmit="return simpanPengaturan(event)">

                <div class="card-body">
                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <label for="appNama">Nama Bank Sampah</label>
                            <input type="text" id="appNama" value="Bank Sampah Sahabat Ibu" readonly>
                            <span class="field-sub">Nama bank sampah tidak dapat diubah dari halaman ini.</span>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="appAlamat">Alamat Kantor / Pusat</label>
                            <textarea id="appAlamat" name="alamat" rows="3">Jl. Contoh Alamat No. 123, Desa Sejahtera</textarea>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="appHp">Nomor HP / WhatsApp Admin</label>
                            <input type="text" id="appHp" name="no_hp" value="081234567890">
                        </div>

                    </div>
                </div>

                <div class="card-foot">
                    <button class="btn btn--primary" type="submit">Simpan Pengaturan</button>
                </div>

            </form>
        </section>


        <!-- ============================================= -->
        <!-- CARD 3: SESI PERANGKAT LOGIN (full width)      -->
        <!-- ============================================= -->
        <section class="card card--wide">

            <div class="card-head">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="4" width="20" height="13" rx="2"></rect>
                        <path d="M8 21h8M12 17v4"></path>
                    </svg>
                    Sesi Perangkat Login
                </h3>
                <p class="card-sub">Daftar perangkat yang saat ini memiliki akses terhubung ke akun ini.</p>
            </div>

            <div class="device-list">
                @forelse ($devices as $device)
                    <div class="device-row">

                        <div class="device-info">

                            <div class="device-icon">
                                @if ($device->is_desktop)
                                    <svg viewBox="0 0 24 24">
                                        <rect x="2" y="4" width="20" height="13" rx="2"></rect>
                                        <path d="M8 21h8M12 17v4"></path>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24">
                                        <rect x="6" y="2" width="12" height="20" rx="2"></rect>
                                        <path d="M12 18h.01"></path>
                                    </svg>
                                @endif
                            </div>

                            <div>
                                <div class="device-name">
                                    <span>
                                        {{ $device->platform ?: 'Sistem Tak Dikenal' }} —
                                        {{ $device->browser ?: 'Browser' }}
                                    </span>
                                    @if ($device->is_current_device)
                                        <span class="status-badge status-badge--approved">Sesi ini</span>
                                    @endif
                                </div>
                                <p class="device-meta">
                                    IP: <span class="mono">{{ $device->ip_address }}</span> &bull;
                                    Aktif terakhir: {{ $device->last_activity }}
                                </p>
                            </div>

                        </div>

                        <div>
                            @if (!$device->is_current_device)
                                <form action="{{ route('admin.devices.logout', $device->id) }}" method="POST"
                                    onsubmit="return confirm('Keluarkan perangkat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--danger-soft">Keluarkan</button>
                                </form>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">💻</div>
                        <strong>Tidak Ada Sesi Aktif</strong>
                        <p>Belum ada perangkat yang terhubung ke akun ini.</p>
                    </div>
                @endforelse
            </div>

        </section>

    </div>


    <!-- TOAST NOTIFIKASI -->
    <div class="toast-wrap" id="toastWrap"></div>


    <style>
        /* =========================================================
           TEMA BERSAMA - disalin dari Verifikasi Setoran / Data Warga
           supaya identik
           ========================================================= */

        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
        }

        .mono {
            font-family: monospace;
            font-size: 12px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary, #4338ca);
        }

        .form-group textarea {
            resize: vertical;
        }

        .field-sub {
            font-size: 11.5px;
            color: #9ca3af;
        }

        .btn--danger {
            background: #dc2626;
            color: #fff;
            border: 1px solid #dc2626;
        }

        .btn--danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .stat-chip {
            background: var(--surface, #fff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 130px;
        }

        .stat-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .stat-dot--green {
            background: #16a34a;
        }

        .stat-num {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
            color: #1f2937;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge--approved {
            background: #e9f9ee;
            color: #15803d;
            border: 1px solid #bfead0;
        }

        /* ---------- Toast ---------- */

        .toast-wrap {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 300px;
            max-width: 380px;
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
            padding: 14px 16px;
            pointer-events: auto;
            animation: toastIn .18s ease-out;
        }

        .toast.toast--error {
            border-left-color: #dc2626;
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast.toast--error .toast-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .toast-icon svg {
            width: 13px;
            height: 13px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 2px;
        }

        .toast-text {
            font-size: 12.5px;
            color: #6b7280;
            margin: 0;
            line-height: 1.5;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: #9ca3af;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
        }

        .toast-close:hover {
            color: #111827;
        }

        .toast.toast--leaving {
            animation: toastOut .18s ease-in forwards;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(16px);
            }
        }

        @media (max-width: 480px) {
            .toast-wrap {
                left: 16px;
                right: 16px;
                top: 16px;
            }

            .toast {
                min-width: 0;
                max-width: none;
                width: 100%;
            }
        }

        /* =========================================================
           TAMBAHAN KHUSUS PENGATURAN
           ========================================================= */

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            align-items: start;
        }

        .card--wide {
            grid-column: 1 / -1;
        }

        .card-head {
            padding: 20px 24px;
            border-bottom: 1px solid #edf0f2;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card-sub {
            margin: 4px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        .card-body {
            padding: 22px 24px;
        }

        .card-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid #edf0f2;
        }

        .form-group input[readonly] {
            background: #f8fafc;
            color: #6b7280;
            cursor: not-allowed;
        }

        .btn--danger-soft {
            background: #fdeced;
            color: #c0293c;
            border: 1px solid #f4c2c8;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
        }

        .btn--danger-soft:hover {
            background: #fbd9dc;
        }

        .device-list {
            padding: 4px 24px;
        }

        .device-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #edf0f2;
        }

        .device-row:last-child {
            border-bottom: none;
        }

        .device-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .device-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .device-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .device-name {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        .device-name .status-badge {
            padding: 3px 9px;
            font-size: 11.5px;
        }

        .device-meta {
            margin: 4px 0 0;
            font-size: 12.5px;
            color: #6b7280;
        }

        .device-meta .mono {
            color: #374151;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            font-size: 13px;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .empty-state p {
            margin: 5px 0 0;
            color: #6b7280;
        }

        @media (max-width: 900px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                width: 100%;
            }

            .stat-chip {
                flex: 1;
            }

            .device-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

        });


        /* ---------- Helper ---------- */

        function showToast(title, text, type = 'success') {

            const wrap = document.getElementById('toastWrap');

            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon">
                    <svg viewBox="0 0 24 24">${iconPath}</svg>
                </div>
                <div class="toast-body">
                    <p class="toast-title">${title}</p>
                    <p class="toast-text">${text}</p>
                </div>
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            function hapusToast() {
                toast.classList.add('toast--leaving');
                setTimeout(() => toast.remove(), 180);
            }

            toast.querySelector('.toast-close').addEventListener('click', hapusToast);

            wrap.appendChild(toast);

            setTimeout(hapusToast, 3500);
        }


        /*
        |--------------------------------------------------------------------------
        | Simpan pengaturan
        |--------------------------------------------------------------------------
        | Sementara masih menampilkan notifikasi saja (sama seperti sebelumnya).
        | Ganti dengan fetch() ke route simpan saat backend-nya sudah tersedia.
        */

        function simpanPengaturan(event) {
            event.preventDefault();

            showToast('Berhasil disimpan', 'Perubahan pengaturan berhasil diperbarui.');

            return false;
        }
    </script>

@endsection
