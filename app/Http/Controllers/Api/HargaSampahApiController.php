<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;

class HargaSampahApiController extends Controller
{
    /**
     * Menampilkan daftar harga sampah untuk halaman publik/mobile app.
     * Mendukung fitur pencarian berdasarkan nama kategori (contoh: ?search=aluminium).
     */
    public function index(Request $request)
    {
        $query = KategoriSampah::with('hargaTerbaru');

        // Fitur Pencarian (Search) sesuai input di UI Mobile
        if ($request->has('search') && !empty($request->search)) {
            $keyword = $request->search;
            $query->where('nama_kategori', 'like', "%{$keyword}%");
        }

        $kategori = $query->orderBy('id_kategori', 'asc')
            ->get()
            ->map(function ($item) {
                // Ambil harga dari relasi 'hargaTerbaru', atau dari kolom 'harga_satuan' pada model KategoriSampah
                $harga = 0;
                if ($item->hargaTerbaru) {
                    $harga = $item->hargaTerbaru->harga_satuan
                          ?? $item->hargaTerbaru->harga
                          ?? $item->hargaTerbaru->nominal
                          ?? 0;
                } else {
                    $harga = $item->harga_satuan ?? 0;
                }

                return [
                    'id_kategori'     => $item->id_kategori,
                    'nama_kategori'   => $item->nama_kategori,
                    'satuan'          => $item->satuan,
                    'harga_satuan'    => (float) $harga,
                    'tanggal_berlaku' => $item->hargaTerbaru ? $item->hargaTerbaru->tanggal_berlaku : null,
                ];
            });

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil data harga sampah',
            'data'    => $kategori
        ], 200);
    }
}
