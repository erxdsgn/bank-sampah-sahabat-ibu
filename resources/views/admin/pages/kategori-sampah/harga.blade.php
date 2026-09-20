@extends('admin.layouts.app')

@section('title', 'Riwayat Harga')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Riwayat Harga')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>

            <h1 class="hero-title">
                Riwayat Harga — <span class="accent">{{ $kategoriSampah->nama_kategori }}</span>
            </h1>

            <p class="hero-sub">
                Kelola penetapan harga per {{ $kategoriSampah->satuan }} dari waktu ke waktu.
            </p>
        </div>
    </section>


    <!-- ============================= -->
    <!-- FORM TAMBAH HARGA              -->
    <!-- ============================= -->
    <section class="card mb-4">

        <form action="{{ route('admin.kategori-sampah.harga.store', $kategoriSampah->id_kategori) }}"
              method="POST" class="form-panel">
            @csrf

            <div class="form-grid">

                <!-- HARGA -->
                <div class="form-group">
                    <label for="harga_per_kg">Harga per {{ $kategoriSampah->satuan }} (Rp)</label>
                    <input
                        type="number"
                        step="0.01"
                        id="harga_per_kg"
                        name="harga_per_kg"
                        required
                        value="{{ old('harga_per_kg') }}"
                        placeholder="Contoh: 5000"
                        class="form-control @error('harga_per_kg') is-invalid @enderror"
                    >
                    @error('harga_per_kg')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>


                <!-- TANGGAL BERLAKU -->
                <div class="form-group">
                    <label for="tanggal_berlaku">Berlaku Mulai</label>
                    <input
                        type="date"
                        id="tanggal_berlaku"
                        name="tanggal_berlaku"
                        value="{{ old('tanggal_berlaku', now()->format('Y-m-d')) }}"
                        required
                        class="form-control @error('tanggal_berlaku') is-invalid @enderror"
                    >
                    @error('tanggal_berlaku')
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
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Harga
                </button>

            </div>

        </form>

    </section>


    <!-- ============================= -->
    <!-- TABEL RIWAYAT HARGA            -->
    <!-- ============================= -->
    <section class="card">

        <div class="table-toolbar">

            <div>
                <h3 class="card-title">Riwayat Penetapan Harga</h3>
                <p class="card-sub">Daftar harga yang pernah ditetapkan untuk kategori ini.</p>
            </div>

        </div>


        <div class="table-responsive">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Tanggal Berlaku</th>
                        <th>Harga per {{ $kategoriSampah->satuan }}</th>
                        <th>Diinput oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($riwayat as $harga)
                        <tr>

                            <td>
                                {{ $harga->tanggal_berlaku->format('d M Y') }}
                            </td>

                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($harga->harga_per_kg, 0, ',', '.') }}
                                </strong>
                            </td>

                            <td>
                                {{ $harga->admin->name ?? '-' }}
                            </td>

                            <td>
                                <div class="table-actions">

                                    <button
                                        class="icon-btn icon-btn--danger"
                                        title="Hapus Harga"
                                        type="button"
                                        data-id="{{ $harga->id_harga }}"
                                        data-harga="{{ $harga->harga_per_kg }}"
                                        data-tanggal="{{ $harga->tanggal_berlaku->format('d M Y') }}"
                                        data-url="{{ route('admin.kategori-sampah.harga.destroy', [$kategoriSampah->id_kategori, 'hargaSampah' => $harga->id_harga]) }}"
                                    >
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

                    @empty

                        <tr>

                            <td colspan="4" style="text-align: center; padding: 40px;">

                                <div style="font-size: 30px; margin-bottom: 10px;">
                                    💰
                                </div>

                                <strong>Belum Ada Riwayat Harga</strong>

                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada penetapan harga untuk kategori ini.
                                </p>

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="table-footer">
            <div class="table-info">
                Total riwayat:
                <strong>{{ $riwayat->count() }}</strong>
            </div>
        </div>

    </section>


    <!-- TOAST NOTIFIKASI -->
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

                <h3 class="confirm-title">Hapus Data Harga?</h3>

                <p class="confirm-text">
                    Anda akan menghapus data harga
                    <strong id="hapusHargaText">-</strong>.
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
           CARD / TABLE
        ========================= */

        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }

        .card-sub {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border, #e5e7eb);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
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

        /* =========================
           TOMBOL AKSI
        ========================= */

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
            white-space: nowrap;
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
           FORM
        ========================= */

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
            border-color: var(--primary, #4338ca);
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
        /*
        |--------------------------------------------------------------------------
        | State global
        |--------------------------------------------------------------------------
        */
        let hargaAkanDihapus = null;
        let hapusUrl = null;

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

            const iconPath = type === 'error'
                ? '<path d="M18 6 6 18M6 6l12 12"></path>'
                : '<path d="M20 6 9 17l-5-5"></path>';

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
        | Konfirmasi hapus
        |--------------------------------------------------------------------------
        */
        function konfirmasiHapus() {

            if (!hapusUrl) {
                showToast('Gagal menghapus', 'URL hapus tidak ditemukan.', 'error');
                return;
            }

            fetch(hapusUrl, {
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
                    showToast('Berhasil dihapus', 'Data harga telah dihapus.');
                    hargaAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Init saat DOM ready
        |--------------------------------------------------------------------------
        */
        document.addEventListener('DOMContentLoaded', function () {

            // === Notifikasi dari server ===
            @if ($errors->any())
                showToast('Gagal menyimpan', 'Periksa kembali isian form, ada data yang belum valid.', 'error');
            @endif

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif


            // === Event delegation: tombol hapus ===
            document.querySelectorAll('.icon-btn--danger[data-id]').forEach(function (btn) {
                btn.addEventListener('click', function () {

                    const id      = btn.dataset.id;
                    const harga   = btn.dataset.harga;
                    const tanggal = btn.dataset.tanggal;

                    hargaAkanDihapus = id;
                    hapusUrl = btn.dataset.url;

                    document.getElementById('hapusHargaText').textContent =
                        'Rp ' + Number(harga).toLocaleString('id-ID') + ' (' + tanggal + ')';

                    bukaModal('modalHapus');
                });
            });


            // === Tutup modal saat klik area gelap ===
            document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) {
                        overlay.classList.remove('active');
                    }
                });
            });


            // === Tutup modal dengan tombol Escape ===
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(function (o) {
                        o.classList.remove('active');
                    });
                }
            });

        });
    </script>

@endsection
