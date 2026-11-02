@extends('layouts.admin')

@section('title', 'Jasa Pengiriman')
@section('active', 'produk')
@section('crumb', 'Kelola Produk & Kategori')
@section('crumb_current', 'Jasa Pengiriman')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Jasa Pengiriman</h1>
            <p class="ad-sub">Ongkos kirim memakai tarif tetap per jasa untuk tiap pulau tujuan.</p>
        </div>
    </div>

    <section class="ad-card">
        @include('admin.partials.produkTabs', ['tab' => 'jasa'])

        <div class="ad-card__head">
            <form method="GET" action="{{ route('admin.jasa.index') }}" class="ad-toolbar" style="flex:1">
                <label class="ad-search">
                    @include('admin.partials.icon', ['name' => 'search'])
                    <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Cari jasa pengiriman...">
                </label>
            </form>
            <button type="button" class="ad-btn" data-modal="#modal-jasa">
                @include('admin.partials.icon', ['name' => 'plus'])
                Tambah Jasa
            </button>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Nama Jasa</th>
                        <th>Jumlah Pulau Terdaftar</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jasa as $j)
                        <tr>
                            <td>
                                <div class="ad-produk">
                                    <span class="ad-avatar">{{ mb_strtoupper(mb_substr($j->nama_jasa, 0, 2)) }}</span>
                                    <span class="ad-produk__nama">{{ $j->nama_jasa }}</span>
                                </div>
                            </td>
                            <td class="is-muted">
                                @if ($j->tarif_count > 0)
                                    {{ $j->tarif_count }} Pulau Terdaftar
                                @else
                                    <span class="ad-badge ad-badge--warn">Belum ada tarif</span>
                                @endif
                            </td>
                            <td>
                                <div class="ad-table__actions">
                                    <a href="{{ route('admin.jasa.tarif', $j) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Atur Tarif</a>
                                    <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" data-modal="#modal-edit-{{ $j->id_jasa }}">Ubah</button>
                                    <form method="POST" action="{{ route('admin.jasa.destroy', $j) }}" data-confirm="Hapus jasa {{ $j->nama_jasa }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm">Hapus</button>
                                    </form>
                                </div>

                                <dialog class="ad-modal" id="modal-edit-{{ $j->id_jasa }}">
                                    <form method="POST" action="{{ route('admin.jasa.update', $j) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="ad-modal__head">
                                            <h3 class="ad-modal__title">Ubah Jasa Pengiriman</h3>
                                        </div>
                                        <div class="ad-modal__body">
                                            <div class="ad-field">
                                                <label class="ad-label">Nama Jasa</label>
                                                <input type="text" name="nama_jasa" value="{{ $j->nama_jasa }}" class="ad-input" maxlength="50" required>
                                            </div>
                                        </div>
                                        <div class="ad-modal__foot">
                                            <button type="button" class="ad-btn ad-btn--ghost" data-modal-close>Batal</button>
                                            <button type="submit" class="ad-btn">Simpan</button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="ad-empty">
                                    <strong>Belum ada jasa pengiriman</strong>
                                    Tambahkan JNE, J&amp;T, atau jasa lain agar pelanggan bisa memilih ongkir.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ad-card__foot">
            @include('admin.partials.pager', ['items' => $jasa])
        </div>
    </section>

    <dialog class="ad-modal" id="modal-jasa">
        <form method="POST" action="{{ route('admin.jasa.store') }}">
            @csrf
            <div class="ad-modal__head">
                <h3 class="ad-modal__title">Tambah Jasa Pengiriman</h3>
            </div>
            <div class="ad-modal__body">
                <div class="ad-field">
                    <label class="ad-label">Nama Jasa <span>*</span></label>
                    <input type="text" name="nama_jasa" class="ad-input" maxlength="50" placeholder="Contoh: JNE Reguler" required>
                    <span class="ad-hint">Tarif per pulau diatur setelah jasa dibuat.</span>
                </div>
            </div>
            <div class="ad-modal__foot">
                <button type="button" class="ad-btn ad-btn--ghost" data-modal-close>Batal</button>
                <button type="submit" class="ad-btn">Tambah</button>
            </div>
        </form>
    </dialog>
@endsection