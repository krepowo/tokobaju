<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy katalog + pengiriman untuk pengembangan fitur pelanggan,
 * supaya katalog/keranjang/checkout bisa dikerjakan tanpa menunggu
 * data produk dari sisi penjual (admin).
 *
 * Menghapus lalu mengisi ulang: produk, ukuran_stok, jasa_pengiriman,
 * dan tarif_pengiriman. Aman dijalankan pada database kosong (migrate:fresh).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $penjual = User::where('email', 'admin@gavin.test')->first();

        if (! $penjual) {
            $this->command?->warn('DemoSeeder dilewati: akun penjual admin@gavin.test belum ada.');

            return;
        }

        $kategori = DB::table('kategori')->pluck('id_kategori', 'nama_kategori');

        // Bersihkan data demo lama (urutan mengikuti arah foreign key).
        DB::table('tarif_pengiriman')->delete();
        DB::table('jasa_pengiriman')->delete();
        DB::table('ukuran_stok')->delete();
        DB::table('produk')->delete();

        $this->seedProduk($penjual, $kategori);
        $this->seedJasaPengiriman($penjual);

        $this->command?->info('Data demo siap: '.DB::table('produk')->count().' produk, '
            .DB::table('ukuran_stok')->count().' varian, '
            .DB::table('jasa_pengiriman')->count().' jasa, '
            .DB::table('tarif_pengiriman')->count().' tarif.');
    }

    private function seedProduk(User $penjual, $kategori): void
    {
        $daftar = [
            [
                'kategori' => 'Atasan',
                'nama' => 'Kaos Crewneck Katun Pria',
                'deskripsi' => 'Kaos lengan pendek bahan katun combed 24s, adem dan nyaman untuk aktivitas harian.',
                'gambar' => 'images/figma/home/prod-kaos.jpg',
                'harga' => 159000,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang | Panjang Lengan\nS | 50 cm | 66 cm | 19 cm\nM | 52 cm | 68 cm | 20 cm\nL | 55 cm | 70 cm | 21 cm\nXL | 58 cm | 72 cm | 22 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['S', 'M', 'L', 'XL'], 'stok' => 20],
                    ['warna' => 'Hitam', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 15],
                ],
            ],
            [
                'kategori' => 'Atasan',
                'nama' => 'Kemeja Oxford Casual',
                'deskripsi' => 'Kemeja lengan panjang berbahan oxford, cocok untuk gaya smart casual.',
                'gambar' => 'images/figma/home/cat-atasan.jpg',
                'harga' => 189000,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang | Panjang Lengan\nM | 54 cm | 70 cm | 60 cm\nL | 57 cm | 72 cm | 61 cm\nXL | 60 cm | 74 cm | 62 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 18],
                    ['warna' => 'Biru', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 12],
                ],
            ],
            [
                'kategori' => 'Bawahan',
                'nama' => 'Celana Chino Slim Fit',
                'deskripsi' => 'Celana chino potongan slim fit dengan bahan stretch yang nyaman dipakai.',
                'gambar' => 'images/figma/home/prod-chino.jpg',
                'harga' => 219000,
                'spesifikasi' => "Ukuran | Lingkar Pinggang | Panjang\n30 | 78 cm | 98 cm\n32 | 82 cm | 99 cm\n34 | 86 cm | 100 cm\n36 | 90 cm | 101 cm",
                'varian' => [
                    ['warna' => 'Navy', 'ukuran' => ['30', '32', '34', '36'], 'stok' => 16],
                    ['warna' => 'Beige', 'ukuran' => ['32', '34'], 'stok' => 10],
                ],
            ],
            [
                'kategori' => 'Dress',
                'nama' => 'Casual Midi Dress Linen',
                'deskripsi' => 'Dress midi berbahan linen ringan, elegan untuk acara santai maupun semi-formal.',
                'gambar' => 'images/figma/home/prod-dress.jpg',
                'harga' => 289000,
                'spesifikasi' => "Ukuran | Lingkar Dada | Panjang\nS | 84 cm | 110 cm\nM | 88 cm | 112 cm\nL | 92 cm | 114 cm",
                'varian' => [
                    ['warna' => 'Cream', 'ukuran' => ['S', 'M', 'L'], 'stok' => 14],
                    ['warna' => 'Sage', 'ukuran' => ['M', 'L'], 'stok' => 9, 'aktif' => false],
                ],
            ],
            [
                'kategori' => 'Outer',
                'nama' => 'Cardigan Knit Premium',
                'deskripsi' => 'Cardigan rajut tebal yang hangat, bisa dipadukan dengan banyak gaya.',
                'gambar' => 'images/figma/home/prod-cardigan.jpg',
                'harga' => 249000,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nM | 55 cm | 64 cm\nL | 58 cm | 66 cm\nXL | 62 cm | 68 cm",
                'varian' => [
                    ['warna' => 'Abu-abu', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 13],
                    ['warna' => 'Cokelat', 'ukuran' => ['L', 'XL'], 'stok' => 8],
                ],
            ],
            [
                'kategori' => 'Hijab',
                'nama' => 'Pashmina Ceruty Babydoll',
                'deskripsi' => 'Pashmina bahan ceruty babydoll, jatuh dan mudah dibentuk.',
                'gambar' => 'images/figma/home/cat-hijab.jpg',
                'harga' => 85000,
                'spesifikasi' => "Ukuran | Panjang | Lebar\nOne Size | 180 cm | 75 cm",
                'varian' => [
                    ['warna' => 'Maroon', 'ukuran' => ['One Size'], 'stok' => 30],
                    ['warna' => 'Dusty Pink', 'ukuran' => ['One Size'], 'stok' => 25],
                ],
            ],
            [
                'kategori' => 'Sepatu',
                'nama' => 'Sneakers Klasik Putih',
                'deskripsi' => 'Sneakers putik minimalis berbahan kulit sintetis, nyaman dipakai seharian.',
                'gambar' => 'images/figma/home/cat-sepatu.jpg',
                'harga' => 329000,
                'spesifikasi' => "Ukuran | Panjang Kaki\n39 | 25 cm\n40 | 25.5 cm\n41 | 26 cm\n42 | 26.5 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['39', '40', '41', '42'], 'stok' => 11],
                ],
            ],
            [
                'kategori' => 'Aksesori',
                'nama' => 'Kacamata Hitam Unisex',
                'deskripsi' => 'Kacamata hitam gaya klasik dengan lensa proteksi UV.',
                'gambar' => 'images/figma/home/cat-aksesori.jpg',
                'harga' => 129000,
                'spesifikasi' => null,
                'varian' => [
                    ['warna' => 'Hitam', 'ukuran' => ['One Size'], 'stok' => 22],
                ],
            ],
        ];

        foreach ($daftar as $p) {
            $idProduk = (string) Str::uuid();

            DB::table('produk')->insert([
                'id_produk' => $idProduk,
                'id_kategori' => $kategori[$p['kategori']],
                'id_penjual' => $penjual->id_user,
                'nama_produk' => $p['nama'],
                'deskripsi' => $p['deskripsi'],
                'gambar' => $p['gambar'],
                'harga' => $p['harga'],
                'spesifikasi_ukuran' => $p['spesifikasi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($p['varian'] as $v) {
                $aktif = $v['aktif'] ?? true;

                foreach ($v['ukuran'] as $u) {
                    DB::table('ukuran_stok')->insert([
                        'id_ukuran' => (string) Str::uuid(),
                        'id_produk' => $idProduk,
                        'ukuran' => $u,
                        'warna' => $v['warna'],
                        'stok' => $v['stok'],
                        'aktif' => $aktif,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedJasaPengiriman(User $penjual): void
    {
        $pulau = ['Jawa', 'Sumatera', 'Kalimantan', 'Sulawesi', 'Bali & Nusa Tenggara', 'Maluku & Papua'];

        $jasa = [
            ['nama' => 'JNE Reguler', 'tarif' => [9000, 17000, 24000, 26000, 20000, 42000]],
            ['nama' => 'J&T Express', 'tarif' => [8500, 16000, 23000, 25000, 19000, 40000]],
        ];

        foreach ($jasa as $j) {
            $idJasa = (string) Str::uuid();

            DB::table('jasa_pengiriman')->insert([
                'id_jasa' => $idJasa,
                'id_penjual' => $penjual->id_user,
                'nama_jasa' => $j['nama'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($pulau as $i => $p) {
                DB::table('tarif_pengiriman')->insert([
                    'id_tarif' => (string) Str::uuid(),
                    'id_jasa' => $idJasa,
                    'pulau_tujuan' => $p,
                    'tarif' => $j['tarif'][$i],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}