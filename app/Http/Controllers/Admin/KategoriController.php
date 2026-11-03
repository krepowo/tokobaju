<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola kategori (mockup Figma 3.2.1).
 */
class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));

        $daftarKategori = Kategori::query()
            ->withCount('produk')
            ->when($cari !== '', fn ($query) => $query->where('nama_kategori', 'like', "%{$cari}%"))
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kategori.index', [
            'daftarKategori' => $daftarKategori,
            'cari' => $cari,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', 'unique:kategori,nama_kategori'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 50 karakter.',
            'nama_kategori.unique' => 'Kategori tersebut sudah ada.',
        ]);

        Kategori::create($data);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori "'.$data['nama_kategori'].'" berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => [
                'required', 'string', 'max:50',
                Rule::unique('kategori', 'nama_kategori')->ignore($kategori->id_kategori, 'id_kategori'),
            ],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Kategori tersebut sudah ada.',
        ]);

        $kategori->update($data);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil diubah menjadi "'.$data['nama_kategori'].'".');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $nama = $kategori->nama_kategori;

        if ($kategori->produk()->exists()) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', 'Kategori "'.$nama.'" masih dipakai oleh produk, jadi tidak bisa dihapus.');
        }

        $kategori->delete();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori "'.$nama.'" berhasil dihapus.');
    }
}
