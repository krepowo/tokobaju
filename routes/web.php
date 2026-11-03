<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JasaPengirimanController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PembayaranController as PembayaranAdminController;
use App\Http\Controllers\Admin\PengirimanController;
use App\Http\Controllers\Admin\PesananController as PesananAdminController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\StokController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PesananController;
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
    } catch (Throwable $e) {
        $kategori = collect([]);
        $produkUnggulan = collect([]);
    }

    return view('home', compact('kategori', 'produkUnggulan'));
})->name('home');

// =========================================================================
// 2. KATALOG & DETAIL PRODUK (Publik)
// =========================================================================
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
Route::get('/produk/{id}', [KatalogController::class, 'show'])->name('produk.show');

// =========================================================================
// 3. KERANJANG + CHECKOUT + PESANAN (Wajib Login sesuai Aturan Bisnis)
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::patch('/keranjang/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');
    Route::post('/keranjang/toggle-all', [KeranjangController::class, 'toggleAll'])->name('keranjang.toggleAll');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{pesanan}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::post('/pesanan/{pesanan}/bayar', [PesananController::class, 'uploadBukti'])->name('pesanan.bayar');
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
// 5. AREA PENJUAL (Role: Penjual)
// =========================================================================
Route::middleware(['auth', 'role:penjual'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Kelola Produk, Kategori & Pengiriman
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');

        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        Route::get('/jasa', [JasaPengirimanController::class, 'index'])->name('jasa.index');
        Route::post('/jasa', [JasaPengirimanController::class, 'store'])->name('jasa.store');
        Route::put('/jasa/{jasa}', [JasaPengirimanController::class, 'update'])->name('jasa.update');
        Route::delete('/jasa/{jasa}', [JasaPengirimanController::class, 'destroy'])->name('jasa.destroy');
        Route::put('/jasa/{jasa}/tarif', [JasaPengirimanController::class, 'updateTarif'])->name('jasa.tarif.update');

        // Kelola Stok, Ukuran & Warna
        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::put('/stok/{produk}', [StokController::class, 'update'])->name('stok.update');

        // Pesanan & Invoice
        Route::get('/pesanan', [PesananAdminController::class, 'index'])->name('pesanan.index');
        Route::get('/pesanan/cetak', [PesananAdminController::class, 'cetak'])->name('pesanan.cetak');
        Route::get('/pesanan/{pesanan}', [PesananAdminController::class, 'show'])->name('pesanan.show');

        // Proses Pengiriman
        Route::get('/pengiriman', [PengirimanController::class, 'index'])->name('pengiriman.index');
        Route::put('/pengiriman/{pesanan}', [PengirimanController::class, 'update'])->name('pengiriman.update');

        // Verifikasi Bayar
        Route::get('/verifikasi', [PembayaranAdminController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{pembayaran}', [PembayaranAdminController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{pembayaran}/setujui', [PembayaranAdminController::class, 'setujui'])->name('verifikasi.setujui');
        Route::post('/verifikasi/{pembayaran}/tolak', [PembayaranAdminController::class, 'tolak'])->name('verifikasi.tolak');
    });
