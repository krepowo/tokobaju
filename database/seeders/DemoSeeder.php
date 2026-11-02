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
        $this->seedUlasan($produk, $jasa[0]['id']);

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
                'gambar' => 'prod-dress-main.webp', 'rating' => 4.8,
                'deskripsi' => "Midi dress berbahan premium linen natural dengan siluet A-line yang anggun dan santai. Dilengkapi kerah V-neck yang feminin, kancing depan aktif, serta aksen ikat pinggang serasi yang memberikan siluet proporsional dan nyaman dikenakan sepanjang hari.\nMaterial: 100% Pure Organic Linen, breathable, sejuk, dan lembut di kulit.\nPotongan & Detail: Kerah V-neck, kancing depan bukaan penuh, saku samping fungsional, dan sabuk kain lepas-pasang.\nInstruksi Perawatan: Cuci dengan putaran lembut pada air dingin, jangan diperas kencang, jemur di tempat teduh, dan setrika dengan suhu sedang.",
                'spesifikasi' => "Ukuran | Lingkar Dada | Lingkar Pinggang | Panjang Dress\nS | 92 cm | 72 - 76 cm | 114 cm\nM | 96 cm | 76 - 80 cm | 115 cm\nL | 100 cm | 80 - 84 cm | 116 cm\nXL | 106 cm | 86 - 90 cm | 117 cm\nXXL | 112 cm | 92 - 96 cm | 118 cm",
                'varian' => [
                    ['warna' => 'Sky Stripe', 'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'], 'stok' => 12, 'habis' => ['XXL'], 'gambar' => 'prod-dress-sky-stripe.webp'],
                    ['warna' => 'Choco Stripe', 'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'], 'stok' => 12, 'habis' => ['XXL'], 'gambar' => 'prod-dress-choco-stripe.webp'],
                    ['warna' => 'Deep Brown', 'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'], 'stok' => 12, 'habis' => ['XXL'], 'gambar' => 'prod-dress-deep-brown.webp'],
                    ['warna' => 'Butter', 'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'], 'stok' => 12, 'habis' => ['XXL'], 'gambar' => 'prod-dress-butter.webp'],
                ],
            ],
            [
                'kategori' => 'Atasan', 'nama' => 'Kaos Crewneck Katun Pria', 'harga' => 159000,
                'gambar' => 'prod-kaos.jpg', 'rating' => 4.9,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nS | 50 cm | 66 cm\nM | 52 cm | 68 cm\nL | 55 cm | 70 cm\nXL | 58 cm | 72 cm\nXXL | 61 cm | 74 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'], 'stok' => 20],
                    ['warna' => 'Hitam', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 15],
                ],
            ],
            [
                'kategori' => 'Atasan', 'nama' => 'Kemeja Oxford Katun Pria', 'harga' => 249000,
                'gambar' => 'prod-kemeja-oxford.webp', 'rating' => 4.7,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang | Panjang Lengan\nM | 54 cm | 70 cm | 60 cm\nL | 57 cm | 72 cm | 61 cm\nXL | 60 cm | 74 cm | 62 cm",
                'varian' => [
                    ['warna' => 'Putih', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 18],
                    ['warna' => 'Biru', 'ukuran' => ['M', 'L'], 'stok' => 12],
                ],
            ],
            [
                'kategori' => 'Outer', 'nama' => 'Cardigan Rajut Oversize', 'harga' => 289000,
                'gambar' => 'prod-cardigan-oversize.webp', 'rating' => 4.7,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nM | 58 cm | 64 cm\nL | 61 cm | 66 cm\nXL | 65 cm | 68 cm",
                'varian' => [['warna' => 'Abu-abu', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 0]],
            ],
            [
                'kategori' => 'Atasan', 'nama' => 'Knit Turtleneck Hitam', 'harga' => 219000,
                'gambar' => 'prod-knit-turtleneck.webp', 'rating' => 4.8,
                'spesifikasi' => "Ukuran | Lebar Dada | Panjang\nM | 52 cm | 64 cm\nL | 55 cm | 66 cm\nXL | 58 cm | 68 cm",
                'varian' => [['warna' => 'Hitam', 'ukuran' => ['M', 'L', 'XL'], 'stok' => 16]],
            ],
            [
                'kategori' => 'Bawahan', 'nama' => 'Celana Smart Trousers Charcoal', 'harga' => 299000,
                'gambar' => 'prod-smart-trousers.webp', 'rating' => null,
                'spesifikasi' => "Ukuran | Lingkar Pinggang | Panjang\n30 | 78 cm | 98 cm\n32 | 82 cm | 99 cm\n34 | 86 cm | 100 cm\n36 | 90 cm | 101 cm",
                'varian' => [['warna' => 'Charcoal', 'ukuran' => ['30', '32', '34', '36'], 'stok' => 12]],
            ],
            [
                'kategori' => 'Dress', 'nama' => 'Floral Summer Wrap Dress', 'harga' => 319000,
                'gambar' => 'prod-floral-dress.webp', 'rating' => 4.9,
                'spesifikasi' => "Ukuran | Lingkar Dada | Panjang\nS | 84 cm | 105 cm\nM | 88 cm | 107 cm\nL | 92 cm | 109 cm",
                'varian' => [['warna' => 'Floral', 'ukuran' => ['S', 'M', 'L'], 'stok' => 10]],
            ],
            [
                'kategori' => 'Aksesori', 'nama' => 'Tas Kulit Selempang Minimalis', 'harga' => 349000,
                'gambar' => 'prod-tas-kulit.webp', 'rating' => 4.8,
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
                'deskripsi' => $p['deskripsi'] ?? 'Produk '.strtolower($p['nama']).' koleksi Gavin Fashion Store.',
                'gambar' => $p['gambar'],
                'harga' => $p['harga'],
                'spesifikasi_ukuran' => $p['spesifikasi'],
                'created_at' => now()->subMinutes($i + 1),
                'updated_at' => now(),
            ]);

            $seq = 0;
            foreach ($p['varian'] as $v) {
                $habis = $v['habis'] ?? [];
                foreach ($v['ukuran'] as $u) {
                    $idUkuran = (string) Str::uuid();
                    $ukuranIds[] = $idUkuran;
                    $stok = in_array($u, $habis, true) ? 0 : $v['stok'];
                    DB::table('ukuran_stok')->insert([
                        'id_ukuran' => $idUkuran,
                        'id_produk' => $idProduk,
                        'ukuran' => $u,
                        'warna' => $v['warna'],
                        'gambar' => $v['gambar'] ?? null,
                        'stok' => $stok,
                        'aktif' => $stok > 0,
                        'created_at' => now()->addSeconds($seq++),
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
            ['nama' => 'JNE', 'tarif' => [15000, 21000, 26000, 28000, 22000, 42000]],
            ['nama' => 'J&T', 'tarif' => [15000, 20000, 25000, 27000, 21000, 40000]],
            ['nama' => 'GoSend', 'tarif' => [20000, 28000, 34000, 36000, 30000, 50000]],
            ['nama' => 'GoShop', 'tarif' => [18000, 26000, 32000, 34000, 28000, 48000]],
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
                    'nama' => $nama,
                ];
            }

            $subtotal = array_sum(array_map(fn ($it) => $it['harga'] * $it['jumlah'], $items));
            $ongkir = 15000; // Jawa
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
                if ($p['status'] === 'selesai' && $it['rating'] !== null && $it['nama'] !== 'Casual Midi Dress Linen') {
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


    /**
     * Ulasan untuk Casual Midi Dress Linen (produk sorotan halaman detail F-04):
     * ringkasan "4,8 · 24 ulasan" (20×5 + 4×4 bintang) dan tiga ulasan teratas dengan
     * nama, ukuran, tanggal, dan teks sesuai desain Figma.
     */
    private function seedUlasan(array $produk, string $idJasa): void
    {
        $dressId = $produk['Casual Midi Dress Linen']['id'];

        $featured = [
            ['Aditya Wardhana', 'M', '2026-02-12 09:30:00',
             'Kualitas bahannya sangat bagus dan halus di kulit. Potongan linennya jatuh dengan rapi dan tidak panas dipakai seharian di kantor. Pengiriman cepat dan packaging sangat eksklusif.'],
            ['Clara Novita', 'S', '2026-01-28 10:15:00',
             'Sesuai dengan foto dan deskripsi! Warnanya broken white bersih, kancing terpasang kuat dan jahitannya rapi. Cocok dipadukan dengan celana bahan atau rok. Pasti repeat order di Gavin.'],
            ['Bambang Setiawan', 'L', '2026-01-15 11:00:00',
             'Bahan linennya bagus dan sejuk. Saran saya pilih satu size di atas jika ingin kesan sedikit lebih loose/oversized. Secara keseluruhan sangat puas dengan kualitasnya.'],
        ];

        $names = ['Aditya Wardhana', 'Clara Novita', 'Bambang Setiawan',
                  'Rizky Pratama', 'Sinta Dewi', 'Rahmat Hidayat',
                  'Larasati Putri', 'Bagus Saputra', 'Dewi Lestari'];

        $userIds = [];
        foreach ($names as $i => $nama) {
            $user = User::firstOrCreate(
                ['email' => Str::slug($nama, '.').'@gavin.test'],
                [
                    'nama' => $nama,
                    'no_telepon' => '0812'.str_pad((string) (34560000 + $i), 8, '0', STR_PAD_LEFT),
                    'password' => bcrypt('password'),
                    'role' => User::ROLE_PELANGGAN,
                ],
            );
            $userIds[] = $user->id_user;
        }

        $fillerComments = [
            'Barang sesuai deskripsi, bahan nyaman dipakai.',
            'Pengiriman cepat, packing rapi. Recommended!',
            'Warna dan jahitan bagus, worth it.',
            'Size pas, kualitas linennya adem.',
            'Repeat order, pelayanan memuaskan.',
        ];
        $fillerSizes = ['S', 'M', 'L', 'XL'];

        // 3 ulasan featured (5 bintang).
        $reviews = [];
        foreach ($featured as $i => $f) {
            $reviews[] = [
                'user' => $userIds[$i],
                'ukuran' => $f[1],
                'rating' => 5,
                'tanggal' => $f[2],
                'komentar' => $f[3],
            ];
        }

        // 21 ulasan filler: 17×5 bintang lalu 4×4 bintang, tanggal mundur (Des 2025).
        for ($i = 0; $i < 21; $i++) {
            $reviews[] = [
                'user' => $userIds[3 + ($i % 6)],
                'ukuran' => $fillerSizes[$i % 4],
                'rating' => $i < 17 ? 5 : 4,
                'tanggal' => sprintf('2025-12-%02d 09:00:00', 1 + $i),
                'komentar' => $fillerComments[$i % 5],
            ];
        }

        foreach ($reviews as $r) {
            $idUkuran = DB::table('ukuran_stok')
                    ->where('id_produk', $dressId)
                    ->where('ukuran', $r['ukuran'])
                    ->where('aktif', 1)
                    ->value('id_ukuran')
                ?: DB::table('ukuran_stok')
                    ->where('id_produk', $dressId)
                    ->where('ukuran', $r['ukuran'])
                    ->value('id_ukuran');

            $idPesanan = (string) Str::uuid();
            DB::table('pesanan')->insert([
                'id_pesanan' => $idPesanan,
                'id_user' => $r['user'],
                'id_jasa' => $idJasa,
                'tanggal_pesanan' => $r['tanggal'],
                'alamat_tujuan' => 'Jl. Demo No. 1, Kec. Coblong, Kota Bandung, Jawa Barat 40132',
                'pulau_tujuan' => 'Jawa',
                'ongkir' => 15000,
                'total' => 279000 + 15000,
                'status' => 'selesai',
                'batas_bayar' => null,
                'created_at' => $r['tanggal'],
                'updated_at' => $r['tanggal'],
            ]);

            $idDetail = (string) Str::uuid();
            DB::table('detail_pesanan')->insert([
                'id_detail' => $idDetail,
                'id_pesanan' => $idPesanan,
                'id_ukuran' => $idUkuran,
                'jumlah' => 1,
                'harga_satuan' => 279000,
                'subtotal' => 279000,
                'created_at' => $r['tanggal'],
                'updated_at' => $r['tanggal'],
            ]);

            DB::table('ulasan')->insert([
                'id_ulasan' => (string) Str::uuid(),
                'id_user' => $r['user'],
                'id_produk' => $dressId,
                'id_detail' => $idDetail,
                'rating' => $r['rating'],
                'komentar' => $r['komentar'],
                'tanggal' => $r['tanggal'],
                'created_at' => $r['tanggal'],
                'updated_at' => $r['tanggal'],
            ]);
        }
    }
}
