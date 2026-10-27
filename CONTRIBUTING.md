# Panduan Kontribusi

Untuk anggota **Kelompok 1 — Toko Baju Online (Gavin Fashion Store)**.

Semua orang di sini pemula Git, jadi panduan ini ditulis langkah demi langkah: perintahnya bisa
di-copy, dan setiap langkah menjelaskan *kenapa*-nya. Baca bagian 0–2 sekali di awal, lalu bagian 3
setiap kali mau ngoding.

> **Satu aturan yang tidak boleh dilanggar:** jangan pernah mengerjakan fitur langsung di branch
> `main`. Selalu bikin branch sendiri, lalu gabungkan lewat Pull Request.

---

## 0. Istilah Git dalam 30 detik

| Istilah | Artinya |
|---|---|
| **Repository (repo)** | Folder project yang riwayat perubahannya dilacak Git. Punya kita ada di GitHub. |
| **Clone** | Menyalin repo dari GitHub ke komputer sendiri (sekali saja). |
| **Branch** | Cabang kerja. `main` = versi resmi. Fitur baru dikerjakan di branch sendiri supaya `main` tetap aman. |
| **Commit** | Menyimpan perubahan ke riwayat Git, disertai pesan singkat. Commit = titik simpan lokal. |
| **Push** | Mengirim commit dari komputer ke GitHub. |
| **Pull** | Mengambil perubahan terbaru dari GitHub ke komputer. |
| **Pull Request (PR)** | Permintaan menggabungkan branch kita ke `main`, sekaligus tempat review. |
| **Conflict** | Dua orang mengubah baris yang sama. Git tidak bisa memilih sendiri, jadi kita yang memutuskan. |

Alur besarnya:

```text
clone  →  pull  →  branch baru  →  edit  →  commit  →  push  →  Pull Request  →  merge
```

---

## 1. Persiapan (sekali saja)

### 1.1 Yang harus terpasang

| Aplikasi | Untuk apa | Catatan |
|---|---|---|
| **Git for Windows** | perintah `git` | https://git-scm.com/download/win — pilih "Git Bash" saat install |
| **Laragon** | PHP 8.2 + MySQL + HeidiSQL | jalankan **Start All** sebelum ngoding |
| **VS Code** | editor + penangan konflik | extension "GitLens" opsional |
| **Akun GitHub** | tempat repo | kirim username ke ketua kelompok untuk di-invite sebagai **Collaborator** |

Cek semuanya sudah terbaca:

```bash
git --version
php -v
composer --version
```

### 1.2 Daftarkan identitas Git

Cukup sekali. Nama dan email ini yang muncul di setiap commit.

```bash
git config --global user.name "Nama Kamu"
git config --global user.email "email@kamu.com"
git config --global core.autocrlf true
```

`core.autocrlf true` khusus Windows, supaya karakter baris baru tidak dianggap berubah terus.

### 1.3 Login GitHub dari Git

Saat `push` pertama kali, Git akan minta username dan password. **Password biasa tidak berlaku** —
GitHub memakai *Personal Access Token*:

1. Buka https://github.com/settings/tokens → **Generate new token (classic)**
2. Centang scope **`repo`**, atur masa berlaku, klik **Generate**
3. Copy token-nya (hanya muncul sekali)
4. Saat Git bertanya: username = username GitHub, password = **token** itu

Kalau muncul jendela browser "Sign in with your browser", itu Git Credential Manager — klik saja,
lebih mudah dan token tidak perlu dibuat manual.

---

## 2. Clone & jalankan project (sekali saja)

```bash
cd D:/Code/PHP
git clone https://github.com/krepowo/tokobaju.git
cd tokobaju
composer install
```

Buat file `.env` dari contohnya:

```bash
cp .env.example .env
```

Kalau memakai **Command Prompt** (bukan Git Bash), perintahnya `copy .env.example .env`.

Lalu isi bagian database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tokobajuonline
DB_USERNAME=root
DB_PASSWORD=
```

Buat database-nya (password Laragon default kosong):

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS tokobajuonline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Pilih salah satu** cara mengisi tabel:

**A. Import dump (disarankan — sudah ada data contoh)**

Unduh https://cdn.krepowo.my.id/files/tokobajuonline.sql lalu:

```bash
mysql -u root tokobajuonline < C:/Users/NAMA/Downloads/tokobajuonline.sql
```

Bisa juga lewat **HeidiSQL** (sudah ikut Laragon): buka database `tokobajuonline` → menu
*File → Run SQL file* → pilih file `.sql` tadi.

**B. Migrasi dari nol (database masih kosong)**

```bash
php artisan migrate --seed
```

Selesaikan setup:

```bash
php artisan key:generate
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000

Akun demo hasil seeder:

| Peran | Login | Kata sandi |
|---|---|---|
| Penjual | `admin@gavin.test` | `password` |
| Pelanggan | `annisa.ayu@gmail.com` | `password` |

> **Catatan:** `npm install` **tidak wajib**. Tampilan memakai `public/css/app.css` langsung, jadi
> tidak perlu build Vite. Kalau nanti memakai Vite, jalankan `npm install && npm run dev` di terminal
> terpisah.

---

## 3. Alur kerja harian (ini yang diulang terus)

### Langkah 1 — Ambil update terbaru

Selalu mulai dari `main` yang paling baru. Ini yang mencegah konflik.

```bash
git checkout main
git pull origin main
```

### Langkah 2 — Buat branch untuk pekerjaanmu

Satu pekerjaan = satu branch. Jangan campur "benerin login" dan "bikin katalog" di branch yang sama.

```bash
git checkout -b fitur/halaman-katalog
```

### Langkah 3 — Kerjakan, lalu commit

Cek apa yang berubah:

```bash
git status
git diff
```

Simpan perubahannya:

```bash
git add .
git commit -m "feat: tambah halaman katalog dengan filter kategori"
```

Commit kecil-kecil lebih baik daripada satu commit raksasa di akhir hari. Kalau macet di tengah,
commit saja dulu ("wip: ..."), nanti dirapikan.

### Langkah 4 — Kirim ke GitHub

```bash
git push -u origin fitur/halaman-katalog
```

`-u` hanya perlu di push pertama branch itu; setelah itu cukup `git push`.

### Langkah 5 — Buka Pull Request

1. Buka https://github.com/krepowo/tokobaju — akan muncul tombol **Compare & pull request**
2. Judul singkat, deskripsi: apa yang diubah dan cara mengetesnya
3. Klik **Create pull request**, lalu minta 1 orang mereview
4. Setelah disetujui, klik **Merge pull request**

### Langkah 6 — Bersihkan

```bash
git checkout main
git pull origin main
git branch -d fitur/halaman-katalog
```

Sekarang `main` di komputermu sudah berisi pekerjaanmu + pekerjaan teman.

---

## 4. Konvensi nama branch & pesan commit

### Branch

| Awalan | Dipakai untuk | Contoh |
|---|---|---|
| `fitur/` | fitur baru | `fitur/keranjang-belanja` |
| `fix/` | memperbaiki bug | `fix/stok-tidak-berkurang` |
| `ui/` | tampilan/CSS | `ui/halaman-login` |
| `db/` | migration, seeder, model | `db/tabel-pesanan` |
| `docs/` | dokumentasi | `docs/panduan-kontribusi` |

Gunakan huruf kecil dan tanda hubung. Jangan pakai spasi.

### Pesan commit

Formatnya: `tipe: penjelasan singkat` (maksimal ~60 karakter, tanpa titik di akhir).

| Tipe | Arti |
|---|---|
| `feat` | menambah fitur/halaman baru |
| `fix` | memperbaiki yang rusak |
| `style` | CSS, layout, spasi — tidak mengubah logika |
| `refactor` | merapikan kode tanpa mengubah perilaku |
| `docs` | dokumentasi, README, komentar |
| `chore` | konfigurasi, dependency, file pendukung |

Contoh benar:

```bash
git commit -m "feat: tambah validasi stok saat checkout"
git commit -m "fix: total invoice salah saat qty lebih dari 1"
git commit -m "style: rapikan jarak kartu produk di katalog"
git commit -m "docs: tambah panduan kontribusi"
```

Contoh yang bikin riwayat tidak berguna:

```text
LASJDHKASJD
update
asdasd
bismillah
final final beneran
```

Riwayat commit adalah catatan kerja kelompok — dan ikut dinilai saat presentasi.

---

## 5. Aturan main supaya tidak tabrakan

1. **`git pull origin main` sebelum mulai ngoding dan sebelum push.** Ini 90% penyebab konflik.
2. **Satu fitur = satu branch = satu PR.** PR kecil lebih cepat direview.
3. **Jangan commit file rahasia/berat:** `.env`, `vendor/`, `node_modules/`, `storage/logs/*`,
   `public/storage`, `*.sqlite`. Semuanya sudah ada di `.gitignore` — jangan dipaksa dengan `git add -f`.
4. **Kabari grup sebelum menyentuh file bersama.** File yang paling sering bentrok:
   `routes/web.php`, `resources/views/partials/header.blade.php`, `resources/views/partials/footer.blade.php`,
   `public/css/app.css`, `database/seeders/DatabaseSeeder.php`.
5. **Satu orang, satu file.** Kalau harus mengubah file yang sedang dipegang orang lain, bagi per
   bagian atau tunggu giliran.
6. **Jangan `git push --force`** ke `main` atau ke branch orang lain. Kalau terpaksa (branch sendiri),
   pakai `--force-with-lease` dan kabari grup.
7. **Jangan merge PR sendiri tanpa review**, kecuali darurat dan sudah disepakati.

---

## 6. Kalau terjadi konflik

Konflik muncul saat dua orang mengubah baris yang sama. Ini normal, bukan bencana.

### Cara aman (disarankan untuk pemula)

```bash
git checkout main
git pull origin main
git checkout fitur/halaman-katalog
git merge main
```

Buka VS Code. File yang konflik akan ditandai. Kamu akan melihat penanda seperti ini:

```text
<<<<<<< HEAD
$harga = $produk->price;
=======
$harga = $produk->harga;
>>>>>>> main
```

Arti: bagian atas = versi branch kamu, bagian bawah = versi `main`. Edit manual sampai menjadi versi
final yang benar, lalu **hapus ketiga penanda** (`<<<<<<<`, `=======`, `>>>>>>>`).

Setelah selesai:

```bash
git add .
git commit -m "merge: selesaikan konflik routes/web.php"
git push
```

### Cara cepat dengan rebase

```bash
git pull --rebase origin main
```

Ulangi perbaikan → `git add .` → `git rebase --continue`. Kalau panik dan ingin membatalkan:

```bash
git rebase --abort
```

### Kalau bingung memilih versi

```bash
git checkout --ours   routes/web.php   # pakai versi branch kamu
git checkout --theirs routes/web.php   # pakai versi main
```

Hati-hati: perintah ini menimpa seluruh file. Lebih baik minta bantuan ketua kelompok sambil
share screen daripada menebak.

---

## 7. Perintah penyelamat

| Situasi | Perintah |
|---|---|
| Batalkan perubahan file yang belum di-commit | `git restore nama-file.php` |
| Batalkan semua perubahan yang belum di-commit | `git restore .` |
| Simpan pekerjaan sementara, pindah branch dulu | `git stash` lalu `git stash pop` |
| Lihat commit terakhir | `git log --oneline -5` |
| Lihat siapa mengubah baris ini | `git blame nama-file.php` |
| Batal commit terakhir, perubahannya tetap ada | `git reset --soft HEAD~1` |
| Batalkan semuanya sampai commit terakhir (hati-hati) | `git reset --hard HEAD` |
| Ambil file tertentu dari `main` | `git checkout main -- path/file.php` |

Sebelum eksperimen berisiko, simpan dulu kondisi aman:

```bash
git branch cadangan-sebelum-eksperimen
```

---

## 8. Checklist sebelum minta merge

- [ ] `php artisan serve` jalan, halaman yang diubah bisa dibuka tanpa error
- [ ] Tidak ada `dd()`, `var_dump()`, atau `console.log` yang tertinggal
- [ ] Tidak ada file `.env`, `vendor/`, `node_modules/` yang ikut ter-commit (`git status` bersih dari itu)
- [ ] Kalau menambah migration: `php artisan migrate:fresh --seed` berhasil dari nol
- [ ] Kalau mengubah tampilan: dicek di layar kecil (mobile), bukan cuma desktop
- [ ] `git pull origin main` sudah dijalankan, tidak ada konflik
- [ ] Deskripsi PR menjelaskan **apa** dan **cara mengetesnya**

---

## 9. Error yang sering muncul

| Pesan error | Artinya | Solusi |
|---|---|---|
| `fatal: not a git repository` | terminal ada di folder yang salah | `cd D:/Code/PHP/tokobaju` |
| `Authentication failed` | memakai password biasa | pakai Personal Access Token (bagian 1.3) |
| `Updates were rejected because the remote contains work` | GitHub sudah lebih baru dari komputermu | `git pull --rebase origin main` lalu `git push` |
| `Your local changes would be overwritten by merge` | ada perubahan belum di-commit | `git add . && git commit -m "wip"` atau `git stash` |
| `error: pathspec 'fitur/x' did not match` | nama branch salah/typo | `git branch -a` untuk melihat daftar branch |
| `No application encryption key has been specified` | `.env` belum punya `APP_KEY` | `php artisan key:generate` |
| `SQLSTATE[42S02] Base table or view not found` | database belum diimport/migrasi | import dump atau `php artisan migrate` |
| `Class "..." not found` | autoload ketinggalan | `composer dump-autoload` |
| `Failed to listen on 127.0.0.1:8000` | port dipakai proses lain | `php artisan serve --port=8080` |
| Halaman putih / 500 | ada error tersembunyi | buka `storage/logs/laravel.log` |
| `The page has expired due to inactivity` | token CSRF kedaluwarsa | refresh halaman, submit form lagi |
| `npm ERR!` saat install | tidak wajib npm | lewati saja, project tidak butuh build Vite |

Kalau masih macet: **jangan** langsung `git reset --hard` atau menghapus folder. Screenshot error-nya,
kirim ke grup, tunggu yang lain bantu.

---

## 10. Ringkasan perintah (cheat sheet)

```bash
# mulai kerja
git checkout main
git pull origin main
git checkout -b fitur/nama-fitur

# selama kerja
git status
git diff
git add .
git commit -m "feat: penjelasan singkat"

# kirim
git push -u origin fitur/nama-fitur

# setelah PR di-merge
git checkout main
git pull origin main
git branch -d fitur/nama-fitur
```

---

## 11. Pembagian tugas

Isi tabel ini sesuai kesepakatan kelompok, dan update kalau ada perubahan. Satu baris = satu branch
= satu PR.

| Anggota | Bagian | Branch | Status |
|---|---|---|---|
|  | Login & registrasi | `fitur/auth` | selesai |
|  | Katalog & detail produk | `fitur/katalog` |  |
|  | Keranjang & checkout | `fitur/keranjang` |  |
|  | Pesanan, invoice, pembayaran | `fitur/pesanan` |  |
|  | Dashboard penjual | `fitur/dashboard-penjual` |  |
|  | Laporan omzet & ulasan | `fitur/laporan` |  |

Cara klaim tugas: tulis nama di tabel ini lewat branch `docs/pembagian-tugas`, atau cukup bilang di
grup lalu commit perubahannya. Yang penting tidak ada dua orang mengerjakan bagian yang sama.

---

## 12. Butuh bantuan?

1. Baca pesan error-nya dulu — biasanya menyebut file dan baris yang bermasalah
2. Cek `storage/logs/laravel.log` untuk error PHP/Laravel
3. Tanya di grup dengan menyertakan: **perintah yang dijalankan**, **pesan error lengkap**, **apa yang
   sedang dikerjakan**
4. Kalau menyangkut Git dan sudah mentok, jangan improvisasi — minta tolong sambil share screen
