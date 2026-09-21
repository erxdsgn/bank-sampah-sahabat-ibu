@extends('admin.layouts.app')

@section('title', 'Keuangan')
@section('active', 'keuangan')
@section('crumbs', 'Keuangan & Produk | Keuangan')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">KEUANGAN & PRODUK</span>

            <h1 class="hero-title">
                Kelola <span class="accent">Keuangan</span>
            </h1>

            <p class="hero-sub">
                Transaksi otomatis dari setoran, pencairan saldo, dan penjualan ke pengepul.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModalTambah()">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Transaksi
            </button>
        </div>
    </section>


    <!-- SUMMARY -->
    <section class="summary-grid">

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Pemasukan</div>
                    <div class="summary-value">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi pemasukan</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Pengeluaran</div>
                    <div class="summary-value">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi pengeluaran</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Saldo Kas</div>
                    <div class="summary-value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                        <path d="M16 15h2"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Pemasukan dikurangi pengeluaran</div>
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
                <input type="text" id="searchKeuangan" placeholder="Cari kategori atau keterangan..."
                    autocomplete="off">
            </div>

            <div class="toolbar-right">
                <select id="filterSumber" class="toolbar-select">
                    <option value="">Semua Sumber</option>
                    <option value="kas">Kas Manual</option>
                    <option value="setoran">Setoran</option>
                    <option value="pencairan">Pencairan Saldo</option>
                    <option value="penjualan">Penjualan Pengepul</option>
                </select>

                <select id="filterJenis" class="toolbar-select">
                    <option value="">Semua Jenis</option>
                    <option value="Pemasukan">Pemasukan</option>
                    <option value="Pengeluaran">Pengeluaran</option>
                </select>

                <select id="filterPeriode" class="toolbar-select">
                    <option value="">Semua Periode</option>
                    @foreach ($periodeList as $periode)
                        <option value="{{ $periode }}">{{ $periode }}</option>
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
            <table class="data-table" id="keuanganTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="keuanganTableBody">
                    @forelse ($transaksi as $item)
                        <tr data-jenis="{{ $item->jenis }}" data-periode="{{ $item->periode }}"
                            data-sumber="{{ $item->sumber }}">
                            <td class="nowrap">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $item->jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger' }}">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td><strong>{{ $item->kategori }}</strong></td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td>
                                <strong class="saldo {{ $item->jenis === 'Pemasukan' ? 'amount-in' : 'amount-out' }}">
                                    {{ $item->jenis === 'Pemasukan' ? '+' : '-' }} Rp
                                    {{ number_format($item->jumlah, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <div class="table-actions">

                                    <button class="icon-btn" title="Lihat Detail" type="button"
                                        onclick="lihatDetail({{ Js::from($item) }})">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </button>

                                    {{-- Transaksi otomatis hanya bisa diubah dari halaman asalnya --}}
                                    @unless ($item->otomatis)
                                        <button class="icon-btn" title="Edit Transaksi" type="button"
                                            onclick="editTransaksi({{ Js::from($item) }})">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                            </svg>
                                        </button>

                                        <button class="icon-btn danger" title="Hapus Transaksi" type="button"
                                            onclick="hapusTransaksi({{ Js::from($item) }})">
                                            <svg viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                        </button>
                                    @endunless

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                <strong>Belum Ada Transaksi</strong>
                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada transaksi keuangan yang tercatat.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="catalog-empty" id="keuanganKosongCari" style="display: none;">
                <div style="font-size: 30px; margin-bottom: 10px;">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p style="margin: 5px 0 0; color: #6b7280;">
                    Tidak ada transaksi yang cocok dengan pencarian atau filter ini.
                </p>
            </div>
        </div>


        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info">
                Menampilkan <strong id="jumlahTampil">{{ $transaksi->count() }}</strong>
                dari <strong>{{ $transaksi->count() }}</strong> transaksi
            </div>
        </div>

    </section>


    <!-- TOAST -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- MODAL: TAMBAH / EDIT TRANSAKSI -->
    <div class="modal-overlay" id="modalTransaksi">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title" id="modalTransaksiTitle">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Transaksi
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTransaksi')">&times;</button>
            </div>

            <form id="formTransaksi" onsubmit="return simpanTransaksi(event)">
                <div class="modal-body">

                    <input type="hidden" id="transaksiId">

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="transaksiJenis">Jenis Transaksi</label>
                            <select id="transaksiJenis" required>
                                <option value="">Pilih jenis transaksi</option>
                                <option value="Pemasukan">Pemasukan</option>
                                <option value="Pengeluaran">Pengeluaran</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="transaksiTanggal">Tanggal</label>
                            <input type="date" id="transaksiTanggal" required>
                        </div>

                        <div class="form-group">
                            <label for="transaksiKategori">Kategori</label>
                            <input type="text" id="transaksiKategori" placeholder="Contoh: Operasional" required>
                        </div>

                        <div class="form-group">
                            <label for="transaksiJumlah">Jumlah (Rp)</label>
                            <input type="number" id="transaksiJumlah" min="0" placeholder="0" required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="transaksiKeterangan">Keterangan</label>
                            <textarea id="transaksiKeterangan" rows="3" placeholder="Tambahkan keterangan transaksi..."></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTransaksi')">Batal</button>
                    <button class="btn btn--primary" type="submit" id="transaksiSubmitBtn">Simpan Transaksi</button>
                </div>
            </form>

        </div>
    </div>


    <!-- MODAL: DETAIL TRANSAKSI -->
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                    Detail Transaksi
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">

                <div class="detail-grid">

                    <div class="detail-item">
                        <span class="detail-label">Jenis</span>
                        <span id="detailJenis">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Tanggal</span>
                        <span class="detail-value" id="detailTanggal">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Kategori</span>
                        <span class="detail-value" id="detailKategori">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Sumber</span>
                        <span class="detail-value" id="detailSumber">-</span>
                    </div>

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Jumlah</span>
                        <span class="detail-value saldo" id="detailJumlah">-</span>
                    </div>

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Keterangan</span>
                        <span class="detail-value" id="detailKeterangan">-</span>
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

                <h3 class="confirm-title">Hapus Transaksi?</h3>

                <p class="confirm-text">
                    Anda akan menghapus transaksi
                    <strong id="hapusKategori">-</strong>
                    sebesar <span id="hapusJumlah">-</span>.
                    Tindakan ini tidak dapat dibatalkan.
                </p>

            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapusTransaksi()">Ya, Hapus</button>
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

        .summary-icon--expense {
            background: #fee2e2;
            color: #dc2626;
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
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .summary-grid {
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

        .nowrap {
            white-space: nowrap;
        }

        .saldo {
            white-space: nowrap;
        }

        .amount-in {
            color: #16a34a;
        }

        .amount-out {
            color: #dc2626;
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

        .badge--warning {
            background: #fef3c7;
            color: #d97706;
        }

        .badge--success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge--danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .catalog-empty {
            text-align: center;
            padding: 50px 20px;
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

        .form-group textarea {
            resize: vertical;
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

            const searchInput = document.getElementById('searchKeuangan');
            const filterSumber = document.getElementById('filterSumber');
            const filterJenis = document.getElementById('filterJenis');
            const filterPeriode = document.getElementById('filterPeriode');

            if (searchInput) searchInput.addEventListener('keyup', filterTabel);
            if (filterSumber) filterSumber.addEventListener('change', filterTabel);
            if (filterJenis) filterJenis.addEventListener('change', filterTabel);
            if (filterPeriode) filterPeriode.addEventListener('change', filterTabel);

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
            // 'Y-m-d' dibaca sebagai tanggal lokal supaya tidak bergeser sehari
            const d = new Date(String(tgl).length === 10 ? tgl + 'T00:00:00' : tgl);
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
            const keyword = (document.getElementById('searchKeuangan')?.value || '').toLowerCase().trim();
            const sumber = document.getElementById('filterSumber')?.value || '';
            const jenis = document.getElementById('filterJenis')?.value || '';
            const periode = document.getElementById('filterPeriode')?.value || '';

            const rows = document.querySelectorAll('#keuanganTableBody tr[data-jenis]');
            let tampil = 0;

            rows.forEach(function(row) {
                const cocokSumber = !sumber || row.dataset.sumber === sumber;
                const cocokJenis = !jenis || row.dataset.jenis === jenis;
                const cocokPeriode = !periode || row.dataset.periode === periode;
                const cocokCari = !keyword || row.textContent.toLowerCase().includes(keyword);

                const cocok = cocokSumber && cocokJenis && cocokPeriode && cocokCari;
                row.style.display = cocok ? '' : 'none';
                if (cocok) tampil++;
            });

            const kosongCari = document.getElementById('keuanganKosongCari');
            if (kosongCari) {
                kosongCari.style.display = (rows.length > 0 && tampil === 0) ? '' : 'none';
            }

            const info = document.getElementById('jumlahTampil');
            if (info) info.textContent = tampil;
        }

        /*
        |--------------------------------------------------------------------------
        | Tambah / Edit transaksi (hanya untuk transaksi kas manual)
        |--------------------------------------------------------------------------
        */
        function bukaModalTambah() {
            document.getElementById('formTransaksi').reset();
            document.getElementById('transaksiId').value = '';
            document.getElementById('transaksiTanggal').value = new Date().toISOString().substring(0, 10);
            document.getElementById('modalTransaksiTitle').lastChild.textContent = ' Tambah Transaksi';
            document.getElementById('transaksiSubmitBtn').textContent = 'Simpan Transaksi';
            bukaModal('modalTransaksi');
        }

        function editTransaksi(item) {
            document.getElementById('transaksiId').value = item.id_kas;
            document.getElementById('transaksiJenis').value = item.jenis || '';
            document.getElementById('transaksiTanggal').value = (item.tanggal || '').substring(0, 10);
            document.getElementById('transaksiKategori').value = item.kategori || '';
            document.getElementById('transaksiJumlah').value = item.jumlah || 0;
            document.getElementById('transaksiKeterangan').value = item.keterangan || '';

            document.getElementById('modalTransaksiTitle').lastChild.textContent = ' Edit Transaksi';
            document.getElementById('transaksiSubmitBtn').textContent = 'Simpan Perubahan';

            bukaModal('modalTransaksi');
        }

        function simpanTransaksi(event) {
            event.preventDefault();

            const id = document.getElementById('transaksiId').value;

            const payload = {
                jenis: document.getElementById('transaksiJenis').value,
                tanggal: document.getElementById('transaksiTanggal').value,
                kategori: document.getElementById('transaksiKategori').value,
                jumlah: document.getElementById('transaksiJumlah').value,
                keterangan: document.getElementById('transaksiKeterangan').value,
            };

            const url = id ?
                `/admin/pages/keuangan/${id}` :
                `{{ route('admin.keuangan.store') }}`;

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
                            (err.message || 'Gagal menyimpan transaksi.');
                        throw new Error(pesan);
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalTransaksi');
                    showToast('Berhasil disimpan', id ?
                        'Perubahan transaksi telah tersimpan.' :
                        'Transaksi baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Detail transaksi
        |--------------------------------------------------------------------------
        */
        function lihatDetail(item) {
            const badgeClass = item.jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger';
            document.getElementById('detailJenis').innerHTML =
                `<span class="badge ${badgeClass}">${item.jenis}</span>`;

            document.getElementById('detailTanggal').textContent = formatTanggal(item.tanggal);
            document.getElementById('detailKategori').textContent = item.kategori || '-';
            document.getElementById('detailSumber').textContent =
                item.otomatis ? 'Otomatis · ' + item.sumber_label : (item.sumber_label || 'Kas Manual');

            const tanda = item.jenis === 'Pemasukan' ? '+ ' : '- ';
            document.getElementById('detailJumlah').textContent = tanda + formatRupiah(item.jumlah);
            document.getElementById('detailKeterangan').textContent = item.keterangan || '-';

            bukaModal('modalDetail');
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus transaksi (hanya transaksi kas manual)
        |--------------------------------------------------------------------------
        */
        let transaksiAkanDihapus = null;

        function hapusTransaksi(item) {
            transaksiAkanDihapus = item;

            document.getElementById('hapusKategori').textContent = item.kategori || '-';
            document.getElementById('hapusJumlah').textContent = formatRupiah(item.jumlah);

            bukaModal('modalHapus');
        }

        function konfirmasiHapusTransaksi() {
            if (!transaksiAkanDihapus) return;

            const id = transaksiAkanDihapus.id_kas;
            const kategori = transaksiAkanDihapus.kategori || 'Transaksi';

            fetch(`/admin/pages/keuangan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menghapus transaksi.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Berhasil dihapus', `Transaksi "${kategori}" telah dihapus.`);
                    transaksiAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
    </script>

@endsection
