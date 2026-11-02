<?php

namespace App\Support;

/**
 * Label & warna badge status pesanan (dipakai halaman penjual).
 */
class StatusPesanan
{
    /**
     * @return array<string, array{label: string, badge: string}>
     */
    public static function semua(): array
    {
        return [
            'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'badge' => 'warn'],
            'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'badge' => 'info'],
            'pembayaran_ditolak' => ['label' => 'Pembayaran Ditolak', 'badge' => 'danger'],
            'diproses' => ['label' => 'Diproses', 'badge' => 'purple'],
            'dikirim' => ['label' => 'Dikirim', 'badge' => 'indigo'],
            'selesai' => ['label' => 'Selesai', 'badge' => 'success'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'badge' => 'muted'],
        ];
    }

    public static function label(?string $status): string
    {
        return self::semua()[$status]['label'] ?? ucfirst((string) $status);
    }

    public static function badge(?string $status): string
    {
        return self::semua()[$status]['badge'] ?? 'muted';
    }

    /**
     * Urutan proses pesanan untuk stepper status.
     *
     * @return list<string>
     */
    public static function alur(): array
    {
        return ['menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikirim', 'selesai'];
    }
}
