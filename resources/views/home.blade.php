@extends('layouts.site')

@section('title', 'Beranda')
@section('active', 'beranda')
@section('main_class', 'site-main--flush')

@section('content')
    <section class="hero">
        <div class="hero__bg">
            <img src="{{ asset('images/figma/home/hero-bg.jpg') }}" alt="Koleksi Gavin Fashion Store">
        </div>
        <div class="hero__veil" aria-hidden="true"></div>
        <span class="hero__glow" aria-hidden="true"></span>

        <div class="hero__content">
            <span class="hero__badge">
                <i aria-hidden="true"></i>
                <span>Edisi Spesial 2025 &bull; Koleksi Terbaru</span>
            </span>

            <h1>Tampil Percaya Diri dengan<br>Sentuhan Elegan Modern</h1>

            <p class="hero__sub">
                Eksplorasi pakaian esensial dan potongan kontemporer Gavin Fashion Store.
                Dibuat dengan material premium untuk kenyamanan gaya sehari-hari.
            </p>

            <div class="hero__cta-row">
                <a href="#" class="hero__cta">
                    Belanja Sekarang
                    <img src="{{ asset('images/figma/home/ico-chevron-white.svg') }}" alt="">
                </a>
            </div>
        </div>
    </section>

    <section class="cats wrap">
        <div class="cats__head">
            <div>
                <h2 class="cats__title">Kategori Pilihan</h2>
                <p class="cats__sub">Temukan gaya yang tepat untuk setiap momen Anda</p>
            </div>
        </div>

        <div class="cats__row">
            @foreach ($categories as $category)
                <a href="#" class="cat-chip">
                    <span class="cat-chip__circle">
                        <img src="{{ asset('images/figma/home/' . $category['image']) }}" alt="{{ $category['label'] }}">
                    </span>
                    <span class="cat-chip__label">{{ $category['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="products wrap">
        <div class="products__head">
            <h2 class="products__title">Produk Unggulan</h2>
            <a href="#" class="products__link">
                Lihat semua
                <img src="{{ asset('images/figma/home/ico-arrow-right.svg') }}" alt="">
            </a>
        </div>

        <div class="products__grid">
            @foreach ($products as $product)
                <a href="#" class="pcard">
                    <span class="pcard__media">
                        <img src="{{ asset('images/figma/home/' . $product['image']) }}" alt="{{ $product['name'] }}">
                    </span>
                    <span class="pcard__cat">{{ $product['category'] }}</span>
                    <span class="pcard__name">{{ $product['name'] }}</span>
                    <span class="pcard__rating">
                        <img src="{{ asset('images/figma/home/ico-star-1.svg') }}" alt="">
                        <b>{{ $product['rating'] }}</b>
                        <span>({{ $product['reviews'] }})</span>
                    </span>
                    <span class="pcard__price">Rp {{ number_format($product['price'], 0, ',', '.') }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endsection
