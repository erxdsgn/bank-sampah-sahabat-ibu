<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Warga;
use App\Support\RingkasanKeuangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class HomeController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function index(): View
    {
        // Perhitungan Saldo Kas — pakai sumber yang SAMA dengan halaman Keuangan
        // (kas manual + setoran + pencairan saldo + penjualan ke pengepul),
        // supaya angkanya tidak pernah menyimpang dari admin.pages.keuangan.
        $transaksiKeuangan = RingkasanKeuangan::transaksi();

        $totalPemasukan   = (float) $transaksiKeuangan->where('jenis', 'Pemasukan')->sum('jumlah');
        $totalPengeluaran = (float) $transaksiKeuangan->where('jenis', 'Pengeluaran')->sum('jumlah');
        $saldoKas         = $totalPemasukan - $totalPengeluaran;

        // Stat ringkasan
        $jumlahWarga            = DB::table('warga')->count();
        $jumlahSetoran          = DB::table('penyetoran')->count();
        $totalBeratSampah       = DB::table('penyetoran')->sum('total_berat');
        $totalSaldoWarga        = DB::table('warga')->sum('saldo');
        $totalPencairanSaldo    = DB::table('pencairan_saldo')->sum('jumlah');
        $totalPenjualanPengepul = DB::table('barang_keluar')->sum('total');
        $jumlahKategoriSampah   = DB::table('kategori_sampah')->count();

        // Riwayat Setoran
        $riwayatSetoran = DB::table('penyetoran')
            ->leftJoin('warga', 'penyetoran.id_warga', '=', 'warga.id_warga')
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

        // Grafik Setoran Bulanan
        $setoranPerBulan = DB::table('penyetoran')
            ->select(
                DB::raw('MONTH(tanggal_setoran) as bulan'),
                DB::raw('SUM(total_berat) as total_berat')
            )
            ->whereYear('tanggal_setoran', now()->year)
            ->groupBy(DB::raw('MONTH(tanggal_setoran)'))
            ->orderBy(DB::raw('MONTH(tanggal_setoran)'))
            ->get();

        // Statistik Kategori Sampah
        $statistikJenisSampah = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->select(
                'kategori_sampah.nama_kategori',
                DB::raw('SUM(detail_setoran.berat_gram) as total_berat')
            )
            ->groupBy('kategori_sampah.id_kategori', 'kategori_sampah.nama_kategori')
            ->orderByDesc('total_berat')
            ->get();

        return view('admin.pages.dashboard', compact(
            'saldoKas',
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
     * Tampilan Data Warga
     */
    public function warga(): View
    {
        $warga = Warga::orderBy('id_warga', 'desc')->get();

        return view('admin.pages.warga', compact('warga'));
    }

    /**
     * Tampilan Pengaturan Admin & Sesi Perangkat Login
     */
    public function pengaturan(Request $request): View
    {
        // 1. Ambil data Admin yang sedang login
        $admin = Auth::user();

        // 2. Ambil ID admin (fallback ke id_admin atau id)
        $adminId = $admin->id_admin ?? $admin->id ?? Auth::id();

        // 3. Ambil data sesi perangkat login user dari tabel 'sessions'
        $sessions = DB::table('sessions')
            ->where('user_id', $adminId)
            ->orderBy('last_activity', 'desc')
            ->get();

        $devices = $sessions->map(function ($session) use ($request) {
            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);

            return (object) [
                'id'                => $session->id,
                'ip_address'        => $session->ip_address,
                'platform'          => $agent->platform(),
                'browser'           => $agent->browser(),
                'is_desktop'        => $agent->isDesktop(),
                'last_activity'     => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'is_current_device' => $session->id === $request->session()->getId(),
            ];
        });

        return view('admin.pages.pengaturan', compact('admin', 'devices'));
    }

    /**
     * Update Profil Admin
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50'],
            'alamat'   => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        /** @var Admin $admin */
        $admin = Auth::user();

        $data = [
            'nama'     => $request->nama,
            'username' => $request->username,
            'alamat'   => $request->alamat,
        ];

        // Password hanya diperbarui jika diisi
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $admin->update($data);

        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }


    /**
     * Form Kontak
     */
    public function storeContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        return back()
            ->with('status', 'Pesan Anda sudah terkirim. Terima kasih!')
            ->withInput($data);
    }
}
