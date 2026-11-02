<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasUuids;

    public const MENUNGGU = 'menunggu';
    public const DISETUJUI = 'disetujui';
    public const DITOLAK = 'ditolak';

    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_pesanan',
        'diverifikasi_oleh',
        'bukti_transfer',
        'tanggal_upload',
        'status_verifikasi',
        'alasan_tolak',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_upload' => 'datetime',
        ];
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh', 'id_user');
    }
}
