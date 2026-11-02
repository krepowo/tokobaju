<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pesanan';
    protected $primaryKey = 'id_pesanan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'id_jasa',
        'tanggal_pesanan',
        'alamat_tujuan',
        'pulau_tujuan',
        'ongkir',
        'total',
        'status',
        'batas_bayar',
    ];

    protected $casts = [
        'tanggal_pesanan' => 'datetime',
        'batas_bayar' => 'datetime',
        'ongkir' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function jasaPengiriman(): BelongsTo
    {
        return $this->belongsTo(JasaPengiriman::class, 'id_jasa', 'id_jasa');
    }

    /** Alias agar halaman checkout/pesanan memakai ->jasa. */
    public function jasa(): BelongsTo
    {
        return $this->jasaPengiriman();
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /** Alias agar halaman pesanan memakai ->detailPesanan. */
    public function detailPesanan(): HasMany
    {
        return $this->detail();
    }

    public function statusLabel(): string
    {
        return [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'pembayaran_ditolak' => 'Pembayaran Ditolak',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ][$this->status] ?? $this->status;
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id_pesanan', 'id_pesanan');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_pesanan', 'id_pesanan');
    }

    public function pembayaranTerakhir(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan', 'id_pesanan')->latestOfMany('tanggal_upload');
    }

    public function pengiriman(): HasOne
    {
        return $this->hasOne(Pengiriman::class, 'id_pesanan', 'id_pesanan');
    }
}
