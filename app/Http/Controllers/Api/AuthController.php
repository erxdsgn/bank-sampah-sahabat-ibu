<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                // 1. Generasi username unik berbasis Nama + 4 angka acak
                $baseUsername = Str::slug($request->nama, '_');
                $username = $baseUsername . '_' . rand(1000, 9999);

                // Perulangan untuk menjamin tidak ada duplikasi username
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . rand(1000, 9999);
                }

                // 2. Buat akun di tabel users
                $user = User::create([
                    'username' => $username,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'warga',
                ]);

                // 3. Generate ID Warga kustom
                $idWarga = 'WRG' . date('Ymd') . rand(1000, 9999);

                // 4. Buat profil lengkap di tabel warga
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
                    'saldo'                   => 0,
                    'tanggal_daftar'          => now(),
                    'status_rekening'         => 'unverified',
                ]);

                // 5. Buat token Sanctum untuk login langsung dari mobile
                $token = $user->createToken('flutter_auth_token')->plainTextToken;

                return [
                    'token' => $token,
                    'user'  => [
                        'id'       => $user->id,
                        'username' => $user->username,
                        'email'    => $user->email,
                        'role'     => 'warga',
                        'profil'   => $warga,
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
     * API Edit Profil & Mengubah Username
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'username'                => 'required|string|max:50|unique:users,username,' . $user->id,
            'nama'                    => 'required|string|max:100',
            'no_hp'                   => 'nullable|string|max:20',
            'jumlah_anggota_keluarga' => 'nullable|integer|min:1',
            'nama_bank_ewallet'       => 'nullable|string|max:50',
            'nomor_rekening'          => 'nullable|string|max:50',
            'nama_pemilik_rekening'   => 'nullable|string|max:100',
            'alamat'                  => 'nullable|string|max:255',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan orang lain.',
            'nama.required'     => 'Nama lengkap wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $request) {
                $user->update([
                    'username' => $request->username,
                ]);

                $user->warga()->update([
                    'nama'                    => $request->nama,
                    'no_hp'                   => $request->no_hp,
                    'jumlah_anggota_keluarga' => $request->jumlah_anggota_keluarga,
                    'nama_bank_ewallet'       => $request->nama_bank_ewallet,
                    'nomor_rekening'          => $request->nomor_rekening,
                    'nama_pemilik_rekening'   => $request->nama_pemilik_rekening,
                    'alamat'                  => $request->alamat,
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Profil berhasil diperbarui.',
                'data'    => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => 'warga',
                    'profil'   => $user->warga()->first(),
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
                'message' => 'Anda masih belum memiliki akun atau password salah.',
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
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => 'warga',
                    'profil'   => $user->warga,
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
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => 'warga',
                'profil'   => $user->warga,
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