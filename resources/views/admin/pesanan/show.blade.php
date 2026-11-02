@extends('layouts.admin')

@section('title', 'Pesanan ' . ($pesanan->invoice?->no_invoice ?? ''))
@section('active', 'pesanan')
@section('crumb', 'Pesanan & Invoice')
@section('crumb_current', $pesanan->invoice?->no_invoice ?? 'Detail Pesanan')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $alur = \App\Support\StatusPesanan::alur();
        $posisi = array_search($pesanan->status, $alur, true);
        $subtotal = $pesanan->detail->sum('subtotal');
        $pembayaran = $pesanan->pembayaran->first();
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Pesanan {{ $pesanan->invoice?->no_invoice }}</h1>
            <p class="ad-sub">
                Dibuat {{ $pesanan->tanggal_pesanan?->translatedFormat('d F Y, H:i') }} oleh {{ $pesanan->pelanggan?->nama }}
            </p>
        </div>
        <div class="ad-btn-row">
            <a href="{{ route('admin.pesanan.cetak', $pesanan) }}" target="_blank" class="ad-btn">
                @include('admin.partials.icon', ['name' => 'print'])
                Cetak Invoice
            </a>
            @if ($pesanan->status === \App\Models\Pesanan::MENUNGGU_VERIFIKASI)
                <a href="{{ route('admin.verifikasi.show', $pesanan) }}" class="ad-btn ad-btn--dark">Verifikasi Bayar</a>
            @endif
            @if ($pesanan->status === \App\Models\Pesanan::DIPROSES)
                <a href="{{ route('admin.kirim.index') }}" class="ad-btn ad-btn--dark">Input Resi</a>
            @endif
            <a href="{{ route('admin.pesanan.index') }}" class="ad-btn ad-btn--ghost">Kembali</a>
        </div>
    </div>

    <div class="ad-card" style="margin-bottom:24px">
        <div class="ad-card__body" style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap">
            <div class="ad-stepper">
                @foreach ($alur as $i => $tahap)
                    @php
                        $kelas = 'ad-step';
                        if ($posisi !== false && $i < $posisi) $kelas .= ' is-done';
                        if ($posisi !== false && $i === $posisi) $kelas .= ' is-now';
                    @endphp
                    <span class="{{ $kelas }}">{{ $i + 1 }}. {{ \App\Support\StatusPesanan::label($tahap) }}</span>
                @endforeach
            </div>
            @include('admin.partials.badge', ['status' => $pesanan->status])
        </div>
    </div>

    <div class="ad-grid-2">
        <div>
            <section class="ad-card">
                <div class="ad-card__head">
                    <h2 class="ad-section-title">Rincian Produk</h2>
                    <span class="ad-badge ad-badge--muted">{{ $pesanan->detail->count() }} item</span>
                </div>
                <div class="ad-table-wrap">
                    <table class="ad-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Ukuran / Warna</th>
                                <th class="is-center">Jumlah</th>
                                <th class="is-right">Harga</th>
                                <th class="is-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pesanan->detail as $d)
                                <tr>
                                    <td>
                                        <div class="ad-produk">
                                            <img src="{{ asset($d->ukuranStok?->produk?->gambar ?? 'images/figma/home/logo-mark.png') }}" alt="" class="ad-thumb">
                                            <span class="ad-produk__nama">{{ $d->ukuranStok?->produk?->nama_produk ?? 'Produk dihapus' }}</span>
                                        </div>
                                    </td>
                                    <td class="is-muted">{{ $d->ukuranStok?->ukuran }} / {{ $d->ukuranStok?->warna }}</td>
                                    <td class="is-center">{{ $d->jumlah }}</td>
                                    <td class="is-right">{{ $rupiah($d->harga_satuan) }}</td>
                                    <td class="is-right is-strong">{{ $rupiah($d->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="ad-card__body" style="border-top:1px solid var(--ad-line-soft)">
                    <dl class="ad-dl">
                        <div class="ad-dl__row"><dt>Subtotal produk</dt><dd>{{ $rupiah($subtotal) }}</dd></div>
                        <div class="ad-dl__row">
                            <dt>Ongkos kirim ({{ $pesanan->jasa?->nama_jasa }} &middot; {{ $pesanan->pulau_tujuan }})</dt>
                            <dd>{{ $rupiah($pesanan->ongkir) }}</dd>
                        </div>
                        <div class="ad-dl__row ad-dl__row--total"><dt>Total pembayaran</dt><dd>{{ $rupiah($pesanan->total) }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="ad-card">
                <div class="ad-card__head">
                    <h2 class="ad-section-title">Pengiriman</h2>
                    @if ($pesanan->pengiriman)
                        <span class="ad-badge ad-badge--indigo">{{ $pesanan->pengiriman->no_resi }}</span>
                    @else
                        <span class="ad-badge ad-badge--muted">Belum dikirim</span>
                    @endif
                </div>
                <div class="ad-card__body">
                    <dl class="ad-dl">
                        <div class="ad-dl__row"><dt>Jasa pengiriman</dt><dd>{{ $pesanan->jasa?->nama_jasa ?? '—' }}</dd></div>
                        <div class="ad-dl__row"><dt>Pulau tujuan</dt><dd>{{ $pesanan->pulau_tujuan }}</dd></div>
                        <div class="ad-dl__row"><dt>Nomor resi</dt><dd>{{ $pesanan->pengiriman?->no_resi ?? 'Belum diisi' }}</dd></div>
                        <div class="ad-dl__row"><dt>Tanggal kirim</dt><dd>{{ $pesanan->pengiriman?->tanggal_kirim?->translatedFormat('d M Y H:i') ?? '—' }}</dd></div>
                        <div class="ad-dl__row"><dt>Catatan</dt><dd>{{ $pesanan->pengiriman?->catatan ?? '—' }}</dd></div>
                    </dl>
                    <div style="margin-top:16px">
                        <span class="ad-label">Alamat tujuan</span>
                        <p class="ad-sub" style="margin-top:4px">{{ $pesanan->alamat_tujuan }}</p>
                    </div>
                </div>
            </section>
        </div>

        <div>
            <section class="ad-card">
                <div class="ad-card__head">
                    <h2 class="ad-section-title">Invoice</h2>
                </div>
                <div class="ad-card__body">
                    <dl class="ad-dl">
                        <div class="ad-dl__row"><dt>No. invoice</dt><dd>{{ $pesanan->invoice?->no_invoice ?? '—' }}</dd></div>
                        <div class="ad-dl__row"><dt>Terbit</dt><dd>{{ $pesanan->invoice?->tanggal_terbit?->translatedFormat('d M Y H:i') }}</dd></div>
                        <div class="ad-dl__row"><dt>Total tagihan</dt><dd class="ad-total">{{ $rupiah($pesanan->invoice?->total_tagihan ?? $pesanan->total) }}</dd></div>
                        <div class="ad-dl__row"><dt>Batas bayar</dt><dd>{{ $pesanan->batas_bayar?->translatedFormat('d M Y H:i') }}</dd></div>
                        <div class="ad-dl__row"><dt>Rekening tujuan</dt><dd>{{ $pesanan->invoice?->rekening_tujuan }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="ad-card">
                <div class="ad-card__head">
                    <h2 class="ad-section-title">Pembayaran</h2>
                    @if ($pembayaran)
                        <span class="ad-badge ad-badge--{{ $pembayaran->status_verifikasi === 'disetujui' ? 'success' : ($pembayaran->status_verifikasi === 'ditolak' ? 'danger' : 'warn') }}">
                            {{ ucfirst($pembayaran->status_verifikasi) }}
                        </span>
                    @else
                        <span class="ad-badge ad-badge--muted">Belum dibayar</span>
                    @endif
                </div>
                <div class="ad-card__body">
                    @if ($pembayaran)
                        <div class="ad-bukti" style="min-height:180px">
                            @if ($pembayaran->bukti_transfer && file_exists(public_path($pembayaran->bukti_transfer)))
                                <img src="{{ asset($pembayaran->bukti_transfer) }}" alt="Bukti transfer">
                            @else
                                <div class="ad-bukti__kosong">
                                    Berkas bukti tidak ditemukan di server.<br>
                                    <code>{{ $pembayaran->bukti_transfer }}</code>
                                </div>
                            @endif
                        </div>
                        <dl class="ad-dl" style="margin-top:16px">
                            <div class="ad-dl__row"><dt>Diunggah</dt><dd>{{ $pembayaran->tanggal_upload?->translatedFormat('d M Y H:i') }}</dd></div>
                            <div class="ad-dl__row"><dt>Diverifikasi oleh</dt><dd>{{ $pembayaran->verifikator?->nama ?? '—' }}</dd></div>
                            @if ($pembayaran->alasan_tolak)
                                <div class="ad-dl__row"><dt>Alasan ditolak</dt><dd>{{ $pembayaran->alasan_tolak }}</dd></div>
                            @endif
                        </dl>
                    @else
                        <div class="ad-bukti__kosong">Pelanggan belum mengunggah bukti transfer.</div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection