@extends('layouts.admin')

@section('title', 'Invoice '.($pesanan->invoice?->no_invoice ?? ''))
@section('nav', 'pesanan')
@section('breadcrumb', 'Pesanan & Invoice')

@section('content')
    @php
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggal = \Carbon\Carbon::parse($pesanan->tanggal_pesanan);
        $subtotalProduk = $pesanan->detailPesanan->sum('subtotal');

        // alamat_tujuan menyimpan "Nama (telepon), jalan, ..." — nama sudah tampil terpisah.
        $alamat = \App\Support\Alamat::tanpaNama($pesanan->alamat_tujuan);
    @endphp

    <div class="invoice-actions">
        <a href="{{ route('admin.pesanan.index') }}" class="btn-ghost">
            <svg viewBox="0 0 24 24"><path d="M19 12H6M11 6.5 5.5 12 11 17.5"/></svg>
            Kembali
        </a>

        <button type="button" class="btn-dark" onclick="window.print()">
            <svg viewBox="0 0 24 24"><path d="M6.5 9V4h11v5"/><path d="M6.5 18H5a1.5 1.5 0 0 1-1.5-1.5v-5A1.5 1.5 0 0 1 5 10h14a1.5 1.5 0 0 1 1.5 1.5v5A1.5 1.5 0 0 1 19 18h-1.5"/><path d="M6.5 14h11v6h-11z"/></svg>
            Cetak Invoice
        </button>
    </div>

    <div class="invoice-grid">
        {{-- Kartu invoice --}}
        <article class="invoice">
            <header class="invoice__head">
                <div class="invoice__brand">
                    <span class="admin-brand__mark">G</span>
                    <span>
                        <strong>Gavin Fashion Store</strong>
                        <small>Atelier &amp; Ready-to-Wear</small>
                    </span>
                </div>

                <div class="invoice__title">
                    <h2>INVOICE</h2>
                    <span>Official Billing</span>
                </div>
            </header>

            <section class="invoice__meta">
                <div>
                    <h4>Tujuan Pengiriman</h4>
                    <strong>{{ $pesanan->user?->nama ?? '—' }}</strong>
                    <p>{{ $alamat }}</p>
                </div>

                <div class="invoice__meta-list">
                    <div><span>No. Invoice</span><strong class="mono">{{ $pesanan->invoice?->no_invoice ?? '—' }}</strong></div>
                    <div><span>Tanggal Pesanan</span><strong>{{ $tanggal->day.' '.$bulan[(int) $tanggal->month].' '.$tanggal->year }}, {{ $tanggal->format('H:i') }} WIB</strong></div>
                    <div><span>Metode Pembayaran</span><strong>{{ $pesanan->invoice?->rekening_tujuan ? 'Transfer Bank' : 'Transfer Bank' }}</strong></div>
                    <div><span>Rekening Tujuan</span><strong>{{ $pesanan->invoice?->rekening_tujuan ?? '—' }}</strong></div>
                </div>
            </section>

            <table class="invoice__items">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Warna</th>
                        <th>Ukuran</th>
                        <th class="is-right">Jumlah</th>
                        <th class="is-right">Harga</th>
                        <th class="is-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanan->detailPesanan as $detail)
                        <tr>
                            <td>{{ $detail->ukuran?->produk?->nama_produk ?? 'Produk dihapus' }}</td>
                            <td>{{ $detail->ukuran?->warna ?? '—' }}</td>
                            <td>{{ $detail->ukuran?->ukuran ?? '—' }}</td>
                            <td class="is-right mono">{{ $detail->jumlah }}</td>
                            <td class="is-right mono">Rp {{ number_format((float) $detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="is-right mono">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <footer class="invoice__foot">
                <p class="invoice__note">
                    Terima kasih telah berbelanja di Gavin Fashion Store. Dokumen ini diterbitkan
                    otomatis oleh sistem dan sah tanpa tanda tangan.
                </p>

                <div class="invoice__totals">
                    <div><span>Subtotal Produk</span><strong>Rp {{ number_format((float) $subtotalProduk, 0, ',', '.') }}</strong></div>
                    <div><span>Ongkos Kirim ({{ $pesanan->jasa?->nama_jasa ?? '—' }} &middot; {{ $pesanan->pulau_tujuan }})</span><strong>Rp {{ number_format((float) $pesanan->ongkir, 0, ',', '.') }}</strong></div>
                    <div class="invoice__total"><span>Total Tagihan</span><strong>Rp {{ number_format((float) $pesanan->total, 0, ',', '.') }}</strong></div>
                </div>
            </footer>
        </article>

        {{-- Panel kanan --}}
        <aside class="invoice-side">
            <section class="panel">
                <header class="panel__head"><h2>Status</h2></header>
                <span class="badge badge--{{ $pesanan->status }}">{{ $pesanan->statusLabel() }}</span>

                @if ($sisaWaktu !== null)
                    <div class="invoice-side__timer">
                        <span class="invoice-side__label">Sisa waktu pembayaran</span>
                        <strong>{{ $sisaWaktu }}</strong>
                        <div class="progress">
                            <span style="width: {{ $persenWaktu }}%"></span>
                        </div>
                    </div>
                @endif
            </section>

            <section class="panel">
                <header class="panel__head"><h2>Pengiriman</h2></header>

                <dl class="detail-list">
                    <dt>Jasa Pengiriman</dt>
                    <dd>{{ $pesanan->jasa?->nama_jasa ?? '—' }}</dd>

                    <dt>Pulau Tujuan</dt>
                    <dd>{{ $pesanan->pulau_tujuan }}</dd>

                    <dt>Nomor Resi</dt>
                    <dd class="mono">{{ $pesanan->pengiriman?->no_resi ?? '—' }}</dd>

                    <dt>Tanggal Kirim</dt>
                    <dd>
                        @if ($pesanan->pengiriman?->tanggal_kirim)
                            {{ \Carbon\Carbon::parse($pesanan->pengiriman->tanggal_kirim)->format('d/m/Y H:i') }}
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </section>

            @if ($pesanan->pembayaran->isNotEmpty())
                <section class="panel">
                    <header class="panel__head"><h2>Pembayaran</h2></header>

                    <dl class="detail-list">
                        @foreach ($pesanan->pembayaran as $bayar)
                            <dt>Tanggal Upload</dt>
                            <dd>{{ \Carbon\Carbon::parse($bayar->tanggal_upload)->format('d/m/Y H:i') }}</dd>

                            <dt>Status Verifikasi</dt>
                            <dd>
                                <span class="badge badge--{{ $bayar->status_verifikasi === 'disetujui' ? 'selesai' : ($bayar->status_verifikasi === 'ditolak' ? 'pembayaran_ditolak' : 'menunggu_verifikasi') }}">
                                    {{ ucfirst($bayar->status_verifikasi) }}
                                </span>
                            </dd>

                            @if ($bayar->alasan_tolak)
                                <dt>Alasan Ditolak</dt>
                                <dd>{{ $bayar->alasan_tolak }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </section>
            @endif
        </aside>
    </div>
@endsection
