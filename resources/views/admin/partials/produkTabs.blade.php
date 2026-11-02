@php
    $tab = $tab ?? 'produk';
@endphp
<nav class="ad-tabs">
    <a href="{{ route('admin.produk.index') }}" class="ad-tab {{ $tab === 'produk' ? 'is-active' : '' }}">Produk</a>
    <a href="{{ route('admin.kategori.index') }}" class="ad-tab {{ $tab === 'kategori' ? 'is-active' : '' }}">Kategori</a>
    <a href="{{ route('admin.jasa.index') }}" class="ad-tab {{ $tab === 'jasa' ? 'is-active' : '' }}">Jasa Pengiriman</a>
</nav>