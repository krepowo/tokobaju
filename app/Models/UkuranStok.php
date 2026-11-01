<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UkuranStok extends Model
{
    use HasUuids;

    protected $table = 'ukuran_stok';

    protected $primaryKey = 'id_ukuran';

    protected $fillable = [
        'id_produk',
        'ukuran',
        'warna',
        'stok',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
