<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function penjual(): User
    {
        return User::factory()->create([
            'nama' => 'Admin Gavin',
            'role' => User::ROLE_PENJUAL,
        ]);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_pelanggan_tidak_boleh_membuka_dashboard_penjual(): void
    {
        $pelanggan = User::factory()->create(['role' => User::ROLE_PELANGGAN]);

        $this->actingAs($pelanggan)->get('/admin')->assertForbidden();
    }

    public function test_penjual_melihat_kartu_grafik_dan_tabel_dashboard(): void
    {
        $this->actingAs($this->penjual())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Omzet Bulan Ini')
            ->assertSee('Perlu Diverifikasi')
            ->assertSee('Perlu Dikirim')
            ->assertSee('Stok Menipis')
            ->assertSee('Omzet 7 Hari Terakhir')
            ->assertSee('Pesanan Terbaru');
    }

    public function test_sidebar_menampilkan_delapan_menu_penjual(): void
    {
        $this->actingAs($this->penjual())
            ->get('/admin')
            ->assertSee('Dashboard')
            ->assertSee('Kelola Produk, Kategori &amp; Pengiriman', false)
            ->assertSee('Kelola Stok &amp; Ukuran', false)
            ->assertSee('Pesanan &amp; Invoice', false)
            ->assertSee('Proses Pengiriman')
            ->assertSee('Verifikasi Bayar')
            ->assertSee('Lihat Ulasan')
            ->assertSee('Laporan Omzet');
    }
}
