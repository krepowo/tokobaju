<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Pesanan & Invoice (mockup Figma 3.4.1 dan 3.4.2).
 */
class PesananController extends Controller
{
    /** Daftar status pesanan sesuai enum di database. */
    public const DAFTAR_STATUS = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'pembayaran_ditolak' => 'Pembayaran Ditolak',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    public function index(Request $request): View
    {
        $filter = $this->filter($request);

        return view('admin.pesanan.index', [
            'daftarPesanan' => $this->kueri($filter)->paginate(10)->withQueryString(),
            'daftarStatus' => self::DAFTAR_STATUS,
            'filter' => $filter,
            'ringkasan' => $this->ringkasan($filter),
        ]);
    }

    public function show(Pesanan $pesanan): View
    {
        $pesanan->load([
            'user',
            'jasa',
            'invoice',
            'pembayaran',
            'pengiriman',
            'detailPesanan.ukuran.produk',
        ]);

        $sisaWaktu = null;
        $persenWaktu = 0;

        if ($pesanan->status === 'menunggu_pembayaran' && $pesanan->batas_bayar) {
            $batas = Carbon::parse($pesanan->batas_bayar);
            $sisaDetik = max(0, now()->diffInSeconds($batas, false));
            $sisaWaktu = $sisaDetik > 0 ? $this->durasi($sisaDetik) : 'Kedaluwarsa';
            $persenWaktu = (int) round(min(100, $sisaDetik / 86400 * 100));
        }

        return view('admin.pesanan.show', [
            'pesanan' => $pesanan,
            'sisaWaktu' => $sisaWaktu,
            'persenWaktu' => $persenWaktu,
            'daftarStatus' => self::DAFTAR_STATUS,
        ]);
    }

    /**
     * Halaman cetak daftar pesanan (tombol "Cetak Laporan").
     */
    public function cetak(Request $request): View
    {
        $filter = $this->filter($request);

        return view('admin.pesanan.cetak', [
            'daftarPesanan' => $this->kueri($filter)->get(),
            'filter' => $filter,
            'ringkasan' => $this->ringkasan($filter),
            'daftarStatus' => self::DAFTAR_STATUS,
        ]);
    }

    /**
     * @return array{cari: string, status: string, dari: ?string, sampai: ?string}
     */
    private function filter(Request $request): array
    {
        $cari = trim((string) $request->query('cari', ''));
        $status = (string) $request->query('status', 'semua');

        $dari = $this->tanggalValid((string) $request->query('dari', ''));
        $sampai = $this->tanggalValid((string) $request->query('sampai', ''));

        // Tanpa parameter tanggal sama sekali → default bulan berjalan.
        if (! $request->has('dari') && ! $request->has('sampai')) {
            $dari = now()->startOfMonth()->toDateString();
            $sampai = now()->endOfMonth()->toDateString();
        }

        return [
            'cari' => $cari,
            'status' => isset(self::DAFTAR_STATUS[$status]) ? $status : 'semua',
            'dari' => $dari,
            'sampai' => $sampai,
        ];
    }

    private function tanggalValid(string $nilai): ?string
    {
        if ($nilai === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function kueri(array $filter)
    {
        return Pesanan::query()
            ->with(['user', 'invoice', 'jasa'])
            ->when($filter['cari'] !== '', function ($query) use ($filter) {
                $cari = $filter['cari'];

                $query->where(function ($sub) use ($cari) {
                    $sub->whereHas('invoice', fn ($invoice) => $invoice->where('no_invoice', 'like', "%{$cari}%"))
                        ->orWhereHas('user', fn ($user) => $user->where('nama', 'like', "%{$cari}%"));
                });
            })
            ->when($filter['status'] !== 'semua', fn ($query) => $query->where('status', $filter['status']))
            ->when($filter['dari'], fn ($query) => $query->whereDate('tanggal_pesanan', '>=', $filter['dari']))
            ->when($filter['sampai'], fn ($query) => $query->whereDate('tanggal_pesanan', '<=', $filter['sampai']))
            ->orderByDesc('tanggal_pesanan');
    }

    /**
     * @return array{jumlah: int, total: float, selesai: int, belum_bayar: int}
     */
    private function ringkasan(array $filter): array
    {
        /** @var Collection $baris */
        $baris = $this->kueri($filter)->get(['total', 'status']);

        return [
            'jumlah' => $baris->count(),
            'total' => (float) $baris->sum('total'),
            'selesai' => $baris->where('status', 'selesai')->count(),
            'belum_bayar' => $baris->where('status', 'menunggu_pembayaran')->count(),
        ];
    }

    private function durasi(int $detik): string
    {
        $jam = intdiv($detik, 3600);
        $menit = intdiv($detik % 3600, 60);

        if ($jam > 0) {
            return $jam.' jam '.$menit.' menit';
        }

        return $menit.' menit';
    }
}
