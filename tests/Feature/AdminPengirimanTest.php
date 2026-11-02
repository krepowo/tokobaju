<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\User;
use App\Support\Alamat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPengirimanTest extends TestCase
{
    use RefreshDatabase;

    private function penjual(): User
    {
        return User::factory()->create(['nama' => 'Admin Gavin', 'role' => User::ROLE_PENJUAL]);
    }

    private function pesanan(string $status = 'diproses', array $ubah = []): Pesanan
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
            'alamat_tujuan' => 'Annisa Ayu (081234567890), Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12190',
            'pulau_tujuan' => 'Jawa',
            'ongkir' => 15000,
            'total' => 294000,
            'status' => $status,
            'batas_bayar' => now()->addDay(),
        ]);

        if ($ubah['dengan_invoice'] ?? true) {
            DB::table('invoice')->insert([
                'id_invoice' => (string) Str::uuid(),
                'id_pesanan' => $idPesanan,
                'no_invoice' => $ubah['no_invoice'] ?? 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
                'tanggal_terbit' => now(),
                'total_tagihan' => 294000,
                'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
            ]);
        }

        return Pesanan::findOrFail($idPesanan);
    }

    // ---------------------------------------------------------------- akses ---

    public function test_tamu_dan_pelanggan_tidak_bisa_membuka_proses_pengiriman(): void
    {
        $this->get(route('admin.pengiriman.index'))->assertRedirect(route('login'));

        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);
        $this->actingAs($pelanggan)->get(route('admin.pengiriman.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------- daftar ---

    public function test_hanya_pesanan_diproses_dan_dikirim_yang_tampil(): void
    {
        $this->pesanan('diproses', ['no_invoice' => 'INV-PROSES']);
        $this->pesanan('dikirim', ['no_invoice' => 'INV-KIRIM', 'nama_pelanggan' => 'Bambang']);
        $this->pesanan('selesai', ['no_invoice' => 'INV-SELESAI', 'nama_pelanggan' => 'Clara']);
        $this->pesanan('menunggu_pembayaran', ['no_invoice' => 'INV-BAYAR', 'nama_pelanggan' => 'Dimas']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pengiriman.index'))
            ->assertOk()
            ->assertSee('INV-PROSES')
            ->assertSee('INV-KIRIM')
            ->assertDontSee('INV-SELESAI')
            ->assertDontSee('INV-BAYAR');
    }

    public function test_kartu_ringkasan_menghitung_beban_kerja(): void
    {
        $this->pesanan('diproses');
        $this->pesanan('diproses', ['nama_pelanggan' => 'Budi']);
        $this->pesanan('dikirim', ['nama_pelanggan' => 'Citra']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pengiriman.index'))
            ->assertOk()
            ->assertSee('Total Pesanan')
            ->assertSee('Perlu Diproses')
            ->assertSee('Dikirim')
            ->assertViewHas('ringkasan', fn ($ringkasan) => $ringkasan['total'] === 3
                && $ringkasan['perlu_diproses'] === 2
                && $ringkasan['dikirim'] === 1);
    }

    public function test_kota_dan_jumlah_item_ditampilkan(): void
    {
        $pesanan = $this->pesanan('diproses');

        $kategoriId = (string) Str::uuid();
        DB::table('kategori')->insert(['id_kategori' => $kategoriId, 'nama_kategori' => 'Dress']);

        $produkId = (string) Str::uuid();
        DB::table('produk')->insert([
            'id_produk' => $produkId,
            'id_kategori' => $kategoriId,
            'id_penjual' => $this->penjual()->id_user,
            'nama_produk' => 'Casual Midi Dress Linen',
            'deskripsi' => 'Dress linen.',
            'gambar' => 'prod-dress-main.webp',
            'harga' => 279000,
        ]);

        $ukuranId = (string) Str::uuid();
        DB::table('ukuran_stok')->insert([
            'id_ukuran' => $ukuranId,
            'id_produk' => $produkId,
            'ukuran' => 'M',
            'warna' => 'Sky Stripe',
            'stok' => 5,
            'aktif' => true,
        ]);

        DB::table('detail_pesanan')->insert([
            'id_detail' => (string) Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'id_ukuran' => $ukuranId,
            'jumlah' => 2,
            'harga_satuan' => 279000,
            'subtotal' => 558000,
        ]);

        $this->actingAs($this->penjual())
            ->get(route('admin.pengiriman.index'))
            ->assertOk()
            ->assertSee('Jakarta Selatan')
            ->assertSee('2 item')
            ->assertSee('Casual Midi Dress Linen');
    }

    public function test_filter_status_pengiriman(): void
    {
        $this->pesanan('diproses', ['no_invoice' => 'INV-A']);
        $this->pesanan('dikirim', ['no_invoice' => 'INV-B', 'nama_pelanggan' => 'Bambang']);

        $this->actingAs($this->penjual())
            ->get(route('admin.pengiriman.index', ['status' => 'diproses']))
            ->assertOk()
            ->assertSee('INV-A')
            ->assertDontSee('INV-B');
    }

    // ------------------------------------------------------------------ resi ---

    public function test_simpan_resi_mengubah_status_menjadi_dikirim(): void
    {
        $pesanan = $this->pesanan('diproses');

        $this->actingAs($this->penjual())
            ->put(route('admin.pengiriman.update', $pesanan), [
                'no_resi' => 'JNE1234567890',
                'catatan' => 'Dijemput kurir 14.00',
            ])
            ->assertRedirect(route('admin.pengiriman.index'));

        $this->assertSame('dikirim', $pesanan->fresh()->status);
        $this->assertDatabaseHas('pengiriman', [
            'id_pesanan' => $pesanan->id_pesanan,
            'no_resi' => 'JNE1234567890',
            'catatan' => 'Dijemput kurir 14.00',
        ]);
    }

    public function test_nomor_resi_wajib_diisi(): void
    {
        $pesanan = $this->pesanan('diproses');

        $this->actingAs($this->penjual())
            ->put(route('admin.pengiriman.update', $pesanan), ['no_resi' => ''])
            ->assertSessionHasErrors('no_resi');

        $this->assertSame('diproses', $pesanan->fresh()->status);
    }

    public function test_menyimpan_ulang_memperbarui_resi_tanpa_duplikat(): void
    {
        $pesanan = $this->pesanan('diproses');

        $this->actingAs($this->penjual())
            ->put(route('admin.pengiriman.update', $pesanan), ['no_resi' => 'JNE111']);

        $this->actingAs($this->penjual())
            ->put(route('admin.pengiriman.update', $pesanan), ['no_resi' => 'JNE222']);

        $this->assertSame(1, DB::table('pengiriman')->where('id_pesanan', $pesanan->id_pesanan)->count());
        $this->assertSame('JNE222', $pesanan->fresh()->pengiriman->no_resi);
    }

    public function test_pesanan_selesai_tidak_bisa_diinput_resi(): void
    {
        $pesanan = $this->pesanan('selesai');

        $this->actingAs($this->penjual())
            ->put(route('admin.pengiriman.update', $pesanan), ['no_resi' => 'JNE999'])
            ->assertRedirect(route('admin.pengiriman.index'))
            ->assertSessionHas('error');

        $this->assertSame('selesai', $pesanan->fresh()->status);
        $this->assertSame(0, DB::table('pengiriman')->count());
    }

    // ---------------------------------------------------------------- helper ---

    public function test_ambil_kota_dan_telepon_dari_alamat(): void
    {
        $alamat = 'Annisa Ayu (081234567890), Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12190';

        $this->assertSame('Jakarta Selatan', Alamat::kota($alamat));
        $this->assertSame('081234567890', Alamat::telepon($alamat, null));
        $this->assertSame('0899999', Alamat::telepon($alamat, '0899999'));
        $this->assertSame('—', Alamat::kota(null));
        $this->assertSame('Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12190', Alamat::tanpaNama($alamat));
        $this->assertSame('Jl. Melati No. 3, Bandung', Alamat::tanpaNama('Jl. Melati No. 3, Bandung'));
    }
}
