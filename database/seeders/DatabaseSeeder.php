<?php

namespace Database\Seeders;

use App\Models\JasaPengiriman;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Provinsi;
use App\Models\TarifPengiriman;
use App\Models\UkuranStok;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =====================================================================
        // 1. PENGGUNA (User: Penjual & Pelanggan)
        // =====================================================================
        $admin = User::updateOrCreate(
            ['email' => 'admin@gavin.test'],
            [
                'nama' => 'Admin Gavin',
                'no_telepon' => '081200000001',
                'password' => 'password',
                'role' => User::ROLE_PENJUAL,
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'annisa.ayu@gmail.com'],
            [
                'nama' => 'Annisa Ayu',
                'no_telepon' => '081234567890',
                'password' => 'password',
                'role' => User::ROLE_PELANGGAN,
            ]
        );

        $reviewer1 = User::updateOrCreate(
            ['email' => 'aditya.wardhana@gmail.com'],
            [
                'nama' => 'Aditya Wardhana',
                'no_telepon' => '081234567891',
                'password' => 'password',
                'role' => User::ROLE_PELANGGAN,
            ]
        );

        $reviewer2 = User::updateOrCreate(
            ['email' => 'clara.novita@gmail.com'],
            [
                'nama' => 'Clara Novita',
                'no_telepon' => '081234567892',
                'password' => 'password',
                'role' => User::ROLE_PELANGGAN,
            ]
        );

        $reviewer3 = User::updateOrCreate(
            ['email' => 'budi.santoso@gmail.com'],
            [
                'nama' => 'Budi Santoso',
                'no_telepon' => '081234567893',
                'password' => 'password',
                'role' => User::ROLE_PELANGGAN,
            ]
        );

        // =====================================================================
        // 2. KATEGORI (7 Kategori sesuai desain & aturan)
        // =====================================================================
        $kategoriList = [
            'Atasan',
            'Bawahan',
            'Outer',
            'Dress',
            'Aksesori',
            'Hijab',
            'Sepatu',
        ];

        $kategoriModels = [];
        foreach ($kategoriList as $namaKategori) {
            $kategoriModels[$namaKategori] = Kategori::updateOrCreate(
                ['nama_kategori' => $namaKategori]
            );
        }

        // =====================================================================
        // 3. 38 PROVINSI INDONESIA (Dikelompokkan ke 6 Pulau Resmi Aturan Bisnis)
        // Pulau: Jawa, Sumatera, Kalimantan, Sulawesi, Bali dan Nusa Tenggara, Maluku dan Papua
        // =====================================================================
        $provinsi38 = [
            // Sumatera (10)
            ['nama' => 'Aceh', 'pulau' => 'Sumatera'],
            ['nama' => 'Sumatera Utara', 'pulau' => 'Sumatera'],
            ['nama' => 'Sumatera Barat', 'pulau' => 'Sumatera'],
            ['nama' => 'Riau', 'pulau' => 'Sumatera'],
            ['nama' => 'Kepulauan Riau', 'pulau' => 'Sumatera'],
            ['nama' => 'Jambi', 'pulau' => 'Sumatera'],
            ['nama' => 'Sumatera Selatan', 'pulau' => 'Sumatera'],
            ['nama' => 'Kepulauan Bangka Belitung', 'pulau' => 'Sumatera'],
            ['nama' => 'Bengkulu', 'pulau' => 'Sumatera'],
            ['nama' => 'Lampung', 'pulau' => 'Sumatera'],

            // Jawa (6)
            ['nama' => 'DKI Jakarta', 'pulau' => 'Jawa'],
            ['nama' => 'Jawa Barat', 'pulau' => 'Jawa'],
            ['nama' => 'Banten', 'pulau' => 'Jawa'],
            ['nama' => 'Jawa Tengah', 'pulau' => 'Jawa'],
            ['nama' => 'DI Yogyakarta', 'pulau' => 'Jawa'],
            ['nama' => 'Jawa Timur', 'pulau' => 'Jawa'],

            // Bali dan Nusa Tenggara (3)
            ['nama' => 'Bali', 'pulau' => 'Bali dan Nusa Tenggara'],
            ['nama' => 'Nusa Tenggara Barat', 'pulau' => 'Bali dan Nusa Tenggara'],
            ['nama' => 'Nusa Tenggara Timur', 'pulau' => 'Bali dan Nusa Tenggara'],

            // Kalimantan (5)
            ['nama' => 'Kalimantan Barat', 'pulau' => 'Kalimantan'],
            ['nama' => 'Kalimantan Tengah', 'pulau' => 'Kalimantan'],
            ['nama' => 'Kalimantan Selatan', 'pulau' => 'Kalimantan'],
            ['nama' => 'Kalimantan Timur', 'pulau' => 'Kalimantan'],
            ['nama' => 'Kalimantan Utara', 'pulau' => 'Kalimantan'],

            // Sulawesi (6)
            ['nama' => 'Sulawesi Utara', 'pulau' => 'Sulawesi'],
            ['nama' => 'Gorontalo', 'pulau' => 'Sulawesi'],
            ['nama' => 'Sulawesi Tengah', 'pulau' => 'Sulawesi'],
            ['nama' => 'Sulawesi Barat', 'pulau' => 'Sulawesi'],
            ['nama' => 'Sulawesi Selatan', 'pulau' => 'Sulawesi'],
            ['nama' => 'Sulawesi Tenggara', 'pulau' => 'Sulawesi'],

            // Maluku dan Papua (8)
            ['nama' => 'Maluku', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Maluku Utara', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Papua', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Papua Barat', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Papua Selatan', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Papua Tengah', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Papua Pegunungan', 'pulau' => 'Maluku dan Papua'],
            ['nama' => 'Papua Barat Daya', 'pulau' => 'Maluku dan Papua'],
        ];

        foreach ($provinsi38 as $item) {
            Provinsi::updateOrCreate(
                ['nama_provinsi' => $item['nama']],
                ['pulau' => $item['pulau']]
            );
        }

        // =====================================================================
        // 4. JASA PENGIRIMAN & TARIF PER PULAU
        // =====================================================================
        $kurirList = [
            [
                'nama' => 'JNE Reguler',
                'tarif' => [
                    'Jawa' => 15000,
                    'Sumatera' => 25000,
                    'Bali dan Nusa Tenggara' => 28000,
                    'Kalimantan' => 32000,
                    'Sulawesi' => 35000,
                    'Maluku dan Papua' => 55000,
                ],
            ],
            [
                'nama' => 'J&T Express',
                'tarif' => [
                    'Jawa' => 16000,
                    'Sumatera' => 26000,
                    'Bali dan Nusa Tenggara' => 27000,
                    'Kalimantan' => 30000,
                    'Sulawesi' => 34000,
                    'Maluku dan Papua' => 52000,
                ],
            ],
            [
                'nama' => 'SiCepat Reguler',
                'tarif' => [
                    'Jawa' => 14000,
                    'Sumatera' => 24000,
                    'Bali dan Nusa Tenggara' => 26000,
                    'Kalimantan' => 31000,
                    'Sulawesi' => 33000,
                    'Maluku dan Papua' => 50000,
                ],
            ],
        ];

        foreach ($kurirList as $kurirData) {
            $jasa = JasaPengiriman::updateOrCreate(
                [
                    'id_penjual' => $admin->id_user,
                    'nama_jasa' => $kurirData['nama'],
                ]
            );

            foreach ($kurirData['tarif'] as $pulau => $tarifNominal) {
                TarifPengiriman::updateOrCreate(
                    [
                        'id_jasa' => $jasa->id_jasa,
                        'pulau_tujuan' => $pulau,
                    ],
                    ['tarif' => $tarifNominal]
                );
            }
        }

        // =====================================================================
        // 5. PRODUK KATALOG LENGKAP & VARIAN UKURAN-WARNA
        // =====================================================================
        $produkList = [
            [
                'kategori' => 'Dress',
                'nama' => 'Casual Midi Dress Linen',
                'harga' => 279000,
                'gambar' => 'prod-dress-main.webp',
                'deskripsi' => "Midi dress premium berbahan 100% serat linen organik alami terpilih yang menghadirkan siluet A-line feminin, jatuh anggun, serta memberikan kesejukan ekstra untuk iklim tropis. Dirancang dengan kerah V-neck presisi yang mempertegas garis leher, deretan kancing depan aktif dengan aksen resin natural, serta tali ikat pinggang fleksibel yang dapat disesuaikan untuk menonjolkan siluet tubuh yang proporsional.\n\nDetail Material & Konstruksi:\n- 100% Pure Organic Linen pre-washed (tidak menyusut setelah dicuci).\n- Gramasi benang optimal, tidak menerawang namun tetap sangat breathable dan ringan.\n- Dua saku samping tersembunyi (hidden side pockets) yang fungsional.\n- Jahitan tepi obras rapat standar ekspor dan finishing hem halus.\n\nPanduan Gaya (Styling Advice):\nSangat fleksibel untuk berbagai suasana: padukan dengan strappy sandals dan tas anyaman untuk liburan akhir pekan santai, atau pasangkan dengan mules dan blazer tipis untuk tampilan smart-casual ke kantor.\n\nPetunjuk Perawatan:\nCuci dengan tangan atau siklus lembut mesin cuci menggunakan air dingin. Hindari pemutih klorin. Keringkan di tempat teduh dan setrika saat kain masih sedikit lembap dengan suhu sedang.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '92 cm', 'lingkar_pinggang' => '72 - 76 cm', 'panjang' => '114 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '96 cm', 'lingkar_pinggang' => '76 - 80 cm', 'panjang' => '115 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '100 cm', 'lingkar_pinggang' => '80 - 84 cm', 'panjang' => '116 cm'],
                    ['ukuran' => 'XL', 'lingkar_dada' => '108 cm', 'lingkar_pinggang' => '86 - 90 cm', 'panjang' => '117 cm'],
                    ['ukuran' => 'XXL', 'lingkar_dada' => '112 cm', 'lingkar_pinggang' => '92 - 96 cm', 'panjang' => '118 cm'],
                ]),
                'warna' => ['Sky Stripe', 'Choco Stripe', 'Deep Brown', 'Butter'],
                'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'],
                'stok_default' => 15,
                'stok_custom' => [
                    'Sky Stripe_M' => 12,
                    'Sky Stripe_S' => 8,
                    'Choco Stripe_M' => 10,
                ],
            ],
            [
                'kategori' => 'Atasan',
                'nama' => 'Kaos Crewneck Katun Pria',
                'harga' => 159000,
                'gambar' => 'prod-kaos.jpg',
                'deskripsi' => "Kaos crewneck esensial premium yang dirancang khusus untuk kenyamanan maksimal pria urban sehari-hari. Dibuat dari 100% katun combed 24s berkualitas tinggi dengan gramasi 185-195 gsm yang memberikan ketebalan pas—tidak tipis menerawang dan tidak gerah saat dipakai di bawah terik matahari. Kain telah melalui proses enzyme wash ganda sehingga permukaan terasa sehalus sutra serta bebas bulu halus.\n\nDetail Material & Keunggulan:\n- 100% Premium Combed Cotton 24s berdaya serap keringat tinggi.\n- Kerah rib elastis berbahan spandeks katun kualitas tinggi dengan jahitan stick dobel yang tidak melar atau bergelombang setelah dicuci berkali-kali.\n- Jahitan rantai pada bagian pundak dan leher belakang dengan pita katun lembut (cotton taping) untuk mencegah iritasi kulit leher.\n- Potongan regular fit modern yang pas di bahu dan dada tanpa rasa sesak.\n\nPanduan Gaya:\nItem wajib serbaguna! Kenakan tunggal bersama celana chino atau denim untuk gaya kasual abadi, atau gunakan sebagai inner layer di bawah kemeja flanel, jaket denim, maupun blazer kasual.\n\nPetunjuk Perawatan:\nCuci pakaian dengan warna serupa dalam suhu air normal. Jangan gunakan mesin pengering bersuhu tinggi. Setrika bagian dalam kaos pada suhu sedang.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '96 cm', 'lingkar_pinggang' => '94 cm', 'panjang' => '68 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '100 cm', 'lingkar_pinggang' => '98 cm', 'panjang' => '70 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '106 cm', 'lingkar_pinggang' => '104 cm', 'panjang' => '72 cm'],
                    ['ukuran' => 'XL', 'lingkar_dada' => '112 cm', 'lingkar_pinggang' => '110 cm', 'panjang' => '74 cm'],
                    ['ukuran' => 'XXL', 'lingkar_dada' => '118 cm', 'lingkar_pinggang' => '116 cm', 'panjang' => '76 cm'],
                ]),
                'warna' => ['Offwhite', 'Black', 'Navy', 'Heather Grey'],
                'ukuran' => ['S', 'M', 'L', 'XL', 'XXL'],
                'stok_default' => 20,
            ],
            [
                'kategori' => 'Atasan',
                'nama' => 'Kemeja Oxford Katun Pria',
                'harga' => 249000,
                'gambar' => 'prod-kemeja-oxford.webp',
                'deskripsi' => "Kemeja lengan panjang bergaya button-down klasik modern yang menjadi pilar busana pria profesional maupun santai. Memanfaatkan tenunan benang Oxford ganda bertekstur 'basketweave' yang khas, kemeja ini menyatukan ketahanan struktur kain dengan kelembutan serat katun alami. Tampilan rapi yang tidak kaku menjadikannya pilihan andalan dari ruang rapat hingga kencan makan malam.\n\nDetail Material & Fitur:\n- 100% Fine Long-Staple Cotton Oxford weave yang tahan lama dan bernapas.\n- Kerah button-down kokoh dengan interlining berkualitas agar bentuk kerah tetap tegak rapi.\n- Kancing resin berkilau mutiara yang dijahit silang kuat tahan copot.\n- Saku dada klasik dan placket depan berpotongan bersih.\n- Manset pergelangan tangan dengan dua kancing pengatur kelonggaran.\n\nPanduan Gaya:\nTampil smart-casual dengan melipat lengan dua kali dipadukan celana chino berwarna kontras, atau masukkan kemeja ke dalam celana smart trousers dengan sabuk kulit untuk pertemuan formal.\n\nPetunjuk Perawatan:\nBisa dicuci dengan mesin pada siklus normal. Disarankan langsung digantung setelah selesai dicuci untuk mempermudah proses menyetrika dengan uap sedang.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '98 cm', 'lingkar_pinggang' => '96 cm', 'panjang' => '70 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '102 cm', 'lingkar_pinggang' => '100 cm', 'panjang' => '72 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '108 cm', 'lingkar_pinggang' => '106 cm', 'panjang' => '74 cm'],
                    ['ukuran' => 'XL', 'lingkar_dada' => '114 cm', 'lingkar_pinggang' => '112 cm', 'panjang' => '76 cm'],
                ]),
                'warna' => ['Pure White', 'Light Blue', 'Soft Grey'],
                'ukuran' => ['S', 'M', 'L', 'XL'],
                'stok_default' => 15,
            ],
            [
                'kategori' => 'Outer',
                'nama' => 'Cardigan Rajut Oversize',
                'harga' => 289000,
                'gambar' => 'prod-cardigan-oversize.webp',
                'deskripsi' => "Cardigan rajut wanita dengan siluet relaxed drop-shoulder bergaya vintage Jepang modern. Dirajut menggunakan benang akrilik premium 7-gauge berserat lembut yang memberikan kehangatan lembut tanpa rasa gatal atau berat di pundak. Potongannya yang longgar dan mengalir memberikan kenyamanan ekstra serta siluet estetis yang sangat fotogenik.\n\nDetail Material & Desain:\n- Benang rajut premium anti-pilling bersertifikasi, halus dan lembut di kulit.\n- Aksen 4 kancing marmer eksklusif berukuran besar yang mewah di bagian depan.\n- Rib tebal elastis pada bagian pergelangan tangan dan ujung bawah yang mengunci kehangatan.\n- Kerah V-neck dalam yang sempurna memperlihatkan dalaman inner blouse, kaos putih, atau dress.\n\nPanduan Gaya:\nPadukan di atas gaun musim panas untuk transisi cuaca sejuk, atau kombinasikan dengan celana jeans high-waist dan sepatu boots untuk nuansa effortless autumn aesthetic.\n\nPetunjuk Perawatan:\nSangat disarankan dicuci tangan menggunakan deterjen cair lembut. Jangan diperas dengan memelintir kain. Keringkan secara mendatar (flat dry) untuk menjaga bentuk rajutan agar tidak meregang.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '104 cm', 'lingkar_pinggang' => '104 cm', 'panjang' => '60 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '110 cm', 'lingkar_pinggang' => '110 cm', 'panjang' => '62 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '116 cm', 'lingkar_pinggang' => '116 cm', 'panjang' => '64 cm'],
                    ['ukuran' => 'XL', 'lingkar_dada' => '122 cm', 'lingkar_pinggang' => '122 cm', 'panjang' => '66 cm'],
                ]),
                'warna' => ['Maroon', 'Beige', 'Baby Blue', 'Sage Green'],
                'ukuran' => ['S', 'M', 'L', 'XL'],
                'stok_default' => 12,
                'stok_custom' => [
                    'Maroon_S' => 7,
                    'Maroon_M' => 21,
                    'Maroon_L' => 12,
                    'Maroon_XL' => 4,
                    'Beige_S' => 9,
                    'Beige_M' => 25,
                    'Beige_L' => 15,
                    'Beige_XL' => 3,
                    'Baby Blue_S' => 5,
                    'Baby Blue_M' => 16,
                    'Baby Blue_L' => 8,
                    'Baby Blue_XL' => 0,
                    'Sage Green_S' => 8,
                    'Sage Green_M' => 19,
                    'Sage Green_L' => 11,
                    'Sage Green_XL' => 2,
                ],
            ],
            [
                'kategori' => 'Atasan',
                'nama' => 'Knit Turtleneck',
                'harga' => 199000,
                'gambar' => 'prod-knit-turtleneck.webp',
                'deskripsi' => "Atasan rajut bertekstur rib halus dengan kerah turtleneck tinggi yang memeluk leher secara lembut dan elegan. Dibuat dengan konstruksi seamless rib-knit yang memiliki elastisitas luar biasa, membentuk kurva tubuh secara anggun (flattering slim-fit) tanpa terasa mengekang pernapasan atau gerak tubuh.\n\nDetail Material & Keunggulan:\n- Kombinasi Viscose & Polyamide rajut premium yang memiliki kilau mewah, super adem, dan sangat lembut.\n- Kerah lipat ganda (double-fold collar) yang tegak elegan dan tidak mudah kusut.\n- Keliman tepi dijahit rapi dengan jahitan overlock presisi tinggi.\n- Ketebalan medium yang pas: tidak menerawang dan sangat ideal untuk layering di bawah mantel, blazer, atau jaket kulit.\n\nPanduan Gaya:\nKenakan bersama rok plisket midi untuk penampilan feminin anggun, atau masukkan ke dalam celana high-waist berpotongan lebar untuk ilusi kaki yang lebih jenjang.\n\nPetunjuk Perawatan:\nCuci lembut dengan air dingin menggunakan kantung cuci (laundry net). Jemur terbentang di atas permukaan datar. Jangan disetrika langsung dengan suhu tinggi.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '84 - 90 cm', 'lingkar_pinggang' => '70 - 76 cm', 'panjang' => '56 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '88 - 94 cm', 'lingkar_pinggang' => '74 - 80 cm', 'panjang' => '58 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '94 - 100 cm', 'lingkar_pinggang' => '80 - 86 cm', 'panjang' => '60 cm'],
                ]),
                'warna' => ['Cream', 'Black', 'Mocha'],
                'ukuran' => ['S', 'M', 'L'],
                'stok_default' => 14,
            ],
            [
                'kategori' => 'Bawahan',
                'nama' => 'Celana Smart Trousers',
                'harga' => 249000,
                'gambar' => 'prod-smart-trousers.webp',
                'deskripsi' => "Celana panjang pintar berpotongan tapered ankle-length yang menggabungkan estetika formal celana tailoring bespoke dengan kenyamanan celana santai sehari-hari. Menggunakan bahan stretch semi-wool impor yang memiliki drape jatuh berbobot, anti-kusut, dan dilengkapi karet pinggang tersembunyi (hidden elastic waistband) di kedua sisi yang fleksibel mengikuti pergerakan tubuh Anda.\n\nDetail Material & Fitur:\n- Kain Semi-Wool Blend Stretch 4-way yang tahan kusut dan bebas gerah.\n- Lipit depan permanen (sharp front crease) yang mempertahankan kesan rapi sepanjang hari tanpa perlu sering disetrika.\n- Dua saku samping miring yang dalam dan dua saku belakang fungsional berpenutup kancing.\n- Ritsleting YKK otomatis tahan macet dan kancing pengait kait besi ganda standar tailored pants.\n\nPanduan Gaya:\nPilihan utama para eksekutif muda! Sempurna dipadankan dengan kemeja oxford dan sepatu loafer untuk ngantor, atau dengan kaos polos dan sneakers putih untuk weekend coffee run.\n\nPetunjuk Perawatan:\nCuci kering (dry clean) dianjurkan untuk hasil terbaik, atau cuci mesin dengan putaran paling lembut menggunakan air dingin. Setrika dengan pelindung kain pada suhu sedang.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '-', 'lingkar_pinggang' => '74 - 78 cm', 'panjang' => '92 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '-', 'lingkar_pinggang' => '78 - 82 cm', 'panjang' => '94 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '-', 'lingkar_pinggang' => '82 - 88 cm', 'panjang' => '96 cm'],
                    ['ukuran' => 'XL', 'lingkar_dada' => '-', 'lingkar_pinggang' => '88 - 94 cm', 'panjang' => '98 cm'],
                ]),
                'warna' => ['Charcoal', 'Black', 'Navy'],
                'ukuran' => ['S', 'M', 'L', 'XL'],
                'stok_default' => 10,
                'stok_custom' => [
                    'Charcoal_L' => 1, // Untuk simulasi "Stok tidak cukup" jika di keranjang jumlahnya 3
                ],
            ],
            [
                'kategori' => 'Dress',
                'nama' => 'Floral Summer Wrap',
                'harga' => 299000,
                'gambar' => 'prod-floral-dress.webp',
                'deskripsi' => "Gaun lilit (wrap dress) motif floral feminin bernuansa cerah yang terinspirasi dari keindahan musim semi di Riviera. Dibuat dari bahan chiffon ceruti premium bertabur motif bunga eksklusif yang lembut, gaun ini dilengkapi furing katun adem penuh di seluruh badan sehingga terjamin 100% tidak menerawang dan nyaman bergerak.\n\nDetail Material & Desain:\n- Kain Ceruti Chiffon Premium bertekstur jatuh melayang (flowy) dengan furing katun lembut.\n- Tali lilit pinggang (side wrap tie) yang bisa disesuaikan bebas dengan lingkar pinggang Anda.\n- Keliman bawah dengan aksen ruffle bertingkat halus yang mekar anggun saat Anda melangkah.\n- Lengan semi-balon dengan karet pergelangan tangan elastis yang wudhu-friendly.\n\nPanduan Gaya:\nSangat memikat untuk momen istimewa seperti garden party, brunch santai, liburan pantai, hingga sesi foto pre-wedding. Cukup tambahkan heels minimalis atau wedges dan tas selempang kulit.\n\nPetunjuk Perawatan:\nCuci tangan menggunakan air dingin dan sampo pakaian berbahan lembut. Jangan diperas dengan mesin cuci. Jemur gantung di tempat yang teduh.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '88 cm', 'lingkar_pinggang' => '68 - 74 cm', 'panjang' => '112 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '92 cm', 'lingkar_pinggang' => '72 - 78 cm', 'panjang' => '114 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '98 cm', 'lingkar_pinggang' => '78 - 84 cm', 'panjang' => '116 cm'],
                ]),
                'warna' => ['Sky Floral', 'Blush Pink', 'Cream Daisy'],
                'ukuran' => ['S', 'M', 'L'],
                'stok_default' => 15,
            ],
            [
                'kategori' => 'Aksesori',
                'nama' => 'Tas Kulit Selempang',
                'harga' => 219000,
                'gambar' => 'prod-tas-kulit.webp',
                'deskripsi' => "Tas selempang multifungsi berstruktur kokoh dengan sentuhan modern minimalis mewah. Dibuat dari material premium vegan nappa leather berkualitas tinggi yang tahan goresan ringan dan tahan cipratan air. Dilengkapi hardware logam emas (gold hardware) berpelapis anti-karat yang memberikan aksen eksklusif pada setiap detailnya.\n\nDetail Material & Fitur:\n- Premium Vegan Nappa Leather tebal dengan permukaan halus bertekstur mewah.\n- Kompartemen utama luas dilapisi furing suede sintetis yang lembut, dilengkapi slot kartu dan saku ritsleting rahasia untuk barang berharga.\n- Pengunci magnetik kuat (magnetic snap closure) yang menutup presisi dan aman.\n- Tali selempang kulit dengan 5 lubang penyesuaian panjang dan bantalan pundak empuk.\n- Dimensi kompak: 24 cm x 16 cm x 8 cm, muat untuk smartphone ukuran besar, powerbank, dompet, dan perlengkapan makeup harian.\n\nPanduan Gaya:\nPelengkap esensial untuk melengkapi seluruh busana Anda, dari gaya kasual santai hingga tampilan semi-formal malam hari.\n\nPetunjuk Perawatan:\nBersihkan permukaan tas secara berkala menggunakan kain microfiber lembut yang sedikit dibasahi air. Simpan di dalam dustbag saat tidak digunakan.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'All Size', 'lingkar_dada' => '-', 'lingkar_pinggang' => '-', 'panjang' => '24 x 16 x 8 cm'],
                ]),
                'warna' => ['Glossy Black', 'Dark Brown', 'Caramel'],
                'ukuran' => ['All Size'],
                'stok_default' => 18,
            ],
            [
                'kategori' => 'Bawahan',
                'nama' => 'Celana Chino Slim',
                'harga' => 219000,
                'gambar' => 'prod-chino.jpg',
                'deskripsi' => "Celana chino esensial pria dengan potongan slim-tapered yang modern dan rapi. Ditenun dari bahan katun twill tebal berserat rapat dengan campuran elastane 2% yang memberikan kelenturan optimal saat Anda melangkah, jongkok, atau duduk berjam-jam di meja kerja.\n\nDetail Material & Keunggulan:\n- 98% Premium Cotton Twill + 2% Spandex Elastane.\n- Permukaan bahan telah melalui proses garment-dye untuk warna yang lebih pekat, matang, dan tidak mudah luntur.\n- Jahitan bar-tack penguat di setiap titik tumpu saku dan selangkangan agar tidak mudah robek.\n- Saku samping dalam yang aman menampung ponsel pintar dan dompet tanpa tampak menggembung.\n\nPanduan Gaya:\nCocok dipadukan dengan kemeja polo, kaos basic, maupun kemeja flannel. Gulung bagian ujung celana sebanyak 1-2 lipatan kecil untuk aksen kasual kontemporer.\n\nPetunjuk Perawatan:\nCuci dengan posisi bagian dalam di luar (inside-out) untuk menjaga kepekatan warna. Gunakan air dingin dan setrika dengan suhu sedang.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '-', 'lingkar_pinggang' => '76 cm', 'panjang' => '98 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '-', 'lingkar_pinggang' => '80 cm', 'panjang' => '100 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '-', 'lingkar_pinggang' => '85 cm', 'panjang' => '102 cm'],
                    ['ukuran' => 'XL', 'lingkar_dada' => '-', 'lingkar_pinggang' => '90 cm', 'panjang' => '104 cm'],
                ]),
                'warna' => ['Camel Brown', 'Navy', 'Army Green', 'Black'],
                'ukuran' => ['S', 'M', 'L', 'XL'],
                'stok_default' => 16,
            ],
            [
                'kategori' => 'Outer',
                'nama' => 'Cardigan Knit Premium',
                'harga' => 249000,
                'gambar' => 'prod-cardigan.jpg',
                'deskripsi' => "Cardigan rajut wanita bermotif kabel klasik dengan detail kancing vintage beraksen emas mutiara. Menghadirkan tekstur rajutan tiga dimensi yang padat, empuk, dan tebal namun tetap breathable saat dikenakan di dalam ruangan ber-AC.\n\nDetail Material & Desain:\n- Benang Cotton-Acrylic Blend rajut tebal 5-gauge yang tidak gatal di kulit sensitif.\n- Detail motif rajut kabel (cable-knit) mewah di bagian dada dan lengan.\n- Kancing logam berlapis emas antik dengan sentuhan mutiara di bagian tengah.\n- Potongan semi-crop yang pas jatuh tepat di atas garis pinggang celana atau rok high-waist.\n\nPanduan Gaya:\nKenakan sebagai outerwear manis di atas tank top putih, atau kancingkan penuh sebagai atasan rajut independen yang modis dan anggun.\n\nPetunjuk Perawatan:\nCuci tangan perlahan dengan air dingin. Jangan digantung saat basah agar serat rajut tidak melorot ke bawah; jemur mendatar di atas handuk kering.",
                'spesifikasi' => json_encode([
                    ['ukuran' => 'S', 'lingkar_dada' => '96 cm', 'lingkar_pinggang' => '92 cm', 'panjang' => '54 cm'],
                    ['ukuran' => 'M', 'lingkar_dada' => '100 cm', 'lingkar_pinggang' => '96 cm', 'panjang' => '56 cm'],
                    ['ukuran' => 'L', 'lingkar_dada' => '106 cm', 'lingkar_pinggang' => '102 cm', 'panjang' => '58 cm'],
                ]),
                'warna' => ['Sky Blue', 'Offwhite', 'Soft Lilac'],
                'ukuran' => ['S', 'M', 'L'],
                'stok_default' => 12,
            ],
        ];

        $createdProduk = [];
        $createdVarian = [];

        foreach ($produkList as $item) {
            $catId = $kategoriModels[$item['kategori']]->id_kategori ?? $kategoriModels['Atasan']->id_kategori;

            $produk = Produk::updateOrCreate(
                [
                    'id_penjual' => $admin->id_user,
                    'nama_produk' => $item['nama'],
                ],
                [
                    'id_kategori' => $catId,
                    'deskripsi' => $item['deskripsi'],
                    'gambar' => $item['gambar'],
                    'harga' => $item['harga'],
                    'spesifikasi_ukuran' => $item['spesifikasi'],
                ]
            );

            $createdProduk[$item['nama']] = $produk;

            foreach ($item['warna'] as $warna) {
                foreach ($item['ukuran'] as $ukuran) {
                    $comboKey = "{$warna}_{$ukuran}";

                    // Stok realistis berbeda per ukuran (S, M, L, XL, XXL) dan warna
                    $sizeBase = match ($ukuran) {
                        'S' => 8,
                        'M' => 22,
                        'L' => 14,
                        'XL' => 5,
                        'XXL' => 3,
                        'All Size' => 16,
                        default => 10,
                    };
                    $colorOffset = (abs(crc32($warna . $item['nama'])) % 5) - 2; // variasi -2 s/d +2
                    $stokCalculated = max(1, $sizeBase + $colorOffset);
                    $stok = $item['stok_custom'][$comboKey] ?? $stokCalculated;

                    $var = UkuranStok::updateOrCreate(
                        [
                            'id_produk' => $produk->id_produk,
                            'ukuran' => $ukuran,
                            'warna' => $warna,
                        ],
                        [
                            'stok' => $stok,
                            'aktif' => true,
                        ]
                    );

                    $createdVarian["{$item['nama']}_{$comboKey}"] = $var;
                }
            }
        }

        // =====================================================================
        // 6. ULASAN PEMBELI REALISTIS & DINAMIS (Tidak semuanya 24 ulasan!)
        // =====================================================================
        // Bersihkan data ulasan lama agar data baru terisi presisi sesuai target
        Ulasan::truncate();

        // Pool nama-nama pembeli asli Indonesia
        $namaPelangganList = [
            'Aditya Wardhana', 'Clara Novita', 'Budi Santoso', 'Siti Rahmawati', 'Fajar Nugraha',
            'Dewi Sartika', 'Bambang Pamungkas', 'Nadia Utami', 'Rizky Ramadhan', 'Maya Indriani',
            'Eko Pratama', 'Putri Maharani', 'Ahmad Fauzi', 'Tania Wulandari', 'Hendrik Kurniawan',
            'Dian Sastrowardoyo', 'Yoga Pratama', 'Gita Gutawa', 'Rendy Pandugo', 'Fitri Handayani',
            'Agus Setiawan', 'Lestari Dewi', 'Doni Tata', 'Mega Utari', 'Bayu Wicaksono',
            'Anisa Rahma', 'Gilang Dirga', 'Sari Indah', 'Ilham Ramadhan', 'Chandra Wijaya',
            'Rani Permata', 'Bagus Tri', 'Kiki Amalia', 'Rio Febrian', 'Zahra Amelia',
            'Farhan Halim', 'Nabila Syakieb', 'Dimas Anggara', 'Vania Larissa', 'Tommy Kurniawan',
            'Indah Permatasari', 'Kevin Sanjaya', 'Melati Daeva', 'Reza Rahadian', 'Prilly Latuconsina',
            'Chelsea Islan', 'Arifin Putra', 'Tatjana Saphira', 'Bryan Domani', 'Maudy Ayunda'
        ];

        $userPool = [];
        foreach ($namaPelangganList as $idx => $namaP) {
            $emailClean = strtolower(str_replace(' ', '.', $namaP)) . ($idx + 1) . '@gmail.com';
            $userPool[] = User::updateOrCreate(
                ['email' => $emailClean],
                [
                    'nama' => $namaP,
                    'no_telepon' => '0812' . str_pad($idx + 100, 8, '0', STR_PAD_LEFT),
                    'password' => 'password',
                    'role' => User::ROLE_PELANGGAN,
                ]
            );
        }

        // Target jumlah ulasan bervariasi untuk setiap produk (bukan semuanya 24!)
        $targetUlasan = [
            'Casual Midi Dress Linen' => 24,  // Persis 24 ulasan sesuai contoh user
            'Kaos Crewneck Katun Pria' => 88, // Produk terlaris (88 ulasan)
            'Kemeja Oxford Katun Pria' => 34, // 34 ulasan
            'Cardigan Rajut Oversize' => 25,  // 25 ulasan
            'Knit Turtleneck' => 18,          // 18 ulasan
            'Celana Smart Trousers' => 42,    // 42 ulasan
            'Floral Summer Wrap' => 15,       // 15 ulasan
            'Tas Kulit Selempang' => 31,      // 31 ulasan
            'Celana Chino Slim' => 28,        // 28 ulasan
            'Cardigan Knit Premium' => 22,    // 22 ulasan
        ];

        $komentarPool = [
            5 => [
                'Kualitas bahannya sangat bagus dan halus di kulit. Potongannya jatuh dengan rapi dan tidak panas dipakai seharian. Pengiriman cepat dan packaging sangat eksklusif.',
                'Sesuai dengan foto dan deskripsi! Bahannya adem, kancing terpasang kuat dan jahitannya rapi. Sangat nyaman untuk harian maupun acara resmi.',
                'Bagus banget, bahannya tebal tapi tidak bikin gerah. Warnanya persis seperti di foto katalog. Pasti bakal repeat order lagi di Gavin!',
                'Jahitan sangat rapi standar butik. Ukuran M pas banget di badan saya (TB 165 cm, BB 55 kg). Senang sekali belanja di sini.',
                'Pengiriman kilat cuma sehari sampai. Barangnya original dan kainnya lembut sekali. Rekomendasi banget untuk yang cari baju berkualitas.',
                'Sangat puas! Cutting-nya pas membuat tubuh terlihat lebih ramping. Suami dan teman-teman kantor pada muji.',
                'Bahan premium banget, tidak mudah kusut setelah dicuci. Finishing kancing dan sakunya rapi tanpa benang sisa.',
                'Worth the price! Baju favorit baru saya. Cocok dipadukan dengan berbagai outfit lain.',
                'Packing aman dan wangi waktu dibuka. Kualitas Gavin Store memang tidak pernah mengecewakan.',
                'Pilihan warna cantiknya elegan. Dipakai nyaman dan adem seharian di luar ruangan.'
            ],
            4 => [
                'Modelnya sangat elegan dan modern. Pas di badan saya. Pengemasan sangat aman dan cepat sampai.',
                'Bahannya bagus dan lembut. Hanya saja warnanya sedikit lebih gelap dari layar hp saya, tapi overall tetap suka.',
                'Kualitas jahitan rapi dan nyaman dipakai. Respon seller ramah saat ditanya soal ukuran.',
                'Baju sampai dengan aman. Ukuran L agak sedikit longgar di pinggang tapi masih oke banget setelah disetrika.',
                'Kainnya adem dan enak dipakai santai. Pengiriman ekspedisi agak terlambat sehari tapi barang memuaskan.',
                'Overall sangat memuaskan, bahan tebal dan dingin. Desain minimalisnya timeless.'
            ],
            3 => [
                'Barang bagus dan jahitan rapi. Cuma kurirnya agak lambat mengantar sampai rumah.',
                'Kain cukup nyaman, ukuran agak pas-pasan jadi saran naik 1 size kalau suka yang agak longgar.'
            ]
        ];

        foreach ($targetUlasan as $namaProd => $jumlahTarget) {
            $prodModel = $createdProduk[$namaProd] ?? null;
            if (! $prodModel) continue;

            for ($i = 0; $i < $jumlahTarget; $i++) {
                $user = $userPool[$i % count($userPool)];

                // Distribusi rating: ~78% rating 5, ~20% rating 4, ~2% rating 3 -> rata-rata ~4.8
                $rand = rand(1, 100);
                if ($rand <= 78) {
                    $rating = 5;
                } elseif ($rand <= 98) {
                    $rating = 4;
                } else {
                    $rating = 3;
                }

                $listKomentar = $komentarPool[$rating];
                $komentar = $listKomentar[($i + $prodModel->harga) % count($listKomentar)];
                $tgl = now()->subDays(rand(1, 90))->subHours(rand(1, 23));

                Ulasan::create([
                    'id_user' => $user->id_user,
                    'id_produk' => $prodModel->id_produk,
                    'rating' => $rating,
                    'komentar' => $komentar,
                    'tanggal' => $tgl,
                ]);
            }
        }

        // =====================================================================
        // 7. ITEM KERANJANG AWAL (Untuk Annisa Ayu sesuai Mockup 3)
        // =====================================================================
        // Item 1: Casual Midi Dress Linen (Sky Stripe, M) -> qty: 1, dipilih: true
        $varMidi = $createdVarian['Casual Midi Dress Linen_Sky Stripe_M'] ?? null;
        if ($varMidi) {
            Keranjang::updateOrCreate(
                [
                    'id_user' => $customer->id_user,
                    'id_ukuran' => $varMidi->id_ukuran,
                ],
                [
                    'jumlah' => 1,
                    'dipilih' => true,
                ]
            );
        }

        // Item 2: Kaos Crewneck Katun Pria (Offwhite, L) -> qty: 1, dipilih: true
        $varKaos = $createdVarian['Kaos Crewneck Katun Pria_Offwhite_L'] ?? null;
        if ($varKaos) {
            Keranjang::updateOrCreate(
                [
                    'id_user' => $customer->id_user,
                    'id_ukuran' => $varKaos->id_ukuran,
                ],
                [
                    'jumlah' => 1,
                    'dipilih' => true,
                ]
            );
        }

        // Item 3: Celana Smart Trousers (Charcoal, L) -> qty: 3, dipilih: false (Stok hanya 1, memicu pesan "Stok tidak cukup")
        $varCelana = $createdVarian['Celana Smart Trousers_Charcoal_L'] ?? null;
        if ($varCelana) {
            Keranjang::updateOrCreate(
                [
                    'id_user' => $customer->id_user,
                    'id_ukuran' => $varCelana->id_ukuran,
                ],
                [
                    'jumlah' => 3,
                    'dipilih' => false,
                ]
            );
        }

        // Item 4: Kemeja Oxford Katun Pria (Pure White, L) -> qty: 1, dipilih: false
        $varKemeja = $createdVarian['Kemeja Oxford Katun Pria_Pure White_L'] ?? null;
        if ($varKemeja) {
            Keranjang::updateOrCreate(
                [
                    'id_user' => $customer->id_user,
                    'id_ukuran' => $varKemeja->id_ukuran,
                ],
                [
                    'jumlah' => 1,
                    'dipilih' => false,
                ]
            );
        }
    }
}
