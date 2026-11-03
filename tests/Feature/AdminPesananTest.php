<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\UkuranStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPesananTest extends TestCase
{
    use RefreshDatabase;

    private function penjual(): User
    {
        return User::factory()->create(['nama' => 'Admin Gavin', 'role' => User::ROLE_PENJUAL]);
    }

    private function pelanggan(string $nama = 'Annisa Ayu'): User
    {
        return User::factory()->create(['nama' => $nama, 'role' => User::ROLE_PELANGGAN]);
    }

    private function idJasa(): string
    {
        $id = (string) Str::uuid();

        DB::table('jasa_pengiriman')->insert([
            'id_jasa' => $id,
            'id_penjual' => $this->penjual()->id_user,
            'nama_jasa' => 'JNE',
        ]);

        return $id;
    }

    /**
     * Buat pesanan lengkap (pelanggan, jasa, 1 detail, invoice).
     */
    private function pesanan(array $ubah = []): Pesanan
    {
        $pelanggan = $this->pelanggan($ubah['nama_pelanggan'] ?? 'Annisa Ayu');
        $idJasa = $this->idJasa();

        $kategori = Kategori::firstOrCreate(['nama_kategori' => 'Dress']);
        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'id_penjual' => $this->penjual()->id_user,
            'nama_produk' => 'Casual Midi Dress Linen',
            'deskripsi' => 'Dress linen premium.',
            'gambar' => 'prod-dress-main.webp',
            'harga' => 279000,
        ]);

        $ukuran = UkuranStok::create([
            'id_produk' => $produk->id_produk,
            'ukuran' => 'M',
            'warna' => 'Sky Stripe',
            'stok' => 10,
            'aktif' => true,
        ]);

        $idPesanan = (string) Str::uuid();
        $tanggal = $ubah['tanggal'] ?? now();

        DB::table('pesanan')->insert([
            'id_pesanan' => $idPesanan,
            'id_user' => $pelanggan->id_user,
            'id_jasa' => $idJasa,
            'tanggal_pesanan' => $tanggal,
            'alamat_tujuan' => 'Annisa Ayu (081234567890), Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12190',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 294000,
            'status' => $ubah['status'] ?? 'menunggu_pembayaran',
            'batas_bayar' => $ubah['batas_bayar'] ?? now()->addDay(),
        ]);

        DB::table('detail_pesanan')->insert([
            'id_detail' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'id_ukuran' => $ukuran->id_ukuran,
            'jumlah' => 1,
            'harga_satuan' => 279000,
            'subtotal' => 279000,
        ]);

        if ($ubah['dengan_invoice'] ?? true) {
            DB::table('invoice')->insert([
                'id_invoice' => (string) Str::uuid(),
                'id_pesanan' => $idPesanan,
                'no_invoice' => $ubah['no_invoice'] ?? 'INV-'.now()->format('Ymd').'-0001',
                'tanggal_terbit' => $tanggal,
                'total_tagihan' => 294000,
                'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
            ]);
        }

        return Pesanan::findOrFail($idPesanan);
    }

    // ---------------------------------------------------------------- akses ---

    public function test_tamu_dan_pelanggan_tidak_bisa_membuka_pesanan(): void
    {
        $this->get(route('admin.pesanan.index'))->assertRedirect(route('login'));

        $pelanggan = $this->pelanggan();
        $this->actingAs($pelanggan)->get(route('admin.pesanan.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------- daftar ---

    public function test_penjual_melihat_pesanan_bulan_berjalan(): void
    {
        $this->pesanan(['no_invoice' => 'INV-20261102-0001']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index'))
            ->assertOk()
            ->assertSee('INV-20261102-0001')
            ->assertSee('Annisa Ayu')
            ->assertSee('Rp 294.000')
            ->assertSee('Menunggu Pembayaran');
    }

    public function test_pesanan_lama_di_luar_bulan_berjalan_tidak_tampil_secara_default(): void
    {
        $this->pesanan(['no_invoice' => 'INV-LAMA-0001', 'tanggal' => now()->subMonths(3)]);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index'))
            ->assertOk()
            ->assertDontSee('INV-LAMA-0001');

        // Rentang dikosongkan → semua pesanan tampil.
        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index', ['dari' => '', 'sampai' => '']))
            ->assertOk()
            ->assertSee('INV-LAMA-0001');
    }

    public function test_filter_status_menyaring_pesanan(): void
    {
        $this->pesanan(['no_invoice' => 'INV-A', 'status' => 'menunggu_pembayaran']);
        $this->pesanan(['no_invoice' => 'INV-B', 'status' => 'dikirim', 'nama_pelanggan' => 'Bambang']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index', ['status' => 'dikirim']))
            ->assertOk()
            ->assertSee('INV-B')
            ->assertDontSee('INV-A');
    }

    public function test_pencarian_berdasarkan_invoice_dan_nama_pelanggan(): void
    {
        $this->pesanan(['no_invoice' => 'INV-CARI-01']);
        $this->pesanan(['no_invoice' => 'INV-LAIN-02', 'nama_pelanggan' => 'Bambang Setiawan']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index', ['cari' => 'CARI']))
            ->assertOk()
            ->assertSee('INV-CARI-01')
            ->assertDontSee('INV-LAIN-02');

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index', ['cari' => 'Bambang']))
            ->assertOk()
            ->assertSee('INV-LAIN-02')
            ->assertDontSee('INV-CARI-01');
    }

    public function test_filter_rentang_tanggal(): void
    {
        $this->pesanan(['no_invoice' => 'INV-KEMARIN', 'tanggal' => now()->subDays(2)]);
        $this->pesanan(['no_invoice' => 'INV-HARIINI']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.index', [
                'dari' => now()->toDateString(),
                'sampai' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('INV-HARIINI')
            ->assertDontSee('INV-KEMARIN');
    }

    // --------------------------------------------------------------- invoice ---

    public function test_halaman_invoice_menampilkan_rincian_pesanan(): void
    {
        $pesanan = $this->pesanan(['no_invoice' => 'INV-20261102-0012']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.show', $pesanan))
            ->assertOk()
            ->assertSee('INV-20261102-0012')
            ->assertSee('Casual Midi Dress Linen')
            ->assertSee('Sky Stripe')
            ->assertSee('Rp 279.000')
            ->assertSee('Rp 15.000')
            ->assertSee('Rp 294.000')
            ->assertSee('Sisa waktu pembayaran');
    }

    public function test_invoice_tanpa_nomor_tetap_bisa_dibuka(): void
    {
        $pesanan = $this->pesanan(['dengan_invoice' => false]);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.show', $pesanan))
            ->assertOk()
            ->assertSee('Casual Midi Dress Linen');
    }

    public function test_pesanan_selesai_tidak_menampilkan_sisa_waktu(): void
    {
        $pesanan = $this->pesanan(['status' => 'selesai']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.show', $pesanan))
            ->assertOk()
            ->assertDontSee('Sisa waktu pembayaran');
    }

    // ----------------------------------------------------------------- cetak ---

    public function test_halaman_cetak_laporan_tampil(): void
    {
        $this->pesanan(['no_invoice' => 'INV-CETAK-01']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pesanan.cetak'))
            ->assertOk()
            ->assertSee('Laporan Pesanan')
            ->assertSee('INV-CETAK-01')
            ->assertSee('Total Nilai');
    }
}
