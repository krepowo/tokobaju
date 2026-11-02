<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TarifPengiriman extends Model
{
    use HasUuids;

    protected $table = 'tarif_pengiriman';

    protected $primaryKey = 'id_tarif';

    protected $fillable = [
        'id_jasa',
        'pulau_tujuan',
        'tarif',
    ];

    protected function casts(): array
    {
        return [
            'tarif' => 'decimal:2',
        ];
    }

    public function jasa()
    {
        return $this->belongsTo(JasaPengiriman::class, 'id_jasa', 'id_jasa');
    }
}
