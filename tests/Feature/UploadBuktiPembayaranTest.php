<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\Invoice;
use App\Models\JasaPengiriman;
use App\Models\Kategori;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\UkuranStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UploadBuktiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private JasaPengiriman $jasa;
    private UkuranStok $ukuranStok;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id_user' => (string) Str::uuid(),
            'nama' => 'Test Pelanggan',
            'email' => 'pelanggan@test.com',
            'no_telepon' => '081234567890',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);

        $penjual = User::create([
            'id_user' => (string) Str::uuid(),
            'nama' => 'Penjual Gavin',
            'email' => 'penjual@test.com',
            'no_telepon' => '081111111111',
            'password' => bcrypt('password'),
            'role' => 'penjual',
        ]);

        $this->jasa = JasaPengiriman::create([
            'id_jasa' => (string) Str::uuid(),
            'id_penjual' => $penjual->id_user,
            'nama_jasa' => 'JNE',
        ]);

        $kategori = Kategori::create([
            'id_kategori' => (string) Str::uuid(),
            'nama_kategori' => 'Atasan',
        ]);

        $produk = Produk::create([
            'id_produk' => (string) Str::uuid(),
            'id_kategori' => $kategori->id_kategori,
            'id_penjual' => $penjual->id_user,
            'nama_produk' => 'Kemeja Linen Oxford',
            'deskripsi' => 'Kemeja santai',
            'gambar' => 'prod-kemeja.webp',
            'harga' => 150000,
        ]);

        $this->ukuranStok = UkuranStok::create([
            'id_ukuran' => (string) Str::uuid(),
            'id_produk' => $produk->id_produk,
            'ukuran' => 'M',
            'warna' => 'Putih',
            'stok' => 10,
            'aktif' => true,
        ]);
    }

    private function createPesanan(array $overrides = []): Pesanan
    {
        $idPesanan = (string) Str::uuid();

        $pesanan = Pesanan::create(array_merge([
            'id_pesanan' => $idPesanan,
            'id_user' => $this->user->id_user,
            'id_jasa' => $this->jasa->id_jasa,
            'tanggal_pesanan' => now(),
            'alamat_tujuan' => 'Jl. Merdeka No. 10',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 165000,
            'status' => 'menunggu_pembayaran',
            'batas_bayar' => now()->addDay(),
        ], $overrides));

        DetailPesanan::create([
            'id_detail' => (string) Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'id_ukuran' => $this->ukuranStok->id_ukuran,
            'jumlah' => 1,
            'harga_satuan' => 150000,
            'subtotal' => 150000,
        ]);

        static $seq = 1;
        Invoice::create([
            'id_invoice' => (string) Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'no_invoice' => 'INV-'.now()->format('Ymd').'-'.str_pad((string) ($seq++), 4, '0', STR_PAD_LEFT),
            'tanggal_terbit' => now(),
            'total_tagihan' => $pesanan->total,
            'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
        ]);

        return $pesanan;
    }

    public function test_tamu_tidak_bisa_upload_bukti(): void
    {
        $pesanan = $this->createPesanan();

        $response = $this->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_lain_tidak_bisa_upload_bukti_pesanan_orang_lain(): void
    {
        $pesanan = $this->createPesanan();

        $otherUser = User::create([
            'id_user' => (string) Str::uuid(),
            'nama' => 'User Lain',
            'email' => 'other@test.com',
            'no_telepon' => '089876543210',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);

        $response = $this->actingAs($otherUser)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertStatus(403);
    }

    public function test_validasi_upload_gagal_jika_bukan_gambar_atau_kosong(): void
    {
        $pesanan = $this->createPesanan();

        $response = $this->actingAs($this->user)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), []);
        $response->assertSessionHasErrors(['bukti_transfer']);

        $txtFile = UploadedFile::fake()->create('dokumen.txt', 100);
        $response2 = $this->actingAs($this->user)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => $txtFile,
        ]);
        $response2->assertSessionHasErrors(['bukti_transfer']);
    }

    public function test_berhasil_upload_bukti_pembayaran(): void
    {
        Storage::fake('public');

        $pesanan = $this->createPesanan();
        $file = UploadedFile::fake()->image('struk_transfer.png', 500, 500);

        $response = $this->actingAs($this->user)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => $file,
        ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertSessionHas('success', 'Bukti pembayaran berhasil diunggah');

        $pesanan->refresh();
        $this->assertEquals('menunggu_verifikasi', $pesanan->status);

        $this->assertDatabaseHas('pembayaran', [
            'id_pesanan' => $pesanan->id_pesanan,
            'status_verifikasi' => 'menunggu',
        ]);

        $pembayaran = $pesanan->pembayaran()->first();
        $this->assertNotNull($pembayaran);
        Storage::disk('public')->assertExists($pembayaran->bukti_transfer);
    }

    public function test_tidak_dapat_upload_jika_batas_bayar_sudah_lewat(): void
    {
        Storage::fake('public');

        $pesanan = $this->createPesanan([
            'status' => 'menunggu_pembayaran',
            'batas_bayar' => now()->subHour(),
        ]);

        $file = UploadedFile::fake()->image('struk.jpg');

        $response = $this->actingAs($this->user)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => $file,
        ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertSessionHas('error', 'Pesanan tidak dapat dibayar lagi');

        $pesanan->refresh();
        $this->assertEquals('menunggu_pembayaran', $pesanan->status);
    }

    public function test_tidak_dapat_upload_jika_status_bukan_menunggu_pembayaran_atau_ditolak(): void
    {
        Storage::fake('public');

        $pesanan = $this->createPesanan([
            'status' => 'diproses',
            'batas_bayar' => now()->addDay(),
        ]);

        $file = UploadedFile::fake()->image('struk.jpg');

        $response = $this->actingAs($this->user)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => $file,
        ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertSessionHas('error', 'Pesanan tidak dapat dibayar lagi');
    }

    public function test_upload_ulang_diperbolehkan_saat_pembayaran_ditolak(): void
    {
        Storage::fake('public');

        $pesanan = $this->createPesanan([
            'status' => 'pembayaran_ditolak',
            'batas_bayar' => now()->addDay(),
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id_pesanan,
            'bukti_transfer' => 'pembayaran/lama.jpg',
            'tanggal_upload' => now()->subHours(2),
            'status_verifikasi' => 'ditolak',
            'alasan_tolak' => 'Struk tidak terbaca',
        ]);

        $file = UploadedFile::fake()->image('struk_baru.jpg');

        $response = $this->actingAs($this->user)->post(route('pesanan.upload-bukti', $pesanan->id_pesanan), [
            'bukti_transfer' => $file,
        ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertSessionHas('success', 'Bukti pembayaran berhasil diunggah');

        $pesanan->refresh();
        $this->assertEquals('menunggu_verifikasi', $pesanan->status);
        $this->assertEquals(2, $pesanan->pembayaran()->count());
    }

    public function test_konfirmasi_terima_berhasil_mengubah_status_menjadi_selesai(): void
    {
        $pesanan = $this->createPesanan(['status' => 'dikirim']);
        $pengiriman = Pengiriman::create([
            'id_pengiriman' => (string) Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'no_resi' => 'JNE1234567890',
            'tanggal_kirim' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)->post(route('pesanan.konfirmasi-terima', $pesanan->id_pesanan));

        $response->assertRedirect(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertSessionHas('success', 'Pesanan telah dikonfirmasi diterima. Terima kasih sudah berbelanja di Gavin Fashion Store!');

        $pesanan->refresh();
        $pengiriman->refresh();

        $this->assertEquals('selesai', $pesanan->status);
        $this->assertNotNull($pengiriman->tanggal_diterima);
    }

    public function test_konfirmasi_terima_gagal_jika_bukan_status_dikirim(): void
    {
        $pesanan = $this->createPesanan(['status' => 'diproses']);

        $response = $this->actingAs($this->user)->post(route('pesanan.konfirmasi-terima', $pesanan->id_pesanan));

        $response->assertRedirect(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertSessionHas('error', 'Pesanan belum dapat dikonfirmasi');

        $pesanan->refresh();
        $this->assertEquals('diproses', $pesanan->status);
    }

    public function test_command_batalkan_pesanan_kedaluwarsa_mengembalikan_stok(): void
    {
        $this->assertEquals(10, $this->ukuranStok->stok);

        // Buat pesanan kedaluwarsa (1 item jumlah 2)
        $pesanan = $this->createPesanan([
            'status' => 'menunggu_pembayaran',
            'batas_bayar' => now()->subHour(),
        ]);
        $pesanan->detailPesanan()->first()->update(['jumlah' => 2]);

        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('Berhasil memproses pembatalan: 1 pesanan kedaluwarsa')
            ->assertExitCode(0);

        $pesanan->refresh();
        $this->assertEquals('dibatalkan', $pesanan->status);

        // Stok bertambah 2 (dari 10 menjadi 12)
        $this->ukuranStok->refresh();
        $this->assertEquals(12, $this->ukuranStok->stok);

        // Jika command dijalankan lagi, stok tidak bertambah ganda
        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('Berhasil memproses pembatalan: 0 pesanan kedaluwarsa')
            ->assertExitCode(0);

        $this->ukuranStok->refresh();
        $this->assertEquals(12, $this->ukuranStok->stok);
    }

    public function test_tampilan_view_berdasarkan_status(): void
    {
        // 1. Status menunggu_pembayaran
        $p1 = $this->createPesanan(['status' => 'menunggu_pembayaran']);
        $r1 = $this->actingAs($this->user)->get(route('pesanan.show', $p1->id_pesanan));
        $r1->assertOk();
        $r1->assertSee('Kembali ke Pesanan Saya');
        $r1->assertSee('Detail Pesanan');
        $r1->assertSee('ALUR PEMROSESAN PESANAN');
        $r1->assertSee('Sisa Waktu Pembayaran');
        $r1->assertSee('Tujuan Transfer Manual');
        $r1->assertSee('Upload Bukti Pembayaran');
        $r1->assertSee('modalUploadBukti');
        $r1->assertSee('Pilih file bukti pembayaran terlebih dahulu');
        $r1->assertSee('Belum tersedia'); // resi belum tersedia

        // 2. Status menunggu_verifikasi
        $p2 = $this->createPesanan(['status' => 'menunggu_verifikasi']);
        Pembayaran::create([
            'id_pesanan' => $p2->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk1.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'menunggu',
        ]);
        $r2 = $this->actingAs($this->user)->get(route('pesanan.show', $p2->id_pesanan));
        $r2->assertOk();
        $r2->assertSee('Sedang Diverifikasi');
        $r2->assertSee('Bukti Terkirim');
        $r2->assertSee('lightboxBuktiTransfer');
        $r2->assertDontSee('btnBukaUploadModal');

        // 3. Status diproses
        $p3 = $this->createPesanan(['status' => 'diproses']);
        Pembayaran::create([
            'id_pesanan' => $p3->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk2.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'disetujui',
        ]);
        $r3 = $this->actingAs($this->user)->get(route('pesanan.show', $p3->id_pesanan));
        $r3->assertOk();
        $r3->assertSee('Pembayaran Terverifikasi');
        $r3->assertSee('Estimasi Penyiapan Kurir');
        $r3->assertSee('Lihat Invoice');
        $r3->assertDontSee('Lihat Lembar Invoice');

        // 4. Status dikirim
        $p4 = $this->createPesanan(['status' => 'dikirim']);
        Pengiriman::create([
            'id_pengiriman' => (string) Str::uuid(),
            'id_pesanan' => $p4->id_pesanan,
            'no_resi' => 'JNE-99887766',
            'tanggal_kirim' => now(),
        ]);
        $r4 = $this->actingAs($this->user)->get(route('pesanan.show', $p4->id_pesanan));
        $r4->assertOk();
        $r4->assertSee('Paket Dalam Pengiriman');
        $r4->assertSee('JNE-99887766');
        $r4->assertSee('Konfirmasi Pesanan Diterima');

        // 5. Status selesai
        $p5 = $this->createPesanan(['status' => 'selesai']);
        Pengiriman::create([
            'id_pengiriman' => (string) Str::uuid(),
            'id_pesanan' => $p5->id_pesanan,
            'no_resi' => 'JNE-99887766',
            'tanggal_kirim' => now()->subDay(),
            'tanggal_diterima' => now(),
        ]);
        $r5 = $this->actingAs($this->user)->get(route('pesanan.show', $p5->id_pesanan));
        $r5->assertOk();
        $r5->assertSee('Transaksi Selesai');
        $r5->assertSee('Beli Lagi Produk Serupa');
        $r5->assertSee('Lihat Invoice');
        $r5->assertDontSee('Unduh Invoice PDF');

        // 6. Status dibatalkan
        $p6 = $this->createPesanan(['status' => 'dibatalkan']);
        $r6 = $this->actingAs($this->user)->get(route('pesanan.show', $p6->id_pesanan));
        $r6->assertOk();
        $r6->assertSee('Pesanan Dibatalkan');
        $r6->assertDontSee('ALUR PEMROSESAN PESANAN');
        $r6->assertDontSee('btnBukaUploadModal');
    }

    public function test_thumbnail_bukti_transfer_pada_berbagai_status_dan_null_safe(): void
    {
        // 1. menunggu_pembayaran: tidak boleh muncul bukti pembayaran
        $pMenunggu = $this->createPesanan(['status' => 'menunggu_pembayaran']);
        $resMenunggu = $this->actingAs($this->user)->get(route('pesanan.show', $pMenunggu->id_pesanan));
        $resMenunggu->assertOk();
        $resMenunggu->assertDontSee('pesanan-proof-card');

        // 2. pembayaran_ditolak: muncul bukti pembayaran dengan badge Ditolak
        $pDitolak = $this->createPesanan(['status' => 'pembayaran_ditolak']);
        Pembayaran::create([
            'id_pesanan' => $pDitolak->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk_ditolak.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'ditolak',
            'alasan_tolak' => 'Struk tidak jelas',
        ]);
        $resDitolak = $this->actingAs($this->user)->get(route('pesanan.show', $pDitolak->id_pesanan));
        $resDitolak->assertOk();
        $resDitolak->assertSee('pesanan-proof-card');
        $resDitolak->assertSee('Bukti Pembayaran');
        $resDitolak->assertSee('Ditolak');
        $resDitolak->assertSee('Lihat Bukti Transfer');

        // 3. menunggu_verifikasi: muncul bukti pembayaran dengan badge Sedang Diverifikasi
        $pVerifikasi = $this->createPesanan(['status' => 'menunggu_verifikasi']);
        Pembayaran::create([
            'id_pesanan' => $pVerifikasi->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk_verif.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'menunggu',
        ]);
        $resVerif = $this->actingAs($this->user)->get(route('pesanan.show', $pVerifikasi->id_pesanan));
        $resVerif->assertOk();
        $resVerif->assertSee('pesanan-proof-card');
        $resVerif->assertSee('Bukti Pembayaran');
        $resVerif->assertSee('Sedang Diverifikasi');

        // 4. diproses: muncul bukti pembayaran dengan badge Terverifikasi
        $pDiproses = $this->createPesanan(['status' => 'diproses']);
        Pembayaran::create([
            'id_pesanan' => $pDiproses->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk_diproses.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'disetujui',
        ]);
        $resDiproses = $this->actingAs($this->user)->get(route('pesanan.show', $pDiproses->id_pesanan));
        $resDiproses->assertOk();
        $resDiproses->assertSee('pesanan-proof-card');
        $resDiproses->assertSee('Bukti Pembayaran');
        $resDiproses->assertSee('Terverifikasi');

        // 5. dikirim: muncul bukti pembayaran dengan badge Terverifikasi
        $pDikirim = $this->createPesanan(['status' => 'dikirim']);
        Pembayaran::create([
            'id_pesanan' => $pDikirim->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk_dikirim.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'disetujui',
        ]);
        $resDikirim = $this->actingAs($this->user)->get(route('pesanan.show', $pDikirim->id_pesanan));
        $resDikirim->assertOk();
        $resDikirim->assertSee('pesanan-proof-card');
        $resDikirim->assertSee('Bukti Pembayaran');
        $resDikirim->assertSee('Terverifikasi');

        // 6. selesai: muncul bukti pembayaran dengan badge Terverifikasi
        $pSelesai = $this->createPesanan(['status' => 'selesai']);
        Pembayaran::create([
            'id_pesanan' => $pSelesai->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk_selesai.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'disetujui',
        ]);
        $resSelesai = $this->actingAs($this->user)->get(route('pesanan.show', $pSelesai->id_pesanan));
        $resSelesai->assertOk();
        $resSelesai->assertSee('pesanan-proof-card');
        $resSelesai->assertSee('Bukti Pembayaran');
        $resSelesai->assertSee('Terverifikasi');

        // 7. dibatalkan (meski ada pembayaran): tidak boleh muncul bukti pembayaran
        $pBatal = $this->createPesanan(['status' => 'dibatalkan']);
        Pembayaran::create([
            'id_pesanan' => $pBatal->id_pesanan,
            'bukti_transfer' => 'pembayaran/struk_batal.jpg',
            'tanggal_upload' => now(),
            'status_verifikasi' => 'ditolak',
        ]);
        $resBatal = $this->actingAs($this->user)->get(route('pesanan.show', $pBatal->id_pesanan));
        $resBatal->assertOk();
        $resBatal->assertDontSee('pesanan-proof-card');

        // 8. Null-safe jika status diproses tetapi belum ada record pembayaran
        $pNull = $this->createPesanan(['status' => 'diproses']);
        $resNull = $this->actingAs($this->user)->get(route('pesanan.show', $pNull->id_pesanan));
        $resNull->assertOk();
        $resNull->assertDontSee('pesanan-proof-card');
    }

    public function test_show_eager_loads_user_relation(): void
    {
        $pesanan = $this->createPesanan(['status' => 'diproses']);

        $response = $this->actingAs($this->user)->get(route('pesanan.show', $pesanan->id_pesanan));
        $response->assertOk();

        // Pastikan view menerima order dengan relasi 'user' yang sudah di-load
        $viewOrder = $response->viewData('order');
        $this->assertNotNull($viewOrder);
        $this->assertTrue($viewOrder->relationLoaded('user'), 'Relasi user harus di-eager load');
        $this->assertEquals($this->user->id_user, $viewOrder->user->id_user);
    }

    public function test_akses_dan_unduh_invoice_dengan_dompdf(): void
    {
        $pesanan = $this->createPesanan(['status' => 'selesai']);

        // 1. Guest tidak bisa akses invoice
        $this->get(route('pesanan.invoice', $pesanan->id_pesanan))
            ->assertRedirect(route('login'));
        $this->get(route('pesanan.invoice.unduh', $pesanan->id_pesanan))
            ->assertRedirect(route('login'));

        // 2. User lain tidak bisa akses invoice (403)
        $otherUser = User::create([
            'id_user' => (string) Str::uuid(),
            'nama' => 'Orang Lain',
            'email' => 'other@test.com',
            'no_telepon' => '089999999999',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);
        $this->actingAs($otherUser)
            ->get(route('pesanan.invoice', $pesanan->id_pesanan))
            ->assertStatus(403);
        $this->actingAs($otherUser)
            ->get(route('pesanan.invoice.unduh', $pesanan->id_pesanan))
            ->assertStatus(403);

        // 3. Pesanan dibatalkan -> 404
        $pesananBatal = $this->createPesanan(['status' => 'dibatalkan']);
        $this->actingAs($this->user)
            ->get(route('pesanan.invoice', $pesananBatal->id_pesanan))
            ->assertStatus(404);

        // 4. Pemilik pesanan bisa melihat invoice versi web
        $resWeb = $this->actingAs($this->user)
            ->get(route('pesanan.invoice', $pesanan->id_pesanan));
        $resWeb->assertOk();
        $resWeb->assertSee('INVOICE');
        $resWeb->assertSee($pesanan->invoice->no_invoice);
        $resWeb->assertSee('GAVIN FASHION STORE');
        $resWeb->assertSee('Unduh PDF');
        $resWeb->assertSee('Cetak');

        // 5. Pemilik pesanan bisa mengunduh PDF DomPDF
        $resPdf = $this->actingAs($this->user)
            ->get(route('pesanan.invoice.unduh', $pesanan->id_pesanan));
        $resPdf->assertOk();
        $resPdf->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('invoice-' . $pesanan->invoice->no_invoice . '.pdf', $resPdf->headers->get('content-disposition'));
    }
}
