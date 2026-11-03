@extends('layouts.admin')

@section('title', 'Ulasan Pembeli')
@section('nav', 'ulasan')
@section('breadcrumb', 'Ulasan Pembeli')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.ulasan.index') }}" class="toolbar-filters">
            <select name="produk" class="select-field" style="min-width:240px" onchange="this.form.submit()" aria-label="Pilih produk">
                @forelse ($daftarProduk as $p)
                    <option value="{{ $p->id_produk }}" @selected($produk && $p->id_produk === $produk->id_produk)>
                        {{ $p->nama_produk }} ({{ $p->ulasan_count }})
                    </option>
                @empty
                    <option value="">Belum ada ulasan</option>
                @endforelse
            </select>

            <select name="bintang" class="select-field" onchange="this.form.submit()" aria-label="Filter bintang">
                <option value="0">Semua bintang</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected($bintang === $i)>{{ $i }} bintang</option>
                @endfor
            </select>

            <input type="search" name="q" value="{{ $q }}" class="search-field" placeholder="Cari komentar / nama pelanggan...">

            @if ($produk)
                <a href="{{ route('admin.ulasan.ekspor', ['produk' => $produk->id_produk]) }}" class="btn-ghost">Ekspor CSV</a>
            @endif
        </form>
    </div>

    @if ($produk && $ringkasan)
        <div class="stat-grid">
            <article class="stat-card stat-card--accent">
                <span class="stat-card__label">Rata-rata Rating</span>
                <span class="stat-card__value">{{ number_format($ringkasan['rata_rata'], 1, ',', '.') }} &#9733;</span>
                <span class="stat-card__hint">dari {{ $ringkasan['jumlah'] }} ulasan</span>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Total Ulasan</span>
                <span class="stat-card__value">{{ $ringkasan['jumlah'] }}</span>
                <span class="stat-card__hint">{{ $produk->nama_produk }}</span>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Puas (4&ndash;5 &#9733;)</span>
                <span class="stat-card__value">{{ $ringkasan['puas'] }}%</span>
                <span class="stat-card__hint">Pembeli menilai positif</span>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Terjual</span>
                <span class="stat-card__value">{{ $ringkasan['terjual'] }} pcs</span>
                <span class="stat-card__hint">Total unit produk ini</span>
            </article>
        </div>

        <section class="panel">
            <header class="panel__head"><h2>Sebaran Bintang</h2></header>
            <div style="padding:0 18px 18px">
                @for ($i = 5; $i >= 1; $i--)
                    @php
                        $jml = (int) ($ringkasan['sebaran'][$i] ?? 0);
                        $persen = $ringkasan['jumlah'] > 0 ? round($jml / $ringkasan['jumlah'] * 100) : 0;
                    @endphp
                    <div style="display:flex;align-items:center;gap:10px;margin:7px 0;font-size:13px">
                        <span style="width:48px;color:var(--muted)">{{ $i }} &#9733;</span>
                        <div class="progress" style="flex:1"><span style="width:{{ $persen }}%"></span></div>
                        <span style="width:78px;text-align:right;color:var(--muted)">{{ $jml }} ({{ $persen }}%)</span>
                    </div>
                @endfor
            </div>
        </section>

        <section class="panel">
            <header class="panel__head">
                <h2>Daftar Ulasan</h2>
                <span class="panel__meta">{{ $ulasan->total() }} ulasan</span>
            </header>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Rating</th>
                        <th>Ukuran / Warna</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ulasan as $u)
                        <tr>
                            <td>{{ $u->user?->nama ?? '—' }}</td>
                            <td><span class="badge badge--selesai">{{ $u->rating }} &#9733;</span></td>
                            <td class="cell-sub">{{ $u->detailPesanan?->ukuranStok?->ukuran ?? '—' }} / {{ $u->detailPesanan?->ukuranStok?->warna ?? '—' }}</td>
                            <td class="cell-sub">{{ \Illuminate\Support\Str::limit($u->komentar, 90) ?: '—' }}</td>
                            <td class="cell-sub">{{ $u->tanggal?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada ulasan pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="admin-pagination">
                <span>Menampilkan {{ $ulasan->firstItem() ?? 0 }}–{{ $ulasan->lastItem() ?? 0 }} dari {{ $ulasan->total() }} ulasan</span>
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
        </section>
    @else
        <section class="panel"><p class="empty">Belum ada produk yang memiliki ulasan.</p></section>
    @endif
@endsection
