<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Ulasan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UlasanProdukController extends Controller
{
    /**
     * Form beri ulasan (F-11) untuk satu item pesanan yang sudah selesai.
     */
    public function create(Request $request, DetailPesanan $detail): View|RedirectResponse
    {
        $detail->load(['pesanan.invoice', 'pesanan.pelanggan', 'ukuranStok.produk', 'ulasan']);

        abort_unless($detail->pesanan?->id_user === $request->user()->id_user, 403);

        if ($detail->pesanan->status !== Pesanan::SELESAI) {
            return redirect()->route('pesanan.index')
                ->withErrors(['pesanan' => 'Ulasan hanya dapat diberikan setelah pesanan berstatus selesai.']);
        }

        if ($detail->ulasan) {
            return redirect()->route('pesanan.index')
                ->with('success', 'Produk ini sudah Anda ulas. Terima kasih!');
        }

        return view('ulasan.create', [
            'detail' => $detail,
            'produk' => $detail->ukuranStok?->produk,
            'pesanan' => $detail->pesanan,
        ]);
    }

    /**
     * Simpan ulasan: rating wajib 1-5, komentar opsional maksimal 500 karakter.
     */
    public function store(Request $request, DetailPesanan $detail): RedirectResponse
    {
        $detail->load(['pesanan', 'ulasan']);

        abort_unless($detail->pesanan?->id_user === $request->user()->id_user, 403);

        if ($detail->pesanan->status !== Pesanan::SELESAI) {
            return redirect()->route('pesanan.index')
                ->withErrors(['pesanan' => 'Ulasan hanya dapat diberikan setelah pesanan berstatus selesai.']);
        }

        if ($detail->ulasan) {
            return redirect()->route('pesanan.index')->with('success', 'Produk ini sudah Anda ulas.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:500'],
        ], [
            'rating.required' => 'Rating wajib diisi dengan memilih 1 sampai 5 bintang.',
            'rating.between' => 'Rating harus bernilai 1 sampai 5 bintang.',
            'komentar.max' => 'Ulasan tertulis maksimal 500 karakter.',
        ]);

        Ulasan::create([
            'id_user' => $request->user()->id_user,
            'id_produk' => $detail->ukuranStok->id_produk,
            'id_detail' => $detail->id_detail,
            'rating' => (int) $data['rating'],
            'komentar' => $data['komentar'] ?? null,
            'tanggal' => now(),
        ]);

        return redirect()->route('pesanan.index')
            ->with('success', 'Ulasan berhasil dikirim. Terima kasih atas penilaian Anda!');
    }
}
