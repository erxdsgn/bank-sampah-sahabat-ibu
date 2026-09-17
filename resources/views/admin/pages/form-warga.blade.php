@extends('admin.layouts.app')

@section('title', 'Tambah Warga')
@section('active', 'warga')
@section('crumbs', 'Master Data | Tambah Warga')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>

            <h1 class="hero-title">
                Tambah <span class="accent">Warga</span>
            </h1>

            <p class="hero-sub">
                Tambahkan data masyarakat baru sebagai anggota Bank Sampah.
            </p>
        </div>
    </section>


    <section class="card">

        <form action="{{ route('admin.warga.store') }}" method="POST" class="form-panel">
            @csrf

            <div class="form-grid">

                <!-- NIK -->
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input
                        type="text"
                        id="nik"
                        name="nik"
                        value="{{ old('nik') }}"
                        placeholder="Masukkan NIK"
                        required
                        class="form-control @error('nik') is-invalid @enderror"
                    >
                    @error('nik')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- NAMA -->
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama') }}"
                        placeholder="Masukkan nama warga"
                        required
                        class="form-control @error('nama') is-invalid @enderror"
                    >
                    @error('nama')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- NO HP -->
                <div class="form-group">
                    <label for="no_hp">No. HP</label>
                    <input
                        type="text"
                        id="no_hp"
                        name="no_hp"
                        value="{{ old('no_hp') }}"
                        placeholder="Masukkan nomor HP"
                        class="form-control @error('no_hp') is-invalid @enderror"
                    >
                    @error('no_hp')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- JUMLAH ANGGOTA KELUARGA -->
                <div class="form-group">
                    <label for="jumlah_anggota_keluarga">Jumlah Anggota Keluarga</label>
                    <input
                        type="number"
                        id="jumlah_anggota_keluarga"
                        name="jumlah_anggota_keluarga"
                        value="{{ old('jumlah_anggota_keluarga', 0) }}"
                        min="0"
                        class="form-control @error('jumlah_anggota_keluarga') is-invalid @enderror"
                    >
                    @error('jumlah_anggota_keluarga')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- ALAMAT (full width) -->
                <div class="form-group form-group--full">
                    <label for="alamat">Alamat</label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        placeholder="Masukkan alamat"
                        rows="3"
                        class="form-control @error('alamat') is-invalid @enderror"
                    >{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>


            <!-- ACTIONS -->
            <div class="form-actions">

                <a href="{{ route('admin.warga') }}" class="btn btn--ghost">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 12H5"></path>
                        <path d="m12 19-7-7 7-7"></path>
                    </svg>
                    Kembali
                </a>

                <button type="submit" class="btn btn--primary">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <path d="M17 21v-8H7v8"></path>
                        <path d="M7 3v5h8"></path>
                    </svg>
                    Simpan Warga
                </button>

            </div>

        </form>

    </section>


    <!-- TOAST NOTIFIKASI -->
    <div class="toast-wrap" id="toastWrap"></div>


    <style>
        .form-panel {
            padding: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 13px;
            color: #374151;
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
            transition: border-color .15s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        textarea.form-control {
            resize: vertical;
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .form-error {
            margin-top: 6px;
            font-size: 12px;
            color: #dc2626;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 20px 24px;
            border-top: 1px solid var(--border, #e5e7eb);
            margin: 24px -24px -24px;
        }

        .form-actions .btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* =========================
           TOAST
        ========================= */

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
    </style>


    <script>
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

        document.addEventListener('DOMContentLoaded', function () {

            @if ($errors->any())
                showToast('Gagal menyimpan', 'Periksa kembali isian form, ada data yang belum valid.', 'error');
            @endif

        });
    </script>

@endsection
