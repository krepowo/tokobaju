<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    private const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    public function index(Request $request): View
    {
        $periode = $request->query('periode', 'bulanan');
        $periode = in_array($periode, ['harian', 'bulanan', 'tahunan'], true) ? $periode : 'bulanan';

        $tanggal = $request->query('tanggal', now()->toDateString());
        $bulan = (string) $request->query('bulan', now()->format('Y-m'));
        $tahun = (int) $request->query('tahun', now()->year);

        [$mulai, $selesai, $label] = $this->rentang($periode, $tanggal, $bulan, $tahun);

        $pesanan = Pesanan::terbayar()
            ->with(['pelanggan', 'invoice', 'pembayaran'])
            ->whereBetween('tanggal_pesanan', [$mulai, $selesai])
            ->orderBy('tanggal_pesanan')
            ->get();

        $baris = $this->baris($periode, $pesanan, $mulai, $selesai);

        return view('admin.laporan.index', [
            'periode' => $periode,
            'label' => $label,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'baris' => $baris,
            'pesanan' => $pesanan,
            'totalOmzet' => (float) $pesanan->sum('total'),
            'jumlahPesanan' => $pesanan->count(),
            'rataRata' => $pesanan->count() > 0 ? (float) $pesanan->avg('total') : 0.0,
            'tahunTersedia' => Pesanan::query()->selectRaw('YEAR(tanggal_pesanan) as th')->distinct()->orderByDesc('th')->pluck('th'),
        ]);
    }

    public function cetak(Request $request): View
    {
        $data = $this->index($request)->getData();

        return view('admin.laporan.cetak', $data);
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon, 2: string}
     */
    private function rentang(string $periode, string $tanggal, string $bulan, int $tahun): array
    {
        if ($periode === 'harian') {
            $hari = \Illuminate\Support\Carbon::parse($tanggal);

            return [$hari->copy()->startOfDay(), $hari->copy()->endOfDay(), $hari->translatedFormat('d F Y')];
        }

        if ($periode === 'tahunan') {
            $awal = \Illuminate\Support\Carbon::create($tahun, 1, 1)->startOfDay();

            return [$awal, $awal->copy()->endOfYear()->endOfDay(), 'Tahun '.$tahun];
        }

        $awal = \Illuminate\Support\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        return [$awal, $awal->copy()->endOfMonth()->endOfDay(), self::BULAN[(int) $awal->month].' '.$awal->year];
    }

    /**
     * @return list<array{label: string, jumlah: int, total: float}>
     */
    private function baris(string $periode, $pesanan, $mulai, $selesai): array
    {
        $baris = [];

        if ($periode === 'harian') {
            $perHari = $pesanan->groupBy(fn ($p) => $p->tanggal_pesanan->toDateString());

            foreach ($perHari as $tanggal => $items) {
                $baris[] = [
                    'label' => \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('d M Y'),
                    'jumlah' => $items->count(),
                    'total' => (float) $items->sum('total'),
                ];
            }

            return $baris;
        }

        if ($periode === 'tahunan') {
            $perBulan = $pesanan->groupBy(fn ($p) => (int) $p->tanggal_pesanan->month);

            foreach ($perBulan->sortKeys() as $bulan => $items) {
                $baris[] = [
                    'label' => self::BULAN[$bulan].' '.$mulai->year,
                    'jumlah' => $items->count(),
                    'total' => (float) $items->sum('total'),
                ];
            }

            return $baris;
        }

        $perHari = $pesanan->groupBy(fn ($p) => $p->tanggal_pesanan->toDateString());

        foreach ($perHari->sortKeys() as $tanggal => $items) {
            $baris[] = [
                'label' => \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('d M Y'),
                'jumlah' => $items->count(),
                'total' => (float) $items->sum('total'),
            ];
        }

        return $baris;
    }
}
