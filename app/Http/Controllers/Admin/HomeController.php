<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | STATISTIK DASHBOARD
        |--------------------------------------------------------------------------
        */

        // Jumlah warga terdaftar
        $jumlahWarga = DB::table('warga')->count();

        // Jumlah transaksi / setoran
        $jumlahSetoran = DB::table('penyetoran')->count();

        // Total berat sampah
        $totalBeratSampah = DB::table('penyetoran')->sum('total_berat');

        // Total saldo seluruh warga
        $totalSaldoWarga = DB::table('warga')->sum('saldo');

        // Total pencairan saldo
        $totalPencairanSaldo = DB::table('pencairan_saldo')
            ->sum('jumlah');

        // Total penjualan ke pengepul
        $totalPenjualanPengepul = DB::table('barang_keluar')
            ->sum('total');

        // Jumlah kategori sampah
        $jumlahKategoriSampah = DB::table('kategori_sampah')->count();


        /*
        |--------------------------------------------------------------------------
        | RIWAYAT SETORAN
        |--------------------------------------------------------------------------
        */

        $riwayatSetoran = DB::table('penyetoran')
            ->leftJoin(
                'warga',
                'penyetoran.id_warga',
                '=',
                'warga.id_warga'
            )
            ->select(
                'penyetoran.id_setoran',
                'penyetoran.tanggal_setoran',
                'penyetoran.status',
                'penyetoran.total_berat',
                'penyetoran.total_nilai',
                'warga.nama'
            )
            ->orderBy('penyetoran.tanggal_setoran', 'desc')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GRAFIK SETORAN PER BULAN
        |--------------------------------------------------------------------------
        */

        $setoranPerBulan = DB::table('penyetoran')
            ->select(
                DB::raw('MONTH(tanggal_setoran) as bulan'),
                DB::raw('SUM(total_berat) as total_berat')
            )
            ->whereYear('tanggal_setoran', now()->year)
            ->groupBy(DB::raw('MONTH(tanggal_setoran)'))
            ->orderBy(DB::raw('MONTH(tanggal_setoran)'))
            ->get();


        /*
        |--------------------------------------------------------------------------
        | STATISTIK JENIS SAMPAH
        |--------------------------------------------------------------------------
        */

        $statistikJenisSampah = DB::table('detail_setoran')
            ->join(
                'kategori_sampah',
                'detail_setoran.id_kategori',
                '=',
                'kategori_sampah.id_kategori'
            )
            ->select(
                'kategori_sampah.nama_kategori',
                DB::raw('SUM(detail_setoran.berat_kg) as total_berat')
            )
            ->groupBy(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori'
            )
            ->orderByDesc('total_berat')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | KIRIM DATA KE VIEW
        |--------------------------------------------------------------------------
        */

        return view('admin.pages.dashboard', compact(
            'jumlahWarga',
            'jumlahSetoran',
            'totalBeratSampah',
            'totalSaldoWarga',
            'totalPencairanSaldo',
            'totalPenjualanPengepul',
            'jumlahKategoriSampah',
            'riwayatSetoran',
            'setoranPerBulan',
            'statistikJenisSampah'
        ));
    }


    /**
     * Tampilkan data warga.
     */
    public function warga(): View
    {
        $warga = Warga::orderBy('id_warga', 'desc')->get();

        return view('admin.pages.warga', compact('warga'));
    }


    /**
     * Terima kiriman form kontak.
     */
    public function storeContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // TODO: simpan ke database atau kirim email.

        return back()
            ->with('status', 'Pesan Anda sudah terkirim. Terima kasih!')
            ->withInput($data);
    }
}
