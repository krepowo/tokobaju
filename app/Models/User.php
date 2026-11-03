<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    public const ROLE_PELANGGAN = 'pelanggan';
    public const ROLE_PENJUAL = 'penjual';

    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'no_telepon',
        'password',
        'role',
        'provinsi',
        'kota_kabupaten',
        'kecamatan',
        'kelurahan_desa',
        'kode_pos',
        'detail_alamat',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isPenjual(): bool
    {
        return $this->role === self::ROLE_PENJUAL;
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_user', 'id_user');
    }

    public function isPelanggan(): bool
    {
        return $this->role === self::ROLE_PELANGGAN;
    }

    /**
     * Normalisasi nomor telepon Indonesia ke format 08xxxxxxxxxx.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    /**
     * Cari akun berdasarkan email atau nomor telepon.
     */
    public static function findByIdentity(string $identity): ?self
    {
        $identity = trim($identity);

        if (str_contains($identity, '@')) {
            return static::whereRaw('LOWER(email) = ?', [mb_strtolower($identity)])->first();
        }

        return static::where('no_telepon', static::normalizePhone($identity))->first();
    }

    public function getIdentityAttribute(): string
    {
        return $this->email ?: (string) $this->no_telepon;
    }

    public function keranjang()
    {
        return $this->hasMany(Keranjang::class, 'id_user', 'id_user');
    }

    public function keranjangCount(): int
    {
        return (int) $this->keranjang()->sum('jumlah');
    }
}
