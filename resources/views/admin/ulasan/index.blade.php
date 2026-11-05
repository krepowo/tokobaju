@extends('layouts.admin')

@section('title', 'Ulasan Pembeli')
@section('nav', 'ulasan')
@section('breadcrumb', 'Ulasan Pembeli')

@section('content')
    @php
        $inisial = function (string $nama): string {
            $nama = trim($nama);
            if ($nama === '') return '?';
            $bagian = preg_split('/\s+/', $nama);
            $a = mb_substr($bagian[0], 0, 1);
            $b = count($bagian) > 1 ? mb_substr(end($bagian), 0, 1) : '';
            return mb_strtoupper($a . $b);
        };
        $warna = function (string $nama): string {
            $palet = ['#b88b42', '#dfb169', '#8a6d3b', '#c98d4e', '#a5844a', '#7a5c35'];
            return $palet[abs(crc32($nama)) % count($palet)];
        };
    @endphp

    <div class="page-head">
        <div class="page-head__title">
            <h1>Ulasan Pembeli</h1>
            <p class="page-head__sub">Kelola evaluasi pelanggan, tanggapan seller, serta sentimen produk secara berkala.</p>
        </div>
        <div class="page-head__actions">
            <form method="GET" action="{{ route('admin.ulasan.index') }}">
                @if ($bintang)<input type="hidden" name="bintang" value="{{ $bintang }}">@endif
                @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
                <label class="select-field">
                    <select name="produk" onchange="this.form.submit()" aria-label="Pilih produk">
                        @foreach ($daftarProduk as $p)
                            <option value="{{ $p->id_produk }}" @selected($produk && $p->id_produk === $produk->id_produk)>{{ $p->nama_produk }} ({{ $p->ulasan_count }})</option>
                        @endforeach
                    </select>
                    <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
                </label>
            </form>
            @if ($produk)
                <a href="{{ route('admin.ulasan.ekspor', ['produk' => $produk->id_produk]) }}" class="btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    Ekspor CSV
                </a>
            @endif
        </div>
    </div>

    @if ($produk && $ringkasan)
        <div class="rating-dashboard">
            <div class="rating-score">
                @php $bintangPenuh = (int) round($ringkasan['rata_rata']); @endphp
                <div class="rating-score__value">{{ number_format($ringkasan['rata_rata'], 1, ',', '.') }}</div>
                <div class="rating-score__stars">
                    @for ($s = 1; $s <= 5; $s++)
                        <span class="{{ $s <= $bintangPenuh ? 'is-on' : 'is-off' }}">&#9733;</span>
                    @endfor
                </div>
                <div class="rating-score__note">Berdasarkan {{ $ringkasan['jumlah'] }} ulasan<br>pembeli</div>
            </div>

            <div class="rating-bars">
                <div class="rating-bars__head">
                    <b>{{ $ringkasan['puas'] }}% Puas</b>
                    <span>Rating pembeli 4&ndash;5 bintang</span>
                </div>
                @for ($i = 5; $i >= 1; $i--)
                    @php
                        $c = (int) ($ringkasan['sebaran'][$i] ?? 0);
                        $pct = $ringkasan['jumlah'] > 0 ? round($c / $ringkasan['jumlah'] * 100) : 0;
                    @endphp
                    <div class="rating-bar">
                        <span class="rating-bar__label">{{ $i }} &#9733;</span>
                        <div class="progress"><span style="width:{{ $pct }}%"></span></div>
                        <span class="rating-bar__count">{{ $c }} ({{ $pct }}%)</span>
                    </div>
                @endfor
            </div>

            <aside class="product-highlight">
                <span class="product-highlight__flag">Produk Terpilih</span>
                <img src="{{ $produk->gambar_url }}" alt="{{ $produk->nama_produk }}">
                <span class="product-highlight__name">{{ $produk->nama_produk }}</span>
                <span class="product-highlight__meta">{{ $produk->kategori?->nama_kategori ?? 'Fashion' }}</span>
                <div class="product-highlight__foot">
                    <span class="product-highlight__price">{{ $produk->harga_formatted }}</span>
                    <span class="product-highlight__sold">Terjual {{ $ringkasan['terjual'] }}</span>
                </div>
            </aside>
        </div>

        <div class="review-toolbar">
            <div class="filter-chips">
                <a class="filter-chip {{ ! $bintang ? 'is-active' : '' }}"
                   href="{{ route('admin.ulasan.index', array_filter(['produk' => $produk->id_produk, 'q' => $q !== '' ? $q : null])) }}">Semua <b>({{ $ringkasan['jumlah'] }})</b></a>
                @for ($i = 5; $i >= 1; $i--)
                    @php $c = (int) ($ringkasan['sebaran'][$i] ?? 0); @endphp
                    <a class="filter-chip {{ $bintang === $i ? 'is-active' : '' }}"
                       href="{{ route('admin.ulasan.index', array_filter(['produk' => $produk->id_produk, 'bintang' => $i, 'q' => $q !== '' ? $q : null])) }}">{{ $i }} Bintang <b>({{ $c }})</b></a>
                @endfor
            </div>

            <form method="GET" action="{{ route('admin.ulasan.index') }}">
                <input type="hidden" name="produk" value="{{ $produk->id_produk }}">
                @if ($bintang)<input type="hidden" name="bintang" value="{{ $bintang }}">@endif
                <label class="search-field">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Cari ulasan pembeli..." aria-label="Cari ulasan">
                </label>
            </form>
        </div>

        <section class="panel">
            <header class="panel__head">
                <h2>Daftar Ulasan</h2>
                <span class="panel__meta">{{ $ulasan->total() }} ulasan</span>
            </header>
            <div class="review-list">
                @forelse ($ulasan as $u)
                    <article class="review-card">
                        <span class="avatar" style="background: {{ $warna($u->user?->nama ?? '') }}">{{ $inisial($u->user?->nama ?? '') }}</span>
                        <div>
                            <div class="review-card__top">
                                <span class="review-card__name">{{ $u->user?->nama ?? '—' }}</span>
                                <span class="review-card__stars">@for ($s = 1; $s <= $u->rating; $s++)&#9733;@endfor</span>
                                <span class="review-card__verified">Pembeli Terverifikasi</span>
                            </div>
                            <div class="review-card__meta">
                                {{ $u->tanggal?->translatedFormat('d M Y') ?? '—' }}
                                &bull; Ukuran: {{ $u->detailPesanan?->ukuranStok?->ukuran ?? '—' }}
                                &middot; Warna: {{ $u->detailPesanan?->ukuranStok?->warna ?? '—' }}
                            </div>
                            @if ($u->komentar)
                                <p class="review-card__body">{{ $u->komentar }}</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="empty">Belum ada ulasan pada filter ini.</p>
                @endforelse
            </div>

            @if ($ulasan->hasPages())
                <div class="admin-pagination">
                    <span>Menampilkan {{ $ulasan->firstItem() ?? 0 }}&ndash;{{ $ulasan->lastItem() ?? 0 }} dari {{ $ulasan->total() }} ulasan</span>
                    <div class="admin-pagination__links">
                        @if ($ulasan->onFirstPage())
                            <span class="is-disabled">Sebelumnya</span>
                        @else
                            <a href="{{ $ulasan->previousPageUrl() }}">Sebelumnya</a>
                        @endif
                        @if ($ulasan->hasMorePages())
                            <a href="{{ $ulasan->nextPageUrl() }}">Selanjutnya</a>
                        @else
                            <span class="is-disabled">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    @else
        <section class="panel"><p class="empty">Belum ada produk yang memiliki ulasan.</p></section>
    @endif
@endsection