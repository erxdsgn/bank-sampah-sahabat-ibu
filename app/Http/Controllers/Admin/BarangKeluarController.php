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
            ->selectRaw('id_kategori, SUM(berat_gram) as total_masuk')
            ->groupBy('id_kategori')
            ->pluck('total_masuk', 'id_kategori');

        // Hitung Total Keluar (Penjualan) per id_kategori
        $barangKeluarTotal = BarangKeluar::selectRaw('id_kategori, SUM(berat_gram) as total_keluar')
            ->groupBy('id_kategori')
            ->pluck('total_keluar', 'id_kategori');

        // Ambil kategori beserta stok dan penyesuaian satuannya
        $kategoriSampah = KategoriSampah::all()->map(function ($kat) use ($setoranMasuk, $barangKeluarTotal) {
            $totalMasuk  = $setoranMasuk->get($kat->id_kategori, 0);
            $totalKeluar = $barangKeluarTotal->get($kat->id_kategori, 0);

            $sisaStok = max(0, $totalMasuk - $totalKeluar);
            $satuan   = strtolower($kat->satuan ?? 'kg');

            // Jika satuan kg, tampilkan dalam bentuk kg (dibagi 1000). Jika pcs/lainnya, tampilkan apa adanya.
            $kat->stok_tersedia = ($satuan === 'kg') ? ($sisaStok / 1000) : $sisaStok;

            return $kat;
        });

        // Ringkasan Statistik
        $totalPenjualan  = $barangKeluar->sum('total');
        $totalKuantitas  = $barangKeluar->sum('berat_gram');

        // Asumsi ringkasan berat total kg mengambil dari kategori yang bersatuan kg
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
            'pembeli'           => 'required|string|regex:/^[a-zA-Z\s]+$/|max:50',
            'id_kategori'       => 'required|exists:kategori_sampah,id_kategori',
            'tanggal_transaksi' => 'required|date',
            'kuantitas'         => 'required|numeric|min:1',
            'harga_satuan'      => 'required|numeric|min:1',
        ], [
            'pembeli.regex' => 'Nama pembeli hanya boleh berisi huruf dan spasi.',
        ]);
        $kategori = KategoriSampah::findOrFail($validated['id_kategori']);
        $satuan   = strtolower($kategori->satuan ?? 'kg');

        // Konversi kuantitas ke basis penyimpanan sistem
        $kuantitasInput = $validated['kuantitas'];
        $kuantitasSimpan = ($satuan === 'kg') ? ($kuantitasInput * 1000) : $kuantitasInput;

        $stokTersedia = $this->getSisaStokSistem($validated['id_kategori']);

        if ($kuantitasSimpan > $stokTersedia) {
            $formattedStok = ($satuan === 'kg') ? number_format($stokTersedia / 1000, 2, ',', '.') : number_format($stokTersedia, 0, ',', '.');
            $errorMessage = "Jumlah penjualan melebihi stok yang tersedia (Stok saat ini: {$formattedStok} {$kategori->satuan}).";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['kuantitas' => $errorMessage]);
        }

        // Perhitungan harga jual per satuan dan total harga
        $hargaJualPerSatuan = $validated['harga_satuan'];
        $hargaJualSimpan = ($satuan === 'kg') ? ($hargaJualPerSatuan / 1000) : $hargaJualPerSatuan;
        $totalHarga = ($satuan === 'kg')
            ? (($kuantitasInput * 1000) * ($hargaJualPerSatuan / 1000))
            : ($kuantitasInput * $hargaJualPerSatuan);

        $dataInsert = [
            'id_kategori'         => $validated['id_kategori'],
            'tanggal'             => $validated['tanggal_transaksi'],
            'berat_gram'          => $kuantitasSimpan, // Kolom database tetap menggunakan 'berat_gram' sebagai representatif nilai simpan sistem
            'harga_jual_per_gram' => $hargaJualSimpan,  // Representatif harga per unit sistem
            'total'               => $totalHarga,
            'id_admin'            => Auth::id(),
            'pembeli'             => $validated['pembeli'],
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
            return response()->json([
                'status' => 'success',
                'data'   => $barangKeluar
            ]);
        }

        return view('admin.pages.penjualan-pengepul.show', compact('barangKeluar'));
    }

    /**
     * Perbarui data transaksi penjualan.
     */
    public function update(Request $request, BarangKeluar $barangKeluar)
    {
        $validated = $request->validate([
            'id_kategori'         => 'required|exists:kategori_sampah,id_kategori',
            'tanggal'             => 'required|date',
            'berat_gram'          => 'required|numeric|min:1',
            'harga_jual_per_gram' => 'required|numeric|min:1',
            'pembeli'             => 'required|string|regex:/^[a-zA-Z\s]+$/|max:50',
        ], [
            'pembeli.regex' => 'Nama pembeli hanya boleh berisi huruf dan spasi.',
        ]);

        $kategori = KategoriSampah::findOrFail($validated['id_kategori']);
        $satuan   = strtolower($kategori->satuan ?? 'kg');

        $kuantitasInput = $validated['berat_gram'];
        $kuantitasSimpan = ($satuan === 'kg') ? ($kuantitasInput * 1000) : $kuantitasInput;

        $stokTersedia = $this->getSisaStokSistem($validated['id_kategori'], $barangKeluar);

        if ($kuantitasSimpan > $stokTersedia) {
            $formattedStok = ($satuan === 'kg') ? number_format($stokTersedia / 1000, 2, ',', '.') : number_format($stokTersedia, 0, ',', '.');
            $errorMessage = "Jumlah penjualan melebihi stok yang tersedia (Stok tersedia: {$formattedStok} {$kategori->satuan}).";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['berat_gram' => $errorMessage]);
        }

        $hargaSatuanSimpan = ($satuan === 'kg') ? ($validated['harga_jual_per_gram'] / 1000) : $validated['harga_jual_per_gram'];
        $totalHarga = ($satuan === 'kg')
            ? (($kuantitasInput * 1000) * ($validated['harga_jual_per_gram'] / 1000))
            : ($kuantitasInput * $validated['harga_jual_per_gram']);

        $updateData = [
            'id_kategori'         => $validated['id_kategori'],
            'tanggal'             => $validated['tanggal'],
            'berat_gram'          => $kuantitasSimpan,
            'harga_jual_per_gram' => $hargaSatuanSimpan,
            'total'               => $totalHarga,
            'pembeli'             => $validated['pembeli'],
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
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.penjualan-pengepul.index')
            ->with('success', 'Transaksi penjualan berhasil dihapus.');
    }

    /**
     * Helper privat untuk menghitung sisa stok sistem per kategori secara dinamis.
     */
    private function getSisaStokSistem(int $idKategori, ?BarangKeluar $ignoreItem = null): float
    {
        $totalMasuk = DetailSetoran::where('id_kategori', $idKategori)
            ->whereHas('setoran', function ($q) {
                $q->where('status', 'approved');
            })
            ->sum('berat_gram');

        $queryKeluar = BarangKeluar::where('id_kategori', $idKategori);

        if ($ignoreItem && $ignoreItem->id_kategori == $idKategori) {
            $queryKeluar->where('id_barang_keluar', '!=', $ignoreItem->id_barang_keluar);
        }

        $totalKeluar = $queryKeluar->sum('berat_gram');

        return max(0, $totalMasuk - $totalKeluar);
    }
}
