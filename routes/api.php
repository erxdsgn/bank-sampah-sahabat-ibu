<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\HargaSampahApiController;
use App\Http\Controllers\Api\JadwalSetoranApiController;
use App\Http\Controllers\Api\SetoranApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\LeaderboardApiController;
use App\Http\Controllers\Api\ArtikelEdukasiApiController;
use App\Http\Controllers\Api\PengaduanApiController;
use Illuminate\Support\Facades\Route;
use Kreait\Laravel\Firebase\Facades\Firebase;

// =============================
// 1. Endpoint Publik (Tanpa Login)
// =============================
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Maks 5x per menit
Route::post('/register-warga', [AuthController::class, 'registerWarga'])->middleware('throttle:3,1'); // Maks 3x per menit

// Konfirmasi Email Registrasi (Link)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmailLink'])
    ->middleware(['signed'])
    ->name('verification.verify.link');
Route::post('/email/resend-verification', [AuthController::class, 'resendVerification'])
    ->middleware('throttle:3,1');
Route::post('/email/check-status', [AuthController::class, 'checkVerificationStatus'])
    ->middleware('throttle:10,1'); // Maks 10x per menit (karena user mungkin klik berulang)

// Fitur Lupa Password (OTP)
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->middleware('throttle:3,1');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:3,1');

Route::get('/harga-sampah', [HargaSampahApiController::class, 'index']);
Route::get('/artikel-edukasi', [ArtikelEdukasiApiController::class, 'index']);

// =============================
// UJI KONEKSI FIREBASE
// =============================
Route::get('/test-firebase', function () {
    try {
        Firebase::messaging();
        return response()->json(['status' => 'success', 'message' => 'Firebase connected successfully!']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Firebase error: ' . $e->getMessage()], 500);
    }
});

// =============================
// 2. Endpoint Terproteksi Token (Wajib Login & Warga Aktif)
// =============================
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profil Warga
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/update-avatar', [AuthController::class, 'updateAvatar']);
    Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::put('/update-notifikasi', [AuthController::class, 'updateNotifikasi']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Jadwal Setoran
    Route::prefix('jadwal-setoran')->group(function () {
        Route::get('/', [JadwalSetoranApiController::class, 'index']);
        Route::get('/active', [JadwalSetoranApiController::class, 'getActiveSession']);
        Route::get('/{id}', [JadwalSetoranApiController::class, 'show']);
    });

    // Fitur Setor Sampah Warga
    Route::get('/kategori-sampah', [SetoranApiController::class, 'getKategori']);
    Route::post('/setor-sampah', [SetoranApiController::class, 'store']);
    Route::get('/riwayat-setoran', [SetoranApiController::class, 'riwayat']);

    // Fitur Katalog Produk
    Route::get('/produk', [ProductApiController::class, 'index']);
    Route::get('/produk/{id}', [ProductApiController::class, 'show']);

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardApiController::class, 'index']);

    // Fitur Pengaduan Warga
    Route::get('/pengaduan', [PengaduanApiController::class, 'index']);
    Route::post('/pengaduan', [PengaduanApiController::class, 'store']);
});
