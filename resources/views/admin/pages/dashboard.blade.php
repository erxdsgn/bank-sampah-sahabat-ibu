@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@section('content')

    @php
        // =========================================================
        // KONVERSI GRAM KE KG
        // =========================================================
        $gramKeKg = fn($gram) => ((float) $gram) / 1000;

        // =========================================================
        // RIWAYAT SETORAN
        // Data terbaru berada di paling atas
        // =========================================================
        $riwayat = collect($riwayatSetoran)
            ->sortByDesc('id_setoran')
            ->take(10)
            ->values();
    @endphp


    {{-- =========================================================
        HERO
    ========================================================== --}}

    <section class="hero">
        <div class="hero-text">

            <span class="eyebrow">
                RINGKASAN
            </span>

            <h1 class="hero-title">
                <span class="accent">
                    Dashboard
                </span>
            </h1>

            <p class="hero-sub">
                Ringkasan aktivitas Bank Sampah.
            </p>

        </div>
    </section>


    {{-- =========================================================
        STATISTIK UTAMA
    ========================================================== --}}

    <div class="stat-grid">

        {{-- JUMLAH WARGA --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--indigo">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path>
                    <circle cx="19" cy="8" r="2.3"></circle>
                    <path d="M17 21v-1a3 3 0 0 0-2-2.83"></path>
                </svg>
            </div>

            <div>
                <div class="stat-label">
                    Jumlah Warga Terdaftar
                </div>

                <div class="stat-value">
                    {{ number_format($jumlahWarga, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Warga terdaftar
                </div>
            </div>

        </div>


        {{-- TOTAL BERAT SAMPAH --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Total Berat Sampah
                </div>

                <div class="stat-value">

                    {{ number_format(
                        $gramKeKg($totalBeratSampah),
                        2,
                        ',',
                        '.'
                    ) }}

                    <span class="stat-unit">
                        Kg
                    </span>

                </div>

                <div class="stat-description">
                    Total sampah disetor
                </div>

            </div>

        </div>


        {{-- TOTAL SALDO WARGA --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--blue">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Total Saldo Warga
                </div>

                <div class="stat-value">
                    Rp {{ number_format($totalSaldoWarga, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Saldo seluruh warga
                </div>

            </div>

        </div>


        {{-- SALDO KAS --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--teal">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M6 6v-1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Saldo Kas
                </div>

                <div class="stat-value">
                    Rp {{ number_format($saldoKas, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Pemasukan dikurangi pengeluaran
                </div>

            </div>

        </div>


        {{-- PENCAIRAN --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--orange">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M3 10h18"></path>
                    <path d="M16 15h2"></path>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Total Pencairan Saldo
                </div>

                <div class="stat-value">
                    Rp {{ number_format($totalPencairanSaldo, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Total pencairan
                </div>

            </div>

        </div>


        {{-- PENJUALAN --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24">
                    <path d="M3 7h11v8H3z"></path>
                    <path d="M14 10h4l3 3v2h-7z"></path>
                    <circle cx="7" cy="18" r="2"></circle>
                    <circle cx="17" cy="18" r="2"></circle>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Penjualan ke Pengepul
                </div>

                <div class="stat-value">
                    Rp {{ number_format($totalPenjualanPengepul, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Total barang keluar
                </div>

            </div>

        </div>


        {{-- KATEGORI --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--purple">
                <svg viewBox="0 0 24 24">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Kategori Sampah
                </div>

                <div class="stat-value">
                    {{ number_format($jumlahKategoriSampah, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Kategori terdaftar
                </div>

            </div>

        </div>


        {{-- TRANSAKSI --}}
        <div class="stat-card">

            <div class="stat-icon stat-icon--indigo">
                <svg viewBox="0 0 24 24">
                    <path d="M8 6h13"></path>
                    <path d="M8 12h13"></path>
                    <path d="M8 18h13"></path>
                    <path d="M3 6h.01"></path>
                    <path d="M3 12h.01"></path>
                    <path d="M3 18h.01"></path>
                </svg>
            </div>

            <div>

                <div class="stat-label">
                    Jumlah Setoran
                </div>

                <div class="stat-value">
                    {{ number_format($jumlahSetoran, 0, ',', '.') }}
                </div>

                <div class="stat-description">
                    Total transaksi setoran
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        GRAFIK
    ========================================================== --}}

    <div class="dashboard-columns">

        {{-- GRAFIK SETORAN --}}
        <div class="card chart-card chart-card--large">

            <div class="card-head">

                <div>

                    <h2 class="card-title">
                        Setoran Sampah per Bulan
                    </h2>

                    <p class="card-sub">
                        Total berat sampah tahun {{ now()->year }}
                    </p>

                </div>

            </div>

            <div class="chart-container">
                <canvas id="setoranBulananChart"></canvas>
            </div>

        </div>


        {{-- STATISTIK JENIS SAMPAH --}}
        <div class="card chart-card chart-card--small">

            <div class="card-head">

                <div>

                    <h2 class="card-title">
                        Statistik Jenis Sampah
                    </h2>

                    <p class="card-sub">
                        Berdasarkan total berat
                    </p>

                </div>

            </div>

            <div class="chart-container">
                <canvas id="jenisSampahChart"></canvas>
            </div>

        </div>

    </div>


    {{-- =========================================================
        RIWAYAT SETORAN
    ========================================================== --}}

    <section class="card history-card">

        <div class="card-head">

            <div>

                <h2 class="card-title">
                    Riwayat Setoran
                </h2>

                <p class="card-sub">
                    10 transaksi setoran terbaru
                </p>

            </div>

        </div>


        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Warga</th>
                        <th>Berat</th>
                        <th>Nilai</th>
                        <th>Status</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($riwayat as $index => $setoran)

                        <tr>

                            <td>
                                {{ $index + 1 }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($setoran->tanggal_setoran)->format('d/m/Y') }}
                            </td>

                            <td>
                                <strong>
                                    {{ $setoran->nama }}
                                </strong>
                            </td>

                            <td>
                                {{ number_format(
                                    $gramKeKg($setoran->total_berat),
                                    2,
                                    ',',
                                    '.'
                                ) }}
                                Kg
                            </td>

                            <td>
                                Rp {{ number_format(
                                    $setoran->total_nilai,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td>

                                <span class="badge badge--success">
                                    {{ $setoran->status }}
                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="empty-table"
                            >

                                <div class="empty-icon">
                                    📋
                                </div>

                                <strong>
                                    Belum Ada Riwayat Setoran
                                </strong>

                                <p class="empty-description">
                                    Belum ada transaksi setoran yang tercatat.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>


    {{-- =========================================================
        CSS DASHBOARD
    ========================================================== --}}

    <style>

        /* =====================================================
           THEME VARIABLES
        ===================================================== */

        :root {

            --dashboard-page:
                #f5f7fb;

            --dashboard-card:
                #ffffff;

            --dashboard-card-secondary:
                #f8fafc;

            --dashboard-text:
                #1f2937;

            --dashboard-text-secondary:
                #6b7280;

            --dashboard-text-muted:
                #9ca3af;

            --dashboard-border:
                #e5e7eb;

            --dashboard-table-head:
                #f8fafc;

            --dashboard-table-row:
                #ffffff;

            --dashboard-table-hover:
                #fafafa;

            --dashboard-chart-grid:
                #e5e7eb;

            --dashboard-chart-text:
                #6b7280;

            --dashboard-empty:
                #f8fafc;

            --dashboard-shadow:
                0 8px 25px rgba(15, 23, 42, .06);
        }


        /* =====================================================
           DARK THEME
        ===================================================== */

        [data-theme="dark"] {

            --dashboard-page:
                #0b1220;

            --dashboard-card:
                #151d2f;

            --dashboard-card-secondary:
                #1b2438;

            --dashboard-text:
                #f1f5f9;

            --dashboard-text-secondary:
                #aab6c8;

            --dashboard-text-muted:
                #748198;

            --dashboard-border:
                #29364d;

            --dashboard-table-head:
                #111a2c;

            --dashboard-table-row:
                #151d2f;

            --dashboard-table-hover:
                #202b40;

            --dashboard-chart-grid:
                #2c3950;

            --dashboard-chart-text:
                #aab6c8;

            --dashboard-empty:
                #111a2c;

            --dashboard-shadow:
                0 8px 25px rgba(0, 0, 0, .25);
        }


        /* =====================================================
           BODY
        ===================================================== */

        body {

            background:
                var(--dashboard-page);

            color:
                var(--dashboard-text);

            transition:
                background-color .25s ease,
                color .25s ease;
        }


        /* =====================================================
           STAT GRID
        ===================================================== */

        .stat-grid {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 12px;

            margin-bottom: 16px;
        }


        /* =====================================================
           STAT CARD
        ===================================================== */

        .stat-card {

            background:
                var(--dashboard-card);

            border:
                1px solid var(--dashboard-border);

            border-radius:
                14px;

            padding:
                14px 16px;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            color:
                var(--dashboard-text);

            box-shadow:
                var(--dashboard-shadow);

            transition:
                background-color .25s ease,
                border-color .25s ease,
                color .25s ease,
                box-shadow .25s ease;
        }


        /* =====================================================
           STAT ICON
        ===================================================== */

        .stat-icon {

            width:
                40px;

            height:
                40px;

            flex:
                0 0 40px;

            border-radius:
                11px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;
        }


        .stat-icon svg {

            width:
                20px;

            height:
                20px;

            fill:
                none;

            stroke:
                currentColor;

            stroke-width:
                1.8;
        }


        .stat-icon--indigo {

            background:
                #eef2ff;

            color:
                #4338ca;
        }


        .stat-icon--green {

            background:
                #dcfce7;

            color:
                #16a34a;
        }


        .stat-icon--blue {

            background:
                #e0f2fe;

            color:
                #0284c7;
        }


        .stat-icon--orange {

            background:
                #ffedd5;

            color:
                #ea580c;
        }


        .stat-icon--purple {

            background:
                #f3e8ff;

            color:
                #7c3aed;
        }


        .stat-icon--teal {

            background:
                #ccfbf1;

            color:
                #0d9488;
        }


        /* =====================================================
           STAT TEXT
        ===================================================== */

        .stat-label {

            font-size:
                12px;

            color:
                var(--dashboard-text-secondary);

            margin:
                0 0 3px;

            white-space:
                nowrap;
        }


        .stat-value {

            font-size:
                18px;

            font-weight:
                700;

            color:
                var(--dashboard-text);

            line-height:
                1.2;
        }


        .stat-unit {

            font-size:
                12px;

            font-weight:
                500;

            color:
                var(--dashboard-text-secondary);
        }


        .stat-description {

            margin-top:
                2px;

            font-size:
                11px;

            color:
                var(--dashboard-text-muted);
        }


        /* =====================================================
           DASHBOARD COLUMNS
        ===================================================== */

        .dashboard-columns {

            display:
                grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap:
                16px;

            margin-bottom:
                16px;
        }


        /* GRAFIK BESAR */

        .chart-card--large {

            grid-column:
                span 3;
        }


        /* GRAFIK KECIL */

        .chart-card--small {

            grid-column:
                span 1;
        }


        /* =====================================================
           CARD
        ===================================================== */

        .card {

            background:
                var(--dashboard-card);

            border:
                1px solid var(--dashboard-border);

            border-radius:
                14px;

            overflow:
                hidden;

            margin-bottom:
                16px;

            color:
                var(--dashboard-text);

            box-shadow:
                var(--dashboard-shadow);

            transition:
                background-color .25s ease,
                border-color .25s ease,
                color .25s ease,
                box-shadow .25s ease;
        }


        /* =====================================================
           CHART CARD
        ===================================================== */

        .chart-card {

            padding:
                18px 20px;

            margin-bottom:
                0;

            min-width:
                0;
        }


        /* =====================================================
           CARD HEADER
        ===================================================== */

        .card-head {

            padding:
                20px 22px;

            border-bottom:
                1px solid var(--dashboard-border);
        }


        .chart-card .card-head {

            padding:
                0 0 16px;

            border-bottom:
                none;
        }


        /* =====================================================
           CARD TITLE
        ===================================================== */

        .card-title {

            margin:
                0 0 5px;

            font-size:
                17px;

            font-weight:
                700;

            color:
                var(--dashboard-text);
        }


        /* =====================================================
           CARD SUBTITLE
        ===================================================== */

        .card-sub {

            margin:
                0;

            font-size:
                13px;

            color:
                var(--dashboard-text-secondary);
        }


        /* =====================================================
           CHART
        ===================================================== */

        .chart-container {

            height:
                260px;

            position:
                relative;
        }


        /* =====================================================
           EMPTY CHART
        ===================================================== */

        .chart-empty {

            height:
                100%;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            text-align:
                center;

            font-size:
                13px;

            color:
                var(--dashboard-text-secondary);

            border:
                1px dashed var(--dashboard-border);

            border-radius:
                12px;

            background:
                var(--dashboard-empty);

            transition:
                background-color .25s ease,
                border-color .25s ease,
                color .25s ease;
        }


        /* =====================================================
           TABLE RESPONSIVE
        ===================================================== */

        .table-responsive {

            width:
                100%;

            overflow-x:
                auto;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .data-table {

            width:
                100%;

            border-collapse:
                collapse;

            background:
                var(--dashboard-table-row);

            color:
                var(--dashboard-text);
        }


        /* =====================================================
           TABLE HEADER
        ===================================================== */

        .data-table th {

            background:
                var(--dashboard-table-head);

            color:
                var(--dashboard-text-secondary);

            font-size:
                12px;

            font-weight:
                700;

            text-transform:
                uppercase;

            padding:
                13px 16px;

            text-align:
                left;

            border-top:
                1px solid var(--dashboard-border);

            border-bottom:
                1px solid var(--dashboard-border);

            white-space:
                nowrap;

            transition:
                background-color .25s ease,
                color .25s ease,
                border-color .25s ease;
        }


        /* =====================================================
           TABLE BODY
        ===================================================== */

        .data-table td {

            padding:
                16px;

            border-bottom:
                1px solid var(--dashboard-border);

            font-size:
                13px;

            color:
                var(--dashboard-text);

            vertical-align:
                middle;

            background:
                var(--dashboard-table-row);

            transition:
                background-color .25s ease,
                color .25s ease,
                border-color .25s ease;
        }


        /* =====================================================
           TABLE STRONG
        ===================================================== */

        .data-table td strong {

            color:
                var(--dashboard-text);
        }


        /* =====================================================
           TABLE HOVER
        ===================================================== */

        .data-table tbody tr:hover td {

            background:
                var(--dashboard-table-hover);
        }


        /* =====================================================
           EMPTY TABLE
        ===================================================== */

        .empty-table {

            text-align:
                center;

            padding:
                40px !important;

            color:
                var(--dashboard-text);
        }


        .empty-icon {

            font-size:
                30px;

            margin-bottom:
                10px;
        }


        .empty-description {

            margin:
                5px 0 0;

            color:
                var(--dashboard-text-secondary);
        }


        /* =====================================================
           BADGE
        ===================================================== */

        .badge {

            padding:
                6px 12px;

            border-radius:
                20px;

            font-size:
                11px;

            font-weight:
                700;
        }


        .badge--success {

            background:
                #dcfce7;

            color:
                #15803d;
        }


        /* =====================================================
           DARK MODE BADGE
        ===================================================== */

        [data-theme="dark"] .badge--success {

            background:
                rgba(34, 197, 94, .16);

            color:
                #4ade80;
        }


        /* =====================================================
           DARK MODE CARD FORCE
        ===================================================== */

        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .card,
        [data-theme="dark"] .chart-card,
        [data-theme="dark"] .history-card {

            background:
                #151d2f;

            color:
                #f1f5f9;

            border-color:
                #29364d;
        }


        /* =====================================================
           DARK MODE TABLE FORCE
        ===================================================== */

        [data-theme="dark"] .data-table,
        [data-theme="dark"] .data-table tbody,
        [data-theme="dark"] .data-table tr,
        [data-theme="dark"] .data-table td {

            background:
                #151d2f;

            color:
                #f1f5f9;
        }


        [data-theme="dark"] .data-table th {

            background:
                #111a2c;

            color:
                #aab6c8;

            border-color:
                #29364d;
        }


        [data-theme="dark"] .data-table td {

            border-color:
                #29364d;
        }


        [data-theme="dark"] .data-table tbody tr:hover td {

            background:
                #202b40;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .stat-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 900px) {

            .dashboard-columns {

                grid-template-columns:
                    1fr;
            }


            .chart-card--large,
            .chart-card--small {

                grid-column:
                    span 1;
            }

        }


        @media (max-width: 560px) {

            .stat-grid {

                grid-template-columns:
                    1fr;
            }

        }

    </style>


    {{-- =========================================================
        CHART.JS
    ========================================================== --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                /* =================================================
                   HELPER
                ================================================= */

                function keArray(value) {

                    if (Array.isArray(value)) {
                        return value;
                    }

                    return Object.values(value || {});
                }


                function formatKg(value) {

                    return (
                        Number(value) || 0
                    ).toLocaleString(
                        'id-ID',
                        {
                            maximumFractionDigits: 2
                        }
                    ) + ' Kg';
                }


                function tampilKosong(
                    canvas,
                    teks
                ) {

                    if (!canvas) {
                        return;
                    }

                    canvas.parentElement.innerHTML =
                        '<div class="chart-empty">' +
                        teks +
                        '</div>';
                }


                /* =================================================
                   CANVAS
                ================================================= */

                const canvasBulanan =
                    document.getElementById(
                        'setoranBulananChart'
                    );

                const canvasJenis =
                    document.getElementById(
                        'jenisSampahChart'
                    );


                /* =================================================
                   CEK CHART.JS
                ================================================= */

                if (
                    typeof Chart === 'undefined'
                ) {

                    const pesan =
                        'Grafik tidak dapat dimuat. Periksa koneksi internet.';

                    if (canvasBulanan) {
                        tampilKosong(
                            canvasBulanan,
                            pesan
                        );
                    }

                    if (canvasJenis) {
                        tampilKosong(
                            canvasJenis,
                            pesan
                        );
                    }

                    return;
                }


                /* =================================================
                   THEME COLORS
                ================================================= */

                function getThemeColors() {

                    const isDark =
                        document.documentElement
                            .getAttribute(
                                'data-theme'
                            ) === 'dark';


                    return {

                        isDark: isDark,

                        text: isDark
                            ? '#aab6c8'
                            : '#6b7280',

                        title: isDark
                            ? '#f1f5f9'
                            : '#1f2937',

                        grid: isDark
                            ? '#2c3950'
                            : '#e5e7eb',

                        border: isDark
                            ? '#151d2f'
                            : '#ffffff',

                        tooltipBackground: isDark
                            ? '#111a2c'
                            : '#ffffff',

                        tooltipText: isDark
                            ? '#f1f5f9'
                            : '#1f2937'
                    };
                }


                /* =================================================
                   DATA SETORAN BULANAN
                ================================================= */

                const setoranData =
                    keArray(
                        @json($setoranPerBulan)
                    );


                const bulan = [

                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'Mei',
                    'Jun',
                    'Jul',
                    'Agu',
                    'Sep',
                    'Okt',
                    'Nov',
                    'Des'

                ];


                const dataBulanan =
                    Array(12).fill(0);


                setoranData.forEach(
                    function (item) {

                        const idx =
                            Number(
                                item.bulan
                            ) - 1;


                        if (
                            idx >= 0 &&
                            idx < 12
                        ) {

                            dataBulanan[idx] =
                                (
                                    Number(
                                        item.total_berat
                                    ) || 0
                                ) / 1000;
                        }

                    }
                );


                /* =================================================
                   DATA JENIS SAMPAH
                ================================================= */

                const jenisData =
                    keArray(
                        @json($statistikJenisSampah)
                    );


                const namaJenis =
                    jenisData.map(
                        function (item) {

                            return (
                                item.nama_kategori ||
                                item.nama_lengkap ||
                                item.nama ||
                                'Tanpa nama'
                            );

                        }
                    );


                const beratJenis =
                    jenisData.map(
                        function (item) {

                            return (
                                Number(
                                    item.total_berat
                                ) || 0
                            ) / 1000;

                        }
                    );


                const totalJenis =
                    beratJenis.reduce(
                        function (a, b) {

                            return a + b;

                        },
                        0
                    );


                /* =================================================
                   WARNA DOUGHNUT
                ================================================= */

                const warna = [

                    '#16a34a',
                    '#0284c7',
                    '#ea580c',
                    '#7c3aed',
                    '#0d9488',
                    '#4338ca',
                    '#d97706',
                    '#dc2626',
                    '#64748b',
                    '#84cc16'

                ];


                /* =================================================
                   CHART VARIABLES
                ================================================= */

                let chartBulanan = null;
                let chartJenis = null;


                /* =================================================
                   CHART BULANAN
                ================================================= */

                if (canvasBulanan) {

                    chartBulanan =
                        new Chart(
                            canvasBulanan,
                            {

                                type: 'bar',

                                data: {

                                    labels: bulan,

                                    datasets: [

                                        {

                                            label:
                                                'Berat Sampah (Kg)',

                                            data:
                                                dataBulanan,

                                            backgroundColor:
                                                '#16a34a',

                                            borderRadius:
                                                6,

                                            maxBarThickness:
                                                32

                                        }

                                    ]

                                },


                                options: {

                                    responsive:
                                        true,

                                    maintainAspectRatio:
                                        false,


                                    plugins: {

                                        legend: {

                                            display:
                                                false

                                        },


                                        tooltip: {

                                            callbacks: {

                                                label:
                                                    function (ctx) {

                                                        return formatKg(
                                                            ctx.parsed.y
                                                        );

                                                    }

                                            }

                                        }

                                    },


                                    scales: {

                                        x: {

                                            grid: {

                                                display:
                                                    false

                                            },

                                            ticks: {

                                                color:
                                                    getThemeColors().text

                                            }

                                        },


                                        y: {

                                            beginAtZero:
                                                true,

                                            grid: {

                                                color:
                                                    getThemeColors().grid

                                            },

                                            ticks: {

                                                color:
                                                    getThemeColors().text,

                                                callback:
                                                    function (value) {

                                                        return Number(
                                                            value
                                                        ).toLocaleString(
                                                            'id-ID'
                                                        );

                                                    }

                                            }

                                        }

                                    }

                                }

                            }
                        );

                }


                /* =================================================
                   CHART JENIS SAMPAH
                ================================================= */

                if (canvasJenis) {

                    if (totalJenis <= 0) {

                        tampilKosong(
                            canvasJenis,
                            'Belum ada data setoran untuk ditampilkan.'
                        );

                    } else {

                        chartJenis =
                            new Chart(
                                canvasJenis,
                                {

                                    type:
                                        'doughnut',

                                    data: {

                                        labels:
                                            namaJenis,

                                        datasets: [

                                            {

                                                label:
                                                    'Berat Sampah (Kg)',

                                                data:
                                                    beratJenis,

                                                backgroundColor:
                                                    namaJenis.map(
                                                        function (
                                                            _,
                                                            i
                                                        ) {

                                                            return warna[
                                                                i %
                                                                warna.length
                                                            ];

                                                        }
                                                    ),

                                                borderColor:
                                                    getThemeColors().border,

                                                borderWidth:
                                                    2

                                            }

                                        ]

                                    },


                                    options: {

                                        responsive:
                                            true,

                                        maintainAspectRatio:
                                            false,

                                        cutout:
                                            '62%',


                                        plugins: {

                                            legend: {

                                                position:
                                                    'bottom',

                                                labels: {

                                                    color:
                                                        getThemeColors().text,

                                                    boxWidth:
                                                        12,

                                                    padding:
                                                        14

                                                }

                                            },


                                            tooltip: {

                                                callbacks: {

                                                    label:
                                                        function (ctx) {

                                                            return (
                                                                ' ' +
                                                                ctx.label +
                                                                ': ' +
                                                                formatKg(
                                                                    ctx.parsed
                                                                )
                                                            );

                                                        }

                                                }

                                            }

                                        }

                                    }

                                }
                            );

                    }

                }


                /* =================================================
                   UPDATE CHART SAAT THEME BERUBAH
                ================================================= */

                function updateChartsTheme() {

                    const colors =
                        getThemeColors();


                    /* ---------------------------------------------
                       CHART BULANAN
                    --------------------------------------------- */

                    if (chartBulanan) {

                        chartBulanan.options.scales.x.ticks.color =
                            colors.text;


                        chartBulanan.options.scales.y.ticks.color =
                            colors.text;


                        chartBulanan.options.scales.y.grid.color =
                            colors.grid;


                        chartBulanan.update(
                            'none'
                        );
                    }


                    /* ---------------------------------------------
                       CHART JENIS
                    --------------------------------------------- */

                    if (chartJenis) {

                        chartJenis.options.plugins.legend.labels.color =
                            colors.text;


                        chartJenis.data.datasets[0].borderColor =
                            colors.border;


                        chartJenis.update(
                            'none'
                        );
                    }


                    /* ---------------------------------------------
                       GLOBAL CHART COLOR
                    --------------------------------------------- */

                    Chart.defaults.color =
                        colors.text;
                }


                /* =================================================
                   INITIAL THEME
                ================================================= */

                updateChartsTheme();


                /* =================================================
                   OBSERVER DATA-THEME

                   topbar.js mengubah:

                   <html data-theme="dark">

                   Observer ini mendeteksi perubahan tersebut
                   kemudian memperbarui Chart.js.
                ================================================= */

                const themeObserver =
                    new MutationObserver(
                        function (
                            mutations
                        ) {

                            mutations.forEach(
                                function (
                                    mutation
                                ) {

                                    if (
                                        mutation.type ===
                                        'attributes' &&
                                        mutation.attributeName ===
                                        'data-theme'
                                    ) {

                                        updateChartsTheme();

                                    }

                                }
                            );

                        }
                    );


                themeObserver.observe(
                    document.documentElement,
                    {
                        attributes: true,
                        attributeFilter: [
                            'data-theme'
                        ]
                    }
                );

            }
        );

    </script>

@endsection
