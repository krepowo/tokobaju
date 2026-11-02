<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'menunggu');

        $pesanan = Pesanan::query()
            ->with(['pelanggan', 'invoice', 'pembayaranTerakhir'])
            ->when($tab === 'riwayat', function ($sql) {
                $sql->whereIn('status', [
                    Pesanan::PEMBAYARAN_DITOLAK,
                    Pesanan::DIPROSES,
                    Pesanan::DIKIRIM,
                    Pesanan::SELESAI,
                ]);
            }, fn ($sql) => $sql->where('status', Pesanan::MENUNGGU_VERIFIKASI))
            ->oldest('tanggal_pesanan')
            ->paginate(10)
            ->withQueryString();

        return view('admin.verifikasi.index', [
            'pesanan' => $pesanan,
            'tab' => $tab,
            'jumlahMenunggu' => Pesanan::where('status', Pesanan::MENUNGGU_VERIFIKASI)->count(),
        ]);
    }

    public function show(Pesanan $pesanan): View
    {
        abort_unless(
            in_array($pesanan->status, [Pesanan::MENUNGGU_VERIFIKASI, Pesanan::PEMBAYARAN_DITOLAK], true),
            404,
            'Pesanan ini tidak sedang menunggu verifikasi.'
        );

        $pesanan->load(['pelanggan', 'jasa', 'detail.ukuranStok.produk', 'invoice', 'pembayaran.verifikator']);

        return view('admin.verifikasi.show', [
            'pesanan' => $pesanan,
            'pembayaran' => $pesanan->pembayaran->first(),
        ]);
    }

    public function setujui(Request $request, Pesanan $pesanan): RedirectResponse
    {
        abort_unless($pesanan->status === Pesanan::MENUNGGU_VERIFIKASI, 409);

        $pembayaran = $pesanan->pembayaran()->first();

        if (! $pembayaran) {
            return back()->withErrors(['pembayaran' => 'Belum ada bukti pembayaran untuk pesanan ini.']);
        }

        DB::transaction(function () use ($pesanan, $pembayaran, $request) {
            $pembayaran->update([
                'status_verifikasi' => Pembayaran::DISETUJUI,
                'diverifikasi_oleh' => $request->user()->id_user,
                'alasan_tolak' => null,
            ]);

            $pesanan->update(['status' => Pesanan::DIPROSES]);
        });

        return redirect()->route('admin.verifikasi.index')
            ->with('success', 'Pembayaran '.$pesanan->invoice?->no_invoice.' disetujui. Pesanan masuk ke proses pengemasan.');
    }

    public function tolak(Request $request, Pesanan $pesanan): RedirectResponse
    {
        abort_unless($pesanan->status === Pesanan::MENUNGGU_VERIFIKASI, 409);

        $data = $request->validate([
            'alasan_tolak' => ['required', 'string', 'max:255'],
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
            'alasan_tolak.max' => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        $pembayaran = $pesanan->pembayaran()->first();

        if (! $pembayaran) {
            return back()->withErrors(['pembayaran' => 'Belum ada bukti pembayaran untuk pesanan ini.']);
        }

        DB::transaction(function () use ($pesanan, $pembayaran, $data, $request) {
            $pembayaran->update([
                'status_verifikasi' => Pembayaran::DITOLAK,
                'diverifikasi_oleh' => $request->user()->id_user,
                'alasan_tolak' => $data['alasan_tolak'],
            ]);

            $pesanan->update(['status' => Pesanan::PEMBAYARAN_DITOLAK]);
        });

        return redirect()->route('admin.verifikasi.index')
            ->with('success', 'Pembayaran ditolak. Alasan penolakan sudah disimpan dan dapat dilihat pelanggan.');
    }
}
