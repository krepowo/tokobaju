<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Proses pengiriman (mockup Figma 3.6.1 dan 3.6.2).
 * Pesanan berstatus `diproses` diisi nomor resi → berubah menjadi `dikirim`.
 */
class PengirimanController extends Controller
{
    /** Status yang relevan di halaman pengiriman. */
    public const STATUS_PENGIRIMAN = [
        'diproses' => 'Perlu Diproses',
        'dikirim' => 'Dikirim',
    ];

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));
        $status = (string) $request->query('status', 'semua');
        $status = isset(self::STATUS_PENGIRIMAN[$status]) ? $status : 'semua';

        $daftarPesanan = Pesanan::query()
            ->with(['user', 'invoice', 'jasa', 'pengiriman', 'detailPesanan'])
            ->whereIn('status', array_keys(self::STATUS_PENGIRIMAN))
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($sub) use ($cari) {
                    $sub->whereHas('invoice', fn ($invoice) => $invoice->where('no_invoice', 'like', "%{$cari}%"))
                        ->orWhereHas('user', fn ($user) => $user->where('nama', 'like', "%{$cari}%"));
                });
            })
            ->when($status !== 'semua', fn ($query) => $query->where('status', $status))
            ->orderByDesc('tanggal_pesanan')
            ->paginate(10)
            ->withQueryString();

        // Kartu ringkasan = beban kerja keseluruhan, tidak terpengaruh filter.
        $perluDiproses = Pesanan::query()->where('status', 'diproses')->count();
        $dikirim = Pesanan::query()->where('status', 'dikirim')->count();

        return view('admin.pengiriman.index', [
            'daftarPesanan' => $daftarPesanan,
            'daftarStatus' => self::STATUS_PENGIRIMAN,
            'cari' => $cari,
            'status' => $status,
            'ringkasan' => [
                'total' => $perluDiproses + $dikirim,
                'perlu_diproses' => $perluDiproses,
                'dikirim' => $dikirim,
            ],
        ]);
    }

    public function update(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if (! in_array($pesanan->status, ['diproses', 'dikirim'], true)) {
            return redirect()
                ->route('admin.pengiriman.index')
                ->with('error', 'Pesanan ini tidak sedang dalam proses pengiriman.');
        }

        $data = $request->validate([
            'no_resi' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'no_resi.required' => 'Nomor resi wajib diisi.',
            'no_resi.max' => 'Nomor resi maksimal 50 karakter.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        DB::transaction(function () use ($pesanan, $data) {
            $pengiriman = $pesanan->pengiriman()->first();

            if ($pengiriman) {
                $pengiriman->update([
                    'no_resi' => $data['no_resi'],
                    'catatan' => $data['catatan'] ?? null,
                ]);
            } else {
                $pesanan->pengiriman()->create([
                    'no_resi' => $data['no_resi'],
                    'tanggal_kirim' => now(),
                    'catatan' => $data['catatan'] ?? null,
                ]);
            }

            $pesanan->update(['status' => 'dikirim']);
        });

        $nomor = $pesanan->invoice?->no_invoice ?? 'pesanan ini';

        return redirect()
            ->route('admin.pengiriman.index')
            ->with('success', 'Nomor resi untuk '.$nomor.' disimpan. Status pesanan menjadi "Dikirim".');
    }
}
