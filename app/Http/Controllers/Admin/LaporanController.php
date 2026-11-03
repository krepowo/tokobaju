<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    private const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    private const BULAN_PENDEK = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /**
     * Laporan omzet (F-19): KPI, tren, produk terlaris, omzet per kategori,
     * dan rincian transaksi selesai. Omzet dihitung dari pesanan selesai tanpa ongkir.
     */
    public function index(Request $request): View
    {
        $data = $this->hitung($request);

        $data['kategoriList'] = Kategori::orderBy('nama_kategori')->get();
        $data['tahunTersedia'] = Pesanan::query()
            ->where('status', Pesanan::SELESAI)
            ->selectRaw('YEAR(tanggal_pesanan) as th')
            ->distinct()
            ->orderByDesc('th')
            ->pluck('th');

        return view('admin.laporan.index', $data);
    }

    public function cetak(Request $request): View
    {
        return view('admin.laporan.cetak', $this->hitung($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function hitung(Request $request): array
    {
        $periode = (string) $request->query('periode', 'bulanan');
        $periode = in_array($periode, ['harian', 'bulanan', 'tahunan'], true) ? $periode : 'bulanan';

        $tanggal = (string) $request->query('tanggal', now()->toDateString());
        $bulan = (string) $request->query('bulan', now()->format('Y-m'));
        $tahun = (int) $request->query('tahun', now()->year);
        $kategoriId = (string) $request->query('kategori', '');
        $q = trim((string) $request->query('q', ''));

        [$mulai, $selesai, $label] = $this->rentang($periode, $tanggal, $bulan, $tahun);

        $pesanan = Pesanan::query()
            ->where('status', Pesanan::SELESAI)
            ->with([
                'pelanggan',
                'invoice',
                'detail.ukuranStok.produk.kategori',
                'pembayaranTerakhir',
            ])
            ->whereBetween('tanggal_pesanan', [$mulai, $selesai])
            ->when($kategoriId !== '', fn ($sql) => $sql->whereHas(
                'detail.ukuranStok.produk',
                fn ($p) => $p->where('id_kategori', $kategoriId)
            ))
            ->orderBy('tanggal_pesanan')
            ->get();

        // Omzet = jumlah subtotal produk (tanpa ongkos kirim).
        $omzet = 0.0;
        $pcs = 0;
        $perProduk = [];
        $perKategori = [];

        foreach ($pesanan as $p) {
            foreach ($p->detail as $d) {
                $produk = $d->ukuranStok?->produk;
                $sub = (float) $d->subtotal;
                $omzet += $sub;
                $pcs += (int) $d->jumlah;

                if (! $produk) {
                    continue;
                }

                $kunci = $produk->id_produk;
                $perProduk[$kunci] ??= [
                    'nama' => $produk->nama_produk,
                    'kategori' => $produk->kategori?->nama_kategori ?? 'Tanpa kategori',
                    'pcs' => 0,
                    'omzet' => 0.0,
                    'gambar' => $produk->gambar,
                ];
                $perProduk[$kunci]['pcs'] += (int) $d->jumlah;
                $perProduk[$kunci]['omzet'] += $sub;

                $kat = $produk->kategori?->nama_kategori ?? 'Tanpa kategori';
                $perKategori[$kat] = ($perKategori[$kat] ?? 0) + $sub;
            }
        }

        uasort($perProduk, fn ($a, $b) => $b['omzet'] <=> $a['omzet']);
        arsort($perKategori);

        $topProduk = array_slice($perProduk, 0, 5, true);
        $kontribusiTop = $omzet > 0
            ? round(array_sum(array_column($topProduk, 'omzet')) / $omzet * 100, 1)
            : 0.0;

        // Tren omzet sesuai periode.
        $tren = $this->tren($periode, $pesanan, $mulai, $selesai);

        // Rincian transaksi + pencarian invoice/nama pelanggan.
        $transaksi = $pesanan
            ->when($q !== '', function ($koleksi) use ($q) {
                return $koleksi->filter(function (Pesanan $p) use ($q) {
                    return str_contains(mb_strtolower((string) $p->invoice?->no_invoice), mb_strtolower($q))
                        || str_contains(mb_strtolower((string) $p->pelanggan?->nama), mb_strtolower($q));
                });
            })
            ->sortByDesc('tanggal_pesanan')
            ->values();

        $halaman = max(1, (int) $request->query('page', 1));
        $perHalaman = 5;
        $totalHalaman = max(1, (int) ceil($transaksi->count() / $perHalaman));
        $halaman = min($halaman, $totalHalaman);
        $potongan = $transaksi->forPage($halaman, $perHalaman);

        return [
            'periode' => $periode,
            'label' => $label,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategoriId' => $kategoriId,
            'q' => $q,
            'totalOmzet' => $omzet,
            'jumlahPesanan' => $pesanan->count(),
            'produkTerjual' => $pcs,
            'rataRata' => $pesanan->count() > 0 ? $omzet / $pesanan->count() : 0.0,
            'tren' => $tren,
            'topProduk' => $topProduk,
            'kontribusiTop' => $kontribusiTop,
            'perKategori' => $perKategori,
            'transaksi' => $potongan,
            'semuaTransaksi' => $transaksi,
            'jumlahTransaksi' => $transaksi->count(),
            'halaman' => $halaman,
            'totalHalaman' => $totalHalaman,
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function rentang(string $periode, string $tanggal, string $bulan, int $tahun): array
    {
        if ($periode === 'harian') {
            $hari = Carbon::parse($tanggal);

            return [$hari->copy()->startOfDay(), $hari->copy()->endOfDay(), $hari->translatedFormat('d F Y')];
        }

        if ($periode === 'tahunan') {
            $awal = Carbon::create($tahun, 1, 1)->startOfDay();

            return [$awal, $awal->copy()->endOfYear()->endOfDay(), 'Tahun '.$tahun];
        }

        $awal = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        return [$awal, $awal->copy()->endOfMonth()->endOfDay(), self::BULAN[(int) $awal->month].' '.$awal->year];
    }

    /**
     * Data grafik tren omzet.
     *
     * @return list<array{label: string, value: float, pesanan: int}>
     */
    private function tren(string $periode, $pesanan, Carbon $mulai, Carbon $selesai): array
    {
        $hasil = [];

        if ($periode === 'harian') {
            // Satu batang per pesanan pada hari tersebut.
            foreach ($pesanan as $p) {
                $hasil[] = [
                    'label' => $p->tanggal_pesanan->format('H:i'),
                    'value' => (float) $p->detail->sum('subtotal'),
                    'pesanan' => 1,
                ];
            }

            return $hasil;
        }

        if ($periode === 'tahunan') {
            for ($b = 1; $b <= 12; $b++) {
                $hasil[] = ['label' => self::BULAN_PENDEK[$b], 'value' => 0.0, 'pesanan' => 0];
            }

            foreach ($pesanan as $p) {
                $indeks = (int) $p->tanggal_pesanan->month - 1;
                $hasil[$indeks]['value'] += (float) $p->detail->sum('subtotal');
                $hasil[$indeks]['pesanan']++;
            }

            return $hasil;
        }

        $hari = (int) $mulai->daysInMonth;
        for ($d = 1; $d <= $hari; $d++) {
            $hasil[] = [
                'label' => str_pad((string) $d, 2, '0', STR_PAD_LEFT).' '.self::BULAN_PENDEK[(int) $mulai->month],
                'value' => 0.0,
                'pesanan' => 0,
            ];
        }

        foreach ($pesanan as $p) {
            $indeks = (int) $p->tanggal_pesanan->day - 1;
            $hasil[$indeks]['value'] += (float) $p->detail->sum('subtotal');
            $hasil[$indeks]['pesanan']++;
        }

        return $hasil;
    }
}
