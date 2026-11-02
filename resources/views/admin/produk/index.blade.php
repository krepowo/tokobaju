@extends('layouts.admin')

@section('title', 'Kelola Produk')
@section('active', 'produk')
@section('crumb', 'Kelola Produk & Kategori')
@section('crumb_current', 'Produk')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Kelola Produk</h1>
            <p class="ad-sub">{{ $produk->total() }} produk terdaftar dalam {{ $totalKategori }} kategori.</p>
        </div>
    </div>

    <section class="ad-card">
        @include('admin.partials.produkTabs', ['tab' => 'produk'])

        <div class="ad-card__head">
            <form method="GET" action="{{ route('admin.produk.index') }}" class="ad-toolbar" style="flex:1">
                <label class="ad-search">
                    @include('admin.partials.icon', ['name' => 'search'])
                    <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Cari nama produk...">
                </label>
                <select name="kategori" class="ad-select" style="max-width:200px" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $kat)
                        <option value="{{ $kat->id_kategori }}" @selected($kategoriTerpilih === $kat->id_kategori)>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ad-btn ad-btn--ghost">Cari</button>
            </form>

            <a href="{{ route('admin.produk.create') }}" class="ad-btn">
                @include('admin.partials.icon', ['name' => 'plus'])
                Tambah Produk
            </a>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="is-right">Harga</th>
                        <th class="is-center">Varian</th>
                        <th class="is-center">Total Stok</th>
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
                            <td class="is-muted">{{ $p->kategori?->nama_kategori ?? '—' }}</td>
                            <td class="is-right">{{ $rupiah($p->harga) }}</td>
                            <td class="is-center">{{ $p->ukuran_stok_count }}</td>
                            <td class="is-center">
                                @if ((int) $p->total_stok <= 0)
                                    <span class="ad-badge ad-badge--danger">Habis</span>
                                @elseif ((int) $p->total_stok <= 5)
                                    <span class="ad-badge ad-badge--warn">{{ (int) $p->total_stok }}</span>
                                @else
                                    {{ (int) $p->total_stok }}
                                @endif
                            </td>
                            <td>
                                <div class="ad-table__actions">
                                    <a href="{{ route('admin.stok.kelola', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Stok</a>
                                    <a href="{{ route('admin.produk.edit', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.produk.destroy', $p) }}" data-confirm="Hapus produk {{ $p->nama_produk }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ad-empty">
                                    <strong>Produk tidak ditemukan</strong>
                                    Ubah kata kunci pencarian atau tambahkan produk baru.
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