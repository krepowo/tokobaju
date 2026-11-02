@php
    $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    $subtotal = $pesanan->detail->sum('subtotal');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $pesanan->invoice?->no_invoice }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        body { background: #fff; padding: 32px; }
        .inv { max-width: 760px; margin: 0 auto; font-size: 13px; color: #111827; }
        .inv__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 20px; border-bottom: 2px solid #0e0f12; }
        .inv__brand { display: flex; align-items: center; gap: 12px; }
        .inv__mark { width: 40px; height: 40px; border-radius: 8px; display: grid; place-items: center; font-weight: 700; font-size: 18px;
                     background: linear-gradient(135deg, #e2b961 0%, #b8892b 50%, #7b5921 100%); color: #0a0a0a; }
        .inv__title { text-align: right; }
        .inv__title h1 { font-size: 20px; letter-spacing: -.4px; }
        .inv__meta { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 24px 0; }
        .inv__box h4 { font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 6px; }
        .inv table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .inv th { text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280;
                  border-bottom: 1px solid #e5e7eb; padding: 8px 0; }
        .inv td { padding: 10px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .inv .is-right { text-align: right; }
        .inv__total { margin-top: 16px; margin-left: auto; width: 280px; }
        .inv__total .row { display: flex; justify-content: space-between; padding: 6px 0; }
        .inv__total .row--big { border-top: 1px solid #0e0f12; margin-top: 6px; padding-top: 10px; font-size: 15px; font-weight: 700; }
        .inv__foot { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 11.5px; color: #6b7280; }
        .inv__aksi { display: flex; gap: 10px; justify-content: flex-end; margin-bottom: 20px; }
        @media print { body { padding: 0; } .inv__aksi { display: none; } }
    </style>
</head>
<body class="ad-body">
    <div class="inv">
        <div class="inv__aksi ad-no-print">
            <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="ad-btn ad-btn--ghost">Kembali</a>
            <button type="button" class="ad-btn" onclick="window.print()">Cetak</button>
        </div>

        <div class="inv__head">
            <div class="inv__brand">
                <span class="inv__mark">G</span>
                <div>
                    <div style="font-size:14px;font-weight:600">Gavin Fashion Store</div>
                    <div style="font-size:11.5px;color:#6b7280">Toko baju online &middot; Surakarta</div>
                </div>
            </div>
            <div class="inv__title">
                <h1>INVOICE</h1>
                <div style="font-size:12px;color:#6b7280">{{ $pesanan->invoice?->no_invoice }}</div>
                <div style="font-size:12px;color:#6b7280">{{ $pesanan->invoice?->tanggal_terbit?->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        <div class="inv__meta">
            <div class="inv__box">
                <h4>Ditagihkan kepada</h4>
                <div style="font-weight:600">{{ $pesanan->pelanggan?->nama }}</div>
                <div style="color:#374151">{{ $pesanan->pelanggan?->email }}</div>
                <div style="color:#374151">{{ $pesanan->pelanggan?->no_telepon }}</div>
                <div style="color:#374151;margin-top:6px">{{ $pesanan->alamat_tujuan }}</div>
            </div>
            <div class="inv__box">
                <h4>Pembayaran</h4>
                <div>{{ $pesanan->invoice?->rekening_tujuan }}</div>
                <div style="color:#374151;margin-top:6px">Batas bayar: {{ $pesanan->batas_bayar?->translatedFormat('d F Y H:i') }}</div>
                <div style="color:#374151">Jasa kirim: {{ $pesanan->jasa?->nama_jasa }} ({{ $pesanan->pulau_tujuan }})</div>
                @if ($pesanan->pengiriman)
                    <div style="color:#374151">No. resi: {{ $pesanan->pengiriman->no_resi }}</div>
                @endif
                <div style="margin-top:8px">
                    @include('admin.partials.badge', ['status' => $pesanan->status])
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Ukuran / Warna</th>
                    <th class="is-right">Jumlah</th>
                    <th class="is-right">Harga</th>
                    <th class="is-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pesanan->detail as $d)
                    <tr>
                        <td>{{ $d->ukuranStok?->produk?->nama_produk ?? 'Produk dihapus' }}</td>
                        <td>{{ $d->ukuranStok?->ukuran }} / {{ $d->ukuranStok?->warna }}</td>
                        <td class="is-right">{{ $d->jumlah }}</td>
                        <td class="is-right">{{ $rupiah($d->harga_satuan) }}</td>
                        <td class="is-right">{{ $rupiah($d->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="inv__total">
            <div class="row"><span>Subtotal produk</span><span>{{ $rupiah($subtotal) }}</span></div>
            <div class="row"><span>Ongkos kirim</span><span>{{ $rupiah($pesanan->ongkir) }}</span></div>
            <div class="row row--big"><span>Total</span><span>{{ $rupiah($pesanan->invoice?->total_tagihan ?? $pesanan->total) }}</span></div>
        </div>

        <div class="inv__foot">
            Invoice dibuat otomatis oleh sistem Gavin Fashion Store. Pembayaran hanya melalui transfer bank
            dengan unggah bukti transfer maksimal 1&times;24 jam setelah checkout.
        </div>
    </div>
</body>
</html>