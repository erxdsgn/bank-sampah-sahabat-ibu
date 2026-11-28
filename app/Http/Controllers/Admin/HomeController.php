<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use App\Support\RingkasanKeuangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class HomeController extends Controller
{
    private function grupSatuan(?string $satuan): string
    {
        $satuan = strtolower(trim((string) $satuan));
        if (in_array($satuan, ['gram', 'gr', 'g'], true)) {
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

    private function konversi(float $nilai, string $grup): float
    {
        return $nilai;
    }

    private function konversiDariSatuanAsli(float $nilai, ?string $satuanAsli): float
    {
        $satuan = strtolower(trim((string) $satuanAsli));
        if (in_array($satuan, ['gram', 'gr', 'g'], true)) {
            return $nilai / 1000;
        }
        return $nilai;
    }

    private function ekspresiSatuanDetail(): string
    {
        return "COALESCE(NULLIF(TRIM(detail_setoran.satuan), ''), kategori_sampah.satuan)";
    }

    private function ekspresiJumlahDetail(): string
    {
        return "COALESCE(detail_setoran.jumlah, detail_setoran.berat, 0)";
    }

    private function formatJumlah(float $nilai, string $grup): string
    {
        $tampil = $this->konversi($nilai, $grup);
        $angka = $grup === 'kg'
            ? number_format($tampil, 2, ',', '.')
            : rtrim(rtrim(number_format($tampil, 2, ',', '.'), '0'), ',');
        return $angka . ' ' . $this->labelSatuan($grup);
    }

    private function hitungSaldoKas(): float
    {
        $transaksiKeuangan = RingkasanKeuangan::transaksi();
        $totalPemasukan   = (float) $transaksiKeuangan->where('jenis', 'Pemasukan')->sum('jumlah');
        $totalPengeluaran = (float) $transaksiKeuangan->where('jenis', 'Pengeluaran')->sum('jumlah');
        return $totalPemasukan - $totalPengeluaran;
    }

    /**
     * Filter query penyetoran yang sah masuk (tidak ditolak/dibatalkan).
     * Sesuaikan jika di aplikasi Anda hanya status tertentu yang dianggap masuk stok.
     */
    private function applySetoranMasukValid($query)
    {
        return $query->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(penyetoran.status, "")))'), [
            'ditolak',
            'rejected',
            'batal',
            'dibatalkan',
        ]);
    }

    private function hitungStats(): array
    {
        // Hanya menghitung warga aktif sesuai scopeAktif() pada model Warga
        $jumlahWargaAktif = (int) Warga::aktif()->count();

        return [
            'jumlahWarga'            => $jumlahWargaAktif,
            'jumlahSetoran'          => (int) DB::table('penyetoran')->count(),
            'totalSaldoWarga'        => (float) DB::table('warga')->sum('saldo'),
            'totalPencairanSaldo'    => (float) DB::table('pencairan_saldo')->sum('jumlah'),
            'totalPenjualanPengepul' => (float) DB::table('barang_keluar')->sum('total'),
            'jumlahKategoriSampah'   => (int) DB::table('kategori_sampah')->count(),
        ];
    }

    /**
     * Total sampah masuk per grup satuan dalam rentang bulan & tahun tertentu.
     */
    private function hitungSampahMasukRentang(int $bulanAwal, int $bulanAkhir, int $tahun): array
    {
        $satuanExpr = $this->ekspresiSatuanDetail();
        $jumlahExpr = $this->ekspresiJumlahDetail();

        $query = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereYear('penyetoran.tanggal_setoran', $tahun)
            ->whereBetween(DB::raw('MONTH(penyetoran.tanggal_setoran)'), [$bulanAwal, $bulanAkhir]);

        $this->applySetoranMasukValid($query);

        $grouped = $query
            ->select(
                DB::raw("{$satuanExpr} as satuan_aktual"),
                DB::raw("SUM({$jumlahExpr}) as total")
            )
            ->groupBy(DB::raw($satuanExpr))
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual))
            ->sortBy(fn($rows, $grup) => $grup === 'kg' ? '0' : $grup);

        $result = [];
        foreach ($grouped as $grup => $rows) {
            $totalGrup = collect($rows)->sum(
                fn($r) => $this->konversiDariSatuanAsli((float) $r->total, $r->satuan_aktual)
            );
            $result[(string) $grup] = round($this->konversi((float) $totalGrup, (string) $grup), 2);
        }

        return $result;
    }

    /**
     * Total sampah tersedia (dapat diolah / dijual ke pengepul) per grup satuan:
     * Total sampah masuk valid (seluruh waktu) dikurangi total berat barang keluar ke pengepul.
     */
    private function hitungSampahTersediaPerSatuan(): array
    {
        $satuanExpr = $this->ekspresiSatuanDetail();
        $jumlahExpr = $this->ekspresiJumlahDetail();

        $masukQuery = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori');

        $this->applySetoranMasukValid($masukQuery);

        $masukPerGrup = $masukQuery
            ->select(
                DB::raw("{$satuanExpr} as satuan_aktual"),
                DB::raw("SUM({$jumlahExpr}) as total")
            )
            ->groupBy(DB::raw($satuanExpr))
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual))
            ->map(fn($rows) => (float) collect($rows)->sum(
                fn($r) => $this->konversiDariSatuanAsli((float) $r->total, $r->satuan_aktual)
            ));

        $keluarPerGrup = DB::table('barang_keluar')
            ->join('kategori_sampah', 'barang_keluar.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->select('kategori_sampah.satuan as satuan_aktual', DB::raw('SUM(barang_keluar.berat) as total'))
            ->groupBy('kategori_sampah.satuan')
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual))
            ->map(fn($rows) => (float) collect($rows)->sum(
                fn($r) => $this->konversiDariSatuanAsli((float) $r->total, $r->satuan_aktual)
            ));

        $semuaGrup = $masukPerGrup->keys()
            ->merge($keluarPerGrup->keys())
            ->unique()
            ->sortBy(fn($grup) => $grup === 'kg' ? '0' : $grup);

        $tersedia = [];
        foreach ($semuaGrup as $grup) {
            $grupKey = (string) $grup;
            $sisa = (float) ($masukPerGrup[$grupKey] ?? 0) - (float) ($keluarPerGrup[$grupKey] ?? 0);
            $tersedia[$grupKey] = round($this->konversi(max(0.0, $sisa), $grupKey), 2);
        }

        return $tersedia;
    }

    private function hitungTotalPerGrup(): array
    {
        $satuanExpr = $this->ekspresiSatuanDetail();
        $jumlahExpr = $this->ekspresiJumlahDetail();

        $query = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori');

        $this->applySetoranMasukValid($query);

        $grouped = $query
            ->select(
                DB::raw("{$satuanExpr} as satuan_aktual"),
                DB::raw("SUM({$jumlahExpr}) as total")
            )
            ->groupBy(DB::raw($satuanExpr))
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual))
            ->sortBy(fn($rows, $grup) => $grup === 'kg' ? '0' : $grup);

        $result = [];
        foreach ($grouped as $grup => $rows) {
            $result[(string) $grup] = collect($rows)
                ->map(fn($r) => [
                    'satuan' => isset($r->satuan_aktual) ? (string) $r->satuan_aktual : null,
                    'total'  => round($this->konversiDariSatuanAsli((float) ($r->total ?? 0), $r->satuan_aktual), 2),
                ])
                ->values()
                ->all();
        }

        return $result;
    }

    /**
     * Rekap bulanan seluruh bulan (1-12) untuk tahun tertentu.
     */
    private function hitungSetoranPerBulan(int $tahun): array
    {
        $satuanExpr = $this->ekspresiSatuanDetail();
        $jumlahExpr = $this->ekspresiJumlahDetail();

        $query = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereYear('penyetoran.tanggal_setoran', $tahun);

        $this->applySetoranMasukValid($query);

        $rowsAll = $query
            ->select(
                DB::raw("{$satuanExpr} as satuan_aktual"),
                DB::raw('MONTH(penyetoran.tanggal_setoran) as bulan'),
                DB::raw("SUM({$jumlahExpr}) as total")
            )
            ->groupBy(DB::raw($satuanExpr), DB::raw('MONTH(penyetoran.tanggal_setoran)'))
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual));

        $result = [];
        foreach ($rowsAll as $grup => $rows) {
            $grupKey = (string) $grup;
            $perBulan = [];
            foreach (collect($rows)->groupBy('bulan') as $bulan => $items) {
                $totalBulan = collect($items)->sum(
                    fn($r) => $this->konversiDariSatuanAsli((float) $r->total, $r->satuan_aktual)
                );
                $perBulan[] = [
                    'bulan' => (int) $bulan,
                    'total' => round($this->konversi((float) $totalBulan, $grupKey), 2),
                ];
            }
            $result[$grupKey] = $perBulan;
        }

        return $result;
    }

    /**
     * Statistik jenis sampah untuk tahun tertentu dan opsi filter bulan (0 = seluruh tahun, 1..12 = bulan spesifik).
     */
    private function hitungStatistikJenisSampah(int $tahun, int $bulan = 0): array
    {
        $satuanExpr = $this->ekspresiSatuanDetail();
        $jumlahExpr = $this->ekspresiJumlahDetail();

        $query = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereYear('penyetoran.tanggal_setoran', $tahun);

        $this->applySetoranMasukValid($query);

        if ($bulan >= 1 && $bulan <= 12) {
            $query->whereMonth('penyetoran.tanggal_setoran', $bulan);
        }

        $rowsAll = $query
            ->select(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori',
                DB::raw("{$satuanExpr} as satuan_aktual"),
                DB::raw("SUM({$jumlahExpr}) as total")
            )
            ->groupBy(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori',
                DB::raw($satuanExpr)
            )
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual));

        $result = [];
        foreach ($rowsAll as $grup => $rows) {
            $grupKey = (string) $grup;
            $items = collect($rows)
                ->groupBy('id_kategori')
                ->map(function ($katRows) use ($grupKey) {
                    $first = $katRows->first();
                    $totalKat = $katRows->sum(
                        fn($r) => $this->konversiDariSatuanAsli((float) ($r->total ?? 0), $r->satuan_aktual)
                    );
                    return [
                        'nama'  => (string) ($first->nama_kategori ?? 'Tanpa nama'),
                        'total' => round($this->konversi((float) $totalKat, $grupKey), 2),
                    ];
                })
                ->sortByDesc('total')
                ->values()
                ->all();

            $result[$grupKey] = $items;
        }

        return $result;
    }

    private function daftarTahunTersedia(): array
    {
        $tahunSetoran = DB::table('penyetoran')
            ->whereNotNull('tanggal_setoran')
            ->selectRaw('DISTINCT YEAR(tanggal_setoran) as tahun')
            ->pluck('tahun')
            ->filter()
            ->map(fn($t) => (int) $t);

        $tahunKeluar = DB::table('barang_keluar')
            ->whereNotNull('tanggal')
            ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
            ->pluck('tahun')
            ->filter()
            ->map(fn($t) => (int) $t);

        $tahunAktif = (int) now('Asia/Jakarta')->year;

        return $tahunSetoran
            ->merge($tahunKeluar)
            ->push($tahunAktif)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    public function index(Request $request): View
    {
        $tahunSekarang = (int) now('Asia/Jakarta')->year;
        $bulanSekarang = (int) now('Asia/Jakarta')->month;

        $bulanAwal  = max(1, min(12, (int) $request->input('bulan_awal', 1)));
        $bulanAkhir = max(1, min(12, (int) $request->input('bulan_akhir', min(12, max($bulanAwal, $bulanSekarang)))));
        if ($bulanAwal > $bulanAkhir) {
            [$bulanAwal, $bulanAkhir] = [$bulanAkhir, $bulanAwal];
        }

        $tahunDipilih = (int) $request->input('tahun', $tahunSekarang);
        if ($tahunDipilih < 2000 || $tahunDipilih > 2100) {
            $tahunDipilih = $tahunSekarang;
        }

        $bulanStatistik = max(0, min(12, (int) $request->input('bulan_statistik', 0)));
        $daftarTahun = $this->daftarTahunTersedia();

        // ===== CACHE: saldo kas 5 menit =====
        $rawSaldoKas = cache()->remember('dash.saldo_kas', 300, fn() => $this->hitungSaldoKas());
        if (!is_numeric($rawSaldoKas)) {
            cache()->forget('dash.saldo_kas');
            $rawSaldoKas = $this->hitungSaldoKas();
        }
        $saldoKas = (float) $rawSaldoKas;

        // ===== CACHE: statistik ringkasan 5 menit =====
        $stats = cache()->remember('dash.stats.aktif', 300, fn() => $this->hitungStats());
        if (!is_array($stats) || empty($stats)) {
            cache()->forget('dash.stats.aktif');
            $stats = $this->hitungStats();
        }

        $jumlahWarga            = (int) ($stats['jumlahWarga'] ?? 0);
        $jumlahSetoran          = (int) ($stats['jumlahSetoran'] ?? 0);
        $totalSaldoWarga        = (float) ($stats['totalSaldoWarga'] ?? 0);
        $totalPencairanSaldo    = (float) ($stats['totalPencairanSaldo'] ?? 0);
        $totalPenjualanPengepul = (float) ($stats['totalPenjualanPengepul'] ?? 0);
        $jumlahKategoriSampah   = (int) ($stats['jumlahKategoriSampah'] ?? 0);

        $urutGrup = fn($rows, $grup) => $grup === 'kg' ? '0' : $grup;

        // ===== CACHE: total per satuan 5 menit =====
        $rawTotalPerGrup = cache()->remember('dash.total_per_grup_v2', 300, fn() => $this->hitungTotalPerGrup());
        $isValidTotalPerGrup = is_array($rawTotalPerGrup)
            && collect($rawTotalPerGrup)->every(fn($rows) => is_array($rows) && collect($rows)->every(fn($r) => is_array($r)));

        if (!$isValidTotalPerGrup) {
            cache()->forget('dash.total_per_grup_v2');
            $rawTotalPerGrup = $this->hitungTotalPerGrup();
        }

        $totalPerGrup = collect(is_array($rawTotalPerGrup) ? $rawTotalPerGrup : [])
            ->map(fn($rows) => collect(is_array($rows) ? $rows : []));

        $totalJumlahSampahPerSatuan = $totalPerGrup
            ->mapWithKeys(fn($rows, $grup) => [(string) $grup => $this->konversi((float) $rows->sum('total'), (string) $grup)])
            ->all();

        // Sampah masuk dalam rentang bulan_awal..bulan_akhir pada tahunDipilih
        $sampahMasukRentangPerSatuan = $this->hitungSampahMasukRentang($bulanAwal, $bulanAkhir, $tahunDipilih);

        // Sampah tersedia (dapat diolah / dijual ke pengepul)
        $sampahTersediaPerSatuan = $this->hitungSampahTersediaPerSatuan();

        $semuaKeySatuan = collect(array_keys($totalJumlahSampahPerSatuan))
            ->merge(array_keys($sampahMasukRentangPerSatuan))
            ->merge(array_keys($sampahTersediaPerSatuan))
            ->unique()
            ->sortBy(fn($grup) => $grup === 'kg' ? '0' : $grup)
            ->values();

        $opsiSatuan = $semuaKeySatuan
            ->map(fn($grup) => ['key' => (string) $grup, 'label' => $this->labelSatuan((string) $grup)]);

        if ($opsiSatuan->isEmpty()) {
            $opsiSatuan = collect([['key' => 'kg', 'label' => $this->labelSatuan('kg')]]);
            $totalJumlahSampahPerSatuan = ['kg' => 0];
            $sampahMasukRentangPerSatuan = ['kg' => 0];
            $sampahTersediaPerSatuan = ['kg' => 0];
        }

        // ===== Riwayat setoran =====
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

        $satuanExpr = $this->ekspresiSatuanDetail();
        $jumlahExpr = $this->ekspresiJumlahDetail();

        $detailPerSetoran = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereIn('detail_setoran.id_setoran', $riwayatSetoran->pluck('id_setoran'))
            ->select(
                'detail_setoran.id_setoran',
                DB::raw("{$satuanExpr} as satuan_aktual"),
                DB::raw("SUM({$jumlahExpr}) as total")
            )
            ->groupBy('detail_setoran.id_setoran', DB::raw($satuanExpr))
            ->get()
            ->groupBy('id_setoran');

        $riwayatSetoran->transform(function ($setoran) use ($detailPerSetoran, $urutGrup) {
            $setoran->jumlah_text = ($detailPerSetoran[$setoran->id_setoran] ?? collect())
                ->groupBy(fn($r) => $this->grupSatuan($r->satuan_aktual))
                ->sortBy($urutGrup)
                ->map(function ($rows, $grup) {
                    $total = collect($rows)->sum(
                        fn($r) => $this->konversiDariSatuanAsli((float) $r->total, $r->satuan_aktual)
                    );
                    return $this->formatJumlah($total, $grup);
                })
                ->implode(', ') ?: '-';
            return $setoran;
        });

        // Data grafik bulanan (tahunDipilih) & statistik jenis sampah (tahunDipilih + bulanStatistik)
        $setoranPerBulan = $this->hitungSetoranPerBulan($tahunDipilih);
        $statistikJenisSampah = $this->hitungStatistikJenisSampah($tahunDipilih, $bulanStatistik);

        return view('admin.pages.dashboard', compact(
            'saldoKas',
            'jumlahWarga',
            'jumlahSetoran',
            'totalJumlahSampahPerSatuan',
            'sampahMasukRentangPerSatuan',
            'sampahTersediaPerSatuan',
            'bulanAwal',
            'bulanAkhir',
            'tahunDipilih',
            'bulanStatistik',
            'daftarTahun',
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

    public function warga(): View
    {
        $warga = Warga::orderBy('id_warga', 'desc')->get();
        return view('admin.pages.warga', compact('warga'));
    }

    public function pengaturan(Request $request): View
    {
        $primaryId = Auth::id();
        $userRecord  = DB::table('users')->where('id', $primaryId)->first();
        $adminRecord = DB::table('admin')->where('id_admin', $primaryId)->first();

        $admin = (object) [
            'id_admin' => $adminRecord->id_admin ?? $primaryId,
            'nama'     => $adminRecord->nama ?? '',
            'username' => $userRecord->username ?? '',
            'alamat'   => $adminRecord->alamat ?? '',
        ];

        $sessions = DB::table('sessions')
            ->where('user_id', $primaryId)
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

    public function updateProfil(Request $request): RedirectResponse
    {
        $primaryId = Auth::id();

        $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($primaryId, 'id'),
            ],
            'alamat'   => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $userData = ['username' => $request->username];
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        DB::table('users')->where('id', $primaryId)->update($userData);

        $adminUpdated = DB::table('admin')
            ->where('id_admin', $primaryId)
            ->update([
                'nama'   => $request->nama,
                'alamat' => $request->alamat,
            ]);

        if (!$adminUpdated) {
            DB::table('admin')->insert([
                'id_admin' => $primaryId,
                'nama'     => $request->nama,
                'alamat'   => $request->alamat,
            ]);
        }

        return back()->with('success', 'Profil admin dan password berhasil diperbarui.');
    }

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
