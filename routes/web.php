<?php

use App\Http\Controllers\Admin\AuthViewController;
use App\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');

Route::prefix('components')->name('ui.')->group(function () {
    Route::get('/buttons', [PageController::class, 'buttons'])->name('buttons');
    Route::get('/forms', [PageController::class, 'forms'])->name('forms');
    Route::get('/charts', [PageController::class, 'charts'])->name('charts');
    Route::get('/ui-elements', [PageController::class, 'uiElements'])->name('elements');
});

Route::get('/datatable', [PageController::class, 'datatable'])->name('datatable');

Route::prefix('tables')->name('tables.')->group(function () {
    Route::get('/basic', [PageController::class, 'basicTable'])->name('basic');
});

Route::prefix('communications')->group(function () {
    Route::get('/email', [PageController::class, 'email'])->name('email.index');
    Route::get('/email/compose', [PageController::class, 'compose'])->name('email.compose');
    Route::get('/chat', [PageController::class, 'chat'])->name('chat');
    Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
});

Route::prefix('maps')->name('maps.')->group(function () {
    Route::get('/vector', [PageController::class, 'vectorMaps'])->name('vector');
    Route::get('/google', [PageController::class, 'googleMaps'])->name('google');
});

Route::get('/blank', [PageController::class, 'blank'])->name('pages.blank');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthViewController::class, 'showRegister'])->name('register');
});
