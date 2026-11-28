<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmEmailMail;
use App\Models\User;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * API Registrasi Akun Warga dari Flutter Mobile
     * Belum mengembalikan token agar tidak langsung masuk Home sebelum konfirmasi email.
     */
    public function registerWarga(Request $request)
    {
        // Bersihkan spasi nomor rekening jika dikirim dengan format spasi 4 digit
        if ($request->filled('nomor_rekening')) {
            $request->merge([
                'nomor_rekening' => preg_replace('/\s+/', '', $request->nomor_rekening),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'nik'                     => ['required', 'string', 'digits:16', 'regex:/^[0-9]{16}$/', 'unique:warga,nik'],
            'nama'                    => ['required', 'string', 'max:100'],
            'email'                   => ['required', 'email', 'max:100', 'unique:users,email'],
            'password'                => ['required', 'string', 'min:8'],
            'no_hp'                   => ['nullable', 'string', 'min:10', 'max:14', 'regex:/^(08|628)[0-9]{7,11}$/'],
            'jumlah_anggota_keluarga' => ['nullable', 'integer', 'min:1'],
            'nama_bank_ewallet'       => ['nullable', 'string', 'max:50'],
            'nomor_rekening'          => ['nullable', 'string', 'min:6', 'max:16', 'regex:/^[0-9]+$/'],
            'nama_pemilik_rekening'   => ['nullable', 'string', 'max:100'],
            'alamat'                  => ['nullable', 'string', 'max:255'],
        ], [
            'nik.required'           => 'NIK wajib diisi.',
            'nik.digits'             => 'NIK harus berjumlah 16 digit.',
            'nik.regex'              => 'NIK hanya boleh berisi angka.',
            'nik.unique'             => 'NIK sudah terdaftar.',
            'nama.required'          => 'Nama lengkap wajib diisi.',
            'email.required'         => 'Email login wajib diisi.',
            'email.email'            => 'Format email tidak valid atau domain email tidak aktif.',
            'email.unique'           => 'Email sudah terdaftar.',
            'password.required'      => 'Password wajib diisi.',
            'password.min'           => 'Password minimal 8 karakter.',
            'no_hp.min'              => 'Nomor HP/WA minimal 10 digit.',
            'no_hp.max'              => 'Nomor HP/WA maksimal 14 digit.',
            'no_hp.regex'            => 'Nomor HP/WA harus berupa angka dan diawali dengan 08 atau 628.',
            'nomor_rekening.min'     => 'Nomor rekening/e-wallet minimal 6 digit.',
            'nomor_rekening.max'     => 'Nomor rekening/e-wallet maksimal 16 digit.',
            'nomor_rekening.regex'   => 'Nomor rekening/e-wallet hanya boleh berisi angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $emailNormalized = strtolower(trim($request->email));

            $user = DB::transaction(function () use ($request, $emailNormalized) {
                $baseUsername = Str::slug($request->nama, '_');
                $username = $baseUsername . '_' . rand(1000, 9999);

                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . rand(1000, 9999);
                }

                $user = User::create([
                    'username'          => $username,
                    'email'             => $emailNormalized,
                    'password'          => Hash::make($request->password),
                    'role'              => 'warga',
                    'email_verified_at' => null,
                ]);

                $idWarga = 'WRG' . date('Ymd') . rand(1000, 9999);

                Warga::create([
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

                return $user;
            });

            $this->sendConfirmationLink($user);

            return response()->json([
                'status'  => 'success',
                'message' => 'Registrasi berhasil. Silakan cek inbox atau folder spam email Anda untuk konfirmasi sebelum masuk ke aplikasi.',
                'data'    => [
                    'requires_verification' => true,
                    'email'                 => $user->email,
                    'username'              => $user->username,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal melakukan registrasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint saat pengguna mengklik tombol/link konfirmasi di email
     */
    public function verifyEmailLink(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            if (view()->exists('admin.errors.404')) {
                return response()->view('admin.errors.404', [], 404);
            }
            return response('Akun tidak ditemukan atau link sudah tidak berlaku.', 404);
        }

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            if (view()->exists('admin.errors.403')) {
                return response()->view('admin.errors.403', [], 403);
            }
            return response('Link konfirmasi tidak valid.', 403);
        }

        if (is_null($user->email_verified_at)) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        return response()->view('emails.verified-success', [
            'appName' => 'Bank Sampah Gemi',
        ]);
    }

    /**
     * Kirim ulang link konfirmasi email
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $emailNormalized = strtolower(trim($request->email));
        $user = User::where('email', $emailNormalized)->first();

        if (!$user) {
            return response()->json([
                'status'   => 'success',
                'verified' => false,
                'message'  => 'Jika email terdaftar dan belum diverifikasi, link konfirmasi baru telah dikirim.',
            ], 200);
        }

        if (!is_null($user->email_verified_at)) {
            return response()->json([
                'status'   => 'success',
                'verified' => true,
                'message'  => 'Email sudah terkonfirmasi. Silakan login.',
            ], 200);
        }

        try {
            $this->sendConfirmationLink($user);

            return response()->json([
                'status'   => 'success',
                'verified' => false,
                'message'  => 'Link konfirmasi baru telah dikirim ke email Anda.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim ulang email konfirmasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek apakah email sudah dikonfirmasi (dipanggil dari halaman tunggu konfirmasi di Flutter)
     */
    public function checkVerificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $emailNormalized = strtolower(trim($request->email));
        $user = User::with('warga')->where('email', $emailNormalized)->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email tidak terdaftar.',
            ], 404);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status'   => 'error',
                'verified' => false,
                'message'  => 'Email belum dikonfirmasi. Silakan buka email Anda dan klik link konfirmasi.',
            ], 403);
        }

        $token = $user->createToken('flutter_auth_token')->plainTextToken;

        return response()->json([
            'status'   => 'success',
            'verified' => true,
            'message'  => 'Email sudah terkonfirmasi.',
            'data'     => [
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
     * API Edit Profil, Username, Email & Pemilihan/Perubahan Kelompok (Jeda 30 Hari)
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $warga = $user->warga;

        if ($request->filled('nomor_rekening')) {
            $request->merge([
                'nomor_rekening' => preg_replace('/\s+/', '', $request->nomor_rekening),
            ]);
        }

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
            'username'                => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email'                   => ['required', 'email', 'max:100', 'unique:users,email,' . $user->id],
            'nama'                    => ['required', 'string', 'max:100'],
            'kelompok'                => ['nullable', 'string', 'max:50'],
            'no_hp'                   => ['nullable', 'string', 'min:10', 'max:14', 'regex:/^(08|628)[0-9]{7,11}$/'],
            'jumlah_anggota_keluarga' => ['nullable', 'integer', 'min:1'],
            'nama_bank_ewallet'       => ['nullable', 'string', 'max:50'],
            'nomor_rekening'          => ['nullable', 'string', 'min:6', 'max:16', 'regex:/^[0-9]+$/'],
            'nama_pemilik_rekening'   => ['nullable', 'string', 'max:100'],
            'alamat'                  => ['nullable', 'string', 'max:255'],
        ], [
            'username.required'    => 'Username wajib diisi.',
            'username.unique'      => 'Username sudah digunakan orang lain.',
            'email.required'       => 'Email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email sudah terdaftar.',
            'nama.required'        => 'Nama lengkap wajib diisi.',
            'no_hp.min'            => 'Nomor HP/WA minimal 10 digit.',
            'no_hp.max'            => 'Nomor HP/WA maksimal 14 digit.',
            'no_hp.regex'          => 'Nomor HP/WA harus berupa angka dan diawali dengan 08 atau 628.',
            'nomor_rekening.min'   => 'Nomor rekening/e-wallet minimal 6 digit.',
            'nomor_rekening.max'   => 'Nomor rekening/e-wallet maksimal 16 digit.',
            'nomor_rekening.regex' => 'Nomor rekening/e-wallet hanya boleh berisi angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $emailNormalized = strtolower(trim($request->email));
            $emailChanged = $user->email !== $emailNormalized;

            DB::transaction(function () use ($user, $warga, $request, $emailNormalized, $emailChanged) {
                $userData = [
                    'username' => $request->username,
                    'email'    => $emailNormalized,
                ];

                if ($emailChanged) {
                    $userData['email_verified_at'] = null;
                }

                $user->update($userData);

                // Hanya perbarui kolom yang benar-benar dikirim dan tidak kosong
                // agar data lama (no_hp, alamat, rekening) tidak tertimpa menjadi NULL
                $dataWarga = [
                    'nama' => $request->nama,
                ];

                if ($request->filled('no_hp')) {
                    $dataWarga['no_hp'] = $request->no_hp;
                }

                if ($request->filled('jumlah_anggota_keluarga')) {
                    $dataWarga['jumlah_anggota_keluarga'] = $request->jumlah_anggota_keluarga;
                }

                if ($request->filled('nama_bank_ewallet')) {
                    $dataWarga['nama_bank_ewallet'] = $request->nama_bank_ewallet;
                }

                if ($request->filled('nomor_rekening')) {
                    $dataWarga['nomor_rekening'] = $request->nomor_rekening;
                }

                if ($request->filled('nama_pemilik_rekening')) {
                    $dataWarga['nama_pemilik_rekening'] = $request->nama_pemilik_rekening;
                }

                if ($request->filled('alamat')) {
                    $dataWarga['alamat'] = $request->alamat;
                }

                if ($request->filled('kelompok') && $request->kelompok !== $warga->kelompok) {
                    $dataWarga['kelompok'] = $request->kelompok;
                    $dataWarga['kelompok_updated_at'] = now();
                }

                $warga->update($dataWarga);
            });

            $user->refresh();
            $user->load('warga');

            if ($emailChanged) {
                $this->sendConfirmationLink($user);
            }

            return response()->json([
                'status'  => 'success',
                'message' => $emailChanged
                    ? 'Profil diperbarui. Silakan konfirmasi alamat email baru Anda.'
                    : 'Profil berhasil diperbarui.',
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
            'status'  => 'success',
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
            ->orWhere('email', strtolower(trim($request->username)))
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

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status'                => 'error',
                'requires_verification' => true,
                'email'                 => $user->email,
                'message'               => 'Email belum dikonfirmasi. Silakan cek email Anda dan klik link konfirmasi.',
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

    /**
     * Helper kirim link konfirmasi email bertanda tangan
     */
    private function sendConfirmationLink(User $user): void
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify.link',
            now()->addMinutes(60),
            [
                'id'   => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        Mail::to($user->email)->send(new ConfirmEmailMail($user->username, $verifyUrl));
    }
}
