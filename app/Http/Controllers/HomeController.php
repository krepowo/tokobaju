<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Beranda pelanggan.
     *
     * Data kategori dan produk masih berupa larik statis karena tabel katalog
     * belum dibuat. Bentuk data sengaja dibuat sama dengan yang nanti dipakai
     * model Kategori / Produk supaya view tidak perlu diubah saat fitur
     * katalog (F-03, F-04) sudah tersedia.
     */
    public function index(): View
    {
        $categories = [
            ['label' => 'Atasan', 'image' => 'cat-atasan.jpg'],
            ['label' => 'Bawahan', 'image' => 'cat-bawahan.jpg'],
            ['label' => 'Outer', 'image' => 'cat-outer.jpg'],
            ['label' => 'Dress', 'image' => 'cat-dress.jpg'],
            ['label' => 'Aksesori', 'image' => 'cat-aksesori.jpg'],
            ['label' => 'Hijab', 'image' => 'cat-hijab.jpg'],
            ['label' => 'Sepatu', 'image' => 'cat-sepatu.jpg'],
        ];

        $products = [
            [
                'name' => 'Kaos Crewneck Katun Pria',
                'category' => 'Atasan',
                'rating' => '4,8',
                'reviews' => 24,
                'price' => 159000,
                'image' => 'prod-kaos.jpg',
            ],
            [
                'name' => 'Celana Chino Slim Fit',
                'category' => 'Bawahan',
                'rating' => '4,9',
                'reviews' => 38,
                'price' => 219000,
                'image' => 'prod-chino.jpg',
            ],
            [
                'name' => 'Cardigan Knit Premium',
                'category' => 'Outer',
                'rating' => '4,7',
                'reviews' => 19,
                'price' => 249000,
                'image' => 'prod-cardigan.jpg',
            ],
            [
                'name' => 'Casual Midi Dress Linen',
                'category' => 'Dress',
                'rating' => '4,8',
                'reviews' => 24,
                'price' => 289000,
                'image' => 'prod-dress.jpg',
            ],
        ];

        return view('home', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
