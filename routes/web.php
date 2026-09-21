<?php

use App\Http\Controllers\Admin\AuthViewController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\WargaController;
use App\Http\Controllers\Admin\VerifikasiSetoranController;
use App\Http\Controllers\Admin\DeviceManagementController;
use App\Http\Controllers\Admin\PencairanController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\KeuanganController;
use App\Http\Controllers\Admin\BarangKeluarController;
use App\Http\Controllers\Admin\KategoriHargaController;
use App\Http\Controllers\Admin\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // View Login
    Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');

    // Process Login
    Route::post('/login', [AuthController::class, 'login']);
});

// Alias Route Dashboard untuk menghindari error saat pemanggilan route('dashboard')
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->name('dashboard');

// Process Logout (Hanya bisa diakses jika sudah login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Admin Protected Routes (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    // Pengaturan Sistem & Sesi Perangkat Login
    Route::get('/pages/pengaturan', [HomeController::class, 'pengaturan'])->name('pengaturan');

    // Data Warga
    Route::prefix('pages/warga')->group(function () {
        Route::get('/', [WargaController::class, 'index'])->name('warga');
        Route::post('/', [WargaController::class, 'store'])->name('warga.store');
        Route::get('/{id}', [WargaController::class, 'show'])->name('warga.show');
        Route::get('/{id}/edit', [WargaController::class, 'edit'])->name('warga.edit');
        Route::put('/{id}', [WargaController::class, 'update'])->name('warga.update');
        Route::delete('/{id}', [WargaController::class, 'destroy'])->name('warga.destroy');
    });

    // Verifikasi Setoran
    Route::prefix('pages/verifikasi-setoran')->name('verifikasi-setoran.')->group(function () {
        Route::get('/', [VerifikasiSetoranController::class, 'index'])->name('index');
        Route::post('/', [VerifikasiSetoranController::class, 'store'])->name('store');
        Route::patch('/{setoran}/setujui', [VerifikasiSetoranController::class, 'setujui'])->name('setujui');
        Route::patch('/{setoran}/tolak', [VerifikasiSetoranController::class, 'tolak'])->name('tolak');
    });

    // Laporan & Riwayat (TAMBAHKAN DI SINI)
    Route::prefix('pages/laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
    });

    // Pencairan Saldo
    Route::prefix('pages/pencairan')->group(function () {
        Route::get('/', [PencairanController::class, 'index'])->name('pencairan.index');
        Route::post('/', [PencairanController::class, 'store'])->name('pencairan.store');
        Route::put('/{pencairan}', [PencairanController::class, 'update'])->name('pencairan.update');
        Route::delete('/{pencairan}', [PencairanController::class, 'destroy'])->name('pencairan.destroy');
    });

    Route::prefix('pages/katalog')->name('katalog.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Keuangan
    Route::prefix('pages/keuangan')->name('keuangan.')->group(function () {
        Route::get('/', [KeuanganController::class, 'index'])->name('index');
        Route::post('/', [KeuanganController::class, 'store'])->name('store');
        Route::get('/{keuangan}', [KeuanganController::class, 'show'])->name('show');
        Route::put('/{keuangan}', [KeuanganController::class, 'update'])->name('update');
        Route::delete('/{keuangan}', [KeuanganController::class, 'destroy'])->name('destroy');
    });

    // Penjualan ke Pengepul
    Route::prefix('pages/penjualan-pengepul')->name('penjualan-pengepul.')->group(function () {
        Route::get('/', [BarangKeluarController::class, 'index'])->name('index');
        Route::post('/', [BarangKeluarController::class, 'store'])->name('store');
        Route::get('/{barangKeluar}', [BarangKeluarController::class, 'show'])->name('show');
        Route::put('/{barangKeluar}', [BarangKeluarController::class, 'update'])->name('update');
        Route::delete('/{barangKeluar}', [BarangKeluarController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Perangkat / Session Login
    Route::prefix('pages/devices')->name('devices.')->group(function () {
        Route::get('/', [DeviceManagementController::class, 'index'])->name('index');
        Route::delete('/{id}', [DeviceManagementController::class, 'logoutDevice'])->name('logout');
    });

    // Kategori & Harga Sampah
    Route::prefix('pages/kategori-harga')->name('kategori-harga.')->group(function () {
        Route::get('/', [KategoriHargaController::class, 'index'])->name('index');
        Route::post('/', [KategoriHargaController::class, 'store'])->name('store');
        Route::put('/{kategoriSampah}', [KategoriHargaController::class, 'update'])->name('update');
        Route::delete('/{kategoriSampah}', [KategoriHargaController::class, 'destroy'])->name('destroy');

        Route::prefix('{kategoriSampah}/harga')->name('harga.')->group(function () {
            Route::post('/', [KategoriHargaController::class, 'hargaStore'])->name('store');
            Route::delete('/{hargaSampah}', [KategoriHargaController::class, 'hargaDestroy'])->name('destroy');
        });
    });
});
