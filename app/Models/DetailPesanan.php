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
        'id_pesanan', 'id_ukuran', 'jumlah', 'harga_satuan', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'float',
            'subtotal' => 'float',
        ];
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function ukuran()
    {
        return $this->belongsTo(UkuranStok::class, 'id_ukuran', 'id_ukuran');
    }
}
