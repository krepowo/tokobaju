<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvinsiSeeder extends Seeder
{
    public function run(): void
    {
        $provinsi = [
            'Aceh' => 'Sumatera',
            'Sumatera Utara' => 'Sumatera',
            'Sumatera Barat' => 'Sumatera',
            'Riau' => 'Sumatera',
            'Kepulauan Riau' => 'Sumatera',
            'Jambi' => 'Sumatera',
            'Sumatera Selatan' => 'Sumatera',
            'Kepulauan Bangka Belitung' => 'Sumatera',
            'Bengkulu' => 'Sumatera',
            'Lampung' => 'Sumatera',
            'DKI Jakarta' => 'Jawa',
            'Jawa Barat' => 'Jawa',
            'Banten' => 'Jawa',
            'Jawa Tengah' => 'Jawa',
            'DI Yogyakarta' => 'Jawa',
            'Jawa Timur' => 'Jawa',
            'Kalimantan Barat' => 'Kalimantan',
            'Kalimantan Tengah' => 'Kalimantan',
            'Kalimantan Selatan' => 'Kalimantan',
            'Kalimantan Timur' => 'Kalimantan',
            'Kalimantan Utara' => 'Kalimantan',
            'Sulawesi Utara' => 'Sulawesi',
            'Gorontalo' => 'Sulawesi',
            'Sulawesi Tengah' => 'Sulawesi',
            'Sulawesi Barat' => 'Sulawesi',
            'Sulawesi Selatan' => 'Sulawesi',
            'Sulawesi Tenggara' => 'Sulawesi',
            'Bali' => 'Bali & Nusa Tenggara',
            'Nusa Tenggara Barat' => 'Bali & Nusa Tenggara',
            'Nusa Tenggara Timur' => 'Bali & Nusa Tenggara',
            'Maluku' => 'Maluku & Papua',
            'Maluku Utara' => 'Maluku & Papua',
            'Papua Barat' => 'Maluku & Papua',
            'Papua Barat Daya' => 'Maluku & Papua',
            'Papua' => 'Maluku & Papua',
            'Papua Tengah' => 'Maluku & Papua',
            'Papua Pegunungan' => 'Maluku & Papua',
            'Papua Selatan' => 'Maluku & Papua',
        ];

        $now = now();
        $rows = [];

        foreach ($provinsi as $nama => $pulau) {
            $rows[] = [
                'id_provinsi' => (string) Str::uuid(),
                'nama_provinsi' => $nama,
                'pulau' => $pulau,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('provinsi')->upsert($rows, ['nama_provinsi'], ['pulau', 'updated_at']);
    }
}
