<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WargaController extends Controller
{
    public function index()
    {
        $warga = Warga::orderBy('created_at', 'desc')->get();
        return view('admin.pages.warga', compact('warga'));
    }

    // === LEADERBOARD POPUP (RESET OTOMATIS SETIAP 3 BULAN / TRIWULAN) ===
    public function leaderboardPopup(Request $request)
    {
        $sekarang = Carbon::now();
        $periode = $request->query('periode', 'season_ini');

        $query = Warga::select(
            'warga.id_warga',
            'warga.nama'
        )
            ->join('penyetoran', 'warga.id_warga', '=', 'penyetoran.id_warga')
            ->join('detail_setoran', 'penyetoran.id_setoran', '=', 'detail_setoran.id_setoran')
            ->where('penyetoran.status', 'approved');

        if ($periode === 'season_ini') {
            $query->whereRaw(
                'QUARTER(penyetoran.tanggal_setoran) = ?',
                [$sekarang->quarter]
            )->whereYear(
                'penyetoran.tanggal_setoran',
                $sekarang->year
            );
        }

        $leaderboard = $query
            ->selectRaw('SUM(detail_setoran.poin) as total_poin')
            ->groupBy('warga.id_warga', 'warga.nama')
            ->orderByDesc('total_poin')
            ->take(10)
            ->get();

        return response()->json([
            'html' => view(
                'admin.partials.leaderboard-modal-content',
                compact('leaderboard', 'periode')
            )->render()
        ]);
    }

    public function create()
    {
        return view('admin.pages.form-warga');
    }

    public function store(Request $request)
    {
        if (Warga::where('status', 'aktif')->count() >= 100) {
            return response()->json([
                'message' => 'Gagal menambah data. Batas maksimal 100 warga aktif telah tercapai.'
            ], 422);
        }

        $validated = $request->validate([
            'nik' => 'required|string|digits:16|unique:warga,nik',
            'nama' => 'required|string|max:50|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'email' => 'nullable|email|max:100|unique:users,email',
            'no_hp' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'jumlah_anggota_keluarga' => 'required|integer|min:1|max:15',
            'alamat' => 'required|string',
            'kelompok' => 'required|in:A (Perumahan),B (Luar Perumahan)',
            'nama_bank_ewallet' => 'required|string|min:3|max:50|regex:/^[a-zA-Z\s\.\-]+$/',
            'nomor_rekening' => 'required|string|min:6|max:20|regex:/^[0-9]+$/',
            'nama_pemilik_rekening' => 'required|string|max:50|regex:/^[a-zA-Z\s\.\'\-]+$/',
        ], [
            'nik.digits' => 'NIK harus berisi tepat 16 digit angka.',
            'nama.regex' => 'Nama lengkap hanya boleh berisi huruf, spasi, titik, atau tanda hubung.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.min' => 'Nomor HP minimal 10 digit.',
            'no_hp.regex' => 'Nomor HP hanya boleh berisi angka.',
            'no_hp.max' => 'Nomor HP maksimal 15 digit.',
            'kelompok.required' => 'Kelompok warga wajib dipilih.',
            'kelompok.in' => 'Pilihan kelompok tidak valid.',
            'jumlah_anggota_keluarga.min' => 'Jumlah anggota keluarga minimal 1 orang.',
            'jumlah_anggota_keluarga.max' => 'Jumlah anggota keluarga maksimal 15 orang.',
            'nama_bank_ewallet.min' => 'Nama bank/e-wallet minimal 3 karakter.',
            'nama_bank_ewallet.max' => 'Nama bank/e-wallet maksimal 50 karakter.',
            'nama_bank_ewallet.regex' => 'Nama bank/e-wallet tidak valid.',
            'nomor_rekening.min' => 'Nomor rekening/e-wallet minimal 6 digit.',
            'nomor_rekening.max' => 'Nomor rekening/e-wallet maksimal 20 digit.',
            'nomor_rekening.regex' => 'Nomor rekening/e-wallet hanya boleh berisi angka.',
            'nama_pemilik_rekening.regex' => 'Nama pemilik rekening tidak valid.',
        ]);

        $warga = DB::transaction(function () use ($validated, $request) {
            $baseUsername = Str::slug($validated['nama'], '_');
            $username = $baseUsername . '_' . rand(1000, 9999);

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . rand(1000, 9999);
            }

            $email = $request->filled('email')
                ? $request->email
                : $username . '@banksampah.id';

            $user = User::create([
                'username' => $username,
                'email'    => $email,
                'password' => Hash::make($request->password ?? 'password123'),
                'role'     => 'warga',
            ]);

            $idWarga = 'WRG' . date('Ymd') . rand(1000, 9999);

            return Warga::create([
                'id_warga'                => $idWarga,
                'id_user'                 => $user->id,
                'nik'                     => $validated['nik'],
                'nama'                    => $validated['nama'],
                'no_hp'                   => $validated['no_hp'] ?? null,
                'jumlah_anggota_keluarga' => $validated['jumlah_anggota_keluarga'] ?? 1,
                'alamat'                  => $validated['alamat'],
                'kelompok'                => $validated['kelompok'],
                'saldo'                   => 0,
                'status'                  => 'aktif',
                'tanggal_daftar'          => now(),
                'nama_bank_ewallet'       => $validated['nama_bank_ewallet'] ?? null,
                'nomor_rekening'          => $validated['nomor_rekening'] ?? null,
                'nama_pemilik_rekening'   => $validated['nama_pemilik_rekening'] ?? null,
                'status_rekening'         => 'unverified',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Data warga berhasil ditambahkan.',
                'data' => $warga,
            ], 201);
        }

        return redirect()->route('admin.warga')->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function show($id)
    {
        $warga = Warga::findOrFail($id);
        return view('admin.pages.warga', compact('warga'));
    }

    public function edit($id)
    {
        $warga = Warga::findOrFail($id);
        return view('admin.pages.form-warga', compact('warga'));
    }

    public function update(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $validated = $request->validate([
            'status_rekening' => 'required|in:verified,unverified',
        ], [
            'status_rekening.required' => 'Status rekening wajib dipilih.',
            'status_rekening.in' => 'Status rekening tidak valid.',
        ]);

        $warga->update([
            'status_rekening' => $validated['status_rekening'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $warga]);
        }

        return redirect()->route('admin.warga')->with('success', 'Status rekening berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $statusBaru = ($warga->status ?? 'aktif') === 'tidak_aktif' ? 'aktif' : 'tidak_aktif';
        $warga->update(['status' => $statusBaru]);

        // Jika dinonaktifkan, cabut token Sanctum aktif agar sesi mobile yang sedang jalan terputus
        if ($statusBaru === 'tidak_aktif' && $warga->user) {
            $warga->user->tokens()->delete();
        }

        $pesan = $statusBaru === 'tidak_aktif'
            ? 'Status warga berhasil diubah menjadi tidak aktif.'
            : 'Status warga berhasil diaktifkan kembali.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status'  => $statusBaru,
                'message' => $pesan,
            ]);
        }

        return redirect()->route('admin.warga')->with('success', $pesan);
    }
}
