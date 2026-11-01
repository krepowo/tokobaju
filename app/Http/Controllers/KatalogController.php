<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class KatalogController extends Controller
{
    /**
     * Halaman katalog (F-03): pencarian, filter kategori/ukuran/harga, dan
     * pengurutan. Data produk dibaca dari database.
     */
    public function index(Request $request): View
    {
        $query = Produk::query()
            ->with('kategori')
            ->withCount('ulasan')
            ->withAvg('ulasan', 'rating')
            ->withSum('ukuranStok as total_stok', 'stok');

        $q = trim((string) $request->query('q'));

        if ($q !== '') {
            $query->where('nama_produk', 'like', '%'.$q.'%');
        }

        $kategoriAktif = array_values(array_filter((array) $request->query('kategori', [])));

        if ($kategoriAktif !== []) {
            $query->whereHas('kategori', fn ($k) => $k->whereIn('nama_kategori', $kategoriAktif));
        }

        $ukuranAktif = array_values(array_filter((array) $request->query('ukuran', [])));

        if ($ukuranAktif !== []) {
            $query->whereHas('ukuranStok', fn ($u) => $u->whereIn('ukuran', $ukuranAktif));
        }

        $hargaMin = $request->query('harga_min');
        $hargaMax = $request->query('harga_max');

        if ($hargaMin !== null && $hargaMin !== '') {
            $query->where('harga', '>=', (int) $hargaMin);
        }

        if ($hargaMax !== null && $hargaMax !== '') {
            $query->where('harga', '<=', (int) $hargaMax);
        }

        $sort = (string) $request->query('sort', 'terbaru');

        match ($sort) {
            'harga_terendah' => $query->orderBy('harga'),
            'harga_tertinggi' => $query->orderByDesc('harga'),
            default => $query->latest(),
        };

        $products = $query->get()->map(fn (Produk $p) => [
            'name' => $p->nama_produk,
            'category' => $p->kategori?->nama_kategori,
            'rating' => $p->ulasan_count > 0
                ? number_format((float) $p->ulasan_avg_rating, 1, ',', '')
                : null,
            'reviews' => $p->ulasan_count,
            'price' => (int) $p->harga,
            'image' => $p->gambar,
            'stok_habis' => ((int) $p->total_stok) <= 0,
        ]);

        return view('catalog', [
            'products' => $products,
            'kategori' => $this->presentedCategories(),
            'ukuranSemua' => ['S', 'M', 'L', 'XL', 'XXL'],
            'q' => $q,
            'kategoriAktif' => $kategoriAktif,
            'ukuranAktif' => $ukuranAktif,
            'hargaMin' => $hargaMin,
            'hargaMax' => $hargaMax,
            'sort' => $sort,
        ]);
    }

    private function presentedCategories(): Collection
    {
        $order = ['Atasan', 'Bawahan', 'Outer', 'Dress', 'Aksesori', 'Hijab', 'Sepatu'];
        $map = array_flip($order);

        return Kategori::all()
            ->sortBy(fn (Kategori $k) => $map[$k->nama_kategori] ?? PHP_INT_MAX)
            ->values();
    }
}
