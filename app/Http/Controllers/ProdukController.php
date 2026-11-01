<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Contracts\View\View;

class ProdukController extends Controller
{
    /**
     * Halaman detail produk (F-04) sesuai desain Figma: galeri, info produk
     * (kategori, nama, rating, harga, warna, ukuran, jumlah, stok, CTA),
     * deskripsi + spesifikasi, serta ulasan pembeli.
     */
    public function show(Produk $produk): View
    {
        $produk->load([
            'kategori',
            'ulasan' => fn ($q) => $q->orderByDesc('tanggal'),
            'ulasan.user',
            'ulasan.detail.ukuran',
            'ukuranStok' => fn ($q) => $q->orderBy('created_at'),
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

        // Ringkasan rating (5..1 bintang) untuk blok "Ulasan Pembeli".
        $ratingBars = [];
        for ($bintang = 5; $bintang >= 1; $bintang--) {
            $jumlah = $produk->ulasan->where('rating', $bintang)->count();
            $ratingBars[] = [
                'label' => $bintang.' Bintang',
                'count' => $jumlah,
                'pct' => $ratingCount > 0 ? (int) round($jumlah / $ratingCount * 100) : 0,
            ];
        }

        // Ukuran yang stoknya habis (semua warna tidak aktif/stok 0) -> coret.
        $ukuranHabis = $produk->ukuranStok
            ->groupBy('ukuran')
            ->filter(fn ($rows) => $rows->every(fn ($r) => ! ($r->aktif && $r->stok > 0)))
            ->keys()
            ->values()
            ->all();

        [$deskripsiIntro, $deskripsiPoin] = $this->parseDeskripsi($produk->deskripsi);

        return view('produk.detail', [
            'produk' => $produk,
            'varian' => $varian,
            'ratingAvg' => $ratingAvg,
            'ratingCount' => $ratingCount,
            'ratingBars' => $ratingBars,
            'ulasanList' => $produk->ulasan->take(3),
            'stokTersedia' => $stokTersedia,
            'ukuranHabis' => $ukuranHabis,
            'deskripsiIntro' => $deskripsiIntro,
            'deskripsiPoin' => $deskripsiPoin,
            'spesifikasi' => $this->parseSpesifikasi($produk->spesifikasi_ukuran),
        ]);
    }

    /**
     * Pisahkan deskripsi menjadi paragraf pengantar dan poin "Label: isi".
     *
     * @return array{0: string, 1: list<array{0: string, 1: string}>}
     */
    private function parseDeskripsi(string $deskripsi): array
    {
        $intro = [];
        $poin = [];

        foreach (preg_split('/\R/', trim($deskripsi)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(.+?):\s*(.+)$/', $line, $m)) {
                $poin[] = [$m[1], $m[2]];
            } else {
                $intro[] = $line;
            }
        }

        return [implode(' ', $intro), $poin];
    }

    /**
     * Ubah teks spesifikasi "Ukuran | A | B\nS | 1 | 2" menjadi baris tabel.
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
