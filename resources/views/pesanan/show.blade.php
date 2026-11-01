@extends('layouts.site')

@section('title', 'Detail Pesanan')
@section('active', 'keranjang')
@section('main_class', 'site-main--catalog')

@section('content')
<div class="pesanan">
    <div class="pesanan__head">
        <h1>Pesanan #{{ substr($order->id_pesanan, 0, 8) }}</h1>
        <span class="pesanan__status">{{ $order->statusLabel() }}</span>
    </div>

    <p class="pesanan__meta">
        No Invoice: <b>{{ $order->invoice->no_invoice }}</b>
        &nbsp;&middot;&nbsp; {{ $order->tanggal_pesanan?->format('d M Y H:i') }}
    </p>

    @if ($order->status === 'menunggu_pembayaran')
        <div class="pesanan__due">
            Selesaikan pembayaran sebelum <b>{{ $order->batas_bayar?->format('d M Y H:i') }}</b> (1×24 jam).
        </div>
    @endif

    <div class="pesanan__grid">
        <section class="pesanan__section">
            <h2>Produk</h2>
            @foreach ($order->detailPesanan as $d)
                <div class="pesanan__item">
                    <img src="{{ asset($d->ukuran->produk->gambar) }}" alt="{{ $d->ukuran->produk->nama_produk }}">
                    <div class="pesanan__item-info">
                        <span class="pesanan__item-name">{{ $d->ukuran->produk->nama_produk }}</span>
                        <span class="pesanan__item-var">{{ $d->ukuran->warna }} · {{ $d->ukuran->ukuran }} × {{ $d->jumlah }}</span>
                        <span class="pesanan__item-price">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</span>
                    </div>
                    <span class="pesanan__item-sub">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </section>

        <aside class="pesanan__aside">
            <section class="pesanan__section">
                <h2>Pengiriman</h2>
                <p class="pesanan__line">Jasa: {{ $order->jasa->nama_jasa }}</p>
                <p class="pesanan__line">{{ $order->alamat_tujuan }}</p>
                <p class="pesanan__line muted">Pulau tujuan: {{ $order->pulau_tujuan }}</p>
            </section>

            <section class="pesanan__section">
                <h2>Pembayaran</h2>
                <div class="pesanan__row"><span>Subtotal</span><span>Rp {{ number_format($order->total - $order->ongkir, 0, ',', '.') }}</span></div>
                <div class="pesanan__row"><span>Ongkir</span><span>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span></div>
                <div class="pesanan__row pesanan__row--total"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>

                <p class="pesanan__rek">Transfer ke rekening:<br><b>{{ $order->invoice->rekening_tujuan }}</b></p>

                <button type="button" class="pesanan__upload" disabled>Upload Bukti Pembayaran</button>
                <p class="pesanan__hint">Upload bukti tersedia pada tahap berikutnya (F-08).</p>
            </section>
        </aside>
    </div>
</div>
@endsection
