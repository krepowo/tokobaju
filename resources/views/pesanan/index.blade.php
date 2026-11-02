@extends('layouts.site')

@section('title', 'Pesanan Saya')
@section('active', 'pesanan')
@section('main-class', 'site-main--catalog')

@section('content')
<div class="pesanan-list">
    <h1 class="pesanan-list__title">Pesanan Saya</h1>

    @if ($orders->isEmpty())
        <p class="pesanan-list__empty">Belum ada pesanan. Mulai belanja di
            <a href="{{ route('katalog') }}">katalog</a>.</p>
    @else
        <div class="pesanan-list__items">
            @foreach ($orders as $order)
                <a href="{{ route('pesanan.show', $order->id_pesanan) }}" class="order-card">
                    <div class="order-card__head">
                        <span class="order-card__no">No. Invoice: {{ $order->invoice?->no_invoice ?? '-' }}</span>
                        <span class="order-card__status">{{ $order->statusLabel() }}</span>
                    </div>
                    <div class="order-card__meta">
                        <span>{{ $order->tanggal_pesanan?->format('d M Y H:i') }}</span>
                        <span>{{ $order->detailPesanan->sum('jumlah') }} item</span>
                        <span class="order-card__total">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
