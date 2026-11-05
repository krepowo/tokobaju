<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    /**
     * Menampilkan daftar katalog produk dengan pencarian, filter, dan pengurutan.
     */
    public function index(Request $request): View
    {
        $query = Produk::with(['kategori', 'varianUkuran', 'ulasan']);

        // 1. Pencarian teks
        if ($request->filled('cari')) {
            $keyword = trim($request->cari);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_produk', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('kategori', function ($catQ) use ($keyword) {
                        $catQ->where('nama_kategori', 'like', "%{$keyword}%");
                    });
            });
        }

        // 2. Filter Kategori (bisa string tunggal atau array)
        if ($request->filled('kategori')) {
            $categories = (array) $request->kategori;
            $query->whereHas('kategori', function ($q) use ($categories) {
                $q->whereIn('nama_kategori', $categories);
            });
        }

        // 3. Filter Ukuran
        if ($request->filled('ukuran')) {
            $sizes = (array) $request->ukuran;
            $query->whereHas('varianUkuran', function ($q) use ($sizes) {
                $q->whereIn('ukuran', $sizes);
            });
        }

        // 4. Filter Rentang Harga
        if ($request->filled('min_harga')) {
            $minHarga = (float) preg_replace('/[^0-9]/', '', $request->min_harga);
            if ($minHarga > 0) {
                $query->where('harga', '>=', $minHarga);
            }
        }

        if ($request->filled('max_harga')) {
            $maxHarga = (float) preg_replace('/[^0-9]/', '', $request->max_harga);
            if ($maxHarga > 0) {
                $query->where('harga', '<=', $maxHarga);
            }
        }

        // 5. Pengurutan
        $urutan = $request->get('urutan', 'terbaru');
        match ($urutan) {
            'harga_asc' => $query->orderBy('harga', 'asc'),
            'harga_desc' => $query->orderBy('harga', 'desc'),
            'populer' => $query->withAvg('ulasan', 'rating')->orderByDesc('ulasan_avg_rating'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $produk = $query->paginate(12)->withQueryString();
        
        // Urutan kategori persis seperti Mockup 1: Atasan, Bawahan, Outer, Dress, Aksesori, Hijab, Sepatu
        $order = ['Atasan', 'Bawahan', 'Outer', 'Dress', 'Aksesori', 'Hijab', 'Sepatu'];
        $kategoriList = Kategori::withCount('produk')
            ->get()
            ->sortBy(function ($cat) use ($order) {
                $pos = array_search($cat->nama_kategori, $order);
                return $pos === false ? 999 : $pos;
            })
            ->values();

        $availableSizes = ['S', 'M', 'L', 'XL', 'XXL'];

        return view('katalog.index', compact('produk', 'kategoriList', 'availableSizes', 'urutan'));
    }

    /**
     * Menampilkan detail satu produk dengan galeri, pilihan ukuran-warna, stok, spesifikasi, dan ulasan.
     */
    public function show(string $id): View
    {
        $produk = Produk::with(['kategori', 'varianUkuran', 'ulasan.user'])->findOrFail($id);

        // Ambil daftar warna & ukuran unik
        $colors = $produk->varianUkuran->pluck('warna')->filter()->unique()->values();
        $sizes = $produk->varianUkuran->pluck('ukuran')->filter()->unique()->values();

        // Buat map kombinasi warna_ukuran => [id_ukuran, stok, aktif]
        $stockMap = [];
        foreach ($produk->varianUkuran as $variant) {
            $key = "{$variant->warna}_{$variant->ukuran}";
            $stockMap[$key] = [
                'id_ukuran' => $variant->id_ukuran,
                'stok' => (int) $variant->stok,
                'aktif' => (bool) $variant->aktif,
            ];
        }

        // Galeri foto produk
        $gallery = [];
        if (str_contains(strtolower($produk->nama_produk), 'midi dress')) {
            $gallery = [
                [
                    'thumb' => asset('images/prod-dress-sky-stripe.webp'),
                    'full' => asset('images/prod-dress-main.webp'),
                    'label' => 'Sky Stripe',
                    'color' => 'Sky Stripe',
                ],
                [
                    'thumb' => asset('images/prod-dress-choco-stripe.webp'),
                    'full' => asset('images/prod-dress-choco-stripe.webp'),
                    'label' => 'Choco Stripe',
                    'color' => 'Choco Stripe',
                ],
                [
                    'thumb' => asset('images/prod-dress-butter.webp'),
                    'full' => asset('images/prod-dress-butter.webp'),
                    'label' => 'Butter',
                    'color' => 'Butter',
                ],
                [
                    'thumb' => asset('images/prod-dress-deep-brown.webp'),
                    'full' => asset('images/prod-dress-deep-brown.webp'),
                    'label' => 'Deep Brown',
                    'color' => 'Deep Brown',
                ],
            ];
        } else {
            $gallery = [
                [
                    'thumb' => $produk->gambar_url,
                    'full' => $produk->gambar_url,
                    'label' => $produk->nama_produk,
                    'color' => $colors->first() ?? 'Original',
                ],
            ];
        }

        // Parsing spesifikasi ukuran
        $spesifikasi = [];
        if (!empty($produk->spesifikasi_ukuran)) {
            $decoded = json_decode($produk->spesifikasi_ukuran, true);
            if (is_array($decoded)) {
                $spesifikasi = $decoded;
            }
        }

        // Statistik ulasan pembeli dinamis dari database produk
        $totalUlasan = $produk->ulasan->count();
        $avgRating = $totalUlasan > 0 ? round((float) $produk->ulasan->avg('rating'), 1) : 0.0;
        $effectiveTotal = $totalUlasan;

        $starCounts = [
            5 => $produk->ulasan->where('rating', 5)->count(),
            4 => $produk->ulasan->where('rating', 4)->count(),
            3 => $produk->ulasan->where('rating', 3)->count(),
            2 => $produk->ulasan->where('rating', 2)->count(),
            1 => $produk->ulasan->where('rating', 1)->count(),
        ];

        return view('katalog.show', compact(
            'produk',
            'colors',
            'sizes',
            'stockMap',
            'gallery',
            'spesifikasi',
            'totalUlasan',
            'avgRating',
            'effectiveTotal',
            'starCounts'
        ));
    }
}
