@extends('admin.layouts.app')

@section('title', 'Keuangan')
@section('active', 'keuangan')
@section('crumbs', 'Keuangan & Produk | Keuangan')

@section('content')
<div class="keuangan-page">

    {{-- =========================================================
         HERO SECTION
    ========================================================== --}}
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

    {{-- =========================================================
         SUMMARY CARDS
    ========================================================== --}}
    <section class="summary-grid">
        {{-- Total Pemasukan --}}
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

        {{-- Total Pengeluaran --}}
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

        {{-- Saldo Kas --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Saldo Kas</div>
                    <div class="summary-value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--cash">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                        <path d="M16 15h2"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Pemasukan dikurangi pengeluaran</div>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Jumlah Transaksi</div>
                    <div class="summary-value">{{ $jumlahTransaksi }}</div>
                </div>
                <div class="summary-icon summary-icon--transaction">
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

    {{-- =========================================================
         TABLE CARD & TOOLBAR
    ========================================================== --}}
    <section class="card keuangan-card">
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchKeuangan" placeholder="Cari kategori atau keterangan..." autocomplete="off">
            </div>

            <div class="toolbar-right">
                {{-- Filter Sumber --}}
                <div class="combo combo--arrow" id="combo_filterSumber">
                    <input type="text" id="cari_filterSumber" class="combo-input" autocomplete="off" aria-label="Filter sumber">
                    <div class="combo-list" id="list_filterSumber"></div>
                </div>
                <select id="filterSumber" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    <option value="">Semua Sumber</option>
                    <option value="kas">Kas Manual</option>
                    <option value="setoran">Setoran</option>
                    <option value="pencairan">Pencairan Saldo</option>
                    <option value="penjualan">Penjualan Pengepul</option>
                </select>

                {{-- Filter Jenis --}}
                <div class="combo combo--arrow" id="combo_filterJenis">
                    <input type="text" id="cari_filterJenis" class="combo-input" autocomplete="off" aria-label="Filter jenis">
                    <div class="combo-list" id="list_filterJenis"></div>
                </div>
                <select id="filterJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    <option value="">Semua Jenis</option>
                    <option value="Pemasukan">Pemasukan</option>
                    <option value="Pengeluaran">Pengeluaran</option>
                </select>

                {{-- Filter Periode --}}
                <div class="combo combo--arrow" id="combo_filterPeriode">
                    <input type="text" id="cari_filterPeriode" class="combo-input" autocomplete="off" aria-label="Filter periode">
                    <div class="combo-list" id="list_filterPeriode"></div>
                </div>
                <select id="filterPeriode" class="combo-hidden" tabindex="-1" aria-hidden="true">
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

        {{-- TABLE CONTENT --}}
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
                    @php
                        // Mengurutkan transaksi: Tanggal terbaru di atas.
                        // Jika tanggal sama, diurutkan berdasarkan ID terbesar (terbaru) di atas.
                        $sortedTransaksi = $transaksi->sort(function ($a, $b) {
                            if ($a->tanggal === $b->tanggal) {
                                return $b->id_kas <=> $a->id_kas;
                            }
                            return $b->tanggal <=> $a->tanggal;
                        });
                    @endphp

                    @forelse ($sortedTransaksi as $item)
                        <tr data-jenis="{{ $item->jenis }}" data-periode="{{ $item->periode }}" data-sumber="{{ $item->sumber }}">
                            <td class="nowrap">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $item->jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger' }}">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->kategori }}</strong>
                            </td>
                            <td class="description-cell">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td>
                                <strong class="saldo {{ $item->jenis === 'Pemasukan' ? 'amount-in' : 'amount-out' }}">
                                    {{ $item->jenis === 'Pemasukan' ? '+' : '-' }}
                                    Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <div class="table-actions">
                                    {{-- Detail --}}
                                    <button class="icon-btn" title="Lihat Detail" type="button" onclick="lihatDetail({{ Js::from($item) }})">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </button>

                                    {{-- Hapus --}}
                                    @unless ($item->otomatis)
                                        <button class="icon-btn danger" title="Hapus Transaksi" type="button" onclick="hapusTransaksi({{ Js::from($item) }})">
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
                        <tr class="empty-table-row">
                            <td colspan="6">
                                <div class="table-empty-content">
                                    <div class="empty-icon">📋</div>
                                    <strong>Belum Ada Transaksi</strong>
                                    <p>Belum ada transaksi keuangan yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Empty Search State --}}
            <div class="catalog-empty" id="keuanganKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p>Tidak ada transaksi yang cocok dengan pencarian atau filter ini.</p>
            </div>
        </div>

        {{-- TABLE FOOTER & PAGINATION --}}
        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Transaksi per halaman:</label>
                    <div class="combo combo--up combo--arrow" id="combo_perPageSelect">
                        <input type="text" id="cari_perPageSelect" class="combo-input" placeholder="" autocomplete="off">
                        <div class="combo-list" id="list_perPageSelect"></div>
                    </div>
                    <select id="perPageSelect" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="pagination-buttons" id="paginationButtons"></div>
            </div>
        </div>
    </section>

    {{-- =========================================================
         TOAST CONTAINER
    ========================================================== --}}
    <div class="toast-wrap" id="toastWrap"></div>

    {{-- =========================================================
         MODAL TAMBAH TRANSAKSI
    ========================================================== --}}
    <div class="modal-overlay" id="modalTransaksi">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Transaksi
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTransaksi')">&times;</button>
            </div>

            <form id="formTransaksi" onsubmit="return simpanTransaksi(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="cari_transaksiJenis">Jenis Transaksi</label>
                            <div class="combo combo--arrow" id="combo_transaksiJenis">
                                <input type="text" id="cari_transaksiJenis" class="combo-input" autocomplete="off">
                                <div class="combo-list" id="list_transaksiJenis"></div>
                            </div>
                            <select id="transaksiJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
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

                    <p class="form-note">
                        Catatan: transaksi yang sudah disimpan tidak dapat diedit, hanya dapat dilihat atau dihapus.
                    </p>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTransaksi')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
         MODAL DETAIL TRANSAKSI
    ========================================================== --}}
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

    {{-- =========================================================
         MODAL HAPUS TRANSAKSI
    ========================================================== --}}
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
                    Anda akan menghapus transaksi <strong id="hapusKategori">-</strong> sebesar <span id="hapusJumlah">-</span>. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapusTransaksi()">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

{{-- =============================================================
     STYLE KEUANGAN PAGE
============================================================== --}}
<style>
    .keuangan-page {
        --page-bg: #f5f7fb;
        --surface: #ffffff;
        --surface-secondary: #f8fafc;
        --text: #1f2937;
        --text-secondary: #6b7280;
        --text-muted: #9ca3af;
        --border: #e5e7eb;
        --table-head: #f8fafc;
        --table-row: #ffffff;
        --table-hover: #fafafa;
        --input-bg: #ffffff;
        --empty: #f8fafc;
        --shadow: 0 8px 25px rgba(15, 23, 42, .06);
        color: var(--text);
    }

    html[data-theme="dark"] .keuangan-page {
        --page-bg: #0b1220;
        --surface: #151d2f;
        --surface-secondary: #1b2438;
        --text: #f1f5f9;
        --text-secondary: #aab6c8;
        --text-muted: #748198;
        --border: #29364d;
        --table-head: #111a2c;
        --table-row: #151d2f;
        --table-hover: #202b40;
        --input-bg: #111a2c;
        --empty: #111a2c;
        --shadow: 0 8px 25px rgba(0, 0, 0, .25);
    }

    .keuangan-page input,
    .keuangan-page select,
    .keuangan-page textarea {
        color-scheme: light;
    }

    html[data-theme="dark"] .keuangan-page input,
    html[data-theme="dark"] .keuangan-page select,
    html[data-theme="dark"] .keuangan-page textarea {
        color-scheme: dark;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-card {
        min-width: 0;
        padding: 18px;
        border: 1px solid var(--border);
        background: var(--surface);
        border-radius: 14px;
        box-shadow: var(--shadow);
        transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .summary-card:hover {
        border-color: rgba(34, 197, 94, .28);
        transform: translateY(-1px);
    }

    .summary-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .summary-label {
        margin-bottom: 8px;
        font-size: 13px;
        color: var(--text-secondary);
    }

    .summary-value {
        font-size: 21px;
        font-weight: 800;
        line-height: 1.25;
        color: var(--text);
        letter-spacing: -.02em;
    }

    .summary-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: rgba(99, 102, 241, .12);
        color: #818cf8;
    }

    .summary-icon--income { background: rgba(34, 197, 94, .14); color: #4ade80; }
    .summary-icon--expense { background: rgba(239, 68, 68, .14); color: #f87171; }
    .summary-icon--cash { background: rgba(59, 130, 246, .14); color: #60a5fa; }
    .summary-icon--transaction { background: rgba(168, 85, 247, .14); color: #c084fc; }

    .summary-icon svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .summary-info {
        margin-top: 12px;
        font-size: 12px;
        line-height: 1.5;
        color: var(--text-muted);
    }

    .keuangan-page .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--shadow);
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
        position: relative;
        width: 360px;
        color: var(--text-muted);
    }

    .table-search svg {
        position: absolute;
        top: 50%;
        left: 12px;
        width: 18px;
        height: 18px;
        transform: translateY(-50%);
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        pointer-events: none;
    }

    .table-search input {
        width: 100%;
        height: 40px;
        box-sizing: border-box;
        padding: 0 14px 0 40px;
        border: 1px solid var(--border);
        border-radius: 8px;
        outline: none;
        background: var(--input-bg);
        color: var(--text);
        font-family: inherit;
        font-size: 13px;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .table-search input::placeholder { color: var(--text-muted); }
    .table-search input:focus {
        border-color: var(--primary, #22c55e);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .toolbar-right .combo { width: 160px; }
    .toolbar-right .combo-input {
        height: 40px;
        padding-top: 0;
        padding-bottom: 0;
        color: var(--text-secondary);
    }

    .keuangan-page .btn--ghost {
        background: var(--surface);
        border-color: var(--border);
        color: var(--text-secondary);
    }

    .keuangan-page .btn--ghost:hover {
        background: var(--table-hover);
        color: var(--text);
        border-color: var(--border);
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .data-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .data-table th {
        padding: 13px 16px;
        background: var(--table-head);
        color: var(--text-secondary);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        letter-spacing: .02em;
    }

    .data-table td {
        padding: 16px;
        background: var(--table-row);
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        vertical-align: middle;
        transition: background .15s ease;
    }

    .data-table tbody tr:hover td { background: var(--table-hover); }
    .data-table td strong { color: var(--text); }
    .description-cell { max-width: 280px; }
    .nowrap { white-space: nowrap; }
    .saldo { white-space: nowrap; }
    .amount-in { color: #22c55e !important; }
    .amount-out { color: #ef4444 !important; }

    .table-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .icon-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface);
        color: var(--text-secondary);
        cursor: pointer;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .icon-btn svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .icon-btn:hover {
        background: var(--table-hover);
        color: var(--text);
        border-color: var(--border);
    }

    .icon-btn.danger { color: #f87171; }
    .icon-btn.danger:hover {
        background: rgba(239, 68, 68, .12);
        border-color: rgba(239, 68, 68, .30);
        color: #f87171;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    .badge--success { background: rgba(34, 197, 94, .14); color: #4ade80; }
    .badge--danger { background: rgba(239, 68, 68, .14); color: #f87171; }
    .badge--warning { background: rgba(245, 158, 11, .14); color: #fbbf24; }

    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        min-height: 60px;
        padding: 16px 24px;
        background: var(--surface);
    }

    .table-pagination-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .per-page {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .per-page label { font-size: 13px; color: var(--text-secondary); }
    .per-page .combo { width: 84px; }
    .per-page .combo-input { padding-top: 6px; padding-bottom: 6px; }

    .pagination-buttons {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .pagination-buttons .btn { padding: 6px 12px; min-width: 32px; }
    .pagination-buttons .btn:disabled { opacity: .5; cursor: not-allowed; }
    .pagination-dots { padding: 0 4px; color: var(--text-secondary); }

    .table-info { font-size: 13px; color: var(--text-secondary); }
    .table-info strong { color: var(--text); }

    .combo { position: relative; }
    .combo-hidden { display: none !important; }

    .combo-input {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        font-family: inherit;
        outline: none;
        box-sizing: border-box;
        background: var(--input-bg);
        color: var(--text);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .combo-input::placeholder { color: var(--text-muted); }
    .combo-input:focus {
        border-color: var(--primary, #22c55e);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
    }

    .combo--arrow .combo-input {
        cursor: pointer;
        padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 9px center;
        background-size: 14px;
    }

    .combo-list {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        min-width: 84px;
        max-height: 200px;
        overflow-y: auto;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
        z-index: 60;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
    }

    .combo--up .combo-list { top: auto; bottom: calc(100% + 4px); }
    .combo-list.show { display: block; }
    .combo-item {
        padding: 10px 14px;
        font-size: 13px;
        color: var(--text);
        cursor: pointer;
        transition: background .1s ease, color .1s ease;
    }
    .combo-item.is-selected { font-weight: 700; }
    .combo-item:hover,
    .combo-item.is-active {
        background: var(--table-hover);
        color: #22c55e;
    }

    .empty-table-row td { border-bottom: none; }
    .table-empty-content { padding: 40px 20px; text-align: center; color: var(--text); }
    .empty-icon { margin-bottom: 10px; font-size: 30px; line-height: 1; }
    .table-empty-content strong, .catalog-empty strong { display: block; color: var(--text); font-size: 14px; }
    .table-empty-content p, .catalog-empty p { margin: 6px 0 0; color: var(--text-muted); font-size: 13px; line-height: 1.5; }
    .catalog-empty { padding: 50px 20px; background: var(--empty); color: var(--text); text-align: center; }

    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(2, 6, 23, .58);
        backdrop-filter: blur(2px);
    }
    html[data-theme="dark"] .modal-overlay { background: rgba(2, 6, 23, .72); }
    .modal-overlay.active { display: flex; }

    .modal-box {
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, .18);
    }
    .modal-box--sm { max-width: 420px; }
    .modal-box > form { display: flex; flex-direction: column; flex: 1; min-height: 0; }

    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
    }

    .modal-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: var(--text);
        font-size: 16px;
        font-weight: 700;
    }

    .modal-title svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .modal-close {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 7px;
        background: transparent;
        color: var(--text-secondary);
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        transition: background .2s ease, color .2s ease;
    }
    .modal-close:hover { background: var(--table-hover); color: var(--text); }

    .modal-body {
        padding: 22px 24px;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
    }
    .modal-body--center { padding-top: 28px; text-align: center; }

    .modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        flex-shrink: 0;
    }
    .modal-foot--center { justify-content: center; }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 18px;
    }

    .form-group { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .form-group--full { grid-column: 1 / -1; }
    .form-group label { color: var(--text-secondary); font-size: 12px; font-weight: 700; }

    .form-group input,
    .form-group textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 9px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        outline: none;
        background: var(--input-bg);
        color: var(--text);
        font-family: inherit;
        font-size: 13px;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .form-group input { height: 40px; }
    .form-group .combo-input { height: 40px; }
    .form-group textarea { resize: vertical; min-height: 82px; line-height: 1.5; }
    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--primary, #22c55e);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
    }

    .form-note { margin: 14px 0 0; color: var(--text-muted); font-size: 12px; line-height: 1.6; }
    .btn--danger { background: #dc2626; color: #fff; border: 1px solid #dc2626; }
    .btn--danger:hover { background: #b91c1c; border-color: #b91c1c; }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 20px;
    }
    .detail-item { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .detail-item--full { grid-column: 1 / -1; }
    .detail-label { color: var(--text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
    .detail-value { color: var(--text); font-size: 14px; line-height: 1.5; word-break: break-word; }

    .confirm-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(239, 68, 68, .14);
        color: #f87171;
    }
    .confirm-icon svg { width: 24px; height: 24px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .confirm-title { margin: 0 0 8px; color: var(--text); font-size: 16px; font-weight: 700; }
    .confirm-text { margin: 0; color: var(--text-secondary); font-size: 13px; line-height: 1.6; }
    .confirm-text strong { color: var(--text); }

    .toast-wrap {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1100;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: calc(100vw - 40px);
        pointer-events: none;
    }

    .toast {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        width: 380px;
        max-width: calc(100vw - 40px);
        box-sizing: border-box;
        padding: 14px 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-left: 4px solid #22c55e;
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .15);
        pointer-events: auto;
    }
    .toast.toast--error { border-left-color: #ef4444; }
    .toast-icon {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(34, 197, 94, .14);
        color: #4ade80;
    }
    .toast.toast--error .toast-icon { background: rgba(239, 68, 68, .14); color: #f87171; }
    .toast-icon svg { width: 13px; height: 13px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
    .toast-body { flex: 1; min-width: 0; }
    .toast-title { margin: 0 0 2px; color: var(--text); font-size: 13px; font-weight: 700; }
    .toast-text { margin: 0; color: var(--text-secondary); font-size: 12.5px; line-height: 1.5; word-break: break-word; }
    .toast-close { flex-shrink: 0; padding: 0; border: none; background: transparent; color: var(--text-muted); font-size: 16px; line-height: 1; cursor: pointer; }
    .toast-close:hover { color: var(--text); }

    @media (max-width: 1100px) {
        .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 760px) {
        .table-toolbar { align-items: stretch; padding: 14px 16px; }
        .table-search { width: 100%; }
        .toolbar-right { width: 100%; }
        .toolbar-right .combo { flex: 1 1 140px; min-width: 130px; width: auto; }
        .table-footer { padding: 14px 16px; }
    }
    @media (max-width: 560px) {
        .summary-grid { grid-template-columns: 1fr; }
        .form-grid, .detail-grid { grid-template-columns: 1fr; }
        .form-group--full, .detail-item--full { grid-column: auto; }
        .modal-overlay { padding: 12px; }
        .modal-box { max-height: 94vh; border-radius: 12px; }
        .modal-head, .modal-body, .modal-foot { padding-left: 18px; padding-right: 18px; }
        .modal-foot { flex-wrap: wrap; }
        .modal-foot .btn { flex: 1; }
        .toolbar-right { display: grid; grid-template-columns: 1fr 1fr; }
        .toolbar-right .combo, .toolbar-right .btn { width: 100%; min-width: 0; }
        .toolbar-right .btn { justify-content: center; }
        .toast-wrap { top: 12px; right: 12px; left: 12px; max-width: none; }
        .toast { width: 100%; max-width: none; }
    }
</style>

{{-- =============================================================
     SCRIPT KEUANGAN PAGE
============================================================== --}}
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

        const paginationContainer = document.getElementById('paginationButtons');
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-page]');
                if (!btn || btn.disabled) return;
                keuanganPage = parseInt(btn.dataset.page, 10);
                renderTabel();
            });
        }

        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) perPageSelect.addEventListener('change', filterTabel);

        initComboSelect('perPageSelect');
        initComboSelect('filterSumber');
        initComboSelect('filterJenis');
        initComboSelect('filterPeriode');
        initComboSelect('transaksiJenis', { placeholderKosong: true });

        renderTabel();

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

    function bukaModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('active');
            document.body.classList.add('modal-open');
        }
    }

    function tutupModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('active');

        if (!document.querySelector('.modal-overlay.active')) {
            document.body.classList.remove('modal-open');
        }
    }

    function formatRupiah(angka) {
        return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
    }

    function formatTanggal(tgl) {
        if (!tgl) return '-';
        const d = new Date(String(tgl).length === 10 ? tgl + 'T00:00:00' : tgl);
        if (isNaN(d)) return tgl;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function showToast(title, text, type = 'success') {
        const wrap = document.getElementById('toastWrap');
        if (!wrap) return;

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

        function hapusToast() { toast.remove(); }

        toast.querySelector('.toast-close').addEventListener('click', hapusToast);
        wrap.appendChild(toast);
        setTimeout(hapusToast, 3500);
    }

    let keuanganPage = 1;

    function filterTabel() {
        keuanganPage = 1;
        renderTabel();
    }

    function renderTabel() {
        const keyword = (document.getElementById('searchKeuangan')?.value || '').toLowerCase().trim();
        const sumber = document.getElementById('filterSumber')?.value || '';
        const jenis = document.getElementById('filterJenis')?.value || '';
        const periode = document.getElementById('filterPeriode')?.value || '';

        const perPageSelect = document.getElementById('perPageSelect');
        const paginationContainer = document.getElementById('paginationButtons');
        const tableInfo = document.getElementById('tableInfoPagination');

        const rows = Array.from(document.querySelectorAll('#keuanganTableBody tr[data-jenis]'));

        const filtered = rows.filter(function(row) {
            const cocokSumber = !sumber || row.dataset.sumber === sumber;
            const cocokJenis = !jenis || row.dataset.jenis === jenis;
            const cocokPeriode = !periode || row.dataset.periode === periode;
            const cocokCari = !keyword || row.textContent.toLowerCase().includes(keyword);

            return cocokSumber && cocokJenis && cocokPeriode && cocokCari;
        });

        const kosongCari = document.getElementById('keuanganKosongCari');
        if (kosongCari) {
            kosongCari.style.display = (rows.length > 0 && filtered.length === 0) ? '' : 'none';
        }

        const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
        const totalPages = Math.ceil(filtered.length / perPage) || 1;
        keuanganPage = Math.min(Math.max(keuanganPage, 1), totalPages);

        rows.forEach(r => r.style.display = 'none');

        const start = (keuanganPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(r => r.style.display = '');

        if (tableInfo) {
            tableInfo.innerHTML = filtered.length === 0
                ? 'Tidak ada data yang ditampilkan'
                : `Menampilkan transaksi <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
        }

        if (!paginationContainer) return;

        let html = `<button class="btn btn--ghost" type="button" data-page="${keuanganPage - 1}" ${keuanganPage === 1 ? 'disabled' : ''}>‹</button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= keuanganPage - 1 && i <= keuanganPage + 1)) {
                html += `<button class="btn ${i === keuanganPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
            } else if (i === keuanganPage - 2 || i === keuanganPage + 2) {
                html += '<span class="pagination-dots">…</span>';
            }
        }

        html += `<button class="btn btn--ghost" type="button" data-page="${keuanganPage + 1}" ${keuanganPage === totalPages ? 'disabled' : ''}>›</button>`;
        paginationContainer.innerHTML = html;
    }

    const comboRegistry = {};

    function syncCombo(selectId) {
        if (comboRegistry[selectId]) comboRegistry[selectId]();
    }

    function initComboSelect(selectId, opts = {}) {
        const select = document.getElementById(selectId);
        const input = document.getElementById('cari_' + selectId);
        const list = document.getElementById('list_' + selectId);
        if (!select || !input || !list) return;

        const placeholderKosong = !!opts.placeholderKosong;
        input.readOnly = true;
        let aktif = -1;

        const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
        const esc = v => String(v).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));

        if (placeholderKosong) {
            const kosong = Array.from(select.options).find(o => o.value === '');
            if (kosong) input.placeholder = teks(kosong);
        }

        const daftar = () => Array.from(select.options).filter(o => !(placeholderKosong && o.value === ''));

        function sync() {
            const o = select.options[select.selectedIndex];
            input.value = (!o || (placeholderKosong && o.value === '')) ? '' : teks(o);
        }

        function buka() {
            list.innerHTML = daftar().map(o =>
                `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
            ).join('');
            aktif = -1;
            list.classList.add('show');
            const terpilih = list.querySelector('.is-selected');
            if (terpilih) terpilih.scrollIntoView({ block: 'nearest' });
        }

        function tutup() {
            list.classList.remove('show');
            sync();
        }

        function pilih(value) {
            const berubah = select.value !== String(value);
            select.value = value;
            tutup();
            if (berubah) select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function sorot(arah) {
            const items = list.querySelectorAll('.combo-item');
            if (!items.length) return;
            aktif = (aktif + arah + items.length) % items.length;
            items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
            items[aktif].scrollIntoView({ block: 'nearest' });
        }

        input.addEventListener('focus', buka);
        input.addEventListener('click', () => { if (!list.classList.contains('show')) buka(); });
        input.addEventListener('blur', tutup);

        input.addEventListener('keydown', function(e) {
            const items = list.querySelectorAll('.combo-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!list.classList.contains('show')) buka();
                sorot(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                sorot(-1);
            } else if (e.key === 'Enter') {
                if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                    e.preventDefault();
                    pilih(items[aktif].dataset.value);
                }
            } else if (e.key === 'Escape' && list.classList.contains('show')) {
                e.stopPropagation();
                tutup();
            }
        });

        list.addEventListener('mouseover', function(e) {
            const item = e.target.closest('.combo-item');
            if (!item) return;
            list.querySelectorAll('.combo-item').forEach((el, i) => {
                el.classList.toggle('is-active', el === item);
                if (el === item) aktif = i;
            });
        });

        list.addEventListener('mousedown', function(e) {
            e.preventDefault();
            const item = e.target.closest('.combo-item');
            if (item) pilih(item.dataset.value);
        });

        comboRegistry[selectId] = sync;
        sync();
    }

    function bukaModalTambah() {
        const form = document.getElementById('formTransaksi');
        if (form) form.reset();

        syncCombo('transaksiJenis');

        const tanggal = document.getElementById('transaksiTanggal');
        if (tanggal) {
            const now = new Date();
            const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().substring(0, 10);
            tanggal.value = localDate;
        }

        bukaModal('modalTransaksi');
    }

    function simpanTransaksi(event) {
        event.preventDefault();

        if (!document.getElementById('transaksiJenis').value) {
            showToast('Gagal menyimpan', 'Silakan pilih jenis transaksi.', 'error');
            document.getElementById('cari_transaksiJenis')?.focus();
            return false;
        }

        const payload = {
            jenis: document.getElementById('transaksiJenis').value,
            tanggal: document.getElementById('transaksiTanggal').value,
            kategori: document.getElementById('transaksiKategori').value,
            jumlah: document.getElementById('transaksiJumlah').value,
            keterangan: document.getElementById('transaksiKeterangan').value
        };

        fetch(`{{ route('admin.keuangan.store') }}`, {
            method: 'POST',
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
                const pesan = err.errors
                    ? Object.values(err.errors).flat().join(' ')
                    : (err.message || 'Gagal menyimpan transaksi.');
                throw new Error(pesan);
            }
            return res.json();
        })
        .then(() => {
            tutupModal('modalTransaksi');
            showToast('Berhasil disimpan', 'Transaksi baru telah ditambahkan.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal menyimpan', err.message, 'error');
        });

        return false;
    }

    function lihatDetail(item) {
        const badgeClass = item.jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger';

        document.getElementById('detailJenis').innerHTML = `<span class="badge ${badgeClass}">${item.jenis}</span>`;
        document.getElementById('detailTanggal').textContent = formatTanggal(item.tanggal);
        document.getElementById('detailKategori').textContent = item.kategori || '-';
        document.getElementById('detailSumber').textContent = item.otomatis ? 'Otomatis · ' + item.sumber_label : (item.sumber_label || 'Kas Manual');

        const tanda = item.jenis === 'Pemasukan' ? '+ ' : '- ';
        const jumlahElement = document.getElementById('detailJumlah');

        jumlahElement.textContent = tanda + formatRupiah(item.jumlah);
        jumlahElement.classList.remove('amount-in', 'amount-out');
        jumlahElement.classList.add(item.jenis === 'Pemasukan' ? 'amount-in' : 'amount-out');
        document.getElementById('detailKeterangan').textContent = item.keterangan || '-';

        bukaModal('modalDetail');
    }

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
