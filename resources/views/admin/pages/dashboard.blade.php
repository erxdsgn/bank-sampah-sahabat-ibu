@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@push('styles')
    @vite('resources/css/pages/dashboard.css')
@endpush

@section('content')
    @php
        $riwayat = collect($riwayatSetoran ?? [])->sortByDesc('id_setoran')->values();
        $opsi = collect($opsiSatuan ?? []);
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
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path>
                    <circle cx="19" cy="8" r="2.3"></circle>
                    <path d="M17 21v-1a3 3 0 0 0-2-2.83"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Jumlah Warga Terdaftar</div>
                <div class="stat-value">{{ number_format($jumlahWarga ?? 0, 0, ',', '.') }}</div>
                <div class="stat-description">Warga terdaftar</div>
            </div>
        </div>

        {{-- TOTAL SAMPAH DISETOR (DROPDOWN SATUAN) --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24" aria-hidden="true">
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
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3v18"></path>
                    <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Total Saldo Warga</div>
                <div class="stat-value">Rp {{ number_format($totalSaldoWarga ?? 0, 0, ',', '.') }}</div>
                <div class="stat-description">Saldo seluruh warga</div>
            </div>
        </div>

        {{-- SALDO KAS --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--teal">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M6 6v-1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Saldo Kas</div>
                <div class="stat-value">Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }}</div>
                <div class="stat-description">Pemasukan dikurangi pengeluaran</div>
            </div>
        </div>

        {{-- PENCAIRAN --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--orange">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M3 10h18"></path>
                    <path d="M16 15h2"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Total Pencairan Saldo</div>
                <div class="stat-value">Rp {{ number_format($totalPencairanSaldo ?? 0, 0, ',', '.') }}</div>
                <div class="stat-description">Total pencairan</div>
            </div>
        </div>

        {{-- PENJUALAN --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 7h11v8H3z"></path>
                    <path d="M14 10h4l3 3v2h-7z"></path>
                    <circle cx="7" cy="18" r="2"></circle>
                    <circle cx="17" cy="18" r="2"></circle>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Penjualan ke Pengepul</div>
                <div class="stat-value">Rp {{ number_format($totalPenjualanPengepul ?? 0, 0, ',', '.') }}</div>
                <div class="stat-description">Total barang keluar</div>
            </div>
        </div>

        {{-- KATEGORI --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--purple">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
            </div>

            <div class="stat-content">
                <div class="stat-label">Kategori Sampah</div>
                <div class="stat-value">{{ number_format($jumlahKategoriSampah ?? 0, 0, ',', '.') }}</div>
                <div class="stat-description">Kategori terdaftar</div>
            </div>
        </div>

        {{-- JUMLAH SETORAN --}}
        <div class="stat-card">
            <div class="stat-icon stat-icon--indigo">
                <svg viewBox="0 0 24 24" aria-hidden="true">
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
                <div class="stat-value">{{ number_format($jumlahSetoran ?? 0, 0, ',', '.') }}</div>
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
                <p class="card-sub">Seluruh transaksi setoran yang tercatat</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="riwayatTable">
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

                <tbody id="riwayatTableBody">
                    @forelse($riwayat as $setoran)
                        <tr data-row-riwayat>
                            <td class="riwayat-no"></td>
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

        <div class="table-footer">
            <div class="table-info" id="riwayatInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageRiwayat">Baris per halaman:</label>
                    <div class="combo combo--up combo--arrow" id="combo_perPageRiwayat">
                        <input type="text" id="cari_perPageRiwayat" class="combo-input" readonly
                            autocomplete="off" aria-label="Baris per halaman riwayat setoran">
                        <div class="combo-list" id="list_perPageRiwayat"></div>
                    </div>
                    <select id="perPageRiwayat" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="pagination-buttons" id="paginationButtonsRiwayat"></div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.__DASHBOARD_DATA__ = {
            opsiSatuan: @json($opsiSatuan ?? []),
            setoranPerBulan: @json($setoranPerBulan ?? []),
            statistikJenisSampah: @json($statistikJenisSampah ?? []),
            totalJumlahSampahPerSatuan: @json($totalJumlahSampahPerSatuan ?? []),
        };
    </script>
    @vite('resources/js/pages/dashboard.js')
@endpush
