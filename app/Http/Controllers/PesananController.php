<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Contracts\View\View;

class PesananController extends Controller
{
    public function show(string $pesanan): View
    {
        $order = Pesanan::query()
            ->with(['jasa', 'invoice', 'detailPesanan.ukuran.produk'])
            ->where('id_user', auth()->id())
            ->findOrFail($pesanan);

        return view('pesanan.show', ['order' => $order]);
    }
}
