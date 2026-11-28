@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@push('styles')
    @vite('resources/css/pages/dashboard.css')
@endpush

@section('content')
    @php
        $riwayat = collect($riwayatSetoran ?? [])
            ->sortByDesc('id_setoran')
            ->values();
        $opsi = collect($opsiSatuan ?? []);

        $waktuLokal = now('Asia/Jakarta');
        $jam = $waktuLokal->hour;
        $sapaan = match (true) {
            $jam >= 3 && $jam < 11 => 'Selamat Pagi',
            $jam >= 11 && $jam < 15 => 'Selamat Siang',
            $jam >= 15 && $jam < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };

        $user = auth()->user();
        $namaAdmin = $user?->admin?->nama ?? ($user?->nama ?? ($user?->nama_lengkap ?? ($user?->name ?? 'Admin')));

        $namaBulan = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];
        $namaBulanFull = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $bAwal = (int) ($bulanAwal ?? 1);
        $bAkhir = (int) ($bulanAkhir ?? 6);
        $thnDipilih = (int) ($tahunDipilih ?? $waktuLokal->year);
        $blnStat = (int) ($bulanStatistik ?? 0);
        $listTahun = $daftarTahun ?? [$thnDipilih];

        $defaultSatuan = $opsi->first()['key'] ?? 'kg';
        $satuanDipilih = request('satuan');
        if (!$satuanDipilih || !$opsi->contains('key', $satuanDipilih)) {
            $satuanDipilih = $defaultSatuan;
        }

        $selisihBulan = max(1, $bAkhir - $bAwal + 1);
    @endphp

    <div class="dashboard-page">
        {{-- ============ 1. HERO BANNER ============ --}}
        <section class="welcome-banner" aria-label="Banner sapaan admin">
            <span class="welcome-banner-deco welcome-banner-deco--1" aria-hidden="true"></span>
            <span class="welcome-banner-deco welcome-banner-deco--2" aria-hidden="true"></span>

            <div class="welcome-banner-inner">
                <div class="welcome-banner-content">
                    <div class="welcome-badge">
                        <span class="welcome-badge-dot" aria-hidden="true"></span>
                        <span>Sistem Manajemen Bank Sampah</span>
                    </div>

                    <h1 class="welcome-title">{{ $sapaan }}, {{ $namaAdmin }}!</h1>
                    <p class="welcome-sub">
                        Pantau akumulasi sampah masuk, kelola verifikasi setoran warga, dan cek arus kas bank sampah secara
                        real-time.
                    </p>
                </div>

                <div class="welcome-actions">
                    <a href="{{ route('admin.verifikasi-setoran.index') }}" class="welcome-btn welcome-btn--primary">
                        <span>Verifikasi Setoran</span>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="{{ route('admin.penjadwalan-setoran.index') }}" class="welcome-btn welcome-btn--ghost">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <path d="M16 2v4"></path>
                            <path d="M8 2v4"></path>
                            <path d="M3 10h18"></path>
                        </svg>
                        <span>Jadwal Setoran</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ============ 2. TOOLBAR FILTER ============ --}}
        <section class="dashboard-toolbar" aria-label="Filter dashboard">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="toolbar-form" id="dashboardFilterForm">
                {{-- Kiri: Periode --}}
                <div class="toolbar-group">
                    <div class="toolbar-label">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <path d="M16 2v4"></path>
                            <path d="M8 2v4"></path>
                            <path d="M3 10h18"></path>
                        </svg>
                        <span>Periode:</span>
                    </div>

                    <div class="toolbar-pills">
                        <div class="pill-group">
                            <div class="combo combo--arrow combo--toolbar" id="combo_filterBulanAwal">
                                <input type="text" id="cari_filterBulanAwal" class="combo-input" readonly
                                    autocomplete="off" aria-label="Bulan awal">
                                <div class="combo-list" id="list_filterBulanAwal"></div>
                            </div>
                            <select name="bulan_awal" id="filterBulanAwal" class="combo-hidden" tabindex="-1"
                                aria-hidden="true">
                                @foreach ($namaBulan as $num => $labelBln)
                                    <option value="{{ $num }}" {{ $bAwal === $num ? 'selected' : '' }}>
                                        {{ $labelBln }}</option>
                                @endforeach
                            </select>

                            <span class="pill-sep">–</span>

                            <div class="combo combo--arrow combo--toolbar" id="combo_filterBulanAkhir">
                                <input type="text" id="cari_filterBulanAkhir" class="combo-input" readonly
                                    autocomplete="off" aria-label="Bulan akhir">
                                <div class="combo-list" id="list_filterBulanAkhir"></div>
                            </div>
                            <select name="bulan_akhir" id="filterBulanAkhir" class="combo-hidden" tabindex="-1"
                                aria-hidden="true">
                                @foreach ($namaBulan as $num => $labelBln)
                                    <option value="{{ $num }}" {{ $bAkhir === $num ? 'selected' : '' }}>
                                        {{ $labelBln }}</option>
                                @endforeach
                            </select>
                        </div>

                        <span class="pill-badge">{{ $selisihBulan }} Bulan</span>

                        <div class="combo combo--arrow combo--toolbar combo--year" id="combo_filterTahun">
                            <input type="text" id="cari_filterTahun" class="combo-input" readonly autocomplete="off"
                                aria-label="Tahun">
                            <div class="combo-list" id="list_filterTahun"></div>
                        </div>
                        <select name="tahun" id="filterTahun" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            @foreach ($listTahun as $thn)
                                <option value="{{ $thn }}" {{ $thnDipilih === (int) $thn ? 'selected' : '' }}>
                                    {{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Kanan: Satuan & Terapkan --}}
                <div class="toolbar-group toolbar-group--right">
                    <div class="toolbar-label">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3v18"></path>
                            <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                        </svg>
                        <span>Satuan:</span>
                    </div>

                    <div class="toolbar-pills">
                        <div class="combo combo--arrow combo--toolbar combo--satuan" id="combo_satuanFilter">
                            <input type="text" id="cari_satuanFilter" class="combo-input" readonly autocomplete="off"
                                aria-label="Satuan">
                            <div class="combo-list" id="list_satuanFilter"></div>
                        </div>
                        <select name="satuan" id="satuanFilter" class="combo-hidden" tabindex="-1"
                            aria-hidden="true">
                            @foreach ($opsi as $o)
                                <option value="{{ $o['key'] }}" {{ $satuanDipilih === $o['key'] ? 'selected' : '' }}>
                                    {{ $o['label'] }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="toolbar-submit">Terapkan</button>
                    </div>
                </div>

                <input type="hidden" name="bulan_statistik" id="hiddenBulanStatistik" value="{{ $blnStat }}">
            </form>
        </section>

        {{-- ============ 3. STATIC SUMMARY CARDS (4 CARD ATAS) ============ --}}
        <section class="static-summary-grid">
            <div class="summary-card">
                <div class="summary-icon summary-icon--teal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2.5"></circle>
                        <path d="M6 6v-1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Saldo Kas</div>
                    <div class="summary-value">Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }}</div>
                    <div class="summary-desc">Pemasukan − pengeluaran</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon summary-icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Saldo Warga</div>
                    <div class="summary-value">Rp {{ number_format($totalSaldoWarga ?? 0, 0, ',', '.') }}</div>
                    <div class="summary-desc">Total saldo aktif warga</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon summary-icon--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7h11v8H3z"></path>
                        <path d="M14 10h4l3 3v2h-7z"></path>
                        <circle cx="7" cy="18" r="2"></circle>
                        <circle cx="17" cy="18" r="2"></circle>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Penjualan Pengepul</div>
                    <div class="summary-value">Rp {{ number_format($totalPenjualanPengepul ?? 0, 0, ',', '.') }}</div>
                    <div class="summary-desc">Total barang keluar</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon summary-icon--orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                        <path d="M16 15h2"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Pencairan Saldo</div>
                    <div class="summary-value">Rp {{ number_format($totalPencairanSaldo ?? 0, 0, ',', '.') }}</div>
                    <div class="summary-desc">Total pencairan warga</div>
                </div>
            </div>
        </section>

        {{-- ============ 4. DYNAMIC SUMMARY CARDS (4 CARD BAWAH) ============ --}}
        <section class="dynamic-summary-grid">
            <div class="summary-card">
                <div class="summary-icon summary-icon--indigo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Stok Tersedia</div>
                    <div class="summary-value" id="nilaiSampahTersedia">0</div>
                    <div class="summary-desc" id="descSampahTersedia">Siap diolah / dijual</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon summary-icon--purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Sampah Masuk</div>
                    <div class="summary-value" id="nilaiSampahRentang">0</div>
                    <div class="summary-desc" id="descSampahRentang">Akumulasi periode terpilih</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon summary-icon--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M8 6h13"></path>
                        <path d="M8 12h13"></path>
                        <path d="M8 18h13"></path>
                        <path d="M3 6h.01"></path>
                        <path d="M3 12h.01"></path>
                        <path d="M3 18h.01"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Jumlah Setoran</div>
                    <div class="summary-value">{{ number_format($jumlahSetoran ?? 0, 0, ',', '.') }}</div>
                    <div class="summary-desc">Total transaksi setoran</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon summary-icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path>
                        <circle cx="19" cy="8" r="2.3"></circle>
                        <path d="M17 21v-1a3 3 0 0 0-2-2.83"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Warga Aktif</div>
                    <div class="summary-value">{{ number_format($jumlahWarga ?? 0, 0, ',', '.') }}</div>
                    <div class="summary-desc">Status warga aktif</div>
                </div>
            </div>
        </section>

        {{-- ============ 5. CHARTS ============ --}}
        <div class="dashboard-columns">
            {{-- Bar Chart --}}
            <div class="card chart-card chart-card--large">
                <div class="card-head card-head--row">
                    <div>
                        <h2 class="card-title">Setoran Sampah per Bulan</h2>
                        <p class="card-sub">Total jumlah sampah tahun {{ $thnDipilih }}</p>
                    </div>
                    @if ($opsi->count() > 1)
                        <div class="combo combo--arrow" id="combo_satuanBulanan">
                            <input type="text" id="cari_satuanBulanan" class="combo-input" readonly
                                autocomplete="off" aria-label="Satuan grafik bulanan">
                            <div class="combo-list" id="list_satuanBulanan"></div>
                        </div>
                        <select id="satuanBulanan" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            @foreach ($opsi as $o)
                                <option value="{{ $o['key'] }}" {{ $satuanDipilih === $o['key'] ? 'selected' : '' }}>
                                    {{ $o['label'] }}</option>
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

            {{-- Statistik Jenis Sampah --}}
            <div class="card chart-card chart-card--small">
                <div class="card-head card-head--row">
                    <div>
                        <h2 class="card-title">Statistik Jenis Sampah</h2>
                        <p class="card-sub">
                            {{ $blnStat > 0 ? $namaBulanFull[$blnStat] . ' ' . $thnDipilih : 'Tahun ' . $thnDipilih . ' (Semua Bulan)' }}
                        </p>
                    </div>
                    <div class="chart-controls">
                        <div class="view-toggle" role="group" aria-label="Mode tampilan statistik">
                            <button type="button" class="view-toggle-btn active" id="btnModeDaftar" data-mode="daftar">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M8 6h13"></path>
                                    <path d="M8 12h13"></path>
                                    <path d="M8 18h13"></path>
                                    <path d="M3 6h.01"></path>
                                    <path d="M3 12h.01"></path>
                                    <path d="M3 18h.01"></path>
                                </svg>
                                <span>Daftar</span>
                            </button>
                            <button type="button" class="view-toggle-btn" id="btnModeDonut" data-mode="donut">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>Donut</span>
                            </button>
                        </div>

                        <div class="combo combo--arrow combo--compact" id="combo_selectBulanStatistik">
                            <input type="text" id="cari_selectBulanStatistik" class="combo-input" readonly
                                autocomplete="off" aria-label="Pilih bulan statistik jenis sampah">
                            <div class="combo-list" id="list_selectBulanStatistik"></div>
                        </div>
                        <select id="selectBulanStatistik" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            <option value="0" {{ $blnStat === 0 ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach ($namaBulanFull as $num => $labelBln)
                                <option value="{{ $num }}" {{ $blnStat === $num ? 'selected' : '' }}>
                                    {{ $labelBln }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- MODE DAFTAR --}}
                <div class="jenis-list-container" id="jenisModeDaftar">
                    <div class="jenis-list-header">
                        <div class="search-box">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                            <input type="text" id="cariJenisSampah" placeholder="Cari jenis sampah..."
                                aria-label="Cari jenis sampah">
                        </div>
                        <div class="jenis-total-badge" id="jenisTotalBadge">Total: 0</div>
                    </div>

                    <div class="jenis-list" id="jenisList"></div>
                </div>

                {{-- MODE DONUT --}}
                <div class="jenis-donut-container" id="jenisModeDonut" style="display: none;">
                    <div class="chart-container chart-container--doughnut">
                        <canvas id="jenisSampahChart"></canvas>
                        <div class="chart-center-label" id="jenisCenterLabel" aria-hidden="true">
                            <span class="chart-center-value" id="jenisCenterValue">0</span>
                            <span class="chart-center-sub">Total Masuk</span>
                        </div>
                        <div class="chart-empty" id="jenisKosong" style="display:none;"></div>
                    </div>
                    <div class="chart-custom-legend" id="jenisLegend" hidden></div>
                </div>
            </div>
        </div>

        {{-- ============ 6. RIWAYAT SETORAN ============ --}}
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
                        @forelse ($riwayat as $setoran)
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
    </div>
@endsection

@push('scripts')
    <script>
        window.__DASHBOARD_DATA__ = {
            opsiSatuan: @json($opsiSatuan ?? []),
            setoranPerBulan: @json($setoranPerBulan ?? []),
            statistikJenisSampah: @json($statistikJenisSampah ?? []),
            totalJumlahSampahPerSatuan: @json($totalJumlahSampahPerSatuan ?? []),
            sampahMasukRentangPerSatuan: @json($sampahMasukRentangPerSatuan ?? []),
            sampahTersediaPerSatuan: @json($sampahTersediaPerSatuan ?? []),
            tahunDipilih: {{ $thnDipilih }},
            bulanStatistik: {{ $blnStat }},
            bulanAwal: {{ $bAwal }},
            bulanAkhir: {{ $bAkhir }},
            namaBulan: @json($namaBulan),
        };
    </script>
    @vite('resources/js/pages/dashboard.js')
@endpush
