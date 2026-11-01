<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DetailPesanan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id_detail';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pesanan',
        'id_ukuran',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function varianUkuran(): BelongsTo
    {
        return $this->belongsTo(UkuranStok::class, 'id_ukuran', 'id_ukuran');
    }

    public function ulasan(): HasOne
    {
        return $this->hasOne(Ulasan::class, 'id_detail', 'id_detail');
    }
}
