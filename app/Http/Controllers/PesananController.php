<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $statusList = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'pembayaran_ditolak' => 'Pembayaran Ditolak',
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
            ->with(['invoice', 'jasa', 'detailPesanan.ukuran.produk', 'pengiriman', 'pembayaran', 'pembayaranTerakhir'])
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
            ->with(['user', 'jasa', 'invoice', 'detailPesanan.ukuran.produk', 'pengiriman', 'pembayaran', 'pembayaranTerakhir'])
            ->where('id_user', auth()->id())
            ->findOrFail($pesanan);

        return view('pesanan.show', ['order' => $order]);
    }

    public function uploadBukti(Request $request, string $pesanan): RedirectResponse
    {
        $order = Pesanan::findOrFail($pesanan);

        if ($order->id_user !== auth()->id()) {
            abort(403);
        }

        $statusBoleh = in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak'], true);
        $batasLewat = $order->batas_bayar && $order->batas_bayar->isPast();

        if (! $statusBoleh || $batasLewat) {
            return redirect()->route('pesanan.show', $order->id_pesanan)
                ->with('error', 'Pesanan tidak dapat dibayar lagi');
        }

        $request->validate([
            'bukti_transfer' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'bukti_transfer.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_transfer.image' => 'File bukti pembayaran harus berupa gambar.',
            'bukti_transfer.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'bukti_transfer.max' => 'Ukuran file gambar maksimal 10 MB.',
        ]);

        $path = $request->file('bukti_transfer')->store('pembayaran', 'public');

        DB::transaction(function () use ($order, $path) {
            Pembayaran::create([
                'id_pesanan' => $order->id_pesanan,
                'bukti_transfer' => $path,
                'tanggal_upload' => now(),
                'status_verifikasi' => 'menunggu',
            ]);

            $order->update([
                'status' => 'menunggu_verifikasi',
            ]);
        });

        return redirect()->route('pesanan.show', $order->id_pesanan)
            ->with('success', 'Bukti pembayaran berhasil diunggah');
    }

    public function konfirmasiTerima(Request $request, string $pesanan): RedirectResponse
    {
        $order = Pesanan::with('pengiriman')->findOrFail($pesanan);

        if ($order->id_user !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'dikirim') {
            return redirect()->route('pesanan.show', $order->id_pesanan)
                ->with('error', 'Pesanan belum dapat dikonfirmasi');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'selesai',
            ]);

            if ($order->pengiriman) {
                $order->pengiriman->update([
                    'tanggal_diterima' => now(),
                ]);
            }
        });

        return redirect()->route('pesanan.show', $order->id_pesanan)
            ->with('success', 'Pesanan telah dikonfirmasi diterima. Terima kasih sudah berbelanja di Gavin Fashion Store!');
    }

    private function muatInvoice(string $id): Pesanan
    {
        $pesanan = Pesanan::with([
            'user', 'invoice', 'jasaPengiriman', 'pengiriman',
            'detailPesanan.ukuranStok.produk',
        ])->findOrFail($id);

        abort_if($pesanan->id_user !== auth()->id(), 403);
        abort_if($pesanan->status === 'dibatalkan', 404);

        return $pesanan;
    }

    public function invoice(string $pesanan): View
    {
        $order = $this->muatInvoice($pesanan);
        return view('pesanan.invoice', ['pesanan' => $order, 'mode' => 'web']);
    }

    public function unduhInvoice(string $pesanan)
    {
        $order = $this->muatInvoice($pesanan);

        return Pdf::loadView('pesanan.invoice', ['pesanan' => $order, 'mode' => 'pdf'])
            ->setPaper('a4')
            ->download('invoice-' . $order->invoice->no_invoice . '.pdf');
    }
}
