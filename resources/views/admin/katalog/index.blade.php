@extends('admin.layouts.app')

@section('title', 'Katalog Barang')
@section('active', 'katalog')
@section('crumbs', 'Keuangan & Produk > Katalog Barang')

@section('content')

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    <section class="hero" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div class="hero-text">
            <span class="eyebrow" style="font-size: 11px; font-weight: 700; color: #9ca3af; letter-spacing: 0.05em; text-transform: uppercase;">
                KEUANGAN & PRODUK
            </span>
            <h1 class="hero-title" style="font-size: 32px; font-weight: 800; color: #1f2937; margin: 4px 0 8px; line-height: 1.2;">
                Katalog <span class="accent" style="color: #24a86b;">Barang</span>
            </h1>
            <p class="hero-sub" style="margin: 0; font-size: 14px; color: #6b7280;">
                Kelola informasi produk hasil daur ulang Bank Sampah yang ditawarkan ke masyarakat.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.katalog.create') }}" class="btn-tambah" style="display: inline-flex; align-items: center; gap: 8px; background-color: #24a86b; color: #ffffff; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: background-color 0.2s;">
                <span style="font-size: 16px; font-weight: 700;">+</span> Tambah Produk
            </a>
        </div>
    </section>

    <section class="card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden;">
        <div class="catalog-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; padding: 24px; background: #f8fafc;">

            @forelse($katalogProduk as $produk)
                <div class="catalog-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">

                    <div class="catalog-img-container" style="width: 100%; height: 180px; background: #edf2f7; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        @if($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama_barang }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="color: #a0aec0; font-size: 13px; font-weight: 500;">📸 Tidak ada foto</div>
                        @endif
                    </div>

                    <div class="catalog-content" style="padding: 16px; display: flex; flex-direction: column; flex: 1;">
                        <h3 style="font-size: 15px; font-weight: 700; color: #1f2937; margin: 0 0 6px;">
                            {{ $produk->nama_barang }}
                        </h3>
                        <div style="font-size: 15px; color: #24a86b; font-weight: 700; margin-bottom: 4px;">
                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                        </div>
                        <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px; flex: 1;">
                            Stok: {{ $produk->stok }}
                        </p>

                        <div style="display: flex; gap: 8px; border-top: 1px solid #e5e7eb; padding-top: 12px;">
                            <a href="{{ route('admin.katalog.show', $produk->id) }}" style="flex: 1; padding: 6px; background: #e0f2fe; color: #0284c7; border-radius: 4px; font-size: 12px; font-weight: 600; text-align: center; text-decoration: none;">
                                Detail
                            </a>
                            <a href="{{ route('admin.katalog.edit', $produk->id) }}" style="flex: 1; padding: 6px; background: #f3f4f6; color: #374151; border-radius: 4px; font-size: 12px; font-weight: 600; text-align: center; text-decoration: none;">
                                Edit
                            </a>
                            <form action="{{ route('admin.katalog.destroy', $produk->id) }}" method="POST" style="flex: 1; display:flex;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="width: 100%; padding: 6px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <div style="font-size: 36px; margin-bottom: 12px;">♻️</div>
                    <strong style="font-size: 16px; color: #1f2937;">Belum Ada Katalog Produk Daur Ulang</strong>
                    <p style="margin: 6px 0 0; color: #6b7280; font-size: 13px;">
                        Silakan klik tombol "Tambah Produk" di kanan atas untuk memasukkan data barang daur ulang.
                    </p>
                </div>
            @endforelse

        </div>
    </section>

    <style>
        .btn-tambah:hover { background-color: #1e8f5a !important; }
        .catalog-item:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
    </style>
@endsection
