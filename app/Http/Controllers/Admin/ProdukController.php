<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $kategori = $request->query('kategori');

        $produk = Produk::query()
            ->with('kategori')
            ->withCount('ukuranStok')
            ->withSum('ukuranStok as total_stok', 'stok')
            ->when($q !== '', fn ($sql) => $sql->where('nama_produk', 'like', '%'.$q.'%'))
            ->when($kategori, fn ($sql) => $sql->where('id_kategori', $kategori))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalKategori = Kategori::count();

        return view('admin.produk.index', [
            'produk' => $produk,
            'kategori' => Kategori::orderBy('nama_kategori')->get(),
            'q' => $q,
            'kategoriTerpilih' => $kategori,
            'totalKategori' => $totalKategori,
        ]);
    }

    public function create(): View
    {
        return view('admin.produk.form', [
            'produk' => new Produk(['harga' => 0]),
            'kategori' => Kategori::orderBy('nama_kategori')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['id_penjual'] = $request->user()->id_user;

        Produk::create($data);

        return redirect()->route('admin.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk): View
    {
        return view('admin.produk.form', [
            'produk' => $produk,
            'kategori' => Kategori::orderBy('nama_kategori')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $produk->update($this->validated($request, $produk));

        return redirect()->route('admin.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        if ($produk->ukuranStok()->whereHas('keranjangItem')->exists()
            || \App\Models\DetailPesanan::whereIn('id_ukuran', $produk->ukuranStok()->pluck('id_ukuran'))->exists()) {
            return back()->withErrors(['produk' => 'Produk tidak dapat dihapus karena sudah dipesan pelanggan.']);
        }

        $produk->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Produk $produk = null): array
    {
        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:150'],
            'id_kategori' => ['required', 'exists:kategori,id_kategori'],
            'harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'deskripsi' => ['required', 'string'],
            'spesifikasi_ukuran' => ['nullable', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'gambar.image' => 'Gambar harus berupa berkas gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, JPEG, atau PNG.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        if ($request->hasFile('gambar')) {
            $nama = Str::slug($data['nama_produk']).'-'.Str::random(6).'.'.$request->file('gambar')->getClientOriginalExtension();
            $request->file('gambar')->move(public_path('images/produk'), $nama);
            $data['gambar'] = 'images/produk/'.$nama;
        } elseif ($produk !== null) {
            unset($data['gambar']);
        } else {
            // Kolom gambar wajib terisi: pakai placeholder bila penjual belum mengunggah berkas.
            $data['gambar'] = 'images/produk/placeholder.png';
        }

        return $data;
    }
}
