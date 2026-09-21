@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@section('content')

    @php
        // Semua berat di database disimpan dalam GRAM.
        // Di dashboard ditampilkan dalam Kg (gram / 1000).
        $gramKeKg = fn($gram) => ((float) $gram) / 1000;

        // Riwayat setoran: paling baru di paling atas (tanggal setoran, lalu waktu dibuat)
        $riwayat = collect($riwayatSetoran)->sortByDesc('id_setoran')->take(10)->values();
    @endphp

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

    <div class="stat-grid">

        {{-- Jumlah Warga --}}
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
                <div class="stat-label">Jumlah Warga Terdaftar</div>
                <div class="stat-value">{{ number_format($jumlahWarga, 0, ',', '.') }}</div>
                <div class="stat-description">Warga terdaftar</div>
            </div>
        </div>

        {{-- Total Berat Sampah (gram -> Kg) --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
            </div>
            <div>
                <div class="stat-label">Total Berat Sampah</div>
                <div class="stat-value">
                    {{ number_format($gramKeKg($totalBeratSampah), 2, ',', '.') }}
                    <span class="stat-unit">Kg</span>
                </div>
                <div class="stat-description">Total sampah disetor</div>
            </div>
        </div>

        {{-- Total Saldo Warga --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                </svg>
            </div>
            <div>
                <div class="stat-label">Total Saldo Warga</div>
                <div class="stat-value">Rp {{ number_format($totalSaldoWarga, 0, ',', '.') }}</div>
                <div class="stat-description">Saldo seluruh warga</div>
            </div>
        </div>

        {{-- Saldo Kas --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--teal">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M6 6v-1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path>
                </svg>
            </div>
            <div>
                <div class="stat-label">Saldo Kas</div>
                <div class="stat-value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                <div class="stat-description">Pemasukan dikurangi pengeluaran</div>
            </div>
        </div>

        {{-- Pencairan --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--orange">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M3 10h18"></path>
                    <path d="M16 15h2"></path>
                </svg>
            </div>
            <div>
                <div class="stat-label">Total Pencairan Saldo</div>
                <div class="stat-value">Rp {{ number_format($totalPencairanSaldo, 0, ',', '.') }}</div>
                <div class="stat-description">Total pencairan</div>
            </div>
        </div>

        {{-- Penjualan --}}
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
                <div class="stat-label">Penjualan ke Pengepul</div>
                <div class="stat-value">Rp {{ number_format($totalPenjualanPengepul, 0, ',', '.') }}</div>
                <div class="stat-description">Total barang keluar</div>
            </div>
        </div>

        {{-- Kategori --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--purple">
                <svg viewBox="0 0 24 24">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
            </div>
            <div>
                <div class="stat-label">Kategori Sampah</div>
                <div class="stat-value">{{ number_format($jumlahKategoriSampah, 0, ',', '.') }}</div>
                <div class="stat-description">Kategori terdaftar</div>
            </div>
        </div>

        {{-- Transaksi --}}
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
                <div class="stat-label">Jumlah Setoran</div>
                <div class="stat-value">{{ number_format($jumlahSetoran, 0, ',', '.') }}</div>
                <div class="stat-description">Total transaksi setoran</div>
            </div>
        </div>

    </div>


    {{-- ========================= --}}
    {{-- GRAFIK --}}
    {{-- ========================= --}}

    <div class="dashboard-columns">

        {{-- GRAFIK SETORAN = 3 KOTAK --}}
        <div class="card chart-card chart-card--large">
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


        {{-- STATISTIK JENIS SAMPAH = 1 KOTAK --}}
        <div class="card chart-card chart-card--small">
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
                    @forelse($riwayat as $index => $setoran)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($setoran->tanggal_setoran)->format('d/m/Y') }}</td>
                            <td><strong>{{ $setoran->nama }}</strong></td>
                            <td>{{ number_format($gramKeKg($setoran->total_berat), 2, ',', '.') }} Kg</td>
                            <td>Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}</td>
                            <td><span class="badge badge--success">{{ $setoran->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
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

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        @media (max-width: 1100px) {
            .stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-icon {
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

        .stat-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

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

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 3px;
            white-space: nowrap;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
        }

        .stat-unit {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
        }

        .stat-description {
            margin-top: 2px;
            font-size: 11px;
            color: #9ca3af;
        }


        /* =========================
                           CHART CARDS
                        ========================= */

        .dashboard-columns {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        /* Grafik Setoran = 3 dari 4 kolom */
        .chart-card--large {
            grid-column: span 3;
        }

        /* Statistik = 1 dari 4 kolom */
        .chart-card--small {
            grid-column: span 1;
        }

        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .chart-card {
            padding: 18px 20px;
            margin-bottom: 0;
            min-width: 0;
        }

        .card-head {
            padding: 20px 22px;
            border-bottom: 1px solid var(--border, #e5e7eb);
        }

        .chart-card .card-head {
            padding: 0 0 16px;
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
            height: 260px;
            position: relative;
        }

        .chart-empty {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            border: 1px dashed #dfe3e8;
            border-radius: 12px;
            background: #f8fafc;
        }


        /* =========================
                           TABLE
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


        @media (max-width: 900px) {
            .dashboard-columns {
                grid-template-columns: 1fr;
            }

            .chart-card--large,
            .chart-card--small {
                grid-column: span 1;
            }
        }
    </style>


    {{-- Chart.js dimuat langsung di sini supaya grafik tidak bergantung pada layout --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ---------- Helper ---------- */

            // Aman untuk array maupun object (mis. hasil keyBy dari Laravel)
            function keArray(v) {
                if (Array.isArray(v)) return v;
                return Object.values(v || {});
            }

            function formatKg(n) {
                return (Number(n) || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                }) + ' Kg';
            }

            function tampilKosong(canvas, teks) {
                canvas.parentElement.innerHTML = '<div class="chart-empty">' + teks + '</div>';
            }

            const canvasBulanan = document.getElementById('setoranBulananChart');
            const canvasJenis = document.getElementById('jenisSampahChart');

            if (typeof Chart === 'undefined') {
                const pesan = 'Grafik tidak dapat dimuat. Periksa koneksi internet.';
                if (canvasBulanan) tampilKosong(canvasBulanan, pesan);
                if (canvasJenis) tampilKosong(canvasJenis, pesan);
                return;
            }

            Chart.defaults.font.family = 'inherit';
            Chart.defaults.color = '#6b7280';


            /*
            |--------------------------------------------------------------------------
            | Grafik Setoran per Bulan (data gram -> Kg)
            |--------------------------------------------------------------------------
            */

            const setoranData = keArray(@json($setoranPerBulan));

            const bulan = [
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ];

            const dataBulanan = Array(12).fill(0);

            setoranData.forEach(function(item) {
                const idx = Number(item.bulan) - 1;
                if (idx >= 0 && idx < 12) {
                    dataBulanan[idx] = (Number(item.total_berat) || 0) / 1000;
                }
            });

            if (canvasBulanan) {
                new Chart(canvasBulanan, {
                    type: 'bar',
                    data: {
                        labels: bulan,
                        datasets: [{
                            label: 'Berat Sampah (Kg)',
                            data: dataBulanan,
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
                                        return formatKg(ctx.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(v) {
                                        return Number(v).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }


            /*
            |--------------------------------------------------------------------------
            | Statistik Jenis Sampah (data gram -> Kg)
            |--------------------------------------------------------------------------
            */

            const jenisData = keArray(@json($statistikJenisSampah));

            const namaJenis = jenisData.map(function(item) {
                return item.nama_kategori || item.nama_lengkap || item.nama || 'Tanpa nama';
            });

            const beratJenis = jenisData.map(function(item) {
                return (Number(item.total_berat) || 0) / 1000;
            });

            const totalJenis = beratJenis.reduce(function(a, b) {
                return a + b;
            }, 0);

            const warna = [
                '#16a34a', '#0284c7', '#ea580c', '#7c3aed', '#0d9488',
                '#4338ca', '#d97706', '#dc2626', '#64748b', '#84cc16'
            ];

            if (canvasJenis) {
                if (totalJenis <= 0) {
                    tampilKosong(canvasJenis, 'Belum ada data setoran untuk ditampilkan.');
                } else {
                    new Chart(canvasJenis, {
                        type: 'doughnut',
                        data: {
                            labels: namaJenis,
                            datasets: [{
                                label: 'Berat Sampah (Kg)',
                                data: beratJenis,
                                backgroundColor: namaJenis.map(function(_, i) {
                                    return warna[i % warna.length];
                                }),
                                borderColor: '#ffffff',
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
                                        boxWidth: 12,
                                        padding: 14
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ' ' + ctx.label + ': ' + formatKg(ctx.parsed);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

        });
    </script>

@endsection
