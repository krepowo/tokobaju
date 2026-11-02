<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\StokController;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\UkuranStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminStokTest extends TestCase
{
    use RefreshDatabase;

    private function penjual(): User
    {
        return User::factory()->create(['nama' => 'Admin Gavin', 'role' => User::ROLE_PENJUAL]);
    }

    private function produk(array $varian = []): Produk
    {
        $kategori = Kategori::firstOrCreate(['nama_kategori' => 'Atasan']);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'id_penjual' => $this->penjual()->id_user,
            'nama_produk' => 'Kaos Crewneck',
            'deskripsi' => 'Kaos katun combed.',
            'gambar' => 'prod-kaos.jpg',
            'harga' => 159000,
        ]);

        foreach ($varian as $baris) {
            UkuranStok::create([
                'id_produk' => $produk->id_produk,
                'ukuran' => $baris[0],
                'warna' => $baris[1],
                'stok' => $baris[2],
                'aktif' => $baris[3] ?? true,
            ]);
        }

        return $produk;
    }

    // ---------------------------------------------------------------- akses ---

    public function test_tamu_dan_pelanggan_tidak_bisa_membuka_kelola_stok(): void
    {
        $this->get(route('admin.stok.index'))->assertRedirect(route('login'));

        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);
        $this->actingAs($pelanggan)->get(route('admin.stok.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------- daftar ---

    public function test_penjual_melihat_ringkasan_warna_dan_total_stok(): void
    {
        $this->produk([
            ['M', 'Hitam', 3],
            ['L', 'Hitam', 3],
            ['M', 'Putih', 2],
        ]);

        $this->actingAs($this->penjual())
            ->get(route('admin.stok.index'))
            ->assertOk()
            ->assertSee('Kaos Crewneck')
            ->assertSee('2 warna')
            ->assertSee('8 pcs')
            ->assertSee('stok--menipis', false);
    }

    public function test_produk_tanpa_stok_ditandai_habis(): void
    {
        $this->produk([['M', 'Hitam', 0]]);

        $this->actingAs($this->penjual())
            ->get(route('admin.stok.index'))
            ->assertOk()
            ->assertSee('0 pcs')
            ->assertSee('stok--habis', false);
    }

    public function test_pencarian_produk_menyaring_daftar(): void
    {
        $this->produk([['M', 'Hitam', 3]]);
        $kategori = Kategori::first();
        Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'id_penjual' => $this->penjual()->id_user,
            'nama_produk' => 'Midi Dress Linen',
            'deskripsi' => 'Dress linen.',
            'gambar' => 'prod-dress-main.webp',
            'harga' => 279000,
        ]);

        $this->actingAs($this->penjual())
            ->get(route('admin.stok.index', ['cari' => 'kaos']))
            ->assertOk()
            ->assertSee('Kaos Crewneck')
            ->assertDontSee('Midi Dress Linen');
    }

    // ------------------------------------------------------------------ simpan ---

    public function test_simpan_matriks_menambah_dan_mengubah_varian(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5]]);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => ['Hitam', 'Putih'],
                'ukuran' => ['S', 'M'],
                'stok' => [
                    'Hitam' => ['S' => 3, 'M' => 8],
                    'Putih' => ['S' => 2, 'M' => 7],
                ],
                'aktif' => ['S' => '1', 'M' => '1'],
            ])
            ->assertRedirect(route('admin.stok.index'));

        $this->assertSame(4, $produk->varianUkuran()->count());
        $this->assertSame(8, (int) $produk->varianUkuran()->where('warna', 'Hitam')->where('ukuran', 'M')->value('stok'));
        $this->assertSame(2, (int) $produk->varianUkuran()->where('warna', 'Putih')->where('ukuran', 'S')->value('stok'));
    }

    public function test_varian_yang_dihapus_dari_matriks_ikut_dihapus(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5], ['L', 'Hitam', 5]]);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => ['Hitam'],
                'ukuran' => ['M'],
                'stok' => ['Hitam' => ['M' => 5]],
                'aktif' => ['M' => '1'],
            ])
            ->assertRedirect(route('admin.stok.index'));

        $this->assertSame(1, $produk->varianUkuran()->count());
        $this->assertSame(0, $produk->varianUkuran()->where('ukuran', 'L')->count());
    }

    public function test_varian_yang_sudah_dipesan_hanya_dinonaktifkan(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5], ['L', 'Hitam', 5]]);

        $idUkuranL = $produk->varianUkuran()->where('ukuran', 'L')->value('id_ukuran');
        $this->pesananDenganVarian($produk, $idUkuranL);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => ['Hitam'],
                'ukuran' => ['M'],
                'stok' => ['Hitam' => ['M' => 5]],
                'aktif' => ['M' => '1'],
            ])
            ->assertRedirect(route('admin.stok.index'))
            ->assertSessionHas('success');

        $varianL = UkuranStok::find($idUkuranL);
        $this->assertNotNull($varianL);
        $this->assertFalse((bool) $varianL->aktif);
        $this->assertSame(0, (int) $varianL->stok);
    }

    public function test_toggle_ukuran_nonaktif_menyimpan_aktif_false(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5], ['XL', 'Hitam', 5]]);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => ['Hitam'],
                'ukuran' => ['M', 'XL'],
                'stok' => ['Hitam' => ['M' => 4, 'XL' => 6]],
                'aktif' => ['M' => '1', 'XL' => '0'],
            ]);

        $this->assertTrue((bool) $produk->varianUkuran()->where('ukuran', 'M')->value('aktif'));
        $this->assertFalse((bool) $produk->varianUkuran()->where('ukuran', 'XL')->value('aktif'));
    }

    public function test_validasi_warna_dan_ukuran_wajib_diisi(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5]]);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => [],
                'ukuran' => [],
                'stok' => [],
            ])
            ->assertSessionHasErrors(['warna', 'ukuran']);
    }

    public function test_stok_negatif_ditolak(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5]]);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => ['Hitam'],
                'ukuran' => ['M'],
                'stok' => ['Hitam' => ['M' => -3]],
                'aktif' => ['M' => '1'],
            ])
            ->assertSessionHasErrors('stok.Hitam.M');
    }

    public function test_nama_warna_dan_ukuran_tidak_boleh_dobel(): void
    {
        $produk = $this->produk([['M', 'Hitam', 5]]);

        $this->actingAs($this->penjual())
            ->put(route('admin.stok.update', $produk), [
                'warna' => ['Hitam', 'Hitam'],
                'ukuran' => ['M', 'M'],
                'stok' => ['Hitam' => ['M' => 1]],
                'aktif' => ['M' => '1'],
            ])
            ->assertSessionHasErrors(['warna.0', 'warna.1', 'ukuran.0', 'ukuran.1']);
    }

    // ------------------------------------------------------------------ helper ---

    public function test_urutan_ukuran_angka_dan_huruf(): void
    {
        $this->assertSame(['39', '40', '41', '42'], StokController::urutkan(['41', '39', '42', '40']));
        $this->assertSame(['S', 'M', 'L', 'XL', 'XXL'], StokController::urutkan(['XL', 'S', 'XXL', 'M', 'L']));
    }

    private function pesananDenganVarian(Produk $produk, string $idUkuran): void
    {
        $idJasa = (string) Str::uuid();
        DB::table('jasa_pengiriman')->insert([
            'id_jasa' => $idJasa,
            'id_penjual' => $produk->id_penjual,
            'nama_jasa' => 'JNE',
        ]);

        $idPesanan = (string) Str::uuid();
        DB::table('pesanan')->insert([
            'id_pesanan' => $idPesanan,
            'id_user' => $produk->id_penjual,
            'id_jasa' => $idJasa,
            'tanggal_pesanan' => now(),
            'alamat_tujuan' => 'Jl. Uji No. 1',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 174000,
            'status' => 'selesai',
            'batas_bayar' => now()->addDay(),
        ]);

        DB::table('detail_pesanan')->insert([
            'id_detail' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'id_ukuran' => $idUkuran,
            'jumlah' => 1,
            'harga_satuan' => 159000,
            'subtotal' => 159000,
        ]);
    }
}
