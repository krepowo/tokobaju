<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $statusList = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        $base = Pesanan::query()->where('id_user', auth()->id());

        $totalCount = (clone $base)->count();
        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) AS cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $active = (string) $request->query('status', 'semua');

        $orders = Pesanan::query()
            ->with(['invoice', 'jasa', 'detailPesanan.ukuran.produk', 'pengiriman', 'pembayaran'])
            ->where('id_user', auth()->id())
            ->when($active !== 'semua' && isset($statusList[$active]), fn ($q) => $q->where('status', $active))
            ->orderByDesc('tanggal_pesanan')
            ->get();

        return view('pesanan.index', [
            'orders' => $orders,
            'statusList' => $statusList,
            'counts' => $counts,
            'totalCount' => $totalCount,
            'active' => $active,
        ]);
    }

    public function show(string $pesanan): View
    {
        $order = Pesanan::query()
            ->with(['jasa', 'invoice', 'detailPesanan.ukuran.produk', 'pengiriman', 'pembayaran'])
            ->where('id_user', auth()->id())
            ->findOrFail($pesanan);

        return view('pesanan.show', ['order' => $order]);
    }
}
