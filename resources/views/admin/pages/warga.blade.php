@extends('admin.layouts.app')

@section('title', 'Data Warga')
@section('active', 'warga')
@section('crumbs', 'Master Data | Data Warga')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>

            <h1 class="hero-title">
                Data <span class="accent">Warga</span>
            </h1>

            <p class="hero-sub">
                Kelola data masyarakat yang telah terdaftar sebagai anggota
                Bank Sampah.
            </p>
        </div>

        <div class="hero-actions">
            <a href="{{ route('admin.warga.form-warga') }}" class="btn btn--primary">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Warga
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

                <input type="text" id="searchWarga" placeholder="Cari NIK, nama, nomor HP, atau alamat..."
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

            <table class="data-table" id="wargaTable">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Anggota Keluarga</th>
                        <th>Saldo</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($warga as $index => $item)
                        <tr>

                            <!-- NO -->
                            <td>
                                {{ $index + 1 }}
                            </td>


                            <!-- NIK -->
                            <td>
                                <span class="mono">
                                    {{ $item->nik }}
                                </span>
                            </td>


                            <!-- NAMA -->
                            <td>
                                <strong>
                                    {{ $item->nama }}
                                </strong>
                            </td>


                            <!-- NO HP -->
                            <td>
                                {{ $item->no_hp ?? '-' }}
                            </td>


                            <!-- ALAMAT -->
                            <td>
                                {{ $item->alamat ?? '-' }}
                            </td>


                            <!-- JUMLAH ANGGOTA KELUARGA -->
                            <td>
                                {{ $item->jumlah_anggota_keluarga ?? 0 }}
                                orang
                            </td>


                            <!-- SALDO -->
                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>


                            <!-- TANGGAL DAFTAR -->
                            <td>
                                @if ($item->tanggal_daftar)
                                    {{ \Carbon\Carbon::parse($item->tanggal_daftar)->translatedFormat('d F Y') }}
                                @else
                                    -
                                @endif
                            </td>


                            <!-- AKSI -->
                            <td>

                                <div class="table-actions">

                                    <!-- DETAIL -->
                                    <button class="icon-btn" title="Lihat Detail" type="button"
                                        onclick='lihatWarga(@json($item))'>

                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>

                                    </button>


                                    <!-- EDIT -->
                                    <button class="icon-btn" title="Edit Data" type="button"
                                        onclick='editWarga(@json($item))'>

                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>

                                    </button>


                                    <!-- HAPUS -->
                                    <button class="icon-btn danger" title="Hapus Data" type="button"
                                        onclick='hapusWarga(@json($item))'>

                                        <svg viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6M14 11v6"></path>
                                        </svg>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9" style="text-align: center; padding: 40px;">

                                <div style="font-size: 30px; margin-bottom: 10px;">
                                    📋
                                </div>

                                <strong>Belum Ada Data Warga</strong>

                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada masyarakat yang terdaftar di Bank Sampah.
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

                Total warga:

                <strong>
                    {{ $warga->count() }}
                </strong>

                orang

            </div>

        </div>

    </section>


    <!-- ============================================= -->
    <!-- TOAST NOTIFIKASI                               -->
    <!-- ============================================= -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- ============================================= -->
    <!-- MODAL: DETAIL WARGA                            -->
    <!-- ============================================= -->
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                    Detail Warga
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">

                <div class="detail-avatar-row">
                    <div class="detail-avatar" id="detailAvatar">-</div>
                    <div>
                        <div class="detail-nama" id="detailNama">-</div>
                        <div class="detail-nik mono" id="detailNik">-</div>
                    </div>
                </div>

                <div class="detail-grid">

                    <div class="detail-item">
                        <span class="detail-label">No. HP</span>
                        <span class="detail-value" id="detailHp">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Anggota Keluarga</span>
                        <span class="detail-value" id="detailKeluarga">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Saldo</span>
                        <span class="detail-value saldo" id="detailSaldo">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Tanggal Daftar</span>
                        <span class="detail-value" id="detailTanggal">-</span>
                    </div>

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Alamat</span>
                        <span class="detail-value" id="detailAlamat">-</span>
                    </div>

                </div>

            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>

        </div>
    </div>


    <!-- ============================================= -->
    <!-- MODAL: EDIT WARGA                              -->
    <!-- ============================================= -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                    Edit Data Warga
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalEdit')">&times;</button>
            </div>

            <form id="formEditWarga" onsubmit="return simpanWarga(event)">

                <div class="modal-body">

                    <input type="hidden" id="editId">

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="editNik">NIK</label>
                            <input type="text" id="editNik" maxlength="16" required>
                        </div>

                        <div class="form-group">
                            <label for="editNama">Nama Lengkap</label>
                            <input type="text" id="editNama" required>
                        </div>

                        <div class="form-group">
                            <label for="editHp">No. HP</label>
                            <input type="text" id="editHp">
                        </div>

                        <div class="form-group">
                            <label for="editKeluarga">Anggota Keluarga</label>
                            <input type="number" id="editKeluarga" min="0">
                        </div>

                        <div class="form-group">
                            <label for="editSaldo">Saldo (Rp)</label>
                            <input type="number" id="editSaldo" min="0">
                        </div>

                        <div class="form-group">
                            <label for="editTanggal">Tanggal Daftar</label>
                            <input type="date" id="editTanggal">
                        </div>

                        <div class="form-group form-group--full">
                            <label for="editAlamat">Alamat</label>
                            <textarea id="editAlamat" rows="3"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalEdit')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Perubahan</button>
                </div>

            </form>

        </div>
    </div>


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

                <h3 class="confirm-title">Hapus Data Warga?</h3>

                <p class="confirm-text">
                    Anda akan menghapus data
                    <strong id="hapusNama">-</strong>
                    (NIK: <span class="mono" id="hapusNik">-</span>).
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

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 22px 24px;
            border-bottom: 1px solid var(--border, #e5e7eb);
        }

        .card-title {
            margin: 0;
            font-size: 20px;
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
            min-width: 1150px;
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

        .mono {
            font-family: monospace;
            font-size: 12px;
        }

        .saldo {
            white-space: nowrap;
        }

        .table-actions {
            display: flex;
            gap: 6px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #e1e5e9;
            background: #fff;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .icon-btn:hover {
            background: #f3f4f6;
        }

        .icon-btn.danger {
            color: #dc2626;
        }

        .icon-btn.danger:hover {
            background: #fef2f2;
            border-color: #fecaca;
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
                       MODAL (tema mengikuti .card)
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

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #edf0f2;
        }

        .modal-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-title svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .modal-close {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            border-radius: 7px;
            font-size: 20px;
            line-height: 1;
            color: #6b7280;
            cursor: pointer;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #111827;
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

        /* --- Detail modal --- */
        .detail-avatar-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid #edf0f2;
        }

        .detail-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4338ca;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-nama {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .detail-nik {
            margin-top: 3px;
            color: #6b7280;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-item--full {
            grid-column: 1 / -1;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #9ca3af;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
        }

        /* --- Edit modal --- */
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
        .form-group textarea {
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary, #4338ca);
        }

        .form-group textarea {
            resize: vertical;
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

        @media (max-width: 560px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

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
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchWarga');
            const table = document.getElementById('wargaTable');

            if (!searchInput || !table) {
                return;
            }

            searchInput.addEventListener('keyup', function() {

                const keyword = this.value.toLowerCase().trim();

                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(function(row) {

                    const text = row.textContent.toLowerCase();

                    row.style.display =
                        text.includes(keyword) ? '' : 'none';

                });

            });

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

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }

        function formatTanggal(tgl) {
            if (!tgl) return '-';
            const d = new Date(tgl);
            if (isNaN(d)) return tgl;
            return d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Toast notifikasi (pengganti alert() bawaan browser)
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
        | Tombol Detail (mata) -> popup card detail
        |--------------------------------------------------------------------------
        */

        function lihatWarga(warga) {

            document.getElementById('detailAvatar').textContent =
                (warga.nama || '-').trim().charAt(0).toUpperCase();

            document.getElementById('detailNama').textContent = warga.nama || '-';
            document.getElementById('detailNik').textContent = warga.nik || '-';
            document.getElementById('detailHp').textContent = warga.no_hp || '-';
            document.getElementById('detailKeluarga').textContent =
                (warga.jumlah_anggota_keluarga ?? 0) + ' orang';
            document.getElementById('detailSaldo').textContent = formatRupiah(warga.saldo);
            document.getElementById('detailTanggal').textContent = formatTanggal(warga.tanggal_daftar);
            document.getElementById('detailAlamat').textContent = warga.alamat || '-';

            bukaModal('modalDetail');

        }


        /*
        |--------------------------------------------------------------------------
        | Tombol Edit (pensil) -> popup form edit
        |--------------------------------------------------------------------------
        */

        function editWarga(warga) {

            document.getElementById('editId').value = warga.id_warga;
            document.getElementById('editNik').value = warga.nik || '';
            document.getElementById('editNama').value = warga.nama || '';
            document.getElementById('editHp').value = warga.no_hp || '';
            document.getElementById('editKeluarga').value = warga.jumlah_anggota_keluarga || 0;
            document.getElementById('editSaldo').value = warga.saldo || 0;
            document.getElementById('editTanggal').value = warga.tanggal_daftar ?
                warga.tanggal_daftar.substring(0, 10) : '';
            document.getElementById('editAlamat').value = warga.alamat || '';

            bukaModal('modalEdit');

        }

        function simpanWarga(event) {

            event.preventDefault();

            const id = document.getElementById('editId').value;

            const payload = {
                nik: document.getElementById('editNik').value,
                nama: document.getElementById('editNama').value,
                no_hp: document.getElementById('editHp').value,
                jumlah_anggota_keluarga: document.getElementById('editKeluarga').value,
                saldo: document.getElementById('editSaldo').value,
                tanggal_daftar: document.getElementById('editTanggal').value,
                alamat: document.getElementById('editAlamat').value,
            };

            fetch(`/admin/page/warga/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menyimpan data.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalEdit');
                    showToast('Berhasil disimpan', 'Perubahan data warga telah tersimpan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Tombol Hapus (tong sampah) -> popup konfirmasi hapus
        |--------------------------------------------------------------------------
        */

        let wargaAkanDihapus = null;

        function hapusWarga(warga) {

            wargaAkanDihapus = warga;

            document.getElementById('hapusNama').textContent = warga.nama || '-';
            document.getElementById('hapusNik').textContent = warga.nik || '-';

            bukaModal('modalHapus');

        }

        function konfirmasiHapus() {

            if (!wargaAkanDihapus) {
                return;
            }

            const id = wargaAkanDihapus.id_warga;
            const namaDihapus = wargaAkanDihapus.nama || 'Warga';

            fetch(`/admin/page/warga/${id}`, {
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
                    showToast('Data warga berhasil dihapus.', namaDihapus + ' telah dihapus dari data warga.');
                    wargaAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
    </script>

@endsection
