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

    private function konversi(float $nilai, string $grup): float
    {
        // ✅ DIPERBAIKI: Data di database sudah berupa angka asli (kg/liter),
        // jadi tidak perlu dibagi 1000 lagi.
        return $nilai;
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

    private function hitungStats(): array
    {
        return [
            'jumlahWarga'            => (int) DB::table('warga')->count(),
            'jumlahSetoran'          => (int) DB::table('penyetoran')->count(),
            'totalSaldoWarga'        => (float) DB::table('warga')->sum('saldo'),
            'totalPencairanSaldo'    => (float) DB::table('pencairan_saldo')->sum('jumlah'),
            'totalPenjualanPengepul' => (float) DB::table('barang_keluar')->sum('total'),
            'jumlahKategoriSampah'   => (int) DB::table('kategori_sampah')->count(),
        ];
    }

    private function hitungTotalPerGrup(): array
    {
        $grouped = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->select('kategori_sampah.satuan', DB::raw('SUM(detail_setoran.berat) as total'))
            ->groupBy('kategori_sampah.satuan')
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan))
            ->sortBy(fn($rows, $grup) => $grup === 'kg' ? '0' : $grup);

        $result = [];
        foreach ($grouped as $grup => $rows) {
            $result[(string) $grup] = collect($rows)
                ->map(fn($r) => [
                    'satuan' => isset($r->satuan) ? (string) $r->satuan : null,
                    'total'  => (float) ($r->total ?? 0),
                ])
                ->values()
                ->all();
        }

        return $result;
    }

    private function hitungSetoranPerBulan(): array
    {
        $rowsAll = DB::table('detail_setoran')
            ->join('penyetoran', 'detail_setoran.id_setoran', '=', 'penyetoran.id_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->whereYear('penyetoran.tanggal_setoran', now()->year)
            ->select(
                'kategori_sampah.satuan',
                DB::raw('MONTH(penyetoran.tanggal_setoran) as bulan'),
                DB::raw('SUM(detail_setoran.berat) as total')
            )
            ->groupBy('kategori_sampah.satuan', DB::raw('MONTH(penyetoran.tanggal_setoran)'))
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan));

        $result = [];
        foreach ($rowsAll as $grup => $rows) {
            $grupKey = (string) $grup;
            $perBulan = [];
            foreach (collect($rows)->groupBy('bulan') as $bulan => $items) {
                $perBulan[] = [
                    'bulan' => (int) $bulan,
                    'total' => $this->konversi((float) collect($items)->sum('total'), $grupKey),
                ];
            }
            $result[$grupKey] = $perBulan;
        }

        return $result;
    }

    private function hitungStatistikJenisSampah(): array
    {
        $rowsAll = DB::table('detail_setoran')
            ->join('kategori_sampah', 'detail_setoran.id_kategori', '=', 'kategori_sampah.id_kategori')
            ->select(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori',
                'kategori_sampah.satuan',
                DB::raw('SUM(detail_setoran.berat) as total')
            )
            ->groupBy(
                'kategori_sampah.id_kategori',
                'kategori_sampah.nama_kategori',
                'kategori_sampah.satuan'
            )
            ->get()
            ->groupBy(fn($r) => $this->grupSatuan($r->satuan));

        $result = [];
        foreach ($rowsAll as $grup => $rows) {
            $grupKey = (string) $grup;
            $items = collect($rows)
                ->map(fn($r) => [
                    'nama'  => (string) ($r->nama_kategori ?? 'Tanpa nama'),
                    'total' => $this->konversi((float) ($r->total ?? 0), $grupKey),
                ])
                ->sortByDesc('total')
                ->values()
                ->all();

            $result[$grupKey] = $items;
        }

        return $result;
    }

    public function index(): View
    {
        // ===== CACHE: saldo kas 5 menit =====
        $rawSaldoKas = cache()->remember('dash.saldo_kas', 300, fn() => $this->hitungSaldoKas());
        if (!is_numeric($rawSaldoKas)) {
            cache()->forget('dash.saldo_kas');
            $rawSaldoKas = $this->hitungSaldoKas();
        }
        $saldoKas = (float) $rawSaldoKas;

        // ===== CACHE: statistik ringkasan 5 menit =====
        $stats = cache()->remember('dash.stats', 300, fn() => $this->hitungStats());
        if (!is_array($stats) || empty($stats)) {
            cache()->forget('dash.stats');
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
        $rawTotalPerGrup = cache()->remember('dash.total_per_grup', 300, fn() => $this->hitungTotalPerGrup());
        $isValidTotalPerGrup = is_array($rawTotalPerGrup)
            && collect($rawTotalPerGrup)->every(fn($rows) => is_array($rows) && collect($rows)->every(fn($r) => is_array($r)));

        if (!$isValidTotalPerGrup) {
            cache()->forget('dash.total_per_grup');
            $rawTotalPerGrup = $this->hitungTotalPerGrup();
        }

        $totalPerGrup = collect(is_array($rawTotalPerGrup) ? $rawTotalPerGrup : [])
            ->map(fn($rows) => collect(is_array($rows) ? $rows : []));

        $totalJumlahSampah = $totalPerGrup
            ->map(fn($rows, $grup) => $this->formatJumlah((float) $rows->sum('total'), (string) $grup))
            ->values();

        $totalJumlahSampahPerSatuan = $totalPerGrup
            ->mapWithKeys(fn($rows, $grup) => [(string) $grup => $this->konversi((float) $rows->sum('total'), (string) $grup)])
            ->all();

        $opsiSatuan = $totalPerGrup
            ->keys()
            ->map(fn($grup) => ['key' => (string) $grup, 'label' => $this->labelSatuan((string) $grup)])
            ->values();

        if ($opsiSatuan->isEmpty()) {
            $opsiSatuan = collect([['key' => 'kg', 'label' => $this->labelSatuan('kg')]]);
            $totalJumlahSampahPerSatuan = ['kg' => 0];
        }

        // ===== Riwayat setoran (tidak di-cache karena butuh real-time) =====
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
                DB::raw('SUM(detail_setoran.berat) as total')
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

        // ===== CACHE: grafik bulanan 5 menit =====
        $setoranPerBulan = cache()->remember('dash.setoran_per_bulan', 300, fn() => $this->hitungSetoranPerBulan());
        $isValidSetoranPerBulan = is_array($setoranPerBulan)
            && collect($setoranPerBulan)->every(fn($items) => is_array($items) && collect($items)->every(fn($row) => is_array($row)));

        if (!$isValidSetoranPerBulan) {
            cache()->forget('dash.setoran_per_bulan');
            $setoranPerBulan = $this->hitungSetoranPerBulan();
        }

        // ===== CACHE: statistik jenis 5 menit =====
        $statistikJenisSampah = cache()->remember('dash.statistik_jenis', 300, fn() => $this->hitungStatistikJenisSampah());
        $isValidStatistikJenis = is_array($statistikJenisSampah)
            && collect($statistikJenisSampah)->every(fn($items) => is_array($items) && collect($items)->every(fn($row) => is_array($row)));

        if (!$isValidStatistikJenis) {
            cache()->forget('dash.statistik_jenis');
            $statistikJenisSampah = $this->hitungStatistikJenisSampah();
        }

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
