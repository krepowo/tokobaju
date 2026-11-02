@extends('layouts.admin')

@section('title', 'Kelola Stok & Ukuran')
@section('active', 'stok')
@section('crumb_current', 'Kelola Stok & Ukuran')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Kelola Stok &amp; Ukuran</h1>
            <p class="ad-sub">Stok dicatat per kombinasi ukuran dan warna. Stok tidak boleh bernilai negatif.</p>
        </div>
    </div>

    <section class="ad-card">
        <div class="ad-card__head">
            <form method="GET" action="{{ route('admin.stok.index') }}" class="ad-toolbar" style="flex:1">
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
                        <th class="is-center">Varian</th>
                        <th class="is-center">Total Stok</th>
                        <th class="is-center">Varian Habis</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produk as $p)
                        <tr>
                            <td>
                                <div class="ad-produk">
                                    <img src="{{ asset($p->gambar) }}" alt="" class="ad-thumb">
                                    <div>
                                        <div class="ad-produk__nama">{{ $p->nama_produk }}</div>
                                        <span class="ad-table__sub">{{ $p->kategori?->nama_kategori ?? 'Tanpa kategori' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="is-center">{{ $p->ukuran_stok_count }}</td>
                            <td class="is-center">
                                @if ((int) $p->total_stok <= 5)
                                    <span class="ad-badge ad-badge--warn">{{ (int) $p->total_stok }}</span>
                                @else
                                    {{ (int) $p->total_stok }}
                                @endif
                            </td>
                            <td class="is-center">
                                @if ($p->varian_habis > 0)
                                    <span class="ad-badge ad-badge--danger">{{ $p->varian_habis }}</span>
                                @else
                                    <span class="ad-badge ad-badge--success">0</span>
                                @endif
                            </td>
                            <td>
                                <div class="ad-table__actions">
                                    <a href="{{ route('admin.stok.kelola', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Kelola Stok</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ad-empty">
                                    <strong>Tidak ada produk</strong>
                                    Tambahkan produk terlebih dahulu pada menu Kelola Produk.
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