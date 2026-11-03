@extends('layouts.site')

@section('title', 'Invoice ' . ($pesanan->invoice?->no_invoice ?? ''))
@section('active', 'pesanan')
@section('main_class', 'site-main--flush')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
@endpush

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $subtotal = $pesanan->detail->sum('subtotal');
    @endphp

    <div class="ps-page">
        <div class="ps-wrap">
            <div class="ps-head ps-no-print">
                <div>
                    <h1 class="ps-title">Invoice</h1>
                    <p class="ps-count">{{ $pesanan->invoice?->no_invoice }}</p>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap">
                    <a href="{{ route('pesanan.index') }}" class="btn btn--ghost btn--sm">Kembali</a>
                    <button type="button" class="btn btn--sm" onclick="window.print()">Cetak Invoice</button>
                </div>
            </div>

            <div class="ps-invoice-card">
                <div class="ps-invoice-head">
                    <div class="ps-invoice-brand">
                        <span class="ps-invoice-mark">G</span>
                        <div>
                            <div style="font-size:14px; font-weight:600">Gavin Fashion Store</div>
                            <div style="font-size:12px; color:#6b7280">Toko baju online &middot; Surakarta</div>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:19px; font-weight:700; letter-spacing:-.4px">INVOICE</div>
                        <div style="font-size:12px; color:#6b7280">{{ $pesanan->invoice?->no_invoice }}</div>
                        <div style="font-size:12px; color:#6b7280">{{ $pesanan->invoice?->tanggal_terbit?->translatedFormat('d F Y') }}</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin:22px 0">
                    <div>
                        <div style="font-size:10.5px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; margin-bottom:6px">Ditagihkan kepada</div>
                        <div style="font-weight:600">{{ $pesanan->pelanggan?->nama }}</div>
                        <div style="color:#374151; font-size:13px">{{ $pesanan->pelanggan?->email }}</div>
                        <div style="color:#374151; font-size:13px">{{ $pesanan->pelanggan?->no_telepon }}</div>
                        <div style="color:#374151; font-size:13px; margin-top:6px">{{ $pesanan->alamat_tujuan }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; margin-bottom:6px">Pembayaran</div>
                        <div style="font-size:13px">{{ $pesanan->invoice?->rekening_tujuan }}</div>
                        <div style="color:#374151; font-size:13px; margin-top:6px">Batas bayar: {{ $pesanan->batas_bayar?->translatedFormat('d F Y H:i') }}</div>
                        <div style="color:#374151; font-size:13px">Jasa kirim: {{ $pesanan->jasa?->nama_jasa }} ({{ $pesanan->pulau_tujuan }})</div>
                        @if ($pesanan->pengiriman)
                            <div style="color:#374151; font-size:13px">No. resi: {{ $pesanan->pengiriman->no_resi }}</div>
                        @endif
                        <div style="margin-top:10px">
                            <span class="ps-status ps-status--{{ \App\Support\StatusPesanan::badge($pesanan->status) }}">
                                {{ \App\Support\StatusPesanan::label($pesanan->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <table class="ps-invoice-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Ukuran / Warna</th>
                            <th class="r">Jumlah</th>
                            <th class="r">Harga</th>
                            <th class="r">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pesanan->detail as $d)
                            <tr>
                                <td>{{ $d->ukuranStok?->produk?->nama_produk ?? 'Produk dihapus' }}</td>
                                <td>{{ $d->ukuranStok?->ukuran }} / {{ $d->ukuranStok?->warna }}</td>
                                <td class="r">{{ $d->jumlah }}</td>
                                <td class="r">{{ $rupiah($d->harga_satuan) }}</td>
                                <td class="r">{{ $rupiah($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="ps-invoice-totals">
                    <div class="row"><span>Subtotal produk</span><span>{{ $rupiah($subtotal) }}</span></div>
                    <div class="row"><span>Ongkos kirim</span><span>{{ $rupiah($pesanan->ongkir) }}</span></div>
                    <div class="row row--big"><span>Total</span><span>{{ $rupiah($pesanan->invoice?->total_tagihan ?? $pesanan->total) }}</span></div>
                </div>

                <p style="font-size:11.5px; color:#6b7280; margin-top:28px; padding-top:16px; border-top:1px solid #e5e7eb">
                    Invoice dibuat otomatis oleh sistem Gavin Fashion Store. Pembayaran hanya melalui transfer bank
                    dengan unggah bukti transfer maksimal 1&times;24 jam setelah checkout.
                </p>
            </div>
        </div>
    </div>
@endsection
