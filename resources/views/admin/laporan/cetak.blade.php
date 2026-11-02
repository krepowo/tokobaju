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
        .lap { max-width: 760px; margin: 0 auto; font-size: 13px; color: #111827; }
        .lap__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;
                     padding-bottom: 20px; border-bottom: 2px solid #0e0f12; }
        .lap__brand { display: flex; align-items: center; gap: 12px; }
        .lap__mark { width: 40px; height: 40px; border-radius: 8px; display: grid; place-items: center; font-weight: 700; font-size: 18px;
                     background: linear-gradient(135deg, #e2b961 0%, #b8892b 50%, #7b5921 100%); color: #0a0a0a; }
        .lap__sum { display: flex; gap: 32px; margin: 24px 0; }
        .lap__sum div span { display: block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; }
        .lap__sum div strong { font-size: 17px; }
        .lap table { width: 100%; border-collapse: collapse; }
        .lap th { text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280;
                  border-bottom: 1px solid #e5e7eb; padding: 8px 0; }
        .lap td { padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .lap .is-right { text-align: right; }
        .lap .is-center { text-align: center; }
        .lap tfoot td { border-top: 1px solid #0e0f12; font-weight: 700; padding-top: 12px; }
        .lap__foot { margin-top: 28px; font-size: 11.5px; color: #6b7280; }
        .lap__aksi { display: flex; gap: 10px; justify-content: flex-end; margin-bottom: 20px; }
        @media print { body { padding: 0; } .lap__aksi { display: none; } }
    </style>
</head>
<body class="ad-body">
    <div class="lap">
        <div class="lap__aksi ad-no-print">
            <a href="{{ route('admin.laporan.index', ['periode' => $periode, 'tanggal' => $tanggal, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="ad-btn ad-btn--ghost">Kembali</a>
            <button type="button" class="ad-btn" onclick="window.print()">Cetak</button>
        </div>

        <div class="lap__head">
            <div class="lap__brand">
                <span class="lap__mark">G</span>
                <div>
                    <div style="font-size:14px;font-weight:600">Gavin Fashion Store</div>
                    <div style="font-size:11.5px;color:#6b7280">Laporan omzet penjualan</div>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:16px;font-weight:700">LAPORAN OMZET</div>
                <div style="font-size:12px;color:#6b7280">Periode {{ $label }}</div>
                <div style="font-size:12px;color:#6b7280">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
            </div>
        </div>

        <div class="lap__sum">
            <div><span>Total Omzet</span><strong>{{ $rupiah($totalOmzet) }}</strong></div>
            <div><span>Jumlah Pesanan</span><strong>{{ $jumlahPesanan }}</strong></div>
            <div><span>Rata-rata</span><strong>{{ $rupiah($rataRata) }}</strong></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>{{ $periode === 'harian' ? 'No. Invoice' : 'Tanggal' }}</th>
                    <th class="is-center">Pesanan</th>
                    <th class="is-right">Omzet</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($baris as $b)
                    <tr>
                        <td>{{ $b['label'] }}</td>
                        <td class="is-center">{{ $b['jumlah'] }}</td>
                        <td class="is-right">{{ $rupiah($b['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Tidak ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="is-center">{{ $jumlahPesanan }}</td>
                    <td class="is-right">{{ $rupiah($totalOmzet) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="lap__foot">
            Laporan dihasilkan otomatis dari pesanan dengan pembayaran yang sudah disetujui penjual.
            Pesanan batal atau kedaluwarsa tidak dihitung.
        </div>
    </div>
</body>
</html>