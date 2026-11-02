@php
    $active = $active ?? '';
    $penjual = auth()->user();
    $inisial = \Illuminate\Support\Str::of($penjual?->nama ?? 'Admin')->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $jumlahVerifikasi = \App\Models\Pesanan::where('status', \App\Models\Pesanan::MENUNGGU_VERIFIKASI)->count();
    $menu = [
        ['kunci' => 'dashboard', 'label' => 'Dashboard', 'route' => route('admin.dashboard'), 'icon' => 'grid'],
        ['kunci' => 'produk', 'label' => 'Kelola Produk & Kategori', 'route' => route('admin.produk.index'), 'icon' => 'box'],
        ['kunci' => 'stok', 'label' => 'Kelola Stok & Ukuran', 'route' => route('admin.stok.index'), 'icon' => 'layers'],
        ['kunci' => 'pesanan', 'label' => 'Pesanan & Invoice', 'route' => route('admin.pesanan.index'), 'icon' => 'receipt'],
        ['kunci' => 'kirim', 'label' => 'Proses Pengiriman', 'route' => route('admin.kirim.index'), 'icon' => 'truck'],
        ['kunci' => 'verifikasi', 'label' => 'Verifikasi Bayar', 'route' => route('admin.verifikasi.index'), 'icon' => 'check', 'badge' => $jumlahVerifikasi],
        ['kunci' => 'ulasan', 'label' => 'Lihat Ulasan', 'route' => route('admin.ulasan.index'), 'icon' => 'star'],
        ['kunci' => 'laporan', 'label' => 'Laporan Omzet', 'route' => route('admin.laporan.index'), 'icon' => 'chart'],
    ];
@endphp

<aside class="ad-side">
    <div class="ad-side__brand">
        <span class="ad-side__mark">G</span>
        <div>
            <div class="ad-side__name">Gavin Fashion Store</div>
            <div class="ad-side__role">Admin Seller</div>
        </div>
    </div>

    <nav class="ad-nav" aria-label="Navigasi penjual">
        @foreach ($menu as $item)
            <a href="{{ $item['route'] }}" class="ad-nav__link {{ $active === $item['kunci'] ? 'is-active' : '' }}">
                @include('admin.partials.icon', ['name' => $item['icon']])
                <span>{{ $item['label'] }}</span>
                @if (! empty($item['badge']))
                    <span class="ad-nav__badge">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="ad-side__user">
        <span class="ad-side__avatar">{{ strtoupper($inisial) }}</span>
        <div>
            <div class="ad-side__user-name">{{ $penjual?->nama ?? 'Admin Gavin' }}</div>
            <div class="ad-side__user-role">Admin</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ad-side__logout" title="Keluar" aria-label="Keluar">
                @include('admin.partials.icon', ['name' => 'logout'])
            </button>
        </form>
    </div>
</aside>