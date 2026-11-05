<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_kategori',
        'id_penjual',
        'nama_produk',
        'deskripsi',
        'gambar',
        'harga',
        'spesifikasi_ukuran',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_penjual', 'id_user');
    }

    public function varianUkuran(): HasMany
    {
        return $this->hasMany(UkuranStok::class, 'id_produk', 'id_produk');
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'id_produk', 'id_produk');
    }

    /**
     * Resolusi URL gambar produk (dari upload penjual di storage, folder images, atau fallback).
     */
    public function getGambarUrlAttribute(): string
    {
        if (!empty($this->gambar)) {
            // 1. Jika gambar berupa URL eksternal lengkap
            if (str_starts_with($this->gambar, 'http://') || str_starts_with($this->gambar, 'https://')) {
                return $this->gambar;
            }

            // 2. Jika gambar hasil upload penjual di folder storage (storage/app/public/)
            if (file_exists(public_path('storage/' . $this->gambar)) || \Illuminate\Support\Facades\Storage::disk('public')->exists($this->gambar)) {
                return asset('storage/' . ltrim($this->gambar, '/'));
            }

            // 3. Jika gambar tersimpan di public/images/
            if (file_exists(public_path('images/' . $this->gambar))) {
                return asset('images/' . $this->gambar);
            }
        }

        // Fallback default jika foto belum diunggah
        return asset('images/prod-kaos.jpg');
    }

    /**
     * Format harga Rupiah.
     */
    public function getHargaFormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->harga, 0, ',', '.');
    }

    /**
     * Rating rata-rata dari relasi ulasan (dengan fallback 4.8).
     */
    public function getRatingRataRataAttribute(): float
    {
        $avg = $this->ulasan()->avg('rating');
        return $avg ? round((float) $avg, 1) : 0.0;
    }

    /**
     * Total ulasan dari relasi ulasan di database.
     */
    public function getTotalUlasanAttribute(): int
    {
        return $this->ulasan()->count();
    }
}
