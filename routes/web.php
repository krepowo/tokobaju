<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. BERANDA PELANGGAN (Publik)
// =========================================================================
Route::get('/', function () {
    try {
        $kategori = Kategori::all();
        $produkUnggulan = Produk::with('kategori')->take(4)->get();
    } catch (\Throwable $e) {
        $kategori = collect([]);
        $produkUnggulan = collect([]);
    }

    return view('home', compact('kategori', 'produkUnggulan'));
})->name('home');

// =========================================================================
// 2. KATALOG & DETAIL PRODUK (Publik - Dapat diakses tanpa login)
// =========================================================================
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
Route::get('/produk/{id}', [KatalogController::class, 'show'])->name('produk.show');

// =========================================================================
// 3. KERANJANG BELANJA (Wajib Login sesuai Aturan Bisnis #9)
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::patch('/keranjang/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');
    Route::post('/keranjang/toggle-all', [KeranjangController::class, 'toggleAll'])->name('keranjang.toggleAll');

    // Placeholder untuk pesanan saya
    Route::get('/pesanan', fn () => redirect()->route('home')->with('info', 'Halaman Pesanan Saya akan segera hadir.'))->name('pesanan.index');
});

// =========================================================================
// 4. AUTENTIKASI (Login, Register, Logout)
// =========================================================================
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

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =========================================================================
// 5. HALAMAN PENJUAL (Role: Penjual)
// =========================================================================
Route::get('/admin', fn () => view('admin.home'))
    ->middleware(['auth', 'role:penjual'])
    ->name('admin.home');
