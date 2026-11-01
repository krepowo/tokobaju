<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasUuids;

    protected $table = 'produk';

    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_kategori',
        'id_penjual',
        'nama_produk',
        'deskripsi',
        'gambar',
        'harga',
        'spesifikasi_ukuran',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_produk', 'id_produk');
    }
}
