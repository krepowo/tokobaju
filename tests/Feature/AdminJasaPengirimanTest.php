<?php

namespace Tests\Feature;

use App\Models\JasaPengiriman;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\TarifPengiriman;
use App\Models\UkuranStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminJasaPengirimanTest extends TestCase
{
    use RefreshDatabase;

    private const PULAU = ['Jawa', 'Sumatera', 'Kalimantan', 'Sulawesi', 'Bali & Nusa Tenggara', 'Maluku & Papua'];

    private function penjual(): User
    {
        return User::factory()->create(['nama' => 'Admin Gavin', 'role' => User::ROLE_PENJUAL]);
    }

    private function jasa(string $nama = 'JNE', array $pulau = self::PULAU, int $tarif = 15000): JasaPengiriman
    {
        $jasa = JasaPengiriman::create([
            'id_penjual' => $this->penjual()->id_user,
            'nama_jasa' => $nama,
        ]);

        foreach ($pulau as $namaPulau) {
            TarifPengiriman::create([
                'id_jasa' => $jasa->id_jasa,
                'pulau_tujuan' => $namaPulau,
                'tarif' => $tarif,
            ]);
        }

        return $jasa;
    }

    // ---------------------------------------------------------------- akses ---

    public function test_tamu_dan_pelanggan_tidak_bisa_membuka_jasa_pengiriman(): void
    {
        $this->get(route('admin.jasa.index'))->assertRedirect(route('login'));

        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);
        $this->actingAs($pelanggan)->get(route('admin.jasa.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------- daftar ---

    public function test_penjual_melihat_daftar_jasa_dengan_jumlah_pulau(): void
    {
        $this->jasa('JNE');
        $this->jasa('GoSend', ['Jawa'], 18000);

        $this->actingAs($this->penjual())
            ->get(route('admin.jasa.index'))
            ->assertOk()
            ->assertSee('JNE')
            ->assertSee('6 Pulau Terdaftar')
            ->assertSee('1 Pulau Terdaftar (Jawa)')
            ->assertSee('Menampilkan 1–2 dari 2 jasa pengiriman');
    }

    public function test_jasa_tanpa_tarif_ditandai_belum_ada_tarif(): void
    {
        JasaPengiriman::create([
            'id_penjual' => $this->penjual()->id_user,
            'nama_jasa' => 'Kurir Baru',
        ]);

        $this->actingAs($this->penjual())
            ->get(route('admin.jasa.index'))
            ->assertOk()
            ->assertSee('0 Pulau Terdaftar')
            ->assertSee('Belum ada tarif');
    }

    // ------------------------------------------------------------------ CRUD ---

    public function test_menambah_jasa_sekaligus_menyimpan_tarif_tiap_pulau(): void
    {
        $this->actingAs($this->penjual())
            ->post(route('admin.jasa.store'), [
                'nama_jasa' => 'SiCepat',
                'tarif' => [
                    'Jawa' => 12000,
                    'Sumatera' => 25000,
                    'Kalimantan' => 30000,
                    'Sulawesi' => 35000,
                    'Bali & Nusa Tenggara' => 28000,
                    'Maluku & Papua' => 45000,
                ],
            ])
            ->assertRedirect(route('admin.jasa.index'));

        $jasa = JasaPengiriman::where('nama_jasa', 'SiCepat')->firstOrFail();

        $this->assertSame(6, $jasa->tarif()->count());
        $this->assertSame(12000.0, (float) $jasa->tarif()->where('pulau_tujuan', 'Jawa')->value('tarif'));
    }

    public function test_jumlah_pulau_mengikuti_tarif_yang_diisi(): void
    {
        $this->actingAs($this->penjual())
            ->post(route('admin.jasa.store'), [
                'nama_jasa' => 'GoSend',
                'tarif' => ['Jawa' => 18000, 'Sumatera' => ''],
            ])
            ->assertRedirect(route('admin.jasa.index'));

        $jasa = JasaPengiriman::where('nama_jasa', 'GoSend')->firstOrFail();
        $this->assertSame(1, $jasa->tarif()->count());

        $this->actingAs($this->penjual())
            ->get(route('admin.jasa.index'))
            ->assertOk()
            ->assertSee('1 Pulau Terdaftar (Jawa)');
    }

    public function test_tambah_jasa_tanpa_tarif_ditolak(): void
    {
        $this->actingAs($this->penjual())
            ->post(route('admin.jasa.store'), [
                'nama_jasa' => 'Kurir Kosong',
                'tarif' => ['Jawa' => '', 'Sumatera' => ''],
            ])
            ->assertSessionHasErrors('tarif');

        $this->assertDatabaseMissing('jasa_pengiriman', ['nama_jasa' => 'Kurir Kosong']);
    }

    public function test_nama_jasa_harus_unik(): void
    {
        $this->jasa('JNE');

        $this->actingAs($this->penjual())
            ->post(route('admin.jasa.store'), ['nama_jasa' => 'JNE', 'tarif' => ['Jawa' => 15000]])
            ->assertSessionHasErrors('nama_jasa');
    }

    public function test_nama_jasa_bisa_diubah(): void
    {
        $jasa = $this->jasa('JNE');

        $this->actingAs($this->penjual())
            ->put(route('admin.jasa.update', $jasa), ['nama_jasa' => 'JNE Reguler'])
            ->assertRedirect(route('admin.jasa.index'));

        $this->assertSame('JNE Reguler', $jasa->fresh()->nama_jasa);
    }

    public function test_jasa_tanpa_pesanan_bisa_dihapus_beserta_tarifnya(): void
    {
        $jasa = $this->jasa('GoSend', ['Jawa']);

        $this->actingAs($this->penjual())
            ->delete(route('admin.jasa.destroy', $jasa))
            ->assertRedirect(route('admin.jasa.index'));

        $this->assertDatabaseMissing('jasa_pengiriman', ['id_jasa' => $jasa->id_jasa]);
        $this->assertSame(0, TarifPengiriman::where('id_jasa', $jasa->id_jasa)->count());
    }

    public function test_jasa_yang_dipakai_pesanan_tidak_bisa_dihapus(): void
    {
        $jasa = $this->jasa('JNE');
        $this->pesananDenganJasa($jasa);

        $this->actingAs($this->penjual())
            ->delete(route('admin.jasa.destroy', $jasa))
            ->assertRedirect(route('admin.jasa.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('jasa_pengiriman', ['id_jasa' => $jasa->id_jasa]);
    }

    // ---------------------------------------------------------------- tarif ---

    public function test_simpan_tarif_untuk_keenam_pulau(): void
    {
        $jasa = $this->jasa('JNE');

        $this->actingAs($this->penjual())
            ->put(route('admin.jasa.tarif.update', $jasa), [
                'tarif' => [
                    'Jawa' => 17000,
                    'Sumatera' => 27000,
                    'Kalimantan' => 32000,
                    'Sulawesi' => 37000,
                    'Bali & Nusa Tenggara' => 24000,
                    'Maluku & Papua' => 47000,
                ],
            ])
            ->assertRedirect(route('admin.jasa.index'));

        $this->assertSame(6, $jasa->tarif()->count());
        $this->assertSame(17000.0, (float) $jasa->tarif()->where('pulau_tujuan', 'Jawa')->value('tarif'));
    }

    public function test_mengosongkan_pulau_menghapus_tarifnya(): void
    {
        $jasa = $this->jasa('JNE');

        $this->actingAs($this->penjual())
            ->put(route('admin.jasa.tarif.update', $jasa), [
                'tarif' => [
                    'Jawa' => 17000,
                    'Sumatera' => '',
                    'Kalimantan' => '',
                    'Sulawesi' => '',
                    'Bali & Nusa Tenggara' => '',
                    'Maluku & Papua' => '',
                ],
            ])
            ->assertRedirect(route('admin.jasa.index'));

        $this->assertSame(1, $jasa->tarif()->count());
        $this->assertSame('Jawa', $jasa->tarif()->value('pulau_tujuan'));
        $this->assertSame(17000.0, (float) $jasa->tarif()->value('tarif'));
    }

    public function test_semua_tarif_kosong_ditolak(): void
    {
        $jasa = $this->jasa('JNE');

        $this->actingAs($this->penjual())
            ->put(route('admin.jasa.tarif.update', $jasa), [
                'tarif' => array_fill_keys(self::PULAU, ''),
            ])
            ->assertSessionHasErrors('tarif');

        $this->assertSame(6, $jasa->tarif()->count());
    }

    // -------------------------------------------------------------- checkout ---

    public function test_checkout_ditolak_kalau_tarif_pulau_tujuan_belum_ada(): void
    {
        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);
        $jasa = $this->jasa('GoSend', ['Jawa'], 18000);

        // Pulau tujuan Sumatera sengaja tidak punya tarif untuk GoSend.
        DB::table('provinsi')->insert([
            'id_provinsi' => (string) Str::uuid(),
            'nama_provinsi' => 'Sumatera Utara',
            'pulau' => 'Sumatera',
        ]);

        $this->siapkanKeranjang($pelanggan);

        $this->actingAs($pelanggan)
            ->post(route('checkout.store'), [
                'nama_penerima' => 'Annisa Ayu',
                'no_telepon' => '081234567890',
                'provinsi' => 'Sumatera Utara',
                'kota_kabupaten' => 'Medan',
                'kecamatan' => 'Medan Baru',
                'kelurahan_desa' => 'Petisah',
                'kode_pos' => '20112',
                'detail_alamat' => 'Jl. Uji No. 5',
                'id_jasa' => $jasa->id_jasa,
            ])
            ->assertSessionHasErrors('id_jasa');

        $this->assertSame(0, DB::table('pesanan')->count());
    }

    public function test_checkout_berhasil_dan_ongkir_memakai_tarif_pulau(): void
    {
        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);
        $jasa = $this->jasa('JNE', self::PULAU, 15000);

        DB::table('provinsi')->insert([
            'id_provinsi' => (string) Str::uuid(),
            'nama_provinsi' => 'Jawa Barat',
            'pulau' => 'Jawa',
        ]);

        $this->siapkanKeranjang($pelanggan);

        $this->actingAs($pelanggan)
            ->post(route('checkout.store'), [
                'nama_penerima' => 'Annisa Ayu',
                'no_telepon' => '081234567890',
                'provinsi' => 'Jawa Barat',
                'kota_kabupaten' => 'Bandung',
                'kecamatan' => 'Coblong',
                'kelurahan_desa' => 'Dago',
                'kode_pos' => '40135',
                'detail_alamat' => 'Jl. Uji No. 5',
                'id_jasa' => $jasa->id_jasa,
            ]);

        $pesanan = DB::table('pesanan')->first();
        $this->assertNotNull($pesanan);
        $this->assertSame('Jawa', $pesanan->pulau_tujuan);
        $this->assertSame(15000.0, (float) $pesanan->ongkir);
    }

    // --------------------------------------------------------------- helper ---

    private function pesananDenganJasa(JasaPengiriman $jasa): void
    {
        DB::table('pesanan')->insert([
            'id_pesanan' => (string) Str::uuid(),
            'id_user' => $jasa->id_penjual,
            'id_jasa' => $jasa->id_jasa,
            'tanggal_pesanan' => now(),
            'alamat_tujuan' => 'Jl. Uji No. 1',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 264000,
            'status' => 'selesai',
            'batas_bayar' => now()->addDay(),
        ]);
    }

    private function siapkanKeranjang(User $pelanggan): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Atasan']);
        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'id_penjual' => $this->penjual()->id_user,
            'nama_produk' => 'Kaos Uji',
            'deskripsi' => 'Kaos untuk pengujian checkout.',
            'gambar' => 'prod-kaos.jpg',
            'harga' => 150000,
        ]);

        $ukuran = UkuranStok::create([
            'id_produk' => $produk->id_produk,
            'ukuran' => 'M',
            'warna' => 'Hitam',
            'stok' => 10,
            'aktif' => true,
        ]);

        DB::table('keranjang')->insert([
            'id_keranjang' => (string) Str::uuid(),
            'id_user' => $pelanggan->id_user,
            'id_ukuran' => $ukuran->id_ukuran,
            'jumlah' => 1,
            'dipilih' => true,
        ]);
    }
}
