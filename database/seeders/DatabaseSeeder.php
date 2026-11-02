<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProvinsiSeeder::class,
            KategoriSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@gavin.test'],
            [
                'nama' => 'Admin Gavin',
                'no_telepon' => '081200000001',
                'password' => 'password',
                'role' => User::ROLE_PENJUAL,
            ]
        );

        User::updateOrCreate(
            ['email' => 'annisa.ayu@gmail.com'],
            [
                'nama' => 'Annisa Ayu',
                'no_telepon' => '081234567890',
                'password' => 'password',
                'role' => User::ROLE_PELANGGAN,
                'provinsi' => 'Jawa Tengah',
                'kota_kabupaten' => 'Kota Surakarta',
                'kecamatan' => 'Banjarsari',
                'kelurahan_desa' => 'Manahan',
                'kode_pos' => '57139',
                'detail_alamat' => 'Jl. Adi Sucipto No. 88, RT 02 / RW 04, Manahan, Patokan: Dekat Stadion Manahan',
            ]
        );

        $this->call(DemoSeeder::class);
    }
}
