<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UkuranStok extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ukuran_stok';
    protected $primaryKey = 'id_ukuran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'ukuran',
        'warna',
        'gambar',
        'stok',
        'aktif',
    ];

    protected $casts = [
        'stok' => 'integer',
        'aktif' => 'boolean',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
