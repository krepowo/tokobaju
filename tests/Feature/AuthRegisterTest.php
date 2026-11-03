<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelanggan_baru_bisa_mendaftar(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Rina Kartika',
            'email' => 'Rina.Kartika@Example.com',
            'phone' => '081298765432',
            'password' => 'rahasia123',
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Registrasi berhasil. Selamat datang, Rina Kartika.');

        $user = User::where('email', 'rina.kartika@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Rina Kartika', $user->nama);
        $this->assertSame('081298765432', $user->no_telepon);
        $this->assertSame(User::ROLE_PELANGGAN, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_nomor_telepon_dinormalisasi(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '+62 812-3456-7890',
            'password' => 'rahasia123',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
        ]);
    }

    public function test_daftar_tanpa_nomor_telepon_tetap_bisa(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Tanpa Telepon',
            'email' => 'tanpa@example.com',
            'password' => 'rahasia123',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'tanpa@example.com',
            'no_telepon' => null,
        ]);
    }

    public function test_email_ganda_ditolak(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'role' => User::ROLE_PELANGGAN]);

        $this->post(route('register.store'), [
            'name' => 'Pendaftar Baru',
            'email' => 'ADA@example.com',
            'phone' => '081200001111',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::whereRaw('LOWER(email) = ?', ['ada@example.com'])->count());
    }

    public function test_nomor_telepon_ganda_ditolak_tanpa_error_database(): void
    {
        User::factory()->create([
            'email' => 'lama@example.com',
            'no_telepon' => '081255556666',
            'role' => User::ROLE_PELANGGAN,
        ]);

        // Regresi: dulu query memakai kolom `phone` yang tidak ada di tabel users.
        $this->post(route('register.store'), [
            'name' => 'Nomor Sama',
            'email' => 'baru@example.com',
            'phone' => '081255556666',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('phone');

        $this->assertSame(0, User::where('email', 'baru@example.com')->count());
    }

    public function test_validasi_dasar_register(): void
    {
        $this->post(route('register.store'), [
            'name' => 'ab',
            'email' => 'bukan-email',
            'password' => 'pendek',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
