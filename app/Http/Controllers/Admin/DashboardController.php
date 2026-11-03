<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\UkuranStok;
use Illuminate\View\View;

/**
 * Dashboard penjual (mockup Figma 3.1).
 * Semua angka dihitung dari tabel yang sudah ada — tanpa tabel baru.
 */
class DashboardController extends Controller
{
    /** Ambang satu varian dianggap "stok menipis" (pcs). */
    private const AMBANG_STOK_MENIPIS = 10;

    /** Singkatan bulan Indonesia untuk label grafik. */
    private const BULAN_SINGKAT = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function index(): View
    {
        $awalBulan = now()->startOfMonth();

        // Kartu 1 — omzet bulan berjalan dari pesanan yang sudah selesai.
        $omzetBulanIni = (float) Pesanan::query()
            ->where('status', 'selesai')
            ->where('tanggal_pesanan', '>=', $awalBulan)
            ->sum('total');

        $jumlahPesananBulanIni = Pesanan::query()
            ->where('status', 'selesai')
            ->where('tanggal_pesanan', '>=', $awalBulan)
            ->count();

        // Kartu 2 & 3 — antrean kerja penjual.
        $perluDiverifikasi = Pesanan::query()->where('status', 'menunggu_verifikasi')->count();
        $perluDikirim = Pesanan::query()->where('status', 'diproses')->count();

        // Kartu 4 — varian aktif dengan stok tipis.
        $stokMenipis = UkuranStok::query()
            ->where('aktif', true)
            ->where('stok', '<=', self::AMBANG_STOK_MENIPIS)
            ->count();

        // Grafik — omzet 7 hari terakhir, termasuk hari tanpa transaksi.
        $omzetPerHari = Pesanan::query()
            ->where('status', 'selesai')
            ->where('tanggal_pesanan', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(tanggal_pesanan) AS tanggal, SUM(total) AS omzet')
            ->groupBy('tanggal')
            ->pluck('omzet', 'tanggal');

        $grafik = collect(range(6, 0))->map(function (int $mundur) use ($omzetPerHari) {
            $hari = now()->subDays($mundur);

            return [
                'tanggal' => $hari->toDateString(),
                'label' => $hari->day.' '.self::BULAN_SINGKAT[(int) $hari->month],
                'omzet' => (float) ($omzetPerHari[$hari->toDateString()] ?? 0),
            ];
        });

        // Tabel — 5 pesanan terbaru.
        $pesananTerbaru = Pesanan::query()
            ->with(['user', 'invoice'])
            ->orderByDesc('tanggal_pesanan')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'omzetBulanIni' => $omzetBulanIni,
            'jumlahPesananBulanIni' => $jumlahPesananBulanIni,
            'perluDiverifikasi' => $perluDiverifikasi,
            'perluDikirim' => $perluDikirim,
            'stokMenipis' => $stokMenipis,
            'ambangStokMenipis' => self::AMBANG_STOK_MENIPIS,
            'grafik' => $grafik,
            'totalGrafik' => $grafik->sum('omzet'),
            'pesananTerbaru' => $pesananTerbaru,
        ]);
    }
}
