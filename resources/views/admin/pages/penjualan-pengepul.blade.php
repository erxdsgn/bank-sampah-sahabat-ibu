@extends('admin.layouts.app')

@section('title', 'Penjualan ke Pengepul')
@section('active', 'penjualan-pengepul')
@section('crumbs', 'Transaksi Sampah | Penjualan ke Pengepul')

@section('content')

    @php
        $barangKeluar = collect($barangKeluar)->sortByDesc('id_barang_keluar')->values();
    @endphp

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>

            <h1 class="hero-title">
                Penjualan ke <span class="accent">Pengepul</span>
            </h1>

            <p class="hero-sub">
                Kelola data penjualan sampah yang telah dipilah kepada pengepul.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModalTambah()">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Penjualan
            </button>
        </div>
    </section>


    <!-- SUMMARY -->
    <section class="summary-grid summary-grid--3">

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Penjualan</div>
                    <div class="summary-value">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi penjualan</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Sampah Terjual</div>
                    <div class="summary-value">{{ number_format($totalBeratKg, 2, ',', '.') }} Kg</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 7h18l-1.5 12a2 2 0 0 1-2 2H6.5a2 2 0 0 1-2-2z"></path>
                        <path d="M8 7V5a4 4 0 0 1 8 0v2"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi berat sampah yang telah dijual</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Jumlah Transaksi</div>
                    <div class="summary-value">{{ $jumlahTransaksi }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M8 6h13"></path>
                        <path d="M8 12h13"></path>
                        <path d="M8 18h13"></path>
                        <path d="M3 6h.01"></path>
                        <path d="M3 12h.01"></path>
                        <path d="M3 18h.01"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Total transaksi yang tercatat</div>
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
                <input type="text" id="searchPenjualan" placeholder="Cari kategori atau pengepul..." autocomplete="off">
            </div>

            <div class="toolbar-right">
                <select id="filterKategori" class="toolbar-select">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategori as $kat)
                        <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>

                <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                    </svg>
                    Refresh
                </button>
            </div>

        </div>


        <!-- TABLE -->
        <div class="table-responsive">
            <table class="data-table" id="penjualanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pengepul</th>
                        <th>Kategori Sampah</th>
                        <th>Berat</th>
                        <th>Harga / Gram</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="penjualanTableBody">
                    @forelse ($barangKeluar as $index => $item)
                        <tr data-kategori-id="{{ $item->id_kategori }}">
                            <td class="row-number">{{ $index + 1 }}</td>
                            <td class="nowrap">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td><strong>{{ $item->pembeli }}</strong></td>
                            <td>
                                <span class="badge badge--info">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                            </td>
                            <td>{{ number_format($item->berat_gram, 0, ',', '.') }} gram</td>
                            <td>Rp {{ number_format($item->harga_jual_per_gram, 0, ',', '.') }}</td>
                            <td>
                                <strong class="saldo amount-in">
                                    Rp {{ number_format($item->total, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <div class="table-actions">

                                    <button class="icon-btn" title="Lihat Detail" type="button"
                                        onclick='lihatDetail(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </button>

                                    <button class="icon-btn" title="Edit Penjualan" type="button"
                                        onclick='editPenjualan(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>
                                    </button>

                                    <button class="icon-btn danger" title="Hapus Penjualan" type="button"
                                        onclick='hapusPenjualan(@json($item))'>
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
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                <strong>Belum Ada Data Penjualan</strong>
                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada data penjualan sampah ke pengepul.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="catalog-empty" id="penjualanKosongCari" style="display: none;">
                <div style="font-size: 30px; margin-bottom: 10px;">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p style="margin: 5px 0 0; color: #6b7280;">
                    Tidak ada data yang cocok dengan pencarian atau filter ini.
                </p>
            </div>
        </div>


        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info">
                Menampilkan <strong id="jumlahTampil">{{ $barangKeluar->count() }}</strong>
                dari <strong>{{ $barangKeluar->count() }}</strong> transaksi
            </div>
        </div>

    </section>


    <!-- TOAST -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- MODAL: TAMBAH / EDIT PENJUALAN -->
    <div class="modal-overlay" id="modalPenjualan">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title" id="modalPenjualanTitle">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Penjualan ke Pengepul
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalPenjualan')">&times;</button>
            </div>

            <form id="formPenjualan" onsubmit="return simpanPenjualan(event)">
                <div class="modal-body">

                    <input type="hidden" id="penjualanId">

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="penjualanTanggal">Tanggal Penjualan</label>
                            <input type="date" id="penjualanTanggal" required>
                        </div>

                        <div class="form-group">
                            <label for="penjualanPembeli">Nama Pengepul</label>
                            <input type="text" id="penjualanPembeli" placeholder="Contoh: Pak Budi" required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="penjualanKategori">Kategori Sampah</label>
                            <select id="penjualanKategori" required>
                                <option value="">-- Pilih Kategori Sampah --</option>
                                @foreach ($kategori as $kat)
                                    <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="penjualanBerat">Berat Sampah (Gram)</label>
                            <input type="number" id="penjualanBerat" min="1" step="1"
                                placeholder="Contoh: 25000" oninput="hitungTotalPreview()" required>
                        </div>

                        <div class="form-group">
                            <label for="penjualanHarga">Harga Jual per Gram</label>
                            <input type="number" id="penjualanHarga" min="0" step="0.01"
                                placeholder="Contoh: 4" oninput="hitungTotalPreview()" required>
                        </div>

                        <div class="form-group form-group--full">
                            <div class="total-preview">
                                <span class="detail-label">Total Penjualan</span>
                                <strong class="saldo amount-in" id="totalPreview">Rp 0</strong>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalPenjualan')">Batal</button>
                    <button class="btn btn--primary" type="submit" id="penjualanSubmitBtn">Simpan Penjualan</button>
                </div>
            </form>

        </div>
    </div>


    <!-- MODAL: DETAIL PENJUALAN -->
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                    Detail Penjualan
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">

                <div class="detail-grid">

                    <div class="detail-item">
                        <span class="detail-label">Tanggal</span>
                        <span class="detail-value" id="detailTanggal">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Pengepul</span>
                        <span class="detail-value" id="detailPembeli">-</span>
                    </div>

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Kategori Sampah</span>
                        <span id="detailKategori">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Berat</span>
                        <span class="detail-value" id="detailBerat">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Harga / Gram</span>
                        <span class="detail-value" id="detailHarga">-</span>
                    </div>

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Total Penjualan</span>
                        <span class="detail-value saldo amount-in" id="detailTotal">-</span>
                    </div>

                </div>

            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>

        </div>
    </div>


    <!-- MODAL: KONFIRMASI HAPUS -->
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

                <h3 class="confirm-title">Hapus Data Penjualan?</h3>

                <p class="confirm-text">
                    Anda akan menghapus data penjualan kepada
                    <strong id="hapusPembeli">-</strong>
                    sebesar <span id="hapusTotal">-</span>.
                    Tindakan ini tidak dapat dibatalkan.
                </p>

            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapusPenjualan()">Ya, Hapus</button>
            </div>

        </div>
    </div>


    <style>
        /* =========================
                   SUMMARY
                ========================= */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .summary-grid--3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .summary-card {
            border: 1px solid var(--border, #e5e7eb);
            background: var(--surface, #ffffff);
            border-radius: 14px;
            padding: 18px;
            min-width: 0;
        }

        .summary-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .summary-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 21px;
            font-weight: 800;
            line-height: 1.25;
            color: #1f2937;
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: var(--primary, #4338ca);
        }

        .summary-icon--income {
            background: #dcfce7;
            color: #16a34a;
        }

        .summary-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .summary-info {
            margin-top: 12px;
            font-size: 12px;
            color: #6b7280;
        }

        @media (max-width: 1100px) {

            .summary-grid,
            .summary-grid--3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {

            .summary-grid,
            .summary-grid--3 {
                grid-template-columns: 1fr;
            }
        }

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
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .table-search {
            width: 360px;
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

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-select {
            height: 40px;
            padding: 0 12px;
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            background: #fff;
            font-family: inherit;
            font-size: 13px;
            color: #374151;
            outline: none;
            cursor: pointer;
        }

        .toolbar-select:focus {
            border-color: var(--primary, #4338ca);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
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

        .nowrap {
            white-space: nowrap;
        }

        .saldo {
            white-space: nowrap;
        }

        .amount-in {
            color: #16a34a;
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

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge--info {
            background: #e0f2fe;
            color: #0369a1;
        }

        .catalog-empty {
            text-align: center;
            padding: 50px 20px;
        }

        /* =========================
                   TOTAL PREVIEW
                ========================= */

        .total-preview {
            background: #f0faf5;
            border: 1px solid #ccefdc;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
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
        }

        .modal-box--sm {
            max-width: 420px;
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
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary, #4338ca);
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

            .form-grid,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .table-search {
                width: 100%;
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
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchPenjualan');
            const filterKategori = document.getElementById('filterKategori');

            if (searchInput) searchInput.addEventListener('keyup', filterTabel);
            if (filterKategori) filterKategori.addEventListener('change', filterTabel);

            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

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

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon"><svg viewBox="0 0 24 24">${iconPath}</svg></div>
                <div class="toast-body">
                    <p class="toast-title">${title}</p>
                    <p class="toast-text">${text}</p>
                </div>
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            function hapusToast() {
                toast.remove();
            }

            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        /*
        |--------------------------------------------------------------------------
        | Pencarian + filter
        |--------------------------------------------------------------------------
        */
        function filterTabel() {
            const keyword = (document.getElementById('searchPenjualan')?.value || '').toLowerCase().trim();
            const kategoriId = document.getElementById('filterKategori')?.value || '';

            const rows = document.querySelectorAll('#penjualanTableBody tr[data-kategori-id]');
            let tampil = 0;

            rows.forEach(function(row) {
                const cocokKategori = !kategoriId || row.dataset.kategoriId === kategoriId;
                const cocokCari = !keyword || row.textContent.toLowerCase().includes(keyword);

                const cocok = cocokKategori && cocokCari;
                row.style.display = cocok ? '' : 'none';

                if (cocok) {
                    tampil++;
                    const no = row.querySelector('.row-number');
                    if (no) no.textContent = tampil;
                }
            });

            const kosongCari = document.getElementById('penjualanKosongCari');
            if (kosongCari) {
                kosongCari.style.display = (rows.length > 0 && tampil === 0) ? '' : 'none';
            }

            const info = document.getElementById('jumlahTampil');
            if (info) info.textContent = tampil;
        }

        /*
        |--------------------------------------------------------------------------
        | Hitung total preview
        |--------------------------------------------------------------------------
        */
        function hitungTotalPreview() {
            const berat = parseFloat(document.getElementById('penjualanBerat').value) || 0;
            const harga = parseFloat(document.getElementById('penjualanHarga').value) || 0;
            const total = berat * harga;

            document.getElementById('totalPreview').textContent = formatRupiah(total);
        }

        /*
        |--------------------------------------------------------------------------
        | Tambah / Edit penjualan
        |--------------------------------------------------------------------------
        */
        function bukaModalTambah() {
            document.getElementById('formPenjualan').reset();
            document.getElementById('penjualanId').value = '';
            document.getElementById('penjualanTanggal').value = new Date().toISOString().substring(0, 10);
            document.getElementById('totalPreview').textContent = 'Rp 0';
            document.getElementById('modalPenjualanTitle').lastChild.textContent = ' Tambah Penjualan ke Pengepul';
            document.getElementById('penjualanSubmitBtn').textContent = 'Simpan Penjualan';
            bukaModal('modalPenjualan');
        }

        function editPenjualan(item) {
            document.getElementById('penjualanId').value = item.id_barang_keluar;
            document.getElementById('penjualanTanggal').value = (item.tanggal || '').substring(0, 10);
            document.getElementById('penjualanPembeli').value = item.pembeli || '';
            document.getElementById('penjualanKategori').value = item.id_kategori || '';
            document.getElementById('penjualanBerat').value = item.berat_gram || 0;
            document.getElementById('penjualanHarga').value = item.harga_jual_per_gram || 0;

            hitungTotalPreview();

            document.getElementById('modalPenjualanTitle').lastChild.textContent = ' Edit Penjualan ke Pengepul';
            document.getElementById('penjualanSubmitBtn').textContent = 'Simpan Perubahan';

            bukaModal('modalPenjualan');
        }

        function simpanPenjualan(event) {
            event.preventDefault();

            const id = document.getElementById('penjualanId').value;

            const payload = {
                tanggal: document.getElementById('penjualanTanggal').value,
                pembeli: document.getElementById('penjualanPembeli').value,
                id_kategori: document.getElementById('penjualanKategori').value,
                berat_gram: document.getElementById('penjualanBerat').value,
                harga_jual_per_gram: document.getElementById('penjualanHarga').value,
            };

            const url = id ?
                `{{ url('admin/pages/penjualan-pengepul') }}/${id}` :
                `{{ route('admin.penjualan-pengepul.store') }}`;

            fetch(url, {
                    method: id ? 'PUT' : 'POST',
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
                        const pesan = err.errors ?
                            Object.values(err.errors).flat().join(' ') :
                            (err.message || 'Gagal menyimpan data penjualan.');
                        throw new Error(pesan);
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalPenjualan');
                    showToast('Berhasil disimpan', id ?
                        'Perubahan data penjualan telah tersimpan.' :
                        'Data penjualan baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Detail penjualan
        |--------------------------------------------------------------------------
        */
        function lihatDetail(item) {
            document.getElementById('detailTanggal').textContent = formatTanggal(item.tanggal);
            document.getElementById('detailPembeli').textContent = item.pembeli || '-';
            document.getElementById('detailKategori').innerHTML =
                `<span class="badge badge--info">${(item.kategori && item.kategori.nama_kategori) || '-'}</span>`;
            document.getElementById('detailBerat').textContent =
                Number(item.berat_gram || 0).toLocaleString('id-ID') + ' gram';
            document.getElementById('detailHarga').textContent = formatRupiah(item.harga_jual_per_gram);
            document.getElementById('detailTotal').textContent = formatRupiah(item.total);

            bukaModal('modalDetail');
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus penjualan
        |--------------------------------------------------------------------------
        */
        let penjualanAkanDihapus = null;

        function hapusPenjualan(item) {
            penjualanAkanDihapus = item;

            document.getElementById('hapusPembeli').textContent = item.pembeli || '-';
            document.getElementById('hapusTotal').textContent = formatRupiah(item.total);

            bukaModal('modalHapus');
        }

        function konfirmasiHapusPenjualan() {
            if (!penjualanAkanDihapus) return;

            const id = penjualanAkanDihapus.id_barang_keluar;
            const pembeli = penjualanAkanDihapus.pembeli || 'Data';

            fetch(`{{ url('admin/pages/penjualan-pengepul') }}/${id}`, {
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
                    showToast('Berhasil dihapus', `Data penjualan kepada ${pembeli} telah dihapus.`);
                    penjualanAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
    </script>

@endsection
