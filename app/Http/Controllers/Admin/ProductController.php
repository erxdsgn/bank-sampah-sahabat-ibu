<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Menampilkan katalog produk
     */
    public function index()
    {
        $katalogProduk = Product::orderByDesc('tanggal_upload')
            ->orderByDesc('id_produk')
            ->get();

        return view('admin.pages.katalog', compact('katalogProduk'));
    }


    /**
     * Menyimpan produk baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:80|regex:/^[a-zA-Z\s]+$/',
            'harga'       => 'required|numeric|min:1|max:500000',
            'stok'        => 'required|integer|min:1|max:500',
            'foto'        => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            // Kustom pesan error agar lebih komunikatif (opsional)
            'nama_produk.regex' => 'Nama produk hanya boleh berisi huruf dan spasi.',
            'harga.max'         => 'Harga produk maksimal Rp 500.000.',
            'stok.max'          => 'Stok produk maksimal 500 unit.',
        ]);

        // Simpan foto ke storage/app/public/products
        $fotoPath = $request->file('foto')->store('products', 'public');

        // Simpan data produk
        $produk = Product::create([
            'id_admin'       => Auth::id() ?? 1,
            'nama_produk'    => $request->nama_produk,
            'harga'          => $request->harga,
            'stok'           => $request->stok,
            'foto'           => $fotoPath,
            'tanggal_upload' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => $produk,
        ], 201);
    }


    /**
     * Menampilkan detail produk
     */
    public function show($id)
    {
        $produk = Product::findOrFail($id);

        return response()->json($produk);
    }


    /**
     * Mengubah produk
     */
    public function update(Request $request, $id)
    {
        $produk = Product::findOrFail($id);

        $request->validate([
            'nama_produk' => 'required|string|max:80|regex:/^[a-zA-Z\s]+$/',
            'harga'       => 'required|numeric|min:1|max:500000',
            'stok'        => 'required|integer|min:1|max:500',
            'foto'        => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nama_produk.regex' => 'Nama produk hanya boleh berisi huruf dan spasi.',
            'harga.max'         => 'Harga produk maksimal Rp 500.000.',
            'stok.max'          => 'Stok produk maksimal 500 unit.',
        ]);

        $data = [
            'nama_produk' => $request->nama_produk,
            'harga'       => $request->harga,
            'stok'        => $request->stok,
        ];

        // Jika user mengganti foto
        if ($request->hasFile('foto')) {

            // Hapus foto lama
            if (
                $produk->foto &&
                Storage::disk('public')->exists($produk->foto)
            ) {
                Storage::disk('public')->delete($produk->foto);
            }

            // Simpan foto baru
            $data['foto'] = $request->file('foto')
                ->store('products', 'public');
        }

        $produk->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data'    => $produk->fresh(),
        ]);
    }


    /**
     * Menghapus produk
     */
    public function destroy($id)
    {
        $produk = Product::findOrFail($id);

        // Hapus foto dari storage
        if (
            $produk->foto &&
            Storage::disk('public')->exists($produk->foto)
        ) {
            Storage::disk('public')->delete($produk->foto);
        }

        // Hapus data produk
        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
