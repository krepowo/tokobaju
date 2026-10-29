@php
    $active = $active ?? '';
    $firstName = auth()->check() ? \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') : null;
@endphp

<header class="site-header">
    <div class="site-header__inner">
        <a href="{{ route('home') }}" class="hdr-brand" aria-label="Gavin Fashion Store">
            <span class="hdr-brand__mark">
                <img src="{{ asset('images/figma/home/logo-mark.png') }}" alt="">
            </span>
            <span class="hdr-brand__name">Gavin Fashion Store</span>
        </a>

        <nav class="hdr-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="{{ $active === 'beranda' ? 'is-active' : '' }}">Beranda</a>
            <a href="#" class="is-disabled" title="Segera hadir">Katalog</a>
            <a href="#" class="is-disabled" title="Segera hadir">
                Kategori
                <img class="hdr-nav__caret" src="{{ asset('images/figma/home/ico-arrow-down.svg') }}" alt="">
            </a>
            <a href="#" class="is-disabled" title="Segera hadir">Pesanan Saya</a>
        </nav>

        <div class="hdr-actions">
            <button type="button" class="hdr-icon" aria-label="Cari produk">
                <img src="{{ asset('images/figma/home/ico-search.svg') }}" alt="">
            </button>

            @auth
                <a href="{{ auth()->user()->isPenjual() ? route('admin.home') : route('home') }}" class="hdr-cta">
                    Halo, {{ $firstName }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hdr-avatar" title="Keluar" aria-label="Keluar">
                        <img src="{{ asset('images/figma/home/ico-user.svg') }}" alt="">
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hdr-cta">Masuk / Daftar</a>
                <a href="{{ route('login') }}" class="hdr-avatar" title="Masuk ke akun" aria-label="Masuk ke akun">
                    <img src="{{ asset('images/figma/home/ico-user.svg') }}" alt="">
                </a>
            @endauth
        </div>
    </div>
    <nav class="hdr-navmob" aria-label="Navigasi utama (seluler)">
        <a href="{{ route('home') }}" class="{{ $active === 'beranda' ? 'is-active' : '' }}">Beranda</a>
        <a href="#" class="is-disabled">Katalog</a>
        <a href="#" class="is-disabled">Kategori</a>
        <a href="#" class="is-disabled">Pesanan Saya</a>
    </nav>
</header>
