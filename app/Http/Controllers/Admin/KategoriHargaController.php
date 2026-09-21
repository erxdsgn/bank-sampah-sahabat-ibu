<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaSampah;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KategoriHargaController extends Controller
{
    public function index()
    {
        $kategoriInduk = KategoriSampah::whereNull('id_induk')
            ->with([
                'hargaTerbaru',
                'hargaSampah' => fn ($q) => $q->latest('tanggal_berlaku'),
                'anak.hargaTerbaru',
                'anak.hargaSampah' => fn ($q) => $q->latest('tanggal_berlaku'),
            ])
            ->orderBy('nama_kategori')
            ->get();

        return view('admin.pages.kategori-harga', compact('kategoriInduk'));
    }

    /**
     * Simpan kategori baru sekaligus harga awalnya (satu form, satu submit).
     * Satuan selalu "gram", tidak perlu diisi manual.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_induk'        => ['nullable', 'exists:kategori_sampah,id_kategori'],
            'nama_kategori'   => ['required', 'string', 'max:100'],
            'harga_per_gram'  => ['required', 'numeric', 'min:0'],
            'tanggal_berlaku' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $kategori = DB::transaction(function () use ($data) {
            $kategori = KategoriSampah::create([
                'id_induk'      => $data['id_induk'] ?? null,
                'nama_kategori' => $data['nama_kategori'],
                'satuan'        => 'gram',
            ]);

            HargaSampah::create([
                'id_kategori'     => $kategori->id_kategori,
                'id_admin'        => Auth::id(),
                'harga_per_gram'  => $data['harga_per_gram'],
                'tanggal_berlaku' => $data['tanggal_berlaku'],
            ]);

            return $kategori;
        });

        return response()->json([
            'message' => 'Kategori dan harga awal berhasil ditambahkan.',
            'data'    => $kategori->load('hargaTerbaru'),
        ]);
    }

    /**
     * Perbarui kategori. Satuan tetap dipaksa "gram".
     */
    public function update(Request $request, KategoriSampah $kategoriSampah)
    {
        $validator = Validator::make($request->all(), [
            'id_induk'      => ['nullable', 'exists:kategori_sampah,id_kategori'],
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('id_induk') && (int) $request->id_induk === (int) $kategoriSampah->id_kategori) {
            return response()->json([
                'errors' => ['id_induk' => ['Kategori tidak boleh menjadi induk dari dirinya sendiri.']],
            ], 422);
        }

        $kategoriSampah->update([
            'id_induk'      => $request->id_induk ?: null,
            'nama_kategori' => $request->nama_kategori,
            'satuan'        => 'gram',
        ]);

        return response()->json([
            'message' => 'Perubahan kategori berhasil disimpan.',
            'data'    => $kategoriSampah,
        ]);
    }

    public function destroy(KategoriSampah $kategoriSampah)
    {
        if ($kategoriSampah->anak()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki sub-kategori.',
            ], 422);
        }

        $kategoriSampah->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }

    /**
     * Tambah harga baru untuk kategori yang sudah ada (dipakai modal "Kelola Harga").
     */
    public function hargaStore(Request $request, KategoriSampah $kategoriSampah)
    {
        $validator = Validator::make($request->all(), [
            'harga_per_gram'  => ['required', 'numeric', 'min:0'],
            'tanggal_berlaku' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $harga = HargaSampah::create([
            'id_kategori'     => $kategoriSampah->id_kategori,
            'id_admin'        => Auth::id(),
            'harga_per_gram'  => $request->harga_per_gram,
            'tanggal_berlaku' => $request->tanggal_berlaku,
        ]);

        return response()->json([
            'message' => 'Harga baru berhasil ditambahkan.',
            'data'    => $harga,
        ]);
    }

    public function hargaDestroy(KategoriSampah $kategoriSampah, HargaSampah $hargaSampah)
    {
        if ($hargaSampah->id_kategori !== $kategoriSampah->id_kategori) {
            return response()->json(['message' => 'Data harga tidak ditemukan.'], 404);
        }

        $hargaSampah->delete();

        return response()->json(['message' => 'Data harga berhasil dihapus.']);
    }
}
