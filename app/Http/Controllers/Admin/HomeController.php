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
     * kg & gram digabung menjadi satu grup "kg".
     */
    private function grupSatuan(?string $satuan): string
    {
        $satuan = strtolower(trim((string) $satuan));

        if ($satuan === 'gram') {
            return 'kg';
        }

        return $satuan !== '' ? $satuan : 'lainnya';
    }

    private function labelSatuan(string $grup): string
    {
        return [
            'kg'    => 'Kg',
            'pcs'   => 'Pcs',
            'liter' => 'Liter',
            'unit'  => 'Unit',
            'set'   => 'Set',
        ][$grup] ?? ucfirst($grup);
    }

    /**
     * Nilai mentah (kolom berat_gram) -> nilai tampil.
     * Berat: gram -> Kg. Satuan lain: apa adanya.
     */
    private function konversi(float $nilai, string $grup): float
    {
        return $grup === 'kg' ? $nilai / 1000 : $nilai;
    }

    private function formatJumlah(float $nilai, string $grup): string
    {
        $tampil = $this->konversi($nilai, $grup);

        $angka = $grup === 'kg'
            ? number_format($tampil, 2, ',', '.')
            : rtrim(rtrim(number_format($tampil, 2, ',', '.'), '0'), ',');

        return $angka . ' ' . $this->labelSatuan($grup);
    }

    /**
     * Dashboard Admin
     */
    public function index(): View
    {
        // Saldo Kas — sumber sama dengan halaman Keuangan
        $transaksiKeuangan = RingkasanKeuangan::transaksi();

        $totalPemasukan   = (float) $transaksiKeuangan->where('jenis', 'Pemasukan')->sum('jumlah');
        $totalPengeluaran = (float) $transaksiKeuangan->where('jenis', 'Pengeluaran')->sum('jumlah');
        $saldoKas         = $totalPemasukan - $totalPengeluaran;

        // Stat ringkasan
        $jumlahWarga            = DB::table('warga')->count();
        $jumlahSetoran          = DB::table('penyetoran')->count();
        $totalSaldoWarga        = DB::table('warga')->sum('saldo');
        $totalPencairanSaldo    = DB::table('pencairan_saldo')->sum('jumlah');
        $totalPenjualanPengepul = DB::table('barang_keluar')->sum('total');
        $jumlahKategoriSampah   = DB::table('kategori_sampah')->count();

        // Urutkan grup satuan: kg dulu, sisanya alfabetis
        $urutGrup = fn($rows, $grup) => $grup === 'kg' ? '0' : $grup;

        // ---------------------------------------------------------
        // Total sampah disetor, per satuan
        // ---------------------------------------------------------
        $totalPerGrup = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->select('kategori_sampah.satuan', DB::raw('SUM(detail_setoran.berat_gram) as total'))
            ->groupBy('kategori_sampah.satuan')
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan))
            ->sortBy($urutGrup);

        $totalJumlahSampah = $totalPerGrup
            ->map(fn($rows, $grup) => $this->formatJumlah((float) $rows->sum('total'), $grup))
            ->values();

        // Data mentah nilai per satuan untuk kartu dropdown interaktif
        $totalJumlahSampahPerSatuan = $totalPerGrup
            ->map(fn($rows, $grup) => $this->konversi((float) $rows->sum('total'), $grup))
            ->all();

        // Opsi satuan untuk pilihan di grafik
        $opsiSatuan = $totalPerGrup
            ->keys()
            ->map(fn($grup) => ['key' => $grup, 'label' => $this->labelSatuan($grup)])
            ->values();
        // ---------------------------------------------------------
        // Riwayat Setoran
        // ---------------------------------------------------------
        $riwayatSetoran = DB::table('penyetoran')
            ->leftJoin('warga', 'penyetoran.id_warga', '=', 'warga.id_warga')
            ->select(
                'penyetoran.id_setoran',
                'penyetoran.tanggal_setoran',
                'penyetoran.status',
                'penyetoran.total_nilai',
                'warga.nama'
            )
            ->orderBy('penyetoran.tanggal_setoran', 'desc')
            ->orderBy('penyetoran.id_setoran', 'desc')
            ->limit(10)
            ->get();

        $detailPerSetoran = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereIn('detail_setoran.id_setoran', $riwayatSetoran->pluck('id_setoran'))
            ->select(
                'detail_setoran.id_setoran',
                'kategori_sampah.satuan',
                DB::raw('SUM(detail_setoran.berat_gram) as total')
            )
            ->groupBy('detail_setoran.id_setoran', 'kategori_sampah.satuan')
            ->get()
            ->groupBy('id_setoran');

        $riwayatSetoran->transform(function ($setoran) use ($detailPerSetoran, $urutGrup) {
            $setoran->jumlah_text = ($detailPerSetoran[$setoran->id_setoran] ?? collect())
                ->groupBy(fn($r) => $this->grupSatuan($r->satuan))
                ->sortBy($urutGrup)
                ->map(fn($rows, $grup) => $this->formatJumlah((float) $rows->sum('total'), $grup))
                ->implode(', ') ?: '-';

            return $setoran;
        });

        // ---------------------------------------------------------
        // Grafik bulanan: [grup => [{bulan, total}, ...]]
        // ---------------------------------------------------------
        $setoranPerBulan = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereYear('penyetoran.tanggal_setoran', now()->year)
            ->select(
                'kategori_sampah.satuan',
                DB::raw('MONTH(penyetoran.tanggal_setoran) as bulan'),
                DB::raw('SUM(detail_setoran.berat_gram) as total')
            )
            ->groupBy('kategori_sampah.satuan', DB::raw('MONTH(penyetoran.tanggal_setoran)'))
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan))
            ->map(
                fn($rows, $grup) => $rows
                    ->groupBy('bulan')
                    ->map(fn($r, $bulan) => [
                        'bulan' => (int) $bulan,
                        'total' => $this->konversi((float) $r->sum('total'), $grup),
                    ])
                    ->values()
            )
            ->all();

        // ---------------------------------------------------------
        // Statistik jenis sampah: [grup => [{nama, total}, ...]]
        // ---------------------------------------------------------
        $statistikJenisSampah = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->select(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori',
                'kategori_sampah.satuan',
                DB::raw('SUM(detail_setoran.berat_gram) as total')
            )
            ->groupBy(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori',
                'kategori_sampah.satuan'
            )
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan))
            ->map(
                fn($rows, $grup) => $rows
                    ->map(fn($r) => [
                        'nama'  => $r->nama_kategori,
                        'total' => $this->konversi((float) $r->total, $grup),
                    ])
                    ->sortByDesc('total')
                    ->values()
            )
            ->all();

        return view('admin.pages.dashboard', compact(
            'saldoKas',
            'jumlahWarga',
            'jumlahSetoran',
            'totalJumlahSampah',
            'totalJumlahSampahPerSatuan',
            'totalSaldoWarga',
            'totalPencairanSaldo',
            'totalPenjualanPengepul',
            'jumlahKategoriSampah',
            'riwayatSetoran',
            'setoranPerBulan',
            'statistikJenisSampah',
            'opsiSatuan'
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
        /** @var Admin $admin */
        $admin = Auth::user();

        $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:admin,username,' . $admin->id_admin . ',id_admin'],
            'alamat'   => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        // Update data dasar profil satu per satu
        $admin->nama     = $request->nama;
        $admin->username = $request->username;
        $admin->alamat   = $request->alamat;

        // Jika password diisi, tetapkan ke properti langsung agar casts 'hashed' aktif secara otomatis
        if ($request->filled('password')) {
            $admin->password = $request->password;
        }

        // Simpan menggunakan ->save() agar enkripsi Bcrypt otomatis diterapkan oleh model
        $admin->save();

        return back()->with('success', 'Profil admin dan password berhasil diperbarui.');
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
