<?php

use App\Http\Controllers\Admin\AuthViewController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\WargaController;
use App\Http\Controllers\Admin\KategoriSampahController;
use App\Http\Controllers\Admin\HargaSampahController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    // Data Warga
    Route::prefix('pages/warga')->group(function () {

        Route::get('/', [WargaController::class, 'index'])
            ->name('warga');

        Route::get('/create', [WargaController::class, 'create'])
            ->name('warga.form-warga');

        Route::post('/', [WargaController::class, 'store'])
            ->name('warga.store');

        Route::get('/{id}', [WargaController::class, 'show'])
            ->name('warga.show');

        Route::get('/{id}/edit', [WargaController::class, 'edit'])
            ->name('warga.edit');

        Route::put('/{id}', [WargaController::class, 'update'])
            ->name('warga.update');

        Route::delete('/{id}', [WargaController::class, 'destroy'])
            ->name('warga.destroy');
    });

});

// Kategori Sampah
Route::prefix('admin')->name('admin.')->group(function () {
    
    Route::resource('kategori-sampah', KategoriSampahController::class)
        ->parameters(['kategori-sampah' => 'kategoriSampah']);

    Route::prefix('kategori-sampah/{kategoriSampah}/harga')->name('kategori-sampah.harga.')->group(function () {
        Route::get('/', [HargaSampahController::class, 'index'])->name('index');
        Route::post('/', [HargaSampahController::class, 'store'])->name('store');
        Route::delete('/{hargaSampah}', [HargaSampahController::class, 'destroy'])->name('destroy');
    });

});
