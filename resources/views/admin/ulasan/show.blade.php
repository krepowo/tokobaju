@extends('layouts.admin')

@section('title', 'Ulasan ' . $produk->nama_produk)
@section('active', 'ulasan')
@section('crumb', 'Lihat Ulasan')
@section('crumb_current', $produk->nama_produk)

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">{{ $produk->nama_produk }}</h1>
            <p class="ad-sub">{{ $produk->kategori?->nama_kategori ?? 'Tanpa kategori' }} &middot; {{ $jumlah }} ulasan</p>
        </div>
        <a href="{{ route('admin.ulasan.index') }}" class="ad-btn ad-btn--ghost">Kembali</a>
    </div>

    <div class="ad-grid-2">
        <section class="ad-card">
            <div class="ad-card__head">
                <h2 class="ad-section-title">Ulasan Pembeli</h2>
            </div>

            @if ($ulasan->isEmpty())
                <div class="ad-empty">
                    <strong>Belum ada ulasan</strong>
                    Ulasan muncul setelah pelanggan menyelesaikan pesanan dan memberi rating.
                </div>
            @else
                <div class="ad-list">
                    @foreach ($ulasan as $u)
                        <article class="ad-list__item">
                            <div class="ad-list__top">
                                <div style="display:flex; align-items:center; gap:10px">
                                    <span class="ad-avatar">{{ mb_strtoupper(mb_substr($u->user?->nama ?? 'A', 0, 2)) }}</span>
                                    <div>
                                        <div class="ad-list__nama">{{ $u->user?->nama ?? 'Pelanggan' }}</div>
                                        <div class="ad-list__meta">
                                            {{ $u->detailPesanan?->ukuranStok?->ukuran ?? '—' }} /
                                            {{ $u->detailPesanan?->ukuranStok?->warna ?? '—' }}
                                            &middot; {{ $u->tanggal?->translatedFormat('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                                <span class="ad-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $u->rating ? '' : 'ad-stars__off' }}">★</span>
                                    @endfor
                                </span>
                            </div>
                            <p class="ad-list__isi">{{ $u->komentar ?: 'Pelanggan tidak menulis komentar.' }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="ad-card__foot">
                    @include('admin.partials.pager', ['items' => $ulasan])
                </div>
            @endif
        </section>

        <div>
            <section class="ad-card">
                <div class="ad-card__head">
                    <h2 class="ad-section-title">Ringkasan Rating</h2>
                </div>
                <div class="ad-card__body">
                    <div class="ad-rating-big">
                        <strong>{{ number_format($rataRata, 1, ',', '.') }}</strong>
                        <div>
                            <span class="ad-stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= round($rataRata) ? '' : 'ad-stars__off' }}">★</span>
                                @endfor
                            </span>
                            <div class="ad-list__meta">dari {{ $jumlah }} ulasan</div>
                        </div>
                    </div>

                    <div class="ad-dl" style="margin-top:20px">
                        @foreach ([5, 4, 3, 2, 1] as $bintang)
                            @php
                                $jml = (int) ($sebaran[$bintang] ?? 0);
                                $persen = $jumlah > 0 ? round($jml / $jumlah * 100) : 0;
                            @endphp
                            <div class="ad-dl__row" style="align-items:center">
                                <dt style="display:flex; align-items:center; gap:8px; min-width:64px">{{ $bintang }} ★</dt>
                                <dd style="display:flex; align-items:center; gap:10px; flex:1">
                                    <span style="flex:1; height:6px; background:#f2f4f6; border-radius:9999px; overflow:hidden">
                                        <span style="display:block; height:100%; width:{{ $persen }}%; background:var(--ad-gold)"></span>
                                    </span>
                                    <span style="min-width:52px; text-align:right; font-size:12px; color:var(--ad-muted)">{{ $jml }} ({{ $persen }}%)</span>
                                </dd>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="ad-card">
                <div class="ad-card__body">
                    <div class="ad-produk">
                        <img src="{{ asset($produk->gambar) }}" alt="" class="ad-thumb" style="width:56px;height:56px">
                        <div>
                            <div class="ad-produk__nama">{{ $produk->nama_produk }}</div>
                            <span class="ad-table__sub">Rp {{ number_format((float) $produk->harga, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection