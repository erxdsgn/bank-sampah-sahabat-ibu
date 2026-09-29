<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use App\Models\Setoran;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class VerifikasiSetoranController extends Controller
{
    private const JENIS_MUTASI_MASUK = 'masuk';

    /**
     * Satuan yang diperbolehkan sistem.
     */
    private const SATUAN_VALID = [
        'gram',
        'kg',
        'pcs',
        'liter',
        'unit',
        'set',
    ];

    /**
     * Halaman Verifikasi Setoran.
     */
    public function index()
    {
        $setoran = Setoran::with([
            'warga',
            'details.kategori',
        ])
            ->orderByDesc('id_setoran')
            ->get();

        $detailMap = DB::table('detail_setoran')
            ->whereIn(
                'id_setoran',
                $setoran->modelKeys()
            )
            ->get()
            ->groupBy('id_setoran');

        $kategoriSampah = KategoriSampah::with([
            'induk',
            'hargaTerbaru',
        ])
            ->orderBy('nama_kategori')
            ->get()
            ->sortBy('nama_lengkap')
            ->values();

        $wargaList = Warga::orderBy(
            'nama',
            'asc'
        )->get();

        return view(
            'admin.pages.verifikasi-setoran',
            compact(
                'setoran',
                'detailMap',
                'kategoriSampah',
                'wargaList'
            )
        );
    }

    /**
     * Menyetujui setoran dari warga.
     */
    public function setujui(
        Request $request,
        Setoran $setoran
    ) {
        $validator = Validator::make(
            $request->all(),
            [
                'id_kategori' => [
                    'required',
                    'exists:kategori_sampah,id_kategori',
                ],

                'jumlah' => [
                    'required',
                    'numeric',
                    'min:0.01',
                    'max:100000000',
                ],
            ],
            [
                'id_kategori.required' => 'Kategori sampah wajib dipilih.',
                'id_kategori.exists'   => 'Kategori sampah tidak ditemukan.',
                'jumlah.required'      => 'Jumlah wajib diisi.',
                'jumlah.numeric'       => 'Jumlah harus berupa angka.',
                'jumlah.min'           => 'Jumlah minimal 0,01.',
                'jumlah.max'           => 'Jumlah terlalu besar.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if ($setoran->status !== 'pending') {
            return response()->json([
                'message' => 'Setoran ini sudah diproses sebelumnya.',
            ], 409);
        }

        $jumlahDetail = DB::table('detail_setoran')
            ->where('id_setoran', $setoran->getKey())
            ->count();

        if ($jumlahDetail > 1) {
            return response()->json([
                'message' => 'Setoran ini berisi lebih dari satu kategori. Form verifikasi saat ini baru mendukung satu kategori per setoran.',
            ], 422);
        }

        $kategori = KategoriSampah::with('hargaTerbaru')->find($request->id_kategori);

        if (! $kategori) {
            return response()->json([
                'message' => 'Kategori sampah tidak ditemukan.',
            ], 422);
        }

        if (! $kategori->hargaTerbaru) {
            return response()->json([
                'message' => 'Kategori ini belum memiliki harga aktif. Atur harga terlebih dahulu di halaman Kategori & Harga Sampah.',
            ], 422);
        }

        $satuan = strtolower(trim($kategori->satuan ?? ''));

        if (! in_array($satuan, self::SATUAN_VALID, true)) {
            return response()->json([
                'message' => 'Satuan kategori tidak valid. Gunakan gram, kg, pcs, liter, unit, atau set.',
            ], 422);
        }

        $jumlah = (float) $request->jumlah;
        $hargaSatuan = (float) $kategori->hargaTerbaru->harga_satuan;
        $totalNilai = round($jumlah * $hargaSatuan, 2);

        DB::transaction(
            function () use (
                $setoran,
                $kategori,
                $jumlah,
                $satuan,
                $hargaSatuan,
                $totalNilai
            ) {
                $setoran->forceFill([
                    'id_admin'    => Auth::id(),
                    'total_berat' => $satuan === 'gram' ? $jumlah : null,
                    'total_nilai' => $totalNilai,
                    'status'      => 'approved',
                ])->save();

                $this->simpanDetailDanSaldo(
                    $setoran,
                    $kategori,
                    $jumlah,
                    $satuan,
                    $hargaSatuan,
                    $totalNilai
                );
            }
        );

        return response()->json([
            'message' => 'Setoran disetujui dan saldo warga berhasil diperbarui.',
        ]);
    }

    /**
     * Menolak setoran.
     */
    public function tolak(
        Request $request,
        Setoran $setoran
    ) {
        if ($setoran->status !== 'pending') {
            return response()->json([
                'message' => 'Setoran ini sudah diproses sebelumnya.',
            ], 409);
        }

        $catatan = $request->input('catatan_admin') ?? $request->input('alasan');

        $request->merge([
            'catatan_admin' => $catatan,
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                'catatan_admin' => [
                    'required',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\.\,\?\!\-]+$/',
                ],
            ],
            [
                'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
                'catatan_admin.max'      => 'Catatan maksimal 500 karakter.',
                'catatan_admin.regex'    => 'Catatan hanya boleh berisi huruf, angka, spasi, titik, koma, tanda tanya, tanda seru, dan tanda hubung.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $setoran->forceFill([
            'id_admin'      => Auth::id(),
            'status'        => 'rejected',
            'catatan_admin' => $request->catatan_admin,
        ])->save();

        return response()->json([
            'message' => 'Setoran berhasil ditolak.',
        ]);
    }

    /**
     * Tambah setoran secara manual oleh admin.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_warga' => [
                    'required',
                    'exists:warga,id_warga',
                ],

                'id_kategori' => [
                    'required',
                    'exists:kategori_sampah,id_kategori',
                ],

                'jumlah' => [
                    'required',
                    'numeric',
                    'min:0.01',
                    'max:100000000',
                ],

                'tanggal_setoran' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],

                'catatan_admin' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\.\,\?\!\-]+$/',
                ],
            ],
            [
                'id_warga.required'             => 'Warga wajib dipilih.',
                'id_warga.exists'               => 'Data warga tidak ditemukan.',
                'id_kategori.required'          => 'Kategori sampah wajib dipilih.',
                'id_kategori.exists'            => 'Kategori sampah tidak ditemukan.',
                'jumlah.required'               => 'Jumlah wajib diisi.',
                'jumlah.numeric'                => 'Jumlah harus berupa angka.',
                'jumlah.min'                    => 'Jumlah minimal 0,01.',
                'tanggal_setoran.required'      => 'Tanggal setoran wajib diisi.',
                'tanggal_setoran.before_or_equal' => 'Tanggal setoran tidak boleh melebihi hari ini.',
                'catatan_admin.regex'           => 'Catatan hanya boleh berisi huruf, angka, spasi, titik, koma, tanda tanya, tanda seru, dan tanda hubung.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $kategori = KategoriSampah::with('hargaTerbaru')->find($request->id_kategori);

        if (! $kategori) {
            return response()->json([
                'message' => 'Kategori sampah tidak ditemukan.',
            ], 422);
        }

        if (! $kategori->hargaTerbaru) {
            return response()->json([
                'message' => 'Kategori ini belum memiliki harga aktif.',
            ], 422);
        }

        $satuan = strtolower(trim($kategori->satuan ?? ''));

        if (! in_array($satuan, self::SATUAN_VALID, true)) {
            return response()->json([
                'message' => 'Satuan kategori tidak valid. Gunakan gram, kg, pcs, liter, unit, atau set.',
            ], 422);
        }

        $jumlah = (float) $request->jumlah;
        $hargaSatuan = (float) $kategori->hargaTerbaru->harga_satuan;
        $totalNilai = round($jumlah * $hargaSatuan, 2);

        DB::transaction(
            function () use (
                $request,
                $kategori,
                $jumlah,
                $satuan,
                $hargaSatuan,
                $totalNilai
            ) {
                $setoran = new Setoran();

                $setoran->forceFill([
                    'id_warga'        => $request->id_warga,
                    'id_admin'        => Auth::id(),
                    'tanggal_setoran' => $request->tanggal_setoran,
                    'status'          => 'approved',
                    'total_berat'     => $satuan === 'gram' ? $jumlah : null,
                    'total_nilai'     => $totalNilai,
                    'catatan_admin'   => $request->catatan_admin,
                ])->save();

                $this->simpanDetailDanSaldo(
                    $setoran,
                    $kategori,
                    $jumlah,
                    $satuan,
                    $hargaSatuan,
                    $totalNilai
                );
            }
        );

        return response()->json([
            'message' => 'Setoran berhasil ditambahkan dan saldo warga diperbarui.',
        ]);
    }

    /**
     * Simpan detail setoran, saldo, dan mutasi.
     */
    private function simpanDetailDanSaldo(
        Setoran $setoran,
        KategoriSampah $kategori,
        float $jumlah,
        string $satuan,
        float $hargaSatuan,
        float $totalNilai
    ): void {
        $idSetoran = $setoran->getKey();

        $data = [
            'id_kategori' => $kategori->id_kategori,
            'jumlah'      => $jumlah,
            'satuan'      => $satuan,
            'subtotal'    => $totalNilai,
            'updated_at'  => now(),
        ];

        if (Schema::hasColumn('detail_setoran', 'harga_per_gram')) {
            $data['harga_per_gram'] = $hargaSatuan;
        }

        if (Schema::hasColumn('detail_setoran', 'berat_gram')) {
            switch ($satuan) {
                case 'kg':
                    $data['berat_gram'] = $jumlah * 1000;
                    break;
                case 'gram':
                    $data['berat_gram'] = $jumlah;
                    break;
                case 'liter':
                case 'pcs':
                case 'unit':
                case 'set':
                default:
                    $data['berat_gram'] = $jumlah;
                    break;
            }
        }

        $detailLama = DB::table('detail_setoran')
            ->where('id_setoran', $idSetoran)
            ->first();

        if ($detailLama) {
            DB::table('detail_setoran')
                ->where('id_setoran', $idSetoran)
                ->update($data);
        } else {
            $data['id_setoran'] = $idSetoran;
            $data['created_at'] = now();

            DB::table('detail_setoran')->insert($data);
        }

        $setoran->warga()->increment('saldo', $totalNilai);

        $dataMutasi = [
            'id_warga'     => $setoran->id_warga,
            'id_setoran'   => $idSetoran,
            'jenis_mutasi' => self::JENIS_MUTASI_MASUK,
            'jumlah'       => $totalNilai,
            'tanggal'      => now()->toDateString(),
            'keterangan'   => 'Setoran sampah: '
                . $kategori->nama_lengkap
                . ' ('
                . $jumlah
                . ' '
                . $satuan
                . ')',
        ];

        DB::table('mutasi_saldo')->insert($dataMutasi);
    }
}
