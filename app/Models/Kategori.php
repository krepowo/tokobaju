<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_kategori',
    ];

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }

    /**
     * URL gambar ikon / thumbnail kategori.
     */
    public function getGambarUrlAttribute(): string
    {
        $map = [
            'atasan' => 'cat-atasan.jpg',
            'bawahan' => 'cat-bawahan.jpg',
            'outer' => 'cat-outer.jpg',
            'dress' => 'cat-dress.jpg',
            'aksesoris' => 'cat-aksesoris.jpg',
            'hijab' => 'cat-hijab.jpg',
            'sepatu' => 'cat-sepatu.jpg',
        ];

        $key = strtolower(trim($this->nama_kategori));
        $file = $map[$key] ?? 'cat-atasan.jpg';

        return asset('images/' . $file);
    }
}
