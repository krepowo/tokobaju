@php
    $aktif = $aktif ?? 'produk';
@endphp

<nav class="admin-tabs" aria-label="Kelola produk">
    <a href="{{ route('admin.produk.index') }}"
       class="admin-tab {{ $aktif === 'produk' ? 'is-active' : '' }}">Produk</a>

    <a href="{{ route('admin.kategori.index') }}"
       class="admin-tab {{ $aktif === 'kategori' ? 'is-active' : '' }}">Kategori</a>

    <a href="{{ route('admin.jasa.index') }}"
       class="admin-tab {{ $aktif === 'jasa' ? 'is-active' : '' }}">Jasa Pengiriman</a>
</nav>
