<?php

namespace App\Support;

use App\Models\Pesanan;
use App\Models\UkuranStok;

/**
 * Pemesanan dan pengembalian stok varian.
 *
 * Aturan yang dipakai konsisten di seluruh aplikasi:
 * - checkout memotong stok (stok = "dipesan"),
 * - pesanan ditolak / dibatalkan → stok dikembalikan,
 * - pelanggan mengunggah ulang bukti setelah ditolak → stok dipesan lagi,
 * - pembayaran disetujui → stok tidak disentuh (masih dipesan).
 */
class Stok
{
    /** Kembalikan stok seluruh item pesanan. */
    public static function kembalikan(Pesanan $pesanan): void
    {
        foreach ($pesanan->detailPesanan as $detail) {
            UkuranStok::query()
                ->where('id_ukuran', $detail->id_ukuran)
                ->increment('stok', (int) $detail->jumlah);
        }
    }

    /**
     * Pesan ulang stok pesanan yang sebelumnya dikembalikan.
     * Mengembalikan false (tanpa mengubah apa pun) bila ada varian yang tidak cukup.
     */
    public static function pesanUlang(Pesanan $pesanan): bool
    {
        $detail = $pesanan->detailPesanan;

        foreach ($detail as $item) {
            $varian = UkuranStok::query()->find($item->id_ukuran);

            if (! $varian || (int) $varian->stok < (int) $item->jumlah) {
                return false;
            }
        }

        foreach ($detail as $item) {
            UkuranStok::query()
                ->where('id_ukuran', $item->id_ukuran)
                ->decrement('stok', (int) $item->jumlah);
        }

        return true;
    }
}
