@extends('layouts.admin')

@section('title', 'Proses Pengiriman')
@section('active', 'kirim')
@section('crumb_current', 'Proses Pengiriman')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Proses Pengiriman</h1>
            <p class="ad-sub">Isi nomor resi setelah paket diserahkan ke ekspedisi. Pelanggan melihat resi dan status Dikirim.</p>
        </div>
    </div>

    <section class="ad-card">
        <nav class="ad-tabs">
            <a href="{{ route('admin.kirim.index') }}" class="ad-tab {{ $tab !== 'sudah' ? 'is-active' : '' }}">
                Perlu Dikirim <span class="ad-tab__count">{{ $perluDikirim }}</span>
            </a>
            <a href="{{ route('admin.kirim.index', ['tab' => 'sudah']) }}" class="ad-tab {{ $tab === 'sudah' ? 'is-active' : '' }}">
                Sudah Dikirim <span class="ad-tab__count">{{ $sudahDikirim }}</span>
            </a>
        </nav>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Jasa</th>
                        <th>No. Resi</th>
                        <th>Status</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $p)
                        <tr>
                            <td class="is-strong">
                                <a href="{{ route('admin.pesanan.show', $p) }}">{{ $p->invoice?->no_invoice ?? '—' }}</a>
                                <span class="ad-table__sub">{{ $p->tanggal_pesanan?->translatedFormat('d M Y') }}</span>
                            </td>
                            <td class="is-muted">
                                {{ $p->pelanggan?->nama ?? '—' }}
                                <span class="ad-table__sub">{{ \Illuminate\Support\Str::limit($p->alamat_tujuan, 42) }}</span>
                            </td>
                            <td class="is-muted">{{ $p->jasa?->nama_jasa ?? '—' }}</td>
                            <td class="is-muted">{{ $p->pengiriman?->no_resi ?? '—' }}</td>
                            <td>@include('admin.partials.badge', ['status' => $p->status])</td>
                            <td>
                                <div class="ad-table__actions">
                                    <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" data-drawer="#drawer-{{ $p->id_pesanan }}">
                                        {{ $p->pengiriman ? 'Ubah Resi' : 'Input Resi' }}
                                    </button>
                                </div>

                                <div class="ad-drawer" id="drawer-{{ $p->id_pesanan }}">
                                    <div class="ad-drawer__backdrop" data-drawer-close></div>
                                    <div class="ad-drawer__panel">
                                        <form method="POST" action="{{ route('admin.kirim.simpan', $p) }}" style="display:contents">
                                            @csrf
                                            <div class="ad-drawer__head">
                                                <div>
                                                    <h2 class="ad-section-title">Input Pengiriman</h2>
                                                    <p class="ad-sub">{{ $p->invoice?->no_invoice }}</p>
                                                </div>
                                                <button type="button" class="ad-close" data-drawer-close aria-label="Tutup">&times;</button>
                                            </div>
                                            <div class="ad-drawer__body">
                                                <div class="ad-form">
                                                    <div class="ad-field">
                                                        <label class="ad-label">Jasa pengiriman</label>
                                                        <input type="text" class="ad-input" value="{{ $p->jasa?->nama_jasa }}" disabled>
                                                    </div>
                                                    <div class="ad-field">
                                                        <label class="ad-label" for="resi-{{ $p->id_pesanan }}">Nomor resi <span>*</span></label>
                                                        <input type="text" id="resi-{{ $p->id_pesanan }}" name="no_resi" class="ad-input"
                                                               value="{{ $p->pengiriman?->no_resi }}" maxlength="50" required>
                                                    </div>
                                                    <div class="ad-field">
                                                        <label class="ad-label" for="catatan-{{ $p->id_pesanan }}">Catatan pengiriman</label>
                                                        <textarea id="catatan-{{ $p->id_pesanan }}" name="catatan" class="ad-textarea" maxlength="255"
                                                                  placeholder="Opsional, contoh: dikirim dari gudang Bandung.">{{ $p->pengiriman?->catatan }}</textarea>
                                                    </div>
                                                    <div class="ad-note">
                                                        Menyimpan resi akan mengubah status pesanan menjadi <strong>Dikirim</strong>.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ad-drawer__foot">
                                                <button type="button" class="ad-btn ad-btn--ghost" data-drawer-close>Batal</button>
                                                <button type="submit" class="ad-btn">Simpan Resi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ad-empty">
                                    <strong>{{ $tab === 'sudah' ? 'Belum ada pesanan dikirim' : 'Tidak ada pesanan yang perlu dikirim' }}</strong>
                                    {{ $tab === 'sudah' ? 'Pesanan yang sudah diisi resi akan tampil di sini.' : 'Pesanan masuk ke tahap ini setelah pembayaran disetujui.' }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ad-card__foot">
            @include('admin.partials.pager', ['items' => $pesanan])
        </div>
    </section>
@endsection