<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy lengkap untuk pengembangan fitur pelanggan.
 *
 * Mengisi SEMUA tabel transaksional: kategori (lewat KategoriSeeder), provinsi
 * (lewat ProvinsiSeeder), users (lewat DatabaseSeeder), lalu di sini: produk,
 * ukuran_stok, keranjang, jasa_pengiriman, tarif_pengiriman, pesanan,
 * detail_pesanan, invoice, pembayaran, pengiriman, dan ulasan.
 *
 * Produk mengikuti frame katalog (74:2863): 8 produk beserta harga dan rating.
 * Aman dijalankan ulang; menghapus data demo lama dulu.
 */
class DemoSeeder extends Seeder
{
    private const PENJUAL_EMAIL = 'admin@gavin.test';
    private const PELANGGAN_EMAIL = 'annisa.ayu@gmail.com';
    private const REKENING = 'BCA 1234 5678 90 a.n. Gavin Fashion Store';

    public function run(): void
    {
        $penjual = User::where('email', self::PENJUAL_EMAIL)->first();
        $pelanggan = User::where('email', self::PELANGGAN_EMAIL)->first();

        if (! $penjual || ! $pelanggan) {
            $this->command?->warn('DemoSeeder dilewati: akun penjual/pelanggan belum ada.');
            return;
        }

        $kategori = DB::table('kategori')->pluck('id_kategori', 'nama_kategori');

        // Bersihkan data demo lama (urutan sesuai arah foreign key).
        foreach ([
            'ulasan', 'keranjang', 'detail_pesanan', 'pengiriman',
            'pembayaran', 'invoice', 'pesanan', 'tarif_pengiriman',
            'jasa_pengiriman', 'ukuran_stok', 'produk',
        ] as $table) {
            DB::table($table)->delete();
        }

        $produk = $this->seedProduk($penjual, $kategori);
        $jasa = $this->seedJasaPengiriman($penjual);
        $this->seedKeranjang($pelanggan, $produk);
        $this->seedPesanan($penjual, $pelanggan, $produk, $jasa);

        $this->command?->info('Data demo siap: '.DB::table('produk')->count().' produk, '
            .DB::table('ukuran_stok')->count().' varian, '
            .DB::table('pesanan')->count().' pesanan, '
            .DB::table('ulasan')->count().' ulasan.');
    }

    /**
     * @return array<string, array{id:string, harga:int, rating:?float, kategori:string, ukuran:list<string>}>
     */
    private function seedProduk(User $penjual, $kategori): array
    {
        $daftar = [
            [
                'kategori' => 'Dress', 'nama' => 'Casual Midi Dress Linen', 'harga' => 279000,
                'gambar' => 'images/figma/katalog/prod-midi-dress.png', 'rating' => 4.8,
                'spesifikasi' => "Ukuran | Lingkar Dada | Panjang\nS | 84 cm | 110 cm\nM | 88 cm | 112 cm\nL | 94 cm | 114 cm\nXL | 98 cm | 116 cm",
                'varian' => [['warna' => 'Cream', 'ukuran' => ['S', 'M', 'L', 'XL'], 'stok' => 14]],
            ],
            [
                'kategori' => 'Atasan', 'nama' => 'Kaos Crewneck Katun Pria', 'harga' => 159000,
                'gambar' => 'images/figma/katalog/prod-kaos-crewneck.png', 'rating' => 4.9,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nS | 50 cm | 66 cm\nM | 52 cm | 68 cm\nL | 55 cm | 70 cm\nXL | 58 cm | 72 cm\nXXL | 61 cm | 74 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'], 'stok' => 20],
                    ['warna' => 'Hitam', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 15],
                ],
            ],
            [
                'kategori' => 'Atasan', 'nama' => 'Kemeja Oxford Katun Pria', 'harga' => 249000,
                'gambar' => 'images/figma/katalog/prod-kemeja-oxford.png', 'rating' => 4.7,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang | Panjang Lengan\nM | 54 cm | 70 cm | 60 cm\nL | 57 cm | 72 cm | 61 cm\nXL | 60 cm | 74 cm | 62 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 18],
                    ['warna' => 'Biru', 'ukuran' => ['M', 'L'], 'stok' => 12],
                ],
            ],
            [
                'kategori' => 'Outer', 'nama' => 'Cardigan Rajut Oversize', 'harga' => 289000,
                'gambar' => 'images/figma/katalog/prod-cardigan-oversize.png', 'rating' => 4.7,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nM | 58 cm | 64 cm\nL | 61 cm | 66 cm\nXL | 65 cm | 68 cm",
                'varian' => [['warna' => 'Abu-abu', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 0]],
            ],
            [
                'kategori' => 'Atasan', 'nama' => 'Knit Turtleneck Hitam', 'harga' => 219000,
                'gambar' => 'images/figma/katalog/prod-turtleneck.png', 'rating' => 4.8,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nM | 52 cm | 64 cm\nL | 55 cm | 66 cm\nXL | 58 cm | 68 cm",
                'varian' => [['warna' => 'Hitam', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 16]],
            ],
            [
                'kategori' => 'Bawahan', 'nama' => 'Celana Smart Trousers Charcoal', 'harga' => 299000,
                'gambar' => 'images/figma/katalog/prod-trousers.png', 'rating' => null,
                'spesifikasi' => "Ukuran | Lingkar Pinggang | Panjang\n30 | 78 cm | 98 cm\n32 | 82 cm | 99 cm\n34 | 86 cm | 100 cm\n36 | 90 cm | 101 cm",
                'varian' => [['warna' => 'Charcoal', 'ukuran' => ['30', '32', '34', '36'], 'stok' => 12]],
            ],
            [
                'kategori' => 'Dress', 'nama' => 'Floral Summer Wrap Dress', 'harga' => 319000,
                'gambar' => 'images/figma/katalog/prod-wrap-dress.png', 'rating' => 4.9,
                'spesifikasi' => "Ukuran | Lingkar Dada | Panjang\nS | 84 cm | 105 cm\nM | 88 cm | 107 cm\nL | 92 cm | 109 cm",
                'varian' => [['warna' => 'Floral', 'ukuran' => ['S', 'M', 'L'], 'stok' => 10]],
            ],
            [
                'kategori' => 'Aksesori', 'nama' => 'Tas Kulit Selempang Minimalis', 'harga' => 349000,
                'gambar' => 'images/figma/katalog/prod-tas-kulit.png', 'rating' => 4.8,
                'spesifikasi' => "Ukuran | Panjang | Lebar\nOne Size | 22 cm | 16 cm",
                'varian' => [['warna' => 'Cokelat', 'ukuran' => ['One Size'], 'stok' => 8]],
            ],
        ];

        $result = [];

        foreach ($daftar as $i => $p) {
            $idProduk = (string) Str::uuid();
            $ukuranIds = [];

            DB::table('produk')->insert([
                'id_produk' => $idProduk,
                'id_kategori' => $kategori[$p['kategori']],
                'id_penjual' => $penjual->id_user,
                'nama_produk' => $p['nama'],
                'deskripsi' => 'Produk '.strtolower($p['nama']).' koleksi Gavin Fashion Store.',
                'gambar' => $p['gambar'],
                'harga' => $p['harga'],
                'spesifikasi_ukuran' => $p['spesifikasi'],
                'created_at' => now()->subMinutes($i + 1),
                'updated_at' => now(),
            ]);

            foreach ($p['varian'] as $v) {
                foreach ($v['ukuran'] as $u) {
                    $idUkuran = (string) Str::uuid();
                    $ukuranIds[] = $idUkuran;
                    DB::table('ukuran_stok')->insert([
                        'id_ukuran' => $idUkuran,
                        'id_produk' => $idProduk,
                        'ukuran' => $u,
                        'warna' => $v['warna'],
                        'stok' => $v['stok'],
                        'aktif' => $v['stok'] > 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $result[$p['nama']] = [
                'id' => $idProduk,
                'harga' => $p['harga'],
                'rating' => $p['rating'],
                'kategori' => $p['kategori'],
                'ukuran' => $ukuranIds,
            ];
        }

        return $result;
    }

    /**
     * @return list<array{id:string, nama:string}>
     */
    private function seedJasaPengiriman(User $penjual): array
    {
        $pulau = ['Jawa', 'Sumatera', 'Kalimantan', 'Sulawesi', 'Bali & Nusa Tenggara', 'Maluku & Papua'];
        $jasa = [
            ['nama' => 'JNE Reguler', 'tarif' => [9000, 17000, 24000, 26000, 20000, 42000]],
            ['nama' => 'J&T Express', 'tarif' => [8500, 16000, 23000, 25000, 19000, 40000]],
        ];

        $result = [];

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

            $result[] = ['id' => $idJasa, 'nama' => $j['nama']];
        }

        return $result;
    }

    private function seedKeranjang(User $pelanggan, array $produk): void
    {
        $item = [
            ['Kemeja Oxford Katun Pria', true],
            ['Floral Summer Wrap Dress', true],
            ['Tas Kulit Selempang Minimalis', false],
        ];

        foreach ($item as [$nama, $dipilih]) {
            DB::table('keranjang')->insert([
                'id_keranjang' => (string) Str::uuid(),
                'id_user' => $pelanggan->id_user,
                'id_ukuran' => $produk[$nama]['ukuran'][0],
                'jumlah' => 1,
                'dipilih' => $dipilih,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPesanan(User $penjual, User $pelanggan, array $produk, array $jasa): void
    {
        $idJasa = $jasa[0]['id'];
        $alamat = "Jl. Melati No. 12, Kel. Sukasari, Kec. Cimahi, Kota Cimahi, Jawa Barat, 40521";
        $bulanLalu = now()->subMonth();

        // Skema [nama_produk, jumlah, status]
        $pesanan = [
            [
                'status' => 'selesai', 'dibuat' => $bulanLalu->copy()->subDays(20),
                'item' => [
                    ['Casual Midi Dress Linen', 1],
                    ['Kaos Crewneck Katun Pria', 2],
                    ['Kemeja Oxford Katun Pria', 1],
                ],
            ],
            [
                'status' => 'selesai', 'dibuat' => $bulanLalu->copy()->subDays(12),
                'item' => [
                    ['Cardigan Rajut Oversize', 1],
                    ['Knit Turtleneck Hitam', 1],
                    ['Floral Summer Wrap Dress', 1],
                    ['Tas Kulit Selempang Minimalis', 1],
                ],
            ],
            [
                'status' => 'dikirim', 'dibuat' => $bulanLalu->copy()->subDays(4),
                'item' => [
                    ['Celana Smart Trousers Charcoal', 1],
                ],
            ],
            [
                'status' => 'menunggu_verifikasi', 'dibuat' => now()->subDays(2),
                'item' => [
                    ['Kaos Crewneck Katun Pria', 1],
                ],
            ],
            [
                'status' => 'menunggu_pembayaran', 'dibuat' => now()->subHours(3),
                'item' => [
                    ['Tas Kulit Selempang Minimalis', 1],
                ],
            ],
        ];

        foreach ($pesanan as $idx => $p) {
            $tanggal = $p['dibuat'];
            $items = [];

            foreach ($p['item'] as [$nama, $jumlah]) {
                $items[] = [
                    'id' => $produk[$nama]['id'],
                    'id_ukuran' => $produk[$nama]['ukuran'][0],
                    'harga' => $produk[$nama]['harga'],
                    'jumlah' => $jumlah,
                    'rating' => $produk[$nama]['rating'],
                ];
            }

            $subtotal = array_sum(array_map(fn ($it) => $it['harga'] * $it['jumlah'], $items));
            $ongkir = 9000; // Jawa
            $total = $subtotal + $ongkir;
            $no = 'INV-'.$tanggal->format('Ymd').'-'.str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT);

            $idPesanan = (string) Str::uuid();
            DB::table('pesanan')->insert([
                'id_pesanan' => $idPesanan,
                'id_user' => $pelanggan->id_user,
                'id_jasa' => $idJasa,
                'tanggal_pesanan' => $tanggal,
                'alamat_tujuan' => $alamat,
                'pulau_tujuan' => 'Jawa',
                'ongkir' => $ongkir,
                'total' => $total,
                'status' => $p['status'],
                'batas_bayar' => $tanggal->copy()->addDay(),
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            foreach ($items as $it) {
                $idDetail = (string) Str::uuid();
                DB::table('detail_pesanan')->insert([
                    'id_detail' => $idDetail,
                    'id_pesanan' => $idPesanan,
                    'id_ukuran' => $it['id_ukuran'],
                    'jumlah' => $it['jumlah'],
                    'harga_satuan' => $it['harga'],
                    'subtotal' => $it['harga'] * $it['jumlah'],
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                // Ulasan hanya untuk pesanan selesai dan produk yang punya rating.
                if ($p['status'] === 'selesai' && $it['rating'] !== null) {
                    DB::table('ulasan')->insert([
                        'id_ulasan' => (string) Str::uuid(),
                        'id_user' => $pelanggan->id_user,
                        'id_produk' => $it['id'],
                        'id_detail' => $idDetail,
                        'rating' => (int) floor($it['rating']),
                        'komentar' => 'Kualitas bagus dan sesuai deskripsi.',
                        'tanggal' => $tanggal->copy()->addDays(3),
                        'created_at' => $tanggal,
                        'updated_at' => $tanggal,
                    ]);
                }
            }

            // Invoice selalu dibuat saat checkout.
            DB::table('invoice')->insert([
                'id_invoice' => (string) Str::uuid(),
                'id_pesanan' => $idPesanan,
                'no_invoice' => $no,
                'tanggal_terbit' => $tanggal,
                'total_tagihan' => $total,
                'rekening_tujuan' => self::REKENING,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            // Pembayaran & pengiriman mengikuti status.
            if (in_array($p['status'], ['selesai', 'dikirim'], true)) {
                DB::table('pembayaran')->insert([
                    'id_pembayaran' => (string) Str::uuid(),
                    'id_pesanan' => $idPesanan,
                    'diverifikasi_oleh' => $penjual->id_user,
                    'bukti_transfer' => 'images/demo/bukti-transfer.jpg',
                    'tanggal_upload' => $tanggal->copy()->addHours(2),
                    'status_verifikasi' => 'disetujui',
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                DB::table('pengiriman')->insert([
                    'id_pengiriman' => (string) Str::uuid(),
                    'id_pesanan' => $idPesanan,
                    'no_resi' => 'JNE'.random_int(1000000000, 9999999999),
                    'tanggal_kirim' => $tanggal->copy()->addDay(),
                    'catatan' => 'Pesanan dikirim dari gudang Bandung.',
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
            } elseif ($p['status'] === 'menunggu_verifikasi') {
                DB::table('pembayaran')->insert([
                    'id_pembayaran' => (string) Str::uuid(),
                    'id_pesanan' => $idPesanan,
                    'diverifikasi_oleh' => null,
                    'bukti_transfer' => 'images/demo/bukti-transfer-2.jpg',
                    'tanggal_upload' => now()->subDay(),
                    'status_verifikasi' => 'menunggu',
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
            }
        }
    }
}
