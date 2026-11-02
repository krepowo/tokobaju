<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasUuids;

    protected $table = 'invoice';

    protected $primaryKey = 'id_invoice';

    protected $fillable = [
        'id_pesanan',
        'no_invoice',
        'tanggal_terbit',
        'total_tagihan',
        'rekening_tujuan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'datetime',
            'total_tagihan' => 'decimal:2',
        ];
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
