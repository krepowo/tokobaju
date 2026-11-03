@php
    $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Omzet {{ $label }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        body { background: #fff; padding: 32px; }
        .lap { max-width: 820px; margin: 0 auto; font-size: 13px; color: #111827; }
        .lap__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;
                     padding-bottom: 18px; border-bottom: 2px solid #0e0f12; }
        .lap__brand { display: flex; align-items: center; gap: 12px; }
        .lap__mark { width: 40px; height: 40px; border-radius: 8px; display: grid; place-items: center; font-weight: 700; font-size: 18px;
                     background: linear-gradient(135deg, #e2b961 0%, #b8892b 50%, #7b5921 100%); color: #0a0a0a; }
        .lap__kpi { display: flex; gap: 28px; flex-wrap: wrap; margin: 22px 0; }
        .lap__kpi div span { display: block; font-size: 10px; letter-spacing: .6px; text-transform: uppercase; color: #6b7280; }
        .lap__kpi div strong { font-size: 16px; }
        .lap table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .lap th { text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280;
                  border-bottom: 1px solid #e5e7eb; padding: 8px 0; }
        .lap td { padding: 9px 0; border-bottom: 1px solid #f3f4f6; }
        .lap .is-right { text-align: right; }
        .lap .is-center { text-align: center; }
        .lap tfoot td { border-top: 1px solid #0e0f12; font-weight: 700; padding-top: 12px; }
        .lap__foot { margin-top: 24px; font-size: 11.5px; color: #6b7280; }
        .lap__aksi { display: flex; gap: 10px; justify-content: flex-end; margin-bottom: 20px; }
        .lap__sub { font-size: 15px; font-weight: 700; margin: 22px 0 6px; }
        @media print { body { padding: 0; } .lap__aksi { display: none; } }
    </style>
</head>
<body class="ad-body">
    <div class="lap">
        <div class="lap__aksi ad-no-print">
            <a href="{{ route('admin.laporan.index', array_filter(['periode' => $periode, 'tanggal' => $tanggal, 'bulan' => $bulan, 'tahun' => $tahun, 'kategori' => $kategoriId ?: null])) }}" class="ad-btn ad-btn--ghost">Kembali</a>
            <button type="button" class="ad-btn" onclick="window.print()">Cetak</button>
        </div>

        <div class="lap__head">
            <div class="lap__brand">
                <span class="lap__mark">G</span>
                <div>
                    <div style="font-size:14px; font-weight:600">Gavin Fashion Store</div>
                    <div style="font-size:11.5px; color:#6b7280">Laporan omzet penjualan</div>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:16px; font-weight:700">LAPORAN OMZET</div>
                <div style="font-size:12px; color:#6b7280">Periode {{ $label }}</div>
                <div style="font-size:12px; color:#6b7280">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
            </div>
        </div>

        <div class="lap__kpi">
            <div><span>Total Omzet</span><strong>{{ $rupiah($totalOmzet) }}</strong></div>
            <div><span>Pesanan Selesai</span><strong>{{ $jumlahPesanan }} pesanan</strong></div>
            <div><span>Produk Terjual</span><strong>{{ $produkTerjual }} pcs</strong></div>
            <div><span>Rata-rata / Pesanan</span><strong>{{ $rupiah($rataRata) }}</strong></div>
        </div>

        <div class="lap__sub">Rincian Transaksi Selesai</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Invoice</th>
                    <th>Pelanggan</th>
                    <th class="is-center">Item</th>
                    <th class="is-right">Total Produk</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($semuaTransaksi as $p)
                    <tr>
                        <td>{{ $p->tanggal_pesanan?->translatedFormat('d M Y, H:i') }}</td>
                        <td>{{ $p->invoice?->no_invoice ?? '—' }}</td>
                        <td>{{ $p->pelanggan?->nama ?? '—' }}</td>
                        <td class="is-center">{{ $p->detail->count() }}</td>
                        <td class="is-right">{{ $rupiah($p->detail->sum('subtotal')) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Tidak ada transaksi selesai pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td></td>
                    <td></td>
                    <td class="is-center">{{ $produkTerjual }} pcs</td>
                    <td class="is-right">{{ $rupiah($totalOmzet) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="lap__sub">Produk Terlaris</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th class="is-center">Terjual</th>
                    <th class="is-right">Omzet</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topProduk as $i => $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td>{{ $item['kategori'] }}</td>
                        <td class="is-center">{{ $item['pcs'] }} pcs</td>
                        <td class="is-right">{{ $rupiah($item['omzet']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada produk terjual.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="lap__foot">
            Omzet dihitung dari pesanan berstatus Selesai, tanpa ongkos kirim.
            Pesanan batal atau kedaluwarsa tidak dihitung.
        </div>
    </div>
</body>
</html>
