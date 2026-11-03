@extends('layouts.site')

@section('title', $produk->nama_produk)
@section('active', 'katalog')
@section('main-class', 'site-main--full')

@section('content')
<div class="product-detail-page">
    <div class="product-detail-container">
        <!-- Toast Notification for Add to Cart -->
        <div id="cartToast" class="cart-toast" style="display: none;">
            <div class="cart-toast__content">
                <svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20" class="cart-toast__icon">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <span id="cartToastMessage">Produk berhasil ditambahkan ke keranjang!</span>
            </div>
            <a href="{{ route('keranjang.index') }}" class="cart-toast__link">Lihat Keranjang</a>
        </div>

        @if (session('error'))
            <div class="alert-box alert-box--error">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert-box alert-box--success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Top Section: Visual Gallery + Buying Options -->
        <div class="product-hero-grid">
            <!-- Left: Gallery -->
            <div class="product-gallery">
                <div class="product-gallery__main">
                    <img id="mainProductImage"
                         src="{{ $gallery[0]['full'] ?? $produk->gambar_url }}"
                         alt="{{ $produk->nama_produk }}">
                </div>

                @if (count($gallery) > 1)
                    <div class="product-gallery__thumbs">
                        @foreach ($gallery as $idx => $g)
                            <button type="button"
                                    class="gallery-thumb {{ $idx === 0 ? 'is-active' : '' }}"
                                    onclick="switchMainImage('{{ $g['full'] }}', this, '{{ $g['color'] ?? '' }}')">
                                <img src="{{ $g['thumb'] }}" alt="{{ $g['label'] }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Product Information & Purchase Form -->
            <div class="product-info-panel">
                <div class="product-category-tag">
                    <a href="{{ route('katalog', ['kategori' => $produk->kategori?->nama_kategori]) }}">
                        {{ strtoupper($produk->kategori?->nama_kategori ?? 'FASHION') }}
                    </a>
                </div>

                <h1 class="product-info__title">{{ $produk->nama_produk }}</h1>

                <!-- Rating -->
                <div class="product-info__rating">
                    <div class="stars-gold" aria-hidden="true">
                        &#9733;&#9733;&#9733;&#9733;&#9733;
                    </div>
                    <span class="rating-number">{{ number_format($avgRating, 1, ',', '.') }}</span>
                    <span class="rating-count">({{ $effectiveTotal }} ulasan)</span>
                </div>

                <!-- Price -->
                <div class="product-info__price">
                    {{ $produk->harga_formatted }}
                </div>

                <!-- Form Tambah ke Keranjang / Beli Sekarang -->
                <form action="{{ route('keranjang.store') }}" method="POST" id="purchaseForm" class="purchase-form">
                    @csrf
                    <input type="hidden" name="id_ukuran" id="selectedVariantId" value="">
                    <input type="hidden" name="jumlah" id="selectedQuantity" value="1">
                    <input type="hidden" name="beli_langsung" id="beliLangsungField" value="0">

                    <!-- Pilihan Warna -->
                    @if ($colors->isNotEmpty())
                        <div class="form-choice-group">
                            <label class="form-choice-label">Pilihan Warna:</label>
                            <div class="color-chips-row">
                                @foreach ($colors as $cIdx => $warna)
                                    <button type="button"
                                            class="color-chip {{ $cIdx === 0 ? 'is-active' : '' }}"
                                            data-color="{{ $warna }}"
                                            onclick="selectColor('{{ $warna }}', this)">
                                        {{ $warna }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Pilihan Ukuran (Mockup 2: M terpilih by default) -->
                    @if ($sizes->isNotEmpty())
                        @php
                            $defaultSize = $sizes->contains('M') ? 'M' : ($sizes->first() ?? '');
                        @endphp
                        <div class="form-choice-group">
                            <label class="form-choice-label">Pilih Ukuran:</label>
                            <div class="size-chips-row">
                                @foreach ($sizes as $sz)
                                    <button type="button"
                                            class="size-chip-btn {{ $sz === $defaultSize ? 'is-active' : '' }} {{ $sz === 'XXL' ? 'is-disabled' : '' }}"
                                            data-size="{{ $sz }}"
                                            onclick="selectSize('{{ $sz }}', this)">
                                        {{ $sz }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Jumlah & Indikator Stok -->
                    <div class="form-choice-group form-choice-group--quantity">
                        <label class="form-choice-label">Jumlah</label>
                        <div class="quantity-stock-row">
                            <div class="quantity-stepper">
                                <button type="button" class="stepper-btn" onclick="adjustQty(-1)" aria-label="Kurangi jumlah">&minus;</button>
                                <span class="stepper-value" id="qtyDisplay">1</span>
                                <button type="button" class="stepper-btn" onclick="adjustQty(1)" aria-label="Tambah jumlah">&plus;</button>
                            </div>

                            <div class="stock-status" id="stockStatusDisplay">
                                <span class="stock-dot"></span>
                                <span id="stockText">Tersedia 12 pcs</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="product-actions-row">
                        @auth
                            <button type="button" class="btn-add-cart" id="btnAddCart" onclick="handleAddToCart(false)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                                    <path d="M6 8h12l-1.2 11.2a1.6 1.6 0 0 1-1.6 1.4H8.8a1.6 1.6 0 0 1-1.6-1.4L6 8Z"/>
                                    <path d="M9.2 8V6.4a2.8 2.8 0 0 1 5.6 0V8"/>
                                </svg>
                                <span>Tambah ke Keranjang</span>
                            </button>

                            <button type="button" class="btn-buy-now" id="btnBuyNow" onclick="handleAddToCart(true)">
                                Beli Sekarang
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="btn-add-cart">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                                    <path d="M6 8h12l-1.2 11.2a1.6 1.6 0 0 1-1.6 1.4H8.8a1.6 1.6 0 0 1-1.6-1.4L6 8Z"/>
                                    <path d="M9.2 8V6.4a2.8 2.8 0 0 1 5.6 0V8"/>
                                </svg>
                                <span>Tambah ke Keranjang</span>
                            </a>

                            <a href="{{ route('login') }}" class="btn-buy-now">
                                Beli Sekarang
                            </a>
                        @endauth
                    </div>
                </form>
            </div>
        </div>

        <!-- Middle Section: Deskripsi + Spesifikasi & Detail Ukuran -->
        <div class="product-details-split">
            <!-- Left: Deskripsi Produk -->
            <div class="product-description-card">
                <h2 class="section-title">Deskripsi Produk</h2>
                <div class="description-prose">
                    {!! nl2br(e($produk->deskripsi)) !!}
                </div>
            </div>

            <!-- Right: Spesifikasi & Detail Ukuran -->
            <div class="product-specs-card">
                <div class="specs-card-header">
                    <div class="specs-title-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <path d="M7 8h10M7 12h10M7 16h6"/>
                        </svg>
                        <h2 class="specs-title">Spesifikasi & Detail Ukuran</h2>
                    </div>
                    <span class="specs-unit">Satuan dalam centimeter (cm)</span>
                </div>

                <div class="specs-table-wrapper">
                    <table class="specs-table">
                        <thead>
                            <tr>
                                <th>Ukuran</th>
                                <th>Lingkar Dada</th>
                                <th>Lingkar Pinggang</th>
                                <th>Panjang Dress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($spesifikasi))
                                @foreach ($spesifikasi as $row)
                                    <tr>
                                        <td><strong>{{ $row['ukuran'] ?? '-' }}</strong></td>
                                        <td>{{ $row['lingkar_dada'] ?? '-' }}</td>
                                        <td>{{ $row['lingkar_pinggang'] ?? '-' }}</td>
                                        <td>{{ $row['panjang'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><strong>S</strong></td>
                                    <td>92 cm</td>
                                    <td>72 - 76 cm</td>
                                    <td>114 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>M</strong></td>
                                    <td>96 cm</td>
                                    <td>76 - 80 cm</td>
                                    <td>115 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>L</strong></td>
                                    <td>100 cm</td>
                                    <td>80 - 84 cm</td>
                                    <td>116 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>XL</strong></td>
                                    <td>108 cm</td>
                                    <td>86 - 90 cm</td>
                                    <td>117 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>XXL</strong></td>
                                    <td>112 cm</td>
                                    <td>92 - 96 cm</td>
                                    <td>118 cm</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <p class="specs-footnote">
                    * Toleransi ukuran 1-2 cm dapat terjadi karena proses jahitan manual. Model dress dilengkapi sabuk lepas-pasang yang fleksibel.
                </p>
            </div>
        </div>

        <!-- Bottom Section: Ulasan Pembeli -->
        <div class="product-reviews-section">
            <div class="reviews-header">
                <h2 class="section-title">Ulasan Pembeli</h2>
                <span class="reviews-subtitle">Ulasan terverifikasi dari pembeli produk ini</span>
            </div>

            <div class="reviews-content-grid">
                <!-- Left: Rating Summary Card -->
                <div class="rating-summary-card">
                    <div class="summary-score-large">{{ number_format($avgRating, 1, ',', '.') }}</div>
                    <div class="stars-gold-large" aria-hidden="true">
                        &#9733;&#9733;&#9733;&#9733;&#9733;
                    </div>
                    <div class="summary-count-text">{{ $effectiveTotal }} ulasan</div>

                    <!-- Breakdown Bars (Mockup 2: 5 Bintang dan 4 Bintang) -->
                    <div class="rating-breakdown-bars">
                        @foreach ($starCounts as $s => $cnt)
                            @if ($cnt > 0)
                                @php
                                    $pct = $effectiveTotal > 0 ? round(($cnt / $effectiveTotal) * 100) : 0;
                                @endphp
                                <div class="breakdown-row">
                                    <span class="breakdown-label">{{ $s }} Bintang</span>
                                    <div class="breakdown-bar-track">
                                        <div class="breakdown-bar-fill" style="width: {{ $pct }}%;"></div>
                                    </div>
                                    <span class="breakdown-count">{{ $cnt }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Right: Review Cards List (Mockup 2: Aditya Wardhana & Clara Novita) -->
                <!-- Right: Review Cards List -->
                <div class="review-cards-list">
                    @forelse ($produk->ulasan as $idx => $ulasan)
                        <article class="review-card {{ $idx >= 4 ? 'review-card--extra' : '' }}" style="{{ $idx >= 4 ? 'display: none;' : '' }}">
                            <div class="review-card__header">
                                <div class="reviewer-meta">
                                    <span class="reviewer-avatar-circle">{{ strtoupper(substr($ulasan->user?->nama ?? 'P', 0, 1)) }}</span>
                                    <div class="reviewer-text-meta">
                                        <strong class="reviewer-name">{{ $ulasan->user?->nama ?? 'Pelanggan Gavin' }}</strong>
                                        <span class="review-verified-badge">&#10003; Pembelian Terverifikasi</span>
                                    </div>
                                </div>
                                <span class="review-date">{{ $ulasan->tanggal ? $ulasan->tanggal->format('d M Y') : 'Terbaru' }}</span>
                            </div>

                            <div class="review-stars-row">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="star-item {{ $i <= $ulasan->rating ? 'is-filled' : '' }}">&#9733;</span>
                                @endfor
                            </div>

                            <p class="review-comment">
                                {{ $ulasan->komentar }}
                            </p>
                        </article>
                    @empty
                        <p class="no-reviews-text">Belum ada ulasan untuk produk ini.</p>
                    @endforelse

                    @if ($produk->ulasan->count() > 4)
                        <button type="button" class="btn-toggle-reviews" id="btnToggleReviews" onclick="toggleMoreReviews()">
                            Lihat {{ $produk->ulasan->count() - 4 }} Ulasan Lainnya &darr;
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Stock & Variant Selection Logic -->
<script>
    const stockData = @json($stockMap);
    let selectedColor = "{{ $colors->first() ?? '' }}";
    let selectedSize = "{{ $defaultSize ?? ($sizes->first() ?? '') }}";
    let currentQty = 1;

    function updateVariantState() {
        const key = `${selectedColor}_${selectedSize}`;
        const variant = stockData[key];
        const stockStatusDisplay = document.getElementById('stockStatusDisplay');
        const stockText = document.getElementById('stockText');
        const btnAddCart = document.getElementById('btnAddCart');
        const btnBuyNow = document.getElementById('btnBuyNow');
        const selectedVariantInput = document.getElementById('selectedVariantId');

        if (variant && variant.stok > 0) {
            stockStatusDisplay.className = 'stock-status is-available';
            stockText.textContent = `Tersedia ${variant.stok} pcs`;
            selectedVariantInput.value = variant.id_ukuran;

            if (btnAddCart) btnAddCart.disabled = false;
            if (btnBuyNow) btnBuyNow.disabled = false;

            if (currentQty > variant.stok) {
                currentQty = variant.stok;
                document.getElementById('qtyDisplay').textContent = currentQty;
                document.getElementById('selectedQuantity').value = currentQty;
            }
        } else {
            stockStatusDisplay.className = 'stock-status is-empty';
            stockText.textContent = 'Stok habis';
            selectedVariantInput.value = variant ? variant.id_ukuran : '';

            if (btnAddCart) btnAddCart.disabled = true;
            if (btnBuyNow) btnBuyNow.disabled = true;
        }
    }

    function selectColor(colorName, btn) {
        selectedColor = colorName;
        document.querySelectorAll('.color-chip').forEach(el => el.classList.remove('is-active'));
        btn.classList.add('is-active');

        // Switch photo if thumbnail has this color
        const thumb = document.querySelector(`.gallery-thumb[data-color="${colorName}"]`);
        if (thumb) {
            thumb.click();
        }

        updateVariantState();
    }

    function selectSize(sizeName, btn) {
        selectedSize = sizeName;
        document.querySelectorAll('.size-chip-btn').forEach(el => el.classList.remove('is-active'));
        btn.classList.add('is-active');
        updateVariantState();
    }

    function adjustQty(delta) {
        const key = `${selectedColor}_${selectedSize}`;
        const variant = stockData[key];
        const maxStock = variant ? variant.stok : 1;

        let nextQty = currentQty + delta;
        if (nextQty < 1) nextQty = 1;
        if (nextQty > maxStock) nextQty = maxStock;

        currentQty = nextQty;
        document.getElementById('qtyDisplay').textContent = currentQty;
        document.getElementById('selectedQuantity').value = currentQty;
    }

    function switchMainImage(url, btn, color) {
        document.getElementById('mainProductImage').src = url;
        document.querySelectorAll('.gallery-thumb').forEach(el => el.classList.remove('is-active'));
        btn.classList.add('is-active');
    }

    async function handleAddToCart(isBuyNow) {
        const form = document.getElementById('purchaseForm');
        document.getElementById('beliLangsungField').value = isBuyNow ? '1' : '0';

        if (isBuyNow) {
            form.submit();
            return;
        }

        // AJAX Add to cart
        const formData = new FormData(form);
        try {
            const res = await fetch("{{ route('keranjang.store') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                // Update badge in header
                const badge = document.querySelector('.cart-badge');
                if (badge) {
                    badge.textContent = data.cart_count;
                } else {
                    const container = document.querySelector('.cart-icon-container');
                    if (container) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'cart-badge';
                        newBadge.textContent = data.cart_count;
                        container.appendChild(newBadge);
                    }
                }

                // Show toast
                const toast = document.getElementById('cartToast');
                document.getElementById('cartToastMessage').textContent = data.message;
                toast.style.display = 'flex';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 4000);
            } else {
                alert(data.message || 'Gagal menambahkan produk ke keranjang.');
            }
        } catch (e) {
            form.submit();
        }
    }

    function toggleMoreReviews() {
        const extras = document.querySelectorAll('.review-card--extra');
        const btn = document.getElementById('btnToggleReviews');
        if (!extras.length) return;
        const isHidden = extras[0].style.display === 'none';
        extras.forEach(el => el.style.display = isHidden ? 'block' : 'none');
        if (btn) {
            btn.innerHTML = isHidden ? 'Sembunyikan Sebagian Ulasan &uarr;' : 'Lihat ' + extras.length + ' Ulasan Lainnya &darr;';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateVariantState();
    });
</script>
@endsection
