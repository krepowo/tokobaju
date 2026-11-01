<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JasaPengiriman extends Model
{
    use HasUuids;

    protected $table = 'jasa_pengiriman';

    protected $primaryKey = 'id_jasa';

    protected $fillable = ['id_penjual', 'nama_jasa'];
}
