@extends('layouts.admin')

@section('title', 'Verifikasi ' . ($pesanan->invoice?->no_invoice ?? ''))
@section('active', 'verifikasi')
@section('crumb', 'Verifikasi Bayar')
@section('crumb_current', 'Detail Verifikasi')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $subtotal = $pesanan->detail->sum('subtotal');
        $adaBukti = $pembayaran && file_exists(public_path($pembayaran->bukti_transfer));
        $bisaVerifikasi = $pesanan->status === \App\Models\Pesanan::MENUNGGU_VERIFIKASI;
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Verifikasi {{ $pesanan->invoice?->no_invoice }}</h1>
            <p class="ad-sub">Dibayar oleh {{ $pesanan->pelanggan?->nama }} &middot; total {{ $rupiah($pesanan->total) }}</p>
        </div>
        <div class="ad-btn-row">
            <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="ad-btn ad-btn--ghost">Detail Pesanan</a>
            <a href="{{ route('admin.verifikasi.index') }}" class="ad-btn ad-btn--ghost">Kembali</a>
        </div>
    </div>

    @if (! $bisaVerifikasi)
        <div class="ad-alert ad-alert--info">
            Pesanan ini berstatus <strong>{{ \App\Support\StatusPesanan::label($pesanan->status) }}</strong>.
            @if ($pembayaran?->alasan_tolak)
                Alasan penolakan sebelumnya: {{ $pembayaran->alasan_tolak }}
            @endif
        </div>
    @endif

    <div class="ad-grid-2">
        <section class="ad-card">
            <div class="ad-card__head">
                <h2 class="ad-section-title">Bukti Transfer</h2>
                @if ($pembayaran)
                    <span class="ad-badge ad-badge--{{ $pembayaran->status_verifikasi === 'disetujui' ? 'success' : ($pembayaran->status_verifikasi === 'ditolak' ? 'danger' : 'warn') }}">
                        {{ ucfirst($pembayaran->status_verifikasi) }}
                    </span>
                @endif
            </div>
            <div class="ad-card__body">
                <div class="ad-bukti">
                    @if ($adaBukti)
                        <img src="{{ asset($pembayaran->bukti_transfer) }}" alt="Bukti transfer">
                    @else
                        <div class="ad-bukti__kosong">
                            <strong>Berkas bukti tidak tersedia</strong>
                            {{ $pembayaran?->bukti_transfer ?? 'Pelanggan belum mengunggah bukti transfer.' }}
                        </div>
                    @endif
                </div>

                @if ($pembayaran)
                    <dl class="ad-dl" style="margin-top:18px">
                        <div class="ad-dl__row"><dt>Diunggah</dt><dd>{{ $pembayaran->tanggal_upload?->translatedFormat('d F Y, H:i') }}</dd></div>
                        <div class="ad-dl__row"><dt>Rekening tujuan</dt><dd>{{ $pesanan->invoice?->rekening_tujuan }}</dd></div>
                        <div class="ad-dl__row"><dt>Jumlah tagihan</dt><dd>{{ $rupiah($pesanan->invoice?->total_tagihan ?? $pesanan->total) }}</dd></div>
                    </dl>
                @endif
            </div>
        </section>

        <div>
            <section class="ad-card">
                <div class="ad-card__head">
                    <h2 class="ad-section-title">Ringkasan Pesanan</h2>
                </div>
                <div class="ad-card__body">
                    <dl class="ad-dl">
                        @foreach ($pesanan->detail as $d)
                            <div class="ad-dl__row">
                                <dt>{{ $d->ukuranStok?->produk?->nama_produk }} ({{ $d->ukuranStok?->ukuran }}/{{ $d->ukuranStok?->warna }}) &times; {{ $d->jumlah }}</dt>
                                <dd>{{ $rupiah($d->subtotal) }}</dd>
                            </div>
                        @endforeach
                        <div class="ad-dl__row"><dt>Subtotal</dt><dd>{{ $rupiah($subtotal) }}</dd></div>
                        <div class="ad-dl__row"><dt>Ongkir ({{ $pesanan->jasa?->nama_jasa }})</dt><dd>{{ $rupiah($pesanan->ongkir) }}</dd></div>
                        <div class="ad-dl__row ad-dl__row--total"><dt>Total</dt><dd>{{ $rupiah($pesanan->total) }}</dd></div>
                    </dl>

                    <div style="margin-top:16px">
                        <span class="ad-label">Alamat pengiriman</span>
                        <p class="ad-sub" style="margin-top:4px">{{ $pesanan->alamat_tujuan }}</p>
                    </div>
                </div>
            </section>

            @if ($bisaVerifikasi)
                <section class="ad-card">
                    <div class="ad-card__head">
                        <h2 class="ad-section-title">Keputusan</h2>
                    </div>
                    <div class="ad-card__body">
                        <p class="ad-sub" style="margin-bottom:16px">
                            Menyetujui pembayaran akan mengubah status pesanan menjadi <strong>Diproses</strong>.
                        </p>
                        <div class="ad-btn-row">
                            <form method="POST" action="{{ route('admin.verifikasi.setujui', $pesanan) }}"
                                  data-confirm="Setujui pembayaran {{ $pesanan->invoice?->no_invoice }}?">
                                @csrf
                                <button type="submit" class="ad-btn" @disabled(! $pembayaran)>Setujui Pembayaran</button>
                            </form>
                            <button type="button" class="ad-btn ad-btn--danger" data-modal="#modal-tolak" @disabled(! $pembayaran)>Tolak</button>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    <dialog class="ad-modal" id="modal-tolak">
        <form method="POST" action="{{ route('admin.verifikasi.tolak', $pesanan) }}">
            @csrf
            <div class="ad-modal__head">
                <h3 class="ad-modal__title">Tolak Pembayaran</h3>
            </div>
            <div class="ad-modal__body">
                <div class="ad-field">
                    <label class="ad-label" for="alasan_tolak">Alasan penolakan <span>*</span></label>
                    <textarea id="alasan_tolak" name="alasan_tolak" class="ad-textarea" maxlength="255" required
                              placeholder="Contoh: nominal transfer tidak sesuai dengan total tagihan."></textarea>
                    <span class="ad-hint">Alasan ini ditampilkan kepada pelanggan pada halaman pesanannya.</span>
                </div>
            </div>
            <div class="ad-modal__foot">
                <button type="button" class="ad-btn ad-btn--ghost" data-modal-close>Batal</button>
                <button type="submit" class="ad-btn ad-btn--danger">Tolak Pembayaran</button>
            </div>
        </form>
    </dialog>
@endsection