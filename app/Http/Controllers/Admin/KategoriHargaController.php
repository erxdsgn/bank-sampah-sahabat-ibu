<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaSampah;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KategoriHargaController extends Controller
{
    /**
     * Menampilkan halaman Kategori & Harga.
     */
    public function index()
    {
        $kategoriInduk = KategoriSampah::whereNull('id_induk')
            ->with([
                'hargaTerbaru',
                'hargaSampah' => fn ($q) =>
                    $q->latest('tanggal_berlaku'),

                'anak.hargaTerbaru',
                'anak.hargaSampah' => fn ($q) =>
                    $q->latest('tanggal_berlaku'),
            ])
            ->orderByDesc('id_kategori')
            ->get();

        return view(
            'admin.pages.kategori-harga',
            compact('kategoriInduk')
        );
    }

    /**
     * Simpan kategori baru sekaligus harga awal.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_induk' => [
                    'nullable',
                    'exists:kategori_sampah,id_kategori',
                ],

                'nama_kategori' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-Z\s]+$/',
                    'unique:kategori_sampah,nama_kategori',
                ],

                'satuan' => [
                    'required',
                    Rule::in([
                        'gram',
                        'kg',
                        'pcs',
                        'liter',
                        'unit',
                        'set',
                    ]),
                ],

                'harga_satuan' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:9999999999',
                ],

                'tanggal_berlaku' => [
                    'required',
                    'date',
                ],
            ],
            [
                'nama_kategori.regex' =>
                    'Nama kategori hanya boleh berisi huruf dan spasi.',

                'nama_kategori.unique' =>
                    'Nama kategori sudah ada, gunakan nama lain.',

                'satuan.in' =>
                    'Satuan harus salah satu dari: gram, kg, pcs, liter, unit, atau set.',

                'harga_satuan.numeric' =>
                    'Harga hanya boleh berupa angka.',

                'harga_satuan.min' =>
                    'Harga tidak boleh bernilai negatif.',

                'harga_satuan.max' =>
                    'Harga terlalu besar.',

                'tanggal_berlaku.required' =>
                    'Tanggal berlaku wajib diisi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $kategori = DB::transaction(function () use ($data) {

            $kategori = KategoriSampah::create([
                'id_induk'      => $data['id_induk'] ?? null,
                'nama_kategori' => trim($data['nama_kategori']),
                'satuan'        => $data['satuan'],
            ]);

            HargaSampah::create([
                'id_kategori'     => $kategori->id_kategori,
                'id_admin'        => Auth::id(),
                'harga_satuan'    => $data['harga_satuan'],
                'tanggal_berlaku' => $data['tanggal_berlaku'],
            ]);

            return $kategori;
        });

        return response()->json([
            'message' =>
                'Kategori dan harga awal berhasil ditambahkan.',

            'data' =>
                $kategori->load('hargaTerbaru'),
        ]);
    }

    /**
     * Perbarui kategori.
     */
    public function update(
        Request $request,
        KategoriSampah $kategoriSampah
    ) {
        $validator = Validator::make(
            $request->all(),
            [
                'id_induk' => [
                    'nullable',
                    'exists:kategori_sampah,id_kategori',
                ],

                'nama_kategori' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-Z\s]+$/',

                    Rule::unique(
                        'kategori_sampah',
                        'nama_kategori'
                    )->ignore(
                        $kategoriSampah->id_kategori,
                        'id_kategori'
                    ),
                ],

                'satuan' => [
                    'required',
                    Rule::in([
                        'gram',
                        'kg',
                        'pcs',
                        'liter',
                        'unit',
                        'set',
                    ]),
                ],
            ],
            [
                'nama_kategori.regex' =>
                    'Nama kategori hanya boleh berisi huruf dan spasi.',

                'nama_kategori.unique' =>
                    'Nama kategori sudah digunakan.',

                'satuan.in' =>
                    'Satuan harus salah satu dari: gram, kg, pcs, liter, unit, atau set.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Kategori tidak boleh menjadi induknya sendiri.
         */
        if (
            $request->filled('id_induk') &&
            (int) $request->id_induk ===
            (int) $kategoriSampah->id_kategori
        ) {
            return response()->json([
                'errors' => [
                    'id_induk' => [
                        'Kategori tidak boleh menjadi induk dari dirinya sendiri.',
                    ],
                ],
            ], 422);
        }

        /*
         * Jangan izinkan kategori anak menjadi induk dari anaknya sendiri.
         */
        if ($request->filled('id_induk')) {

            $idInduk = (int) $request->id_induk;

            $anakIds = $kategoriSampah
                ->anak()
                ->pluck('id_kategori')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            if (in_array($idInduk, $anakIds, true)) {
                return response()->json([
                    'errors' => [
                        'id_induk' => [
                            'Kategori tidak dapat menggunakan sub-kategorinya sebagai induk.',
                        ],
                    ],
                ], 422);
            }
        }

        $kategoriSampah->update([
            'id_induk'      => $request->id_induk ?: null,
            'nama_kategori' => trim($request->nama_kategori),
            'satuan'        => $request->satuan,
        ]);

        return response()->json([
            'message' =>
                'Perubahan kategori berhasil disimpan.',

            'data' =>
                $kategoriSampah->fresh([
                    'induk',
                    'hargaTerbaru',
                ]),
        ]);
    }

    /**
     * Hapus kategori.
     */
    public function destroy(KategoriSampah $kategoriSampah)
    {
        if ($kategoriSampah->anak()->exists()) {
            return response()->json([
                'message' =>
                    'Kategori tidak dapat dihapus karena masih memiliki sub-kategori.',
            ], 422);
        }

        /*
         * Hapus riwayat harga terlebih dahulu.
         */
        $kategoriSampah->hargaSampah()->delete();

        $kategoriSampah->delete();

        return response()->json([
            'message' =>
                'Kategori berhasil dihapus.',
        ]);
    }

    /**
     * Tambah harga baru untuk kategori yang sudah ada.
     */
    public function hargaStore(
        Request $request,
        KategoriSampah $kategoriSampah
    ) {
        $validator = Validator::make(
            $request->all(),
            [
                'harga_satuan' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:9999999999',
                ],

                'tanggal_berlaku' => [
                    'required',
                    'date',
                ],
            ],
            [
                'harga_satuan.required' =>
                    'Harga wajib diisi.',

                'harga_satuan.numeric' =>
                    'Harga hanya boleh berupa angka.',

                'harga_satuan.min' =>
                    'Harga tidak boleh bernilai negatif.',

                'harga_satuan.max' =>
                    'Harga terlalu besar.',

                'tanggal_berlaku.required' =>
                    'Tanggal berlaku wajib diisi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $harga = HargaSampah::create([
            'id_kategori'     => $kategoriSampah->id_kategori,
            'id_admin'        => Auth::id(),
            'harga_satuan'    => $request->harga_satuan,
            'tanggal_berlaku' => $request->tanggal_berlaku,
        ]);

        return response()->json([
            'message' =>
                'Harga baru berhasil ditambahkan.',

            'data' =>
                $harga,
        ]);
    }

    /**
     * Hapus harga.
     */
    public function hargaDestroy(
        KategoriSampah $kategoriSampah,
        HargaSampah $hargaSampah
    ) {
        if (
            (int) $hargaSampah->id_kategori !==
            (int) $kategoriSampah->id_kategori
        ) {
            return response()->json([
                'message' =>
                    'Data harga tidak ditemukan.',
            ], 404);
        }

        $hargaSampah->delete();

        return response()->json([
            'message' =>
                'Data harga berhasil dihapus.',
        ]);
    }
}
