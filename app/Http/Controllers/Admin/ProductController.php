<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $katalogProduk = Product::orderByDesc('tanggal_upload')
            ->orderByDesc('id_produk')
            ->get();

        return view('admin.pages.katalog', compact('katalogProduk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:80|regex:/^[a-zA-Z\s]+$/',
            'harga'       => 'required|numeric|min:1|max:500000',
            'stok'        => 'required|integer|min:1|max:500',
            'deskripsi'   => 'nullable|string|max:500',
            // Menggunakan mimes agar menerima HEIC jika JS gagal mengonversi
            'foto'        => 'required|file|mimes:jpeg,png,jpg,webp,heic,heif|max:2048',
        ], [
            'nama_produk.regex' => 'Nama produk hanya boleh berisi huruf dan spasi.',
            'deskripsi.max'     => 'Deskripsi maksimal 500 karakter.',
            'harga.max'         => 'Harga produk maksimal Rp 500.000.',
            'stok.max'          => 'Stok produk maksimal 500 unit.',
        ]);

        $fotoPath = $request->file('foto')->store('products', 'public');

        $produk = Product::create([
            'id_admin'       => Auth::id() ?? 1,
            'nama_produk'    => $request->nama_produk,
            'harga'          => $request->harga,
            'stok'           => $request->stok,
            'deskripsi'      => $request->deskripsi,
            'foto'           => $fotoPath,
            'tanggal_upload' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => $produk,
        ], 201);
    }

    public function show($id)
    {
        $produk = Product::findOrFail($id);
        return response()->json($produk);
    }

    public function update(Request $request, $id)
    {
        $produk = Product::findOrFail($id);

        $request->validate([
            'nama_produk' => 'required|string|max:80|regex:/^[a-zA-Z\s]+$/',
            'harga'       => 'required|numeric|min:1|max:500000',
            'stok'        => 'required|integer|min:1|max:500',
            'deskripsi'   => 'nullable|string|max:500',
            'foto'        => 'nullable|file|mimes:jpeg,png,jpg,webp,heic,heif|max:2048',
        ], [
            'nama_produk.regex' => 'Nama produk hanya boleh berisi huruf dan spasi.',
            'deskripsi.max'     => 'Deskripsi maksimal 500 karakter.',
            'harga.max'         => 'Harga produk maksimal Rp 500.000.',
            'stok.max'          => 'Stok produk maksimal 500 unit.',
        ]);

        $data = [
            'nama_produk' => $request->nama_produk,
            'harga'       => $request->harga,
            'stok'        => $request->stok,
            'deskripsi'   => $request->deskripsi,
        ];

        if ($request->hasFile('foto')) {
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }
            $data['foto'] = $request->file('foto')->store('products', 'public');
        }

        $produk->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data'    => $produk->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $produk = Product::findOrFail($id);

        if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
            Storage::disk('public')->delete($produk->foto);
        }

        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
