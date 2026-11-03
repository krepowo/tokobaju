<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Pesanan &middot; Gavin Fashion Store</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 28px 32px;
            font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            background: #fff;
        }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 2px solid #0d0d0f; padding-bottom: 12px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand__mark { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px; background: #dfb169; color: #0d0d0f; font-weight: 800; font-size: 16px; }
        .brand strong { display: block; font-size: 14px; }
        .brand small { display: block; font-size: 9px; letter-spacing: .16em; text-transform: uppercase; color: #6b7280; }
        h1 { margin: 0; font-size: 15px; font-weight: 700; text-align: right; }
        .periode { margin: 3px 0 0; font-size: 11px; color: #6b7280; text-align: right; }
        .ringkas { display: flex; gap: 28px; margin: 16px 0 14px; }
        .ringkas div span { display: block; font-size: 9.5px; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
        .ringkas div strong { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 6px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { font-size: 9.5px; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #9ca3af; }
        .is-right { text-align: right; }
        .mono { font-variant-numeric: tabular-nums; }
        tfoot td { border-top: 2px solid #0d0d0f; border-bottom: 0; font-weight: 700; font-size: 12.5px; }
        footer { margin-top: 18px; font-size: 10px; color: #6b7280; display: flex; justify-content: space-between; }
        .aksi-cetak { margin-bottom: 16px; }
        .aksi-cetak button { height: 32px; padding: 0 14px; border: 1px solid #0d0d0f; border-radius: 8px; background: #0d0d0f; color: #fff; font: inherit; font-size: 11.5px; font-weight: 600; cursor: pointer; }
        @media print {
            body { padding: 0; }
            .aksi-cetak { display: none; }
        }
    </style>
</head>
<body>
    <div class="aksi-cetak">
        <button type="button" onclick="window.print()">Cetak Sekarang</button>
    </div>

    <header>
        <div class="brand">
            <span class="brand__mark">G</span>
            <span>
                <strong>Gavin Fashion Store</strong>
                <small>Admin Seller</small>
            </span>
        </div>

        <div>
            <h1>Laporan Pesanan</h1>
            <p class="periode">
                @if ($filter['dari'] || $filter['sampai'])
                    Periode {{ $filter['dari'] ? \Carbon\Carbon::parse($filter['dari'])->format('d/m/Y') : '…' }}
                    &ndash; {{ $filter['sampai'] ? \Carbon\Carbon::parse($filter['sampai'])->format('d/m/Y') : '…' }}
                @else
                    Seluruh periode
                @endif
                &middot; Status: {{ $filter['status'] === 'semua' ? 'Semua Status' : $daftarStatus[$filter['status']] }}
                &middot; Dicetak {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
    </header>

    <section class="ringkas">
        <div><span>Jumlah Pesanan</span><strong>{{ $ringkasan['jumlah'] }}</strong></div>
        <div><span>Total Nilai</span><strong>Rp {{ number_format($ringkasan['total'], 0, ',', '.') }}</strong></div>
        <div><span>Selesai</span><strong>{{ $ringkasan['selesai'] }}</strong></div>
        <div><span>Menunggu Pembayaran</span><strong>{{ $ringkasan['belum_bayar'] }}</strong></div>
    </section>

    <table>
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Pulau</th>
                <th class="is-right">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarPesanan as $pesanan)
                <tr>
                    <td class="mono">{{ $pesanan->invoice?->no_invoice ?? '—' }}</td>
                    <td class="mono">{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d/m/Y H:i') }}</td>
                    <td>{{ $pesanan->user?->nama ?? '—' }}</td>
                    <td>{{ $pesanan->pulau_tujuan }}</td>
                    <td class="is-right mono">Rp {{ number_format((float) $pesanan->total, 0, ',', '.') }}</td>
                    <td>{{ $pesanan->statusLabel() }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada pesanan pada filter ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total ({{ $ringkasan['jumlah'] }} pesanan)</td>
                <td class="is-right mono">Rp {{ number_format($ringkasan['total'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <footer>
        <span>Gavin Fashion Store &middot; Laporan dibuat otomatis oleh sistem</span>
        <span>{{ now()->format('d/m/Y H:i') }}</span>
    </footer>

    <script>
        window.addEventListener('load', function () {
            if (!window.location.search.includes('nocetak')) {
                window.print();
            }
        });
    </script>
</body>
</html>
