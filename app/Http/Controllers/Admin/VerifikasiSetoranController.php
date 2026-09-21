<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use App\Models\Setoran;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VerifikasiSetoranController extends Controller
{
    /**
     * Nilai kolom mutasi_saldo.jenis_mutasi untuk saldo yang bertambah.
     * SESUAIKAN dengan nilai yang dipakai di data mutasi_saldo Anda (mis. 'masuk', 'kredit', 'setoran').
     */
    private const JENIS_MUTASI_MASUK = 'masuk';

    /**
     * Tampilkan halaman verifikasi setoran.
     */
    public function index()
    {
        $setoran = Setoran::with(['warga', 'details'])
            ->orderByDesc('id_setoran')
            ->get();

        // Kategori & berat tiap setoran ada di tabel detail_setoran
        $detailMap = DB::table('detail_setoran')
            ->whereIn('id_setoran', $setoran->modelKeys())
            ->get()
            ->groupBy('id_setoran');

        // Sumber harga sama dengan halaman Kategori & Harga (relasi hargaTerbaru)
        $kategoriSampah = KategoriSampah::with(['induk', 'hargaTerbaru'])
            ->orderBy('nama_kategori', 'asc')
            ->get()
            ->sortBy('nama_lengkap')
            ->values();

        $wargaList = Warga::orderBy('nama', 'asc')->get();

        return view('admin.pages.verifikasi-setoran', compact('setoran', 'detailMap', 'kategoriSampah', 'wargaList'));
    }

    /**
     * Setujui setoran: koreksi kategori & berat (gram), hitung nilai, tambahkan saldo warga.
     */
    public function setujui(Request $request, Setoran $setoran)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|exists:kategori_sampah,id_kategori',
            'berat_gram'  => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($setoran->status !== 'pending') {
            return response()->json(['message' => 'Setoran ini sudah diproses sebelumnya.'], 409);
        }

        $jumlahDetail = DB::table('detail_setoran')->where('id_setoran', $setoran->getKey())->count();

        if ($jumlahDetail > 1) {
            return response()->json([
                'message' => 'Setoran ini berisi lebih dari satu kategori. Form verifikasi saat ini baru mendukung satu kategori per setoran.',
            ], 422);
        }

        $kategori = KategoriSampah::with('hargaTerbaru')->find($request->id_kategori);

        if (! $kategori || ! $kategori->hargaTerbaru) {
            return response()->json([
                'message' => 'Kategori ini belum memiliki harga aktif. Atur harga terlebih dahulu di halaman Kategori & Harga Sampah.',
            ], 422);
        }

        // harga per gram x berat (gram)
        $totalNilai = round($kategori->hargaTerbaru->harga_per_gram * $request->berat_gram, 2);

        DB::transaction(function () use ($setoran, $request, $kategori, $totalNilai) {
            $setoran->forceFill([
                'id_admin'    => Auth::id(),
                'total_berat' => $request->berat_gram,
                'total_nilai' => $totalNilai,
                'status'      => 'approved',
            ])->save();

            $this->simpanDetailDanSaldo($setoran, $kategori, $request->berat_gram, $totalNilai);
        });

        return response()->json(['message' => 'Setoran disetujui dan saldo warga berhasil diperbarui.']);
    }

    /**
     * Tolak setoran beserta alasannya (catatan_admin).
     */
    public function tolak(Request $request, Setoran $setoran)
    {
        $validator = Validator::make($request->all(), [
            'catatan_admin' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($setoran->status !== 'pending') {
            return response()->json(['message' => 'Setoran ini sudah diproses sebelumnya.'], 409);
        }

        $setoran->forceFill([
            'status'        => 'rejected',
            'catatan_admin' => $request->catatan_admin,
            'id_admin'      => Auth::id(),
        ])->save();

        return response()->json(['message' => 'Setoran berhasil ditolak.']);
    }

    /**
     * Tambah setoran baru secara manual oleh Admin (langsung disetujui).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_warga'        => 'required|exists:warga,id_warga',
            'id_kategori'     => 'required|exists:kategori_sampah,id_kategori',
            'berat_gram'      => 'required|numeric|min:1',
            'tanggal_setoran' => 'required|date',
            'catatan_admin'   => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $kategori = KategoriSampah::with('hargaTerbaru')->find($request->id_kategori);

        if (! $kategori || ! $kategori->hargaTerbaru) {
            return response()->json(['message' => 'Kategori ini belum memiliki harga aktif.'], 422);
        }

        $totalNilai = round($kategori->hargaTerbaru->harga_per_gram * $request->berat_gram, 2);

        DB::transaction(function () use ($request, $kategori, $totalNilai) {
            $setoran = new Setoran();

            $setoran->forceFill([
                'id_warga'        => $request->id_warga,
                'id_admin'        => Auth::id(),
                'tanggal_setoran' => $request->tanggal_setoran,
                'status'          => 'approved',
                'total_berat'     => $request->berat_gram,
                'total_nilai'     => $totalNilai,
                'catatan_admin'   => $request->catatan_admin,
            ])->save();

            $this->simpanDetailDanSaldo($setoran, $kategori, $request->berat_gram, $totalNilai);
        });

        return response()->json(['message' => 'Setoran berhasil ditambahkan dan saldo warga diperbarui.']);
    }

    /**
     * Simpan (atau perbarui) baris detail_setoran, tambah saldo warga, dan catat mutasi_saldo.
     * Harus dipanggil di dalam DB::transaction.
     */
    private function simpanDetailDanSaldo(Setoran $setoran, KategoriSampah $kategori, $beratGram, $totalNilai): void
    {
        $idSetoran = $setoran->getKey();

        $data = [
            'id_kategori'    => $kategori->id_kategori,
            'berat_gram'     => $beratGram,
            'harga_per_gram' => $kategori->hargaTerbaru->harga_per_gram,
            'subtotal'       => $totalNilai,
            'updated_at'     => now(),
        ];

        $ada = DB::table('detail_setoran')->where('id_setoran', $idSetoran)->exists();

        if ($ada) {
            DB::table('detail_setoran')->where('id_setoran', $idSetoran)->update($data);
        } else {
            DB::table('detail_setoran')->insert($data + [
                'id_setoran' => $idSetoran,
                'created_at' => now(),
            ]);
        }

        $setoran->warga()->increment('saldo', $totalNilai);

        DB::table('mutasi_saldo')->insert([
            'id_warga'     => $setoran->id_warga,
            'id_setoran'   => $idSetoran,
            'jenis_mutasi' => self::JENIS_MUTASI_MASUK,
            'jumlah'       => $totalNilai,
            'tanggal'      => now()->toDateString(),
            'keterangan'   => 'Setoran sampah: ' . $kategori->nama_lengkap . ' (' . $beratGram . ' gram)',
        ]);
    }
}
