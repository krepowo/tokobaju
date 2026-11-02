@extends('layouts.site')

@section('title', 'Pesanan Saya')
@section('active', 'pesanan')
@section('main-class', 'site-main--full')

@section('content')
<div class="orders-page">
    <div class="orders-container">
        <div class="orders-head">
            <h1 class="orders-title">Pesanan Saya</h1>
            <span class="orders-count">{{ $totalCount }} pesanan</span>
        </div>

        <div class="orders-tabs">
            <a href="{{ route('pesanan.index') }}" class="orders-tab {{ $active === 'semua' ? 'is-active' : '' }}">
                <span>Semua</span>
                <span class="orders-tab__num">{{ $totalCount }}</span>
            </a>
            @foreach ($statusList as $key => $label)
                <a href="{{ route('pesanan.index', ['status' => $key]) }}" class="orders-tab {{ $active === $key ? 'is-active' : '' }}">
                    <span>{{ $label }}</span>
                    <span class="orders-tab__num">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        @forelse ($orders as $order)
            <article class="order">
                <header class="order__head">
                    <div class="order__id">
                        <b>{{ $order->invoice?->no_invoice ?? 'INV-' . substr($order->id_pesanan, 0, 8) }}</b>
                        <span class="order__dot">•</span>
                        <span>{{ $order->tanggal_pesanan?->format('d F Y') }}</span>
                    </div>
                    <span class="status-badge st-{{ $order->status }}">
                        <i class="dot"></i>{{ $order->statusLabel() }}
                    </span>
                </header>

                <div class="order__items">
                    @foreach ($order->detailPesanan as $i => $d)
                        <div class="order-item {{ $i > 0 ? 'is-split' : '' }}">
                            <div class="order-item__main">
                                <img class="order-item__img" src="{{ $d->ukuran->produk->gambar_url }}" alt="{{ $d->ukuran->produk->nama_produk }}">
                                <div class="order-item__meta">
                                    <b class="order-item__name">{{ $d->ukuran->produk->nama_produk }}</b>
                                    <span class="order-item__variant">Warna: {{ $d->ukuran->warna }} &bull; Ukuran: {{ $d->ukuran->ukuran }}</span>
                                </div>
                            </div>
                            <div class="order-item__price">
                                <span class="order-item__qty">{{ $d->jumlah }} &times; Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</span>
                                <b class="order-item__sub">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</b>
                            </div>
                            @if ($order->status === 'selesai')
                                <button type="button" class="order-item__review" title="Beri Ulasan">
                                    <svg viewBox="0 0 24 24" fill="#dfb169" width="14" height="13" aria-hidden="true"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                    Beri Ulasan
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="order__ship">
                    <div class="order__ship-left">
                        Jasa Pengiriman: <b>{{ $order->jasa?->nama_jasa }}</b>
                        &nbsp;&bull;&nbsp; Ongkos Kirim <b>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</b>
                        @if ($order->status === 'selesai')
                            &nbsp;&bull;&nbsp; <b class="has-success">Paket telah diterima pembeli</b>
                        @elseif ($order->status === 'dikirim')
                            &nbsp;&bull;&nbsp; <b class="has-info">Paket dalam perjalanan</b>
                        @endif
                    </div>
                    <div class="order__ship-right">
                        <span class="order__ship-resi">Resi:</span>
                        <b class="order__resi-val">{{ $order->pengiriman?->no_resi ?? '—' }}</b>
                        @if ($order->pengiriman?->no_resi)
                            <button type="button" class="order__copy" data-copy="{{ $order->pengiriman->no_resi }}">Salin</button>
                        @endif
                    </div>
                </div>

                <footer class="order__foot">
                    <div class="order__total">
                        <span>Total Pembayaran:</span>
                        <b>Rp {{ number_format($order->total, 0, ',', '.') }}</b>
                        @if ($order->status === 'menunggu_pembayaran')
                            <span class="order__hint hint-warn">Batas bayar {{ $order->batas_bayar?->format('d M Y H:i') }}</span>
                        @elseif ($order->status === 'selesai')
                            <span class="order__hint hint-success">Paket telah diterima pada {{ $order->pengiriman?->tanggal_kirim?->format('d F Y') ?? $order->tanggal_pesanan?->format('d F Y') }}</span>
                        @elseif ($order->status === 'dibatalkan' || $order->status === 'pembayaran_ditolak')
                            <span class="order__hint hint-danger">{{ $order->statusLabel() }}</span>
                        @endif
                    </div>
                    <a href="{{ route('pesanan.show', $order->id_pesanan) }}" class="order__invoice">Lihat Invoice</a>
                </footer>
            </article>
        @empty
            <p class="orders-empty">Belum ada pesanan. Mulai belanja di <a href="{{ route('katalog') }}">katalog</a>.</p>
        @endforelse
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.order__copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var val = btn.getAttribute('data-copy');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(val).then(function () {
                    btn.textContent = 'Tersalin';
                    setTimeout(function () { btn.textContent = 'Salin'; }, 1200);
                });
            } else {
                var t = document.createElement('textarea');
                t.value = val; document.body.appendChild(t); t.select();
                document.execCommand('copy'); document.body.removeChild(t);
                btn.textContent = 'Tersalin';
                setTimeout(function () { btn.textContent = 'Salin'; }, 1200);
            }
        });
    });
})();
</script>
@endsection
