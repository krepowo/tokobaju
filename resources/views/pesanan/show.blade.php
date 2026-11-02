@extends('layouts.site')

@section('title', 'Detail Pesanan')
@section('active', 'keranjang')
@section('main-class', 'site-main--catalog')

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
                    <img src="{{ $d->ukuran->produk->gambar_url }}" alt="{{ $d->ukuran->produk->nama_produk }}">
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

                @php
                    $bayarTerakhir = $order->pembayaran->sortByDesc('tanggal_upload')->first();
                    $bisaUnggah = in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak'], true);
                    $buktiAda = $bayarTerakhir && file_exists(public_path($bayarTerakhir->bukti_transfer));
                @endphp

                @if ($bayarTerakhir && $order->status === 'pembayaran_ditolak')
                    <div class="pesanan__tolak">
                        <b>Pembayaran ditolak.</b>
                        <span>{{ $bayarTerakhir->alasan_tolak }}</span>
                    </div>
                @endif

                @if ($bayarTerakhir && $order->status === 'menunggu_verifikasi')
                    <div class="pesanan__verif">
                        <b>Bukti sudah dikirim.</b>
                        <span>Menunggu verifikasi penjual. Diunggah {{ $bayarTerakhir->tanggal_upload?->format('d M Y H:i') }}.</span>
                    </div>

                    @if ($buktiAda)
                        <a href="{{ asset($bayarTerakhir->bukti_transfer) }}" target="_blank" rel="noopener" class="pesanan__bukti">
                            <img src="{{ asset($bayarTerakhir->bukti_transfer) }}" alt="Bukti transfer">
                        </a>
                    @endif
                @endif

                @if ($bisaUnggah)
                    <form method="POST" action="{{ route('pesanan.bayar', $order) }}" enctype="multipart/form-data" class="pesanan__form">
                        @csrf
                        <label class="pesanan__file">
                            <input type="file" name="bukti" accept="image/jpeg,image/png" required>
                            <span class="pesanan__file-teks">Pilih gambar bukti transfer (JPG/PNG, maks 10 MB)</span>
                        </label>

                        @error('bukti')
                            <p class="pesanan__error">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="pesanan__upload">Upload Bukti Pembayaran</button>
                    </form>
                @elseif (! $bayarTerakhir)
                    <p class="pesanan__hint">Pesanan ini tidak lagi menerima bukti pembayaran.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
@endsection
