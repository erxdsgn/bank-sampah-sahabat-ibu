@extends('admin.layouts.app')

@section('title', 'Edit Kategori')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Edit Kategori')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>

            <h1 class="hero-title">
                Edit <span class="accent">Kategori</span>
            </h1>

            <p class="hero-sub">
                Perbarui data kategori "{{ $kategoriSampah->nama_kategori }}".
            </p>
        </div>
    </section>


    <section class="card">

        <form action="{{ route('admin.kategori-sampah.update', $kategoriSampah->id_kategori) }}" method="POST" class="form-panel">
            @csrf
            @method('PUT')

            <div class="form-grid">

                <!-- KATEGORI INDUK -->
                <div class="form-group form-group--full">
                    <label for="id_induk">Kategori Induk (opsional)</label>
                    <select
                        id="id_induk"
                        name="id_induk"
                        class="form-control @error('id_induk') is-invalid @enderror"
                    >
                        <option value="">— Tidak ada (kategori utama) —</option>
                        @foreach($kategoriInduk as $induk)
                            <option value="{{ $induk->id_kategori }}"
                                @selected(old('id_induk', $kategoriSampah->id_induk) == $induk->id_kategori)>
                                {{ $induk->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_induk')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- NAMA KATEGORI -->
                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori</label>
                    <input
                        type="text"
                        id="nama_kategori"
                        name="nama_kategori"
                        value="{{ old('nama_kategori', $kategoriSampah->nama_kategori) }}"
                        required
                        class="form-control @error('nama_kategori') is-invalid @enderror"
                    >
                    @error('nama_kategori')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- SATUAN -->
                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <input
                        type="text"
                        id="satuan"
                        name="satuan"
                        value="{{ old('satuan', $kategoriSampah->satuan) }}"
                        class="form-control @error('satuan') is-invalid @enderror"
                    >
                    @error('satuan')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>


            <!-- ACTIONS -->
            <div class="form-actions">

                <a href="{{ route('admin.kategori-sampah.index') }}" class="btn btn--ghost">
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
                    Simpan Perubahan
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
            background: #fff;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        select.form-control {
            cursor: pointer;
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

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

        });
    </script>

@endsection
