<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasUuids;

    public const MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const PEMBAYARAN_DITOLAK = 'pembayaran_ditolak';
    public const DIPROSES = 'diproses';
    public const DIKIRIM = 'dikirim';
    public const SELESAI = 'selesai';
    public const DIBATALKAN = 'dibatalkan';

    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

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

    protected function casts(): array
    {
        return [
            'tanggal_pesanan' => 'datetime',
            'batas_bayar' => 'datetime',
            'ongkir' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function jasa()
    {
        return $this->belongsTo(JasaPengiriman::class, 'id_jasa', 'id_jasa');
    }

    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id_pesanan', 'id_pesanan');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pesanan', 'id_pesanan')->latest('tanggal_upload');
    }

    public function pembayaranTerakhir()
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan', 'id_pesanan')->latestOfMany('tanggal_upload');
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'id_pesanan', 'id_pesanan');
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Pesanan yang dianggap menghasilkan omzet: pembayaran sudah disetujui.
     */
    public function scopeTerbayar($query)
    {
        return $query->whereIn('status', [self::DIPROSES, self::DIKIRIM, self::SELESAI])
            ->whereHas('pembayaran', fn ($q) => $q->where('status_verifikasi', 'disetujui'));
    }

    public function sudahKedaluwarsa(): bool
    {
        return $this->status === self::MENUNGGU_PEMBAYARAN
            && $this->batas_bayar !== null
            && $this->batas_bayar->isPast();
    }

    public function sisaWaktu(): ?string
    {
        if ($this->batas_bayar === null) {
            return null;
        }

        if ($this->batas_bayar->isPast()) {
            return 'Kedaluwarsa';
        }

        return $this->batas_bayar->diffForHumans(now(), [
            'parts' => 2,
            'short' => true,
            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
        ]);
    }
}
