<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'provinsi';
    protected $primaryKey = 'id_provinsi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_provinsi',
        'pulau',
    ];
}
