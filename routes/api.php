<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ==========================================
// ENDPOINT PUBLIK (Bisa diakses tanpa token)
// ==========================================
// Endpoint Publik
Route::post('/login', [AuthController::class, 'login']);
// Ubah dari '/register' menjadi '/register-warga' dan arahkan ke 'registerWarga'
Route::post('/register-warga', [AuthController::class, 'registerWarga']);

// Atau jika menggunakan '/register-warga', uncomment baris di bawah ini:
// Route::post('/register-warga', [AuthController::class, 'registerWarga']);

// ENDPOINT TERPROTEKSI (Harus bawa Token Sanctum)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});