@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">RINGKASAN</span>

            <h1 class="hero-title">
                <span class="accent">Dashboard</span>
            </h1>

            <p class="hero-sub">
                Ringkasan aktivitas Bank Sampah.
            </p>
        </div>
    </section>


    {{-- ========================= --}}
    {{-- STATISTIK UTAMA --}}
    {{-- ========================= --}}

    <div class="ws-stat-grid">

        {{-- Jumlah Warga --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                👥
            </div>

            <div>
                <div class="ws-stat-label">
                    Jumlah Warga Terdaftar
                </div>

                <div class="ws-stat-value">
                    {{ number_format($jumlahWarga, 0, ',', '.') }}
                </div>

                <div class="ws-stat-description">
                    Warga terdaftar
                </div>
            </div>
        </div>


        {{-- Total Berat Sampah --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                ♻️
            </div>

            <div>
                <div class="ws-stat-label">
                    Total Berat Sampah
                </div>

                <div class="ws-stat-value">
                    {{ number_format($totalBeratSampah, 2, ',', '.') }}
                    <span class="ws-stat-unit">Kg</span>
                </div>

                <div class="ws-stat-description">
                    Total sampah disetor
                </div>
            </div>
        </div>


        {{-- Total Saldo --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                💰
            </div>

            <div>
                <div class="ws-stat-label">
                    Total Saldo Warga
                </div>

                <div class="ws-stat-value">
                    Rp {{ number_format($totalSaldoWarga, 0, ',', '.') }}
                </div>

                <div class="ws-stat-description">
                    Saldo seluruh warga
                </div>
            </div>
        </div>


        {{-- Pencairan --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                💸
            </div>

            <div>
                <div class="ws-stat-label">
                    Total Pencairan Saldo
                </div>

                <div class="ws-stat-value">
                    Rp {{ number_format($totalPencairanSaldo, 0, ',', '.') }}
                </div>

                <div class="ws-stat-description">
                    Total pencairan
                </div>
            </div>
        </div>


        <div class="ws-stat-break"></div>


        {{-- Penjualan --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                🚚
            </div>

            <div>
                <div class="ws-stat-label">
                    Penjualan ke Pengepul
                </div>

                <div class="ws-stat-value">
                    Rp {{ number_format($totalPenjualanPengepul, 0, ',', '.') }}
                </div>

                <div class="ws-stat-description">
                    Total barang keluar
                </div>
            </div>
        </div>


        {{-- Kategori --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                🗂️
            </div>

            <div>
                <div class="ws-stat-label">
                    Kategori Sampah
                </div>

                <div class="ws-stat-value">
                    {{ number_format($jumlahKategoriSampah, 0, ',', '.') }}
                </div>

                <div class="ws-stat-description">
                    Kategori terdaftar
                </div>
            </div>
        </div>


        {{-- Transaksi --}}
        <div class="card ws-stat-card">
            <div class="ws-stat-icon">
                📋
            </div>

            <div>
                <div class="ws-stat-label">
                    Jumlah Setoran
                </div>

                <div class="ws-stat-value">
                    {{ number_format($jumlahSetoran, 0, ',', '.') }}
                </div>

                <div class="ws-stat-description">
                    Total transaksi setoran
                </div>
            </div>
        </div>

    </div>


    {{-- ========================= --}}
    {{-- GRAFIK --}}
    {{-- ========================= --}}

    <div class="dashboard-columns">

        <div class="card chart-card">

            <div class="card-head">
                <div>
                    <h2 class="card-title">Setoran Sampah per Bulan</h2>
                    <p class="card-sub">Total berat sampah tahun {{ now()->year }}</p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="setoranBulananChart"></canvas>
            </div>

        </div>


        <div class="card chart-card">

            <div class="card-head">
                <div>
                    <h2 class="card-title">Statistik Jenis Sampah</h2>
                    <p class="card-sub">Berdasarkan total berat</p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="jenisSampahChart"></canvas>
            </div>

        </div>

    </div>


    {{-- ========================= --}}
    {{-- RIWAYAT SETORAN --}}
    {{-- ========================= --}}

    <section class="card">

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
                        <th>Berat</th>
                        <th>Nilai</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($riwayatSetoran as $index => $setoran)
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
                                {{ number_format($setoran->total_berat, 2, ',', '.') }}
                                Kg
                            </td>

                            <td>
                                Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}
                            </td>

                            <td>
                                <span class="status-badge">
                                    {{ $setoran->status }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">

                                <div style="font-size: 30px; margin-bottom: 10px;">
                                    📋
                                </div>

                                <strong>Belum Ada Riwayat Setoran</strong>

                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada transaksi setoran yang tercatat.
                                </p>

                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </section>


    <style>
        /* =========================
               STAT CARDS
            ========================= */

        .ws-stat-grid {
            display: flex;
            flex-wrap: wrap;
            row-gap: 7px;
            column-gap: 14px;
            margin-bottom: 22px;
        }

        .ws-stat-break {
            flex-basis: 100%;
            width: 0;
            height: 0;
            margin: 0;
            padding: 0;
        }

        .ws-stat-card.ws-stat-card {
            flex: 1 1 180px;
            padding: 14px 16px;
            display: flex;
            flex-direction: row;
            gap: 11px;
            align-items: center;
            text-align: left;
        }

        .ws-stat-icon.ws-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin: 0;
        }

        .ws-stat-label.ws-stat-label {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 4px;
            white-space: nowrap;
            text-align: left;
        }

        .ws-stat-value.ws-stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
            text-align: left;
        }

        .ws-stat-unit.ws-stat-unit {
            font-size: 12px;
            font-weight: 500;
        }

        .ws-stat-description.ws-stat-description {
            margin-top: 2px;
            font-size: 11px;
            color: #9ca3af;
            text-align: left;
        }


        /* =========================
               CHART CARDS
            ========================= */

        .dashboard-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .chart-card {
            padding: 18px 20px;
        }

        .card-head {
            padding: 22px 24px;
            border-bottom: 1px solid var(--border, #e5e7eb);
        }

        .chart-card .card-head {
            padding: 0 0 18px;
            border-bottom: none;
        }

        .card-title {
            margin: 0 0 5px;
            font-size: 17px;
            font-weight: 700;
            color: #1f2937;
        }

        .card-sub {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .chart-container {
            height: 240px;
            position: relative;
        }


        /* =========================
               TABLE (samakan dengan Data Warga)
            ========================= */

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
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

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #eef7f1;
            color: #24a86b;
            font-size: 11px;
            font-weight: 700;
        }


        @media (max-width: 700px) {

            .dashboard-columns {
                grid-template-columns: 1fr;
            }

            .ws-stat-card.ws-stat-card {
                flex-basis: 140px;
            }

        }
    </style>

@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | Grafik Setoran per Bulan
            |--------------------------------------------------------------------------
            */

            const setoranData = @json($setoranPerBulan);

            const bulan = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember'
            ];

            const dataBulanan = Array(12).fill(0);

            setoranData.forEach(function(item) {
                dataBulanan[item.bulan - 1] = Number(item.total_berat);
            });


            const canvasBulanan =
                document.getElementById('setoranBulananChart');

            if (canvasBulanan) {

                new Chart(canvasBulanan, {

                    type: 'bar',

                    data: {
                        labels: bulan,

                        datasets: [{
                            label: 'Berat Sampah (Kg)',
                            data: dataBulanan,
                            borderWidth: 1
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Statistik Jenis Sampah
            |--------------------------------------------------------------------------
            */

            const jenisData = @json($statistikJenisSampah);

            const namaJenis = jenisData.map(function(item) {
                return item.nama_kategori;
            });

            const beratJenis = jenisData.map(function(item) {
                return Number(item.total_berat);
            });


            const canvasJenis =
                document.getElementById('jenisSampahChart');

            if (canvasJenis) {

                new Chart(canvasJenis, {

                    type: 'doughnut',

                    data: {

                        labels: namaJenis,

                        datasets: [{
                            label: 'Berat Sampah',
                            data: beratJenis,
                            borderWidth: 1
                        }]

                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }

                });

            }

        });
    </script>
@endpush
