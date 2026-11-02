<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $produk = Produk::query()
            ->with('kategori')
            ->withCount('ulasan')
            ->withAvg('ulasan', 'rating')
            ->when($q !== '', fn ($sql) => $sql->where('nama_produk', 'like', '%'.$q.'%'))
            ->orderByDesc('ulasan_count')
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        return view('admin.ulasan.index', compact('produk', 'q'));
    }

    public function show(Produk $produk): View
    {
        $ulasan = $produk->ulasan()
            ->with(['user', 'detailPesanan.ukuranStok'])
            ->latest('tanggal')
            ->paginate(10);

        return view('admin.ulasan.show', [
            'produk' => $produk->load('kategori'),
            'ulasan' => $ulasan,
            'rataRata' => round((float) $produk->ulasan()->avg('rating'), 1),
            'jumlah' => $produk->ulasan()->count(),
            'sebaran' => $produk->ulasan()
                ->selectRaw('rating, COUNT(*) as jumlah')
                ->groupBy('rating')
                ->pluck('jumlah', 'rating'),
        ]);
    }
}
