@extends('admin.layouts.app')

@section('title', 'Keuangan')
@section('active', 'keuangan')
@section('crumbs', 'Keuangan & Produk | Keuangan')

@section('content')

    <div class="keuangan-page">

        {{-- =========================================================
             HERO
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
             SUMMARY
        ========================================================== --}}
        <section class="summary-grid">

            {{-- TOTAL PEMASUKAN --}}
            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Total Pemasukan</div>
                        <div class="summary-value">
                            Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="summary-icon summary-icon--income">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3v18"></path>
                            <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Akumulasi seluruh transaksi pemasukan
                </div>
            </div>


            {{-- TOTAL PENGELUARAN --}}
            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Total Pengeluaran</div>
                        <div class="summary-value">
                            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="summary-icon summary-icon--expense">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3v18"></path>
                            <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Akumulasi seluruh transaksi pengeluaran
                </div>
            </div>


            {{-- SALDO KAS --}}
            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Saldo Kas</div>
                        <div class="summary-value">
                            Rp {{ number_format($saldoKas, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="summary-icon summary-icon--cash">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <path d="M3 10h18"></path>
                            <path d="M16 15h2"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Pemasukan dikurangi pengeluaran
                </div>
            </div>


            {{-- JUMLAH TRANSAKSI --}}
            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Jumlah Transaksi</div>
                        <div class="summary-value">
                            {{ $jumlahTransaksi }}
                        </div>
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

                <div class="summary-info">
                    Total transaksi yang tercatat
                </div>
            </div>

        </section>


        {{-- =========================================================
             TABLE CARD
        ========================================================== --}}
        <section class="card keuangan-card">

            {{-- TOOLBAR --}}
            <div class="table-toolbar">

                <div class="table-search">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>

                    <input
                        type="text"
                        id="searchKeuangan"
                        placeholder="Cari kategori atau keterangan..."
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
                            <option value="{{ $periode }}">
                                {{ $periode }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        class="btn btn--ghost"
                        type="button"
                        onclick="window.location.reload()">

                        <svg viewBox="0 0 24 24">
                            <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                            <path d="M21 3v5h-5"></path>
                        </svg>

                        Refresh
                    </button>

                </div>

            </div>


            {{-- TABLE --}}
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

                            <tr
                                data-jenis="{{ $item->jenis }}"
                                data-periode="{{ $item->periode }}"
                                data-sumber="{{ $item->sumber }}">

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

                                        {{-- DETAIL --}}
                                        <button
                                            class="icon-btn"
                                            title="Lihat Detail"
                                            type="button"
                                            onclick="lihatDetail({{ Js::from($item) }})">

                                            <svg viewBox="0 0 24 24">
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                                <circle cx="12" cy="12" r="2.5"></circle>
                                            </svg>

                                        </button>


                                        {{-- HAPUS --}}
                                        @unless ($item->otomatis)

                                            <button
                                                class="icon-btn danger"
                                                title="Hapus Transaksi"
                                                type="button"
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

                            <tr class="empty-table-row">
                                <td colspan="6">

                                    <div class="table-empty-content">

                                        <div class="empty-icon">📋</div>

                                        <strong>Belum Ada Transaksi</strong>

                                        <p>
                                            Belum ada transaksi keuangan yang tercatat.
                                        </p>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>


                {{-- EMPTY SEARCH --}}
                <div
                    class="catalog-empty"
                    id="keuanganKosongCari"
                    style="display: none;">

                    <div class="empty-icon">🔍</div>

                    <strong>Data Tidak Ditemukan</strong>

                    <p>
                        Tidak ada transaksi yang cocok dengan pencarian atau filter ini.
                    </p>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="table-footer">

                <div class="table-info">
                    Menampilkan
                    <strong id="jumlahTampil">{{ $transaksi->count() }}</strong>
                    dari
                    <strong>{{ $transaksi->count() }}</strong>
                    transaksi
                </div>

            </div>

        </section>


        {{-- =========================================================
             TOAST
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

                    <button
                        class="modal-close"
                        type="button"
                        onclick="tutupModal('modalTransaksi')">
                        &times;
                    </button>

                </div>


                <form
                    id="formTransaksi"
                    onsubmit="return simpanTransaksi(event)">

                    <div class="modal-body">

                        <div class="form-grid">

                            <div class="form-group">

                                <label for="transaksiJenis">
                                    Jenis Transaksi
                                </label>

                                <select id="transaksiJenis" required>
                                    <option value="">
                                        Pilih jenis transaksi
                                    </option>

                                    <option value="Pemasukan">
                                        Pemasukan
                                    </option>

                                    <option value="Pengeluaran">
                                        Pengeluaran
                                    </option>
                                </select>

                            </div>


                            <div class="form-group">

                                <label for="transaksiTanggal">
                                    Tanggal
                                </label>

                                <input
                                    type="date"
                                    id="transaksiTanggal"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="transaksiKategori">
                                    Kategori
                                </label>

                                <input
                                    type="text"
                                    id="transaksiKategori"
                                    placeholder="Contoh: Operasional"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="transaksiJumlah">
                                    Jumlah (Rp)
                                </label>

                                <input
                                    type="number"
                                    id="transaksiJumlah"
                                    min="0"
                                    placeholder="0"
                                    required>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="transaksiKeterangan">
                                    Keterangan
                                </label>

                                <textarea
                                    id="transaksiKeterangan"
                                    rows="3"
                                    placeholder="Tambahkan keterangan transaksi..."></textarea>

                            </div>

                        </div>


                        <p class="form-note">
                            Catatan: transaksi yang sudah disimpan tidak dapat diedit,
                            hanya dapat dilihat atau dihapus.
                        </p>

                    </div>


                    <div class="modal-foot">

                        <button
                            class="btn btn--ghost"
                            type="button"
                            onclick="tutupModal('modalTransaksi')">
                            Batal
                        </button>

                        <button
                            class="btn btn--primary"
                            type="submit">
                            Simpan Transaksi
                        </button>

                    </div>

                </form>

            </div>

        </div>


        {{-- =========================================================
             MODAL DETAIL
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

                    <button
                        class="modal-close"
                        type="button"
                        onclick="tutupModal('modalDetail')">
                        &times;
                    </button>

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

                    <button
                        class="btn btn--ghost"
                        type="button"
                        onclick="tutupModal('modalDetail')">
                        Tutup
                    </button>

                </div>

            </div>

        </div>


        {{-- =========================================================
             MODAL HAPUS
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

                    <h3 class="confirm-title">
                        Hapus Transaksi?
                    </h3>

                    <p class="confirm-text">
                        Anda akan menghapus transaksi
                        <strong id="hapusKategori">-</strong>
                        sebesar
                        <span id="hapusJumlah">-</span>.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                </div>


                <div class="modal-foot modal-foot--center">

                    <button
                        class="btn btn--ghost"
                        type="button"
                        onclick="tutupModal('modalHapus')">
                        Batal
                    </button>

                    <button
                        class="btn btn--danger"
                        type="button"
                        onclick="konfirmasiHapusTransaksi()">
                        Ya, Hapus
                    </button>

                </div>

            </div>

        </div>

    </div>


    {{-- =============================================================
         STYLE
    ============================================================== --}}
    <style>
        /* =========================================================
           PAGE THEME
        ========================================================== */

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


        /* =========================================================
           FORM COLOR SCHEME
        ========================================================== */

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


        /* =========================================================
           SUMMARY
        ========================================================== */

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
            transition:
                border-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
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

        .summary-icon--income {
            background: rgba(34, 197, 94, .14);
            color: #4ade80;
        }

        .summary-icon--expense {
            background: rgba(239, 68, 68, .14);
            color: #f87171;
        }

        .summary-icon--cash {
            background: rgba(59, 130, 246, .14);
            color: #60a5fa;
        }

        .summary-icon--transaction {
            background: rgba(168, 85, 247, .14);
            color: #c084fc;
        }

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


        /* =========================================================
           CARD
        ========================================================== */

        .keuangan-page .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }


        /* =========================================================
           TOOLBAR
        ========================================================== */

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

        .table-search input::placeholder {
            color: var(--text-muted);
        }

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

        .toolbar-select {
            height: 40px;
            min-width: 130px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-secondary);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }

        .toolbar-select:focus {
            border-color: var(--primary, #22c55e);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
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


        /* =========================================================
           TABLE
        ========================================================== */

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

        .data-table tbody tr:hover td {
            background: var(--table-hover);
        }

        .data-table td strong {
            color: var(--text);
        }

        .description-cell {
            max-width: 280px;
        }

        .nowrap {
            white-space: nowrap;
        }

        .saldo {
            white-space: nowrap;
        }

        .amount-in {
            color: #22c55e !important;
        }

        .amount-out {
            color: #ef4444 !important;
        }


        /* =========================================================
           ACTION BUTTON
        ========================================================== */

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
            transition:
                background .2s ease,
                color .2s ease,
                border-color .2s ease;
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

        .icon-btn.danger {
            color: #f87171;
        }

        .icon-btn.danger:hover {
            background: rgba(239, 68, 68, .12);
            border-color: rgba(239, 68, 68, .30);
            color: #f87171;
        }


        /* =========================================================
           BADGE
        ========================================================== */

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

        .badge--success {
            background: rgba(34, 197, 94, .14);
            color: #4ade80;
        }

        .badge--danger {
            background: rgba(239, 68, 68, .14);
            color: #f87171;
        }

        .badge--warning {
            background: rgba(245, 158, 11, .14);
            color: #fbbf24;
        }


        /* =========================================================
           TABLE FOOTER
        ========================================================== */

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 60px;
            padding: 16px 24px;
            background: var(--surface);
        }

        .table-info {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .table-info strong {
            color: var(--text);
        }


        /* =========================================================
           EMPTY STATE
        ========================================================== */

        .empty-table-row td {
            border-bottom: none;
        }

        .table-empty-content {
            padding: 40px 20px;
            text-align: center;
            color: var(--text);
        }

        .empty-icon {
            margin-bottom: 10px;
            font-size: 30px;
            line-height: 1;
        }

        .table-empty-content strong,
        .catalog-empty strong {
            display: block;
            color: var(--text);
            font-size: 14px;
        }

        .table-empty-content p,
        .catalog-empty p {
            margin: 6px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .catalog-empty {
            padding: 50px 20px;
            background: var(--empty);
            color: var(--text);
            text-align: center;
        }


        /* =========================================================
           MODAL
        ========================================================== */

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

        html[data-theme="dark"] .modal-overlay {
            background: rgba(2, 6, 23, .72);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .18);
        }

        html[data-theme="dark"] .modal-box {
            box-shadow: 0 20px 45px rgba(0, 0, 0, .45);
        }

        .modal-box--sm {
            max-width: 420px;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
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

        .modal-close:hover {
            background: var(--table-hover);
            color: var(--text);
        }

        .modal-body {
            padding: 22px 24px;
        }

        .modal-body--center {
            padding-top: 28px;
            text-align: center;
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--border);
        }

        .modal-foot--center {
            justify-content: center;
        }


        /* =========================================================
           FORM
        ========================================================== */

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 700;
        }

        .form-group input,
        .form-group select,
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
            transition:
                border-color .2s ease,
                box-shadow .2s ease;
        }

        .form-group input {
            height: 40px;
        }

        .form-group select {
            height: 40px;
            cursor: pointer;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 82px;
            line-height: 1.5;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-muted);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary, #22c55e);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .form-note {
            margin: 14px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.6;
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


        /* =========================================================
           DETAIL
        ========================================================== */

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }

        .detail-item--full {
            grid-column: 1 / -1;
        }

        .detail-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .detail-value {
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }


        /* =========================================================
           CONFIRM DELETE
        ========================================================== */

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

        .confirm-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .confirm-title {
            margin: 0 0 8px;
            color: var(--text);
            font-size: 16px;
            font-weight: 700;
        }

        .confirm-text {
            margin: 0;
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.6;
        }

        .confirm-text strong {
            color: var(--text);
        }


        /* =========================================================
           TOAST
        ========================================================== */

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

        html[data-theme="dark"] .toast {
            box-shadow: 0 12px 30px rgba(0, 0, 0, .35);
        }

        .toast.toast--error {
            border-left-color: #ef4444;
        }

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

        .toast.toast--error .toast-icon {
            background: rgba(239, 68, 68, .14);
            color: #f87171;
        }

        .toast-icon svg {
            width: 13px;
            height: 13px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .toast-body {
            flex: 1;
            min-width: 0;
        }

        .toast-title {
            margin: 0 0 2px;
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
        }

        .toast-text {
            margin: 0;
            color: var(--text-secondary);
            font-size: 12.5px;
            line-height: 1.5;
            word-break: break-word;
        }

        .toast-close {
            flex-shrink: 0;
            padding: 0;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
        }

        .toast-close:hover {
            color: var(--text);
        }


        /* =========================================================
           RESPONSIVE
        ========================================================== */

        @media (max-width: 1100px) {

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 760px) {

            .table-toolbar {
                align-items: stretch;
                padding: 14px 16px;
            }

            .table-search {
                width: 100%;
            }

            .toolbar-right {
                width: 100%;
            }

            .toolbar-select {
                flex: 1 1 140px;
            }

            .toolbar-right .btn {
                flex: 0 0 auto;
            }

            .table-footer {
                padding: 14px 16px;
            }

        }

        @media (max-width: 560px) {

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .form-grid,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .form-group--full,
            .detail-item--full {
                grid-column: auto;
            }

            .modal-overlay {
                padding: 12px;
            }

            .modal-box {
                max-height: 94vh;
                border-radius: 12px;
            }

            .modal-head,
            .modal-body,
            .modal-foot {
                padding-left: 18px;
                padding-right: 18px;
            }

            .modal-foot {
                flex-wrap: wrap;
            }

            .modal-foot .btn {
                flex: 1;
            }

            .toolbar-right {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .toolbar-select,
            .toolbar-right .btn {
                width: 100%;
                min-width: 0;
            }

            .toolbar-right .btn {
                justify-content: center;
            }

            .toast-wrap {
                top: 12px;
                right: 12px;
                left: 12px;
                max-width: none;
            }

            .toast {
                width: 100%;
                max-width: none;
            }

        }
    </style>


    {{-- =============================================================
         SCRIPT
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

            if (searchInput) {
                searchInput.addEventListener('keyup', filterTabel);
            }

            if (filterSumber) {
                filterSumber.addEventListener('change', filterTabel);
            }

            if (filterJenis) {
                filterJenis.addEventListener('change', filterTabel);
            }

            if (filterPeriode) {
                filterPeriode.addEventListener('change', filterTabel);
            }


            /* Tutup modal ketika klik area luar */
            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {

                overlay.addEventListener('click', function(e) {

                    if (e.target === overlay) {
                        overlay.classList.remove('active');
                    }

                });

            });


            /* Tutup modal dengan Escape */
            document.addEventListener('keydown', function(e) {

                if (e.key === 'Escape') {

                    document
                        .querySelectorAll('.modal-overlay.active')
                        .forEach(function(overlay) {

                            overlay.classList.remove('active');

                        });

                }

            });

        });


        /* =========================================================
           MODAL
        ========================================================== */

        function bukaModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('active');
                document.body.classList.add('modal-open');
            }
        }


        function tutupModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('active');
            }

            if (!document.querySelector('.modal-overlay.active')) {
                document.body.classList.remove('modal-open');
            }
        }


        /* =========================================================
           FORMAT
        ========================================================== */

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }


        function formatTanggal(tgl) {

            if (!tgl) return '-';

            const d = new Date(
                String(tgl).length === 10
                    ? tgl + 'T00:00:00'
                    : tgl
            );

            if (isNaN(d)) return tgl;

            return d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }


        /* =========================================================
           TOAST
        ========================================================== */

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
                    <p class="toast-title">${title}</p>
                    <p class="toast-text">${text}</p>
                </div>

                <button
                    class="toast-close"
                    type="button"
                    aria-label="Tutup">
                    &times;
                </button>
            `;

            function hapusToast() {
                toast.remove();
            }

            toast
                .querySelector('.toast-close')
                .addEventListener('click', hapusToast);

            wrap.appendChild(toast);

            setTimeout(hapusToast, 3500);
        }


        /* =========================================================
           SEARCH + FILTER
        ========================================================== */

        function filterTabel() {

            const keyword =
                (document.getElementById('searchKeuangan')?.value || '')
                .toLowerCase()
                .trim();

            const sumber =
                document.getElementById('filterSumber')?.value || '';

            const jenis =
                document.getElementById('filterJenis')?.value || '';

            const periode =
                document.getElementById('filterPeriode')?.value || '';


            const rows =
                document.querySelectorAll(
                    '#keuanganTableBody tr[data-jenis]'
                );

            let tampil = 0;


            rows.forEach(function(row) {

                const cocokSumber =
                    !sumber ||
                    row.dataset.sumber === sumber;

                const cocokJenis =
                    !jenis ||
                    row.dataset.jenis === jenis;

                const cocokPeriode =
                    !periode ||
                    row.dataset.periode === periode;

                const cocokCari =
                    !keyword ||
                    row.textContent
                    .toLowerCase()
                    .includes(keyword);

                const cocok =
                    cocokSumber &&
                    cocokJenis &&
                    cocokPeriode &&
                    cocokCari;


                row.style.display = cocok ? '' : 'none';

                if (cocok) {
                    tampil++;
                }

            });


            const kosongCari =
                document.getElementById('keuanganKosongCari');

            if (kosongCari) {

                kosongCari.style.display =
                    (rows.length > 0 && tampil === 0)
                        ? ''
                        : 'none';

            }


            const info =
                document.getElementById('jumlahTampil');

            if (info) {
                info.textContent = tampil;
            }

        }


        /* =========================================================
           TAMBAH TRANSAKSI
        ========================================================== */

        function bukaModalTambah() {

            const form =
                document.getElementById('formTransaksi');

            if (form) {
                form.reset();
            }

            const tanggal =
                document.getElementById('transaksiTanggal');

            if (tanggal) {

                const now = new Date();

                const localDate =
                    new Date(
                        now.getTime() -
                        now.getTimezoneOffset() * 60000
                    )
                    .toISOString()
                    .substring(0, 10);

                tanggal.value = localDate;
            }

            bukaModal('modalTransaksi');
        }


        function simpanTransaksi(event) {

            event.preventDefault();


            const payload = {

                jenis:
                    document.getElementById(
                        'transaksiJenis'
                    ).value,

                tanggal:
                    document.getElementById(
                        'transaksiTanggal'
                    ).value,

                kategori:
                    document.getElementById(
                        'transaksiKategori'
                    ).value,

                jumlah:
                    document.getElementById(
                        'transaksiJumlah'
                    ).value,

                keterangan:
                    document.getElementById(
                        'transaksiKeterangan'
                    ).value

            };


            fetch(`{{ route('admin.keuangan.store') }}`, {

                method: 'POST',

                headers: {

                    'Content-Type':
                        'application/json',

                    'Accept':
                        'application/json',

                    'X-CSRF-TOKEN':
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content

                },

                body: JSON.stringify(payload)

            })

            .then(async (res) => {

                if (!res.ok) {

                    const err =
                        await res
                        .json()
                        .catch(() => ({}));

                    const pesan =
                        err.errors
                            ?
                            Object
                            .values(err.errors)
                            .flat()
                            .join(' ')
                            :
                            (
                                err.message ||
                                'Gagal menyimpan transaksi.'
                            );

                    throw new Error(pesan);
                }

                return res.json();

            })

            .then(() => {

                tutupModal('modalTransaksi');

                showToast(
                    'Berhasil disimpan',
                    'Transaksi baru telah ditambahkan.'
                );

                setTimeout(
                    () => window.location.reload(),
                    800
                );

            })

            .catch((err) => {

                showToast(
                    'Gagal menyimpan',
                    err.message,
                    'error'
                );

            });


            return false;
        }


        /* =========================================================
           DETAIL TRANSAKSI
        ========================================================== */

        function lihatDetail(item) {

            const badgeClass =
                item.jenis === 'Pemasukan'
                    ? 'badge--success'
                    : 'badge--danger';


            document.getElementById(
                'detailJenis'
            ).innerHTML =
                `<span class="badge ${badgeClass}">
                    ${item.jenis}
                </span>`;


            document.getElementById(
                'detailTanggal'
            ).textContent =
                formatTanggal(item.tanggal);


            document.getElementById(
                'detailKategori'
            ).textContent =
                item.kategori || '-';


            document.getElementById(
                'detailSumber'
            ).textContent =
                item.otomatis
                    ?
                    'Otomatis · ' +
                    item.sumber_label
                    :
                    (
                        item.sumber_label ||
                        'Kas Manual'
                    );


            const tanda =
                item.jenis === 'Pemasukan'
                    ? '+ '
                    : '- ';


            const jumlahElement =
                document.getElementById(
                    'detailJumlah'
                );

            jumlahElement.textContent =
                tanda +
                formatRupiah(item.jumlah);

            jumlahElement.classList.remove(
                'amount-in',
                'amount-out'
            );

            jumlahElement.classList.add(
                item.jenis === 'Pemasukan'
                    ? 'amount-in'
                    : 'amount-out'
            );


            document.getElementById(
                'detailKeterangan'
            ).textContent =
                item.keterangan || '-';


            bukaModal('modalDetail');
        }


        /* =========================================================
           HAPUS TRANSAKSI
        ========================================================== */

        let transaksiAkanDihapus = null;


        function hapusTransaksi(item) {

            transaksiAkanDihapus = item;


            document.getElementById(
                'hapusKategori'
            ).textContent =
                item.kategori || '-';


            document.getElementById(
                'hapusJumlah'
            ).textContent =
                formatRupiah(item.jumlah);


            bukaModal('modalHapus');
        }


        function konfirmasiHapusTransaksi() {

            if (!transaksiAkanDihapus) {
                return;
            }


            const id =
                transaksiAkanDihapus.id_kas;

            const kategori =
                transaksiAkanDihapus.kategori ||
                'Transaksi';


            fetch(
                `/admin/pages/keuangan/${id}`,
                {

                    method: 'DELETE',

                    headers: {

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content

                    }

                }
            )

            .then(async (res) => {

                if (!res.ok) {

                    const err =
                        await res
                        .json()
                        .catch(() => ({}));

                    throw new Error(
                        err.message ||
                        'Gagal menghapus transaksi.'
                    );
                }

                return res.json();

            })

            .then(() => {

                tutupModal('modalHapus');

                showToast(
                    'Berhasil dihapus',
                    `Transaksi "${kategori}" telah dihapus.`
                );

                transaksiAkanDihapus = null;

                setTimeout(
                    () => window.location.reload(),
                    800
                );

            })

            .catch((err) => {

                showToast(
                    'Gagal menghapus',
                    err.message,
                    'error'
                );

            });

        }
    </script>

@endsection
