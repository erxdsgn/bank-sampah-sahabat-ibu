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

            /**
             * Menyeragamkan penulisan satuan ke bentuk baku.
             */
            $normSatuan = function ($satuanRaw) {
                $s = strtolower(trim((string) ($satuanRaw ?: 'kg')));

                return match (true) {
                    in_array($s, ['kg', 'kilogram']) => 'kg',
                    in_array($s, ['g', 'gram']) => 'gram',
                    in_array($s, ['pcs', 'pc', 'piece', 'pieces', 'buah', 'lembar']) => 'pcs',
                    in_array($s, ['l', 'liter', 'litre', 'ltr']) => 'liter',
                    $s === 'unit' => 'unit',
                    $s === 'set' => 'set',
                    default => $s,
                };
            };

            /**
             * Memformat nilai kuantitas sesuai satuan kategori sampah.
             *
             * @return array [nilai terformat, label satuan]
             */
            $formatSatuan = function ($nilaiDasar, $satuanRaw) use ($normSatuan) {
                $nilaiDasar = (float) $nilaiDasar;
                $satuan = $normSatuan($satuanRaw);

                return match ($satuan) {
                    'kg' => [number_format($nilaiDasar / 1000, 2, ',', '.'), 'Kg'],
                    'gram' => [number_format($nilaiDasar, 0, ',', '.'), 'Gram'],
                    'pcs' => [number_format($nilaiDasar, 0, ',', '.'), 'Pcs'],
                    'unit' => [number_format($nilaiDasar, 0, ',', '.'), 'Unit'],
                    'set' => [number_format($nilaiDasar, 0, ',', '.'), 'Set'],
                    'liter' => [number_format($nilaiDasar, 2, ',', '.'), 'Liter'],
                    default => [number_format($nilaiDasar, 2, ',', '.'), ucfirst($satuan)],
                };
            };

            $urutanSatuan = ['kg' => 1, 'gram' => 2, 'pcs' => 3, 'unit' => 4, 'set' => 5, 'liter' => 6];

            $volumeItems = collect($rincianVolume ?? [])
                ->groupBy(fn($total, $satuan) => $normSatuan($satuan))
                ->map(fn($group) => $group->sum())
                ->sortBy(fn($total, $satuan) => $urutanSatuan[$satuan] ?? 99)
                ->map(function ($total, $satuan) use ($formatSatuan) {
                    [$nilai, $label] = $formatSatuan($total, $satuan);
                    return ['nilai' => $nilai, 'label' => $label];
                })
                ->values();
        @endphp

        {{-- KOP LAPORAN KHUSUS CETAK (Seperti Dokumen Word) --}}
        <div class="print-header-doc" style="display: none;">
            <h2>LAPORAN RESMI BANK SAMPAH</h2>
            <p>Tanggal Cetak: {{ date('d/m/Y') }}</p>
            <hr style="border: 1px solid #000; margin-bottom: 15px;">
        </div>

        <div class="laporan-page">

            {{-- HERO --}}
            <section class="hero">
                <div class="hero-text">
                    <span class="eyebrow">KONTEN & LAPORAN</span>

                    <h1 class="hero-title">
                        Laporan <span class="accent">& Riwayat</span>
                    </h1>

                    <p class="hero-sub">
                        Pantau transaksi, penyetoran warga, dan aktivitas operasional Bank Sampah.
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


            {{-- SUMMARY (Diubah menjadi 2 Kotak Statistik) --}}
            <section class="summary-grid summary-grid--two">

                {{-- Kotak 1: Total Penjualan & Total Transaksi --}}
                <div class="summary-card">
                    <div class="summary-card-top">
                        <div class="summary-dual-content">
                            <div class="summary-item-block">
                                <div class="summary-label">Total Penjualan</div>
                                <div class="summary-value">
                                    Rp {{ number_format($totalPenjualan, 0, ',', '.') }}
                                </div>
                            </div>

                            <div class="summary-item-block summary-item-block--divider">
                                <div class="summary-label">Total Transaksi</div>
                                <div class="summary-value">
                                    {{ $totalTransaksiKeluar }} <small class="summary-unit-text">transaksi</small>
                                </div>
                            </div>
                        </div>

                        <div class="summary-icon summary-icon--income">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3v18"></path>
                                <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="summary-info">Akumulasi hasil penjualan dan jumlah transaksi barang keluar</div>
                </div>

                {{-- Kotak 2: Total Volume Keluar --}}
                <div class="summary-card">
                    <div class="summary-card-top">
                        <div>
                            <div class="summary-label">Total Volume Keluar</div>

                            <div class="summary-value-badges">
                                @forelse ($volumeItems as $v)
                                    <span class="volume-badge">
                                        <strong>{{ $v['nilai'] }}</strong> <small>{{ $v['label'] }}</small>
                                    </span>
                                @empty
                                    <span class="volume-badge">
                                        <strong>0</strong> <small>Kg</small>
                                    </span>
                                @endforelse
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

                    <div class="summary-info">Sampah terjual / terdistribusi (per satuan)</div>
                </div>

            </section>


            {{-- REPORT CARD --}}
            <section class="card report-card">

                {{-- TAB --}}
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

                    <button type="button" data-tab="setoran"
                        class="report-tab {{ $activeTab === 'setoran' ? 'tab-active' : '' }}">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3v18"></path>
                            <path d="M17 7H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H7"></path>
                        </svg>

                        Penyetoran Warga

                        <span class="tab-count">{{ $riwayatSetoran->total() }}</span>
                    </button>

                </div>


                {{-- TAB PENJUALAN --}}
                <div class="report-panel" data-panel="penjualan" @if ($activeTab !== 'penjualan') hidden @endif>

                    <div class="table-toolbar">
                        <div class="toolbar-left">

                            <form class="table-search" method="GET" action="{{ route('admin.laporan.index') }}">
                                <input type="hidden" name="tab" value="penjualan">

                                <svg viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>

                                <input type="text" id="searchPenjualan" name="search_penjualan"
                                    value="{{ request('search_penjualan') }}"
                                    placeholder="Cari nama pembeli... (Enter untuk cari semua data)"
                                    autocomplete="off">
                            </form>

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
                                    <th class="col-num">Kuantitas</th>
                                    <th class="col-num">Harga / Satuan</th>
                                    <th class="col-num">Total Nominal</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($riwayatPenjualan as $item)

                                    @php
                                        $satuanItem = $normSatuan($item->kategori->satuan ?? null);

                                        [$beratTampil, $satuanTampil] = $formatSatuan(
                                            $item->berat_gram,
                                            $satuanItem
                                        );

                                        $hargaTampil = $satuanItem === 'kg'
                                            ? $item->harga_jual_per_gram * 1000
                                            : $item->harga_jual_per_gram;
                                    @endphp

                                    <tr>
                                        <td class="nowrap">
                                            {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                        </td>

                                        <td><strong>{{ $item->pembeli ?: '-' }}</strong></td>

                                        <td>
                                            @if ($item->kategori->nama_kategori ?? null)
                                                <strong>{{ $item->kategori->nama_kategori }}</strong>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="col-num nowrap">
                                            <strong>{{ $beratTampil }}</strong>
                                            <span class="unit">{{ $satuanTampil }}</span>
                                        </td>

                                        <td class="col-num nowrap">
                                            Rp {{ number_format($hargaTampil, 0, ',', '.') }}
                                            <span class="unit">/ {{ $satuanTampil }}</span>
                                        </td>

                                        <td class="col-num nowrap">
                                            <strong class="amount-in">
                                                Rp {{ number_format($item->total, 0, ',', '.') }}
                                            </strong>
                                        </td>

                                        <td>{{ $item->admin->nama ?? '-' }}</td>
                                    </tr>

                                @empty

                                    <tr class="empty-row">
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <div class="empty-icon">📋</div>

                                                <strong>
                                                    {{ request('search_penjualan') ? 'Pembeli Tidak Ditemukan' : 'Belum Ada Data Penjualan' }}
                                                </strong>

                                                <p>
                                                    {{ request('search_penjualan')
                                                        ? 'Coba gunakan nama pembeli lain atau reset pencarian.'
                                                        : 'Transaksi barang keluar akan muncul di sini.' }}
                                                </p>
                                            </div>
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
                            {{ $riwayatPenjualan->appends(
                                array_merge(request()->except('penjualan_page', 'tab'), ['tab' => 'penjualan'])
                            )->links() }}
                        </div>
                    </div>

                </div>


                {{-- TAB SETORAN WARGA --}}
                <div class="report-panel" data-panel="setoran" @if ($activeTab !== 'setoran') hidden @endif>

                    <div class="table-toolbar">
                        <div class="toolbar-left">

                            <form class="table-search" method="GET" action="{{ route('admin.laporan.index') }}">
                                <input type="hidden" name="tab" value="setoran">

                                <svg viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>

                                <input type="text" id="searchSetoran" name="search_setoran"
                                    value="{{ request('search_setoran') }}"
                                    placeholder="Cari nama warga atau NIK... (Enter untuk cari semua data)"
                                    autocomplete="off">
                            </form>

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
                                    <th class="col-num">Total Jumlah</th>
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
                                            ->map(fn($detail) => optional($detail->kategori)->nama_lengkap
                                                ?? optional($detail->kategori)->nama_kategori)
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');

                                        [$badgeVariant, $badgeLabel] = $statusMap[$setoran->status] ?? [
                                            'warning',
                                            ucfirst((string) $setoran->status),
                                        ];

                                        $rincianBerat = $details
                                            ->groupBy(fn($detail) => $normSatuan(optional($detail->kategori)->satuan))
                                            ->map(function ($group, $satuanKey) use ($formatSatuan) {
                                                [$nilai, $label] = $formatSatuan($group->sum('berat_gram'), $satuanKey);
                                                return $nilai . ' ' . $label;
                                            })
                                            ->values();

                                        $totalBeratTampil = $rincianBerat->isNotEmpty()
                                            ? $rincianBerat->implode(', ')
                                            : '-';
                                    @endphp

                                    <tr>
                                        <td class="nowrap">
                                            {{ $setoran->tanggal_setoran
                                                ? \Carbon\Carbon::parse($setoran->tanggal_setoran)->format('d/m/Y')
                                                : '-' }}
                                        </td>

                                        <td>
                                            <span class="party-info">
                                                <strong>{{ $setoran->warga->nama ?? '-' }}</strong>
                                                <span class="mono">{{ $setoran->warga->nik ?? '-' }}</span>
                                            </span>
                                        </td>

                                        <td>
                                            @if ($namaKategori)
                                                <strong>{{ $namaKategori }}</strong>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="col-num">{{ $totalBeratTampil }}</td>

                                        <td class="col-num nowrap">
                                            <strong>Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}</strong>
                                        </td>

                                        <td>
                                            <span class="badge badge--{{ $badgeVariant }}">{{ $badgeLabel }}</span>
                                        </td>

                                        <td class="cell-note" title="{{ $setoran->catatan_admin }}">
                                            {{ $setoran->catatan_admin
                                                ? \Illuminate\Support\Str::limit($setoran->catatan_admin, 60)
                                                : '-' }}
                                        </td>
                                    </tr>

                                @empty

                                    <tr class="empty-row">
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <div class="empty-icon">📋</div>

                                                <strong>
                                                    {{ request('search_setoran') ? 'Setoran Tidak Ditemukan' : 'Belum Ada Data Setoran' }}
                                                </strong>

                                                <p>
                                                    {{ request('search_setoran')
                                                        ? 'Coba gunakan nama warga atau NIK lain, atau reset pencarian.'
                                                        : 'Penyetoran sampah warga akan muncul di sini.' }}
                                                </p>
                                            </div>
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
                            {{ $riwayatSetoran->appends(
                                array_merge(request()->except('setoran_page', 'tab'), ['tab' => 'setoran'])
                            )->links() }}
                        </div>
                    </div>

                </div>

            </section>

        </div>


        <style>
            /* LAPORAN & RIWAYAT */

            .laporan-page {
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

                --success-bg: #dcfce7;
                --success-text: #15803d;

                --warning-bg: #fef3c7;
                --warning-text: #d97706;

                --danger-bg: #fee2e2;
                --danger-text: #b91c1c;

                --primary-soft: #eef2ff;
                --primary-text: var(--primary, #4338ca);

                color: var(--text);
            }

            html[data-theme="dark"] .laporan-page {
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

                --success-bg: rgba(34, 197, 94, .14);
                --success-text: #4ade80;

                --warning-bg: rgba(245, 158, 11, .14);
                --warning-text: #fbbf24;

                --danger-bg: rgba(239, 68, 68, .14);
                --danger-text: #f87171;

                --primary-soft: rgba(99, 102, 241, .14);
                --primary-text: #a5b4fc;
            }

            .laporan-page input,
            .laporan-page select,
            .laporan-page textarea {
                color-scheme: light;
            }

            html[data-theme="dark"] .laporan-page input,
            html[data-theme="dark"] .laporan-page select,
            html[data-theme="dark"] .laporan-page textarea {
                color-scheme: dark;
            }

            .laporan-page .report-tab,
            .laporan-page .table-search input,
            .laporan-page .btn {
                font-family: inherit;
            }

            /* SUMMARY & GRID 2 KOTAK */
            .laporan-page .summary-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 20px;
            }

            .laporan-page .summary-grid--two {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .laporan-page .summary-dual-content {
                display: flex;
                flex-direction: column;
                gap: 14px;
                width: 100%;
            }

            .laporan-page .summary-item-block--divider {
                padding-top: 10px;
                border-top: 1px dashed var(--border);
            }

            .laporan-page .summary-unit-text {
                font-size: 14px;
                font-weight: 600;
                color: var(--text-secondary);
            }

            .laporan-page .summary-card {
                min-width: 0;
                padding: 18px;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                box-shadow: var(--shadow);
                transition: border-color .15s ease, transform .15s ease;
            }

            .laporan-page .summary-card:hover {
                border-color: rgba(34, 197, 94, .35);
            }

            .laporan-page .summary-card-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
            }

            .laporan-page .summary-label {
                margin-bottom: 8px;
                color: var(--text-secondary);
                font-size: 13px;
                font-weight: 500;
            }

            .laporan-page .summary-value {
                color: var(--text);
                font-size: 21px;
                font-weight: 800;
                line-height: 1.25;
                letter-spacing: -.2px;
            }

            .laporan-page .summary-value small {
                color: var(--text-secondary);
                font-size: 12px;
                font-weight: 600;
            }

            .laporan-page .summary-value-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 4px;
            }

            .laporan-page .volume-badge {
                display: inline-flex;
                align-items: baseline;
                gap: 4px;
                padding: 4px 10px;
                background: var(--surface-secondary);
                border: 1px solid var(--border);
                border-radius: 8px;
                font-size: 15px;
                font-weight: 700;
                color: var(--text);
                font-variant-numeric: tabular-nums;
            }

            .laporan-page .volume-badge small {
                color: var(--text-secondary);
                font-size: 11px;
                font-weight: 600;
            }

            .laporan-page .summary-info {
                margin-top: 12px;
                color: var(--text-muted);
                font-size: 12px;
            }

            .laporan-page .summary-icon {
                width: 40px;
                height: 40px;
                flex: 0 0 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 11px;
                background: var(--primary-soft);
                color: var(--primary-text);
            }

            .laporan-page .summary-icon--income {
                background: var(--success-bg);
                color: var(--success-text);
            }

            .laporan-page .summary-icon svg {
                width: 20px;
                height: 20px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            /* CARD */
            .laporan-page .card {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                overflow: hidden;
                box-shadow: var(--shadow);
            }

            .laporan-page .report-card {
                margin-bottom: 20px;
            }

            .laporan-page .report-card .report-panel {
                display: block;
                width: 100%;
            }

            .laporan-page .report-card .report-panel[hidden] {
                display: none !important;
            }

            /* TABS */
            .laporan-page .tabs-header {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 0 24px;
                background: var(--surface);
                border-bottom: 1px solid var(--border);
            }

            .laporan-page .report-tab {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-right: 18px;
                padding: 16px 8px 14px;
                border: none;
                outline: none;
                background: transparent;
                color: var(--text-secondary);
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: color .15s ease, background .15s ease;
            }

            .laporan-page .report-tab svg {
                width: 16px;
                height: 16px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            .laporan-page .report-tab:hover,
            .laporan-page .report-tab.tab-active {
                color: var(--primary, #22c55e);
            }

            .laporan-page .report-tab.tab-active::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: -1px;
                height: 2px;
                background: var(--primary, #22c55e);
                border-radius: 2px 2px 0 0;
            }

            .laporan-page .tab-count {
                min-width: 22px;
                padding: 1px 7px;
                border-radius: 999px;
                background: var(--surface-secondary);
                color: var(--text-secondary);
                font-size: 11px;
                font-weight: 700;
                line-height: 1.6;
                text-align: center;
                font-variant-numeric: tabular-nums;
            }

            .laporan-page .report-tab.tab-active .tab-count {
                background: var(--primary-soft);
                color: var(--primary-text);
            }

            /* TOOLBAR */
            .laporan-page .table-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 24px;
                flex-wrap: wrap;
            }

            .laporan-page .toolbar-left {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .laporan-page .toolbar-left .btn {
                height: 40px;
                padding: 0 16px;
                white-space: nowrap;
            }

            .laporan-page .table-search {
                position: relative;
                width: 400px;
                max-width: 100%;
                margin: 0;
            }

            .laporan-page .table-search svg {
                position: absolute;
                left: 12px;
                top: 50%;
                width: 18px;
                height: 18px;
                transform: translateY(-50%);
                fill: none;
                stroke: var(--text-muted);
                stroke-width: 1.8;
                pointer-events: none;
            }

            .laporan-page .table-search input[type="text"] {
                box-sizing: border-box;
                width: 100%;
                height: 40px;
                padding: 0 14px 0 40px;
                background: var(--input-bg);
                border: 1px solid var(--border);
                border-radius: 8px;
                outline: none;
                color: var(--text);
                font-size: 13px;
                transition: border-color .15s ease, box-shadow .15s ease;
            }

            .laporan-page .table-search input::placeholder {
                color: var(--text-muted);
            }

            .laporan-page .table-search input:focus {
                border-color: var(--primary, #22c55e);
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }

            /* BUTTON */
            .laporan-page .btn--ghost {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                background: transparent;
                border: 1px solid var(--border);
                color: var(--text-secondary);
            }

            .laporan-page .btn--ghost:hover {
                background: var(--table-hover);
                border-color: var(--border);
                color: var(--text);
            }

            .laporan-page .btn--ghost svg {
                width: 16px;
                height: 16px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            /* TABLE */
            .laporan-page .table-responsive {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .laporan-page .data-table {
                width: 100%;
                min-width: 900px;
                border-collapse: collapse;
            }

            .laporan-page .data-table th {
                padding: 13px 16px;
                background: var(--table-head);
                color: var(--text-secondary);
                border-top: 1px solid var(--border);
                border-bottom: 1px solid var(--border);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                text-align: left;
                white-space: nowrap;
            }

            .laporan-page .data-table td {
                padding: 16px;
                background: var(--table-row);
                color: var(--text);
                border-bottom: 1px solid var(--border);
                font-size: 13px;
                vertical-align: middle;
            }

            .laporan-page .data-table tbody tr {
                transition: background .12s ease;
            }

            .laporan-page .data-table tbody tr:hover td {
                background: var(--table-hover);
            }

            .laporan-page .data-table .col-num {
                text-align: right;
                font-variant-numeric: tabular-nums;
            }

            .laporan-page .data-table th.col-num {
                text-align: right;
            }

            .laporan-page .nowrap {
                white-space: nowrap;
            }

            .laporan-page .amount-in {
                color: var(--success-text);
            }

            .laporan-page .unit {
                color: var(--text-muted);
                font-size: 12px;
            }

            .laporan-page .mono {
                color: var(--text-secondary);
                font-family: monospace;
                font-size: 12px;
            }

            .laporan-page .cell-note {
                max-width: 260px;
                color: var(--text-secondary);
            }

            .laporan-page .party-info {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            /* BADGE */
            .laporan-page .badge {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
            }

            .laporan-page .badge--warning {
                background: var(--warning-bg);
                color: var(--warning-text);
            }

            .laporan-page .badge--success {
                background: var(--success-bg);
                color: var(--success-text);
            }

            .laporan-page .badge--danger {
                background: var(--danger-bg);
                color: var(--danger-text);
            }

            /* EMPTY STATE */
            .laporan-page .empty-row td {
                padding: 0 !important;
            }

            .laporan-page .empty-state {
                min-height: 190px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                background: var(--empty);
                text-align: center;
            }

            .laporan-page .empty-icon {
                margin-bottom: 10px;
                font-size: 30px;
                line-height: 1;
                opacity: .75;
            }

            .laporan-page .empty-state strong {
                color: var(--text);
                font-size: 14px;
            }

            .laporan-page .empty-state p {
                margin: 6px 0 0;
                max-width: 430px;
                color: var(--text-secondary);
                font-size: 12px;
                line-height: 1.6;
            }

            /* FOOTER */
            .laporan-page .table-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 24px;
                flex-wrap: wrap;
            }

            .laporan-page .table-info {
                color: var(--text-secondary);
                font-size: 13px;
            }

            .laporan-page .table-info strong {
                color: var(--text);
                font-weight: 700;
            }

            /* PAGINATION */
            .laporan-page .pagination-wrap {
                display: flex;
                align-items: center;
            }

            .laporan-page .pagination-wrap nav {
                margin: 0;
            }

            .laporan-page .pagination-wrap svg {
                width: 16px;
                height: 16px;
            }

            .laporan-page .pagination-wrap .pagination {
                display: flex;
                align-items: center;
                gap: 4px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .laporan-page .pagination-wrap .pagination .page-link,
            .laporan-page .pagination-wrap .pagination li > span {
                min-width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 10px;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 8px;
                color: var(--text-secondary);
                font-family: inherit;
                font-size: 13px;
                font-weight: 600;
                text-decoration: none;
            }

            .laporan-page .pagination-wrap .pagination .page-link:hover {
                border-color: var(--primary, #22c55e);
                color: var(--primary, #22c55e);
                background: var(--table-hover);
            }

            .laporan-page .pagination-wrap .pagination .active .page-link,
            .laporan-page .pagination-wrap .pagination .active > span {
                background: var(--primary, #22c55e);
                border-color: var(--primary, #22c55e);
                color: #fff;
            }

            .laporan-page .pagination-wrap .pagination .disabled .page-link,
            .laporan-page .pagination-wrap .pagination .disabled > span {
                background: var(--surface-secondary);
                color: var(--text-muted);
                border-color: var(--border);
            }

            /* RESPONSIVE */
            @media (max-width: 900px) {
                .laporan-page .summary-grid:not(.summary-grid--two) {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 768px) {
                .laporan-page .summary-grid--two {
                    grid-template-columns: 1fr !important;
                }
            }

            @media (max-width: 640px) {
                .laporan-page .tabs-header {
                    padding: 0 16px;
                    overflow-x: auto;
                    scrollbar-width: none;
                }

                .laporan-page .tabs-header::-webkit-scrollbar {
                    display: none;
                }

                .laporan-page .report-tab {
                    flex-shrink: 0;
                    white-space: nowrap;
                    margin-right: 10px;
                }

                .laporan-page .table-toolbar {
                    padding: 14px 16px;
                }

                .laporan-page .toolbar-left {
                    width: 100%;
                }

                .laporan-page .table-search {
                    width: 100%;
                }

                .laporan-page .toolbar-left .btn {
                    width: auto;
                }

                .laporan-page .table-footer {
                    justify-content: center;
                    padding: 14px 16px;
                }

                .laporan-page .table-info {
                    width: 100%;
                    text-align: center;
                }

                .laporan-page .pagination-wrap {
                    max-width: 100%;
                    overflow-x: auto;
                    padding-bottom: 2px;
                }
            }

            /* PRINT */
            @media print {
                aside,
                header,
                nav,
                .main-sidebar,
                .main-header,
                .navbar,
                .sidebar,
                footer {
                    display: none !important;
                }

                body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    font-family: Arial, sans-serif !important;
                    margin: 0 !important;
                    padding: 10px !important;
                }

                .print-header-doc {
                    display: block !important;
                    text-align: center;
                    margin-bottom: 20px;
                }
                .print-header-doc h2 {
                    font-size: 16px;
                    font-weight: bold;
                    margin: 0 0 5px 0;
                }
                .print-header-doc p {
                    font-size: 11px;
                    margin: 0;
                    color: #333;
                }

                .hero,
                .summary-grid,
                .hero-actions,
                .tabs-header,
                .laporan-page .table-toolbar,
                .laporan-page .table-footer {
                    display: none !important;
                }

                .laporan-page {
                    box-shadow: none !important;
                    border: none !important;
                    background: transparent !important;
                }

                .laporan-page .report-panel {
                    display: block !important;
                }

                .laporan-page .report-panel[hidden] {
                    display: none !important;
                }

                .laporan-page .card,
                .laporan-page .table-responsive {
                    border: none !important;
                    box-shadow: none !important;
                    overflow: visible !important;
                }

                .laporan-page .data-table {
                    width: 100% !important;
                    min-width: 100% !important;
                    border-collapse: collapse !important;
                    page-break-inside: auto !important;
                }

                .laporan-page .data-table tr {
                    page-break-inside: avoid !important;
                    page-break-after: auto !important;
                }

                .laporan-page .data-table th,
                .laporan-page .data-table td {
                    border: 1px solid #000000 !important;
                    padding: 5px 7px !important;
                    font-size: 10px !important;
                    color: #000000 !important;
                    background: transparent !important;
                }

                .laporan-page .data-table th {
                    background-color: #e6e6e6 !important;
                    font-weight: bold !important;
                    text-align: left !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .laporan-page .badge {
                    border: 1px solid #000 !important;
                    background: transparent !important;
                    color: #000 !important;
                    padding: 1px 4px !important;
                    font-size: 9px !important;
                }
            }
        </style>


        <script>
            document.addEventListener('DOMContentLoaded', function() {

                /* TAB LAPORAN */
                const tabs = document.querySelectorAll('.laporan-page .report-tab[data-tab]');
                const panels = document.querySelectorAll('.laporan-page .report-panel[data-panel]');

                tabs.forEach(function(tab) {
                    tab.addEventListener('click', function() {
                        const target = this.dataset.tab;

                        tabs.forEach(function(item) {
                            item.classList.toggle('tab-active', item === tab);
                        });

                        panels.forEach(function(panel) {
                            panel.hidden = panel.dataset.panel !== target;
                        });

                        const url = new URL(window.location);
                        url.searchParams.set('tab', target);
                        window.history.replaceState({}, '', url);
                    });
                });


                /* LIVE SEARCH */
                function bindLiveSearch(inputId, panelName) {
                    const input = document.getElementById(inputId);
                    const panel = document.querySelector('.laporan-page [data-panel="' + panelName + '"]');

                    if (!input || !panel) return;

                    input.addEventListener('input', function() {
                        const keyword = this.value.toLowerCase().trim();

                        panel.querySelectorAll('tbody tr:not(.empty-row)').forEach(function(row) {
                            row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' : 'none';
                        });
                    });
                }

                bindLiveSearch('searchPenjualan', 'penjualan');
                bindLiveSearch('searchSetoran', 'setoran');

            });
        </script>

    @endsection
