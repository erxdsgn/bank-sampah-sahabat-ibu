<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengaduanApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $items = Pengaduan::where('user_id', $user->id)
            ->orderByDesc('id_pengaduan')
            ->get()
            ->map(function (Pengaduan $item) {
                return [
                    'id'              => $item->id_pengaduan,
                    'kategori'        => $item->kategori,
                    'kategori_label'  => $item->kategori_label,
                    'judul'           => $item->judul,
                    'deskripsi'       => $item->deskripsi,
                    'foto_url'        => $item->foto_url,
                    'status'          => $item->status,
                    'status_label'    => $item->status_label,
                    'tanggapan_admin' => $item->tanggapan_admin,
                    'ditanggapi_at'   => optional($item->ditanggapi_at)->toIso8601String(),
                    'created_at'      => optional($item->created_at)->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kategori'  => ['required', 'string', 'in:layanan_petugas,jadwal_lokasi,jenis_harga,kendala_aplikasi,saran_masukan,lainnya'],
                'judul'     => ['required', 'string', 'max:120'],
                'deskripsi' => ['required', 'string', 'max:2000'],
                'foto'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            ],
            [
                'kategori.required'  => 'Kategori pengaduan wajib dipilih.',
                'kategori.in'        => 'Kategori pengaduan tidak valid.',
                'judul.required'     => 'Judul laporan wajib diisi.',
                'judul.max'        => 'Judul maksimal 120 karakter.',
                'deskripsi.required' => 'Detail pengaduan wajib diisi.',
                'deskripsi.max'    => 'Detail pengaduan maksimal 2000 karakter.',
                'foto.image'         => 'File lampiran harus berupa gambar.',
                'foto.mimes'         => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
                'foto.max'           => 'Ukuran foto maksimal 3 MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pengaduan', 'public');
        }

        $user = $request->user();
        $warga = $user->warga;

        $pengaduan = Pengaduan::create([
            'user_id'   => $user->id,
            'id_warga'  => $warga->id_warga ?? null,
            'kategori'  => $request->kategori,
            'judul'     => trim($request->judul),
            'deskripsi' => trim($request->deskripsi),
            'foto_bukti'=> $fotoPath,
            'status'    => 'menunggu',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Pengaduan berhasil dikirim.',
            'data'    => [
                'id'              => $pengaduan->id_pengaduan,
                'kategori'        => $pengaduan->kategori,
                'kategori_label'  => $pengaduan->kategori_label,
                'judul'           => $pengaduan->judul,
                'deskripsi'       => $pengaduan->deskripsi,
                'foto_url'        => $pengaduan->foto_url,
                'status'          => $pengaduan->status,
                'status_label'    => $pengaduan->status_label,
                'tanggapan_admin' => $pengaduan->tanggapan_admin,
                'ditanggapi_at'   => null,
                'created_at'      => optional($pengaduan->created_at)->toIso8601String(),
            ],
        ], 201);
    }
}
