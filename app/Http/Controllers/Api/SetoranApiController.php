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
     * Mengambil seluruh daftar kategori untuk pilihan dropdown di aplikasi mobile.
     */
    public function getKategori()
    {
        $kategori = KategoriSampah::with('hargaTerbaru')
            ->orderBy('id_kategori', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id_kategori'   => (int) $item->id_kategori,
                    'nama_kategori' => $item->nama_kategori ?? '-',
                    'satuan'        => $item->satuan_normal ?: 'kg',
                    'harga_satuan'  => (float) ($item->harga_satuan_aktual ?? 0),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $kategori
        ], 200);
    }

    /**
     * 2. POST PENGAJUAN SETOR SAMPAH (OTOMATIS OTENTIKASI KE WARGA LOGIN)
     * Warga cukup memilih ID Kategori & Jumlah. Sistem otomatis mengaitkannya ke id_warga pengirim.
     */
    public function store(Request $request)
    {
        // A. Handle payload `items` jika dikirim sebagai string JSON via Multipart Form-Data
        $itemsData = $request->items;
        if (is_string($itemsData)) {
            $itemsData = json_decode($itemsData, true);
        }

        // B. Validasi Input
        $validator = Validator::make([
            'foto_sampah' => $request->file('foto_sampah'),
            'items'       => $itemsData,
        ], [
            'foto_sampah'         => 'nullable|image|mimes:jpeg,png,jpg,heic|max:5120',
            'items'               => 'required|array|min:1',
            'items.*.id_kategori' => 'required|exists:kategori_sampah,id_kategori',
            'items.*.jumlah'      => 'required|numeric|gt:0',
        ], [
            'items.required'               => 'Minimal masukkan 1 jenis sampah.',
            'items.*.id_kategori.required' => 'Kategori sampah wajib dipilih.',
            'items.*.id_kategori.exists'   => 'Kategori sampah tidak valid.',
            'items.*.jumlah.required'      => 'Jumlah sampah wajib diisi.',
            'items.*.jumlah.gt'            => 'Jumlah sampah harus lebih besar dari 0.',
            'foto_sampah.image'            => 'File foto bukti harus berupa gambar.',
            'foto_sampah.max'              => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // C. Identifikasi Warga dari Token Sanctum yang sedang aktif
        $user = $request->user();

        if (!$user || !$user->warga) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Profil warga tidak ditemukan atau Anda tidak memiliki akses warga.',
            ], 403);
        }

        try {
            $setoran = DB::transaction(function () use ($request, $user, $itemsData) {
                // 1. Upload foto bukti sampah jika dilampirkan dari kamera/galeri mobile
                $fotoPath = null;
                if ($request->hasFile('foto_sampah')) {
                    $fotoPath = $request->file('foto_sampah')->store('setoran', 'public');
                }

                // 2. Buat Header Setoran (Otomatis dikaitkan ke id_warga milik pengirim)
                $setoran = Setoran::create([
                    'id_warga'        => $user->warga->id_warga, // OTOMATIS AMBIL ID WARGA SESEORANG YANG LOGIN
                    'id_admin'        => null,                     // Belum diverifikasi admin
                    'tanggal_setoran' => now()->toDateString(),
                    'status'          => 'pending',                // Status awal: Menunggu Verifikasi Admin
                    'total_berat'     => 0,
                    'total_nilai'     => 0,
                    'foto_sampah'     => $fotoPath,
                ]);

                $totalSemuaNilai = 0;
                $totalBeratGram = 0;

                // 3. Simpan Rincian Sampah & Hitung Subtotal Harga
                foreach ($itemsData as $item) {
                    $kategori = KategoriSampah::with('hargaTerbaru')->findOrFail($item['id_kategori']);
                    $jumlah = (float) $item['jumlah'];
                    $hargaSatuan = (float) $kategori->harga_satuan_aktual;
                    $subtotal = round($jumlah * $hargaSatuan, 2);
                    $satuan = $kategori->satuan_normal;

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
                        $totalGram = $totalBeratGram += ($jumlah * 1000);
                    } elseif ($satuan === 'gram') {
                        $totalGram = $totalBeratGram += $jumlah;
                    }
                }

                // 4. Update Akumulasi Nilai Rupiah & Berat pada Header Transaksi
                $setoran->update([
                    'total_berat' => $totalBeratGram > 0 ? $totalBeratGram : null,
                    'total_nilai' => $totalSemuaNilai,
                ]);

                return $setoran;
            });

            // 5. Kembalikan Response Sukses Lengkap dengan Data Warga & Rincian Sampah
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
     * Mengambil riwayat setoran khusus untuk warga yang sedang login.
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
