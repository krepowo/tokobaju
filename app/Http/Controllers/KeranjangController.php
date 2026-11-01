<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\UkuranStok;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    /** Auth di-enforce lewat middleware pada route (web.php). */

    public function index(): View
    {
        $items = Keranjang::query()
            ->with('ukuran.produk')
            ->where('id_user', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        foreach ($items as $it) {
            $it->subtotal = $it->jumlah * (int) $it->ukuran->produk->harga;
        }

        $total = $items->where('dipilih', true)->sum('subtotal');
        $selectedCount = $items->where('dipilih', true)->sum('jumlah');

        return view('keranjang.index', compact('items', 'total', 'selectedCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_ukuran' => ['required', 'uuid'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $ukuran = UkuranStok::find($data['id_ukuran']);

        if (! $ukuran || ! $ukuran->aktif || $ukuran->stok <= 0) {
            return back()->with('error', 'Varian ini sedang tidak tersedia.');
        }

        $jumlah = min((int) $data['jumlah'], (int) $ukuran->stok);

        $item = Keranjang::firstOrNew([
            'id_user' => auth()->id(),
            'id_ukuran' => $ukuran->id_ukuran,
        ]);

        $item->jumlah = min((int) $item->jumlah + $jumlah, (int) $ukuran->stok);
        $item->dipilih = true;
        $item->save();

        if ($request->input('mode') === 'beli') {
            return redirect()->route('keranjang.index')
                ->with('success', 'Produk ditambahkan. Lanjutkan ke checkout (tahap berikutnya).');
        }

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $keranjang): RedirectResponse
    {
        $item = Keranjang::where('id_user', auth()->id())->findOrFail($keranjang);

        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $stok = (int) ($item->ukuran->stok ?? 0);
        $item->jumlah = min((int) $data['jumlah'], max($stok, 1));
        $item->save();

        return back();
    }

    public function togglePilih(string $keranjang): RedirectResponse
    {
        $item = Keranjang::where('id_user', auth()->id())->findOrFail($keranjang);
        $item->dipilih = ! $item->dipilih;
        $item->save();

        return back();
    }

    public function destroy(string $keranjang): RedirectResponse
    {
        Keranjang::where('id_user', auth()->id())
            ->where('id_keranjang', $keranjang)
            ->delete();

        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}
