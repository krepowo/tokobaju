<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\UkuranStok;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $produk = Produk::query()
            ->with('kategori')
            ->withCount(['ukuranStok', 'ukuranStok as varian_habis' => fn ($sql) => $sql->where('stok', 0)])
            ->withSum('ukuranStok as total_stok', 'stok')
            ->when($q !== '', fn ($sql) => $sql->where('nama_produk', 'like', '%'.$q.'%'))
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stok.index', compact('produk', 'q'));
    }

    public function kelola(Produk $produk): View
    {
        return view('admin.stok.kelola', [
            'produk' => $produk->load('kategori'),
            'varian' => $produk->ukuranStok()->orderBy('warna')->orderBy('ukuran')->get(),
        ]);
    }

    public function simpan(Request $request, Produk $produk): RedirectResponse
    {
        $data = $this->validated($request);

        $duplikat = $produk->ukuranStok()
            ->where('ukuran', $data['ukuran'])
            ->where('warna', $data['warna'])
            ->exists();

        if ($duplikat) {
            return back()->withErrors(['ukuran' => 'Kombinasi ukuran dan warna tersebut sudah ada.'])->withInput();
        }

        $produk->ukuranStok()->create($data + ['aktif' => true]);

        return back()->with('success', 'Varian ukuran dan stok berhasil ditambahkan.');
    }

    public function update(Request $request, Produk $produk, UkuranStok $varian): RedirectResponse
    {
        abort_unless($varian->id_produk === $produk->id_produk, 404);

        $data = $this->validated($request);
        $data['aktif'] = $request->boolean('aktif');

        $duplikat = $produk->ukuranStok()
            ->where('ukuran', $data['ukuran'])
            ->where('warna', $data['warna'])
            ->where('id_ukuran', '!=', $varian->id_ukuran)
            ->exists();

        if ($duplikat) {
            return back()->withErrors(['ukuran' => 'Kombinasi ukuran dan warna tersebut sudah ada.'])->withInput();
        }

        $varian->update($data);

        return back()->with('success', 'Varian berhasil diperbarui.');
    }

    public function hapus(Produk $produk, UkuranStok $varian): RedirectResponse
    {
        abort_unless($varian->id_produk === $produk->id_produk, 404);

        $dipakai = $varian->keranjangItem()->exists()
            || \App\Models\DetailPesanan::where('id_ukuran', $varian->id_ukuran)->exists();

        if ($dipakai) {
            return back()->withErrors(['varian' => 'Varian tidak dapat dihapus karena sudah dipesan. Nonaktifkan saja.']);
        }

        $varian->delete();

        return back()->with('success', 'Varian berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'ukuran' => ['required', 'string', 'max:10'],
            'warna' => ['required', 'string', 'max:30'],
            'stok' => ['required', 'integer', 'min:0', 'max:100000'],
        ], [
            'ukuran.required' => 'Ukuran wajib diisi.',
            'warna.required' => 'Warna wajib diisi.',
            'stok.required' => 'Jumlah stok wajib diisi.',
            'stok.integer' => 'Stok harus berupa angka bulat.',
            'stok.min' => 'Stok tidak boleh bernilai negatif.',
        ]);
    }
}
