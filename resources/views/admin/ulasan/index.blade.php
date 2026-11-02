@extends('layouts.admin')

@section('title', 'Lihat Ulasan')
@section('active', 'ulasan')
@section('crumb_current', 'Lihat Ulasan')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Lihat Ulasan</h1>
            <p class="ad-sub">Rating dan ulasan hanya berasal dari pesanan yang sudah selesai.</p>
        </div>
    </div>

    <section class="ad-card">
        <div class="ad-card__head">
            <form method="GET" action="{{ route('admin.ulasan.index') }}" class="ad-toolbar" style="flex:1">
                <label class="ad-search">
                    @include('admin.partials.icon', ['name' => 'search'])
                    <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Cari produk...">
                </label>
                <button type="submit" class="ad-btn ad-btn--ghost">Cari</button>
            </form>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="is-center">Jumlah Ulasan</th>
                        <th>Rating Rata-rata</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produk as $p)
                        <tr>
                            <td>
                                <div class="ad-produk">
                                    <img src="{{ asset($p->gambar) }}" alt="" class="ad-thumb">
                                    <span class="ad-produk__nama">{{ $p->nama_produk }}</span>
                                </div>
                            </td>
                            <td class="is-muted">{{ $p->kategori?->nama_kategori ?? '—' }}</td>
                            <td class="is-center">
                                @if ($p->ulasan_count === 0)
                                    <span class="ad-badge ad-badge--muted">Belum ada</span>
                                @else
                                    {{ $p->ulasan_count }}
                                @endif
                            </td>
                            <td>
                                @if ($p->ulasan_count > 0)
                                    <span class="ad-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= round((float) $p->ulasan_avg_rating) ? '' : 'ad-stars__off' }}">★</span>
                                        @endfor
                                    </span>
                                    <span class="ad-table__sub">{{ number_format((float) $p->ulasan_avg_rating, 1, ',', '.') }} / 5</span>
                                @else
                                    <span class="is-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="ad-table__actions">
                                    <a href="{{ route('admin.ulasan.show', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Lihat Ulasan</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ad-empty">
                                    <strong>Produk tidak ditemukan</strong>
                                    Coba kata kunci lain.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ad-card__foot">
            @include('admin.partials.pager', ['items' => $produk])
        </div>
    </section>
@endsection