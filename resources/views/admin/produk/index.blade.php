@extends('layouts.admin')

@section('title', 'Kelola Produk')
@section('nav', 'produk')
@section('breadcrumb', 'Kelola Produk, Kategori & Pengiriman')

@section('content')
    @include('admin.partials.produk-tabs', ['aktif' => 'produk'])

    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.produk.index') }}" class="toolbar-filters">
            <label class="search-field">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari produk..." aria-label="Cari produk">
            </label>

            <label class="select-field">
                <select name="kategori" onchange="this.form.submit()" aria-label="Filter kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id_kategori }}" @selected($idKategori === $kategori->id_kategori)>
                            {{ $kategori->nama_kategori }} ({{ $kategori->produk_count }})
                        </option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
            </label>
        </form>

        <button type="button" class="btn-gold" data-dialog-open="dialog-tambah-produk">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Produk
        </button>
    </div>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-thumb">Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="is-right">Harga</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarProduk as $produk)
                    <tr>
                        <td class="col-thumb">
                            <img class="thumb" src="{{ $produk->gambar_url }}" alt="{{ $produk->nama_produk }}" loading="lazy">
                        </td>
                        <td>
                            <span class="cell-title">{{ $produk->nama_produk }}</span>
                            <span class="cell-sub">{{ \Illuminate\Support\Str::limit($produk->deskripsi, 62) }}</span>
                        </td>
                        <td>
                            <span class="chip-kategori">{{ $produk->kategori?->nama_kategori ?? '—' }}</span>
                        </td>
                        <td class="is-right mono">{{ $produk->harga_formatted }}</td>
                        <td class="is-right col-aksi">
                            <div class="row-actions">
                                <button type="button" class="icon-btn" title="Ubah {{ $produk->nama_produk }}"
                                        data-dialog-open="dialog-ubah-{{ $produk->id_produk }}">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m14.5 6.5 3 3"/></svg>
                                </button>

                                <form method="POST" action="{{ route('admin.produk.destroy', $produk) }}"
                                      onsubmit="return confirm('Hapus produk {{ addslashes($produk->nama_produk) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn--danger" title="Hapus {{ $produk->nama_produk }}">
                                        <svg viewBox="0 0 24 24"><path d="M5 7h14M10 7V5h4v2M6.5 7l.8 12.2A1.6 1.6 0 0 0 8.9 20.8h6.2a1.6 1.6 0 0 0 1.6-1.6L17.5 7"/><path d="M10.5 11v6M13.5 11v6"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">
                            {{ $cari !== '' || $idKategori ? 'Tidak ada produk yang cocok dengan filter ini.' : 'Belum ada produk. Tambahkan produk pertama.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>
                @if ($daftarProduk->total() > 0)
                    Menampilkan {{ $daftarProduk->firstItem() }}–{{ $daftarProduk->lastItem() }} dari {{ $daftarProduk->total() }} produk
                @else
                    Menampilkan 0 dari 0 produk
                @endif
            </span>

            <div class="admin-pagination__links">
                @if ($daftarProduk->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $daftarProduk->previousPageUrl() }}">Sebelumnya</a>
                @endif

                @if ($daftarProduk->hasMorePages())
                    <a href="{{ $daftarProduk->nextPageUrl() }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Dialog tambah produk --}}
    <dialog class="dialog" id="dialog-tambah-produk">
        <form method="POST" action="{{ route('admin.produk.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="dialog__head">
                <h3>Tambah Produk</h3>
                <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                    <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="dialog__body">
                <div class="field">
                    <label for="baru-nama">Nama Produk <span class="req">*</span></label>
                    <input id="baru-nama" name="nama_produk" value="{{ old('nama_produk') }}" maxlength="150" required placeholder="Contoh: Kemeja Oxford Katun Pria">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="baru-kategori">Kategori <span class="req">*</span></label>
                        <select id="baru-kategori" name="id_kategori" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori->id_kategori }}" @selected(old('id_kategori') === $kategori->id_kategori)>{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="baru-harga">Harga (Rp) <span class="req">*</span></label>
                        <input id="baru-harga" name="harga" type="number" min="0" step="100" value="{{ old('harga') }}" required placeholder="249000">
                    </div>
                </div>

                <div class="field">
                    <label for="baru-deskripsi">Deskripsi <span class="req">*</span></label>
                    <textarea id="baru-deskripsi" name="deskripsi" rows="3" maxlength="2000" required placeholder="Bahan, potongan, dan keterangan produk">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="field">
                    <label for="baru-spek">Spesifikasi Ukuran</label>
                    <textarea id="baru-spek" name="spesifikasi_ukuran" rows="2" maxlength="2000" placeholder="Contoh: M: lebar 52 cm, panjang 70 cm">{{ old('spesifikasi_ukuran') }}</textarea>
                </div>

                <div class="field">
                    <label for="baru-gambar">Gambar Produk <span class="req">*</span></label>
                    <input id="baru-gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" required>
                    <span class="field__hint">JPG, PNG, atau WEBP &middot; maksimal 4 MB</span>
                </div>
            </div>

            <div class="dialog__foot">
                <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                <button type="submit" class="btn-gold">Simpan Produk</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog ubah produk (satu per baris) --}}
    @foreach ($daftarProduk as $produk)
        <dialog class="dialog" id="dialog-ubah-{{ $produk->id_produk }}">
            <form method="POST" action="{{ route('admin.produk.update', $produk) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="dialog__head">
                    <h3>Ubah Produk</h3>
                    <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="dialog__body">
                    <div class="field">
                        <label for="nama-{{ $produk->id_produk }}">Nama Produk <span class="req">*</span></label>
                        <input id="nama-{{ $produk->id_produk }}" name="nama_produk" value="{{ $produk->nama_produk }}" maxlength="150" required>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="kategori-{{ $produk->id_produk }}">Kategori <span class="req">*</span></label>
                            <select id="kategori-{{ $produk->id_produk }}" name="id_kategori" required>
                                @foreach ($kategoriList as $kategori)
                                    <option value="{{ $kategori->id_kategori }}" @selected($produk->id_kategori === $kategori->id_kategori)>{{ $kategori->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="harga-{{ $produk->id_produk }}">Harga (Rp) <span class="req">*</span></label>
                            <input id="harga-{{ $produk->id_produk }}" name="harga" type="number" min="0" step="100" value="{{ (int) $produk->harga }}" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="desk-{{ $produk->id_produk }}">Deskripsi <span class="req">*</span></label>
                        <textarea id="desk-{{ $produk->id_produk }}" name="deskripsi" rows="3" maxlength="2000" required>{{ $produk->deskripsi }}</textarea>
                    </div>

                    <div class="field">
                        <label for="spek-{{ $produk->id_produk }}">Spesifikasi Ukuran</label>
                        <textarea id="spek-{{ $produk->id_produk }}" name="spesifikasi_ukuran" rows="2" maxlength="2000">{{ $produk->spesifikasi_ukuran }}</textarea>
                    </div>

                    <div class="field">
                        <label for="gambar-{{ $produk->id_produk }}">Gambar Produk</label>
                        <input id="gambar-{{ $produk->id_produk }}" name="gambar" type="file" accept="image/jpeg,image/png,image/webp">
                        <span class="field__hint">Biarkan kosong jika tidak ingin mengganti gambar.</span>
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
                var gagal = document.getElementById('dialog-tambah-produk');
                if (gagal) gagal.showModal();
            @endif
        })();
    </script>
@endpush
