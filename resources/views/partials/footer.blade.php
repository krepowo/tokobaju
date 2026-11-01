@php
    $shopLinks = ['Beranda', 'Katalog', 'Pesanan Saya', 'Atasan', 'Bawahan', 'Outer', 'Dress', 'Aksesoris', 'Hijab', 'Sepatu'];
    $orderInfo = [
        'Pembayaran Via Transfer 1x24 jam',
        'Tidak Melayani COD',
        'Pengiriman JNE, J&T, GoSend, GoShop',
    ];
@endphp

<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__brand">
            <span class="site-footer__wordmark">Gavin Fashion<br>Store</span>
            <p class="site-footer__tagline">Temukan gaya terbaikmu, pakaian pilihan untuk gaya sehari-hari.</p>
            <p class="site-footer__note">
                Kurasi busana kontemporer esensial<br>
                dengan sentuhan eksklusif dan material<br>
                premium untuk kepuasan berbusana<br>
                modern.
            </p>
        </div>

        <div class="site-footer__col">
            <h4>Belanja &amp; Kategori</h4>
            <ul>
                @foreach ($shopLinks as $link)
                    <li><a href="{{ $link === 'Beranda' ? route('home') : '#' }}">{{ $link }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="site-footer__col">
            <h4>Informasi Pesanan</h4>
            <ul>
                @foreach ($orderInfo as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>

        <div class="site-footer__col">
            <h4>Hubungi kami:</h4>
            <p class="site-footer__help">untuk konfirmasi pembayaran dan pertanyaan pesanan</p>
            <p class="site-footer__phone">089123-1234-1234-1234</p>
        </div>

        <p class="site-footer__copy">&copy; {{ date('Y') }} Gavin Fashion Store. Hak Cipta Dilindungi.</p>
    </div>
</footer>
