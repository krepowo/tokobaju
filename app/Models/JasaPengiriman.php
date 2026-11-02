<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JasaPengiriman extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jasa_pengiriman';

    protected $primaryKey = 'id_jasa';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_penjual',
        'nama_jasa',
    ];

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_penjual', 'id_user');
    }

    public function tarif(): HasMany
    {
        return $this->hasMany(TarifPengiriman::class, 'id_jasa', 'id_jasa');
    }

    /**
     * Label jumlah pulau: "6 Pulau Terdaftar" atau "1 Pulau Terdaftar (Jawa)".
     */
    public function labelPulau(): string
    {
        $jumlah = $this->tarif_count ?? $this->tarif->count();
        $label = $jumlah.' Pulau Terdaftar';

        if ($jumlah === 1 && $this->tarif->isNotEmpty()) {
            $label .= ' ('.$this->tarif->first()->pulau_tujuan.')';
        }

        return $label;
    }
}
