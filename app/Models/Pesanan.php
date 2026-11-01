<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasUuids;

    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    protected $fillable = [
        'id_user', 'id_jasa', 'tanggal_pesanan', 'alamat_tujuan',
        'pulau_tujuan', 'ongkir', 'total', 'status', 'batas_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pesanan' => 'datetime',
            'batas_bayar' => 'datetime',
            'ongkir' => 'float',
            'total' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function jasa()
    {
        return $this->belongsTo(JasaPengiriman::class, 'id_jasa', 'id_jasa');
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id_pesanan', 'id_pesanan');
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
}
