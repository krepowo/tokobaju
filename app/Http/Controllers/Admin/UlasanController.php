<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Produk;
use App\Models\Ulasan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UlasanController extends Controller
{
    /**
     * Halaman "Ulasan Pembeli" (F-18): ringkasan rating per produk,
     * sebaran bintang, filter bintang, pencarian, dan daftar ulasan.
     */
    public function index(Request $request): View
    {
        $daftarProduk = Produk::query()
            ->has('ulasan')
            ->withCount('ulasan')
            ->orderByDesc('ulasan_count')
            ->orderBy('nama_produk')
            ->get();

        $produkId = (string) $request->query('produk', '');
        $produk = $produkId !== ''
            ? $daftarProduk->firstWhere('id_produk', $produkId)
            : $daftarProduk->first();

        $bintang = (int) $request->query('bintang', 0);
        $q = trim((string) $request->query('q', ''));

        if (! $produk) {
            return view('admin.ulasan.index', [
                'daftarProduk' => $daftarProduk,
                'produk' => null,
                'ulasan' => null,
                'ringkasan' => null,
                'bintang' => $bintang,
                'q' => $q,
            ]);
        }

        $dasar = Ulasan::query()->where('id_produk', $produk->id_produk);

        $jumlah = (clone $dasar)->count();
        $rataRata = round((float) (clone $dasar)->avg('rating'), 1);
        $sebaran = (clone $dasar)
            ->selectRaw('rating, COUNT(*) as jumlah')
            ->groupBy('rating')
            ->pluck('jumlah', 'rating');

        $puas = $jumlah > 0
            ? (int) round(((int) ($sebaran[5] ?? 0) + (int) ($sebaran[4] ?? 0)) / $jumlah * 100)
            : 0;

        $ulasan = (clone $dasar)
            ->with(['user', 'detailPesanan.ukuranStok'])
            ->when($bintang >= 1 && $bintang <= 5, fn ($sql) => $sql->where('rating', $bintang))
            ->when($q !== '', fn ($sql) => $sql->where(function ($sub) use ($q) {
                $sub->where('komentar', 'like', '%'.$q.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', '%'.$q.'%'));
            }))
            ->latest('tanggal')
            ->paginate(5)
            ->withQueryString();

        $terjual = (int) DetailPesanan::query()
            ->whereHas('ukuranStok', fn ($sql) => $sql->where('id_produk', $produk->id_produk))
            ->sum('jumlah');

        return view('admin.ulasan.index', [
            'daftarProduk' => $daftarProduk,
            'produk' => $produk,
            'ulasan' => $ulasan,
            'bintang' => $bintang,
            'q' => $q,
            'ringkasan' => [
                'jumlah' => $jumlah,
                'rata_rata' => $rataRata,
                'sebaran' => $sebaran,
                'puas' => $puas,
                'terjual' => $terjual,
            ],
        ]);
    }

    /**
     * Ekspor seluruh ulasan produk terpilih ke CSV (untuk laporan).
     */
    public function ekspor(Request $request): StreamedResponse
    {
        $produkId = (string) $request->query('produk', '');
        $produk = Produk::find($produkId);

        $namaBerkas = 'ulasan-'.($produk ? \Illuminate\Support\Str::slug($produk->nama_produk) : 'semua').'.csv';

        return response()->streamDownload(function () use ($produk) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Tanggal', 'Produk', 'Pelanggan', 'Rating', 'Ukuran', 'Warna', 'Komentar']);

            Ulasan::query()
                ->with(['user', 'produk', 'detailPesanan.ukuranStok'])
                ->when($produk, fn ($sql) => $sql->where('id_produk', $produk->id_produk))
                ->orderByDesc('tanggal')
                ->chunk(200, function ($baris) use ($out) {
                    foreach ($baris as $u) {
                        fputcsv($out, [
                            $u->tanggal?->format('Y-m-d H:i'),
                            $u->produk?->nama_produk,
                            $u->user?->nama,
                            $u->rating,
                            $u->detailPesanan?->ukuranStok?->ukuran,
                            $u->detailPesanan?->ukuranStok?->warna,
                            $u->komentar,
                        ]);
                    }
                });

            fclose($out);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Tautan lama /admin/ulasan/{produk} diarahkan ke halaman baru.
     */
    public function show(Produk $produk): RedirectResponse
    {
        return redirect()->route('admin.ulasan.index', ['produk' => $produk->id_produk]);
    }
}
