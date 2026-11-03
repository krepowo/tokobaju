@extends('layouts.admin')

@section('title', 'Kelola Kategori')
@section('nav', 'kategori')
@section('breadcrumb', 'Kelola Produk, Kategori & Pengiriman')

@section('content')
    @include('admin.partials.produk-tabs', ['aktif' => 'kategori'])

    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.kategori.index') }}" class="toolbar-filters">
            <label class="search-field">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari kategori..." aria-label="Cari kategori">
            </label>
        </form>

        <button type="button" class="btn-gold" data-dialog-open="dialog-tambah-kategori">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Kategori
        </button>
    </div>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Kategori</th>
                    <th class="is-right">Jumlah Produk</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarKategori as $kategori)
                    <tr>
                        <td><span class="cell-title">{{ $kategori->nama_kategori }}</span></td>
                        <td class="is-right mono">
                            <span class="count-pill">{{ $kategori->produk_count }}</span>
                        </td>
                        <td class="is-right col-aksi">
                            <div class="row-actions">
                                <button type="button" class="icon-btn" title="Ubah {{ $kategori->nama_kategori }}"
                                        data-dialog-open="dialog-ubah-{{ $kategori->id_kategori }}">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m14.5 6.5 3 3"/></svg>
                                </button>

                                <form method="POST" action="{{ route('admin.kategori.destroy', $kategori) }}"
                                      onsubmit="return confirm('Hapus kategori {{ addslashes($kategori->nama_kategori) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn--danger" title="Hapus {{ $kategori->nama_kategori }}">
                                        <svg viewBox="0 0 24 24"><path d="M5 7h14M10 7V5h4v2M6.5 7l.8 12.2A1.6 1.6 0 0 0 8.9 20.8h6.2a1.6 1.6 0 0 0 1.6-1.6L17.5 7"/><path d="M10.5 11v6M13.5 11v6"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">
                            {{ $cari !== '' ? 'Tidak ada kategori yang cocok dengan "'.$cari.'".' : 'Belum ada kategori.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>
                @if ($daftarKategori->total() > 0)
                    Menampilkan {{ $daftarKategori->firstItem() }}–{{ $daftarKategori->lastItem() }} dari {{ $daftarKategori->total() }} kategori
                @else
                    Menampilkan 0 dari 0 kategori
                @endif
            </span>

            <div class="admin-pagination__links">
                @if ($daftarKategori->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $daftarKategori->previousPageUrl() }}">Sebelumnya</a>
                @endif

                @if ($daftarKategori->hasMorePages())
                    <a href="{{ $daftarKategori->nextPageUrl() }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Dialog tambah kategori --}}
    <dialog class="dialog dialog--sm" id="dialog-tambah-kategori">
        <form method="POST" action="{{ route('admin.kategori.store') }}">
            @csrf
            <div class="dialog__head">
                <h3>Tambah Kategori</h3>
                <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                    <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="dialog__body">
                <div class="field">
                    <label for="kategori-baru">Nama Kategori <span class="req">*</span></label>
                    <input id="kategori-baru" name="nama_kategori" value="{{ old('nama_kategori') }}" maxlength="50" required placeholder="Contoh: Aksesori">
                </div>
            </div>

            <div class="dialog__foot">
                <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                <button type="submit" class="btn-gold">Simpan</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog ubah kategori --}}
    @foreach ($daftarKategori as $kategori)
        <dialog class="dialog dialog--sm" id="dialog-ubah-{{ $kategori->id_kategori }}">
            <form method="POST" action="{{ route('admin.kategori.update', $kategori) }}">
                @csrf
                @method('PUT')
                <div class="dialog__head">
                    <h3>Ubah Kategori</h3>
                    <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="dialog__body">
                    <div class="field">
                        <label for="kategori-{{ $kategori->id_kategori }}">Nama Kategori <span class="req">*</span></label>
                        <input id="kategori-{{ $kategori->id_kategori }}" name="nama_kategori" value="{{ $kategori->nama_kategori }}" maxlength="50" required>
                    </div>
                </div>

                <div class="dialog__foot">
                    <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-gold">Simpan Perubahan</button>
                </div>
            </form>
        </dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('[data-dialog-open]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var dialog = document.getElementById(trigger.dataset.dialogOpen);
                    if (dialog) dialog.showModal();
                });
            });

            document.querySelectorAll('.dialog').forEach(function (dialog) {
                dialog.querySelectorAll('[data-dialog-close]').forEach(function (button) {
                    button.addEventListener('click', function () { dialog.close(); });
                });
                dialog.addEventListener('click', function (event) {
                    if (event.target === dialog) dialog.close();
                });
            });

            @if ($errors->any())
                var gagal = document.getElementById('dialog-tambah-kategori');
                if (gagal) gagal.showModal();
            @endif
        })();
    </script>
@endpush
