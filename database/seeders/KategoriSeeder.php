<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'Atasan',
            'Bawahan',
            'Dress',
            'Outer',
            'Aksesori',
            'Hijab',
            'Sepatu',
        ];

        $now = now();
        $rows = [];

        foreach ($kategori as $nama) {
            $rows[] = [
                'id_kategori' => (string) Str::uuid(),
                'nama_kategori' => $nama,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('kategori')->upsert($rows, ['nama_kategori'], ['updated_at']);
    }
}
