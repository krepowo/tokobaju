<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Support\Kedaluwarsa;
use App\Support\Stok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Verifikasi pembayaran (mockup Figma 3.5.1 dan 3.5.2, F-16).
 *
 * Hanya pembayaran berstatus `menunggu` yang masuk antrean.
 * Setujui → pesanan menjadi `diproses`. Tolak → `pembayaran_ditolak` + stok kembali.
 */
class PembayaranController extends Controller
{
    /** Alasan penolakan manual tidak boleh kosong. */
    public function index(Request $request): View
    {
        // Jaring pengaman: tolak yang sudah melewati batas_bayar sebelum ditampilkan.
        Kedaluwarsa::jalankan();

        $cari = trim((string) $request->query('cari', ''));
        $dari = (string) $request->query('dari', '');
        $sampai = (string) $request->query('sampai', '');

        $daftar = Pembayaran::query()
            ->with(['pesanan.user', 'pesanan.invoice', 'pesanan.jasa'])
            ->where('status_verifikasi', 'menunggu')
            ->when($cari !== '', function ($query) use ($cari) {
                $query->whereHas('pesanan', function ($sub) use ($cari) {
                    $sub->whereHas('invoice', fn ($invoice) => $invoice->where('no_invoice', 'like', "%{$cari}%"))
                        ->orWhereHas('user', fn ($user) => $user->where('nama', 'like', "%{$cari}%"));
                });
            })
            ->when($dari !== '', fn ($query) => $query->whereDate('tanggal_upload', '>=', $dari))
            ->when($sampai !== '', fn ($query) => $query->whereDate('tanggal_upload', '<=', $sampai))
            ->orderBy('tanggal_upload')
            ->paginate(10)
            ->withQueryString();

        return view('admin.verifikasi.index', [
            'daftar' => $daftar,
            'cari' => $cari,
            'dari' => $dari,
            'sampai' => $sampai,
            'jumlahMenunggu' => Pembayaran::query()->where('status_verifikasi', 'menunggu')->count(),
        ]);
    }

    public function show(Pembayaran $pembayaran): View
    {
        $pembayaran->load([
            'pesanan.user',
            'pesanan.invoice',
            'pesanan.jasa',
            'pesanan.detailPesanan.ukuran.produk',
        ]);

        return view('admin.verifikasi.show', ['pembayaran' => $pembayaran]);
    }

    public function setujui(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status_verifikasi !== 'menunggu') {
            return redirect()
                ->route('admin.verifikasi.index')
                ->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($pembayaran, $request) {
            $pembayaran->update([
                'status_verifikasi' => 'disetujui',
                'diverifikasi_oleh' => $request->user()->id_user,
                'alasan_tolak' => null,
            ]);

            // Stok sudah dipotong saat checkout dan tidak dikembalikan,
            // jadi tidak perlu dipotong lagi di sini.
            $pembayaran->pesanan->update(['status' => 'diproses']);
        });

        $nomor = $pembayaran->pesanan->invoice?->no_invoice ?? 'pesanan ini';

        return redirect()
            ->route('admin.verifikasi.index')
            ->with('success', 'Pembayaran '.$nomor.' disetujui. Pesanan masuk ke Proses Pengiriman.');
    }

    public function tolak(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status_verifikasi !== 'menunggu') {
            return redirect()
                ->route('admin.verifikasi.index')
                ->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
        }

        $data = $request->validate([
            'alasan_tolak' => ['required', 'string', 'max:255'],
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
            'alasan_tolak.max' => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        DB::transaction(function () use ($pembayaran, $data, $request) {
            $pembayaran->update([
                'status_verifikasi' => 'ditolak',
                'diverifikasi_oleh' => $request->user()->id_user,
                'alasan_tolak' => $data['alasan_tolak'],
            ]);

            $pesanan = $pembayaran->pesanan;
            $pesanan->update(['status' => 'pembayaran_ditolak']);

            // Pesanan ditolak → stok dikembalikan; pelanggan boleh unggah ulang
            // dan stok akan dipesan lagi saat itu.
            Stok::kembalikan($pesanan);
        });

        $nomor = $pembayaran->pesanan->invoice?->no_invoice ?? 'pesanan ini';

        return redirect()
            ->route('admin.verifikasi.index')
            ->with('success', 'Pembayaran '.$nomor.' ditolak. Alasan penolakan ditampilkan ke pelanggan.');
    }
}
