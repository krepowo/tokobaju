<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\UkuranStok;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private const BULAN = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function index(): View
    {
        $omzetBulanIni = (float) Pesanan::terbayar()
            ->whereBetween('tanggal_pesanan', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $perluDiverifikasi = Pesanan::where('status', Pesanan::MENUNGGU_VERIFIKASI)->count();
        $perluDikirim = Pesanan::where('status', Pesanan::DIPROSES)->count();
        $stokMenipis = UkuranStok::where('aktif', true)->where('stok', '<=', 5)->count();

        // Omzet 7 hari terakhir (hanya pesanan yang pembayarannya disetujui).
        $mulai = now()->subDays(6)->startOfDay();
        $mentah = Pesanan::terbayar()
            ->where('tanggal_pesanan', '>=', $mulai)
            ->selectRaw('DATE(tanggal_pesanan) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $chart = [];
        for ($i = 0; $i < 7; $i++) {
            $hari = $mulai->copy()->addDays($i);
            $kunci = $hari->toDateString();
            $chart[] = [
                'label' => $hari->day.' '.self::BULAN[(int) $hari->month],
                'value' => (float) ($mentah[$kunci] ?? 0),
            ];
        }

        $maks = max(array_column($chart, 'value') ?: [0]);
        $adaOmzet = $maks > 0;

        // Skala minimum 100rb agar sumbu Y tetap terbaca saat belum ada penjualan.
        $chartMax = max($maks, 100000);

        $pesananTerbaru = Pesanan::with(['pelanggan', 'invoice'])
            ->latest('tanggal_pesanan')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'omzetBulanIni' => $omzetBulanIni,
            'perluDiverifikasi' => $perluDiverifikasi,
            'perluDikirim' => $perluDikirim,
            'stokMenipis' => $stokMenipis,
            'chart' => $chart,
            'chartMax' => $chartMax,
            'adaOmzet' => $adaOmzet,
            'pesananTerbaru' => $pesananTerbaru,
            'totalProduk' => DB::table('produk')->count(),
            'totalVarian' => DB::table('ukuran_stok')->count(),
        ]);
    }
}
