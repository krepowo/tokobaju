<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasUuids;

    protected $table = 'ulasan';

    protected $primaryKey = 'id_ulasan';

    protected $fillable = [
        'id_user',
        'id_produk',
        'id_detail',
        'rating',
        'komentar',
        'tanggal',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
