@extends('layouts.admin')

@section('title', 'Jasa Pengiriman')
@section('nav', 'jasa')
@section('breadcrumb', 'Kelola Produk, Kategori & Pengiriman')

@section('content')
    @include('admin.partials.produk-tabs', ['aktif' => 'jasa'])

    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.jasa.index') }}" class="toolbar-filters">
            <label class="search-field">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari jasa pengiriman..." aria-label="Cari jasa pengiriman">
            </label>
        </form>

        <button type="button" class="btn-gold" data-dialog-open="dialog-tambah-jasa">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Jasa
        </button>
    </div>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Jasa</th>
                    <th>Jumlah Pulau Terdaftar</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarJasa as $jasa)
                    @php
                        $nilaiTarif = $jasa->tarif->pluck('tarif', 'pulau_tujuan');
                        $tanpaTarif = $jasa->tarif->isEmpty();
                    @endphp

                    <tr>
                        <td><span class="cell-title">{{ $jasa->nama_jasa }}</span></td>
                        <td>
                            <span class="jasa-pulau">{{ $jasa->labelPulau() }}</span>
                            @if ($tanpaTarif)
                                <span class="chip-warn" title="Belum ada tarif untuk pulau mana pun">Belum ada tarif</span>
                            @endif
                        </td>
                        <td class="is-right col-aksi">
                            <div class="row-actions">
                                <button type="button" class="btn-ghost btn-tarif"
                                        data-dialog-open="dialog-tarif-{{ $jasa->id_jasa }}">
                                    <svg viewBox="0 0 24 24"><path d="M5 7h9M18 7h1M5 12h3M12 12h7M5 17h9M18 17h1"/><circle cx="16" cy="7" r="1.7"/><circle cx="10" cy="12" r="1.7"/><circle cx="16" cy="17" r="1.7"/></svg>
                                    Atur Tarif
                                </button>

                                <button type="button" class="icon-btn" title="Ubah {{ $jasa->nama_jasa }}"
                                        data-dialog-open="dialog-ubah-{{ $jasa->id_jasa }}">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m14.5 6.5 3 3"/></svg>
                                </button>

                                <form method="POST" action="{{ route('admin.jasa.destroy', $jasa) }}"
                                      onsubmit="return confirm('Hapus jasa {{ addslashes($jasa->nama_jasa) }}? {{ $jasa->tarif->count() }} tarifnya ikut terhapus permanen.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn--danger" title="Hapus {{ $jasa->nama_jasa }}">
                                        <svg viewBox="0 0 24 24"><path d="M5 7h14M10 7V5h4v2M6.5 7l.8 12.2A1.6 1.6 0 0 0 8.9 20.8h6.2a1.6 1.6 0 0 0 1.6-1.6L17.5 7"/><path d="M10.5 11v6M13.5 11v6"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">
                            {{ $cari !== '' ? 'Tidak ada jasa pengiriman yang cocok dengan "'.$cari.'".' : 'Belum ada jasa pengiriman. Tambahkan jasa pertama.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>
                @if ($daftarJasa->total() > 0)
                    Menampilkan {{ $daftarJasa->firstItem() }}–{{ $daftarJasa->lastItem() }} dari {{ $daftarJasa->total() }} jasa pengiriman
                @else
                    Menampilkan 0 dari 0 jasa pengiriman
                @endif
            </span>

            <div class="admin-pagination__links">
                @if ($daftarJasa->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $daftarJasa->previousPageUrl() }}">Sebelumnya</a>
                @endif

                @if ($daftarJasa->hasMorePages())
                    <a href="{{ $daftarJasa->nextPageUrl() }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Dialog tambah jasa (sekaligus isi tarif tiap pulau) --}}
    <dialog class="dialog" id="dialog-tambah-jasa">
        <form method="POST" action="{{ route('admin.jasa.store') }}">
            @csrf
            <div class="dialog__head">
                <h3>Tambah Jasa Pengiriman</h3>
                <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                    <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="dialog__body">
                <div class="field">
                    <label for="jasa-baru">Nama Jasa <span class="req">*</span></label>
                    <input id="jasa-baru" name="nama_jasa" value="{{ old('nama_jasa') }}" maxlength="50" required placeholder="Contoh: JNE">
                </div>

                <div class="field">
                    <label>Tarif per Pulau <span class="req">*</span></label>
                    <span class="field__hint" style="margin:0 0 8px">
                        Isi tarif untuk pulau yang dilayani. Pulau yang dikosongkan tidak akan terdaftar,
                        dan jumlah pulau di tabel mengikuti yang terisi.
                    </span>

                    @foreach ($daftarPulau as $pulau)
                        <div class="tarif-row">
                            <span class="tarif-row__label">{{ $pulau }}</span>
                            <span class="tarif-row__input">
                                <span class="tarif-row__prefix">Rp</span>
                                <input type="number" name="tarif[{{ $pulau }}]" min="0" step="100"
                                       value="{{ old('tarif.'.$pulau) }}" placeholder="0"
                                       aria-label="Tarif {{ $pulau }}">
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="dialog__foot">
                <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                <button type="submit" class="btn-gold">Simpan</button>
            </div>
        </form>
    </dialog>

    @foreach ($daftarJasa as $jasa)
        {{-- Dialog ubah nama jasa --}}
        <dialog class="dialog dialog--sm" id="dialog-ubah-{{ $jasa->id_jasa }}">
            <form method="POST" action="{{ route('admin.jasa.update', $jasa) }}">
                @csrf
                @method('PUT')
                <div class="dialog__head">
                    <h3>Ubah Jasa Pengiriman</h3>
                    <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="dialog__body">
                    <div class="field">
                        <label for="jasa-{{ $jasa->id_jasa }}">Nama Jasa <span class="req">*</span></label>
                        <input id="jasa-{{ $jasa->id_jasa }}" name="nama_jasa" value="{{ $jasa->nama_jasa }}" maxlength="50" required>
                    </div>
                </div>

                <div class="dialog__foot">
                    <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-gold">Simpan Perubahan</button>
                </div>
            </form>
        </dialog>

        {{-- Dialog atur tarif per pulau --}}
        <dialog class="dialog" id="dialog-tarif-{{ $jasa->id_jasa }}">
            <form method="POST" action="{{ route('admin.jasa.tarif.update', $jasa) }}">
                @csrf
                @method('PUT')
                <div class="dialog__head">
                    <h3>Atur Tarif &middot; {{ $jasa->nama_jasa }}</h3>
                    <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="dialog__body">
                    <p class="dialog__desc">
                        Tarif tetap ongkos kirim per pulau tujuan. Kosongkan pulau yang tidak dilayani —
                        pulau tersebut otomatis tidak terdaftar dan pelanggan dari pulau itu akan diminta
                        memilih jasa lain saat checkout.
                    </p>

                    @foreach ($daftarPulau as $pulau)
                        <div class="tarif-row">
                            <span class="tarif-row__label">{{ $pulau }}</span>
                            <span class="tarif-row__input">
                                <span class="tarif-row__prefix">Rp</span>
                                <input type="number" name="tarif[{{ $pulau }}]" min="0" step="100"
                                       value="{{ (int) ($nilaiTarif[$pulau] ?? 0) ?: '' }}" placeholder="0"
                                       aria-label="Tarif {{ $pulau }}">
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="dialog__foot">
                    <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-gold">Simpan Tarif</button>
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
                var gagal = document.getElementById('dialog-tambah-jasa');
                if (gagal) gagal.showModal();
            @endif
        })();
    </script>
@endpush
