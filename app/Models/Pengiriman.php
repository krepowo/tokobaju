<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasUuids;

    protected $table = 'pengiriman';

    protected $primaryKey = 'id_pengiriman';

    protected $fillable = [
        'id_pesanan',
        'no_resi',
        'tanggal_kirim',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kirim' => 'datetime',
        ];
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
