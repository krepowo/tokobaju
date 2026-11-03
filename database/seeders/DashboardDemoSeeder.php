<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Penambal data demo supaya Dashboard penjual ada isinya.
 *
 * Sifatnya ADDITIF — tidak menghapus atau mengubah pesanan yang sudah ada:
 *  1. Melengkapi invoice/pembayaran/pengiriman untuk pesanan lama yang bolong
 *     (26 pesanan "selesai" sebelumnya tidak punya invoice & pembayaran).
 *  2. Menambah beberapa pesanan selesai di 7 hari terakhir supaya grafik omzet
 *     dan kartu "Omzet Bulan Ini" terisi. Hari yang sudah punya pesanan selesai
 *     dilewati, jadi seeder ini aman dijalankan berulang.
 */
class DashboardDemoSeeder extends Seeder
{
    private string $idJasa = '';

    private string $idPenjual = '';

    private float $tarifJawa = 15000;

    /** @var array<int, string> */
    private array $pelanggan = [];

    private $varian;

    public function run(): void
    {
        $jasa = DB::table('jasa_pengiriman')->where('nama_jasa', 'JNE')->first();

        if (! $jasa) {
            $this->command?->warn('Jasa pengiriman JNE belum ada — seeder dilewati.');

            return;
        }

        $this->idJasa = $jasa->id_jasa;
        $this->idPenjual = (string) DB::table('users')->where('role', 'penjual')->value('id_user');
        $this->tarifJawa = (float) (DB::table('tarif_pengiriman')
            ->where('id_jasa', $jasa->id_jasa)
            ->where('pulau_tujuan', 'Jawa')
            ->value('tarif') ?? 15000);

        $this->pelanggan = DB::table('users')->where('role', 'pelanggan')->pluck('id_user')->all();
        $this->varian = DB::table('ukuran_stok')
            ->join('produk', 'produk.id_produk', '=', 'ukuran_stok.id_produk')
            ->where('ukuran_stok.aktif', true)
            ->where('ukuran_stok.stok', '>=', 6)
            ->select('ukuran_stok.*', 'produk.harga', 'produk.nama_produk')
            ->get();

        if ($this->pelanggan === [] || $this->varian->isEmpty()) {
            $this->command?->warn('Data pelanggan/varian belum cukup — seeder dilewati.');

            return;
        }

        $lengkap = $this->lengkapiPesananLama();
        $baru = $this->buatPesananBaru();

        $this->command?->info("Invoice/pembayaran dilengkapi: {$lengkap} pesanan.");
        $this->command?->info("Pesanan demo baru dibuat: {$baru} pesanan.");
    }

    /**
     * Lengkapi dokumen yang bolong pada pesanan yang sudah ada.
     */
    private function lengkapiPesananLama(): int
    {
        $daftar = DB::table('pesanan')
            ->whereIn('status', ['selesai', 'dikirim', 'diproses', 'menunggu_verifikasi'])
            ->orderBy('tanggal_pesanan')
            ->get();

        $jumlah = 0;

        foreach ($daftar as $pesanan) {
            $adaInvoice = DB::table('invoice')->where('id_pesanan', $pesanan->id_pesanan)->exists();

            if (! $adaInvoice) {
                $this->buatInvoice($pesanan->id_pesanan, (float) $pesanan->total, $pesanan->tanggal_pesanan);
            }

            if (in_array($pesanan->status, ['selesai', 'dikirim'], true)
                && ! DB::table('pembayaran')->where('id_pesanan', $pesanan->id_pesanan)->exists()) {
                $this->buatPembayaran($pesanan->id_pesanan, (float) $pesanan->total, $pesanan->tanggal_pesanan);
            }

            if (in_array($pesanan->status, ['selesai', 'dikirim'], true)
                && ! DB::table('pengiriman')->where('id_pesanan', $pesanan->id_pesanan)->exists()) {
                $this->buatPengiriman($pesanan->id_pesanan, $pesanan->tanggal_pesanan);
            }

            $jumlah++;
        }

        return $jumlah;
    }

    /**
     * Tambah pesanan selesai di 7 hari terakhir untuk mengisi grafik dashboard.
     */
    private function buatPesananBaru(): int
    {
        mt_srand(20260101);
        $dibuat = 0;

        for ($mundur = 6; $mundur >= 0; $mundur--) {
            $tanggal = now()->subDays($mundur)->setTime(9, 30, 0);

            $sudahAda = DB::table('pesanan')
                ->whereDate('tanggal_pesanan', $tanggal->toDateString())
                ->where('status', 'selesai')
                ->exists();

            if ($sudahAda) {
                continue;
            }

            $jumlahPesanan = $mundur % 2 === 0 ? 2 : 1;

            for ($n = 0; $n < $jumlahPesanan; $n++) {
                $this->buatSatuPesanan($tanggal->copy()->addHours($n * 3));
                $dibuat++;
            }
        }

        return $dibuat;
    }

    private function buatSatuPesanan($tanggal): void
    {
        $idUser = $this->pelanggan[array_rand($this->pelanggan)];
        $idPesanan = (string) Str::uuid();

        // 1–2 varian per pesanan, jumlah 1–2 pcs.
        $jumlahItem = mt_rand(1, 2);
        $item = [];

        foreach ($this->varian->random($jumlahItem) as $varian) {
            $qty = mt_rand(1, 2);
            $stok = (int) DB::table('ukuran_stok')->where('id_ukuran', $varian->id_ukuran)->value('stok');

            if ($stok < $qty) {
                continue;
            }

            $item[] = [
                'id_ukuran' => $varian->id_ukuran,
                'jumlah' => $qty,
                'harga' => (float) $varian->harga,
            ];
        }

        if ($item === []) {
            return;
        }

        $subtotal = array_sum(array_map(fn ($i) => $i['harga'] * $i['jumlah'], $item));
        $ongkir = $this->tarifJawa;
        $total = $subtotal + $ongkir;

        DB::transaction(function () use ($idUser, $idPesanan, $tanggal, $item, $ongkir, $total) {
            DB::table('pesanan')->insert([
                'id_pesanan' => $idPesanan,
                'id_user' => $idUser,
                'id_jasa' => $this->idJasa,
                'tanggal_pesanan' => $tanggal,
                'alamat_tujuan' => $this->alamatUntuk($idUser),
                'pulau_tujuan' => 'Jawa',
                'ongkir' => $ongkir,
                'total' => $total,
                'status' => 'selesai',
                'batas_bayar' => $tanggal->copy()->addDay(),
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            foreach ($item as $baris) {
                DB::table('detail_pesanan')->insert([
                    'id_detail' => (string) Str::uuid(),
                    'id_pesanan' => $idPesanan,
                    'id_ukuran' => $baris['id_ukuran'],
                    'jumlah' => $baris['jumlah'],
                    'harga_satuan' => $baris['harga'],
                    'subtotal' => $baris['harga'] * $baris['jumlah'],
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                DB::table('ukuran_stok')
                    ->where('id_ukuran', $baris['id_ukuran'])
                    ->decrement('stok', $baris['jumlah']);
            }

            $this->buatInvoice($idPesanan, $total, $tanggal);
            $this->buatPembayaran($idPesanan, $total, $tanggal);
            $this->buatPengiriman($idPesanan, $tanggal);
        });
    }

    private function alamatUntuk(string $idUser): string
    {
        $u = DB::table('users')->where('id_user', $idUser)->first();

        $nama = $u->nama ?? 'Pelanggan';
        $telepon = $u->no_telepon ?? '081200000000';

        return implode(', ', array_filter([
            $nama.' ('.$telepon.')',
            $u->detail_alamat ?: 'Jl. Melati No. 12',
            $u->kelurahan_desa ?: 'Sukamaju',
            $u->kecamatan ?: 'Cilandak',
            $u->kota_kabupaten ?: 'Jakarta Selatan',
            ($u->provinsi ?: 'DKI Jakarta').' '.($u->kode_pos ?: '12190'),
        ]));
    }

    private function buatInvoice(string $idPesanan, float $total, $tanggal): void
    {
        $tanggal = Carbon::parse($tanggal);
        $prefix = 'INV-'.$tanggal->format('Ymd').'-';
        $urut = DB::table('invoice')->where('no_invoice', 'like', $prefix.'%')->count() + 1;

        do {
            $nomor = $prefix.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
            $urut++;
        } while (DB::table('invoice')->where('no_invoice', $nomor)->exists());

        DB::table('invoice')->insert([
            'id_invoice' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'no_invoice' => $nomor,
            'tanggal_terbit' => $tanggal,
            'total_tagihan' => $total,
            'rekening_tujuan' => 'BCA 1234 5678 90 a.n. Gavin Fashion Store',
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);
    }

    private function buatPembayaran(string $idPesanan, float $total, $tanggal): void
    {
        $tanggal = Carbon::parse($tanggal);

        DB::table('pembayaran')->insert([
            'id_pembayaran' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'diverifikasi_oleh' => $this->idPenjual ?: null,
            'bukti_transfer' => 'images/bukti-transfer-demo.jpg',
            'tanggal_upload' => $tanggal->copy()->addHours(2),
            'status_verifikasi' => 'disetujui',
            'alasan_tolak' => null,
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);
    }

    private function buatPengiriman(string $idPesanan, $tanggal): void
    {
        $tanggal = Carbon::parse($tanggal);

        DB::table('pengiriman')->insert([
            'id_pengiriman' => (string) Str::uuid(),
            'id_pesanan' => $idPesanan,
            'no_resi' => 'JNE'.mt_rand(1000000000, 9999999999),
            'tanggal_kirim' => $tanggal->copy()->addHours(6),
            'catatan' => 'Dijemput kurir pukul 14.00 WIB',
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);
    }
}
