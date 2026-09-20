<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaSampah;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;

class HargaSampahController extends Controller
{
    public function index(KategoriSampah $kategoriSampah) {

        $riwayat = $kategoriSampah->hargaSampah()->orderByDesc('tanggal_berlaku')->get();
        return view('admin.pages.kategori-sampah.harga', compact('kategoriSampah', 'riwayat'));
    }



    public function store(Request $request, KategoriSampah $kategoriSampah) {

        $validated = $request->validate([
            'harga_per_kg' => 'required|numeric|min:0',
            'tanggal_berlaku' => 'required|date',
        ]);

        HargaSampah::create([
            'id_kategori' => $kategoriSampah->id_kategori,
            'id_admin' => 1,
            'harga_per_kg' => $validated['harga_per_kg'],
            'tanggal_berlaku' => $validated['tanggal_berlaku'],
        ]);

        return back()->with('success', 'Harga sampah berhasil diperbarui.');
    }



    public function destroy(Request $request, KategoriSampah $kategoriSampah, HargaSampah $hargaSampah) {


        $hargaSampah->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data harga berhasil dihapus.',
            ]);
        }
        return redirect()->route('admin.kategori-sampah.harga.index', $kategoriSampah->id_kategori)->with('success', 'Data harga dihapus.');
    }
}
