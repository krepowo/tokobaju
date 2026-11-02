<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JasaPengiriman extends Model
{
    use HasUuids;

    protected $table = 'jasa_pengiriman';

    protected $primaryKey = 'id_jasa';

    protected $fillable = [
        'id_penjual',
        'nama_jasa',
    ];

    public function penjual()
    {
        return $this->belongsTo(User::class, 'id_penjual', 'id_user');
    }

    public function tarif()
    {
        return $this->hasMany(TarifPengiriman::class, 'id_jasa', 'id_jasa');
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_jasa', 'id_jasa');
    }
}
