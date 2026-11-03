<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PembayaranPelangganTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->create(['nama' => 'Annisa Ayu', 'role' => User::ROLE_PELANGGAN]);
    }

    /**
     * Pesanan lengkap dengan 1 detail (2 pcs) dan varian stok tertentu.
     */
    private function pesanan(User $pelanggan, string $status = 'menunggu_pembayaran', int $stok = 5): Pesanan
    {
        $penjual = User::factory()->create(['role' => User::ROLE_PENJUAL]);

        $idJasa = (string) Str::uuid();
        DB::table('jasa_pengiriman')->insert([
            'id_jasa' => $idJasa,
            'id_penjual' => $penjual->id_user,
            'nama_jasa' => 'JNE',
        ]);

        $idPesanan = (string) Str::uuid();
        DB::table('pesanan')->insert([
            'id_pesanan' => $idPesanan,
            'id_user' => $pelanggan->id_user,
            'id_jasa' => $idJasa,
            'tanggal_pesanan' => now(),
            'alamat_tujuan' => 'Annisa Ayu (081234567890), Jl. Senopati No. 42, Jakarta Selatan, DKI Jakarta 12190',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 294000,
            'status' => $status,
            'batas_bayar' => now()->addDay(),
        ]);

        $idKategori = (string) Str::uuid();
        DB::table('kategori')->insert(['id_kategori' => $idKategori, 'nama_kategori' => 'Dress']);

        $idProduk = (string) Str::uuid();
        DB::table('produk')->insert([
            'id_produk' => $idProduk,
            'id_kategori' => $idKategori,
            'id_penjual' => $penjual->id_user,
            'nama_produk' => 'Casual Midi Dress Linen',
            'deskripsi' => 'Dress linen.',
            'gambar' => 'prod-dress-main.webp',
            'harga' => 139500,
        ]);

        $idUkuran = (string) Str::uuid();
        DB::table('ukuran_stok')->insert([
            'id_ukuran' => $idUkuran,
            'id_produk' => $idProduk,
            'ukuran' => 'M',
            'warna' => 'Sky Stripe',
            'stok' => $stok,
            'aktif' => true,
        ]);

        DB::table('detail_pesanan')->insert([
            'id_detail' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'id_ukuran' => $idUkuran,
            'jumlah' => 2,
            'harga_satuan' => 139500,
            'subtotal' => 279000,
        ]);

        DB::table('invoice')->insert([
            'id_invoice' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'no_invoice' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'tanggal_terbit' => now(),
            'total_tagihan' => 294000,
            'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
        ]);

        return Pesanan::findOrFail($idPesanan);
    }

    private function bersihkanBukti(): void
    {
        foreach (DB::table('pembayaran')->pluck('bukti_transfer') as $jalur) {
            $penuh = public_path($jalur);

            if ($jalur && File::exists($penuh)) {
                File::delete($penuh);
            }
        }
    }

    protected function tearDown(): void
    {
        $this->bersihkanBukti();

        parent::tearDown();
    }

    // ---------------------------------------------------------------- unggah ---

    public function test_pelanggan_bisa_mengunggah_bukti_transfer(): void
    {
        $pelanggan = $this->pelanggan();
        $pesanan = $this->pesanan($pelanggan);

        $this->actingAs($pelanggan)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg', 600, 800),
            ])
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $bayar = DB::table('pembayaran')->where('id_pesanan', $pesanan->id_pesanan)->first();

        $this->assertNotNull($bayar);
        $this->assertSame('menunggu', $bayar->status_verifikasi);
        $this->assertStringStartsWith('images/uploads/', $bayar->bukti_transfer);
        $this->assertFileExists(public_path($bayar->bukti_transfer));

        $this->assertSame('menunggu_verifikasi', $pesanan->fresh()->status);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $pelanggan = $this->pelanggan();
        $pesanan = $this->pesanan($pelanggan);

        $this->actingAs($pelanggan)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->create('bukti.pdf', 200, 'application/pdf'),
            ])
            ->assertSessionHasErrors('bukti');

        $this->assertSame(0, DB::table('pembayaran')->count());
        $this->assertSame('menunggu_pembayaran', $pesanan->fresh()->status);
    }

    public function test_bukti_lebih_dari_10mb_ditolak(): void
    {
        $pelanggan = $this->pelanggan();
        $pesanan = $this->pesanan($pelanggan);

        $this->actingAs($pelanggan)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg')->size(11000),
            ])
            ->assertSessionHasErrors('bukti');

        $this->assertSame(0, DB::table('pembayaran')->count());
    }

    public function test_pelanggan_lain_tidak_bisa_mengunggah(): void
    {
        $pemilik = $this->pelanggan();
        $pesanan = $this->pesanan($pemilik);

        $lain = User::factory()->create(['role' => User::ROLE_PELANGGAN]);

        $this->actingAs($lain)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertNotFound();
    }

    public function test_pesanan_selesai_tidak_menerima_unggahan(): void
    {
        $pelanggan = $this->pelanggan();
        $pesanan = $this->pesanan($pelanggan, 'selesai');

        $this->actingAs($pelanggan)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, DB::table('pembayaran')->count());
        $this->assertSame('selesai', $pesanan->fresh()->status);
    }

    // ------------------------------------------------------------ unggah ulang ---

    public function test_unggah_ulang_setelah_ditolak_memesankan_stok_lagi(): void
    {
        $pelanggan = $this->pelanggan();
        $pesanan = $this->pesanan($pelanggan, 'pembayaran_ditolak', stok: 5);

        DB::table('pembayaran')->insert([
            'id_pembayaran' => (string) Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'bukti_transfer' => 'images/uploads/lama.jpg',
            'tanggal_upload' => now()->subHour(),
            'status_verifikasi' => 'ditolak',
            'alasan_tolak' => 'Nominal tidak sesuai.',
        ]);

        $idUkuran = DB::table('detail_pesanan')->where('id_pesanan', $pesanan->id_pesanan)->value('id_ukuran');
        $this->assertSame(5, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));

        $this->actingAs($pelanggan)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHas('success');

        // 5 - 2 = 3 (stok dipesan lagi karena pesanan sebelumnya dikembalikan)
        $this->assertSame(3, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));
        $this->assertSame('menunggu_verifikasi', $pesanan->fresh()->status);
        $this->assertSame(2, DB::table('pembayaran')->where('id_pesanan', $pesanan->id_pesanan)->count());
    }

    public function test_unggah_ulang_ditolak_bila_stok_tidak_cukup(): void
    {
        $pelanggan = $this->pelanggan();
        $pesanan = $this->pesanan($pelanggan, 'pembayaran_ditolak', stok: 1);

        $this->actingAs($pelanggan)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, DB::table('pembayaran')->count());
        $this->assertSame('pembayaran_ditolak', $pesanan->fresh()->status);
    }
}
