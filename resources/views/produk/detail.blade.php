@extends('layouts.site')

@section('title', $produk->nama_produk)
@section('active', 'katalog')

@section('main_class', 'site-main--catalog')

@php
    $starSvg = '<svg viewBox="0 0 24 24" fill="#c8a24a" aria-hidden="true"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    $starEmptySvg = '<svg viewBox="0 0 24 24" fill="#e0e0e0" aria-hidden="true"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
@endphp

@section('content')
<div class="pd">
    {{-- ============ PRODUCT SECTION (galeri + info) ============ --}}
    <div class="pd__top">
        <div class="pd__gallery">
            <figure class="pd__main">
                <img src="{{ asset($produk->gambar) }}" alt="{{ $produk->nama_produk }}">
            </figure>
            {{-- Galeri multi-gambar (thumbnail) membutuhkan tabel gambar_produk; belum tersedia. --}}
        </div>

        <div class="pd__info">
            <p class="pd__cat">{{ strtoupper($produk->kategori?->nama_kategori) }}</p>
            <h1 class="pd__name">{{ $produk->nama_produk }}</h1>

            <div class="pd__rating">
                @if ($ratingCount > 0)
                    <span class="pd__stars pd__stars--sm">@for ($i = 0; $i < 5; $i++) {!! $starSvg !!} @endfor</span>
                    <b>{{ number_format($ratingAvg, 1, ',', '') }}</b>
                    <span class="pd__rating-count">({{ $ratingCount }} ulasan)</span>
                @else
                    <span class="pd__rating-count">Belum ada ulasan</span>
                @endif
            </div>

            <p class="pd__price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>

            <hr class="pd__div">

            <div class="pd__field">
                <span class="pd__label">Pilihan Warna:</span>
                <div class="pd__colors" id="pdColors">
                    @foreach ($varian as $warna => $rows)
                        <button type="button" class="pd__color {{ $loop->first ? 'is-active' : '' }}" data-warna="{{ $warna }}">{{ $warna }}</button>
                    @endforeach
                </div>
            </div>

            <div class="pd__field">
                <span class="pd__label">Pilih Ukuran</span>
                <div class="pd__sizes" id="pdSizes"></div>
            </div>

            <div class="pd__field">
                <span class="pd__label">Jumlah</span>
                <div class="pd__qtyrow">
                    <div class="pd__qty">
                        <button type="button" data-step="-1" aria-label="Kurangi jumlah">&minus;</button>
                        <span class="pd__qty-val" id="pdQty">1</span>
                        <button type="button" data-step="1" aria-label="Tambah jumlah">+</button>
                    </div>
                    <div class="pd__stock">
                        <i class="pd__dot"></i>
                        <span>Tersedia</span>
                        <b id="pdStokPcs">{{ $stokTersedia }} pcs</b>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('keranjang.store') }}" class="pd__cta" id="pdForm">
                @csrf
                <input type="hidden" name="id_ukuran" id="pdIdUkuran">
                <input type="hidden" name="jumlah" id="pdJumlah" value="1">
                <button type="submit" name="mode" value="tambah" class="pd__btn pd__btn--cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7Z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
                    Tambah ke Keranjang
                </button>
                <button type="submit" name="mode" value="beli" class="pd__btn pd__btn--buy">Beli Sekarang</button>
            </form>
        </div>
    </div>

    {{-- ============ DESKRIPSI + SPESIFIKASI ============ --}}
    <div class="pd__mid">
        <section class="pd__desc">
            <h2>Deskripsi Produk</h2>
            @if ($deskripsiIntro !== '')
                <p>{{ $deskripsiIntro }}</p>
            @endif
            @if (! empty($deskripsiPoin))
                <ul class="pd__bullets">
                    @foreach ($deskripsiPoin as [$label, $isi])
                        <li><b>{{ $label }}:</b> {{ $isi }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if (! empty($spesifikasi))
            <section class="pd__spec">
                <div class="pd__spec-head">
                    <h3>Spesifikasi &amp; Detail Ukuran</h3>
                    <span>Satuan dalam centimeter (cm)</span>
                </div>
                <table class="pd__spec-table">
                    @foreach ($spesifikasi as $i => $row)
                        <tr class="{{ $i === 0 ? 'is-head' : '' }} {{ ($i > 0 && in_array($row[0], $ukuranHabis, true)) ? 'is-muted' : '' }}">
                            @foreach ($row as $cell)
                                @if ($i === 0)
                                    <th scope="col">{{ $cell }}</th>
                                @else
                                    <td>{{ $cell }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </table>
                <p class="pd__spec-note">* Toleransi ukuran 1-2 cm dapat terjadi karena proses jahitan manual.</p>
            </section>
        @endif
    </div>

    {{-- ============ ULASAN PEMBELI ============ --}}
    <section class="pd__reviews">
        <div class="pd__reviews-head">
            <h2>Ulasan Pembeli</h2>
            <span>Ulasan terverifikasi dari pembeli produk ini</span>
        </div>

        @if ($ratingCount === 0)
            <p class="pd__empty">Belum ada ulasan untuk produk ini. Jadilah yang pertama memberikan ulasan.</p>
        @else
            <div class="pd__reviews-body">
                <aside class="pd__rev-sum">
                    <div class="pd__rev-avg">{{ number_format($ratingAvg, 1, ',', '') }}</div>
                    <div class="pd__stars">@for ($i = 0; $i < 5; $i++) {!! $starSvg !!} @endfor</div>
                    <div class="pd__rev-count">{{ $ratingCount }} ulasan</div>
                    <div class="pd__rev-bars">
                        @foreach ($ratingBars as $bar)
                            <div class="pd__rev-bar">
                                <span class="pd__rev-bar-label">{{ $bar['label'] }}</span>
                                <i class="pd__rev-bar-track"><em style="width: {{ $bar['pct'] }}%"></em></i>
                                <span class="pd__rev-bar-count">{{ $bar['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </aside>

                <div class="pd__rev-list">
                    @foreach ($ulasanList as $ulasan)
                        <article class="pd__rev">
                            <div class="pd__rev-head">
                                <div class="pd__rev-who">
                                    <b>{{ $ulasan->user?->nama ?? 'Pembeli' }}</b>
                                    @if ($ulasan->detail?->ukuran?->ukuran)
                                        <span class="pd__rev-size">Ukuran {{ $ulasan->detail->ukuran->ukuran }}</span>
                                    @endif
                                </div>
                                <time datetime="{{ $ulasan->tanggal?->toDateString() }}">{{ $ulasan->tanggal?->format('d F Y') }}</time>
                            </div>
                            <div class="pd__stars pd__stars--md">
                                @for ($i = 0; $i < 5; $i++)
                                    {!! $i < $ulasan->rating ? $starSvg : $starEmptySvg !!}
                                @endfor
                            </div>
                            <p class="pd__rev-text">{{ $ulasan->komentar }}</p>
                        </article>
                    @endforeach

                    <div class="pd__rev-foot">
                        <button type="button" class="pd__rev-more">Lihat ulasan lainnya
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                        <span>Menampilkan {{ $ulasanList->count() }} dari {{ $ratingCount }} ulasan</span>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>

<script>
(function () {
    var VARIAN = @json($varian);
    var qtyEl = document.getElementById('pdQty');
    var warnaBox = document.getElementById('pdColors');
    var ukuranBox = document.getElementById('pdSizes');
    var pcsEl = document.getElementById('pdStokPcs');
    var hiddenId = document.getElementById('pdIdUkuran');
    var hiddenJumlah = document.getElementById('pdJumlah');

    var selectedWarna = Object.keys(VARIAN)[0];
    var selectedId = null;
    var selectedStok = 0;
    var qty = 1;

    function findFirstAvailable(rows) {
        var hit = null;
        (rows || []).forEach(function (r) { if (!hit && r.aktif && r.stok > 0) { hit = r; } });
        return hit || (rows || [])[0] || null;
    }

    function renderUkuran() {
        var rows = VARIAN[selectedWarna] || [];
        var first = findFirstAvailable(rows);
        selectedId = first ? first.id : null;
        selectedStok = first && first.aktif && first.stok > 0 ? first.stok : 0;

        ukuranBox.innerHTML = '';
        rows.forEach(function (r) {
            var habis = !(r.aktif && r.stok > 0);
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'pd__size' + (r.id === selectedId ? ' is-active' : '') + (habis ? ' is-off' : '');
            b.textContent = r.ukuran;
            b.disabled = habis;
            b.addEventListener('click', function () {
                selectedId = r.id;
                selectedStok = habis ? 0 : r.stok;
                Array.prototype.forEach.call(ukuranBox.children, function (x) {
                    x.classList.toggle('is-active', x === b);
                });
                updateStok();
            });
            ukuranBox.appendChild(b);
        });

        updateStok();
    }

    function updateStok() {
        qty = Math.min(qty, Math.max(1, selectedStok));
        qtyEl.textContent = qty;
        pcsEl.textContent = selectedStok + ' pcs';
    }

    warnaBox.addEventListener('click', function (e) {
        var b = e.target.closest('.pd__color');
        if (!b) { return; }
        selectedWarna = b.getAttribute('data-warna');
        Array.prototype.forEach.call(warnaBox.children, function (x) { x.classList.toggle('is-active', x === b); });
        renderUkuran();
    });

    document.querySelector('.pd__qty').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn || !btn.hasAttribute('data-step')) { return; }
        var step = parseInt(btn.getAttribute('data-step'), 10);
        qty = Math.min(Math.max(1, qty + step), Math.max(1, selectedStok));
        qtyEl.textContent = qty;
    });

    var form = document.getElementById('pdForm');
    if (form) {
        form.addEventListener('submit', function () {
            hiddenId.value = selectedId || '';
            hiddenJumlah.value = qty;
        });
    }

    renderUkuran();
})();
</script>
@endsection