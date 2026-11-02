<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifPengiriman extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tarif_pengiriman';
    protected $primaryKey = 'id_tarif';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jasa',
        'pulau_tujuan',
        'tarif',
    ];

    protected $casts = [
        'tarif' => 'decimal:2',
    ];

    public function jasa(): BelongsTo
    {
        return $this->belongsTo(JasaPengiriman::class, 'id_jasa', 'id_jasa');
    }
}
