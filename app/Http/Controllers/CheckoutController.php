<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $items = Keranjang::query()
            ->with('ukuran.produk')
            ->where('id_user', auth()->id())
            ->where('dipilih', true)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('keranjang.index')
                ->with('error', 'Pilih produk di keranjang terlebih dahulu.');
        }

        foreach ($items as $it) {
            $it->subtotal = $it->jumlah * (int) $it->ukuran->produk->harga;
        }

        $subtotal = $items->sum('subtotal');
        $provinsi = DB::table('provinsi')->orderBy('nama_provinsi')->get();
        $urutan = ['JNE' => 0, 'J&T' => 1, 'GoSend' => 2, 'GoShop' => 3];
        $jasa = DB::table('jasa_pengiriman')->get()
            ->sortBy(fn ($j) => $urutan[$j->nama_jasa] ?? 99)
            ->values();
        $tarif = DB::table('tarif_pengiriman')->get()
            ->groupBy('id_jasa')
            ->map(fn ($rows) => $rows->pluck('tarif', 'pulau_tujuan'));

        return view('checkout.index', compact('items', 'subtotal', 'provinsi', 'jasa', 'tarif'));
    }

    public function store(Request $request): RedirectResponse
    {
        $items = Keranjang::query()
            ->with('ukuran.produk')
            ->where('id_user', auth()->id())
            ->where('dipilih', true)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('keranjang.index')
                ->with('error', 'Tidak ada item yang dipilih untuk checkout.');
        }

        $data = $request->validate([
            'nama_penerima' => ['required', 'string', 'max:100'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota_kabupaten' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kelurahan_desa' => ['required', 'string', 'max:100'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'detail_alamat' => ['required', 'string', 'max:500'],
            'id_jasa' => ['required', 'uuid'],
        ], [
            '*.required' => 'Lengkapi alamat pengiriman dan pilih jasa pengiriman.',
        ]);

        [$pulau, $ongkir] = $this->ongkirUntuk($data['id_jasa'], $data['provinsi']);

        // Jangan pernah memakai ongkir 0 diam-diam kalau tarif pulau belum diatur.
        if ($ongkir === null) {
            return back()->withInput()->withErrors([
                'id_jasa' => 'Ongkos kirim ke pulau '.$pulau.' belum tersedia untuk jasa yang dipilih. Silakan pilih jasa pengiriman lain.',
            ]);
        }

        $subtotal = $items->sum(fn ($it) => $it->jumlah * (int) $it->ukuran->produk->harga);
        $total = $subtotal + $ongkir;

        $alamat = implode(', ', array_filter([
            $data['nama_penerima'].' ('.$data['no_telepon'].')',
            $data['detail_alamat'],
            $data['kelurahan_desa'],
            $data['kecamatan'],
            $data['kota_kabupaten'],
            $data['provinsi'].' '.$data['kode_pos'],
        ]));

        $idPesanan = (string) Str::uuid();

        DB::transaction(function () use ($items, $data, $idPesanan, $pulau, $ongkir, $total, $alamat) {
            $prefix = 'INV-' . now()->format('Ymd') . '-';
            $lastInvoice = DB::table('invoice')
                ->where('no_invoice', 'LIKE', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('no_invoice')
                ->value('no_invoice');

            if ($lastInvoice && preg_match('/^INV-\d{8}-(\d{4})$/', $lastInvoice, $matches)) {
                $seq = ((int) $matches[1]) + 1;
            } else {
                $todayCount = DB::table('invoice')->where('no_invoice', 'LIKE', $prefix . '%')->count();
                $seq = $todayCount + 1;
            }

            do {
                $invoiceNo = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                $exists = DB::table('invoice')->where('no_invoice', $invoiceNo)->exists();
                if ($exists) {
                    $seq++;
                }
            } while ($exists);

            DB::table('pesanan')->insert([
                'id_pesanan' => $idPesanan,
                'id_user' => auth()->id(),
                'id_jasa' => $data['id_jasa'],
                'tanggal_pesanan' => now(),
                'alamat_tujuan' => $alamat,
                'pulau_tujuan' => $pulau,
                'ongkir' => $ongkir,
                'total' => $total,
                'status' => 'menunggu_pembayaran',
                'batas_bayar' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $it) {
                DB::table('detail_pesanan')->insert([
                    'id_detail' => (string) Str::uuid(),
                    'id_pesanan' => $idPesanan,
                    'id_ukuran' => $it->id_ukuran,
                    'jumlah' => $it->jumlah,
                    'harga_satuan' => (int) $it->ukuran->produk->harga,
                    'subtotal' => $it->jumlah * (int) $it->ukuran->produk->harga,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('ukuran_stok')->where('id_ukuran', $it->id_ukuran)->decrement('stok', $it->jumlah);
            }

            DB::table('invoice')->insert([
                'id_invoice' => (string) Str::uuid(),
                'id_pesanan' => $idPesanan,
                'no_invoice' => $invoiceNo,
                'tanggal_terbit' => now(),
                'total_tagihan' => $total,
                'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            auth()->user()->update([
                'no_telepon' => $data['no_telepon'],
                'provinsi' => $data['provinsi'],
                'kota_kabupaten' => $data['kota_kabupaten'],
                'kecamatan' => $data['kecamatan'],
                'kelurahan_desa' => $data['kelurahan_desa'],
                'kode_pos' => $data['kode_pos'],
                'detail_alamat' => $data['detail_alamat'],
            ]);

            Keranjang::where('id_user', auth()->id())->where('dipilih', true)->delete();
        });

        return redirect()->route('pesanan.show', $idPesanan)
            ->with('success', 'Pesanan berhasil dibuat. Selesaikan pembayaran sebelum batas waktu.');
    }

    /**
     * @return array{0: string, 1: int|null} [pulau, ongkir] — ongkir null bila tarif pulau belum diatur
     */
    private function ongkirUntuk(string $idJasa, string $namaProvinsi): array
    {
        $prov = DB::table('provinsi')->where('nama_provinsi', $namaProvinsi)->first();
        $pulau = $prov->pulau ?? 'Jawa';

        $tarif = DB::table('tarif_pengiriman')
            ->where('id_jasa', $idJasa)
            ->where('pulau_tujuan', $pulau)
            ->value('tarif');

        return [$pulau, $tarif === null ? null : (int) $tarif];
    }
}
