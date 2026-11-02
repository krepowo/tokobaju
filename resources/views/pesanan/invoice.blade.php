<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice {{ $pesanan->invoice->no_invoice }}</title>
<style>
  @page { margin: 0; size: A4 portrait; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 11px; color: #111; margin: 0; line-height: 1.5; }
  .page { padding: 18mm 16mm; }
  table { width: 100%; border-collapse: collapse; }
  .muted { color: #666; }
  .label { font-size: 9px; letter-spacing: 1px; text-transform: uppercase; color: #777; }
  .right { text-align: right; }
  .center { text-align: center; }

  .head td { vertical-align: middle; padding-bottom: 14px; border-bottom: 2px solid #111; }
  .logo { height: 42px; }
  .brand { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
  .doc-title { font-size: 20px; font-weight: bold; letter-spacing: 3px; }
  .inv-no { color: #8a6a1f; font-weight: bold; }

  .meta td { vertical-align: top; padding: 16px 0; }
  .meta .col { width: 50%; }
  .meta p { margin: 2px 0; }

  .items { margin-top: 4px; }
  .items th { text-align: left; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; color: #555;
              padding: 8px 6px; border-top: 1px solid #111; border-bottom: 1px solid #111; }
  .items th.right { text-align: right; }
  .items th.center { text-align: center; }
  .items td { padding: 9px 6px; border-bottom: 1px solid #ddd; vertical-align: top; }
  .items .sub { font-size: 10px; color: #666; }

  .totals { margin-top: 10px; }
  .totals td { padding: 4px 6px; }
  .totals .grand td { border-top: 1px solid #111; padding-top: 8px; font-size: 13px; font-weight: bold; }

  .status { display: inline-block; border: 1px solid #111; padding: 2px 10px; font-size: 10px; font-weight: bold; letter-spacing: 2px; }
  .foot { margin-top: 28px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #666; }

  .toolbar { padding: 12px; text-align: center; background: #f3f3f3; border-bottom: 1px solid #ddd; }
  .toolbar a, .toolbar button { font: inherit; font-size: 12px; padding: 7px 16px; border: 1px solid #111; background: #111; color: #fff; text-decoration: none; cursor: pointer; margin: 0 4px; }
  .toolbar a.alt { background: #fff; color: #111; }

  @media print {
    @page { margin: 0; size: A4 portrait; }
    body { background: #ffffff !important; color: #111 !important; margin: 0 !important; padding: 0 !important; }
    .toolbar { display: none !important; }
    .page { max-width: none !important; width: auto !important; margin: 0 !important; padding: 18mm 16mm !important; background: #ffffff !important; box-shadow: none !important; border: none !important; }
  }
</style>

@if(($mode ?? 'web') === 'web')
<style>
@media screen {
  body {
    background-color: #ececec;
    margin: 0;
    padding: 0 0 32px;
  }
  .toolbar {
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .page {
    max-width: 210mm;
    width: 100%;
    margin: 24px auto;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 18mm 16mm;
  }
}

@media screen and (max-width: 768px) {
  body {
    padding: 0 0 20px;
  }
  .page {
    width: calc(100% - 24px);
    margin: 12px auto;
    padding: 16px 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }
  .toolbar {
    padding: 10px 6px;
  }
  .toolbar a, .toolbar button {
    padding: 6px 10px;
    font-size: 11px;
    margin: 0 2px;
  }
  .brand {
    font-size: 13.5px;
  }
  .doc-title {
    font-size: 16px;
    letter-spacing: 2px;
  }
  .inv-no {
    font-size: 11px;
  }
}
</style>
@endif
</head>
<body>

@if(($mode ?? 'web') === 'web')
<div class="toolbar">
  <a href="{{ route('pesanan.show', $pesanan->id_pesanan) }}" class="alt">Kembali</a>
  <button onclick="window.print()">Cetak</button>
  <a href="{{ route('pesanan.invoice.unduh', $pesanan->id_pesanan) }}">Unduh PDF</a>
</div>
@endif

@php
  $lunas = in_array($pesanan->status, ['diproses','dikirim','selesai']);
  $subtotalProduk = $pesanan->detailPesanan->sum('subtotal');

  $logoFile = public_path('images/logo-gavin.png');
  $logoSrc = null;
  if (is_file($logoFile)) {
      $ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION)) === 'jpg' ? 'jpeg' : strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
      $logoSrc = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
  }
@endphp

<div class="page">
<table class="head">
  <tr>
    <td>
      <table style="width:auto;"><tr>
        @if($logoSrc)<td style="border:0; padding:0 10px 0 0;"><img src="{{ $logoSrc }}" class="logo" alt="Gavin"></td>@endif
        <td style="border:0; padding:0;">
          <div class="brand">GAVIN FASHION STORE</div>
          <div class="muted">Toko pakaian online</div>
        </td>
      </tr></table>
    </td>
    <td class="right">
      <div class="doc-title">INVOICE</div>
      <div class="inv-no">{{ $pesanan->invoice->no_invoice }}</div>
    </td>
  </tr>
</table>

<table class="meta">
  <tr>
    <td class="col">
      <div class="label">Ditagihkan kepada</div>
      <p><strong>{{ $pesanan->user->nama }}</strong></p>
      <p>{{ $pesanan->alamat_tujuan }}</p>
      @if($pesanan->user->no_telepon)<p>{{ $pesanan->user->no_telepon }}</p>@endif
    </td>
    <td class="col right">
      <div class="label">Tanggal pesanan</div>
      <p>{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->translatedFormat('d F Y, H:i') }} WIB</p>
      <div class="label" style="margin-top:8px;">Status pembayaran</div>
      <p><span class="status">{{ $lunas ? 'LUNAS' : 'BELUM DIBAYAR' }}</span></p>
    </td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th style="width:4%">No</th>
      <th>Produk</th>
      <th class="center" style="width:8%">Jumlah</th>
      <th class="right" style="width:18%">Harga</th>
      <th class="right" style="width:18%">Subtotal</th>
    </tr>
  </thead>
  <tbody>
    @foreach($pesanan->detailPesanan as $i => $d)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>
        {{ $d->ukuranStok->produk->nama_produk }}
        <div class="sub">Warna {{ $d->ukuranStok->warna }} &middot; Ukuran {{ $d->ukuranStok->ukuran }}</div>
      </td>
      <td class="center">{{ $d->jumlah }}</td>
      <td class="right">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
      <td class="right">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="totals">
  <tr>
    <td style="width:55%"></td>
    <td>Subtotal produk</td>
    <td class="right">Rp {{ number_format($subtotalProduk, 0, ',', '.') }}</td>
  </tr>
  <tr>
    <td></td>
    <td>Ongkos kirim ({{ $pesanan->jasaPengiriman->nama_jasa ?? '-' }})</td>
    <td class="right">Rp {{ number_format($pesanan->ongkir, 0, ',', '.') }}</td>
  </tr>
  <tr class="grand">
    <td></td>
    <td>Total</td>
    <td class="right">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</td>
  </tr>
</table>

<table style="margin-top:22px;">
  <tr>
    <td style="width:50%; vertical-align:top;">
      <div class="label">Pembayaran</div>
      <p style="margin:2px 0;">Transfer bank</p>
      <p style="margin:2px 0;" class="muted">{{ $pesanan->invoice->rekening_tujuan }}</p>
    </td>
    <td style="width:50%; vertical-align:top;">
      <div class="label">Pengiriman</div>
      <p style="margin:2px 0;">{{ $pesanan->jasaPengiriman->nama_jasa ?? '-' }}</p>
      <p style="margin:2px 0;" class="muted">No. resi: {{ $pesanan->pengiriman->no_resi ?? 'Belum tersedia' }}</p>
    </td>
  </tr>
</table>

<div class="foot">
  Terima kasih telah berbelanja di Gavin Fashion Store. Dokumen ini diterbitkan secara elektronik dan sah tanpa tanda tangan.
</div>
</div>

</body>
</html>

