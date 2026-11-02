<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasUuids;

    protected $table = 'detail_pesanan';

    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_pesanan',
        'id_ukuran',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function ukuranStok()
    {
        return $this->belongsTo(UkuranStok::class, 'id_ukuran', 'id_ukuran');
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class, 'id_detail', 'id_detail');
    }
}
