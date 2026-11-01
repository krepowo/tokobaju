<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';

    public const ROLE_PELANGGAN = 'pelanggan';
    public const ROLE_PENJUAL = 'penjual';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'name',
        'email',
        'no_telepon',
        'phone',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accessor & Mutator untuk kompatibilitas nama field
    public function getNameAttribute(): ?string
    {
        return $this->attributes['nama'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nama'] = $value;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->attributes['no_telepon'] ?? $this->attributes['phone'] ?? null;
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['no_telepon'] = $value;
    }

    public function getIdAttribute(): ?string
    {
        return $this->attributes['id_user'] ?? null;
    }

    public function isPenjual(): bool
    {
        return $this->role === self::ROLE_PENJUAL;
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

        $normalized = static::normalizePhone($identity);

        return static::where(function ($query) use ($normalized) {
            $query->where('no_telepon', $normalized)
                ->orWhere('phone', $normalized);
        })->first();
    }

    public function getIdentityAttribute(): string
    {
        return $this->email ?: (string) ($this->no_telepon ?: $this->phone);
    }

    public function keranjang(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Keranjang::class, 'id_user', 'id_user');
    }

    public function pesanan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_user', 'id_user');
    }

    public function ulasan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ulasan::class, 'id_user', 'id_user');
    }

    public function produkPenjual(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Produk::class, 'id_penjual', 'id_user');
    }
}
