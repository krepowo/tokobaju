<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasUuids;

    protected $table = 'provinsi';

    protected $primaryKey = 'id_provinsi';

    protected $fillable = [
        'nama_provinsi',
        'pulau',
    ];
}
