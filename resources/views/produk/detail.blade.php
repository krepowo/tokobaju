@extends('layouts.site')

@section('title', $produk->nama_produk)
@section('active', 'katalog')
@section('main_class', 'site-main--catalog')

@section('content')
<div class="detail">
    <nav class="detail__crumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('katalog') }}">Katalog</a>
        <span aria-hidden="true">/</span>
        <span class="detail__crumb-current">{{ $produk->nama_produk }}</span>
    </nav>

    <div class="detail__layout">
        <figure class="detail__media">
            <img src="{{ asset($produk->gambar) }}" alt="{{ $produk->nama_produk }}">
            @if ($stokTersedia === 0)
                <span class="detail__badge">Stok Habis</span>
            @endif
        </figure>

        <div class="detail__info">
            <p class="detail__cat">{{ $produk->kategori?->nama_kategori }}</p>
            <h1 class="detail__name">{{ $produk->nama_produk }}</h1>

            <div class="detail__rating">
                @if ($ratingCount > 0)
                    <img src="{{ asset('images/figma/home/ico-star-1.svg') }}" alt="" aria-hidden="true">
                    <b>{{ number_format($ratingAvg, 1, ',', '') }}</b>
                    <span>({{ $ratingCount }} ulasan)</span>
                @else
                    <span>Belum ada ulasan</span>
                @endif
            </div>

            <p class="detail__price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>

            <p class="detail__desc">{{ $produk->deskripsi }}</p>

            <div class="detail__field">
                <span class="detail__label">Warna: <b id="detailWarnaLabel">{{ $varian->keys()->first() }}</b></span>
                <div class="detail__options" id="detailWarna">
                    @foreach ($varian as $warna => $rows)
                        <button type="button" class="detail__warna {{ $loop->first ? 'is-active' : '' }}" data-warna="{{ $warna }}">{{ $warna }}</button>
                    @endforeach
                </div>
            </div>

            <div class="detail__field">
                <span class="detail__label">Ukuran</span>
                <div class="detail__options" id="detailUkuran"></div>
                <p class="detail__stock" id="detailStok"></p>
            </div>

            <div class="detail__field">
                <span class="detail__label">Jumlah</span>
                <div class="detail__qty">
                    <button type="button" data-step="-1" aria-label="Kurangi jumlah">&minus;</button>
                    <input type="number" id="detailQty" value="1" min="1" readonly>
                    <button type="button" data-step="1" aria-label="Tambah jumlah">+</button>
                </div>
            </div>

            <form method="POST" action="{{ route('keranjang.store') }}" class="detail__actions" id="detailCartForm">
                @csrf
                <input type="hidden" name="id_ukuran" id="detailUkuranHidden">
                <input type="hidden" name="jumlah" id="detailJumlahHidden" value="1">
                <button type="submit" name="mode" value="tambah" class="detail__btn detail__btn--cart">Tambah ke Keranjang</button>
                <button type="submit" name="mode" value="beli" class="detail__btn detail__btn--buy">Beli Sekarang</button>
            </form>

            @if (! empty($spesifikasi))
                <details class="detail__spec">
                    <summary>Lihat Tabel Spesifikasi Ukuran</summary>
                    <table class="detail__spec-table">
                        @foreach ($spesifikasi as $i => $row)
                            <tr>
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
                </details>
            @endif
        </div>
    </div>

    <section class="detail__reviews">
        <h2>Ulasan Pembeli</h2>

        @if ($produk->ulasan->isEmpty())
            <p class="detail__empty">Belum ada ulasan untuk produk ini.</p>
        @else
            <div class="detail__review-list">
                @foreach ($produk->ulasan as $ulasan)
                    <article class="review">
                        <div class="review__head">
                            <b>{{ $ulasan->user?->nama ?? 'Pembeli' }}</b>
                            <span class="review__stars" aria-label="Rating {{ $ulasan->rating }} dari 5">
                                @for ($s = 0; $s < $ulasan->rating; $s++) &#9733; @endfor
                            </span>
                            <time datetime="{{ $ulasan->tanggal?->toDateString() }}">{{ $ulasan->tanggal?->format('d M Y') }}</time>
                        </div>
                        <p class="review__body">{{ $ulasan->komentar }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>

<script>
(function () {
    var VARIAN = @json($varian);
    var qty = document.getElementById('detailQty');
    var warnaBox = document.getElementById('detailWarna');
    var ukuranBox = document.getElementById('detailUkuran');
    var stokEl = document.getElementById('detailStok');
    var warnaLabel = document.getElementById('detailWarnaLabel');
    var selectedWarna = Object.keys(VARIAN)[0];
    var selectedUkuran = null;
    var selectedId = null;
    var hiddenId = document.getElementById('detailUkuranHidden');
    var hiddenJumlah = document.getElementById('detailJumlahHidden');

    function findFirstAvailable(rows) {
        var hit = null;
        (rows || []).forEach(function (r) { if (!hit && r.aktif && r.stok > 0) { hit = r; } });
        return hit || (rows || [])[0] || null;
    }

    function renderUkuran() {
        var rows = VARIAN[selectedWarna] || [];
        var first = findFirstAvailable(rows);
        selectedUkuran = first ? first.ukuran : null;
        selectedId = first ? first.id : null;
        ukuranBox.innerHTML = '';

        rows.forEach(function (r) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'detail__ukuran ' + (r.ukuran === selectedUkuran ? 'is-active ' : '') + (r.aktif && r.stok > 0 ? 'is-on' : 'is-off');
            b.textContent = r.ukuran;
            b.disabled = !(r.aktif && r.stok > 0);
            b.addEventListener('click', function () {
                selectedUkuran = r.ukuran;
                selectedId = r.id;
                Array.prototype.forEach.call(ukuranBox.children, function (x) {
                    x.classList.toggle('is-active', x.textContent === selectedUkuran);
                });
                updateStok();
            });
            ukuranBox.appendChild(b);
        });

        updateStok();
    }

    function updateStok() {
        var rows = VARIAN[selectedWarna] || [];
        var r = null;
        rows.forEach(function (x) { if (x.ukuran === selectedUkuran) { r = x; } });
        stokEl.textContent = r
            ? (r.stok > 0 ? 'Stok tersisa: ' + r.stok : 'Stok habis')
            : '';
    }

    warnaBox.addEventListener('click', function (e) {
        var b = e.target.closest('.detail__warna');
        if (!b) { return; }
        selectedWarna = b.getAttribute('data-warna');
        Array.prototype.forEach.call(warnaBox.children, function (x) { x.classList.toggle('is-active', x === b); });
        warnaLabel.textContent = selectedWarna;
        renderUkuran();
    });

    document.querySelector('.detail__qty').addEventListener('click', function (e) {
        var step = e.target.closest('button');
        if (!step || !step.hasAttribute('data-step')) { return; }
        var v = parseInt(qty.value, 10) || 1;
        qty.value = Math.max(1, v + parseInt(step.getAttribute('data-step'), 10));
    });

    var cartForm = document.getElementById('detailCartForm');
    if (cartForm) {
        cartForm.addEventListener('submit', function () {
            hiddenId.value = selectedId || '';
            hiddenJumlah.value = qty.value;
        });
    }

    renderUkuran();
})();
</script>
@endsection
