<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\UkuranStok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Kelola stok, ukuran & warna (mockup Figma 3.3.1–3.3.3).
 * Satu baris `ukuran_stok` = satu kombinasi warna × ukuran.
 */
class StokController extends Controller
{
    /** Ambang total stok dianggap menipis (pcs). */
    public const AMBANG_MENIPIS = 10;

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));
        $idKategori = $request->query('kategori');

        $daftarProduk = Produk::query()
            ->with(['kategori', 'varianUkuran'])
            ->when($cari !== '', fn ($query) => $query->where('nama_produk', 'like', "%{$cari}%"))
            ->when($idKategori, fn ($query) => $query->where('id_kategori', $idKategori))
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        foreach ($daftarProduk as $produk) {
            // Total & warna dihitung dari SEMUA varian (bukan hanya aktif),
            // supaya stok yang tersimpan walau varian nonaktif tetap terlihat.
            $produk->total_stok = (int) $produk->varianUkuran->sum('stok');
            $produk->jumlah_warna = $produk->varianUkuran->pluck('warna')->unique()->count();
            $produk->daftar_warna = self::urutkan($produk->varianUkuran->pluck('warna')->unique()->all());
            $produk->daftar_ukuran = self::urutkan($produk->varianUkuran->pluck('ukuran')->unique()->all());
        }

        return view('admin.stok.index', [
            'daftarProduk' => $daftarProduk,
            'kategoriList' => Kategori::withCount('produk')->orderBy('nama_kategori')->get(),
            'cari' => $cari,
            'idKategori' => $idKategori,
            'ambang' => self::AMBANG_MENIPIS,
        ]);
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $data = $request->validate([
            'warna' => ['required', 'array', 'min:1'],
            'warna.*' => ['required', 'string', 'max:30', 'distinct'],
            'ukuran' => ['required', 'array', 'min:1'],
            'ukuran.*' => ['required', 'string', 'max:10', 'distinct'],
            'stok' => ['required', 'array'],
            'stok.*.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'aktif' => ['nullable', 'array'],
            'aktif.*' => ['nullable', 'in:0,1'],
        ], [
            'warna.required' => 'Minimal satu warna harus ada.',
            'warna.*.distinct' => 'Nama warna tidak boleh dobel.',
            'ukuran.required' => 'Minimal satu ukuran harus ada.',
            'ukuran.*.distinct' => 'Nama ukuran tidak boleh dobel.',
            'stok.*.*.integer' => 'Stok harus berupa angka bulat.',
            'stok.*.*.min' => 'Stok tidak boleh negatif.',
        ]);

        $warna = array_values(array_unique(array_map('trim', $data['warna'])));
        $ukuran = array_values(array_unique(array_map('trim', $data['ukuran'])));

        // Varian yang sudah pernah dipesan tidak boleh dihapus (FK detail_pesanan).
        $terpakai = DB::table('detail_pesanan')->distinct()->pluck('id_ukuran')->flip();

        $dihapus = 0;
        $dinonaktifkan = 0;

        DB::transaction(function () use ($produk, $data, $warna, $ukuran, $terpakai, &$dihapus, &$dinonaktifkan) {
            $tersisa = $produk->varianUkuran()->get()->keyBy(fn ($varian) => $varian->warna.'|'.$varian->ukuran);

            foreach ($warna as $namaWarna) {
                foreach ($ukuran as $namaUkuran) {
                    $kunci = $namaWarna.'|'.$namaUkuran;
                    $nilaiStok = (int) ($data['stok'][$namaWarna][$namaUkuran] ?? 0);
                    $aktif = ((string) ($data['aktif'][$namaUkuran] ?? '1')) === '1';

                    if ($tersisa->has($kunci)) {
                        $tersisa[$kunci]->update(['stok' => $nilaiStok, 'aktif' => $aktif]);
                        $tersisa->forget($kunci);
                    } else {
                        UkuranStok::create([
                            'id_produk' => $produk->id_produk,
                            'warna' => $namaWarna,
                            'ukuran' => $namaUkuran,
                            'stok' => $nilaiStok,
                            'aktif' => $aktif,
                        ]);
                    }
                }
            }

            // Sisa = varian yang tidak ada lagi di matriks.
            foreach ($tersisa as $varian) {
                if (isset($terpakai[$varian->id_ukuran])) {
                    $varian->update(['aktif' => false, 'stok' => 0]);
                    $dinonaktifkan++;
                } else {
                    $varian->delete();
                    $dihapus++;
                }
            }
        });

        $pesan = 'Stok "'.$produk->nama_produk.'" disimpan ('.(count($warna) * count($ukuran)).' varian).';

        if ($dihapus > 0) {
            $pesan .= ' '.$dihapus.' varian dihapus.';
        }

        if ($dinonaktifkan > 0) {
            $pesan .= ' '.$dinonaktifkan.' varian dinonaktifkan karena sudah pernah dipesan.';
        }

        return redirect()
            ->route('admin.stok.index')
            ->with('success', $pesan);
    }

    /**
     * Urutkan ukuran: angka (39, 40, 41) bila semuanya numerik,
     * selain itu urutan huruf baku S → XXL.
     *
     * @param  array<int, string>  $daftar
     * @return array<int, string>
     */
    public static function urutkan(array $daftar): array
    {
        $daftar = array_values(array_filter($daftar, fn ($nilai) => $nilai !== null && $nilai !== ''));

        $semuaAngka = $daftar !== [] && count(array_filter($daftar, 'is_numeric')) === count($daftar);

        if ($semuaAngka) {
            sort($daftar, SORT_NUMERIC);

            return $daftar;
        }

        $baku = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'ALL SIZE'];

        usort($daftar, function ($a, $b) use ($baku) {
            $posisiA = array_search(mb_strtoupper($a), $baku, true);
            $posisiB = array_search(mb_strtoupper($b), $baku, true);

            $posisiA = $posisiA === false ? 99 : $posisiA;
            $posisiB = $posisiB === false ? 99 : $posisiB;

            return $posisiA <=> $posisiB ?: strcmp($a, $b);
        });

        return $daftar;
    }
}
