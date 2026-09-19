@extends('admin.layouts.app')

@section('title', 'Tambah Produk')
@section('content')
<div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb;">
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 20px;">Tambah Produk Baru</h2>

    <form action="{{ route('admin.katalog.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Foto Produk</label>
            <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('gambar') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Nama Barang</label>
            <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('nama_barang') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Harga (Rp)</label>
            <input type="number" name="harga" value="{{ old('harga') }}" min="0" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('harga') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Stok</label>
            <input type="number" name="stok" value="{{ old('stok') }}" min="0" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('stok') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" style="background: #24a86b; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Simpan</button>
            <a href="{{ route('admin.katalog.index') }}" style="background: #f3f4f6; color: #374151; padding: 10px 20px; border-radius: 6px; font-weight: 600; text-decoration: none;">Batal</a>
        </div>
    </form>
</div>
@endsection
