<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Contracts\View\View;

class ProdukController extends Controller
{
    /**
     * Halaman detail produk (F-04): gambar, deskripsi, harga, pilihan warna,
     * ukuran dan stok per kombinasi, tabel spesifikasi ukuran, serta ulasan.
     */
    public function show(Produk $produk): View
    {
        $produk->load([
            'kategori',
            'ulasan.user',
            'ukuranStok' => fn ($q) => $q->orderBy('warna')->orderBy('ukuran'),
        ]);

        $varian = $produk->ukuranStok
            ->groupBy('warna')
            ->map(fn ($rows) => $rows->map(fn ($r) => [
                'id' => $r->id_ukuran,
                'ukuran' => $r->ukuran,
                'stok' => (int) $r->stok,
                'aktif' => (bool) $r->aktif,
            ])->values());

        $ratingCount = $produk->ulasan->count();
        $ratingAvg = $ratingCount > 0 ? round((float) $produk->ulasan->avg('rating'), 1) : null;
        $stokTersedia = (int) $produk->ukuranStok->where('aktif', true)->sum('stok');

        return view('produk.detail', [
            'produk' => $produk,
            'varian' => $varian,
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'stokTersedia' => $stokTersedia,
            'spesifikasi' => $this->parseSpesifikasi($produk->spesifikasi_ukuran),
        ]);
    }

    /**
     * Ubah teks spesifikasi "Ukuran | A | B
S | 1 | 2" menjadi baris-baris tabel.
     *
     * @return list<list<string>>
     */
    private function parseSpesifikasi(?string $text): array
    {
        if (blank($text)) {
            return [];
        }

        $rows = [];

        foreach (preg_split('/\R/', trim($text)) as $line) {
            $cells = array_map('trim', explode('|', $line));
            if ($cells !== ['']) {
                $rows[] = $cells;
            }
        }

        return $rows;
    }
}
