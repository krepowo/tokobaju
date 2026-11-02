<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan pembatalan otomatis pesanan kedaluwarsa & pengembalian stok tiap menit
Schedule::command('pesanan:batalkan-kedaluwarsa')->everyMinute();
