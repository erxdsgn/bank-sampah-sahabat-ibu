<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * Menampilkan daftar produk daur ulang untuk aplikasi mobile (Warga)
     */
    public function index()
    {
        $katalogProduk = Product::orderByDesc('tanggal_upload')
            ->orderByDesc('id_produk')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar katalog produk berhasil diambil.',
            'data'    => $katalogProduk
        ], 200);
    }

    /**
     * Menampilkan detail satu produk berdasarkan id_produk
     */
    public function show($id)
    {
        $produk = Product::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data'    => $produk
        ], 200);
    }
}
