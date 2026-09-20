{{-- resources/views/admin/pages/laporan-detail.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Laporan & Riwayat')
@section('crumbs', 'Laporan & Riwayat | Detail transaksi')
@section('active', 'laporan-riwayat')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/admin/css/laporan.css') }}">

@php
    // 2,50 -> "2,5" ; 3,00 -> "3"
    $fmtBerat = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
@endphp

<div class="lp lp--narrow">

    <div class="lp-head lp-noprint">
        <div>
            <a href="{{ route('admin.laporan.index') }}" class="lp-back">&larr; Kembali ke riwayat</a>
            <h1 class="lp-title">Detail transaksi</h1>
        </div>
        <div class="lp-head__actions">
            <button type="button" class="lp-btn" onclick="window.print()">Cetak</button>
            <a href="{{ route('admin.laporan.edit', $transaksi) }}" class="lp-btn">Ubah</a>
            <form method="POST" action="{{ route('admin.laporan.destroy', $transaksi) }}"
                  onsubmit="return confirm('Hapus transaksi {{ $transaksi->kode }}? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="lp-btn lp-btn--danger">Hapus</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="lp-alert lp-alert--success lp-noprint" role="status">{{ session('success') }}</div>
    @endif

    {{-- Informasi transaksi --}}
    <div class="lp-card lp-card__pad">
        <div class="lp-card__meta">
            <p class="lp-kode">{{ $transaksi->kode }}</p>
            <div class="lp-badges">
                <span class="lp-badge {{ $transaksi->jenis_badge }}">{{ $transaksi->jenis_label }}</span>
                <span class="lp-badge {{ $transaksi->status_badge }}">{{ $transaksi->status_label }}</span>
            </div>
        </div>

        <dl class="lp-dl">
            <div>
                <dt>Tanggal transaksi</dt>
                <dd>{{ $transaksi->tanggal->locale('id')->translatedFormat('l, d F Y') }}</dd>
            </div>
            <div>
                <dt>Warga</dt>
                <dd>{{ $transaksi->nama_warga }}</dd>
            </div>
            <div>
                <dt>Dibuat</dt>
                <dd class="lp-plain">{{ $transaksi->created_at->format('d/m/Y, H:i') }}</dd>
            </div>
            <div>
                <dt>Terakhir diperbarui</dt>
                <dd class="lp-plain">{{ $transaksi->updated_at->format('d/m/Y, H:i') }}</dd>
            </div>
            <div class="lp-dl__full">
                <dt>Catatan</dt>
                <dd class="lp-plain">{{ $transaksi->keterangan ?: 'Tidak ada catatan.' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Rincian --}}
    @if ($transaksi->jenis === 'pencairan')
        <div class="lp-card lp-card__pad">
            <p class="lp-stat__label">Nominal pencairan</p>
            <p class="lp-big">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</p>
        </div>
    @else
        <div class="lp-card lp-card--clip">
            <div class="lp-card__head">
                <h2 class="lp-card__title">Rincian sampah</h2>
            </div>
            <div class="lp-table-wrap">
                <table class="lp-table">
                    <thead>
                        <tr>
                            <th>Jenis sampah</th>
                            <th class="lp-num">Berat</th>
                            <th class="lp-num">Harga per kg</th>
                            <th class="lp-num">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksi->items as $item)
                            <tr>
                                <td>{{ $item->nama_item }}</td>
                                <td class="lp-num">{{ $fmtBerat($item->berat) }} kg</td>
                                <td class="lp-num">Rp {{ number_format($item->harga_per_kg, 0, ',', '.') }}</td>
                                <td class="lp-num"><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="lp-empty">Transaksi ini belum punya rincian item.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td class="lp-num">{{ $fmtBerat($transaksi->items->sum('berat')) }} kg</td>
                            <td></td>
                            <td class="lp-num">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection