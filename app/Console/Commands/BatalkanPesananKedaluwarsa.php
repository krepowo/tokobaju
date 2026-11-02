<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BatalkanPesananKedaluwarsa extends Command
{
    /**
     * Nama dan signature command console.
     */
    protected $signature = 'pesanan:batalkan-kedaluwarsa';

    /**
     * Deskripsi command console.
     */
    protected $description = 'Membatalkan pesanan yang belum dibayar setelah melewati batas waktu 1x24 jam dan mengembalikan stok';

    /**
     * Jalankan perintah.
     */
    public function handle(): int
    {
        $expiredOrders = Pesanan::query()
            ->with('detailPesanan')
            ->where('status', 'menunggu_pembayaran')
            ->whereNotNull('batas_bayar')
            ->where('batas_bayar', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$count) {
                // Kunci baris pesanan untuk menghindari race condition
                $fresh = Pesanan::where('id_pesanan', $order->id_pesanan)
                    ->lockForUpdate()
                    ->first();

                // Pastikan status masih menunggu_pembayaran sebelum memproses
                if (! $fresh || $fresh->status !== 'menunggu_pembayaran') {
                    return;
                }

                // Kembalikan stok untuk setiap varian ukuran yang dipesan
                foreach ($order->detailPesanan as $detail) {
                    if ($detail->id_ukuran && $detail->jumlah > 0) {
                        DB::table('ukuran_stok')
                            ->where('id_ukuran', $detail->id_ukuran)
                            ->increment('stok', $detail->jumlah);
                    }
                }

                // Ubah status pesanan menjadi dibatalkan
                $fresh->update([
                    'status' => 'dibatalkan',
                ]);

                $count++;
            });
        }

        $this->info("Berhasil memproses pembatalan: {$count} pesanan kedaluwarsa dibatalkan dan stok dikembalikan.");

        return self::SUCCESS;
    }
}

