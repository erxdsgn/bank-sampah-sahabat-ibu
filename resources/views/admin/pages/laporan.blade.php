@extends('admin.layouts.app')

@section('title', 'Laporan & Riwayat')
@section('active', 'laporan')
@section('crumbs', 'Konten & Laporan | Laporan & Riwayat')

@push('styles')
    @vite('resources/css/pages/laporan.css')
@endpush

@section('content')
@php
    $activeTab = request('tab') === 'setoran' ? 'setoran' : 'penjualan';

    $statusMap = [
        'pending' => ['warning', 'Menunggu'],
        'approved' => ['success', 'Disetujui'],
        'selesai' => ['success', 'Disetujui'],
        'rejected' => ['danger', 'Ditolak'],
    ];

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

    // Satuan yang boleh desimal: hanya kg & liter. Selain itu (gram, pcs, set, unit) bilangan bulat.
    $satuanDesimal = ['kg', 'liter'];

    // Nilai disimpan apa adanya sesuai satuan (kg tetap kg, liter tetap liter, pcs/set/unit integer)
    $formatSatuan = function ($nilai, $satuanRaw) use ($normSatuan, $satuanDesimal) {
        $nilai  = (float) $nilai;
        $satuan = $normSatuan($satuanRaw);
        $desimal = in_array($satuan, $satuanDesimal, true) ? 2 : 0;

        $label = match ($satuan) {
            'kg'    => 'Kg',
            'gram'  => 'Gram',
            'pcs'   => 'Pcs',
            'unit'  => 'Unit',
            'set'   => 'Set',
            'liter' => 'Liter',
            default => ucfirst($satuan),
        };

        return [number_format($nilai, $desimal, ',', '.'), $label];
    };

    $urutanSatuan = ['kg' => 1, 'gram' => 2, 'pcs' => 3, 'unit' => 4, 'set' => 5, 'liter' => 6];

    $perPagePenjualan = (int) request('penjualan_per_page', 10);
    $perPageSetoran = (int) request('setoran_per_page', 10);

    $paginationWindow = function ($paginator) {
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $items = [];
        for ($i = 1; $i <= $last; $i++) {
            if ($i === 1 || $i === $last || ($i >= $current - 1 && $i <= $current + 1)) {
                $items[] = $i;
            } elseif ($i === $current - 2 || $i === $current + 2) {
                $items[] = null;
            }
        }
        return $items;
    };

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

<div class="laporan-page">

    {{-- HERO --}}
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">KONTEN & LAPORAN</span>
            <h1 class="hero-title">
                Laporan <span class="accent">& Riwayat</span>
            </h1>
            <p class="hero-sub">
                Pantau transaksi, setoran warga, dan aktivitas operasional Bank Sampah.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModalCetak()">
                <svg viewBox="0 0 24 24">
                    <path d="M6 9V4h12v5"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <path d="M6 14h12v7H6z"></path>
                </svg>
                Cetak Laporan
            </button>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="summary-grid summary-grid--two">
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
        <div class="tabs-header" role="tablist">
            <button type="button" role="tab" data-tab="penjualan"
                aria-selected="{{ $activeTab === 'penjualan' ? 'true' : 'false' }}"
                class="report-tab {{ $activeTab === 'penjualan' ? 'tab-active' : '' }}">
                <svg viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z"></path>
                    <path d="M8 8h8M8 12h8M8 16h5"></path>
                </svg>
                Penjualan
                <span class="tab-count">{{ $riwayatPenjualan->total() }}</span>
            </button>

            <button type="button" role="tab" data-tab="setoran"
                aria-selected="{{ $activeTab === 'setoran' ? 'true' : 'false' }}"
                class="report-tab {{ $activeTab === 'setoran' ? 'tab-active' : '' }}">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M17 7H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H7"></path>
                </svg>
                Setoran Warga
                <span class="tab-count">{{ $riwayatSetoran->total() }}</span>
            </button>
        </div>

        {{-- TAB PENJUALAN --}}
        <div class="report-panel" data-panel="penjualan" @if ($activeTab !== 'penjualan') hidden @endif>

            <div class="table-toolbar">
                <div class="toolbar-left">
                    <form class="table-search" method="GET" action="{{ route('admin.laporan.index') }}">
                        <input type="hidden" name="tab" value="penjualan">
                        <input type="hidden" name="penjualan_per_page" value="{{ $perPagePenjualan }}">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input type="text" id="searchPenjualan" name="search_penjualan"
                            value="{{ request('search_penjualan') }}"
                            placeholder="Cari nama pembeli..."
                            autocomplete="off">
                    </form>
                    @if (request('search_penjualan'))
                        <a href="{{ route('admin.laporan.index', ['tab' => 'penjualan', 'penjualan_per_page' => $perPagePenjualan]) }}" class="btn btn--ghost">
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
                                [$beratTampil, $satuanTampil] = $formatSatuan($item->berat, $satuanItem);
                                // Harga disimpan apa adanya per satuan (Rp/kg, Rp/liter, Rp/pcs, dst.)
                                $hargaTampil = (float) $item->harga_jual_satuan;
                            @endphp
                            <tr>
                                <td class="nowrap">
                                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                </td>
                                <td><strong>{{ $item->pembeli ?: '-' }}</strong></td>
                                <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                                <td class="col-num nowrap">{{ $beratTampil }} {{ $satuanTampil }}</td>
                                <td class="col-num nowrap">
                                    Rp {{ number_format($hargaTampil, 0, ',', '.') }} / {{ $satuanTampil }}
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
                <div class="table-pagination-controls">
                    <div class="per-page">
                        <label for="cari_perPagePenjualan">Baris per halaman:</label>
                        <div class="combo combo--up combo--arrow" id="combo_perPagePenjualan">
                            <input type="text" id="cari_perPagePenjualan" class="combo-input" placeholder=""
                                autocomplete="off" aria-label="Baris per halaman penjualan">
                            <div class="combo-list" id="list_perPagePenjualan"></div>
                        </div>
                        <select id="perPagePenjualan" class="combo-hidden" tabindex="-1" aria-hidden="true"
                            data-nav-param="penjualan_per_page" data-nav-page-param="penjualan_page">
                            <option value="10" {{ $perPagePenjualan == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPagePenjualan == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPagePenjualan == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPagePenjualan == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    @php
                        $riwayatPenjualan->appends(
                            array_merge(request()->except('penjualan_page', 'tab'), ['tab' => 'penjualan'])
                        );
                        $halamanPenjualan = $paginationWindow($riwayatPenjualan);
                    @endphp

                    <div class="pagination-wrap">
                        <div class="pagination-buttons">
                            <a href="{{ $riwayatPenjualan->previousPageUrl() ?? '#' }}"
                                class="btn btn--ghost {{ $riwayatPenjualan->onFirstPage() ? 'is-disabled' : '' }}"
                                @if ($riwayatPenjualan->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>‹</a>

                            @foreach($halamanPenjualan as $item)
                                @if (is_null($item))
                                    <span class="pagination-dots">…</span>
                                @else
                                    <a href="{{ $riwayatPenjualan->url($item) }}"
                                        class="btn {{ $item == $riwayatPenjualan->currentPage() ? 'btn--primary' : 'btn--ghost' }}">{{ $item }}</a>
                                @endif
                            @endforeach

                            <a href="{{ $riwayatPenjualan->nextPageUrl() ?? '#' }}"
                                class="btn btn--ghost {{ !$riwayatPenjualan->hasMorePages() ? 'is-disabled' : '' }}"
                                @if (!$riwayatPenjualan->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>›</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- TAB SETORAN WARGA --}}
        <div class="report-panel" data-panel="setoran" @if ($activeTab !== 'setoran') hidden @endif>

            <div class="table-toolbar">
                <div class="toolbar-left">
                    <form class="table-search" method="GET" action="{{ route('admin.laporan.index') }}">
                        <input type="hidden" name="tab" value="setoran">
                        <input type="hidden" name="setoran_per_page" value="{{ $perPageSetoran }}">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input type="text" id="searchSetoran" name="search_setoran"
                            value="{{ request('search_setoran') }}"
                            placeholder="Cari nama warga atau NIK..."
                            autocomplete="off">
                    </form>
                    @if (request('search_setoran'))
                        <a href="{{ route('admin.laporan.index', ['tab' => 'setoran', 'setoran_per_page' => $perPageSetoran]) }}" class="btn btn--ghost">
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
                                        [$nilai, $label] = $formatSatuan($group->sum('berat'), $satuanKey);
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
                                <td>{{ $namaKategori ?: '-' }}</td>
                                <td class="col-num">{{ $totalBeratTampil }}</td>
                                <td class="col-num nowrap">
                                    <strong class="amount-in">
                                        Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}
                                    </strong>
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
                                                : 'Setoran sampah warga akan muncul di sini.' }}
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
                    Riwayat setoran
                    <strong>{{ $riwayatSetoran->total() }}</strong>
                    transaksi
                </div>
                <div class="table-pagination-controls">
                    <div class="per-page">
                        <label for="cari_perPageSetoran">Baris per halaman:</label>
                        <div class="combo combo--up combo--arrow" id="combo_perPageSetoran">
                            <input type="text" id="cari_perPageSetoran" class="combo-input" placeholder=""
                                autocomplete="off" aria-label="Baris per halaman setoran">
                            <div class="combo-list" id="list_perPageSetoran"></div>
                        </div>
                        <select id="perPageSetoran" class="combo-hidden" tabindex="-1" aria-hidden="true"
                            data-nav-param="setoran_per_page" data-nav-page-param="setoran_page">
                            <option value="10" {{ $perPageSetoran == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPageSetoran == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPageSetoran == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPageSetoran == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    @php
                        $riwayatSetoran->appends(
                            array_merge(request()->except('setoran_page', 'tab'), ['tab' => 'setoran'])
                        );
                        $halamanSetoran = $paginationWindow($riwayatSetoran);
                    @endphp

                    <div class="pagination-wrap">
                        <div class="pagination-buttons">
                            <a href="{{ $riwayatSetoran->previousPageUrl() ?? '#' }}"
                                class="btn btn--ghost {{ $riwayatSetoran->onFirstPage() ? 'is-disabled' : '' }}"
                                @if ($riwayatSetoran->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>‹</a>

                            @foreach($halamanSetoran as $item)
                                @if (is_null($item))
                                    <span class="pagination-dots">…</span>
                                @else
                                    <a href="{{ $riwayatSetoran->url($item) }}"
                                        class="btn {{ $item == $riwayatSetoran->currentPage() ? 'btn--primary' : 'btn--ghost' }}">{{ $item }}</a>
                                @endif
                            @endforeach

                            <a href="{{ $riwayatSetoran->nextPageUrl() ?? '#' }}"
                                class="btn btn--ghost {{ !$riwayatSetoran->hasMorePages() ? 'is-disabled' : '' }}"
                                @if (!$riwayatSetoran->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>›</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>

    {{-- MODAL CETAK --}}
    <div class="modal-overlay" id="modalCetak">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 9V4h12v5"></path>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <path d="M6 14h12v7H6z"></path>
                    </svg>
                    Cetak Laporan
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalCetak')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group form-group--full">
                        <label>Jenis Laporan yang Dicetak</label>
                        <div class="print-radio-grid">
                            <label class="print-radio-card active" data-jenis="ringkasan">
                                <input type="radio" name="jenis_laporan" value="ringkasan" checked hidden>
                                <div class="print-radio-icon">📊</div>
                                <div><strong>Ringkasan</strong><span>Statistik & ringkasan singkat</span></div>
                            </label>
                            <label class="print-radio-card" data-jenis="penjualan">
                                <input type="radio" name="jenis_laporan" value="penjualan" hidden>
                                <div class="print-radio-icon">💰</div>
                                <div><strong>Penjualan</strong><span>Tabel penjualan ke pengepul</span></div>
                            </label>
                            <label class="print-radio-card" data-jenis="setoran">
                                <input type="radio" name="jenis_laporan" value="setoran" hidden>
                                <div class="print-radio-icon">📥</div>
                                <div><strong>Setoran Warga</strong><span>Tabel setoran sampah</span></div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group form-group--full">
                        <label>Orientasi Kertas</label>
                        <div class="print-radio-grid cols-2">
                            <label class="print-radio-card active" data-orientasi="portrait">
                                <input type="radio" name="orientasi" value="portrait" checked hidden>
                                <div class="print-radio-icon">📄</div>
                                <div><strong>Potrait</strong><span>Tegak (A4)</span></div>
                            </label>
                            <label class="print-radio-card" data-orientasi="landscape">
                                <input type="radio" name="orientasi" value="landscape" hidden>
                                <div class="print-radio-icon">📃</div>
                                <div><strong>Landscape</strong><span>Mendatar (A4)</span></div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cetakTanggalMulai">Tanggal Mulai (opsional)</label>
                        <input type="date" id="cetakTanggalMulai" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cetakTanggalAkhir">Tanggal Akhir (opsional)</label>
                        <input type="date" id="cetakTanggalAkhir" class="form-control">
                    </div>
                </div>
                <p class="form-note">
                    Data yang akan dicetak: <strong id="cetakInfoJumlah">-</strong>.
                    Untuk cetak semua data, ubah <em>"Baris per halaman"</em> ke <strong>100</strong> terlebih dahulu.
                </p>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalCetak')">Batal</button>
                <button class="btn btn--primary" type="button" onclick="cetakLaporan()">Cetak Sekarang</button>
            </div>
        </div>
    </div>

    {{-- PRINT AREA (diisi JS sebelum print) --}}
    <div id="printArea" class="print-area"></div>

</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/laporan.js')
@endpush
