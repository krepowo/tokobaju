@php
    $nav = $nav ?? 'dashboard';
    $badgeBayar = $badgeBayar ?? \App\Models\Pembayaran::query()->where('status_verifikasi', 'menunggu')->count();

    $menu = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
        ['key' => 'produk', 'label' => 'Kelola Produk, Kategori & Pengiriman', 'icon' => 'tag'],
        ['key' => 'stok', 'label' => 'Kelola Stok & Ukuran', 'icon' => 'layers'],
        ['key' => 'pesanan', 'label' => 'Pesanan & Invoice', 'icon' => 'doc'],
        ['key' => 'kirim', 'label' => 'Proses Pengiriman', 'icon' => 'truck'],
        ['key' => 'bayar', 'label' => 'Verifikasi Bayar', 'icon' => 'shield', 'badge' => $badgeBayar],
        ['key' => 'ulasan', 'label' => 'Lihat Ulasan', 'icon' => 'star'],
        ['key' => 'omzet', 'label' => 'Laporan Omzet', 'icon' => 'chart'],
    ];

    $inisial = '';
    foreach (preg_split('/\s+/', trim(auth()->user()->nama ?? 'Admin Gavin')) as $kata) {
        if ($kata !== '') {
            $inisial .= mb_strtoupper(mb_substr($kata, 0, 1));
        }
        if (mb_strlen($inisial) >= 2) {
            break;
        }
    }
@endphp

<aside class="admin-sidebar">
    <nav class="admin-nav" aria-label="Navigasi penjual">
        @foreach ($menu as $item)
            @php
                $rute = [
                    'dashboard' => 'admin.dashboard',
                    'produk' => 'admin.produk.index',
                    'stok' => 'admin.stok.index',
                    'pesanan' => 'admin.pesanan.index',
                    'kirim' => 'admin.pengiriman.index',
                    'bayar' => 'admin.verifikasi.index',
                    'ulasan' => 'admin.ulasan.index',
                    'omzet' => 'admin.laporan.index',
                ][$item['key']] ?? null;

                $aktif = $nav === $item['key']
                    || ($item['key'] === 'produk' && in_array($nav, ['kategori', 'jasa'], true));
                $punyaRoute = $rute !== null;
            @endphp

            @if ($punyaRoute)
                <a href="{{ route($rute) }}"
                   class="admin-nav__item {{ $aktif ? 'is-active' : '' }}"
                   @if ($aktif) aria-current="page" @endif>
            @else
                <span class="admin-nav__item is-soon {{ $aktif ? 'is-active' : '' }}" title="Segera hadir">
            @endif

                @switch($item['icon'])
                    @case('grid')
                        <svg viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.6"/></svg>
                        @break
                    @case('tag')
                        <svg viewBox="0 0 24 24"><path d="M20.6 13.4 12 22l-9-9V4h9l8.6 8.6a1.4 1.4 0 0 1 0 2Z"/><circle cx="7.6" cy="8.6" r="1.3"/></svg>
                        @break
                    @case('layers')
                        <svg viewBox="0 0 24 24"><path d="m12 3.5 8.5 4.4L12 12.3 3.5 7.9 12 3.5Z"/><path d="m3.5 12.3 8.5 4.4 8.5-4.4"/><path d="m3.5 16.6 8.5 4.4 8.5-4.4"/></svg>
                        @break
                    @case('doc')
                        <svg viewBox="0 0 24 24"><path d="M14 3H7a1.6 1.6 0 0 0-1.6 1.6v14.8A1.6 1.6 0 0 0 7 21h10a1.6 1.6 0 0 0 1.6-1.6V7.6L14 3Z"/><path d="M14 3v4.6h4.6"/><path d="M8.8 13h6.4M8.8 16.6h4.2"/></svg>
                        @break
                    @case('truck')
                        <svg viewBox="0 0 24 24"><path d="M2.8 6.5h9.4v9.7H2.8z"/><path d="M12.2 9.8h4l3 3v3.4h-7z"/><circle cx="6.4" cy="18.2" r="1.6"/><circle cx="16.2" cy="18.2" r="1.6"/></svg>
                        @break
                    @case('shield')
                        <svg viewBox="0 0 24 24"><path d="M12 3.2 19 6v6c0 4-3 7.2-7 8.8C8 19.2 5 16 5 12V6l7-2.8Z"/><path d="m9.2 12 2 2 3.6-3.8"/></svg>
                        @break
                    @case('star')
                        <svg viewBox="0 0 24 24"><path d="m12 4 2.5 5.1 5.5.8-4 3.9 1 5.5L12 16.7 7 19.3l1-5.5-4-3.9 5.5-.8L12 4Z"/></svg>
                        @break
                    @case('chart')
                        <svg viewBox="0 0 24 24"><path d="M4 20V9.5M10 20V4M16 20v-6.5M22 20H2"/></svg>
                        @break
                @endswitch

                <span class="admin-nav__label">{{ $item['label'] }}</span>

                @if (! empty($item['badge']))
                    <span class="admin-nav__badge">{{ $item['badge'] }}</span>
                @endif

            @if ($punyaRoute)
                </a>
            @else
                </span>
            @endif
        @endforeach
    </nav>

    <div class="admin-user">
        <span class="admin-user__avatar">{{ $inisial ?: 'AG' }}</span>
        <span class="admin-user__meta">
            <span class="admin-user__name">{{ auth()->user()->nama ?? 'Admin Gavin' }}</span>
            <span class="admin-user__status"><i></i> Online</span>
        </span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="admin-user__logout" title="Keluar" aria-label="Keluar">
                <svg viewBox="0 0 24 24"><path d="M14.5 8.5V6a1.5 1.5 0 0 0-1.5-1.5H6A1.5 1.5 0 0 0 4.5 6v12A1.5 1.5 0 0 0 6 19.5h7a1.5 1.5 0 0 0 1.5-1.5v-2.5"/><path d="M10 12h9.5"/><path d="M16.5 8.8 19.7 12l-3.2 3.2"/></svg>
            </button>
        </form>
    </div>
</aside>
