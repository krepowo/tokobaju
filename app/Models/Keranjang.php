<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasUuids;

    protected $table = 'keranjang';

    protected $primaryKey = 'id_keranjang';

    protected $fillable = [
        'id_user',
        'id_ukuran',
        'jumlah',
        'dipilih',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'dipilih' => 'boolean',
        ];
    }

    public function ukuran()
    {
        return $this->belongsTo(UkuranStok::class, 'id_ukuran', 'id_ukuran');
    }
}
