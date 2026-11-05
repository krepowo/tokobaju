@extends('layouts.site')

@section('title', 'Katalog Produk')
@section('active', 'katalog')
@section('main-class', 'site-main--full')

@section('content')
<div class="katalog-page">
    <div class="katalog-container">
        <!-- Header & Top Search / Sort Bar -->
        <div class="katalog-header-row">
            <div class="katalog-header-title">
                <h1 class="katalog-title">Katalog Produk</h1>
                <p class="katalog-subtitle">Menampilkan produk busana pilihan</p>
            </div>

            <div class="katalog-header-controls">
                <!-- Search Box -->
                <form action="{{ route('katalog') }}" method="GET" class="katalog-search-form" id="katalogSearchForm">
                    <!-- Pertahankan filter kategori & ukuran jika ada -->
                    @if (request('kategori'))
                        @foreach ((array) request('kategori') as $kat)
                            <input type="hidden" name="kategori[]" value="{{ $kat }}">
                        @endforeach
                    @endif
                    @if (request('ukuran'))
                        @foreach ((array) request('ukuran') as $uk)
                            <input type="hidden" name="ukuran[]" value="{{ $uk }}">
                        @endforeach
                    @endif
                    @if (request('min_harga'))
                        <input type="hidden" name="min_harga" value="{{ request('min_harga') }}">
                    @endif
                    @if (request('max_harga'))
                        <input type="hidden" name="max_harga" value="{{ request('max_harga') }}">
                    @endif
                    <input type="hidden" name="urutan" value="{{ request('urutan', 'terbaru') }}">

                    <div class="katalog-search-input-wrapper">
                        <input type="text"
                               name="cari"
                               value="{{ request('cari') }}"
                               placeholder="Cari busana, aksesoris..."
                               class="katalog-search-input"
                               aria-label="Cari busana atau aksesoris">
                    </div>
                </form>

                <!-- Sort Dropdown -->
                <div class="katalog-sort-wrapper">
                    <label for="urutanSelect" class="katalog-sort-label">Urutkan:</label>
                    <div class="katalog-select-container">
                        <select id="urutanSelect" class="katalog-sort-select" onchange="applySort(this.value)">
                            <option value="terbaru" {{ request('urutan', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                            <option value="harga_asc" {{ request('urutan') === 'harga_asc' ? 'selected' : '' }}>Harga Terendah</option>
                            <option value="harga_desc" {{ request('urutan') === 'harga_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                            <option value="populer" {{ request('urutan') === 'populer' ? 'selected' : '' }}>Rating Tertinggi</option>
                        </select>
                        <svg class="katalog-select-arrow" viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body: Sidebar Filters + Products Grid -->
        <div class="katalog-body">
            <!-- Left Sidebar Filter -->
            <aside class="katalog-sidebar">
                <form action="{{ route('katalog') }}" method="GET" id="katalogFilterForm" class="filter-card">
                    @if (request('cari'))
                        <input type="hidden" name="cari" value="{{ request('cari') }}">
                    @endif
                    <input type="hidden" name="urutan" id="hiddenUrutan" value="{{ request('urutan', 'terbaru') }}">

                    <div class="filter-card__header">
                        <span class="filter-card__title">Filter</span>
                        <a href="{{ route('katalog') }}" class="filter-card__reset">Reset</a>
                    </div>

                    <!-- Filter Kategori -->
                    <div class="filter-group">
                        <h3 class="filter-group__title">Kategori</h3>
                        <div class="filter-group__list">
                            @php
                                $selectedCategories = (array) request('kategori', []);
                            @endphp
                            @foreach ($kategoriList as $kat)
                                <label class="filter-checkbox-label">
                                    <input type="checkbox"
                                           name="kategori[]"
                                           value="{{ $kat->nama_kategori }}"
                                           {{ in_array($kat->nama_kategori, $selectedCategories) ? 'checked' : '' }}
                                           onchange="document.getElementById('katalogFilterForm').submit()"
                                           class="filter-checkbox">
                                    <span class="filter-checkbox-text">{{ $kat->nama_kategori }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filter Ukuran -->
                    <div class="filter-group">
                        <h3 class="filter-group__title">Ukuran</h3>
                        <div class="filter-size-chips">
                            @php
                                $selectedSizes = (array) request('ukuran', []);
                            @endphp
                            @foreach ($availableSizes as $sz)
                                <label class="size-chip {{ in_array($sz, $selectedSizes) ? 'is-active' : '' }}">
                                    <input type="checkbox"
                                           name="ukuran[]"
                                           value="{{ $sz }}"
                                           {{ in_array($sz, $selectedSizes) ? 'checked' : '' }}
                                           onchange="document.getElementById('katalogFilterForm').submit()"
                                           class="sr-only">
                                    <span>{{ $sz }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filter Rentang Harga -->
                    <div class="filter-group">
                        <h3 class="filter-group__title">Rentang Harga</h3>
                        <div class="filter-price-inputs">
                            <div class="filter-price-field">
                                <span class="price-prefix">Rp</span>
                                <input type="number"
                                       name="min_harga"
                                       value="{{ request('min_harga') }}"
                                       placeholder="Harga Minimal"
                                       class="price-input"
                                       min="0"
                                       step="10000">
                            </div>
                            <div class="filter-price-field">
                                <span class="price-prefix">Rp</span>
                                <input type="number"
                                       name="max_harga"
                                       value="{{ request('max_harga') }}"
                                       placeholder="Harga Maksimal"
                                       class="price-input"
                                       min="0"
                                       step="10000">
                            </div>
                        </div>
                        <button type="submit" class="btn-filter-apply">Terapkan</button>
                    </div>
                </form>
            </aside>

            <!-- Right Products Grid -->
            <main class="katalog-grid-container">
                @if ($produk->isEmpty())
                    <!-- Empty State sesuai Aturan Bisnis #3 -->
                    <div class="katalog-empty-state">
                        <div class="katalog-empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                                <path d="M8 11h6"/>
                            </svg>
                        </div>
                        <h2 class="katalog-empty-title">Produk tidak ditemukan</h2>
                        <p class="katalog-empty-desc">
                            Maaf, tidak ada produk busana yang sesuai dengan kata kunci atau filter yang Anda pilih.
                        </p>
                        <a href="{{ route('katalog') }}" class="katalog-empty-btn">Lihat Semua Produk</a>
                    </div>
                @else
                    <div class="katalog-product-grid">
                        @foreach ($produk as $item)
                            <a href="{{ route('produk.show', $item->id_produk) }}" class="katalog-card">
                                <div class="katalog-card__thumb">
                                    <img src="{{ $item->gambar_url }}" alt="{{ $item->nama_produk }}" loading="lazy">
                                </div>
                                <div class="katalog-card__body">
                                    <span class="katalog-card__category">{{ strtoupper($item->kategori?->nama_kategori ?? 'FASHION') }}</span>
                                    <h3 class="katalog-card__name">{{ $item->nama_produk }}</h3>
                                    <div class="katalog-card__rating">
                                        @if ($item->total_ulasan > 0)
                                            <span class="katalog-card__star" aria-hidden="true">&#9733;</span>
                                            <span class="katalog-card__score">{{ number_format($item->rating_rata_rata, 1, ',', '.') }}</span>
                                            <span class="katalog-card__count">({{ $item->total_ulasan }})</span>
                                        @else
                                            <span class="katalog-card__count">Belum ada ulasan</span>
                                        @endif
                                    </div>
                                    <div class="katalog-card__price">{{ $item->harga_formatted }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($produk->hasPages())
                        <div class="katalog-pagination">
                            {{ $produk->links() }}
                        </div>
                    @endif
                @endif
            </main>
        </div>
    </div>
</div>

<script>
    function applySort(sortValue) {
        const url = new URL(window.location.href);
        url.searchParams.set('urutan', sortValue);
        window.location.href = url.toString();
    }
</script>
@endsection
