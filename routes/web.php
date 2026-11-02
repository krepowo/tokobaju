<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JasaController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PengirimanController as AdminPengirimanController;
use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\ProdukController as AdminProdukController;
use App\Http\Controllers\Admin\StokController;
use App\Http\Controllers\Admin\UlasanController as AdminUlasanController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');

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

/*
|--------------------------------------------------------------------------
| Halaman Penjual (Admin)
|--------------------------------------------------------------------------
| Seluruh rute di bawah hanya dapat diakses akun dengan role "penjual".
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:penjual'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kelola produk & kategori
    Route::get('/produk', [AdminProdukController::class, 'index'])->name('produk.index');
    Route::get('/produk/tambah', [AdminProdukController::class, 'create'])->name('produk.create');
    Route::post('/produk', [AdminProdukController::class, 'store'])->name('produk.store');
    Route::get('/produk/{produk}/ubah', [AdminProdukController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{produk}', [AdminProdukController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{produk}', [AdminProdukController::class, 'destroy'])->name('produk.destroy');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // Jasa pengiriman & tarif per pulau
    Route::get('/pengiriman', [JasaController::class, 'index'])->name('jasa.index');
    Route::post('/pengiriman', [JasaController::class, 'store'])->name('jasa.store');
    Route::put('/pengiriman/{jasa}', [JasaController::class, 'update'])->name('jasa.update');
    Route::delete('/pengiriman/{jasa}', [JasaController::class, 'destroy'])->name('jasa.destroy');
    Route::get('/pengiriman/{jasa}/tarif', [JasaController::class, 'tarif'])->name('jasa.tarif');
    Route::put('/pengiriman/{jasa}/tarif', [JasaController::class, 'simpanTarif'])->name('jasa.tarif.simpan');

    // Kelola stok & ukuran
    Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
    Route::get('/stok/{produk}', [StokController::class, 'kelola'])->name('stok.kelola');
    Route::post('/stok/{produk}', [StokController::class, 'simpan'])->name('stok.simpan');
    Route::put('/stok/{produk}/{varian}', [StokController::class, 'update'])->name('stok.update');
    Route::delete('/stok/{produk}/{varian}', [StokController::class, 'hapus'])->name('stok.hapus');

    // Pesanan & invoice
    Route::get('/pesanan', [AdminPesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{pesanan}/cetak', [AdminPesananController::class, 'cetak'])->name('pesanan.cetak');
    Route::get('/pesanan/{pesanan}', [AdminPesananController::class, 'show'])->name('pesanan.show');

    // Verifikasi pembayaran
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
    Route::get('/verifikasi/{pesanan}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
    Route::post('/verifikasi/{pesanan}/setujui', [VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
    Route::post('/verifikasi/{pesanan}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');

    // Proses pengiriman & input resi
    Route::get('/kirim', [AdminPengirimanController::class, 'index'])->name('kirim.index');
    Route::post('/kirim/{pesanan}', [AdminPengirimanController::class, 'simpan'])->name('kirim.simpan');

    // Ulasan pelanggan
    Route::get('/ulasan', [AdminUlasanController::class, 'index'])->name('ulasan.index');
    Route::get('/ulasan/{produk}', [AdminUlasanController::class, 'show'])->name('ulasan.show');

    // Laporan omzet
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
});
