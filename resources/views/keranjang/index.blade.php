@extends('layouts.site')

@section('title', 'Keranjang')
@section('active', 'keranjang')
@section('main_class', 'site-main--catalog')

@section('content')
<div class="cart">
    <h1 class="cart__title">Keranjang</h1>

    @if ($items->isEmpty())
        <div class="cart__empty">
            <p>Keranjang masih kosong.</p>
            <a href="{{ route('katalog') }}" class="cart__cta">Mulai Belanja</a>
        </div>
    @else
        <div class="cart__layout">
            <div class="cart__items">
                @foreach ($items as $item)
                    <article class="cart-item">
                        <form method="POST" action="{{ route('keranjang.togglePilih', $item->id_keranjang) }}" class="cart-item__pilih">
                            @csrf
                            <input type="checkbox" @checked($item->dipilih) onchange="this.form.submit()"
                                   aria-label="Pilih {{ $item->ukuran->produk->nama_produk }} untuk checkout">
                        </form>

                        <img class="cart-item__img" src="{{ asset($item->ukuran->produk->gambar) }}" alt="{{ $item->ukuran->produk->nama_produk }}">

                        <div class="cart-item__info">
                            <a href="{{ route('produk.detail', $item->ukuran->produk->id_produk) }}" class="cart-item__name">{{ $item->ukuran->produk->nama_produk }}</a>
                            <p class="cart-item__variant">Warna: {{ $item->ukuran->warna }} &middot; Ukuran: {{ $item->ukuran->ukuran }}</p>
                            <p class="cart-item__price">Rp {{ number_format((int) $item->ukuran->produk->harga, 0, ',', '.') }}</p>
                        </div>

                        <form method="POST" action="{{ route('keranjang.update', $item->id_keranjang) }}" class="cart-item__qty">
                            @csrf
                            <button type="submit" name="jumlah" value="{{ $item->jumlah - 1 }}" @disabled($item->jumlah <= 1) aria-label="Kurangi jumlah">&minus;</button>
                            <span>{{ $item->jumlah }}</span>
                            <button type="submit" name="jumlah" value="{{ $item->jumlah + 1 }}" aria-label="Tambah jumlah">+</button>
                        </form>

                        <p class="cart-item__subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>

                        <form method="POST" action="{{ route('keranjang.hapus', $item->id_keranjang) }}" class="cart-item__hapus">
                            @csrf
                            <button type="submit" aria-label="Hapus item">&times;</button>
                        </form>
                    </article>
                @endforeach
            </div>

            <aside class="cart__summary">
                <h2>Ringkasan</h2>
                <div class="cart__summary-row">
                    <span>Item dipilih</span>
                    <span>{{ $selectedCount }}</span>
                </div>
                <div class="cart__summary-row cart__summary-row--total">
                    <span>Total</span>
                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('checkout.index') }}" class="cart__checkout">Checkout</a>
            </aside>
        </div>
    @endif
</div>
@endsection
