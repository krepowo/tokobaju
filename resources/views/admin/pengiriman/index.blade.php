@extends('layouts.admin')

@section('title', 'Proses Pengiriman')
@section('nav', 'kirim')
@section('breadcrumb', 'Proses Pengiriman')

@section('content')
    <div class="stat-grid stat-grid--tiga">
        <article class="stat-card stat-card--accent">
            <span class="stat-card__icon">
                <svg viewBox="0 0 24 24"><path d="M2.8 6.5h9.4v9.7H2.8z"/><path d="M12.2 9.8h4l3 3v3.4h-7z"/><circle cx="6.4" cy="18.2" r="1.6"/><circle cx="16.2" cy="18.2" r="1.6"/></svg>
            </span>
            <span class="stat-card__label">Total Pesanan</span>
            <span class="stat-card__value">{{ $ringkasan['total'] }}</span>
            <span class="stat-card__hint">Sedang dalam proses pengiriman</span>
        </article>

        <article class="stat-card">
            <span class="stat-card__icon stat-card__icon--amber">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.2"/><path d="M12 7.8V12l2.8 1.8"/></svg>
            </span>
            <span class="stat-card__label">Perlu Diproses</span>
            <span class="stat-card__value">{{ $ringkasan['perlu_diproses'] }}</span>
            <span class="stat-card__hint">Sudah dibayar, belum ada nomor resi</span>
        </article>

        <article class="stat-card">
            <span class="stat-card__icon stat-card__icon--blue">
                <svg viewBox="0 0 24 24"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
            </span>
            <span class="stat-card__label">Dikirim</span>
            <span class="stat-card__value">{{ $ringkasan['dikirim'] }}</span>
            <span class="stat-card__hint">Nomor resi sudah diinput</span>
        </article>
    </div>

    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.pengiriman.index') }}" class="toolbar-filters">
            <label class="search-field">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari no. invoice..." aria-label="Cari pesanan">
            </label>

            <label class="select-field">
                <select name="status" onchange="this.form.submit()" aria-label="Filter status pengiriman">
                    <option value="semua">Semua Status Pengiriman</option>
                    @foreach ($daftarStatus as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
            </label>
        </form>
    </div>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Pelanggan</th>
                    <th>Kota</th>
                    <th class="is-right">Jumlah Item</th>
                    <th>Status</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarPesanan as $pesanan)
                    @php
                        $jumlahItem = (int) $pesanan->detailPesanan->sum('jumlah');
                        $kota = \App\Support\Alamat::kota($pesanan->alamat_tujuan);
                    @endphp

                    <tr>
                        <td class="mono">{{ $pesanan->invoice?->no_invoice ?? '—' }}</td>
                        <td><span class="cell-title">{{ $pesanan->user?->nama ?? '—' }}</span></td>
                        <td>{{ $kota }}</td>
                        <td class="is-right mono">{{ $jumlahItem }} item</td>
                        <td><span class="badge badge--{{ $pesanan->status }}">{{ $pesanan->statusLabel() }}</span></td>
                        <td class="is-right col-aksi">
                            @if ($pesanan->status === 'diproses')
                                <button type="button" class="btn-dark" data-dialog-open="kirim-{{ $pesanan->id_pesanan }}">
                                    Input Pengiriman
                                </button>
                            @else
                                <button type="button" class="resi-chip" data-dialog-open="kirim-{{ $pesanan->id_pesanan }}"
                                        title="Klik untuk mengubah nomor resi">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m14.5 6.5 3 3"/></svg>
                                    {{ $pesanan->jasa?->nama_jasa ?? '—' }} &middot; {{ $pesanan->pengiriman?->no_resi ?? 'tanpa resi' }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            Tidak ada pesanan yang sedang diproses pengirimannya.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>
                @if ($daftarPesanan->total() > 0)
                    Menampilkan {{ $daftarPesanan->firstItem() }}–{{ $daftarPesanan->lastItem() }} dari {{ $daftarPesanan->total() }} pesanan
                @else
                    Menampilkan 0 dari 0 pesanan
                @endif
            </span>

            <div class="admin-pagination__links">
                @if ($daftarPesanan->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $daftarPesanan->previousPageUrl() }}">Sebelumnya</a>
                @endif

                @if ($daftarPesanan->hasMorePages())
                    <a href="{{ $daftarPesanan->nextPageUrl() }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Drawer input nomor resi --}}
    @foreach ($daftarPesanan as $pesanan)
        @php
            $telepon = \App\Support\Alamat::telepon($pesanan->alamat_tujuan, $pesanan->user?->no_telepon);
        @endphp

        <dialog class="drawer" id="kirim-{{ $pesanan->id_pesanan }}">
            <form method="POST" action="{{ route('admin.pengiriman.update', $pesanan) }}" class="drawer__form">
                @csrf
                @method('PUT')

                <header class="drawer__head">
                    <div>
                        <span class="chip-kategori">{{ $pesanan->invoice?->no_invoice ?? 'Tanpa invoice' }}</span>
                        <h3>Input Pengiriman</h3>
                    </div>
                    <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </header>

                <div class="drawer__body">
                    <section class="drawer__section">
                        <h4>Penerima</h4>
                        <dl class="detail-list">
                            <dt>Pelanggan</dt>
                            <dd>{{ $pesanan->user?->nama ?? '—' }}</dd>

                            <dt>No. Telp</dt>
                            <dd class="mono">{{ $telepon }}</dd>

                            <dt>Alamat Kirim</dt>
                            <dd class="detail-list__alamat">{{ \App\Support\Alamat::tanpaNama($pesanan->alamat_tujuan) }}</dd>
                        </dl>
                    </section>

                    <section class="drawer__section">
                        <h4>Item Dipesan</h4>
                        <ul class="item-list">
                            @foreach ($pesanan->detailPesanan as $detail)
                                <li>
                                    <span>{{ $detail->ukuran?->produk?->nama_produk ?? 'Produk dihapus' }}</span>
                                    <small>{{ $detail->ukuran?->warna ?? '—' }} &middot; {{ $detail->ukuran?->ukuran ?? '—' }}</small>
                                    <strong>&times;{{ $detail->jumlah }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="drawer__section">
                        <h4>Status &amp; Jasa</h4>
                        <dl class="detail-list">
                            <dt>Status Sekarang</dt>
                            <dd><span class="badge badge--{{ $pesanan->status }}">{{ $pesanan->statusLabel() }}</span></dd>

                            <dt>Jasa Pengiriman</dt>
                            <dd>
                                {{ $pesanan->jasa?->nama_jasa ?? '—' }}
                                <span class="chip-warn chip-warn--netral">Pilihan Pelanggan</span>
                            </dd>

                            <dt>Pulau Tujuan</dt>
                            <dd>{{ $pesanan->pulau_tujuan }}</dd>
                        </dl>

                        <p class="drawer__hint">
                            Setelah nomor resi disimpan, status pesanan berubah menjadi <strong>Dikirim</strong>.
                        </p>
                    </section>

                    <section class="drawer__section">
                        <div class="field">
                            <label for="resi-{{ $pesanan->id_pesanan }}">Nomor Resi <span class="req">*</span></label>
                            <input id="resi-{{ $pesanan->id_pesanan }}" name="no_resi" maxlength="50" required
                                   value="{{ $pesanan->pengiriman?->no_resi }}"
                                   placeholder="Masukkan nomor resi pengiriman">
                        </div>

                        <div class="field">
                            <label for="catatan-{{ $pesanan->id_pesanan }}">Catatan Tambahan (opsional)</label>
                            <textarea id="catatan-{{ $pesanan->id_pesanan }}" name="catatan" rows="2" maxlength="255"
                                      placeholder="Contoh: Paket dijemput kurir pukul 14.00 WIB">{{ $pesanan->pengiriman?->catatan }}</textarea>
                        </div>
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
            document.querySelectorAll('[data-dialog-open]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var dialog = document.getElementById(trigger.dataset.dialogOpen);
                    if (dialog) dialog.showModal();
                });
            });

            document.querySelectorAll('.drawer').forEach(function (dialog) {
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
