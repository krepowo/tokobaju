<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Omzet &middot; {{ $label }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; color: #111827; margin: 0; padding: 28px 32px; font-size: 12px; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        .muted { color: #6b7280; }
        .head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 16px; }
        .kpi { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
        .kpi div { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; }
        .kpi span { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        .kpi strong { font-size: 15px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f9fafb; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #374151; }
        td.right, th.right { text-align: right; }
        .toolbar { margin-bottom: 16px; }
        button { border: 0; background: #111827; color: #fff; padding: 9px 18px; border-radius: 8px; font-size: 12px; cursor: pointer; }
        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    @php $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.'); @endphp

    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="head">
        <div>
            <h1>Laporan Omzet</h1>
            <div class="muted">Gavin Fashion Store &middot; Periode: {{ $label }}</div>
        </div>
        <div class="muted" style="text-align:right">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
    </div>

    <div class="kpi">
        <div><span>Total Omzet</span><strong>{{ $rupiah($totalOmzet) }}</strong></div>
        <div><span>Pesanan Selesai</span><strong>{{ number_format($jumlahPesanan, 0, ',', '.') }}</strong></div>
        <div><span>Produk Terjual</span><strong>{{ number_format($produkTerjual, 0, ',', '.') }} pcs</strong></div>
        <div><span>Rata-rata / Pesanan</span><strong>{{ $rupiah($rataRata) }}</strong></div>
    </div>

    <h2>Produk Terlaris</h2>
    <table>
        <thead><tr><th>Produk</th><th>Kategori</th><th class="right">Pcs</th><th class="right">Omzet</th></tr></thead>
        <tbody>
            @forelse ($topProduk as $p)
                <tr><td>{{ $p['nama'] }}</td><td>{{ $p['kategori'] }}</td><td class="right">{{ $p['pcs'] }}</td><td class="right">{{ $rupiah($p['omzet']) }}</td></tr>
            @empty
                <tr><td colspan="4" class="muted">Belum ada penjualan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Omzet per Kategori</h2>
    <table>
        <thead><tr><th>Kategori</th><th class="right">Omzet</th></tr></thead>
        <tbody>
            @forelse ($perKategori as $kat => $nilai)
                <tr><td>{{ $kat }}</td><td class="right">{{ $rupiah($nilai) }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rincian Transaksi ({{ $jumlahTransaksi }})</h2>
    <table>
        <thead><tr><th>No. Invoice</th><th>Pelanggan</th><th>Tanggal</th><th class="right">Total</th><th>Status</th></tr></thead>
        <tbody>
            @forelse ($semuaTransaksi as $t)
                <tr>
                    <td>{{ $t->invoice?->no_invoice ?? '—' }}</td>
                    <td>{{ $t->pelanggan?->nama ?? '—' }}</td>
                    <td>{{ $t->tanggal_pesanan?->format('d M Y H:i') }}</td>
                    <td class="right">{{ $rupiah($t->total) }}</td>
                    <td>{{ $t->statusLabel() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Tidak ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
