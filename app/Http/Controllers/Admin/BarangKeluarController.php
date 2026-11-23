<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\DetailSetoran;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
    /**
     * Tampilkan daftar transaksi penjualan barang keluar.
     */
    public function index()
    {
        $barangKeluar = BarangKeluar::with(['kategori', 'admin'])
            ->latest('id_barang_keluar')
            ->get();

        // Hitung Total Masuk (Setoran Disetujui / Approved) per id_kategori
        $setoranMasuk = DetailSetoran::whereHas('setoran', function ($query) {
            $query->where('status', 'approved');
        })
            ->selectRaw('id_kategori, SUM(berat) as total_masuk') // ✅ Gunakan kolom 'berat'
            ->groupBy('id_kategori')
            ->pluck('total_masuk', 'id_kategori');

        // Hitung Total Keluar (Penjualan) per id_kategori
        $barangKeluarTotal = BarangKeluar::selectRaw('id_kategori, SUM(berat) as total_keluar') // ✅ Gunakan kolom 'berat'
            ->groupBy('id_kategori')
            ->pluck('total_keluar', 'id_kategori');

        // Ambil kategori beserta stok
        $kategoriSampah = KategoriSampah::all()->map(function ($kat) use ($setoranMasuk, $barangKeluarTotal) {
            $totalMasuk  = $setoranMasuk->get($kat->id_kategori, 0);
            $totalKeluar = $barangKeluarTotal->get($kat->id_kategori, 0);

            $sisaStok = max(0, $totalMasuk - $totalKeluar);

            // ✅ Stok langsung dimasukkan tanpa dibagi 1000
            $kat->stok_tersedia = (float) $sisaStok;

            return $kat;
        });

        // Ringkasan Statistik
        $totalPenjualan  = $barangKeluar->sum('total');
        $totalKuantitas  = $barangKeluar->sum('berat'); // ✅ Gunakan kolom 'berat'

        $jumlahTransaksi = $barangKeluar->count();
        $jumlahPengepul  = $barangKeluar->pluck('pembeli')->filter()->unique()->count();

        return view('admin.pages.penjualan-pengepul', compact(
            'barangKeluar',
            'kategoriSampah',
            'totalPenjualan',
            'totalKuantitas',
            'jumlahTransaksi',
            'jumlahPengepul'
        ));
    }

    /**
     * Simpan data transaksi penjualan baru ke pengepul.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pembeli'      => 'required|string|regex:/^[a-zA-Z\s]+$/|max:50',
            'id_kategori'  => 'required|exists:kategori_sampah,id_kategori',
            'kuantitas'    => 'required|numeric|min:0.01',
            'harga_satuan' => 'required|numeric|min:1',
        ], [
            'pembeli.regex' => 'Nama pembeli hanya boleh berisi huruf dan spasi.',
        ]);

        $kategori = KategoriSampah::findOrFail($validated['id_kategori']);

        // ✅ Langsung pakai nilai input tanpa dikonversi ke gram
        $kuantitasSimpan = (float) $validated['kuantitas'];
        $hargaJualSimpan = (float) $validated['harga_satuan'];
        $totalHarga = $kuantitasSimpan * $hargaJualSimpan;

        $stokTersedia = $this->getSisaStokSistem($validated['id_kategori']);

        if ($kuantitasSimpan > $stokTersedia) {
            $formattedStok = number_format($stokTersedia, 2, ',', '.');
            $errorMessage = "Jumlah penjualan melebihi stok yang tersedia (Stok saat ini: {$formattedStok} {$kategori->satuan}).";

            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $errorMessage], 422);
            }

            return redirect()->back()->withInput()->withErrors(['kuantitas' => $errorMessage]);
        }

        $dataInsert = [
            'id_kategori'       => $validated['id_kategori'],
            'tanggal'           => now()->toDateString(),
            'berat'             => $kuantitasSimpan, // ✅ Gunakan kolom 'berat'
            'harga_jual_satuan' => $hargaJualSimpan, // ✅ Gunakan kolom 'harga_jual_satuan'
            'total'             => $totalHarga,
            'id_admin'          => Auth::id(),
            'pembeli'           => $validated['pembeli'],
        ];

        $barangKeluar = DB::transaction(function () use ($dataInsert) {
            return BarangKeluar::create($dataInsert);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan ke pengepul berhasil ditambahkan.',
                'data'    => $barangKeluar
            ], 201);
        }

        return redirect()->route('admin.penjualan-pengepul.index')
            ->with('success', 'Transaksi penjualan ke pengepul berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail transaksi penjualan.
     */
    public function show(BarangKeluar $barangKeluar)
    {
        $barangKeluar->load(['kategori', 'admin']);

        if (request()->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $barangKeluar]);
        }

        return view('admin.pages.penjualan-pengepul.show', compact('barangKeluar'));
    }

    /**
     * Perbarui data transaksi penjualan.
     */
    public function update(Request $request, BarangKeluar $barangKeluar)
    {
        $validated = $request->validate([
            'id_kategori'  => 'required|exists:kategori_sampah,id_kategori',
            'kuantitas'    => 'required|numeric|min:0.01', // ✅ Diseragamkan dengan store
            'harga_satuan' => 'required|numeric|min:1',    // ✅ Diseragamkan dengan store
            'pembeli'      => 'required|string|regex:/^[a-zA-Z\s]+$/|max:50',
        ], [
            'pembeli.regex' => 'Nama pembeli hanya boleh berisi huruf dan spasi.',
        ]);

        $kategori = KategoriSampah::findOrFail($validated['id_kategori']);

        $kuantitasSimpan = (float) $validated['kuantitas'];
        $hargaSatuanSimpan = (float) $validated['harga_satuan'];
        $totalHarga = $kuantitasSimpan * $hargaSatuanSimpan;

        $stokTersedia = $this->getSisaStokSistem($validated['id_kategori'], $barangKeluar);

        if ($kuantitasSimpan > $stokTersedia) {
            $formattedStok = number_format($stokTersedia, 2, ',', '.');
            $errorMessage = "Jumlah penjualan melebihi stok yang tersedia (Stok tersedia: {$formattedStok} {$kategori->satuan}).";

            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $errorMessage], 422);
            }

            return redirect()->back()->withInput()->withErrors(['kuantitas' => $errorMessage]);
        }

        $updateData = [
            'id_kategori'       => $validated['id_kategori'],
            'berat'             => $kuantitasSimpan, // ✅ Gunakan kolom 'berat'
            'harga_jual_satuan' => $hargaSatuanSimpan, // ✅ Gunakan kolom 'harga_jual_satuan'
            'total'             => $totalHarga,
            'pembeli'           => $validated['pembeli'],
        ];

        DB::transaction(function () use ($barangKeluar, $updateData) {
            $barangKeluar->update($updateData);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan berhasil diperbarui.',
                'data'    => $barangKeluar
            ]);
        }

        return redirect()->route('admin.penjualan-pengepul.index')
            ->with('success', 'Transaksi penjualan berhasil diperbarui.');
    }

    /**
     * Hapus data transaksi penjualan.
     */
    public function destroy(Request $request, BarangKeluar $barangKeluar)
    {
        DB::transaction(function () use ($barangKeluar) {
            $barangKeluar->delete();
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Transaksi penjualan berhasil dihapus.']);
        }

        return redirect()->route('admin.penjualan-pengepul.index')
            ->with('success', 'Transaksi penjualan berhasil dihapus.');
    }

    /**
     * Helper privat untuk menghitung sisa stok sistem per kategori secara dinamis.
     * Nilai yang dikembalikan adalah nilai asli (kg/liter/pcs).
     */
    private function getSisaStokSistem(int $idKategori, ?BarangKeluar $ignoreItem = null): float
    {
        $totalMasuk = DetailSetoran::where('id_kategori', $idKategori)
            ->whereHas('setoran', function ($q) {
                $q->where('status', 'approved');
            })
            ->sum('berat'); // ✅ Gunakan kolom 'berat'

        $queryKeluar = BarangKeluar::where('id_kategori', $idKategori);

        if ($ignoreItem && $ignoreItem->id_kategori == $idKategori) {
            $queryKeluar->where('id_barang_keluar', '!=', $ignoreItem->id_barang_keluar);
        }

        $totalKeluar = $queryKeluar->sum('berat'); // ✅ Gunakan kolom 'berat'

        return max(0, $totalMasuk - $totalKeluar);
    }
}
