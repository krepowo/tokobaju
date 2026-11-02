<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Support\StatusPesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $status = $request->query('status');

        $pesanan = Pesanan::query()
            ->with(['pelanggan', 'invoice', 'pembayaranTerakhir'])
            ->when($status, fn ($sql) => $sql->where('status', $status))
            ->when($q !== '', function ($sql) use ($q) {
                $sql->where(function ($sub) use ($q) {
                    $sub->whereHas('invoice', fn ($i) => $i->where('no_invoice', 'like', '%'.$q.'%'))
                        ->orWhereHas('pelanggan', fn ($u) => $u->where('nama', 'like', '%'.$q.'%'));
                });
            })
            ->latest('tanggal_pesanan')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pesanan.index', [
            'pesanan' => $pesanan,
            'q' => $q,
            'status' => $status,
            'statusList' => StatusPesanan::semua(),
            'ringkasan' => [
                'semua' => Pesanan::count(),
                'menunggu_pembayaran' => Pesanan::where('status', Pesanan::MENUNGGU_PEMBAYARAN)->count(),
                'menunggu_verifikasi' => Pesanan::where('status', Pesanan::MENUNGGU_VERIFIKASI)->count(),
                'diproses' => Pesanan::where('status', Pesanan::DIPROSES)->count(),
                'dikirim' => Pesanan::where('status', Pesanan::DIKIRIM)->count(),
                'selesai' => Pesanan::where('status', Pesanan::SELESAI)->count(),
            ],
        ]);
    }

    public function show(Pesanan $pesanan): View
    {
        $pesanan->load([
            'pelanggan',
            'jasa.tarif',
            'detail.ukuranStok.produk',
            'invoice',
            'pembayaran.verifikator',
            'pengiriman',
        ]);

        return view('admin.pesanan.show', [
            'pesanan' => $pesanan,
        ]);
    }

    public function cetak(Pesanan $pesanan): View
    {
        $pesanan->load(['pelanggan', 'jasa', 'detail.ukuranStok.produk', 'invoice', 'pembayaran', 'pengiriman']);

        return view('admin.pesanan.cetak', [
            'pesanan' => $pesanan,
        ]);
    }
}
