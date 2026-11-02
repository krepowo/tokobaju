<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('admin.kategori.index', [
            'kategori' => Kategori::withCount('produk')->orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', 'unique:kategori,nama_kategori'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah ada.',
        ]);

        Kategori::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', 'unique:kategori,nama_kategori,'.$kategori->id_kategori.',id_kategori'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah ada.',
        ]);

        $kategori->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->produk()->exists()) {
            return back()->withErrors(['kategori' => 'Kategori tidak dapat dihapus karena masih dipakai produk.']);
        }

        $kategori->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
