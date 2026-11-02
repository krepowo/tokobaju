<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Kelola produk (mockup Figma 3.2 — tab Produk).
 * Tanpa perubahan skema: gambar disimpan sebagai path relatif di kolom produk.gambar.
 */
class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));
        $idKategori = $request->query('kategori');

        $daftarProduk = Produk::query()
            ->with('kategori')
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($sub) use ($cari) {
                    $sub->where('nama_produk', 'like', "%{$cari}%")
                        ->orWhere('deskripsi', 'like', "%{$cari}%");
                });
            })
            ->when($idKategori, fn ($query) => $query->where('id_kategori', $idKategori))
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        return view('admin.produk.index', [
            'daftarProduk' => $daftarProduk,
            'kategoriList' => Kategori::withCount('produk')->orderBy('nama_kategori')->get(),
            'cari' => $cari,
            'idKategori' => $idKategori,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        $data['id_penjual'] = auth()->id();
        $data['gambar'] = $this->simpanGambar($request);

        Produk::create($data);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk "'.$data['nama_produk'].'" berhasil ditambahkan.');
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $data = $request->validate($this->aturan(false), $this->pesan());

        $gambarBaru = $this->simpanGambar($request);

        if ($gambarBaru !== null) {
            $this->hapusGambarLama($produk->gambar);
            $data['gambar'] = $gambarBaru;
        } else {
            unset($data['gambar']);
        }

        $produk->update($data);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk "'.$data['nama_produk'].'" berhasil diperbarui.');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        $nama = $produk->nama_produk;

        // Produk yang sudah pernah dipesan tidak boleh dihapus (FK detail_pesanan → ukuran_stok).
        $sudahDipesan = DB::table('detail_pesanan')
            ->whereIn('id_ukuran', $produk->varianUkuran()->pluck('id_ukuran'))
            ->exists();

        if ($sudahDipesan) {
            return redirect()
                ->route('admin.produk.index')
                ->with('error', 'Produk "'.$nama.'" tidak dapat dihapus karena sudah ada di riwayat pesanan.');
        }

        $this->hapusGambarLama($produk->gambar);
        $produk->delete();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk "'.$nama.'" berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function aturan(bool $wajibGambar = true): array
    {
        return [
            'nama_produk' => ['required', 'string', 'max:150'],
            'id_kategori' => ['required', 'uuid', 'exists:kategori,id_kategori'],
            'harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'spesifikasi_ukuran' => ['nullable', 'string', 'max:2000'],
            'gambar' => [
                $wajibGambar ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function pesan(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'nama_produk.max' => 'Nama produk maksimal 150 karakter.',
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'id_kategori.exists' => 'Kategori yang dipilih tidak valid.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'gambar.required' => 'Gambar produk wajib diunggah.',
            'gambar.image' => 'Berkas gambar tidak valid.',
            'gambar.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }

    private function simpanGambar(Request $request): ?string
    {
        if (! $request->hasFile('gambar')) {
            return null;
        }

        $berkas = $request->file('gambar');
        $dasar = Str::slug(pathinfo($berkas->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'produk';
        $nama = $dasar.'-'.Str::lower(Str::random(6)).'.'.$berkas->getClientOriginalExtension();

        $berkas->move(public_path('images/uploads'), $nama);

        return 'uploads/'.$nama;
    }

    private function hapusGambarLama(?string $gambar): void
    {
        if ($gambar && str_starts_with($gambar, 'uploads/')) {
            $path = public_path('images/'.$gambar);

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
