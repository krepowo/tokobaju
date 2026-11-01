@extends('layouts.site')

@section('title', 'Katalog Produk')
@section('active', 'katalog')
@section('main_class', 'site-main--catalog')

@section('content')
<div class="catalog">
    <div class="catalog__head">
        <div class="catalog__title">
            <h1>Katalog Produk</h1>
            <p>Menampilkan produk busana pilihan</p>
        </div>

        <form method="GET" action="{{ route('katalog') }}" class="catalog__bar">
            @foreach ($kategoriAktif as $k)
                <input type="hidden" name="kategori[]" value="{{ $k }}">
            @endforeach
            @foreach ($ukuranAktif as $u)
                <input type="hidden" name="ukuran[]" value="{{ $u }}">
            @endforeach
            @if ($hargaMin !== null && $hargaMin !== '')
                <input type="hidden" name="harga_min" value="{{ $hargaMin }}">
            @endif
            @if ($hargaMax !== null && $hargaMax !== '')
                <input type="hidden" name="harga_max" value="{{ $hargaMax }}">
            @endif

            <label class="catalog__search">
                <img src="{{ asset('images/figma/home/ico-search.svg') }}" alt="">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari busana, aksesoris...">
            </label>

            <label class="catalog__sort">
                <span>Urutkan:</span>
                <select name="sort" onchange="this.form.submit()">
                    <option value="terbaru" @selected($sort === 'terbaru')>Terbaru</option>
                    <option value="harga_terendah" @selected($sort === 'harga_terendah')>Harga Terendah</option>
                    <option value="harga_tertinggi" @selected($sort === 'harga_tertinggi')>Harga Tertinggi</option>
                </select>
            </label>
        </form>
    </div>

    <div class="catalog__layout">
        <aside class="catalog__filter">
            <form method="GET" action="{{ route('katalog') }}">
                @if ($q !== '')
                    <input type="hidden" name="q" value="{{ $q }}">
                @endif
                <input type="hidden" name="sort" value="{{ $sort }}">

                <div class="catalog__filter-head">
                    <h2>Filter</h2>
                    <a href="{{ route('katalog') }}" class="catalog__reset">Reset</a>
                </div>

                <div class="catalog__group">
                    <h3>Kategori</h3>
                    @foreach ($kategori as $k)
                        <label class="catalog__check">
                            <input type="checkbox" name="kategori[]" value="{{ $k->nama_kategori }}"
                                   @checked(in_array($k->nama_kategori, $kategoriAktif))>
                            <span>{{ $k->nama_kategori }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="catalog__group">
                    <h3>Ukuran</h3>
                    <div class="catalog__sizes">
                        @foreach ($ukuranSemua as $u)
                            <label class="catalog__size @if (in_array($u, $ukuranAktif)) is-active @endif">
                                <input type="checkbox" name="ukuran[]" value="{{ $u }}" @checked(in_array($u, $ukuranAktif))>
                                <span>{{ $u }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="catalog__group catalog__group--last">
                    <h3>Rentang Harga</h3>
                    <label class="catalog__price">
                        <span>Harga Minimal</span>
                        <span class="catalog__price-row"><i>Rp</i><input type="number" name="harga_min" value="{{ $hargaMin }}" min="0" placeholder="0"></span>
                    </label>
                    <label class="catalog__price">
                        <span>Harga Maksimal</span>
                        <span class="catalog__price-row"><i>Rp</i><input type="number" name="harga_max" value="{{ $hargaMax }}" min="0" placeholder="1000000"></span>
                    </label>
                    <button type="submit" class="catalog__apply">Terapkan</button>
                </div>
            </form>
        </aside>

        <section class="catalog__grid">
            @forelse ($products as $p)
                <article class="ccard">
                    <a href="{{ route('produk.detail', $p['id']) }}" class="ccard__link" aria-label="Lihat {{ $p['name'] }}"></a>
                    <div class="ccard__media">
                        <img src="{{ asset($p['image']) }}" alt="{{ $p['name'] }}" loading="lazy">
                        @if ($p['stok_habis'])
                            <span class="ccard__badge ccard__badge--habis">Stok Habis</span>
                        @elseif ($p['reviews'] === 0)
                            <span class="ccard__badge">Baru</span>
                        @endif
                    </div>
                    <p class="ccard__cat">{{ $p['category'] }}</p>
                    <h3 class="ccard__name">{{ $p['name'] }}</h3>
                    <div class="ccard__rating">
                        @if ($p['reviews'] > 0)
                            <img src="{{ asset('images/figma/home/ico-star-1.svg') }}" alt="">
                            <b>{{ $p['rating'] }}</b>
                            <span>({{ $p['reviews'] }})</span>
                        @else
                            <em>Belum ada ulasan</em>
                        @endif
                    </div>
                    <p class="ccard__price">Rp {{ number_format($p['price'], 0, ',', '.') }}</p>
                </article>
            @empty
                <p class="catalog__empty">Tidak ada produk yang cocok dengan filter yang dipilih.</p>
            @endforelse
        </section>
    </div>
</div>
@endsection
