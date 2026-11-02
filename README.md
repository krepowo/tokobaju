# Toko Baju Online — Gavin Fashion Store

## Stack

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL 8 |
| Tampilan | Blade + CSS statis di `public/css/` |
| Lingkungan dev | Laragon (Nginx/Apache + MySQL) |

## Prasyarat

1. **Laragon Full** (versi terbaru) — sudah membundel PHP 8.2, MySQL 8, dan Composer.
   Unduh di <https://laragon.org/download>.

   - Pastikan PHP aktif **8.2** (Menu Laragon → PHP → Version).
   - Pastikan web server **Nginx** atau **Apache** aktif (Menu Laragon → Preferences).

2. **Git** (untuk clone dan kontribusi).

## Cara Setup dengan Laragon

### 1. Clone repository

```bash
git clone https://github.com/krepowo/tokobaju.git tokobajuonline
cd tokobajuonline
```

### 2. Aktifkan domain `.test`

Letakkan folder project di dalam document root Laragon (biasanya `C:\laragon\www`):

```text
C:\laragon\www\tokobajuonline
```

Laragon otomatis membuat host `http://tokobajuonline.test` (fitur *Pretty URL*).
Jika folder ditaruh di luar `www`, tambahkan host manual via menu Laragon →
**Nginx** (atau Apache) → **Sites Enabled** → **Add**.

> ⚠️ **Penting**: domain harus mengarah ke subfolder `public/`, bukan ke root project.
> Jika `http://tokobajuonline.test` menampilkan daftar file *"Index of /"*, berarti
> document root-nya masih menunjuk ke folder project. Lihat perbaikan di bawah.

### 3. Salin konfigurasi environment

```bash
cp .env.example .env
```

Buka `.env`, sesuaikan jika perlu. Default-nya sudah untuk Laragon:

```dotenv
APP_URL=http://tokobajuonline.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tokobajuonline
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat database

Lewat Menu Laragon → **Database** (HeidiSQL), atau jalankan di Terminal Laragon:

```sql
CREATE DATABASE tokobajuonline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Install dependency

```bash
composer install
```

### 6. Generate application key

```bash
php artisan key:generate
```

### 7. Migrasi dan seed data contoh

```bash
php artisan migrate:fresh --seed
```

Sekali jalan, ini membuat seluruh tabel (produk, kategori, pesanan, jasa pengiriman,
tarif, dll) dan mengisi data dummy: **8 produk, 36 varian, 2 jasa pengiriman,
12 tarif, 38 provinsi, 7 kategori**, plus 2 akun demo.

### 8. Jalankan

Buka:

```text
http://tokobajuonline.test
```

## Akun Demo

| Role | Email | Kata sandi |
|---|---|---|
| Pelanggan | `annisa.ayu@gmail.com` | `password` |
| Penjual (admin) | `admin@gavin.test` | `password` |

## Catatan Penting

- **Tanpa build frontend.** CSS/JS adalah file statis di `public/`, jadi **tidak perlu**
  `npm install` atau `npm run build`.
- Struktur database seluruhnya ada di **migrations + seeders** (`database/migrations/`,
  `database/seeders/`) — `migrate:fresh --seed` sudah cukup untuk menyiapkan data.

## Perbaikan "Index of /" (document root salah)

**Nginx** — Menu Laragon → Nginx → Sites Enabled → `auto.tokobajuonline.test.conf`, ubah baris `root`:

```nginx
root "C:/laragon/www/tokobajuonline/public";
```

Lalu reload Nginx (Menu Laragon → Nginx → Reload). Supaya perubahan tidak ditimpa Laragon,
ganti nama file dari `auto....conf` menjadi `tokobajuonline.test.conf` (buang prefix `auto.`).

**Apache** — ubah `DocumentRoot` menjadi subfolder `public`:

```apache
DocumentRoot "C:/laragon/www/tokobajuonline/public"
<Directory "C:/laragon/www/tokobajuonline/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

## Alternatif Tanpa Host `.test`

Jika tidak ingin mengatur domain, jalankan server bawaan Laravel:

```bash
php artisan serve
```

Lalu buka `http://127.0.0.1:8000`.

## Kontribusi

Lihat [CONTRIBUTING.md](CONTRIBUTING.md) untuk alur kerja Git kelompok (branch, commit,
dan Pull Request).
