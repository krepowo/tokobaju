<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Support\Stok;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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

    /**
     * Unggah bukti transfer (F-08).
     * Status pesanan menjadi `menunggu_verifikasi` sehingga masuk antrean penjual.
     */
    public function uploadBukti(Request $request, string $pesanan): RedirectResponse
    {
        $order = Pesanan::query()
            ->with(['detailPesanan', 'pembayaran'])
            ->where('id_user', auth()->id())
            ->findOrFail($pesanan);

        if (! in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak'], true)) {
            return redirect()
                ->route('pesanan.show', $order)
                ->with('error', 'Bukti pembayaran tidak bisa diunggah untuk pesanan berstatus '.$order->statusLabel().'.');
        }

        $request->validate([
            'bukti' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'bukti.required' => 'Pilih file bukti transfer terlebih dahulu.',
            'bukti.image' => 'File bukti transfer harus berupa gambar.',
            'bukti.mimes' => 'Format bukti transfer harus JPG, JPEG, atau PNG.',
            'bukti.max' => 'Ukuran bukti transfer maksimal 10 MB.',
        ]);

        // Unggah ulang setelah ditolak: stok sudah dikembalikan, jadi dipesan lagi.
        if ($order->status === 'pembayaran_ditolak' && ! Stok::pesanUlang($order)) {
            return redirect()
                ->route('pesanan.show', $order)
                ->with('error', 'Stok untuk salah satu varian pada pesanan ini sudah tidak tersedia, sehingga pembayaran tidak dapat dilanjutkan.');
        }

        $folder = public_path('images/uploads');
        File::ensureDirectoryExists($folder);

        $namaBerkas = $request->file('bukti')->hashName();
        $request->file('bukti')->move($folder, $namaBerkas);

        DB::transaction(function () use ($order, $namaBerkas) {
            $order->pembayaran()->create([
                'bukti_transfer' => 'images/uploads/'.$namaBerkas,
                'tanggal_upload' => now(),
                'status_verifikasi' => 'menunggu',
            ]);

            $order->update(['status' => 'menunggu_verifikasi']);
        });

        return redirect()
            ->route('pesanan.show', $order)
            ->with('success', 'Bukti pembayaran terkirim. Pesanan menunggu verifikasi penjual.');
    }
}
