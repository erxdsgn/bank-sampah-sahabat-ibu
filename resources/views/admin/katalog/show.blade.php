@extends('admin.layouts.app')

@section('title', 'Detail Produk')
@section('content')
<div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; gap: 24px;">
    <div style="flex: 1;">
        @if($produk->gambar)
            <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama_barang }}" style="width: 100%; border-radius: 8px;">
        @else
            <div style="width: 100%; height: 200px; background: #edf2f7; display: flex; align-items: center; justify-content: center; border-radius: 8px; color: #a0aec0;">Tidak ada foto</div>
        @endif
    </div>

    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
        <h2 style="font-size: 24px; font-weight: 800; color: #1f2937; margin: 0 0 12px;">{{ $produk->nama_barang }}</h2>
        <div style="font-size: 20px; color: #24a86b; font-weight: 700; margin-bottom: 12px;">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>
        <div style="font-size: 16px; color: #4b5563; margin-bottom: 24px;">Stok Tersedia: <strong>{{ $produk->stok }}</strong></div>

        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.katalog.edit', $produk->id) }}" style="background: #f59e0b; color: #fff; padding: 8px 16px; border-radius: 6px; font-weight: 600; text-decoration: none; text-align: center; flex: 1;">Edit</a>
            <a href="{{ route('admin.katalog.index') }}" style="background: #f3f4f6; color: #374151; padding: 8px 16px; border-radius: 6px; font-weight: 600; text-decoration: none; text-align: center; flex: 1;">Kembali</a>
        </div>
    </div>
</div>
@endsection
