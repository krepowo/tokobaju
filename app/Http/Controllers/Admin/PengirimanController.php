<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengirimanController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'perlu');

        $pesanan = Pesanan::query()
            ->with(['pelanggan', 'invoice', 'jasa', 'pengiriman'])
            ->when($tab === 'sudah', function ($sql) {
                $sql->whereIn('status', [Pesanan::DIKIRIM, Pesanan::SELESAI])
                    ->latest('updated_at');
            }, fn ($sql) => $sql->where('status', Pesanan::DIPROSES)->oldest('tanggal_pesanan'))
            ->paginate(10)
            ->withQueryString();

        return view('admin.kirim.index', [
            'pesanan' => $pesanan,
            'tab' => $tab,
            'perluDikirim' => Pesanan::where('status', Pesanan::DIPROSES)->count(),
            'sudahDikirim' => Pesanan::whereIn('status', [Pesanan::DIKIRIM, Pesanan::SELESAI])->count(),
        ]);
    }

    public function simpan(Request $request, Pesanan $pesanan): RedirectResponse
    {
        abort_unless(in_array($pesanan->status, [Pesanan::DIPROSES, Pesanan::DIKIRIM], true), 409);

        $data = $request->validate([
            'no_resi' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'no_resi.required' => 'Nomor resi wajib diisi.',
            'no_resi.max' => 'Nomor resi maksimal 50 karakter.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        DB::transaction(function () use ($pesanan, $data) {
            Pengiriman::updateOrCreate(
                ['id_pesanan' => $pesanan->id_pesanan],
                [
                    'no_resi' => $data['no_resi'],
                    'catatan' => $data['catatan'] ?? null,
                    'tanggal_kirim' => $pesanan->pengiriman?->tanggal_kirim ?? now(),
                ],
            );

            if ($pesanan->status === Pesanan::DIPROSES) {
                $pesanan->update(['status' => Pesanan::DIKIRIM]);
            }
        });

        return redirect()->route('admin.kirim.index')
            ->with('success', 'Nomor resi disimpan. Pesanan '.$pesanan->invoice?->no_invoice.' berstatus Dikirim.');
    }
}
