<?php

use App\Http\Controllers\Admin\AuthViewController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\WargaController;
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

    Route::view('/pages/keuangan', 'admin.pages.keuangan.index')
    ->name('keuangan');

});

// Route::prefix('communications')->group(function () {
//     Route::get('/email', [PageController::class, 'email'])->name('email.index');
//     Route::get('/email/compose', [PageController::class, 'compose'])->name('email.compose');
//     Route::get('/chat', [PageController::class, 'chat'])->name('chat');
//     Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
// });

// Route::prefix('maps')->name('maps.')->group(function () {
//     Route::get('/vector', [PageController::class, 'vectorMaps'])->name('vector');
//     Route::get('/google', [PageController::class, 'googleMaps'])->name('google');
// });

// Route::get('/blank', [PageController::class, 'blank'])->name('pages.blank');

// Route::middleware('guest')->group(function () {
//     Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
//     Route::get('/register', [AuthViewController::class, 'showRegister'])->name('register');
// });
