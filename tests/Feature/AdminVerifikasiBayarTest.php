<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\User;
use App\Support\Kedaluwarsa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminVerifikasiBayarTest extends TestCase
{
    use RefreshDatabase;

    private function penjual(): User
    {
        return User::factory()->create(['nama' => 'Admin Gavin', 'role' => User::ROLE_PENJUAL]);
    }

    /**
     * Pesanan + varian stok + invoice, dengan status tertentu.
     */
    private function pesanan(string $status = 'menunggu_verifikasi', array $ubah = []): Pesanan
    {
        $pelanggan = User::factory()->create([
            'nama' => $ubah['nama_pelanggan'] ?? 'Annisa Ayu',
            'role' => User::ROLE_PELANGGAN,
        ]);

        $idJasa = (string) Str::uuid();
        DB::table('jasa_pengiriman')->insert([
            'id_jasa' => $idJasa,
            'id_penjual' => $this->penjual()->id_user,
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
            'batas_bayar' => $ubah['batas_bayar'] ?? now()->addDay(),
        ]);

        $idKategori = (string) Str::uuid();
        DB::table('kategori')->insert(['id_kategori' => $idKategori, 'nama_kategori' => 'Dress '.Str::upper(Str::random(4))]);

        $idProduk = (string) Str::uuid();
        DB::table('produk')->insert([
            'id_produk' => $idProduk,
            'id_kategori' => $idKategori,
            'id_penjual' => $this->penjual()->id_user,
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
            'stok' => $ubah['stok'] ?? 3,
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
            'no_invoice' => $ubah['no_invoice'] ?? 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'tanggal_terbit' => now(),
            'total_tagihan' => 294000,
            'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
        ]);

        return Pesanan::findOrFail($idPesanan);
    }

    private function pembayaran(Pesanan $pesanan, string $status = 'menunggu', ?string $alasan = null): Pembayaran
    {
        $id = (string) Str::uuid();

        DB::table('pembayaran')->insert([
            'id_pembayaran' => $id,
            'id_pesanan' => $pesanan->id_pesanan,
            'bukti_transfer' => 'images/uploads/bukti-uji.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => $status,
            'alasan_tolak' => $alasan,
        ]);

        return Pembayaran::findOrFail($id);
    }

    // ---------------------------------------------------------------- akses ---

    public function test_tamu_dan_pelanggan_tidak_bisa_membuka_verifikasi(): void
    {
        $this->get(route('admin.verifikasi.index'))->assertRedirect(route('login'));

        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);
        $this->actingAs($pelanggan)->get(route('admin.verifikasi.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------- daftar ---

    public function test_daftar_hanya_menampilkan_pembayaran_menunggu(): void
    {
        $menunggu = $this->pesanan('menunggu_verifikasi', ['no_invoice' => 'INV-MENUNGGU']);
        $this->pembayaran($menunggu, 'menunggu');

        $selesai = $this->pesanan('diproses', ['no_invoice' => 'INV-DISETUJUI', 'nama_pelanggan' => 'Bambang']);
        $this->pembayaran($selesai, 'disetujui');

        $this->actingAs($this->penjual())
            ->get(route('admin.verifikasi.index'))
            ->assertOk()
            ->assertSee('INV-MENUNGGU')
            ->assertSee('Annisa Ayu')
            ->assertSee('Rp 294.000')
            ->assertDontSee('INV-DISETUJUI')
            ->assertSee('Menampilkan 1–1 dari 1 pesanan menunggu verifikasi');
    }

    public function test_pencarian_invoice_dan_nama_pelanggan(): void
    {
        $a = $this->pesanan('menunggu_verifikasi', ['no_invoice' => 'INV-CARI-01']);
        $this->pembayaran($a, 'menunggu');

        $b = $this->pesanan('menunggu_verifikasi', ['no_invoice' => 'INV-LAIN-02', 'nama_pelanggan' => 'Bambang Setiawan']);
        $this->pembayaran($b, 'menunggu');

        $this->actingAs($this->penjual())
            ->get(route('admin.verifikasi.index', ['cari' => 'CARI']))
            ->assertOk()
            ->assertSee('INV-CARI-01')
            ->assertDontSee('INV-LAIN-02');

        $this->actingAs($this->penjual())
            ->get(route('admin.verifikasi.index', ['cari' => 'Bambang']))
            ->assertOk()
            ->assertSee('INV-LAIN-02')
            ->assertDontSee('INV-CARI-01');
    }

    // ---------------------------------------------------------------- detail ---

    public function test_detail_menampilkan_bukti_dan_rincian(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi', ['no_invoice' => 'INV-DETAIL-01']);
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $this->actingAs($this->penjual())
            ->get(route('admin.verifikasi.show', $bayar))
            ->assertOk()
            ->assertSee('INV-DETAIL-01')
            ->assertSee('Casual Midi Dress Linen')
            ->assertSee('Sky Stripe')
            ->assertSee('Rp 279.000')
            ->assertSee('Rp 294.000')
            ->assertSee('Setujui Pembayaran')
            ->assertSee('Bukti transfer belum tersedia');
    }

    // -------------------------------------------------------------- setujui ---

    public function test_setujui_mengubah_status_pesanan_menjadi_diproses(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi');
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $penjual = $this->penjual();

        $this->actingAs($penjual)
            ->post(route('admin.verifikasi.setujui', $bayar))
            ->assertRedirect(route('admin.verifikasi.index'))
            ->assertSessionHas('success');

        $bayar->refresh();
        $this->assertSame('disetujui', $bayar->status_verifikasi);
        $this->assertSame($penjual->id_user, $bayar->diverifikasi_oleh);
        $this->assertSame('diproses', $pesanan->fresh()->status);
    }

    public function test_setujui_tidak_mengubah_stok(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi', ['stok' => 3]);
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $this->actingAs($this->penjual())->post(route('admin.verifikasi.setujui', $bayar));

        $idUkuran = DB::table('detail_pesanan')->where('id_pesanan', $pesanan->id_pesanan)->value('id_ukuran');
        $this->assertSame(3, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));
    }

    public function test_pembayaran_yang_sudah_diproses_tidak_bisa_disetujui_lagi(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi');
        $bayar = $this->pembayaran($pesanan, 'disetujui');

        $this->actingAs($this->penjual())
            ->post(route('admin.verifikasi.setujui', $bayar))
            ->assertSessionHas('error');

        $this->assertSame('menunggu_verifikasi', $pesanan->fresh()->status);
    }

    // ----------------------------------------------------------------- tolak ---

    public function test_tolak_wajib_alasan(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi');
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $this->actingAs($this->penjual())
            ->post(route('admin.verifikasi.tolak', $bayar), ['alasan_tolak' => ''])
            ->assertSessionHasErrors('alasan_tolak');

        $this->assertSame('menunggu', $bayar->fresh()->status_verifikasi);
        $this->assertSame('menunggu_verifikasi', $pesanan->fresh()->status);
    }

    public function test_tolak_mengembalikan_stok_dan_menyimpan_alasan(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi', ['stok' => 3]);
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $idUkuran = DB::table('detail_pesanan')->where('id_pesanan', $pesanan->id_pesanan)->value('id_ukuran');

        $this->actingAs($this->penjual())
            ->post(route('admin.verifikasi.tolak', $bayar), ['alasan_tolak' => 'Nominal transfer tidak sesuai.'])
            ->assertSessionHas('success');

        $bayar->refresh();
        $this->assertSame('ditolak', $bayar->status_verifikasi);
        $this->assertSame('Nominal transfer tidak sesuai.', $bayar->alasan_tolak);
        $this->assertSame('pembayaran_ditolak', $pesanan->fresh()->status);

        // 3 + 2 = 5 (stok dikembalikan)
        $this->assertSame(5, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));
    }

    // ------------------------------------------------------------ kedaluwarsa ---

    public function test_pembayaran_lewat_batas_bayar_ditolak_otomatis(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi', [
            'batas_bayar' => now()->subHours(5),
            'stok' => 3,
        ]);
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $hasil = Kedaluwarsa::jalankan();

        $this->assertSame(1, $hasil['ditolak']);
        $this->assertSame('ditolak', $bayar->fresh()->status_verifikasi);
        $this->assertSame(Kedaluwarsa::ALASAN, $bayar->fresh()->alasan_tolak);
        $this->assertSame('pembayaran_ditolak', $pesanan->fresh()->status);

        $idUkuran = DB::table('detail_pesanan')->where('id_pesanan', $pesanan->id_pesanan)->value('id_ukuran');
        $this->assertSame(5, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));
    }

    public function test_pesanan_belum_bayar_lewat_batas_dibatalkan_otomatis(): void
    {
        $pesanan = $this->pesanan('menunggu_pembayaran', [
            'batas_bayar' => now()->subHours(3),
            'stok' => 3,
        ]);

        $hasil = Kedaluwarsa::jalankan();

        $this->assertSame(1, $hasil['dibatalkan']);
        $this->assertSame('dibatalkan', $pesanan->fresh()->status);

        $idUkuran = DB::table('detail_pesanan')->where('id_pesanan', $pesanan->id_pesanan)->value('id_ukuran');
        $this->assertSame(5, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));
    }

    public function test_kedaluwarsa_tidak_diproses_dua_kali(): void
    {
        $pesanan = $this->pesanan('menunggu_pembayaran', ['batas_bayar' => now()->subHour(), 'stok' => 3]);

        Kedaluwarsa::jalankan();
        $kedua = Kedaluwarsa::jalankan();

        $this->assertSame(0, $kedua['dibatalkan']);

        $idUkuran = DB::table('detail_pesanan')->where('id_pesanan', $pesanan->id_pesanan)->value('id_ukuran');
        $this->assertSame(5, (int) DB::table('ukuran_stok')->where('id_ukuran', $idUkuran)->value('stok'));
    }

    public function test_pesanan_yang_belum_lewat_batas_tidak_disentuh(): void
    {
        $pesanan = $this->pesanan('menunggu_pembayaran', ['batas_bayar' => now()->addHours(6)]);

        $hasil = Kedaluwarsa::jalankan();

        $this->assertSame(0, $hasil['dibatalkan']);
        $this->assertSame('menunggu_pembayaran', $pesanan->fresh()->status);
    }

    public function test_halaman_verifikasi_menjalankan_penolakan_otomatis(): void
    {
        $pesanan = $this->pesanan('menunggu_verifikasi', ['batas_bayar' => now()->subHours(2)]);
        $bayar = $this->pembayaran($pesanan, 'menunggu');

        $this->actingAs($this->penjual())
            ->get(route('admin.verifikasi.index'))
            ->assertOk()
            ->assertSee('Tidak ada pembayaran yang menunggu verifikasi');

        $this->assertSame('ditolak', $bayar->fresh()->status_verifikasi);
    }

    public function test_perintah_artisan_kedaluwarsa_berjalan(): void
    {
        $this->pesanan('menunggu_pembayaran', ['batas_bayar' => now()->subHour()]);

        $this->artisan('pembayaran:kedaluwarsa')
            ->expectsOutputToContain('Pembayaran ditolak: 0, pesanan dibatalkan: 1.')
            ->assertSuccessful();
    }
}
