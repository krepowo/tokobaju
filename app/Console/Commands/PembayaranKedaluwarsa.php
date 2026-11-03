<?php

namespace App\Console\Commands;

use App\Support\Kedaluwarsa;
use Illuminate\Console\Command;

class PembayaranKedaluwarsa extends Command
{
    protected $signature = 'pembayaran:kedaluwarsa';

    protected $description = 'Tolak pembayaran dan batalkan pesanan yang melewati batas waktu pembayaran';

    public function handle(): int
    {
        $hasil = Kedaluwarsa::jalankan();

        $this->info(sprintf(
            'Pembayaran ditolak: %d, pesanan dibatalkan: %d.',
            $hasil['ditolak'],
            $hasil['dibatalkan']
        ));

        return self::SUCCESS;
    }
}
