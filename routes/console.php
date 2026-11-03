<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tolak pembayaran / batalkan pesanan yang melewati batas_bayar.
// Di Windows jalankan `php artisan schedule:work` agar penjadwal aktif.
Schedule::command('pembayaran:kedaluwarsa')->everyMinute()->withoutOverlapping();
