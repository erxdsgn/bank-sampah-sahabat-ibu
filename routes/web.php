<?php

use App\Http\Controllers\Admin\AuthViewController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\WargaController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    // Katalog Barang (CRUD Produk)
    Route::resource('katalog', ProductController::class);


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


// === FUNGSI HANDLER UNTUK PENCAIRAN SALDO ===
$tampilkanPencairan = function () {
    $dummyData = collect([
        (object)[
            'id_pencairan' => 1,
            'created_at' => '2026-09-17 10:00:00',
            'warga' => (object)[
                'nik' => '3509876356289110001',
                'nama' => 'Ani',
                'no_hp' => '087872550004',
                'alamat' => 'jl kyai haji nurhasyim'
            ],
            'nominal' => 50000,
            'metode' => 'Tunai',
            'status' => 'menunggu'
        ],
        (object)[
            'id_pencairan' => 2,
            'created_at' => '2026-09-16 08:30:00',
            'warga' => (object)[
                'nik' => '3509281145110003',
                'nama' => 'ijut',
                'no_hp' => '082245607023',
                'alamat' => 'jl jl'
            ],
            'nominal' => 100000,
            'metode' => 'Transfer Bank',
            'status' => 'selesai'
        ],
        (object)[
            'id_pencairan' => 3,
            'created_at' => '2026-09-16 14:15:00',
            'warga' => (object)[
                'nik' => '3509281111110001',
                'nama' => 'budi',
                'no_hp' => '082245607041',
                'alamat' => 'jl kaki kanan'
            ],
            'nominal' => 500000,
            'metode' => 'Transfer Bank',
            'status' => 'menunggu'
        ]
    ]);

    return view('admin.pages.pencairan', [
        'pencairan' => $dummyData
    ]);
};


// 1. Tangkap jika diklik dari halaman utama / Dashboard
Route::get('/pencairan-saldo.html', $tampilkanPencairan);

// 2. Tangkap jika diklik dari dalam Data Warga (solusi untuk error 404 tadi)
Route::get('/admin/pages/pencairan-saldo.html', $tampilkanPencairan);

// 3. Tangkap jika diklik dari URL admin lainnya
Route::get('/admin/pencairan-saldo.html', $tampilkanPencairan);
// ===========================================


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
