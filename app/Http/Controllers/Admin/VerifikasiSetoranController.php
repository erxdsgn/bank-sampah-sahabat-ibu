<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use App\Models\Keuangan;
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
            'admin.pages.verifikasi-setoran.index',
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
                'items' => ['required', 'array', 'min:1'],
                'items.*.id_kategori' => ['required', 'exists:kategori_sampah,id_kategori'],
                'items.*.jumlah' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            ],
            [
                'items.required' => 'Rincian sampah wajib diisi.',
                'items.*.id_kategori.required' => 'Kategori sampah wajib dipilih.',
                'items.*.id_kategori.exists'   => 'Kategori sampah tidak ditemukan.',
                'items.*.jumlah.required'      => 'Jumlah wajib diisi.',
                'items.*.jumlah.numeric'       => 'Jumlah harus berupa angka.',
                'items.*.jumlah.min'           => 'Jumlah minimal 0,01.',
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

        DB::transaction(function () use ($request, $setoran) {
            $totalSemuaNilai = 0;
            $totalBeratGram = 0;

            // Hapus detail lama jika ada sebelum dimasukkan ulang yang baru
            DB::table('detail_setoran')->where('id_setoran', $setoran->getKey())->delete();

            foreach ($request->items as $itemData) {
                // Memuat kategori beserta harga terbaru agar accessor getPoinPerSatuanAttribute aktif
                $kategori = KategoriSampah::with('hargaTerbaru')->find($itemData['id_kategori']);

                if (!$kategori || !$kategori->hargaTerbaru) {
                    throw new \Exception('Kategori atau harga aktif tidak ditemukan.');
                }

                $satuan = strtolower(trim($kategori->satuan ?? 'gram'));
                $jumlah = (float) $itemData['jumlah'];
                $hargaSatuan = (float) $kategori->hargaTerbaru->harga_satuan;
                $subtotal = round($jumlah * $hargaSatuan, 2);

                // --- PERHITUNGAN POIN OTOMATIS BERDASARKAN ACCESSOR MODEL ---
                $poinPerSatuan = $kategori->poin_per_satuan; // Otomatis dari Model KategoriSampah
                $totalPoinItem = round($jumlah * $poinPerSatuan);

                $totalSemuaNilai += $subtotal;
                // (Opsional) Jika ingin warga juga punya kolom total poin akumulasi di tabel warga, bisa ditambahkan di sini.

                if ($satuan === 'gram') {
                    $totalBeratGram += $jumlah;
                } elseif ($satuan === 'kg') {
                    $totalBeratGram += ($jumlah * 1000);
                }

                $detailData = [
                    'id_setoran' => $setoran->getKey(),
                    'id_kategori' => $kategori->id_kategori,
                    'jumlah' => $jumlah,
                    'satuan' => $satuan,
                    'subtotal' => $subtotal,
                    'poin' => $totalPoinItem, // <-- Menyimpan poin otomatis per item sampah
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('detail_setoran', 'harga_per_gram')) {
                    $detailData['harga_per_gram'] = $hargaSatuan;
                }

                if (Schema::hasColumn('detail_setoran', 'berat_gram')) {
                    // Konversi khusus satuan berat/volume ke unit terkecil (gram atau mililiter)
                    if ($satuan === 'kg' || $satuan === 'liter') {
                        $detailData['berat_gram'] = $jumlah * 1000;
                    } else {
                        $detailData['berat_gram'] = $jumlah;
                    }
                }

                DB::table('detail_setoran')->insert($detailData);
            }

            $setoran->forceFill([
                'id_admin'    => Auth::id(),
                'total_berat' => $totalBeratGram > 0 ? $totalBeratGram : null,
                'total_nilai' => $totalSemuaNilai,
                'status'      => 'approved',
            ])->save();

            // Update Saldo & Keuangan
            $setoran->warga()->increment('saldo', $totalSemuaNilai);

            DB::table('mutasi_saldo')->insert([
                'id_warga'     => $setoran->id_warga,
                'id_setoran'   => $setoran->getKey(),
                'jenis_mutasi' => self::JENIS_MUTASI_MASUK,
                'jumlah'       => $totalSemuaNilai,
                'tanggal'      => now()->toDateString(),
                'keterangan'   => 'Setoran sampah disetujui (Multi-item)',
            ]);

            Keuangan::updateOrCreate(
                [
                    'tipe_referensi' => 'setoran',
                    'id_referensi'   => $setoran->getKey(),
                ],
                [
                    'id_admin'           => Auth::id() ?? 1,
                    'jenis'              => 'Pemasukan',
                    'tanggal'            => $setoran->tanggal_setoran ?? now()->toDateString(),
                    'kategori_transaksi' => 'Setoran Sampah',
                    'keterangan'         => 'Setoran sampah warga (ID: ' . $setoran->getKey() . ')',
                    'jumlah'             => $totalSemuaNilai,
                ]
            );
        });

        return response()->json([
            'message' => 'Setoran disetujui, saldo warga dan keuangan berhasil diperbarui.',
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
                'id_warga' => ['required', 'exists:warga,id_warga'],
                'tanggal_setoran' => ['required', 'date', 'before_or_equal:today'],
                'catatan_admin' => ['nullable', 'string', 'max:500'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.id_kategori' => ['required', 'exists:kategori_sampah,id_kategori'],
                'items.*.jumlah' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            ],
            [
                'id_warga.required' => 'Warga wajib dipilih.',
                'id_warga.exists' => 'Data warga tidak ditemukan.',
                'items.required' => 'Rincian sampah wajib diisi.',
                'items.*.id_kategori.required' => 'Kategori sampah wajib dipilih.',
                'items.*.id_kategori.exists' => 'Kategori sampah tidak ditemukan.',
                'items.*.jumlah.required' => 'Jumlah wajib diisi.',
                'items.*.jumlah.numeric' => 'Jumlah harus berupa angka.',
                'items.*.jumlah.min' => 'Jumlah minimal 0,01.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::transaction(function () use ($request) {
            $setoran = new Setoran();
            $setoran->forceFill([
                'id_warga'        => $request->id_warga,
                'id_admin'        => Auth::id(),
                'tanggal_setoran' => $request->tanggal_setoran,
                'status'          => 'approved',
                'catatan_admin'   => $request->catatan_admin,
                'total_berat'     => null,
                'total_nilai'     => 0,
            ]);
            $setoran->save();

            $totalSemuaNilai = 0;
            $totalBeratGram = 0;

            foreach ($request->items as $itemData) {
                // Memuat kategori beserta harga terbaru agar accessor getPoinPerSatuanAttribute aktif
                $kategori = KategoriSampah::with('hargaTerbaru')->find($itemData['id_kategori']);

                if (!$kategori || !$kategori->hargaTerbaru) {
                    throw new \Exception('Kategori atau harga aktif tidak ditemukan.');
                }

                $satuan = strtolower(trim($kategori->satuan ?? 'gram'));
                $jumlah = (float) $itemData['jumlah'];
                $hargaSatuan = (float) $kategori->hargaTerbaru->harga_satuan;
                $subtotal = round($jumlah * $hargaSatuan, 2);

                // --- PERHITUNGAN POIN OTOMATIS TAMBAHAN UNTUK METHOD STORE ---
                $poinPerSatuan = $kategori->poin_per_satuan;
                $totalPoinItem = round($jumlah * $poinPerSatuan);

                $totalSemuaNilai += $subtotal;

                if ($satuan === 'gram') {
                    $totalBeratGram += $jumlah;
                } elseif ($satuan === 'kg') {
                    $totalBeratGram += ($jumlah * 1000);
                }

                $detailData = [
                    'id_setoran' => $setoran->getKey(),
                    'id_kategori' => $kategori->id_kategori,
                    'jumlah' => $jumlah,
                    'satuan' => $satuan,
                    'subtotal' => $subtotal,
                    'poin' => $totalPoinItem, // <-- Menambahkan penyimpanan poin otomatis per item
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('detail_setoran', 'harga_per_gram')) {
                    $detailData['harga_per_gram'] = $hargaSatuan;
                }

                if (Schema::hasColumn('detail_setoran', 'berat_gram')) {
                    if ($satuan === 'kg' || $satuan === 'liter') {
                        $detailData['berat_gram'] = $jumlah * 1000;
                    } else {
                        $detailData['berat_gram'] = $jumlah;
                    }
                }

                DB::table('detail_setoran')->insert($detailData);
            }

            $setoran->forceFill([
                'total_berat' => $totalBeratGram > 0 ? $totalBeratGram : null,
                'total_nilai' => $totalSemuaNilai,
            ])->save();

            // 1. Update Saldo Warga
            $setoran->warga()->increment('saldo', $totalSemuaNilai);

            // 2. Catat Mutasi Saldo
            DB::table('mutasi_saldo')->insert([
                'id_warga'     => $setoran->id_warga,
                'id_setoran'   => $setoran->getKey(),
                'jenis_mutasi' => self::JENIS_MUTASI_MASUK,
                'jumlah'       => $totalSemuaNilai,
                'tanggal'      => now()->toDateString(),
                'keterangan'   => 'Setoran sampah manual oleh admin (Multi-item)',
            ]);

            // 3. Catat Keuangan
            Keuangan::updateOrCreate(
                [
                    'tipe_referensi' => 'setoran',
                    'id_referensi'   => $setoran->getKey(),
                ],
                [
                    'id_admin'           => Auth::id() ?? 1,
                    'jenis'              => 'Pemasukan',
                    'tanggal'            => $setoran->tanggal_setoran ?? now()->toDateString(),
                    'kategori_transaksi' => 'Setoran Sampah',
                    'keterangan'         => 'Setoran sampah manual warga (ID: ' . $setoran->getKey() . ')',
                    'jumlah'             => $totalSemuaNilai,
                ]
            );
        });

        return response()->json([
            'message' => 'Setoran berhasil ditambahkan, saldo warga dan keuangan diperbarui.',
        ]);
    }
}
