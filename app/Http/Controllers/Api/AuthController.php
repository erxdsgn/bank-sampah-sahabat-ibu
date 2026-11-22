<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * API Registrasi Akun Warga dari Flutter Mobile
     */
    public function registerWarga(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik'                     => 'required|string|digits:16|unique:warga,nik',
            'nama'                    => 'required|string|max:100',
            'email'                   => 'required|email|max:100|unique:users,email',
            'password'                => 'required|string|min:8',
            'no_hp'                   => 'nullable|string|max:20',
            'jumlah_anggota_keluarga' => 'nullable|integer|min:1',
            'nama_bank_ewallet'       => 'nullable|string|max:50',
            'nomor_rekening'          => 'nullable|string|max:50',
            'nama_pemilik_rekening'   => 'nullable|string|max:100',
            'alamat'                  => 'nullable|string|max:255',
        ], [
            'nik.required'      => 'NIK wajib diisi.',
            'nik.digits'        => 'NIK harus berjumlah 16 digit.',
            'nik.unique'        => 'NIK sudah terdaftar.',
            'nama.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email login wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $baseUsername = Str::slug($request->nama, '_');
                $username = $baseUsername . '_' . rand(1000, 9999);

                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . rand(1000, 9999);
                }

                $user = User::create([
                    'username' => $username,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'warga',
                ]);

                $idWarga = 'WRG' . date('Ymd') . rand(1000, 9999);

                $warga = Warga::create([
                    'id_warga'                => $idWarga,
                    'id_user'                 => $user->id,
                    'nik'                     => $request->nik,
                    'nama'                    => $request->nama,
                    'no_hp'                   => $request->no_hp,
                    'jumlah_anggota_keluarga' => $request->jumlah_anggota_keluarga ?? 1,
                    'nama_bank_ewallet'       => $request->nama_bank_ewallet,
                    'nomor_rekening'          => $request->nomor_rekening,
                    'nama_pemilik_rekening'   => $request->nama_pemilik_rekening,
                    'alamat'                  => $request->alamat,
                    'kelompok'                => null,
                    'kelompok_updated_at'     => null,
                    'saldo'                   => 0,
                    'tanggal_daftar'          => now(),
                    'status_rekening'         => 'unverified',
                ]);

                $token = $user->createToken('flutter_auth_token')->plainTextToken;

                return [
                    'token' => $token,
                    'user'  => [
                        'id'               => $user->id,
                        'username'         => $user->username,
                        'email'            => $user->email,
                        'avatar_url'       => null,
                        'notifikasi_aktif' => true, // Default notif aktif
                        'role'             => 'warga',
                        'profil'           => $warga,
                    ],
                ];
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Registrasi warga berhasil.',
                'data'    => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal melakukan registrasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API Edit Profil, Username, Email & Pemilihan/Perubahan Kelompok (Jeda 30 Hari)
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $warga = $user->warga;

        if ($request->filled('kelompok') && $warga->kelompok !== null && $warga->kelompok !== $request->kelompok) {
            if ($warga->kelompok_updated_at) {
                $lastUpdated = Carbon::parse($warga->kelompok_updated_at);
                $nextAllowedDate = $lastUpdated->copy()->addDays(30);

                if (Carbon::now()->lt($nextAllowedDate)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Kelompok hanya dapat diubah 1 kali sebulan. Anda dapat mengubahnya kembali pada ' . $nextAllowedDate->translatedFormat('d F Y') . '.',
                    ], 422);
                }
            }
        }

        $validator = Validator::make($request->all(), [
            'username'                => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'                   => 'required|email|max:100|unique:users,email,' . $user->id,
            'nama'                    => 'required|string|max:100',
            'kelompok'                => 'nullable|string|max:50',
            'no_hp'                   => 'nullable|string|max:20',
            'jumlah_anggota_keluarga' => 'nullable|integer|min:1',
            'nama_bank_ewallet'       => 'nullable|string|max:50',
            'nomor_rekening'          => 'nullable|string|max:50',
            'nama_pemilik_rekening'   => 'nullable|string|max:100',
            'alamat'                  => 'nullable|string|max:255',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan orang lain.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'nama.required'     => 'Nama lengkap wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $warga, $request) {
                $user->update([
                    'username' => $request->username,
                    'email'    => $request->email,   // ✅ TAMBAHKAN INI
                ]);

                $dataWarga = [
                    'nama'                    => $request->nama,
                    'no_hp'                   => $request->no_hp,
                    'jumlah_anggota_keluarga' => $request->jumlah_anggota_keluarga,
                    'nama_bank_ewallet'       => $request->nama_bank_ewallet,
                    'nomor_rekening'          => $request->nomor_rekening,
                    'nama_pemilik_rekening'   => $request->nama_pemilik_rekening,
                    'alamat'                  => $request->alamat,
                ];

                if ($request->filled('kelompok') && $request->kelompok !== $warga->kelompok) {
                    $dataWarga['kelompok'] = $request->kelompok;
                    $dataWarga['kelompok_updated_at'] = now();
                }

                $warga->update($dataWarga);
            });

            // ✅ Refresh model agar data terbaru (email baru) ikut terkirim
            $user->refresh();
            $user->load('warga');

            return response()->json([
                'status'  => 'success',
                'message' => 'Profil berhasil diperbarui.',
                'data'    => [
                    'id'               => $user->id,
                    'username'         => $user->username,
                    'email'            => $user->email,
                    'avatar_url'       => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'notifikasi_aktif' => $user->notifikasi_aktif ?? true,
                    'role'             => 'warga',
                    'profil'           => $user->warga,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API Ubah Password Warga
     */
    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password saat ini salah.',
            ], 400);
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Password berhasil diperbarui.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui password: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API Upload / Ubah Foto Profil Warga
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Foto profil berhasil diubah',
                'avatar_url' => asset('storage/' . $path)
            ], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan'], 400);
    }

    /**
     * API Simpan FCM Token (Untuk Notifikasi)
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);

        $user = $request->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Token notifikasi berhasil disimpan'
        ], 200);
    }

    /**
     * API Toggle Preferensi Notifikasi
     */
    public function updateNotifikasi(Request $request)
    {
        $request->validate(['notifikasi_aktif' => 'required|boolean']);

        $user = $request->user();
        $user->update(['notifikasi_aktif' => $request->notifikasi_aktif]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan notifikasi diperbarui',
        ], 200);
    }

    /**
     * API Login Khusus Warga
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username/Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::with('warga')
            ->where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Username/Email atau password salah.',
            ], 401);
        }

        if ($user->role !== 'warga' || ! $user->warga) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Aplikasi mobile hanya diperuntukkan bagi Warga.',
            ], 403);
        }

        $token = $user->createToken('flutter_auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'               => $user->id,
                    'username'         => $user->username,
                    'email'            => $user->email,
                    'avatar_url'       => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'notifikasi_aktif' => $user->notifikasi_aktif ?? true,
                    'role'             => 'warga',
                    'profil'           => $user->warga,
                ],
            ],
        ], 200);
    }

    /**
     * Get detail profil Warga yang sedang login
     */
    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load('warga');

        if ($user->role !== 'warga' || ! $user->warga) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses khusus warga.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'               => $user->id,
                'username'         => $user->username,
                'email'            => $user->email,
                'avatar_url'       => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'notifikasi_aktif' => $user->notifikasi_aktif ?? true,
                'role'             => 'warga',
                'profil'           => $user->warga,
            ],
        ], 200);
    }

    /**
     * Logout & Revoke Sanctum Token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil keluar dari sistem.',
        ], 200);
    }
}
