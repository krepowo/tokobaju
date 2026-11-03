<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProdukKategoriTest extends TestCase
{
    use RefreshDatabase;

    private function penjual(): User
    {
        return User::factory()->create([
            'nama' => 'Admin Gavin',
            'role' => User::ROLE_PENJUAL,
        ]);
    }

    private function kategori(string $nama = 'Atasan'): Kategori
    {
        return Kategori::create(['nama_kategori' => $nama]);
    }

    private function produk(Kategori $kategori, string $nama = 'Kemeja Oxford'): Produk
    {
        return Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'id_penjual' => $this->penjual()->id_user,
            'nama_produk' => $nama,
            'deskripsi' => 'Deskripsi contoh untuk '.$nama.'.',
            'gambar' => 'prod-kemeja-oxford.webp',
            'harga' => 249000,
        ]);
    }

    // ---------------------------------------------------------------- akses ---

    public function test_tamu_tidak_bisa_membuka_kelola_produk(): void
    {
        $this->get(route('admin.produk.index'))->assertRedirect(route('login'));
        $this->get(route('admin.kategori.index'))->assertRedirect(route('login'));
    }

    public function test_pelanggan_ditolak_dari_kelola_produk(): void
    {
        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);

        $this->actingAs($pelanggan)->get(route('admin.produk.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------- produk ---

    public function test_penjual_melihat_daftar_produk_dengan_kategori(): void
    {
        $kategori = $this->kategori('Atasan');
        $this->produk($kategori, 'Kemeja Oxford Katun Pria');

        $this->actingAs($this->penjual())
            ->get(route('admin.produk.index'))
            ->assertOk()
            ->assertSee('Kemeja Oxford Katun Pria')
            ->assertSee('Atasan')
            ->assertSee('Rp 249.000')
            ->assertSee('Menampilkan 1–1 dari 1 produk');
    }

    public function test_pencarian_dan_filter_kategori_menyaring_produk(): void
    {
        $atasan = $this->kategori('Atasan');
        $dress = $this->kategori('Dress');
        $this->produk($atasan, 'Kemeja Oxford');
        $this->produk($dress, 'Midi Dress Linen');

        $this->actingAs($this->penjual())
            ->get(route('admin.produk.index', ['cari' => 'kemeja']))
            ->assertOk()
            ->assertSee('Kemeja Oxford')
            ->assertSee('Menampilkan 1–1 dari 1 produk')
            ->assertDontSee('Ubah Midi Dress Linen');

        $this->actingAs($this->penjual())
            ->get(route('admin.produk.index', ['kategori' => $dress->id_kategori]))
            ->assertOk()
            ->assertSee('Midi Dress Linen')
            ->assertSee('Menampilkan 1–1 dari 1 produk')
            ->assertDontSee('Ubah Kemeja Oxford');
    }

    public function test_penjual_menambah_produk_dengan_unggah_gambar(): void
    {
        $kategori = $this->kategori('Atasan');

        $this->actingAs($this->penjual())
            ->post(route('admin.produk.store'), [
                'nama_produk' => 'Kaos Crewneck Baru',
                'id_kategori' => $kategori->id_kategori,
                'harga' => 159000,
                'deskripsi' => 'Kaos katun combed 30s.',
                'spesifikasi_ukuran' => 'M: lebar 50 cm',
                'gambar' => UploadedFile::fake()->image('kaos.jpg', 600, 800),
            ])
            ->assertRedirect(route('admin.produk.index'));

        $produk = Produk::where('nama_produk', 'Kaos Crewneck Baru')->firstOrFail();

        $this->assertStringStartsWith('uploads/', $produk->gambar);
        $this->assertFileExists(public_path('images/'.$produk->gambar));

        @unlink(public_path('images/'.$produk->gambar));
    }

    public function test_produk_wajib_punya_nama_kategori_harga_dan_gambar(): void
    {
        $this->actingAs($this->penjual())
            ->post(route('admin.produk.store'), [])
            ->assertSessionHasErrors(['nama_produk', 'id_kategori', 'harga', 'deskripsi', 'gambar']);
    }

    public function test_gambar_harus_berupa_berkas_gambar(): void
    {
        $kategori = $this->kategori('Atasan');

        $this->actingAs($this->penjual())
            ->post(route('admin.produk.store'), [
                'nama_produk' => 'Produk Nakal',
                'id_kategori' => $kategori->id_kategori,
                'harga' => 100000,
                'deskripsi' => 'Coba unggah berkas bukan gambar.',
                'gambar' => UploadedFile::fake()->create('virus.php', 12, 'text/x-php'),
            ])
            ->assertSessionHasErrors('gambar');
    }

    public function test_penjual_mengubah_produk_tanpa_mengganti_gambar(): void
    {
        $kategori = $this->kategori('Atasan');
        $produk = $this->produk($kategori, 'Nama Lama');

        $this->actingAs($this->penjual())
            ->put(route('admin.produk.update', $produk), [
                'nama_produk' => 'Nama Baru',
                'id_kategori' => $kategori->id_kategori,
                'harga' => 275000,
                'deskripsi' => 'Deskripsi baru.',
            ])
            ->assertRedirect(route('admin.produk.index'));

        $produk->refresh();
        $this->assertSame('Nama Baru', $produk->nama_produk);
        $this->assertSame('prod-kemeja-oxford.webp', $produk->gambar);
    }

    public function test_produk_tanpa_riwayat_pesanan_bisa_dihapus(): void
    {
        $produk = $this->produk($this->kategori('Atasan'));

        $this->actingAs($this->penjual())
            ->delete(route('admin.produk.destroy', $produk))
            ->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseMissing('produk', ['id_produk' => $produk->id_produk]);
    }

    public function test_produk_yang_sudah_dipesan_tidak_bisa_dihapus(): void
    {
        $produk = $this->produk($this->kategori('Atasan'));
        $idUkuran = $this->varianDenganRiwayatPesanan($produk);

        $this->assertNotEmpty($idUkuran);

        $this->actingAs($this->penjual())
            ->delete(route('admin.produk.destroy', $produk))
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('produk', ['id_produk' => $produk->id_produk]);
    }

    // -------------------------------------------------------------- kategori ---

    public function test_penjual_melihat_daftar_kategori_dengan_jumlah_produk(): void
    {
        $atasan = $this->kategori('Atasan');
        $this->produk($atasan, 'Kemeja Oxford');
        $this->kategori('Hijab');

        $this->actingAs($this->penjual())
            ->get(route('admin.kategori.index'))
            ->assertOk()
            ->assertSee('Atasan')
            ->assertSee('Hijab')
            ->assertSee('Menampilkan 1–2 dari 2 kategori');
    }

    public function test_penjual_menambah_kategori_baru(): void
    {
        $this->actingAs($this->penjual())
            ->post(route('admin.kategori.store'), ['nama_kategori' => 'Sepatu'])
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Sepatu']);
    }

    public function test_nama_kategori_harus_unik(): void
    {
        $this->kategori('Atasan');

        $this->actingAs($this->penjual())
            ->post(route('admin.kategori.store'), ['nama_kategori' => 'Atasan'])
            ->assertSessionHasErrors('nama_kategori');
    }

    public function test_kategori_bisa_diubah(): void
    {
        $kategori = $this->kategori('Aksesori');

        $this->actingAs($this->penjual())
            ->put(route('admin.kategori.update', $kategori), ['nama_kategori' => 'Aksesoris'])
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertSame('Aksesoris', $kategori->fresh()->nama_kategori);
    }

    public function test_kategori_yang_masih_dipakai_produk_tidak_bisa_dihapus(): void
    {
        $kategori = $this->kategori('Atasan');
        $this->produk($kategori);

        $this->actingAs($this->penjual())
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori', ['id_kategori' => $kategori->id_kategori]);
    }

    public function test_kategori_kosong_bisa_dihapus(): void
    {
        $kategori = $this->kategori('Hijab');

        $this->actingAs($this->penjual())
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseMissing('kategori', ['id_kategori' => $kategori->id_kategori]);
    }

    /**
     * Buat varian + pesanan + detail pesanan supaya produk punya riwayat transaksi.
     */
    private function varianDenganRiwayatPesanan(Produk $produk): string
    {
        $idUkuran = (string) Str::uuid();
        $idPesanan = (string) Str::uuid();

        DB::table('ukuran_stok')->insert([
            'id_ukuran' => $idUkuran,
            'id_produk' => $produk->id_produk,
            'ukuran' => 'M',
            'warna' => 'Hitam',
            'stok' => 5,
            'aktif' => true,
        ]);

        DB::table('jasa_pengiriman')->insert([
            'id_jasa' => (string) Str::uuid(),
            'id_penjual' => $produk->id_penjual,
            'nama_jasa' => 'JNE',
        ]);

        $idJasa = DB::table('jasa_pengiriman')->value('id_jasa');

        DB::table('pesanan')->insert([
            'id_pesanan' => $idPesanan,
            'id_user' => $produk->id_penjual,
            'id_jasa' => $idJasa,
            'tanggal_pesanan' => now(),
            'alamat_tujuan' => 'Jl. Uji No. 1',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 264000,
            'status' => 'selesai',
            'batas_bayar' => now()->addDay(),
        ]);

        DB::table('detail_pesanan')->insert([
            'id_detail' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'id_ukuran' => $idUkuran,
            'jumlah' => 1,
            'harga_satuan' => 249000,
            'subtotal' => 249000,
        ]);

        return $idUkuran;
    }
}
