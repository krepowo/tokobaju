<?php

namespace App\Support;

use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

/**
 * Penanganan pesanan yang melewati `batas_bayar` (1×24 jam).
 *
 * Dijalankan dari dua tempat supaya tidak bergantung pada cron:
 * 1. perintah `pembayaran:kedaluwarsa` (didaftarkan pada penjadwal Laravel),
 * 2. setiap kali halaman admin Verifikasi Bayar / Dashboard dibuka.
 *
 * Keduanya aman dijalankan berulang karena status pesanan berubah, sehingga
 * pesanan yang sama tidak diproses dua kali.
 */
class Kedaluwarsa
{
    /** Alasan penolakan yang ditulis sistem. */
    public const ALASAN = 'Melewati batas waktu pembayaran 1×24 jam.';

    /**
     * @return array{ditolak: int, dibatalkan: int}
     */
    public static function jalankan(): array
    {
        return [
            'ditolak' => self::tolakYangSudahBayar(),
            'dibatalkan' => self::batalkanYangBelumBayar(),
        ];
    }

    /**
     * Bukti sudah diunggah tetapi tidak sempat diverifikasi penjual.
     * Pembayaran ditolak, stok dikembalikan (pelanggan boleh unggah ulang).
     */
    private static function tolakYangSudahBayar(): int
    {
        $daftar = Pesanan::query()
            ->with(['detailPesanan', 'pembayaran'])
            ->where('status', 'menunggu_verifikasi')
            ->whereNotNull('batas_bayar')
            ->where('batas_bayar', '<', now())
            ->get();

        foreach ($daftar as $pesanan) {
            DB::transaction(function () use ($pesanan) {
                $pesanan->pembayaran()
                    ->where('status_verifikasi', 'menunggu')
                    ->update([
                        'status_verifikasi' => 'ditolak',
                        'alasan_tolak' => self::ALASAN,
                    ]);

                $pesanan->update(['status' => 'pembayaran_ditolak']);
                Stok::kembalikan($pesanan);
            });
        }

        return $daftar->count();
    }

    /**
     * Sama sekali belum ada bukti pembayaran sampai batas waktu.
     * Pesanan dibatalkan dan stok dikembalikan.
     */
    private static function batalkanYangBelumBayar(): int
    {
        $daftar = Pesanan::query()
            ->with(['detailPesanan'])
            ->where('status', 'menunggu_pembayaran')
            ->whereNotNull('batas_bayar')
            ->where('batas_bayar', '<', now())
            ->get();

        foreach ($daftar as $pesanan) {
            DB::transaction(function () use ($pesanan) {
                $pesanan->update(['status' => 'dibatalkan']);
                Stok::kembalikan($pesanan);
            });
        }

        return $daftar->count();
    }
}
