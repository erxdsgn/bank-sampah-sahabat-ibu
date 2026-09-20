@extends('admin.layouts.app')

@section('title', 'Kategori & Harga Sampah')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Kategori & Harga Sampah')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>

            <h1 class="hero-title">
                Kategori & <span class="accent">Harga Sampah</span>
            </h1>

            <p class="hero-sub">
                Kelola kategori sampah beserta riwayat dan penetapan harganya.
            </p>
        </div>

        <div class="hero-actions">
            <a href="{{ route('admin.kategori-sampah.create') }}" class="btn btn--primary">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Kategori
            </a>
        </div>
    </section>


    <section class="card">

        <!-- SEARCH -->
        <div class="table-toolbar">

            <div class="table-search">

                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>

                <input type="text" id="searchKategori" placeholder="Cari nama kategori atau satuan..."
                    autocomplete="off">

            </div>

            <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>

                Refresh
            </button>

        </div>


        <!-- TABLE -->
        <div class="table-responsive">

            <table class="data-table" id="kategoriTable">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Satuan</th>
                        <th>Harga Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($kategoriInduk as $index => $kategori)
                        <tr>

                            <!-- NO -->
                            <td>
                                {{ $index + 1 }}
                            </td>


                            <!-- NAMA KATEGORI -->
                            <td>
                                <strong>
                                    {{ $kategori->nama_kategori }}
                                </strong>
                            </td>


                            <!-- SATUAN -->
                            <td>
                                {{ $kategori->satuan }}
                            </td>


                            <!-- HARGA AKTIF -->
                            <td>
                                @if($kategori->hargaAktif)
                                    <strong class="saldo">
                                        Rp {{ number_format($kategori->hargaAktif->harga_per_kg, 0, ',', '.') }}
                                    </strong>
                                @else
                                    <span style="color:#9aa0a6;">Belum diatur</span>
                                @endif
                            </td>


                            <!-- AKSI -->
                            <td>

                                <div class="table-actions">

                                    <!-- KELOLA HARGA (HIJAU) -->
                                    <a href="{{ route('admin.kategori-sampah.harga.index', $kategori->id_kategori) }}"
                                        class="icon-btn icon-btn--harga" title="Kelola Harga">

                                        <svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14"></path>
                                            <span>Kelola Harga</span>
                                        </svg>

                                    </a>


                                    <!-- EDIT (KUNING) -->
                                    <a href="{{ route('admin.kategori-sampah.edit', $kategori->id_kategori) }}"
                                        class="icon-btn icon-btn--edit" title="Edit Kategori">

                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>

                                        <span>Edit</span>

                                    </a>


                                    <!-- HAPUS (MERAH) -->
                                    <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                        onclick='hapusKategori(@json($kategori))'>

                                        <svg viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6M14 11v6"></path>
                                        </svg>

                                        <span>Hapus</span>

                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- SUB KATEGORI -->
                        @foreach($kategori->children as $sub)
                            <tr style="background:rgba(0,0,0,0.015);">

                                <td></td>

                                <td style="padding-left: 28px; color:#6b7280;">
                                    <span style="opacity:.5;">↳</span> {{ $sub->nama_kategori }}
                                </td>

                                <td>{{ $sub->satuan }}</td>

                                <td>
                                    @if($sub->hargaAktif)
                                        <strong class="saldo">
                                            Rp {{ number_format($sub->hargaAktif->harga_per_kg, 0, ',', '.') }}
                                        </strong>
                                    @else
                                        <span style="color:#9aa0a6;">Belum diatur</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="table-actions">

                                        <!-- KELOLA HARGA (HIJAU) -->
                                        <a href="{{ route('admin.kategori-sampah.harga.index', $kategori->id_kategori) }}"
                                            class="icon-btn icon-btn--harga" title="Kelola Harga">
                                            Kelola Harga
                                        </a>

                                        <!-- EDIT (KUNING) -->
                                        <a href="{{ route('admin.kategori-sampah.edit', $sub->id_kategori) }}"
                                            class="icon-btn icon-btn--edit" title="Edit Kategori">Edit

                                            <svg viewBox="0 0 24 24">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                            </svg>

                                        </a>

                                        <!-- HAPUS (MERAH) -->
                                        <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                            onclick='hapusKategori(@json($sub))'>Delete

                                            <svg viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>

                                        </button>

                                    </div>
                                </td>

                            </tr>
                        @endforeach

                    @empty

                        <tr>

                            <td colspan="5" style="text-align: center; padding: 40px;">

                                <div style="font-size: 30px; margin-bottom: 10px;">
                                    📦
                                </div>

                                <strong>Belum Ada Data Kategori</strong>

                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada kategori sampah yang terdaftar.
                                </p>

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- FOOTER -->
        <div class="table-footer">

            <div class="table-info">

                Total kategori induk:

                <strong>
                    {{ $kategoriInduk->count() }}
                </strong>

            </div>

        </div>

    </section>


    <!-- ============================================= -->
    <!-- TOAST NOTIFIKASI                               -->
    <!-- ============================================= -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- ============================================= -->
    <!-- MODAL: KONFIRMASI HAPUS                        -->
    <!-- ============================================= -->
    <div class="modal-overlay" id="modalHapus">
        <div class="modal-box modal-box--sm">

            <div class="modal-body modal-body--center">

                <div class="confirm-icon">
                    <svg viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                        <path d="M10 11v6M14 11v6"></path>
                    </svg>
                </div>

                <h3 class="confirm-title">Hapus Kategori Sampah?</h3>

                <p class="confirm-text">
                    Anda akan menghapus kategori
                    <strong id="hapusNama">-</strong>.
                    Tindakan ini tidak dapat dibatalkan.
                </p>

            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Hapus</button>
            </div>

        </div>
    </div>


    <style>
        /* =========================
                   TABLE
                ========================= */

        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            padding: 16px 24px;
        }

        .table-search {
            width: 400px;
            position: relative;
        }

        .table-search svg {
            position: absolute;
            width: 18px;
            height: 18px;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .table-search input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 40px;
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
        }

        .table-search input:focus {
            border-color: var(--primary);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #edf0f2;
            font-size: 13px;
            color: #374151;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background: #fafafa;
        }

        .saldo {
            white-space: nowrap;
        }

        .table-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .table-actions .icon-btn {
            width: 100px;
            padding: 0 12px;
            height: 34px;
            border: 1px solid #e1e5e9;
            background: #fff;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s ease;

            font-size: 12.5px;
            font-weight: 600;
            white-space:nowrap;
            line-height: 1;
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            flex-shrink: 0;
        }

        /* ===== WARNA TOMBOL AKSI ===== */

        /* Kelola Harga - Hijau Soft */
        .table-actions .icon-btn--harga {
            color: #16a34a;
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .table-actions .icon-btn--harga:hover {
            background: #dcfce7;
            border-color: #86efac;
            color: #15803d;
        }

        /* Edit - Kuning Soft */
        .table-actions .icon-btn--edit {
            color: #ca8a04;
            background: #fefce8;
            border-color: #fde68a;
        }

        .table-actions .icon-btn--edit:hover {
            background: #fef9c3;
            border-color: #fcd34d;
            color: #a16207;
        }

        /* Hapus - Merah Soft */
        .table-actions .icon-btn--danger {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .table-actions .icon-btn--danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
        }

        .table-info {
            font-size: 13px;
            color: #6b7280;
        }


        /* =========================
                   MODAL
                ========================= */

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
            animation: modalPop .15s ease-out;
        }

        .modal-box--sm {
            max-width: 420px;
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: translateY(8px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-body {
            padding: 22px 24px;
        }

        .modal-body--center {
            text-align: center;
            padding-top: 28px;
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid #edf0f2;
        }

        .modal-foot--center {
            justify-content: center;
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

        /* --- Delete modal --- */
        .confirm-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirm-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .confirm-title {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .confirm-text {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
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
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchKategori');
            const table = document.getElementById('kategoriTable');

            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {

                    const keyword = this.value.toLowerCase().trim();
                    const rows = table.querySelectorAll('tbody tr');

                    rows.forEach(function(row) {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(keyword) ? '' : 'none';
                    });

                });
            }

            // Tutup modal saat klik area gelap di luar box
            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        overlay.classList.remove('active');
                    }
                });
            });

            // Tutup modal dengan tombol Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                        overlay.classList.remove('active');
                    });
                }
            });

        });


        /*
        |--------------------------------------------------------------------------
        | Helper: buka / tutup modal
        |--------------------------------------------------------------------------
        */

        function bukaModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }


        /*
        |--------------------------------------------------------------------------
        | Toast notifikasi
        |--------------------------------------------------------------------------
        */

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
        | Tombol Hapus -> popup
        masi hapus
        |--------------------------------------------------------------------------
        */

        let kategoriAkanDihapus = null;

        function hapusKategori(kategori) {

            kategoriAkanDihapus = kategori;

            document.getElementById('hapusNama').textContent = kategori.nama_kategori || '-';

            bukaModal('modalHapus');

        }

        function konfirmasiHapus() {

            if (!kategoriAkanDihapus) {
                return;
            }

            const id = kategoriAkanDihapus.id_kategori;
            const namaDihapus = kategoriAkanDihapus.nama_kategori || 'Kategori';

            fetch(`/admin/kategori-sampah/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menghapus data.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Berhasil dihapus', namaDihapus + ' telah dihapus dari data kategori.');
                    kategoriAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
    </script>

@endsection
