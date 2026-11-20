<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setoran;
use App\Models\DetailSetoran;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SetoranApiController extends Controller
{
    /**
     * 1. GET DAFTAR KATEGORI SAMPAH & HARGA AKTIF
     */
    public function getKategori()
    {
        $kategori = KategoriSampah::with('hargaTerbaru')
            ->orderBy('id_kategori', 'asc')
            ->get()
            ->map(function ($item) {
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
                    'id_kategori'   => (int) $item->id_kategori,
                    'nama_kategori' => $item->nama_kategori ?? '-',
                    'satuan'        => $item->satuan ?: 'kg', // PERBAIKAN: Gunakan $item->satuan
                    'harga_satuan'  => (float) $harga,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $kategori
        ], 200);
    }

    /**
     * 2. POST PENGAJUAN SETOR SAMPAH
     */
    public function store(Request $request)
    {
        $itemsData = $request->items;
        if (is_string($itemsData)) {
            $itemsData = json_decode($itemsData, true);
        }

        // Validasi Input (Termasuk Batas Maksimal & Foto 2MB)
        $validator = Validator::make([
            'foto_sampah' => $request->file('foto_sampah'),
            'items'       => $itemsData,
        ], [
            'foto_sampah'         => 'nullable|image|mimes:jpeg,png,jpg,heic|max:2048', // Max 2 MB
            'items'               => 'required|array|min:1',
            'items.*.id_kategori' => 'required|exists:kategori_sampah,id_kategori',
            'items.*.jumlah'      => 'required|numeric|gt:0|max:50', // Max 50 per item
        ], [
            'items.required'               => 'Minimal masukkan 1 jenis sampah.',
            'items.*.id_kategori.required' => 'Kategori sampah wajib dipilih.',
            'items.*.id_kategori.exists'   => 'Kategori sampah tidak valid.',
            'items.*.jumlah.required'      => 'Jumlah sampah wajib diisi.',
            'items.*.jumlah.gt'            => 'Jumlah sampah harus lebih besar dari 0.',
            'items.*.jumlah.max'           => 'Maksimal setoran untuk satu jenis sampah adalah 50.',
            'foto_sampah.image'            => 'File foto bukti harus berupa gambar.',
            'foto_sampah.max'              => 'Ukuran foto maksimal 2 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Validasi Tambahan: Total Keseluruhan (Maks 100)
        $totalJumlah = array_sum(array_column($itemsData, 'jumlah'));
        if ($totalJumlah > 100) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Total keseluruhan sampah dalam sekali setor maksimal 100 kg/liter/pcs.',
            ], 422);
        }

        // Identifikasi Warga
        $user = $request->user();
        if (!$user || !$user->warga) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Profil warga tidak ditemukan atau Anda tidak memiliki akses warga.',
            ], 403);
        }

        try {
            $setoran = DB::transaction(function () use ($request, $user, $itemsData) {
                $fotoPath = null;
                if ($request->hasFile('foto_sampah')) {
                    $fotoPath = $request->file('foto_sampah')->store('setoran', 'public');
                }

                $setoran = Setoran::create([
                    'id_warga'        => $user->warga->id_warga,
                    'id_admin'        => null,
                    'tanggal_setoran' => now()->toDateString(),
                    'status'          => 'pending',
                    'total_berat'     => 0,
                    'total_nilai'     => 0,
                    'foto_sampah'     => $fotoPath,
                ]);

                $totalSemuaNilai = 0;
                $totalBeratGram = 0;

                foreach ($itemsData as $item) {
                    $kategori = KategoriSampah::with('hargaTerbaru')->findOrFail($item['id_kategori']);
                    $jumlah = (float) $item['jumlah'];
                    $hargaSatuan = (float) $kategori->harga_satuan_aktual;
                    $subtotal = round($jumlah * $hargaSatuan, 2);
                    $satuan = $kategori->satuan; // PERBAIKAN: Gunakan $kategori->satuan

                    DetailSetoran::create([
                        'id_setoran'     => $setoran->id_setoran,
                        'id_kategori'    => $kategori->id_kategori,
                        'jumlah'         => $jumlah,
                        'satuan'         => $satuan,
                        'harga_per_gram' => $hargaSatuan,
                        'subtotal'       => $subtotal,
                        'berat_gram'     => ($satuan === 'kg' || $satuan === 'liter') ? ($jumlah * 1000) : $jumlah,
                    ]);

                    $totalSemuaNilai += $subtotal;

                    if ($satuan === 'kg') {
                        $totalBeratGram += ($jumlah * 1000);
                    } elseif ($satuan === 'gram') {
                        $totalBeratGram += $jumlah;
                    }
                }

                $setoran->update([
                    'total_berat' => $totalBeratGram > 0 ? $totalBeratGram : null,
                    'total_nilai' => $totalSemuaNilai,
                ]);

                return $setoran;
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan setor sampah berhasil dikirim!',
                'data'    => $setoran->load([
                    'warga:id_warga,nama,nik',
                    'details.kategori:id_kategori,nama_kategori,satuan'
                ]),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan transaksi setoran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3. GET RIWAYAT SETORAN SAYA
     */
    public function riwayat(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->warga) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Profil warga tidak ditemukan.',
            ], 403);
        }

        $riwayat = Setoran::with(['details.kategori'])
            ->where('id_warga', $user->warga->id_warga)
            ->orderByDesc('tanggal_setoran')
            ->orderByDesc('id_setoran')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $riwayat,
        ], 200);
    }
}
