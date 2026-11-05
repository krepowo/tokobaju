@extends('layouts.site')

@section('title', 'Beranda')
@section('active', 'beranda')
@section('main-class', 'site-main--home')

@section('content')
    <!-- ======================================================== HERO SECTION ==== -->
    <section class="hero-fashion">
        <div class="hero-fashion__overlay">
            <div class="hero-fashion__container">
                <div class="hero-fashion__content">
                    <!-- Special Edition Pill Badge -->
                    <div class="hero-fashion__badge">
                        <span class="hero-fashion__badge-dot"></span>
                        <span>EDISI SPESIAL 2025 &bull; KOLEKSI TERBARU</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="hero-fashion__title">
                        Tampil Percaya Diri dengan<br>
                        Sentuhan Elegan Modern
                    </h1>

                    <!-- Subtitle -->
                    <p class="hero-fashion__subtitle">
                        Eksplorasi pakaian esensial dan potongan kontemporer Gavin Fashion
                        Store. Dibuat dengan material premium untuk kenyamanan gaya sehari-hari.
                    </p>

                    <!-- CTA Button -->
                    <a href="{{ route('katalog') }}" class="hero-fashion__cta">
                        <span>Belanja Sekarang</span>
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================== KATEGORI PILIHAN SECTION ==== -->
    <section class="category-showcase" id="kategori-pilihan">
        <div class="category-showcase__container">
            <div class="category-showcase__header">
                <h2 class="category-showcase__title">Kategori Pilihan</h2>
                <p class="category-showcase__subtitle">Temukan gaya yang tepat untuk setiap momen Anda</p>
            </div>

            <div class="category-showcase__grid">
                @forelse ($kategori as $cat)
                    <a href="{{ route('katalog', ['kategori' => $cat->nama_kategori]) }}" class="category-card">
                        <div class="category-card__circle">
                            <img src="{{ $cat->gambar_url }}" alt="{{ $cat->nama_kategori }}" loading="lazy">
                        </div>
                        <span class="category-card__label">{{ $cat->nama_kategori }}</span>
                    </a>
                @empty
                    <p style="color: #a8a29e; text-align: center; grid-column: 1 / -1;">Belum ada kategori.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- =============================================== PRODUK UNGGULAN SECTION ==== -->
    <section class="featured-products" id="produk-unggulan">
        <div class="featured-products__container">
            <div class="featured-products__header">
                <h2 class="featured-products__title">Produk Unggulan</h2>
                <a href="{{ route('katalog') }}" class="featured-products__more">
                    <span>Lihat semua</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.25 5.25a.75.75 0 010 1.08l-5.25 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <div class="featured-products__grid">
                @forelse ($produkUnggulan as $prod)
                    <a href="{{ route('produk.show', $prod->id_produk) }}" class="product-item">
                        <div class="product-item__thumb">
                            <img src="{{ $prod->gambar_url }}" alt="{{ $prod->nama_produk }}" loading="lazy">
                        </div>
                        <div class="product-item__info">
                            <span class="product-item__category">{{ strtoupper($prod->kategori?->nama_kategori ?? 'FASHION') }}</span>
                            <h3 class="product-item__name">{{ $prod->nama_produk }}</h3>
                            <div class="product-item__rating">
                                @if ($prod->total_ulasan > 0)
                                    <span class="product-item__star" aria-hidden="true">&#9733;</span>
                                    <span class="product-item__score">{{ number_format($prod->rating_rata_rata, 1, ',', '.') }}</span>
                                    <span class="product-item__count">({{ $prod->total_ulasan }})</span>
                                @else
                                    <span class="product-item__count">Belum ada ulasan</span>
                                @endif
                            </div>
                            <div class="product-item__price">{{ $prod->harga_formatted }}</div>
                        </div>
                    </a>
                @empty
                    <p style="color: #a8a29e; text-align: center; grid-column: 1 / -1; padding: 2rem 0;">Belum ada produk yang tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
