{{-- resources/views/admin/pages/laporan.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Laporan & Riwayat')
@section('crumbs', 'Laporan & Riwayat')
@section('active', 'laporan-riwayat')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/admin/css/laporan.css') }}">

<div class="lp">

    {{-- Judul --}}
    <div class="lp-head">
        <div>
            <h1 class="lp-title">Laporan & Riwayat</h1>
            <p class="lp-sub">Semua transaksi setoran, pencairan saldo, dan penjualan ke pengepul.</p>
        </div>
        <a href="{{ route('admin.laporan.create') }}" class="lp-btn lp-btn--primary">+ Tambah transaksi</a>
    </div>

    @if (session('success'))
        <div class="lp-alert lp-alert--success" role="status">{{ session('success') }}</div>
    @endif

    {{-- Ringkasan --}}
    @php
        $kgText = rtrim(rtrim(number_format($ringkasan['kg'], 2, ',', '.'), '0'), ',');
    @endphp
    <div class="lp-stats">
        <div class="lp-stat">
            <p class="lp-stat__label">Total sampah terkumpul</p>
            <p class="lp-stat__value lp-stat__value--green">{{ $kgText }} kg</p>
        </div>
        <div class="lp-stat">
            <p class="lp-stat__label">Jumlah transaksi</p>
            <p class="lp-stat__value">{{ number_format($ringkasan['jumlah'], 0, ',', '.') }}</p>
        </div>
        <div class="lp-stat">
            <p class="lp-stat__label">Total pencairan</p>
            <p class="lp-stat__value lp-stat__value--amber">Rp {{ number_format($ringkasan['pencairan'], 0, ',', '.') }}</p>
        </div>
        <div class="lp-stat">
            <p class="lp-stat__label">Total pemasukan</p>
            <p class="lp-stat__value lp-stat__value--blue">Rp {{ number_format($ringkasan['pemasukan'], 0, ',', '.') }}</p>
        </div>
    </div>
    <p class="lp-note">Total sampah, pencairan, dan pemasukan hanya menghitung transaksi berstatus Selesai. Angka mengikuti pencarian dan filter di bawah.</p>

    {{-- Pencarian & filter --}}
    <form method="GET" action="{{ route('admin.laporan.index') }}" class="lp-card lp-filter">
        <div class="lp-filter__full">
            <label for="q" class="lp-label">Cari transaksi</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                   placeholder="Kode transaksi, nama warga, jenis sampah, atau catatan"
                   class="lp-input">
        </div>

        <div>
            <label for="jenis" class="lp-label">Jenis</label>
            <select id="jenis" name="jenis" class="lp-input">
                <option value="">Semua jenis</option>
                @foreach (\App\Models\Transaksi::JENIS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['jenis'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="lp-label">Status</label>
            <select id="status" name="status" class="lp-input">
                <option value="">Semua status</option>
                @foreach (\App\Models\Transaksi::STATUS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="dari" class="lp-label">Dari tanggal</label>
            <input type="date" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}" class="lp-input">
        </div>

        <div>
            <label for="sampai" class="lp-label">Sampai tanggal</label>
            <input type="date" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="lp-input">
        </div>

        <div class="lp-filter__actions">
            <a href="{{ route('admin.laporan.index') }}" class="lp-btn">Atur ulang</a>
            <button type="submit" class="lp-btn lp-btn--dark">Cari</button>
        </div>
    </form>

    {{-- Tabel riwayat --}}
    <div class="lp-card lp-card--clip">
        <div class="lp-table-wrap">
            <table class="lp-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Warga</th>
                        <th>Jenis</th>
                        <th class="lp-num">Total</th>
                        <th>Status</th>
                        <th class="lp-num">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksis as $t)
                        <tr>
                            <td class="lp-nowrap">
                                <a href="{{ route('admin.laporan.show', $t) }}" class="lp-link lp-mono">{{ $t->kode }}</a>
                            </td>
                            <td class="lp-nowrap">{{ $t->tanggal->locale('id')->translatedFormat('d M Y') }}</td>
                            <td>{{ $t->nama_warga }}</td>
                            <td><span class="lp-badge {{ $t->jenis_badge }}">{{ $t->jenis_label }}</span></td>
                            <td class="lp-num"><strong>Rp {{ number_format($t->total, 0, ',', '.') }}</strong></td>
                            <td><span class="lp-badge {{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                            <td class="lp-num">
                                <div class="lp-actions">
                                    <a href="{{ route('admin.laporan.show', $t) }}" class="lp-link">Detail</a>
                                    <a href="{{ route('admin.laporan.edit', $t) }}" class="lp-link lp-link--muted">Ubah</a>
                                    <form method="POST" action="{{ route('admin.laporan.destroy', $t) }}"
                                          onsubmit="return confirm('Hapus transaksi {{ $t->kode }}? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="lp-link lp-link--danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="lp-empty">
                                @if (array_filter($filters))
                                    Tidak ada transaksi yang cocok. Ubah kata kunci atau
                                    <a href="{{ route('admin.laporan.index') }}" class="lp-link">atur ulang filter</a>.
                                @else
                                    Belum ada transaksi.
                                    <a href="{{ route('admin.laporan.create') }}" class="lp-link">Tambah transaksi pertama</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transaksis->total() > 0)
            <div class="lp-pager">
                <span>Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }} transaksi</span>
                @if ($transaksis->hasPages())
                    {{ $transaksis->links('pagination::default') }}
                @endif
            </div>
        @endif
    </div>
</div>
@endsection