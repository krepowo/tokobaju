@extends('layouts.admin')

@section('title', 'Stok ' . $produk->nama_produk)
@section('active', 'stok')
@section('crumb', 'Kelola Stok & Ukuran')
@section('crumb_current', 'Kelola Stok')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">{{ $produk->nama_produk }}</h1>
            <p class="ad-sub">{{ $produk->kategori?->nama_kategori ?? 'Tanpa kategori' }} &middot; Rp {{ number_format((float) $produk->harga, 0, ',', '.') }}</p>
        </div>
        <div class="ad-btn-row">
            <a href="{{ route('admin.produk.edit', $produk) }}" class="ad-btn ad-btn--ghost">Ubah Produk</a>
            <a href="{{ route('admin.stok.index') }}" class="ad-btn ad-btn--ghost">Kembali</a>
        </div>
    </div>

    <section class="ad-card">
        <div class="ad-card__head">
            <h2 class="ad-section-title">Ukuran, Warna &amp; Stok</h2>
            <button type="button" class="ad-btn" data-drawer="#drawer-varian">
                @include('admin.partials.icon', ['name' => 'plus'])
                Tambah Varian
            </button>
        </div>

        @if ($varian->isEmpty())
            <div class="ad-empty">
                <strong>Belum ada varian</strong>
                Tambahkan kombinasi ukuran dan warna beserta jumlah stoknya.
            </div>
        @else
            <div class="ad-table-wrap">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Ukuran</th>
                            <th>Warna</th>
                            <th class="is-center">Stok</th>
                            <th class="is-center">Status</th>
                            <th class="is-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($varian as $v)
                            <tr>
                                <td>
                                    <input form="varian-{{ $v->id_ukuran }}" type="text" name="ukuran" value="{{ $v->ukuran }}"
                                           class="ad-input" maxlength="10" style="max-width:80px" required>
                                </td>
                                <td>
                                    <input form="varian-{{ $v->id_ukuran }}" type="text" name="warna" value="{{ $v->warna }}"
                                           class="ad-input" maxlength="30" style="max-width:150px" required>
                                </td>
                                <td class="is-center">
                                    <input form="varian-{{ $v->id_ukuran }}" type="number" name="stok" value="{{ $v->stok }}"
                                           class="ad-input" min="0" style="max-width:96px" required>
                                    @if ($v->stok <= 0)
                                        <span class="ad-badge ad-badge--danger" style="margin-top:6px">Habis</span>
                                    @elseif ($v->stok <= 5)
                                        <span class="ad-badge ad-badge--warn" style="margin-top:6px">Menipis</span>
                                    @endif
                                </td>
                                <td class="is-center">
                                    <label class="ad-hint" style="display:inline-flex; align-items:center; gap:6px">
                                        <input form="varian-{{ $v->id_ukuran }}" type="checkbox" name="aktif" value="1" @checked($v->aktif)>
                                        Aktif
                                    </label>
                                </td>
                                <td>
                                    <div class="ad-table__actions">
                                        <form id="varian-{{ $v->id_ukuran }}" method="POST"
                                              action="{{ route('admin.stok.update', [$produk, $v]) }}">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                        <button type="submit" form="varian-{{ $v->id_ukuran }}" class="ad-btn ad-btn--ghost ad-btn--sm">Simpan</button>
                                        <form method="POST" action="{{ route('admin.stok.hapus', [$produk, $v]) }}" data-confirm="Hapus varian {{ $v->ukuran }} / {{ $v->warna }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="ad-drawer" id="drawer-varian">
        <div class="ad-drawer__backdrop" data-drawer-close></div>
        <div class="ad-drawer__panel">
            <form method="POST" action="{{ route('admin.stok.simpan', $produk) }}" style="display:contents">
                @csrf
                <div class="ad-drawer__head">
                    <h2 class="ad-section-title">Tambah Varian</h2>
                    <button type="button" class="ad-close" data-drawer-close aria-label="Tutup">&times;</button>
                </div>
                <div class="ad-drawer__body">
                    <div class="ad-form">
                        <div class="ad-field">
                            <label class="ad-label" for="ukuran-baru">Ukuran <span>*</span></label>
                            <input type="text" id="ukuran-baru" name="ukuran" class="ad-input" maxlength="10" placeholder="S, M, L, XL, 39, 40" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="warna-baru">Warna <span>*</span></label>
                            <input type="text" id="warna-baru" name="warna" class="ad-input" maxlength="30" placeholder="Hitam, Cream, Navy" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="stok-baru">Jumlah Stok <span>*</span></label>
                            <input type="number" id="stok-baru" name="stok" class="ad-input" min="0" value="0" required>
                            <span class="ad-hint">Stok tidak boleh negatif.</span>
                        </div>
                    </div>
                </div>
                <div class="ad-drawer__foot">
                    <button type="button" class="ad-btn ad-btn--ghost" data-drawer-close>Batal</button>
                    <button type="submit" class="ad-btn">Simpan Varian</button>
                </div>
            </form>
        </div>
    </div>
@endsection