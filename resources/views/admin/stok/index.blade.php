@extends('layouts.admin')

@section('title', 'Kelola Stok & Ukuran')
@section('nav', 'stok')
@section('breadcrumb', 'Kelola Stok, Ukuran & Warna')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.stok.index') }}" class="toolbar-filters">
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
    </div>

    <p class="admin-hint">
        Angka <strong class="stok--menipis">oranye</strong> berarti total stok &le; {{ $ambang }} pcs,
        <strong class="stok--habis">merah</strong> berarti stok habis.
    </p>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-produk">Produk</th>
                    <th>Warna</th>
                    <th class="is-right">Total Stok</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarProduk as $produk)
                    @php
                        $total = (int) $produk->total_stok;
                        $kelasStok = $total <= 0 ? 'stok--habis' : ($total <= $ambang ? 'stok--menipis' : 'stok--aman');
                    @endphp

                    <tr>
                        <td class="col-produk">
                            <div class="produk-sel">
                                <img class="thumb" src="{{ $produk->gambar_url }}" alt="{{ $produk->nama_produk }}">
                                <span class="produk-sel__teks">
                                    <span class="cell-title">{{ $produk->nama_produk }}</span>
                                    <span class="cell-sub">{{ $produk->kategori?->nama_kategori ?? '—' }}</span>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="warna-jumlah">{{ $produk->jumlah_warna }} warna</span>
                            <span class="cell-sub">{{ implode(', ', $produk->daftar_warna) ?: 'belum ada varian' }}</span>
                        </td>
                        <td class="is-right">
                            <span class="stok {{ $kelasStok }}">{{ $total }} pcs</span>
                        </td>
                        <td class="is-right col-aksi">
                            <button type="button" class="btn-dark" data-dialog-open="stok-{{ $produk->id_produk }}">
                                Atur Stok
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">
                            {{ $cari !== '' || $idKategori ? 'Tidak ada produk yang cocok dengan filter ini.' : 'Belum ada produk.' }}
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

    {{-- Drawer Atur Stok per produk --}}
    @foreach ($daftarProduk as $produk)
        @php
            $peta = $produk->varianUkuran->keyBy(fn ($varian) => $varian->warna.'|'.$varian->ukuran);
            $nilai = [];
            foreach ($produk->daftar_warna as $warna) {
                foreach ($produk->daftar_ukuran as $ukuran) {
                    $nilai[$warna][$ukuran] = (int) ($peta[$warna.'|'.$ukuran]->stok ?? 0);
                }
            }

            $aktifUkuran = [];
            foreach ($produk->daftar_ukuran as $ukuran) {
                $aktifUkuran[$ukuran] = $produk->varianUkuran
                    ->where('ukuran', $ukuran)
                    ->contains(fn ($varian) => (bool) $varian->aktif);
            }

            $semuaAngka = $produk->daftar_ukuran !== []
                && count(array_filter($produk->daftar_ukuran, 'is_numeric')) === count($produk->daftar_ukuran);

            $awal = [
                'warna' => $produk->daftar_warna,
                'ukuran' => $produk->daftar_ukuran,
                'nilai' => $nilai,
                'aktif' => $aktifUkuran,
            ];
        @endphp

        <dialog class="drawer" id="stok-{{ $produk->id_produk }}">
            <form method="POST" action="{{ route('admin.stok.update', $produk) }}"
                  class="drawer__form" data-matriks
                  data-awal="{{ json_encode($awal, JSON_UNESCAPED_UNICODE) }}"
                  data-jenis="{{ $semuaAngka ? 'angka' : 'huruf' }}">
                @csrf
                @method('PUT')

                <header class="drawer__head">
                    <div>
                        <span class="chip-kategori">{{ $produk->kategori?->nama_kategori ?? 'Tanpa kategori' }}</span>
                        <h3>{{ $produk->nama_produk }}</h3>
                    </div>
                    <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </header>

                <div class="drawer__body">
                    <section class="drawer__section">
                        <h4>Warna <span class="drawer__count" data-hitung="warna"></span></h4>
                        <div class="chip-list" data-chip="warna"></div>

                        <button type="button" class="chip-add" data-tambah="warna">+ Tambah warna</button>

                        <div class="chip-input" data-input="warna" hidden>
                            <input type="text" maxlength="30" placeholder="Nama warna (mis. Navy Blue)">
                            <button type="button" class="btn-mini" data-simpan="warna">Tambah</button>
                            <button type="button" class="btn-mini btn-mini--ghost" data-batal="warna">Batal</button>
                        </div>
                    </section>

                    <section class="drawer__section">
                        <h4>
                            Ukuran <span class="drawer__jenis">{{ $semuaAngka ? 'Sepatu — Angka' : 'Pakaian — Huruf' }}</span>
                        </h4>
                        <div class="chip-list" data-chip="ukuran"></div>

                        <button type="button" class="chip-add" data-tambah="ukuran">
                            + Tambah ukuran {{ $semuaAngka ? '(mis. 43, 44)' : '(mis. XXL, All Size)' }}
                        </button>

                        <div class="chip-input" data-input="ukuran" hidden>
                            <input type="text" maxlength="10" placeholder="{{ $semuaAngka ? 'Ukuran angka (mis. 43)' : 'Ukuran huruf (mis. XXL)' }}">
                            <button type="button" class="btn-mini" data-simpan="ukuran">Tambah</button>
                            <button type="button" class="btn-mini btn-mini--ghost" data-batal="ukuran">Batal</button>
                        </div>
                    </section>

                    <section class="drawer__section">
                        <h4>Stok <span class="drawer__jenis">kuantitas fisik per varian</span></h4>
                        <div class="matriks-wrap" data-matriks-wrap></div>
                        <div data-aktif-wrap></div>
                        <p class="drawer__hint">
                            Matikan toggle pada kolom ukuran untuk menonaktifkan seluruh warna pada ukuran tersebut.
                        </p>
                    </section>
                </div>

                <footer class="drawer__foot">
                    <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-gold">Simpan</button>
                </footer>
            </form>
        </dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (function () {
            function esc(nilai) {
                return String(nilai)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            document.querySelectorAll('form[data-matriks]').forEach(function (form) {
                var state = JSON.parse(form.dataset.awal || '{}');
                state.warna = state.warna || [];
                state.ukuran = state.ukuran || [];
                state.nilai = state.nilai || {};
                state.aktif = state.aktif || {};

                var elChipWarna = form.querySelector('[data-chip="warna"]');
                var elChipUkuran = form.querySelector('[data-chip="ukuran"]');
                var elMatriks = form.querySelector('[data-matriks-wrap]');
                var elAktif = form.querySelector('[data-aktif-wrap]');
                var hitungWarna = form.querySelector('[data-hitung="warna"]');

                function ambilNilai() {
                    elMatriks.querySelectorAll('input[data-sel]').forEach(function (input) {
                        var w = input.dataset.warna;
                        var u = input.dataset.ukuran;
                        state.nilai[w] = state.nilai[w] || {};
                        state.nilai[w][u] = parseInt(input.value || '0', 10) || 0;
                    });
                }

                function nilai(w, u) {
                    return state.nilai[w] && state.nilai[w][u] != null ? state.nilai[w][u] : 0;
                }

                function render() {
                    elChipWarna.innerHTML = state.warna.map(function (w, i) {
                        return '<span class="chip">' + esc(w) +
                            '<button type="button" class="chip__x" data-hapus="warna" data-index="' + i + '" aria-label="Hapus ' + esc(w) + '">&times;</button>' +
                            '<input type="hidden" name="warna[]" value="' + esc(w) + '"></span>';
                    }).join('');

                    elChipUkuran.innerHTML = state.ukuran.map(function (u, i) {
                        return '<span class="chip">' + esc(u) +
                            '<button type="button" class="chip__x" data-hapus="ukuran" data-index="' + i + '" aria-label="Hapus ' + esc(u) + '">&times;</button>' +
                            '<input type="hidden" name="ukuran[]" value="' + esc(u) + '"></span>';
                    }).join('');

                    hitungWarna.textContent = state.warna.length + ' varian warna';

                    var html = '<table class="matriks"><thead><tr><th>Warna</th>';
                    state.ukuran.forEach(function (u) {
                        var aktif = state.aktif[u] !== false;
                        html += '<th><span class="matriks__ukuran">' + esc(u) + '</span>' +
                            '<label class="switch" title="Aktifkan ukuran ' + esc(u) + '">' +
                            '<input type="checkbox" data-toggle="' + esc(u) + '"' + (aktif ? ' checked' : '') + '><span></span></label></th>';
                    });
                    html += '</tr></thead><tbody>';

                    state.warna.forEach(function (w) {
                        html += '<tr><th class="matriks__warna">' + esc(w) + '</th>';
                        state.ukuran.forEach(function (u) {
                            var kelas = state.aktif[u] === false ? ' matriks__input is-off' : ' matriks__input';
                            html += '<td><input type="number" class="' + kelas + '" min="0" max="100000" step="1" ' +
                                'data-sel data-warna="' + esc(w) + '" data-ukuran="' + esc(u) + '" ' +
                                'name="stok[' + esc(w) + '][' + esc(u) + ']" value="' + nilai(w, u) + '"></td>';
                        });
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    elMatriks.innerHTML = html;

                    elAktif.innerHTML = state.ukuran.map(function (u) {
                        return '<input type="hidden" name="aktif[' + esc(u) + ']" value="' + (state.aktif[u] !== false ? 1 : 0) + '">';
                    }).join('');
                }

                // tambah / hapus chip
                form.addEventListener('click', function (event) {
                    var hapus = event.target.closest('[data-hapus]');
                    if (hapus) {
                        ambilNilai();
                        var jenis = hapus.dataset.hapus;
                        var index = parseInt(hapus.dataset.index, 10);
                        state[jenis].splice(index, 1);
                        render();
                        return;
                    }

                    var tambah = event.target.closest('[data-tambah]');
                    if (tambah) {
                        var kotak = form.querySelector('[data-input="' + tambah.dataset.tambah + '"]');
                        kotak.hidden = false;
                        kotak.querySelector('input').focus();
                        return;
                    }

                    var batal = event.target.closest('[data-batal]');
                    if (batal) {
                        var kotakBatal = form.querySelector('[data-input="' + batal.dataset.batal + '"]');
                        kotakBatal.hidden = true;
                        kotakBatal.querySelector('input').value = '';
                        return;
                    }

                    var simpan = event.target.closest('[data-simpan]');
                    if (simpan) {
                        var jenisBaru = simpan.dataset.simpan;
                        var kotakBaru = form.querySelector('[data-input="' + jenisBaru + '"]');
                        var inputBaru = kotakBaru.querySelector('input');
                        var teks = inputBaru.value.trim();

                        if (teks === '') {
                            inputBaru.focus();
                            return;
                        }

                        var sudahAda = state[jenisBaru].some(function (item) {
                            return item.toLowerCase() === teks.toLowerCase();
                        });

                        if (sudahAda) {
                            inputBaru.value = '';
                            inputBaru.placeholder = 'Sudah ada, pakai nama lain';
                            inputBaru.focus();
                            return;
                        }

                        ambilNilai();
                        state[jenisBaru].push(teks);
                        state.aktif[teks] = true;
                        inputBaru.value = '';
                        kotakBaru.hidden = true;
                        render();
                    }
                });

                // toggle aktif per ukuran
                form.addEventListener('change', function (event) {
                    var toggle = event.target.closest('[data-toggle]');
                    if (!toggle) return;

                    state.aktif[toggle.dataset.toggle] = toggle.checked;

                    elAktif.innerHTML = state.ukuran.map(function (u) {
                        return '<input type="hidden" name="aktif[' + esc(u) + ']" value="' + (state.aktif[u] !== false ? 1 : 0) + '">';
                    }).join('');

                    elMatriks.querySelectorAll('input[data-sel]').forEach(function (input) {
                        if (input.dataset.ukuran === toggle.dataset.toggle) {
                            input.classList.toggle('is-off', !toggle.checked);
                        }
                    });
                });

                render();
            });

            // buka / tutup dialog
            document.querySelectorAll('[data-dialog-open]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var dialog = document.getElementById(trigger.dataset.dialogOpen);
                    if (dialog) dialog.showModal();
                });
            });

            document.querySelectorAll('.drawer, .dialog').forEach(function (dialog) {
                dialog.querySelectorAll('[data-dialog-close]').forEach(function (button) {
                    button.addEventListener('click', function () { dialog.close(); });
                });
                dialog.addEventListener('click', function (event) {
                    if (event.target === dialog) dialog.close();
                });
            });
        })();
    </script>
@endpush
