<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Support\StatusPesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PesananPelangganController extends Controller
{
    /**
     * Halaman "Pesanan Saya" (F-09): daftar pesanan beserta status, resi,
     * dan aksi sesuai status (konfirmasi terima, beri ulasan, lihat invoice).
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'semua');
        $statusList = StatusPesanan::semua();

        $dasar = Pesanan::query()->where('id_user', $request->user()->id_user);

        $jumlah = ['semua' => (clone $dasar)->count()];

        foreach (array_keys($statusList) as $kunci) {
            $jumlah[$kunci] = (clone $dasar)->where('status', $kunci)->count();
        }

        $pesanan = $dasar
            ->with([
                'detail.ukuranStok.produk',
                'detail.ulasan',
                'invoice',
                'jasa',
                'pengiriman',
                'pembayaranTerakhir',
            ])
            ->when($tab !== 'semua' && array_key_exists($tab, $statusList), fn ($sql) => $sql->where('status', $tab))
            ->latest('tanggal_pesanan')
            ->paginate(5)
            ->withQueryString();

        return view('pesanan.index', [
            'pesanan' => $pesanan,
            'tab' => $tab,
            'jumlah' => $jumlah,
            'total' => $jumlah['semua'],
        ]);
    }

    /**
     * Invoice pesanan untuk pelanggan (F-07).
     */
    public function invoice(Request $request, Pesanan $pesanan): View
    {
        abort_unless($pesanan->id_user === $request->user()->id_user, 403);

        $pesanan->load(['pelanggan', 'jasa', 'detail.ukuranStok.produk', 'invoice', 'pembayaran', 'pengiriman']);

        return view('pesanan.invoice', ['pesanan' => $pesanan]);
    }

    /**
     * Konfirmasi penerimaan (F-10): status dikirim -> selesai.
     */
    public function terima(Request $request, Pesanan $pesanan): RedirectResponse
    {
        abort_unless($pesanan->id_user === $request->user()->id_user, 403);

        if ($pesanan->status !== Pesanan::DIKIRIM) {
            return back()->withErrors(['pesanan' => 'Pesanan ini belum dapat dikonfirmasi karena statusnya bukan "Dikirim".']);
        }

        $pesanan->update(['status' => Pesanan::SELESAI]);

        return back()->with('success', 'Terima kasih. Pesanan '.$pesanan->invoice?->no_invoice.' sudah dikonfirmasi diterima.');
    }
}
