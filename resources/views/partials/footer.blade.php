<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__grid">
            <!-- Col 1: Brand & Desc -->
            <div class="site-footer__col-brand">
                <span class="footer-brand-title">GAVIN FASHION STORE</span>
                <p class="footer-tagline">Temukan gaya terbaikmu, pakaian pilihan untuk gaya sehari-hari.</p>
                <p class="footer-subtext">Kurasi busana kontemporer esensial dengan sentuhan eksklusif dan material premium untuk kepuasan berbusana modern.</p>
            </div>

            <!-- Col 2: Belanja & Kategori -->
            <div class="site-footer__col">
                <h4 class="footer-heading">BELANJA &amp; KATEGORI</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('home') }}#produk-unggulan">Katalog</a></li>
                    <li><a href="{{ auth()->check() ? '#' : route('login') }}">Pesanan Saya</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Atasan</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Bawahan</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Outer</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Dress</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Aksesoris</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Hijab</a></li>
                    <li><a href="{{ route('home') }}#kategori-pilihan">Sepatu</a></li>
                </ul>
            </div>

            <!-- Col 3: Informasi Pesanan -->
            <div class="site-footer__col">
                <h4 class="footer-heading">INFORMASI PESANAN</h4>
                <ul class="footer-info">
                    <li>Pembayaran Via Transfer 1x24 jam</li>
                    <li>Tidak Melayani COD</li>
                    <li>Pengiriman JNE, J&amp;T, GoSend, GoShop</li>
                </ul>
            </div>

            <!-- Col 4: Hubungi Kami -->
            <div class="site-footer__col">
                <h4 class="footer-heading">HUBUNGI KAMI:</h4>
                <p class="footer-contact-desc">untuk konfirmasi pembayaran dan pertanyaan pesanan</p>
                <div class="footer-phone-box">
                    <span>0812-1234-1234-1234</span>
                </div>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>&copy; 2025 Gavin Fashion Store. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</footer>
