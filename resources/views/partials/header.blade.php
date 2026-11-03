@php
    $active = $active ?? '';
    $cartCount = auth()->check() ? auth()->user()->keranjang()->count() : 0;
    try {
        $kategoriNav = \App\Models\Kategori::orderBy('nama_kategori')->get();
    } catch (\Throwable $e) {
        $kategoriNav = collect([]);
    }
@endphp

<header class="site-header">
    <div class="site-header__inner">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="site-header__logo" aria-label="Gavin Fashion Store">
            @include('partials.brand', ['variant' => 'light'])
        </a>

        <!-- Center Nav -->
        <nav class="site-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="{{ $active === 'beranda' ? 'is-active' : '' }}">Beranda</a>
            <a href="{{ route('katalog') }}" class="{{ $active === 'katalog' ? 'is-active' : '' }}">Katalog</a>

            <!-- Kategori Dropdown -->
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown__btn">
                    Kategori
                    <svg class="nav-dropdown__arrow" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div class="nav-dropdown__menu">
                    @forelse ($kategoriNav as $kat)
                        <a href="{{ route('katalog', ['kategori' => $kat->nama_kategori]) }}">{{ $kat->nama_kategori }}</a>
                    @empty
                        <a href="{{ route('katalog', ['kategori' => 'Atasan']) }}">Atasan</a>
                        <a href="{{ route('katalog', ['kategori' => 'Bawahan']) }}">Bawahan</a>
                        <a href="{{ route('katalog', ['kategori' => 'Outer']) }}">Outer</a>
                        <a href="{{ route('katalog', ['kategori' => 'Dress']) }}">Dress</a>
                        <a href="{{ route('katalog', ['kategori' => 'Aksesori']) }}">Aksesori</a>
                        <a href="{{ route('katalog', ['kategori' => 'Hijab']) }}">Hijab</a>
                        <a href="{{ route('katalog', ['kategori' => 'Sepatu']) }}">Sepatu</a>
                    @endforelse
                </div>
            </div>

            <a href="{{ auth()->check() ? route('pesanan.index') : route('login') }}" class="{{ $active === 'pesanan' ? 'is-active' : '' }}">Pesanan Saya</a>
        </nav>

        <!-- Right Actions -->
        <div class="site-header__actions">
            <!-- Shopping bag icon with Badge -->
            <a href="{{ auth()->check() ? route('keranjang.index') : route('login') }}"
               class="icon-btn-gold {{ $active === 'keranjang' ? 'is-active' : '' }}"
               title="Keranjang Belanja"
               aria-label="Keranjang Belanja">
                <div class="cart-icon-container">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="20" height="20">
                        <path d="M6 8h12l-1.2 11.2a1.6 1.6 0 0 1-1.6 1.4H8.8a1.6 1.6 0 0 1-1.6-1.4L6 8Z"/>
                        <path d="M9.2 8V6.4a2.8 2.8 0 0 1 5.6 0V8"/>
                    </svg>
                    @if ($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount }}</span>
                    @endif
                </div>
            </a>

            @auth
                <!-- User Profile Pill & Actions -->
                <div class="user-dropdown">
                    <button type="button" class="btn-auth-gold">
                        {{ auth()->user()->nama ?? auth()->user()->name }}
                    </button>
                    <div class="user-dropdown__menu">
                        <div class="user-dropdown__header">
                            <strong>{{ auth()->user()->nama ?? auth()->user()->name }}</strong>
                            <span class="user-dropdown__role">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                        @if (auth()->user()->isPenjual())
                            <a href="{{ route('admin.dashboard') }}">Halaman Penjual</a>
                        @endif
                        <a href="{{ route('keranjang.index') }}">Keranjang Belanja</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="user-dropdown__logout">Keluar</button>
                        </form>
                    </div>
                </div>

                <div class="user-avatar-btn" title="Akun Saya">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                </div>
            @else
                <!-- Masuk / Daftar Button -->
                <a href="{{ route('login') }}" class="btn-auth-gold">
                    Masuk / Daftar
                </a>

                <a href="{{ route('login') }}" class="user-avatar-btn" title="Masuk">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endauth
        </div>
    </div>
</header>
