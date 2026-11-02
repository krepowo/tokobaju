<?php

namespace App\Support;

/**
 * Helper pembacaan string alamat pesanan.
 *
 * Format alamat dari checkout: "Nama Penerima (0812xxxx), jalan, kelurahan,
 * kecamatan, kota/kabupaten, provinsi kodepos".
 */
class Alamat
{
    /** Kota/kabupaten = segmen kedua dari belakang. */
    public static function kota(?string $alamat): string
    {
        $bagian = self::bagian($alamat);

        if ($bagian === []) {
            return '—';
        }

        return count($bagian) >= 2 ? $bagian[count($bagian) - 2] : $bagian[0];
    }

    /** Nomor telepon penerima, dari profil atau dari segmen pertama alamat. */
    public static function telepon(?string $alamat, ?string $teleponProfil): string
    {
        if (filled($teleponProfil)) {
            return $teleponProfil;
        }

        if (filled($alamat) && preg_match('/\(([0-9+\-\s]{6,})\)/', $alamat, $cocok) === 1) {
            return trim($cocok[1]);
        }

        return '—';
    }

    /** Buang "Nama (telepon), " di awal alamat karena sudah tampil terpisah. */
    public static function tanpaNama(?string $alamat): string
    {
        if (blank($alamat)) {
            return '—';
        }

        $bagian = explode(',', $alamat, 2);

        if (count($bagian) === 2 && preg_match('/\([0-9+\-\s]{6,}\)\s*$/', $bagian[0]) === 1) {
            return trim($bagian[1]);
        }

        return trim($alamat);
    }

    /**
     * @return list<string>
     */
    private static function bagian(?string $alamat): array
    {
        if (blank($alamat)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $alamat))));
    }
}
