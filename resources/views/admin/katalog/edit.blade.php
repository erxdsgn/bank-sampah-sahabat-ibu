@extends('admin.layouts.app')

@section('title', 'Edit Produk')
@section('content')
<div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb;">
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 20px;">Edit Produk: {{ $produk->nama_barang }}</h2>

    <form action="{{ route('admin.katalog.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Foto Produk (Biarkan kosong jika tidak ingin mengubah)</label>
            @if($produk->gambar)
                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="Preview" style="height: 100px; margin-bottom: 8px; border-radius: 6px;">
            @endif
            <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('gambar') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Nama Barang</label>
            <input type="text" name="nama_barang" value="{{ old('nama_barang', $produk->nama_barang) }}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('nama_barang') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Harga (Rp)</label>
            <input type="number" name="harga" value="{{ old('harga', $produk->harga) }}" min="0" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('harga') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Stok</label>
            <input type="number" name="stok" value="{{ old('stok', $produk->stok) }}" min="0" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            @error('stok') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" style="background: #24a86b; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Update</button>
            <a href="{{ route('admin.katalog.index') }}" style="background: #f3f4f6; color: #374151; padding: 10px 20px; border-radius: 6px; font-weight: 600; text-decoration: none;">Batal</a>
        </div>
    </form>
</div>
@endsection
