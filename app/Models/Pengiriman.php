<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengiriman extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pengiriman';

    protected $primaryKey = 'id_pengiriman';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pesanan',
        'no_resi',
        'tanggal_kirim',
        'tanggal_diterima',
        'catatan',
    ];

    protected $casts = [
        'tanggal_kirim' => 'datetime',
        'tanggal_diterima' => 'datetime',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
