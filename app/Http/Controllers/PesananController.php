<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Contracts\View\View;


class PesananController extends Controller
{
    public function index(): View
    {
        $orders = \App\Models\Pesanan::query()
            ->with(['invoice', 'jasa', 'detailPesanan.ukuran.produk'])
            ->where('id_user', auth()->id())
            ->orderByDesc('tanggal_pesanan')
            ->get();

        return view('pesanan.index', ['orders' => $orders]);
    }

    public function show(string $pesanan): View
    {
        $order = Pesanan::query()
            ->with(['jasa', 'invoice', 'detailPesanan.ukuran.produk'])
            ->where('id_user', auth()->id())
            ->findOrFail($pesanan);

        return view('pesanan.show', ['order' => $order]);
    }
}
