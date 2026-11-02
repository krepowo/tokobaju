@extends('layouts.admin')

@section('title', 'Kelola Kategori')
@section('active', 'produk')
@section('crumb', 'Kelola Produk & Kategori')
@section('crumb_current', 'Kategori')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Kelola Kategori</h1>
            <p class="ad-sub">Kategori dipakai untuk filter katalog pelanggan.</p>
        </div>
    </div>

    <section class="ad-card">
        @include('admin.partials.produkTabs', ['tab' => 'kategori'])

        <div class="ad-card__head">
            <h2 class="ad-section-title">Daftar Kategori</h2>
            <form method="POST" action="{{ route('admin.kategori.store') }}" class="ad-toolbar">
                @csrf
                <input type="text" name="nama_kategori" class="ad-input" placeholder="Nama kategori baru" maxlength="50" required style="min-width:220px">
                <button type="submit" class="ad-btn">@include('admin.partials.icon', ['name' => 'plus']) Tambah</button>
            </form>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th class="is-center">Jumlah Produk</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategori as $kat)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('admin.kategori.update', $kat) }}" id="kat-{{ $kat->id_kategori }}" style="display:flex; gap:8px; align-items:center">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nama_kategori" value="{{ $kat->nama_kategori }}" class="ad-input" maxlength="50" required style="max-width:280px">
                                </form>
                            </td>
                            <td class="is-center">{{ $kat->produk_count }}</td>
                            <td>
                                <div class="ad-table__actions">
                                    <button type="submit" form="kat-{{ $kat->id_kategori }}" class="ad-btn ad-btn--ghost ad-btn--sm">Simpan</button>
                                    <form method="POST" action="{{ route('admin.kategori.destroy', $kat) }}" data-confirm="Hapus kategori {{ $kat->nama_kategori }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm" @disabled($kat->produk_count > 0)>Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="ad-empty">
                                    <strong>Belum ada kategori</strong>
                                    Tambahkan kategori pertama lewat kolom di atas.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection