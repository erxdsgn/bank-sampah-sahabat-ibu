<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HargaSampahApiController;
use App\Http\Controllers\Api\JadwalSetoranApiController;
use App\Http\Controllers\Api\SetoranApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\LeaderboardApiController;
use App\Http\Controllers\Api\ArtikelEdukasiApiController;
use Illuminate\Support\Facades\Route;
use Kreait\Laravel\Firebase\Facades\Firebase;

// =============================
// 1. Endpoint Publik (Tanpa Login)
// =============================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-warga', [AuthController::class, 'registerWarga']);
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
// 2. Endpoint Terproteksi Token (Wajib Login)
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
});
