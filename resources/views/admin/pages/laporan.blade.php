@extends('admin.layouts.app')

@section('title', 'Laporan & Riwayat')
@section('active', 'laporan')
@section('crumbs', 'Konten & Laporan | Laporan & Riwayat')

@section('content')

    @php
        $activeTab = request('tab') === 'setoran' ? 'setoran' : 'penjualan';

        $statusMap = [
            'pending' => ['warning', 'Menunggu'],
            'approved' => ['success', 'Disetujui'],
            'selesai' => ['success', 'Disetujui'],
            'rejected' => ['danger', 'Ditolak'],
        ];
    @endphp

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">KONTEN & LAPORAN</span>

            <h1 class="hero-title">
                Laporan <span class="accent">& Riwayat</span>
            </h1>

            <p class="hero-sub">
                Pantau transaksi barang keluar, penyetoran warga,
                dan aktivitas operasional Bank Sampah.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="window.print()">
                <svg viewBox="0 0 24 24">
                    <path d="M6 9V4h12v5"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <path d="M6 14h12v7H6z"></path>
                </svg>
                Cetak Laporan
            </button>
        </div>
    </section>


    <!-- SUMMARY -->
    <section class="summary-grid">

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
            <div class="summary-info">Akumulasi hasil penjualan</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Volume Keluar</div>
                    <div class="summary-value">
                        {{ number_format($totalBeratKeluar, 2, ',', '.') }}
                        <small>Kg</small>
                    </div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 6h18"></path>
                        <path d="M5 6l1 14h12l1-14"></path>
                        <path d="M9 10v6M15 10v6"></path>
                        <path d="M9 6V3h6v3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Sampah terjual / terdistribusi</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Transaksi</div>
                    <div class="summary-value">{{ $totalTransaksiKeluar }}</div>
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
            <div class="summary-info">Jumlah transaksi barang keluar</div>
        </div>

    </section>


    <section class="card report-card">

        <!-- TAB -->
        <div class="tabs-header">

            <button type="button" data-tab="penjualan"
                class="report-tab {{ $activeTab === 'penjualan' ? 'tab-active' : '' }}">
                <svg viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z"></path>
                    <path d="M8 8h8M8 12h8M8 16h5"></path>
                </svg>
                Penjualan
                <span class="tab-count">{{ $riwayatPenjualan->total() }}</span>
            </button>

            <button type="button" data-tab="setoran" class="report-tab {{ $activeTab === 'setoran' ? 'tab-active' : '' }}">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M17 7H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H7"></path>
                </svg>
                Penyetoran Warga
                <span class="tab-count">{{ $riwayatSetoran->total() }}</span>
            </button>

        </div>


        <!-- ========================================= -->
        <!-- TAB PENJUALAN                              -->
        <!-- ========================================= -->
        <div class="report-panel" data-panel="penjualan" @if ($activeTab !== 'penjualan') hidden @endif>

            <div class="table-toolbar">

                <div class="toolbar-left">
                    <div class="table-search">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>

                        <input type="text" id="searchPenjualan" placeholder="Cari nama pembeli..." autocomplete="off">
                    </div>

                    @if (request('search_penjualan'))
                        <a href="{{ route('admin.laporan.index', ['tab' => 'penjualan']) }}" class="btn btn--ghost">
                            <svg viewBox="0 0 24 24">
                                <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>

            </div>


            <div class="table-responsive">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pembeli / Pengepul</th>
                            <th>Kategori Sampah</th>
                            <th class="col-num">Berat</th>
                            <th class="col-num">Harga / Gram</th>
                            <th class="col-num">Total Nominal</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($riwayatPenjualan as $item)
                            <tr>

                                <td class="nowrap">
                                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') : '-' }}
                                </td>

                                <td>
                                    <div class="party">
                                        <span class="avatar">
                                            {{ mb_strtoupper(mb_substr($item->pembeli ?: '-', 0, 1)) }}
                                        </span>
                                        <strong>{{ $item->pembeli ?: '-' }}</strong>
                                    </div>
                                </td>

                                <td>
                                    @if ($item->kategori->nama_kategori ?? null)
                                        <strong>{{ $item->kategori->nama_kategori }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="col-num nowrap">
                                    <strong>{{ number_format($item->berat_gram / 1000, 2, ',', '.') }}</strong>
                                    <span class="unit">Kg</span>
                                </td>

                                <td class="col-num nowrap">
                                    Rp {{ number_format($item->harga_jual_per_gram, 0, ',', '.') }}
                                </td>

                                <td class="col-num nowrap">
                                    <strong class="amount-in">Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
                                </td>

                                <td>{{ $item->admin->nama ?? '-' }}</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                    <strong>
                                        {{ request('search_penjualan') ? 'Pembeli Tidak Ditemukan' : 'Belum Ada Data Penjualan' }}
                                    </strong>
                                    <p style="margin: 5px 0 0; color: #6b7280;">
                                        {{ request('search_penjualan')
                                            ? 'Coba gunakan nama pembeli lain atau reset pencarian.'
                                            : 'Transaksi barang keluar akan muncul di sini.' }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <div class="table-info">
                    Riwayat penjualan
                    <strong>{{ $riwayatPenjualan->total() }}</strong>
                    transaksi
                </div>

                <div class="pagination-wrap">
                    {{ $riwayatPenjualan->appends(array_merge(request()->except('penjualan_page', 'tab'), ['tab' => 'penjualan']))->links() }}
                </div>

            </div>

        </div>


        <!-- ========================================= -->
        <!-- TAB SETORAN WARGA                         -->
        <!-- ========================================= -->
        <div class="report-panel" data-panel="setoran" @if ($activeTab !== 'setoran') hidden @endif>

            <div class="table-toolbar">

                <div class="toolbar-left">
                    <div class="table-search">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>

                        <input type="text" id="searchSetoran" placeholder="Cari nama warga atau NIK..."
                            autocomplete="off">
                    </div>

                    @if (request('search_setoran'))
                        <a href="{{ route('admin.laporan.index', ['tab' => 'setoran']) }}" class="btn btn--ghost">
                            <svg viewBox="0 0 24 24">
                                <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>

            </div>


            <div class="table-responsive">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Tanggal Setor</th>
                            <th>Warga</th>
                            <th>Kategori Sampah</th>
                            <th class="col-num">Total Berat</th>
                            <th class="col-num">Total Nilai</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($riwayatSetoran as $setoran)
                            @php
                                $details = $setoran->details ?? collect();

                                $namaKategori = $details
                                    ->map(function ($detail) {
                                        return optional($detail->kategori)->nama_lengkap ??
                                            optional($detail->kategori)->nama_kategori;
                                    })
                                    ->filter()
                                    ->unique()
                                    ->implode(', ');

                                [$badgeVariant, $badgeLabel] = $statusMap[$setoran->status] ?? [
                                    'warning',
                                    ucfirst((string) $setoran->status),
                                ];
                            @endphp

                            <tr>

                                <td class="nowrap">
                                    {{ $setoran->tanggal_setoran ? \Carbon\Carbon::parse($setoran->tanggal_setoran)->translatedFormat('d M Y') : '-' }}
                                </td>

                                <td>
                                    <div class="party">
                                        <span class="avatar">
                                            {{ mb_strtoupper(mb_substr($setoran->warga->nama ?? '-', 0, 1)) }}
                                        </span>
                                        <span class="party-info">
                                            <strong>{{ $setoran->warga->nama ?? '-' }}</strong>
                                            <span class="mono">{{ $setoran->warga->nik ?? '-' }}</span>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if ($namaKategori)
                                        <strong>{{ $namaKategori }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="col-num nowrap">
                                    <strong>{{ number_format($setoran->total_berat / 1000, 2, ',', '.') }}</strong>
                                    <span class="unit">Kg</span>
                                </td>

                                <td class="col-num nowrap">
                                    <strong>Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}</strong>
                                </td>

                                <td>
                                    <span class="badge badge--{{ $badgeVariant }}">{{ $badgeLabel }}</span>
                                </td>

                                <td class="cell-note" title="{{ $setoran->catatan_admin }}">
                                    {{ $setoran->catatan_admin ? \Illuminate\Support\Str::limit($setoran->catatan_admin, 60) : '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                    <strong>
                                        {{ request('search_setoran') ? 'Setoran Tidak Ditemukan' : 'Belum Ada Data Setoran' }}
                                    </strong>
                                    <p style="margin: 5px 0 0; color: #6b7280;">
                                        {{ request('search_setoran')
                                            ? 'Coba gunakan nama warga atau NIK lain, atau reset pencarian.'
                                            : 'Penyetoran sampah warga akan muncul di sini.' }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <div class="table-info">
                    Riwayat penyetoran
                    <strong>{{ $riwayatSetoran->total() }}</strong>
                    transaksi
                </div>

                <div class="pagination-wrap">
                    {{ $riwayatSetoran->appends(array_merge(request()->except('setoran_page', 'tab'), ['tab' => 'setoran']))->links() }}
                </div>

            </div>

        </div>

    </section>


    <style>
        /* =========================================================
                                                                   TEMA - mengikuti halaman Keuangan
                                                                   (kartu ringkasan, ikon, badge, warna, tabel)
                                                                   ========================================================= */

        .report-tab,
        .table-search input,
        .btn {
            font-family: inherit;
        }

        /* =========================
                                                                   SUMMARY
                                                                ========================= */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
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

        .summary-value small {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
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

        @media (max-width: 800px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }

        /* =========================
                                                                   CARD & TAB
                                                                ========================= */

        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
        }

        .report-card {
            margin-bottom: 20px;
        }

        .report-card .report-panel {
            display: block;
            width: 100%;
        }

        .report-card .report-panel[hidden] {
            display: none !important;
        }

        .tabs-header {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 0 24px;
            border-bottom: 1px solid #edf0f2;
            background: #fff;
        }

        .report-tab {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            background: transparent;
            color: #6b7280;
            padding: 16px 8px 14px;
            margin-right: 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: .15s ease;
        }

        .report-tab svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .report-tab:hover,
        .report-tab.tab-active {
            color: var(--primary, #4338ca);
        }

        .report-tab.tab-active::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 2px;
            background: var(--primary, #4338ca);
            border-radius: 2px 2px 0 0;
        }

        .tab-count {
            min-width: 22px;
            padding: 1px 7px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.6;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .report-tab.tab-active .tab-count {
            background: #eef2ff;
            color: var(--primary, #4338ca);
        }

        /* =========================
                                                                   TABLE
                                                                ========================= */

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-left .btn {
            height: 40px;
            padding: 0 16px;
            white-space: nowrap;
        }

        .table-search {
            width: 360px;
            max-width: 100%;
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
            pointer-events: none;
        }

        .table-search form {
            margin: 0;
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

        .data-table .col-num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .nowrap {
            white-space: nowrap;
        }

        .amount-in {
            color: #16a34a;
        }

        .unit {
            color: #9ca3af;
            font-size: 12px;
        }

        .mono {
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
        }

        .cell-note {
            max-width: 260px;
            color: #6b7280;
        }

        /* nama pembeli / warga + inisial */
        .party {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .party-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #eef2ff;
            color: var(--primary, #4338ca);
            font-size: 12px;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
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

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px 24px;
        }

        .table-info {
            font-size: 13px;
            color: #6b7280;
        }

        .table-info strong {
            color: #1f2937;
            font-weight: 700;
        }

        /* Pagination bawaan Laravel */
        .pagination-wrap {
            display: flex;
            align-items: center;
        }

        .pagination-wrap nav {
            margin: 0;
        }

        .pagination-wrap svg {
            width: 16px;
            height: 16px;
        }

        .pagination-wrap .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pagination-wrap .pagination .page-link,
        .pagination-wrap .pagination li>span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            background: #fff;
            color: #6b7280;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .pagination-wrap .pagination .page-link:hover {
            border-color: var(--primary, #4338ca);
            color: var(--primary, #4338ca);
        }

        .pagination-wrap .pagination .active .page-link,
        .pagination-wrap .pagination .active>span {
            background: var(--primary, #4338ca);
            border-color: var(--primary, #4338ca);
            color: #fff;
        }

        .pagination-wrap .pagination .disabled .page-link,
        .pagination-wrap .pagination .disabled>span {
            background: #f8fafc;
            color: #cbd5e1;
        }

        @media (max-width: 640px) {
            .tabs-header {
                padding: 0 16px;
                overflow-x: auto;
            }

            .report-tab {
                white-space: nowrap;
                margin-right: 10px;
            }

            .table-toolbar {
                padding: 14px 16px;
            }

            .toolbar-left {
                width: 100%;
            }

            .table-search {
                width: 100%;
            }

            .table-footer {
                padding: 14px 16px;
                justify-content: center;
            }
        }

        /* =========================
                                                                   PRINT
                                                                ========================= */

        @media print {

            .hero-actions,
            .table-toolbar,
            .tabs-header,
            .pagination-wrap {
                display: none !important;
            }

            .hero {
                margin-bottom: 20px;
            }

            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .card,
            .summary-card {
                box-shadow: none !important;
            }

            .data-table {
                min-width: 100%;
            }

            .data-table th,
            .data-table td {
                padding: 8px 10px;
                font-size: 11px;
            }

            .data-table th,
            .badge,
            .summary-icon {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .avatar {
                display: none;
            }
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* =====================================
               TAB LAPORAN
            ===================================== */

            const tabs = document.querySelectorAll('.report-tab[data-tab]');
            const panels = document.querySelectorAll('.report-panel[data-panel]');

            tabs.forEach(function(tab) {

                tab.addEventListener('click', function() {

                    const target = this.dataset.tab;

                    tabs.forEach(function(item) {
                        item.classList.toggle(
                            'tab-active',
                            item === tab
                        );
                    });

                    panels.forEach(function(panel) {
                        panel.hidden = panel.dataset.panel !== target;
                    });

                });

            });


            /* =====================================
               LIVE SEARCH PENJUALAN
            ===================================== */

            const searchPenjualan =
                document.getElementById('searchPenjualan');

            if (searchPenjualan) {

                searchPenjualan.addEventListener('input', function() {

                    const keyword =
                        this.value.toLowerCase().trim();

                    const panel =
                        document.querySelector(
                            '[data-panel="penjualan"]'
                        );

                    if (!panel) return;

                    const rows =
                        panel.querySelectorAll('tbody tr');

                    rows.forEach(function(row) {

                        const text =
                            row.textContent.toLowerCase();

                        if (text.includes(keyword)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }

                    });

                });

            }


            /* =====================================
               LIVE SEARCH SETORAN
            ===================================== */

            const searchSetoran =
                document.getElementById('searchSetoran');

            if (searchSetoran) {

                searchSetoran.addEventListener('input', function() {

                    const keyword =
                        this.value.toLowerCase().trim();

                    const panel =
                        document.querySelector(
                            '[data-panel="setoran"]'
                        );

                    if (!panel) return;

                    const rows =
                        panel.querySelectorAll('tbody tr');

                    rows.forEach(function(row) {

                        const text =
                            row.textContent.toLowerCase();

                        if (text.includes(keyword)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }

                    });

                });

            }

        });
    </script>

@endsection
