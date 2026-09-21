<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarangKeluarController extends Controller
{
    /**
     * Tampilkan daftar transaksi penjualan barang keluar.
     */
    public function index()
    {
        $barangKeluar = BarangKeluar::with(['kategori', 'admin'])
            ->latest('tanggal')
            ->get();

        $kategori = KategoriSampah::all();

        // Total nilai penjualan
        $totalPenjualan = $barangKeluar->sum('total');

        // Total berat sampah
        $totalBeratGram = $barangKeluar->sum('berat_gram');

        // Gram ke kilogram
        $totalBeratKg = $totalBeratGram / 1000;

        // Jumlah transaksi
        $jumlahTransaksi = $barangKeluar->count();

        // Jumlah pengepul
        $jumlahPengepul = $barangKeluar
            ->pluck('pembeli')
            ->filter()
            ->unique()
            ->count();

        return view('admin.pages.penjualan-pengepul', compact(
            'barangKeluar',
            'kategori',
            'totalPenjualan',
            'totalBeratKg',
            'jumlahTransaksi',
            'jumlahPengepul'
        ));
    }

    /**
     * Simpan data transaksi penjualan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kategori'         => 'required|exists:kategori_sampah,id_kategori',
            'tanggal'             => 'required|date',
            'berat_gram'          => 'required|numeric|min:0',
            'harga_jual_per_gram' => 'required|numeric|min:0',
            'pembeli'             => 'required|string|max:255',
        ]);

        // Kalkulasi otomatis total nilai penjualan
        $validated['total'] = $validated['berat_gram'] * $validated['harga_jual_per_gram'];

        // Asosiasikan dengan admin yang sedang login
        $validated['id_admin'] = Auth::id();

        $barangKeluar = BarangKeluar::create($validated);

        // Jika dipanggil via AJAX/Fetch API
        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan berhasil ditambahkan.',
                'data'    => $barangKeluar
            ]);
        }

        return redirect()->route('admin.penjualan-pengepul.index')
            ->with('success', 'Transaksi penjualan berhasil ditambahkan.');
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
            'berat_gram'          => 'required|numeric|min:0',
            'harga_jual_per_gram' => 'required|numeric|min:0',
            'pembeli'             => 'required|string|max:255',
        ]);

        $validated['total'] = $validated['berat_gram'] * $validated['harga_jual_per_gram'];

        $barangKeluar->update($validated);

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
        $barangKeluar->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.penjualan-pengepul.index')
            ->with('success', 'Transaksi penjualan berhasil dihapus.');
    }
}
