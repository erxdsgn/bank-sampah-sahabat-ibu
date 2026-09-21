<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\Setoran;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // ==========================================
        // 1. RINGKASAN STATISTIK
        // ==========================================

        $totalPenjualan = BarangKeluar::sum('total');

        // berat_gram dikonversi ke Kg
        $totalBeratKeluar = BarangKeluar::sum('berat_gram') / 1000;

        $totalTransaksiKeluar = BarangKeluar::count();


        // ==========================================
        // 2. RIWAYAT PENJUALAN
        // ==========================================

        $queryPenjualan = BarangKeluar::with([
            'kategori',
            'admin'
        ]);

        if ($request->filled('search_penjualan')) {

            $queryPenjualan->where(
                'pembeli',
                'like',
                '%' . $request->search_penjualan . '%'
            );

        }

        $riwayatPenjualan = $queryPenjualan
            ->latest('tanggal')
            ->paginate(10, ['*'], 'penjualan_page');


        // ==========================================
        // 3. RIWAYAT PENYETORAN WARGA
        // ==========================================

        $querySetoran = Setoran::with([
            'warga',
            'details.kategori'
        ]);

        if ($request->filled('search_setoran')) {

            $keyword = $request->search_setoran;

            $querySetoran->where(function ($q) use ($keyword) {

                $q->where('kode_transaksi', 'like', '%' . $keyword . '%')

                    ->orWhereHas('warga', function ($q) use ($keyword) {

                        $q->where('nama', 'like', '%' . $keyword . '%')
                          ->orWhere('nik', 'like', '%' . $keyword . '%');

                    });

            });
        }

        $riwayatSetoran = $querySetoran
            ->latest('tanggal_setoran')
            ->paginate(10, ['*'], 'setoran_page');


        // ==========================================
        // 4. KIRIM KE VIEW
        // ==========================================

        return view('admin.pages.laporan', compact(
            'totalPenjualan',
            'totalBeratKeluar',
            'totalTransaksiKeluar',
            'riwayatPenjualan',
            'riwayatSetoran'
        ));
    }
}
