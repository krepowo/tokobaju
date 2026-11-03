@extends('layouts.admin')

@section('title', 'Ulasan Pembeli')
@section('active', 'ulasan')
@section('crumb_current', 'Ulasan Pembeli')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $pilihanProduk = $produk?->id_produk;
        $tautan = function (array $ubah = []) use ($pilihanProduk, $bintang, $q) {
            $dasar = array_filter([
                'produk' => $pilihanProduk,
                'bintang' => $bintang ?: null,
                'q' => $q !== '' ? $q : null,
            ]);
            return route('admin.ulasan.index', array_filter(array_merge($dasar, $ubah)));
        };
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Ulasan Pembeli</h1>
            <p class="ad-sub">Kelola evaluasi pelanggan terhadap kualitas produk yang sudah dibeli.</p>
        </div>

        <div class="ad-toolbar">
            <form method="GET" action="{{ route('admin.ulasan.index') }}">
                <select name="produk" class="ad-select" style="min-width:260px" onchange="this.form.submit()">
                    @forelse ($daftarProduk as $p)
                        <option value="{{ $p->id_produk }}" @selected($p->id_produk === $pilihanProduk)>
                            {{ $p->nama_produk }} ({{ $p->ulasan_count }})
                        </option>
                    @empty
                        <option value="">Belum ada produk berulasan</option>
                    @endforelse
                </select>
            </form>

            <a href="{{ route('admin.ulasan.ekspor', $pilihanProduk ? ['produk' => $pilihanProduk] : []) }}" class="ad-btn ad-btn--ghost">
                @include('admin.partials.icon', ['name' => 'upload'])
                Ekspor CSV
            </a>
        </div>
    </div>

    @unless ($produk)
        <section class="ad-card">
            <div class="ad-empty">
                <strong>Belum ada ulasan pembeli</strong>
                Ulasan muncul setelah pelanggan menyelesaikan pesanan dan memberi rating pada produk.
            </div>
        </section>
    @else
        <section class="ad-card">
            <div class="ul-summary">
                <div>
                    <div class="ul-score__num">{{ number_format($ringkasan['rata_rata'], 1, ',', '.') }}</div>
                    <div class="ul-score__stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <span style="color: {{ $i <= round($ringkasan['rata_rata']) ? 'var(--ad-gold)' : '#e5e7eb' }}">&#9733;</span>
                        @endfor
                    </div>
                    <div class="ul-score__meta">Berdasarkan {{ $ringkasan['jumlah'] }} ulasan pembeli</div>
                    <span class="ul-puas">{{ $ringkasan['puas'] }}% Puas</span>
                </div>

                <div class="ul-dist">
                    @foreach ([5, 4, 3, 2, 1] as $nilai)
                        @php
                            $jml = (int) ($ringkasan['sebaran'][$nilai] ?? 0);
                            $persen = $ringkasan['jumlah'] > 0 ? (int) round($jml / $ringkasan['jumlah'] * 100) : 0;
                        @endphp
                        <div class="ul-dist__row">
                            <span class="ul-dist__lab">{{ $nilai }} &#9733;</span>
                            <span class="ul-dist__bar"><span class="ul-dist__fill" style="width: {{ $persen }}%"></span></span>
                            <span class="ul-dist__val">{{ $jml }} ({{ $persen }}%)</span>
                        </div>
                    @endforeach
                </div>

                <div class="ul-produk">
                    <div style="flex:1 1 auto">
                        <div class="ul-produk__label">Produk Terpilih</div>
                        <div class="ul-produk__nama">{{ $produk->nama_produk }}</div>
                        <div class="ul-produk__meta">{{ $produk->kategori?->nama_kategori ?? 'Tanpa kategori' }}</div>
                        <div class="ul-produk__meta">{{ $rupiah($produk->harga) }} &middot; Terjual {{ $ringkasan['terjual'] }}</div>
                    </div>
                    <img src="{{ asset($produk->gambar) }}" alt="" class="ul-produk__thumb">
                </div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ul-pills">
                <a href="{{ $tautan(['bintang' => null]) }}" class="ul-pill {{ ! $bintang ? 'is-active' : '' }}">
                    Semua <span class="ul-pill__n">({{ $ringkasan['jumlah'] }})</span>
                </a>
                @foreach ([5, 4, 3, 2, 1] as $nilai)
                    <a href="{{ $tautan(['bintang' => $nilai]) }}" class="ul-pill {{ $bintang === $nilai ? 'is-active' : '' }}">
                        Bintang {{ $nilai }}
                        <span class="ul-pill__n">({{ (int) ($ringkasan['sebaran'][$nilai] ?? 0) }})</span>
                    </a>
                @endforeach

                <form method="GET" action="{{ route('admin.ulasan.index') }}" class="ad-toolbar" style="margin-left:auto">
                    <input type="hidden" name="produk" value="{{ $produk->id_produk }}">
                    @if ($bintang) <input type="hidden" name="bintang" value="{{ $bintang }}"> @endif
                    <label class="ad-search" style="min-width:220px">
                        @include('admin.partials.icon', ['name' => 'search'])
                        <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Cari ulasan pembeli...">
                    </label>
                </form>
            </div>

            @if ($ringkasan['jumlah'] === 0)
                <div class="ad-empty">
                    <strong>Belum ada ulasan</strong>
                    Produk ini belum menerima ulasan dari pembeli.
                </div>
            @elseif ($ulasan->isEmpty())
                <div class="ad-empty">
                    <strong>Tidak ada ulasan yang cocok</strong>
                    Coba ubah filter bintang atau kata kunci pencarian.
                </div>
            @else
                @foreach ($ulasan as $u)
                    <article class="ul-item">
                        <span class="ul-avatar">{{ strtoupper(\Illuminate\Support\Str::of($u->user?->nama ?? 'P')->explode(' ')->map(fn ($k) => mb_substr($k, 0, 1))->take(2)->implode('')) }}</span>
                        <div style="flex:1 1 auto; min-width:0">
                            <div class="ul-head">
                                <span class="ul-nama">{{ $u->user?->nama ?? 'Pelanggan' }}</span>
                                <span class="ul-verified">Verified Buyer</span>
                                <span class="ad-stars" style="margin-left:auto">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $u->rating ? '' : 'ad-stars__off' }}">&#9733;</span>
                                    @endfor
                                </span>
                            </div>
                            <div class="ul-meta">
                                {{ $u->tanggal?->translatedFormat('d M Y') }}
                                &bull; Ukuran: {{ $u->detailPesanan?->ukuranStok?->ukuran ?? '—' }}
                                | Warna: {{ $u->detailPesanan?->ukuranStok?->warna ?? '—' }}
                            </div>
                            <p class="ul-isi">{{ $u->komentar ?: 'Pembeli tidak menulis komentar.' }}</p>
                        </div>
                    </article>
                @endforeach

                <div class="ad-card__foot">
                    <span>Menampilkan {{ $ulasan->firstItem() }}&ndash;{{ $ulasan->lastItem() }} dari {{ $ulasan->total() }} ulasan</span>
                    <div class="ad-pager__pages">
                        @if ($ulasan->onFirstPage())
                            <span class="ad-pager__link is-disabled">Sebelumnya</span>
                        @else
                            <a href="{{ $ulasan->previousPageUrl() }}" class="ad-pager__link">Sebelumnya</a>
                        @endif
                        @foreach ($ulasan->getUrlRange(max(1, $ulasan->currentPage() - 2), min($ulasan->lastPage(), $ulasan->currentPage() + 2)) as $nomor => $url)
                            <a href="{{ $url }}" class="ad-pager__link {{ $nomor === $ulasan->currentPage() ? 'is-active' : '' }}">{{ $nomor }}</a>
                        @endforeach
                        @if ($ulasan->hasMorePages())
                            <a href="{{ $ulasan->nextPageUrl() }}" class="ad-pager__link">Selanjutnya</a>
                        @else
                            <span class="ad-pager__link is-disabled">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    @endunless
@endsection
