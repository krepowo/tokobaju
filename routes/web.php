<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
Route::get('/produk/{produk}', [ProdukController::class, 'show'])->name('produk.detail');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::post('/keranjang/{keranjang}/update', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::post('/keranjang/{keranjang}/pilih', [KeranjangController::class, 'togglePilih'])->name('keranjang.togglePilih');
    Route::post('/keranjang/{keranjang}/hapus', [KeranjangController::class, 'destroy'])->name('keranjang.hapus');
});

// Halaman penjual (placeholder, dibangun pada tahap berikutnya).
Route::get('/admin', fn () => view('admin.home'))
    ->middleware(['auth', 'role:penjual'])
    ->name('admin.home');
