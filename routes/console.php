<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan pembatalan otomatis pesanan kedaluwarsa & pengembalian stok tiap menit
Schedule::command('pesanan:batalkan-kedaluwarsa')->everyMinute();

// Tolak pembayaran / batalkan pesanan yang melewati batas_bayar (upstream)
// Di Windows jalankan `php artisan schedule:work` agar penjadwal aktif.
Schedule::command('pembayaran:kedaluwarsa')->everyMinute()->withoutOverlapping();
