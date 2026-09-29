@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@section('content')

    @php
        // Riwayat setoran: terbaru di atas
        $riwayat = collect($riwayatSetoran)->sortByDesc('id_setoran')->take(10)->values();

        $opsi = collect($opsiSatuan);
    @endphp


    {{-- HERO --}}
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">RINGKASAN</span>

            <h1 class="hero-title">
                <span class="accent">Dashboard</span>
            </h1>

            <p class="hero-sub">Ringkasan aktivitas Bank Sampah.</p>
        </div>
    </section>


    {{-- STATISTIK UTAMA --}}
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

            <div class="stat-content">
                <div class="stat-label">Jumlah Warga Terdaftar</div>
                <div class="stat-value">{{ number_format($jumlahWarga, 0, ',', '.') }}</div>
                <div class="stat-description">Warga terdaftar</div>
            </div>
        </div>


        {{-- TOTAL SAMPAH DISETOR (DROPDOWN SATUAN) --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-head-row">
                    <div class="stat-label">Total Sampah Disetor</div>

                    @if ($opsi->count() > 0)
                        <div class="combo combo--arrow combo--card" id="combo_satuanTotalSampah">
                            <input type="text" id="cari_satuanTotalSampah" class="combo-input" readonly
                                autocomplete="off" aria-label="Satuan total sampah">
                            <div class="combo-list" id="list_satuanTotalSampah"></div>
                        </div>
                        <select id="satuanTotalSampah" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            @foreach ($opsi as $o)
                                <option value="{{ $o['key'] }}">{{ $o['label'] }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="stat-value" id="nilaiTotalSampah">0</div>
                <div class="stat-description">Berdasarkan satuan dipilih</div>
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

            <div class="stat-content">
                <div class="stat-label">Total Saldo Warga</div>
                <div class="stat-value">Rp {{ number_format($totalSaldoWarga, 0, ',', '.') }}</div>
                <div class="stat-description">Saldo seluruh warga</div>
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

            <div class="stat-content">
                <div class="stat-label">Saldo Kas</div>
                <div class="stat-value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                <div class="stat-description">Pemasukan dikurangi pengeluaran</div>
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

            <div class="stat-content">
                <div class="stat-label">Total Pencairan Saldo</div>
                <div class="stat-value">Rp {{ number_format($totalPencairanSaldo, 0, ',', '.') }}</div>
                <div class="stat-description">Total pencairan</div>
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

            <div class="stat-content">
                <div class="stat-label">Penjualan ke Pengepul</div>
                <div class="stat-value">Rp {{ number_format($totalPenjualanPengepul, 0, ',', '.') }}</div>
                <div class="stat-description">Total barang keluar</div>
            </div>
        </div>


        {{-- KATEGORI --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--purple">
                <svg viewBox="0 0 24 24">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Kategori Sampah</div>
                <div class="stat-value">{{ number_format($jumlahKategoriSampah, 0, ',', '.') }}</div>
                <div class="stat-description">Kategori terdaftar</div>
            </div>
        </div>


        {{-- JUMLAH SETORAN --}}
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

            <div class="stat-content">
                <div class="stat-label">Jumlah Setoran</div>
                <div class="stat-value">{{ number_format($jumlahSetoran, 0, ',', '.') }}</div>
                <div class="stat-description">Total transaksi setoran</div>
            </div>
        </div>

    </div>


    {{-- GRAFIK --}}
    <div class="dashboard-columns">

        {{-- GRAFIK SETORAN BULANAN --}}
        <div class="card chart-card chart-card--large">

            <div class="card-head card-head--row">
                <div>
                    <h2 class="card-title">Setoran Sampah per Bulan</h2>
                    <p class="card-sub">Total jumlah sampah tahun {{ now()->year }}</p>
                </div>

                @if ($opsi->count() > 1)
                    <div class="combo combo--arrow" id="combo_satuanBulanan">
                        <input type="text" id="cari_satuanBulanan" class="combo-input" readonly autocomplete="off"
                            aria-label="Satuan grafik bulanan">
                        <div class="combo-list" id="list_satuanBulanan"></div>
                    </div>
                    <select id="satuanBulanan" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        @foreach ($opsi as $o)
                            <option value="{{ $o['key'] }}">{{ $o['label'] }}</option>
                        @endforeach
                    </select>
                @elseif ($opsi->count() === 1)
                    <span class="unit-badge">{{ $opsi->first()['label'] }}</span>
                @endif
            </div>

            <div class="chart-container">
                <canvas id="setoranBulananChart"></canvas>
                <div class="chart-empty" id="bulananKosong" style="display:none;"></div>
            </div>

        </div>


        {{-- STATISTIK JENIS SAMPAH --}}
        <div class="card chart-card chart-card--small">

            <div class="card-head card-head--row">
                <div>
                    <h2 class="card-title">Statistik Jenis Sampah</h2>
                    <p class="card-sub">Berdasarkan total jumlah</p>
                </div>

                @if ($opsi->count() > 1)
                    <div class="combo combo--arrow" id="combo_satuanJenis">
                        <input type="text" id="cari_satuanJenis" class="combo-input" readonly autocomplete="off"
                            aria-label="Satuan statistik jenis sampah">
                        <div class="combo-list" id="list_satuanJenis"></div>
                    </div>
                    <select id="satuanJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        @foreach ($opsi as $o)
                            <option value="{{ $o['key'] }}">{{ $o['label'] }}</option>
                        @endforeach
                    </select>
                @elseif ($opsi->count() === 1)
                    <span class="unit-badge">{{ $opsi->first()['label'] }}</span>
                @endif
            </div>

            <div class="chart-container">
                <canvas id="jenisSampahChart"></canvas>
                <div class="chart-empty" id="jenisKosong" style="display:none;"></div>
            </div>

        </div>

    </div>


    {{-- RIWAYAT SETORAN --}}
    <section class="card history-card">

        <div class="card-head">
            <div>
                <h2 class="card-title">Riwayat Setoran</h2>
                <p class="card-sub">10 transaksi setoran terbaru</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Warga</th>
                        <th>Jumlah</th>
                        <th>Nilai</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($riwayat as $index => $setoran)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>{{ \Carbon\Carbon::parse($setoran->tanggal_setoran)->format('d/m/Y') }}</td>

                            <td><strong>{{ $setoran->nama }}</strong></td>

                            <td>{{ $setoran->jumlah_text }}</td>

                            <td>Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}</td>

                            <td>
                                <span class="badge badge--success">{{ $setoran->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table">
                                <div class="empty-icon">📋</div>
                                <strong>Belum Ada Riwayat Setoran</strong>
                                <p class="empty-description">Belum ada transaksi setoran yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>


    {{-- CSS DASHBOARD --}}
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --dashboard-page: #f5f7fb;
            --dashboard-card: #ffffff;
            --dashboard-card-secondary: #f8fafc;
            --dashboard-text: #1f2937;
            --dashboard-text-secondary: #6b7280;
            --dashboard-text-muted: #9ca3af;
            --dashboard-border: #e5e7eb;
            --dashboard-table-head: #f8fafc;
            --dashboard-table-row: #ffffff;
            --dashboard-table-hover: #fafafa;
            --dashboard-chart-grid: #e5e7eb;
            --dashboard-chart-text: #6b7280;
            --dashboard-empty: #f8fafc;
            --dashboard-input: #ffffff;
            --dashboard-shadow: 0 8px 25px rgba(15, 23, 42, .06);
        }

        [data-theme="dark"] {
            --dashboard-page: #0b1220;
            --dashboard-card: #151d2f;
            --dashboard-card-secondary: #1b2438;
            --dashboard-text: #f1f5f9;
            --dashboard-text-secondary: #aab6c8;
            --dashboard-text-muted: #748198;
            --dashboard-border: #29364d;
            --dashboard-table-head: #111a2c;
            --dashboard-table-row: #151d2f;
            --dashboard-table-hover: #202b40;
            --dashboard-chart-grid: #2c3950;
            --dashboard-chart-text: #aab6c8;
            --dashboard-empty: #111a2c;
            --dashboard-input: #1b2438;
            --dashboard-shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }

        body {
            background: var(--dashboard-page);
            color: var(--dashboard-text);
            transition: background-color .25s ease, color .25s ease;
        }

        /* ===== STAT GRID ===== */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .stat-card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: var(--dashboard-text);
            box-shadow: var(--dashboard-shadow);
            min-width: 0;
            transition: background-color .25s ease, border-color .25s ease, color .25s ease, box-shadow .25s ease;
        }

        .stat-content {
            flex: 1;
            min-width: 0;
        }

        .stat-head-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 2px;
            width: 100%;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Varian Warna Ikon & Background Terang/Gelap */
        .stat-icon--indigo {
            background: #eef2ff;
            color: #4338ca;
        }

        .stat-icon--green {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-icon--blue {
            background: #e0f2fe;
            color: #0284c7;
        }

        .stat-icon--orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .stat-icon--purple {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .stat-icon--teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        [data-theme="dark"] .stat-icon--indigo {
            background: rgba(67, 56, 202, 0.22);
            color: #818cf8;
        }

        [data-theme="dark"] .stat-icon--green {
            background: rgba(22, 163, 74, 0.22);
            color: #4ade80;
        }

        [data-theme="dark"] .stat-icon--blue {
            background: rgba(2, 132, 199, 0.22);
            color: #38bdf8;
        }

        [data-theme="dark"] .stat-icon--orange {
            background: rgba(234, 88, 12, 0.22);
            color: #fb923c;
        }

        [data-theme="dark"] .stat-icon--purple {
            background: rgba(124, 58, 237, 0.22);
            color: #a78bfa;
        }

        [data-theme="dark"] .stat-icon--teal {
            background: rgba(13, 148, 136, 0.22);
            color: #2dd4bf;
        }

        .stat-label {
            font-size: 11px;
            color: var(--dashboard-text-secondary);
            margin: 0;
            line-height: 1.3;
            word-break: break-word;
            flex: 1;
            min-width: 0;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--dashboard-text);
            line-height: 1.2;
            word-break: break-word;
        }

        .stat-description {
            margin-top: 2px;
            font-size: 11px;
            color: var(--dashboard-text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== DASHBOARD COLUMNS ===== */
        .dashboard-columns {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .chart-card--large {
            grid-column: span 3;
        }

        .chart-card--small {
            grid-column: span 1;
        }

        /* ===== CARD ===== */
        .card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 16px;
            color: var(--dashboard-text);
            box-shadow: var(--dashboard-shadow);
            transition: background-color .25s ease, border-color .25s ease, color .25s ease, box-shadow .25s ease;
        }

        .chart-card {
            padding: 18px 20px;
            margin-bottom: 0;
            min-width: 0;
        }

        .card-head {
            padding: 20px 22px;
            border-bottom: 1px solid var(--dashboard-border);
        }

        .chart-card .card-head {
            padding: 0 0 16px;
            border-bottom: none;
        }

        .card-head--row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .card-title {
            margin: 0 0 5px;
            font-size: 17px;
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .card-sub {
            margin: 0;
            font-size: 13px;
            color: var(--dashboard-text-secondary);
        }

        .unit-badge {
            display: inline-flex;
            align-items: center;
            height: 26px;
            padding: 0 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #dcfce7;
            color: #15803d;
        }

        [data-theme="dark"] .unit-badge {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
        }

        /* ===== COMBOBOX PILIHAN SATUAN ===== */
        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-input {
            width: 100%;
            height: 32px;
            box-sizing: border-box;
            padding: 0 10px;
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            background: var(--dashboard-input);
            color: var(--dashboard-text);
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .combo-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
        }

        .combo--arrow .combo-input {
            cursor: pointer;
            padding-right: 20px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 3px center;
            background-size: 10px;
        }

        .combo--card {
            width: 58px;
            min-width: unset;
            flex: 0 0 auto;
        }

        .combo--card .combo-input {
            height: 24px;
            padding: 0 16px 0 5px;
            font-size: 10.5px;
            border-radius: 6px;
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            left: auto;
            width: 75px;
            max-height: 200px;
            overflow-y: auto;
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
            z-index: 30;
            scrollbar-width: thin;
            scrollbar-color: var(--dashboard-border) transparent;
        }

        .combo-list.show {
            display: block;
        }

        .combo-list::-webkit-scrollbar {
            width: 6px;
        }

        .combo-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .combo-list::-webkit-scrollbar-thumb {
            background: var(--dashboard-border);
            border-radius: 4px;
        }

        .combo-list::-webkit-scrollbar-thumb:hover {
            background: var(--dashboard-text-muted);
        }

        .combo-item {
            padding: 6px 10px;
            font-size: 11px;
            color: var(--dashboard-text);
            cursor: pointer;
            transition: background .1s ease, color .1s ease;
            white-space: nowrap;
        }

        .combo-item.is-selected {
            font-weight: 700;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--dashboard-table-hover);
            color: #16a34a;
        }

        /* ===== CHART ===== */
        .chart-container {
            height: 260px;
            position: relative;
        }

        .chart-empty {
            height: 100%;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 13px;
            color: var(--dashboard-text-secondary);
            border: 1px dashed var(--dashboard-border);
            border-radius: 12px;
            background: var(--dashboard-empty);
            padding: 0 12px;
            transition: background-color .25s ease, border-color .25s ease, color .25s ease;
        }

        /* ===== TABLE ===== */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--dashboard-table-row);
            color: var(--dashboard-text);
        }

        .data-table th {
            background: var(--dashboard-table-head);
            color: var(--dashboard-text-secondary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid var(--dashboard-border);
            border-bottom: 1px solid var(--dashboard-border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--dashboard-border);
            font-size: 13px;
            color: var(--dashboard-text);
            vertical-align: middle;
            background: var(--dashboard-table-row);
            transition: background-color .25s ease, color .25s ease, border-color .25s ease;
        }

        .data-table td strong {
            color: var(--dashboard-text);
        }

        .data-table tbody tr:hover td {
            background: var(--dashboard-table-hover);
        }

        .empty-table {
            text-align: center;
            padding: 40px !important;
            color: var(--dashboard-text);
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .empty-description {
            margin: 5px 0 0;
            color: var(--dashboard-text-secondary);
        }

        /* ===== BADGE ===== */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge--success {
            background: #dcfce7;
            color: #15803d;
        }

        [data-theme="dark"] .badge--success {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .dashboard-columns {
                grid-template-columns: 1fr;
            }

            .chart-card--large,
            .chart-card--small {
                grid-column: span 1;
            }
        }

        @media (max-width: 560px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>


    {{-- CHART.JS & SCRIPT INTERAKTIF --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ================= COMBOBOX (Custom Select) ================= */
            function initComboSelect(selectId) {
                const select = document.getElementById(selectId);
                const input = document.getElementById('cari_' + selectId);
                const list = document.getElementById('list_' + selectId);
                if (!select || !input || !list) return;

                input.readOnly = true;
                let aktif = -1;

                const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
                const esc = v => String(v).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));

                function sync() {
                    const o = select.options[select.selectedIndex];
                    input.value = o ? teks(o) : '';
                }

                function buka() {
                    list.innerHTML = Array.from(select.options).map(o =>
                        `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
                    ).join('');
                    aktif = -1;
                    list.classList.add('show');
                    const terpilih = list.querySelector('.is-selected');
                    if (terpilih) terpilih.scrollIntoView({
                        block: 'nearest'
                    });
                }

                function tutup() {
                    list.classList.remove('show');
                }

                function pilih(value) {
                    const berubah = select.value !== String(value);
                    select.value = value;
                    sync();
                    tutup();
                    if (berubah) select.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                function sorot(arah) {
                    const items = list.querySelectorAll('.combo-item');
                    if (!items.length) return;
                    aktif = (aktif + arah + items.length) % items.length;
                    items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                    items[aktif].scrollIntoView({
                        block: 'nearest'
                    });
                }

                input.addEventListener('focus', buka);
                input.addEventListener('click', () => {
                    if (!list.classList.contains('show')) buka();
                });
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

                sync();
            }

            initComboSelect('satuanTotalSampah');
            initComboSelect('satuanBulanan');
            initComboSelect('satuanJenis');

            /* ================= DATA DARI CONTROLLER ================= */
            const opsiSatuan = @json($opsiSatuan);
            const dataBulananPerSatuan = @json($setoranPerBulan);
            const dataJenisPerSatuan = @json($statistikJenisSampah);
            const dataTotalSampahPerSatuan = @json($totalJumlahSampahPerSatuan ?? []);

            const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            const WARNA = [
                '#16a34a', '#0284c7', '#ea580c', '#7c3aed', '#0d9488',
                '#4338ca', '#d97706', '#dc2626', '#64748b', '#84cc16'
            ];

            /* ================= HELPER ================= */
            function keArray(value) {
                if (Array.isArray(value)) return value;
                return Object.values(value || {});
            }

            function labelSatuan(key) {
                const o = opsiSatuan.find(function(x) {
                    return x.key === key;
                });
                return o ? o.label : '';
            }

            function formatAngka(value, key) {
                const angka = (Number(value) || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
                const label = labelSatuan(key);
                return label ? angka + ' ' + label : angka;
            }

            function satuanAktif(selectId) {
                const el = document.getElementById(selectId);
                if (el) return el.value;
                return opsiSatuan.length ? opsiSatuan[0].key : null;
            }

            function tampilPesan(canvas, kotak, teks) {
                if (canvas) canvas.style.display = 'none';
                if (kotak) {
                    kotak.textContent = teks;
                    kotak.style.display = 'flex';
                }
            }

            function tampilCanvas(canvas, kotak) {
                if (canvas) canvas.style.display = '';
                if (kotak) kotak.style.display = 'none';
            }

            /* ================= UPDATE KARTU TOTAL SAMPAH ================= */
            const selectTotalSampah = document.getElementById('satuanTotalSampah');
            const elNilaiTotalSampah = document.getElementById('nilaiTotalSampah');

            function updateTotalSampahCard() {
                if (!selectTotalSampah || !elNilaiTotalSampah) return;
                const key = selectTotalSampah.value;
                const nilaiRaw = dataTotalSampahPerSatuan[key] || 0;
                elNilaiTotalSampah.textContent = (Number(nilaiRaw) || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
            }

            if (selectTotalSampah) {
                selectTotalSampah.addEventListener('change', updateTotalSampahCard);
                updateTotalSampahCard();
            }

            /* ================= INISIALISASI GRAFIK ================= */
            const canvasBulanan = document.getElementById('setoranBulananChart');
            const canvasJenis = document.getElementById('jenisSampahChart');
            const kosongBulanan = document.getElementById('bulananKosong');
            const kosongJenis = document.getElementById('jenisKosong');

            if (typeof Chart === 'undefined') {
                const pesan = 'Grafik tidak dapat dimuat. Periksa koneksi internet.';
                tampilPesan(canvasBulanan, kosongBulanan, pesan);
                tampilPesan(canvasJenis, kosongJenis, pesan);
                return;
            }

            function getThemeColors() {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                return {
                    text: isDark ? '#aab6c8' : '#6b7280',
                    grid: isDark ? '#2c3950' : '#e5e7eb',
                    border: isDark ? '#151d2f' : '#ffffff'
                };
            }

            function hitungBulanan(key) {
                const hasil = Array(12).fill(0);
                keArray(dataBulananPerSatuan[key]).forEach(function(item) {
                    const idx = Number(item.bulan) - 1;
                    if (idx >= 0 && idx < 12) {
                        hasil[idx] = Number(item.total) || 0;
                    }
                });
                return hasil;
            }

            function hitungJenis(key) {
                const items = keArray(dataJenisPerSatuan[key]);
                return {
                    nama: items.map(function(i) {
                        return i.nama || 'Tanpa nama';
                    }),
                    nilai: items.map(function(i) {
                        return Number(i.total) || 0;
                    })
                };
            }

            let chartBulanan = null;
            let chartJenis = null;

            if (canvasBulanan) {
                const key = satuanAktif('satuanBulanan');
                const warnaTema = getThemeColors();

                chartBulanan = new Chart(canvasBulanan, {
                    type: 'bar',
                    data: {
                        labels: BULAN,
                        datasets: [{
                            label: 'Jumlah (' + labelSatuan(key) + ')',
                            data: key ? hitungBulanan(key) : Array(12).fill(0),
                            backgroundColor: '#16a34a',
                            borderRadius: 6,
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return formatAngka(ctx.parsed.y, satuanAktif('satuanBulanan'));
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: warnaTema.text
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: warnaTema.grid
                                },
                                ticks: {
                                    color: warnaTema.text,
                                    callback: function(value) {
                                        return Number(value).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });

                if (!key) {
                    tampilPesan(canvasBulanan, kosongBulanan, 'Belum ada data setoran untuk ditampilkan.');
                }
            }

            function updateBulanan() {
                if (!chartBulanan) return;
                const key = satuanAktif('satuanBulanan');
                if (!key) return;

                const data = hitungBulanan(key);
                const adaData = data.some(function(v) {
                    return v > 0;
                });

                chartBulanan.data.datasets[0].data = data;
                chartBulanan.data.datasets[0].label = 'Jumlah (' + labelSatuan(key) + ')';
                chartBulanan.update();

                if (adaData) {
                    tampilCanvas(canvasBulanan, kosongBulanan);
                } else {
                    tampilPesan(canvasBulanan, kosongBulanan, 'Belum ada setoran ' + labelSatuan(key) +
                        ' pada tahun ini.');
                }
            }

            function warnaJenis(nama) {
                return nama.map(function(_, i) {
                    return WARNA[i % WARNA.length];
                });
            }

            if (canvasJenis) {
                const key = satuanAktif('satuanJenis');
                const awal = key ? hitungJenis(key) : {
                    nama: [],
                    nilai: []
                };
                const warnaTema = getThemeColors();

                chartJenis = new Chart(canvasJenis, {
                    type: 'doughnut',
                    data: {
                        labels: awal.nama,
                        datasets: [{
                            label: 'Jumlah',
                            data: awal.nilai,
                            backgroundColor: warnaJenis(awal.nama),
                            borderColor: warnaTema.border,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: warnaTema.text,
                                    boxWidth: 12,
                                    padding: 14
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ' ' + ctx.label + ': ' + formatAngka(ctx.parsed,
                                            satuanAktif('satuanJenis'));
                                    }
                                }
                            }
                        }
                    }
                });

                updateJenis();
            }

            function updateJenis() {
                if (!chartJenis) return;
                const key = satuanAktif('satuanJenis');
                const hasil = key ? hitungJenis(key) : {
                    nama: [],
                    nilai: []
                };
                const total = hasil.nilai.reduce(function(a, b) {
                    return a + b;
                }, 0);

                chartJenis.data.labels = hasil.nama;
                chartJenis.data.datasets[0].data = hasil.nilai;
                chartJenis.data.datasets[0].backgroundColor = warnaJenis(hasil.nama);
                chartJenis.update();

                if (total > 0) {
                    tampilCanvas(canvasJenis, kosongJenis);
                } else {
                    tampilPesan(canvasJenis, kosongJenis, 'Belum ada data setoran untuk ditampilkan.');
                }
            }

            const selectBulanan = document.getElementById('satuanBulanan');
            const selectJenis = document.getElementById('satuanJenis');

            if (selectBulanan) selectBulanan.addEventListener('change', updateBulanan);
            if (selectJenis) selectJenis.addEventListener('change', updateJenis);

            function updateChartsTheme() {
                const colors = getThemeColors();
                if (chartBulanan) {
                    chartBulanan.options.scales.x.ticks.color = colors.text;
                    chartBulanan.options.scales.y.ticks.color = colors.text;
                    chartBulanan.options.scales.y.grid.color = colors.grid;
                    chartBulanan.update('none');
                }
                if (chartJenis) {
                    chartJenis.options.plugins.legend.labels.color = colors.text;
                    chartJenis.data.datasets[0].borderColor = colors.border;
                    chartJenis.update('none');
                }
                Chart.defaults.color = colors.text;
            }

            updateChartsTheme();

            const themeObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                        updateChartsTheme();
                    }
                });
            });

            themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });

        });
    </script>

@endsection
