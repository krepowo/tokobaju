<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Beranda pelanggan.
     *
     * Kategori dan produk dibaca dari database. Rating/ulasan dihitung dari
     * tabel ulasan (masih kosong sampai fitur F-11 memberi ulasan dikerjakan).
     */
    public function index(): View
    {
        $categories = $this->presentedCategories();

        $products = Produk::query()
            ->with('kategori')
            ->withCount('ulasan')
            ->withAvg('ulasan', 'rating')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Produk $p) => [
                'id' => $p->id_produk,
                'name' => $p->nama_produk,
                'category' => $p->kategori?->nama_kategori,
                'rating' => $p->ulasan_count > 0
                    ? number_format((float) $p->ulasan_avg_rating, 1, ',', '')
                    : null,
                'reviews' => $p->ulasan_count,
                'price' => (int) $p->harga,
                'image' => $p->gambar,
            ]);

        return view('home', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Kategori beranda mengikuti urutan tampilan desain (frame 25:769).
     * Tabel kategori hanya menyimpan nama, jadi urutan + ikon dipegang di sini.
     */
    private function presentedCategories(): Collection
    {
        $images = [
            'Atasan' => 'cat-atasan.jpg',
            'Bawahan' => 'cat-bawahan.jpg',
            'Outer' => 'cat-outer.jpg',
            'Dress' => 'cat-dress.jpg',
            'Aksesori' => 'cat-aksesori.jpg',
            'Hijab' => 'cat-hijab.jpg',
            'Sepatu' => 'cat-sepatu.jpg',
        ];

        $order = array_flip(array_keys($images));

        return Kategori::all()
            ->sortBy(fn (Kategori $k) => $order[$k->nama_kategori] ?? PHP_INT_MAX)
            ->values()
            ->map(fn (Kategori $k) => [
                'label' => $k->nama_kategori,
                'image' => $images[$k->nama_kategori] ?? 'cat-atasan.jpg',
            ]);
    }
}
