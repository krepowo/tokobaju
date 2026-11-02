<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keranjang extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'keranjang';
    protected $primaryKey = 'id_keranjang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'id_ukuran',
        'jumlah',
        'dipilih',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'dipilih' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function varianUkuran(): BelongsTo
    {
        return $this->belongsTo(UkuranStok::class, 'id_ukuran', 'id_ukuran');
    }

    /** Alias agar kontroler checkout lama memakai ->ukuran(). */
    public function ukuran(): BelongsTo
    {
        return $this->varianUkuran();
    }
}
