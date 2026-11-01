@php
    $active = $active ?? '';
    $nama = auth()->check() ? auth()->user()->nama : null;
    $kategoriNav = \App\Models\Kategori::orderBy('nama_kategori')->get();
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
            <a href="{{ route('katalog') }}" class="{{ $active === 'katalog' ? 'is-active' : '' }}">Katalog</a>

            <span class="hdr-dropdown">
                <button type="button" class="hdr-dropdown__btn" aria-haspopup="true">
                    Kategori
                    <img class="hdr-nav__caret" src="{{ asset('images/figma/home/ico-arrow-down.svg') }}" alt="">
                </button>
                <span class="hdr-dropdown__menu">
                    @foreach ($kategoriNav as $k)
                        <a href="{{ route('katalog') }}?kategori%5B%5D={{ $k->id_kategori }}">{{ $k->nama_kategori }}</a>
                    @endforeach
                </span>
            </span>

            <a href="{{ route('pesanan.index') }}" class="{{ $active === 'pesanan' ? 'is-active' : '' }}">Pesanan Saya</a>
        </nav>

        <div class="hdr-actions">
            @auth
                @if (! auth()->user()->isPenjual())
                    <a href="{{ route('keranjang.index') }}" class="hdr-cart" aria-label="Keranjang">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                            <path d="M3 6h18"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        @if (auth()->user()->keranjangCount() > 0)
                            <span class="hdr-cart__count">{{ auth()->user()->keranjangCount() }}</span>
                        @endif
                    </a>
                @endif
            @endauth

            @auth
                <a href="{{ auth()->user()->isPenjual() ? route('admin.home') : route('home') }}" class="hdr-cta">{{ $nama }}</a>
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
        <a href="{{ route('katalog') }}" class="{{ $active === 'katalog' ? 'is-active' : '' }}">Katalog</a>
        <a href="{{ route('katalog') }}">Kategori</a>
        <a href="{{ route('pesanan.index') }}" class="{{ $active === 'pesanan' ? 'is-active' : '' }}">Pesanan Saya</a>
    </nav>
</header>
