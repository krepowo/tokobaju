@extends('layouts.site')

@section('title', 'Detail Pesanan')
@section('active', 'pesanan')
@section('main-class', 'site-main--catalog')

@section('content')
<div class="pesanan">
    {{-- Tombol Kembali ke Pesanan Saya --}}
    <div class="pesanan__back-bar">
        <a href="{{ route('pesanan.index') }}" class="pesanan__back-btn">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Pesanan Saya</span>
        </a>
    </div>

    {{-- Header: Judul "Detail Pesanan", Badge Nomor Invoice, Tanggal Dibuat --}}
    <div class="pesanan__header-bar">
        <div class="pesanan__header-left">
            <h1 class="pesanan__title">Detail Pesanan</h1>
            <span class="pesanan__invoice-tag">{{ $order->invoice?->no_invoice ?? ('INV-' . substr($order->id_pesanan, 0, 8)) }}</span>
        </div>
        <p class="pesanan__create-date">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>Dibuat pada {{ $order->tanggal_pesanan?->format('d F Y, H:i') ?? $order->tanggal_pesanan?->format('d M Y, H:i') }} WIB</span>
        </p>
    </div>

    {{-- Stepper 5 Langkah Alur Pemrosesan Pesanan --}}
    @include('pesanan.partials.stepper')

    {{-- Layout Dua Kolom: Kiri (Rincian & Pengiriman) & Kanan (Panel Status / Pembayaran) --}}
    <div class="pesanan__grid">
        {{-- ======================================================== --}}
        {{-- KOLOM KIRI                                               --}}
        {{-- ======================================================== --}}
        <div class="pesanan__col-main">
            {{-- Card 1: Rincian Belanja --}}
            <section class="pesanan-card">
                <div class="pesanan-card__head">
                    <h2 class="pesanan-card__title">Rincian Belanja</h2>
                    <span class="pesanan-card__meta-tag">{{ $order->detailPesanan->count() }} Produk Fashion</span>
                </div>

                <div class="pesanan__item-list">
                    @foreach ($order->detailPesanan as $d)
                        <div class="pesanan-product-box">
                            <img src="{{ $d->ukuran->produk->gambar_url }}" alt="{{ $d->ukuran->produk->nama_produk }}" class="pesanan-product-box__img">
                            <div class="pesanan-product-box__info">
                                <h3 class="pesanan-product-box__name">{{ $d->ukuran->produk->nama_produk }}</h3>
                                <div class="pesanan-product-box__variant">Warna: {{ $d->ukuran->warna }} · Ukuran: {{ $d->ukuran->ukuran }}</div>
                                <div class="pesanan-product-box__price">{{ $d->jumlah }} x Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</div>
                            </div>
                            <div class="pesanan-product-box__subtotal">
                                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Rekapitulasi Biaya Belanja di bagian bawah Card 1 --}}
                <div class="pesanan-bill-recap">
                    <div class="pesanan-bill-recap__row">
                        <span>Subtotal Produk</span>
                        <span>Rp {{ number_format($order->total - $order->ongkir, 0, ',', '.') }}</span>
                    </div>
                    <div class="pesanan-bill-recap__row">
                        <span>Biaya Pengiriman ({{ $order->jasa->nama_jasa }} Reguler)</span>
                        <span>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                    </div>
                    <div class="pesanan-bill-recap__row pesanan-bill-recap__row--total">
                        <span>Total Pesanan</span>
                        <span class="pesanan-bill-recap__total-amount">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            {{-- Card 2: Informasi Pengiriman (atau Rincian Pengiriman di status Selesai) --}}
            <section class="pesanan-card">
                <div class="pesanan-card__head">
                    <h2 class="pesanan-card__title">
                        @if ($order->status === 'selesai')
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-right: 6px;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                            Rincian Pengiriman
                        @else
                            Informasi Pengiriman
                        @endif
                    </h2>
                    @if ($order->status === 'selesai')
                        <span class="pesanan-badge-green">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Paket Diterima
                        </span>
                    @endif
                </div>

                <div class="pesanan-shipping-grid">
                    {{-- Box 1: Alamat Pengiriman --}}
                    <div class="pesanan-subbox">
                        <div class="pesanan-subbox__label">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>ALAMAT PENGIRIMAN</span>
                        </div>
                        <div class="pesanan-subbox__name">{{ $order->user->nama ?? 'Pelanggan' }}</div>
                        <div class="pesanan-subbox__phone">{{ $order->user->no_telepon ?? '-' }}</div>
                        <p class="pesanan-subbox__text">{{ $order->alamat_tujuan }}</p>
                    </div>

                    {{-- Box 2: Jasa Pengiriman --}}
                    <div class="pesanan-subbox pesanan-subbox--courier">
                        <div class="pesanan-subbox__label">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                            <span>{{ $order->status === 'selesai' ? 'KURIR & RESI' : 'JASA PENGIRIMAN' }}</span>
                        </div>
                        <div class="pesanan-subbox__name">{{ $order->jasa->nama_jasa }} (Reguler)</div>
                        <p class="pesanan-subbox__hint">Estimasi tiba 2 - 3 hari setelah pengiriman barang.</p>

                        @if ($order->status === 'selesai')
                            <div class="pesanan-delivered-notice">
                                <div class="pesanan-delivered-notice__title">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Paket Diterima oleh {{ $order->user->nama ?? 'Pelanggan' }}</span>
                                </div>
                                <span class="pesanan-delivered-notice__time">
                                    {{ ($order->pengiriman?->tanggal_diterima ?? $order->updated_at)->format('d M Y, H:i') }} WIB (Solo - Banjarsari)
                                </span>
                            </div>
                        @else
                            <div class="pesanan-tracking-box">
                                <span class="pesanan-tracking-box__label">Nomor Resi:</span>
                                @if ($order->pengiriman?->no_resi)
                                    <div class="pesanan-tracking-box__val">
                                        <code>{{ $order->pengiriman->no_resi }}</code>
                                        <button type="button" class="btn-copy js-copy" data-copy="{{ $order->pengiriman->no_resi }}">Salin</button>
                                    </div>
                                @else
                                    <span class="pesanan-tracking-box__empty">Belum tersedia</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- KOLOM KANAN: PANEL STATUS & PEMBAYARAN                   --}}
        {{-- ======================================================== --}}
        <aside class="pesanan__col-aside">
            @php
                $pembayaran = $order->pembayaranTerakhir ?? $order->pembayaran?->sortByDesc('tanggal_upload')->first();
            @endphp

            {{-- ---------------------------------------------------- --}}
            {{-- 1. STATUS: MENUNGGU PEMBAYARAN / PEMBAYARAN DITOLAK   --}}
            {{-- ---------------------------------------------------- --}}
            @if (in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak'], true))
                {{-- Box Sisa Waktu Pembayaran (Amber Card) --}}
                @if ($order->batas_bayar)
                    <div class="countdown-card" id="countdownWrap" data-server-now="{{ now()->toISOString() }}" data-expire-at="{{ $order->batas_bayar->toISOString() }}">
                        <div class="countdown-card__header">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Sisa Waktu Pembayaran</span>
                        </div>
                        <div class="countdown-card__digits" id="countdownTimer">
                            <span id="cdHours">00</span> : <span id="cdMinutes">00</span> : <span id="cdSeconds">00</span>
                        </div>
                        <div class="countdown-card__footer" id="countdownNote">
                            Batas: {{ $order->batas_bayar->format('d F Y, H:i') }} WIB
                        </div>
                    </div>
                @endif

                {{-- Card Tujuan Transfer Manual --}}
                <div class="pesanan-card">
                    <h3 class="pesanan-card__subtitle">Tujuan Transfer Manual</h3>

                    {{-- Box Bank BCA --}}
                    <div class="pesanan-bank-banner">
                        <div class="pesanan-bank-banner__logo">BCA</div>
                        <div class="pesanan-bank-banner__meta">
                            <strong>Bank Central Asia (BCA)</strong>
                            <span>TRANSFER MANUAL ANTAR REKENING</span>
                        </div>
                    </div>

                    {{-- Row Nomor Rekening --}}
                    <div class="pesanan-copy-card">
                        <div class="pesanan-copy-card__info">
                            <span class="pesanan-copy-card__label">Nomor Rekening:</span>
                            <div class="pesanan-copy-card__val">8830-1294-8821</div>
                            <span class="pesanan-copy-card__owner">a.n. Gavin Fashion Store</span>
                        </div>
                        <button type="button" class="btn-copy js-copy" data-copy="883012948821">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Salin
                        </button>
                    </div>

                    {{-- Row Jumlah Ditransfer --}}
                    <div class="pesanan-copy-card">
                        <div class="pesanan-copy-card__info">
                            <span class="pesanan-copy-card__label">Jumlah yang harus dibayar:</span>
                            <div class="pesanan-copy-card__val pesanan-copy-card__val--amber">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </div>
                            <span class="pesanan-copy-card__note">*Transfer tepat hingga digit terakhir</span>
                        </div>
                        <button type="button" class="btn-copy js-copy" data-copy="{{ (int) $order->total }}">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Salin
                        </button>
                    </div>

                    {{-- Keterangan Pembayaran Ditolak (Jika Ada) --}}
                    @if ($order->status === 'pembayaran_ditolak' && $pembayaran)
                        <div class="pesanan-danger-box">
                            <strong>
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                Pembayaran Ditolak
                            </strong>
                            <p><strong>Alasan:</strong> {{ $pembayaran->alasan_tolak ?? 'Bukti transfer tidak sesuai atau tidak valid.' }}</p>
                        </div>
                    @endif

                    {{-- Notice Pembatalan Otomatis --}}
                    <div class="pesanan-notice-row">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <p>Pesanan otomatis dibatalkan jika belum dibayar dalam 1x24 jam sejak dibuat. Mohon simpan bukti transfer Anda untuk diunggah.</p>
                    </div>

                    {{-- Tombol Buka Modal Upload Bukti --}}
                    <div id="uploadBtnContainer">
                        <button type="button" class="btn-primary-dark" id="btnBukaUploadModal">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            {{ $order->status === 'pembayaran_ditolak' ? 'Upload Ulang Bukti Pembayaran' : 'Upload Bukti Pembayaran' }}
                        </button>
                    </div>

                    {{-- Link Bantuan WhatsApp --}}
                    <div class="pesanan-wa-help">
                        <span>Mengalami kendala transaksi?</span>
                        <a href="https://wa.me/6281212341234?text=Halo%20Admin%20Gavin%20Fashion,%20saya%20butuh%20bantuan%20terkait%20pesanan%20nomor%20{{ $order->invoice?->no_invoice }}" target="_blank" rel="noopener noreferrer">
                            WhatsApp Admin ↗
                        </a>
                    </div>
                </div>

                {{-- Bukti Pembayaran Sebelumnya (Khusus status pembayaran_ditolak) --}}
                @if ($order->status === 'pembayaran_ditolak')
                    @include('pesanan.partials.bukti-transfer')
                @endif

                {{-- Card Jaminan Keamanan Transaksi --}}
                <div class="pesanan-security-badge">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <div>
                        <strong>Jaminan Keamanan Transaksi</strong>
                        <p>Verifikasi rekening manual divalidasi langsung oleh admin Gavin Fashion.</p>
                    </div>
                </div>

                {{-- Modal Upload Bukti Transfer (Mockup 2.6.2) --}}
                <div id="modalUploadBukti" class="custom-modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modalUploadTitle">
                    <div class="custom-modal__overlay" id="modalOverlay"></div>
                    <div class="custom-modal__dialog">
                        <div class="custom-modal__header">
                            <div>
                                <h3 class="custom-modal__title" id="modalUploadTitle">Unggah Bukti Transfer</h3>
                                <p class="custom-modal__subtitle">Lakukan transfer sesuai total pembayaran, lalu unggah foto atau tangkapan layar bukti transfer.</p>
                            </div>
                            <span class="custom-modal__step-badge">LANGKAH 1/1</span>
                        </div>

                        <form action="{{ route('pesanan.upload-bukti', $order->id_pesanan) }}" method="POST" enctype="multipart/form-data" id="formUploadBukti" class="custom-modal__form">
                            @csrf
                            <div class="custom-modal__body">
                                {{-- Dropzone --}}
                                <div class="modal-dropzone" id="dropzoneUpload" role="button" tabindex="0">
                                    <div class="modal-dropzone__icon">
                                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#b45309" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path><polyline points="12 12 12 16 14 14"></polyline><polyline points="12 12 12 16 10 14"></polyline></svg>
                                    </div>
                                    <strong class="modal-dropzone__text">Tarik dan lepas file di sini atau klik untuk memilih</strong>
                                    <span class="modal-dropzone__sub">Format JPG, JPEG, atau PNG. Ukuran maksimal 10 MB</span>

                                    <button type="button" class="btn-choose-file" id="btnChooseFile">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                        Pilih File
                                    </button>

                                    <input type="file" name="bukti_transfer" id="inputBuktiTransfer" class="modal-hidden-file" accept="image/jpeg,image/png,image/jpg">

                                    <div id="previewContainer" class="modal-preview" style="display: none;">
                                        <img id="imagePreview" src="" alt="Pratinjau Bukti Transfer">
                                    </div>
                                </div>

                                {{-- Pesan Error Validasi di Dalam Modal (Requirement 1) --}}
                                <div id="uploadErrorMessage" class="modal-error-box" style="display: none;">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span id="uploadErrorText">Pilih file bukti pembayaran terlebih dahulu</span>
                                </div>

                                {{-- Help Note di Bawah Dropzone --}}
                                <div class="modal-help-box">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                    <div>
                                        <strong>Mengalami Kendala Saat Transfer?</strong>
                                        <p>Jika saldo rekening Anda sudah terpotong namun bukti pembayaran gagal terkirim, segera hubungi Layanan Pelanggan kami dengan melampirkan nomor invoice {{ $order->invoice?->no_invoice }}.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-modal__footer">
                                <button type="button" class="btn-modal-cancel" id="btnBatalModal">Batal</button>
                                <button type="submit" class="btn-primary-dark" id="btnSubmitUpload" style="width: auto; padding: 10px 22px;" disabled>Kirim Bukti Pembayaran</button>
                            </div>
                        </form>
                    </div>
                </div>

            {{-- ---------------------------------------------------- --}}
            {{-- 2. STATUS: MENUNGGU VERIFIKASI (Sedang Diverifikasi)  --}}
            {{-- ---------------------------------------------------- --}}
            @elseif ($order->status === 'menunggu_verifikasi')
                <div class="pesanan-card pesanan-card--gold-top">
                    <div class="pesanan-card__head-status">
                        <div>
                            <span class="pesanan-card__pretitle">STATUS PEMBAYARAN</span>
                            <h3 class="pesanan-card__title" style="margin-top: 2px;">Bukti Terkirim</h3>
                        </div>
                        <span class="pesanan-badge-amber">
                            <i class="dot"></i>Sedang Diverifikasi
                        </span>
                    </div>

                    {{-- Jumlah Ditransfer --}}
                    <div class="pesanan-verify-amount">
                        <div>
                            <span class="pesanan-verify-amount__label">Jumlah Ditransfer</span>
                            <div class="pesanan-verify-amount__val">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                        </div>
                        <span class="pesanan-badge-green">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Sesuai Tagihan
                        </span>
                    </div>

                    {{-- Notice Review Admin --}}
                    <div class="pesanan-notice-row pesanan-notice-row--amber">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <p>Admin <strong>Gavin Fashion Store</strong> sedang memverifikasi pembayaran Anda (estimasi 15-30 menit pada jam operasional).</p>
                    </div>

                    <a href="https://wa.me/6281212341234?text=Halo%20Admin%20Gavin%20Fashion,%20saya%20sudah%20upload%20bukti%20transfer%20untuk%20pesanan%20nomor%20{{ $order->invoice?->no_invoice }}" target="_blank" rel="noopener noreferrer" class="btn-primary-dark">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        Hubungi Admin WA
                    </a>

                    {{-- Footer ID & Enkripsi --}}
                    <div class="pesanan-card__footer-meta">
                        <div class="pesanan-card__footer-left">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>Transaksi Terenkripsi Aman</span>
                        </div>
                        <span class="pesanan-card__footer-right">ID Pesanan: #{{ substr($order->id_pesanan, 0, 8) }}</span>
                    </div>
                </div>

                {{-- Bukti Pembayaran (Dibawah Kartu Status) --}}
                @include('pesanan.partials.bukti-transfer')

                {{-- Card Jam Operasional Verifikasi --}}
                <div class="pesanan-info-card">
                    <div class="pesanan-info-card__title">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Jam Operasional Verifikasi</span>
                    </div>
                    <div class="pesanan-info-card__row">Senin - Sabtu: 08.00 - 21.00 WIB</div>
                    <div class="pesanan-info-card__row">Minggu & Libur Nasional: 09.00 - 17.00 WIB</div>
                    <p class="pesanan-info-card__note">Pembayaran di luar jam operasional diproses di awal jam kerja berikutnya.</p>
                </div>

            {{-- ---------------------------------------------------- --}}
            {{-- 3. STATUS: PESANAN DIPROSES                          --}}
            {{-- ---------------------------------------------------- --}}
            @elseif ($order->status === 'diproses')
                {{-- Card Pembayaran Terverifikasi --}}
                <div class="pesanan-card">
                    <div class="pesanan-verify-success-head">
                        <div class="pesanan-verify-success-icon">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <h3 class="pesanan-card__title" style="margin: 0;">Pembayaran Terverifikasi</h3>
                            <span class="pesanan-card__subtitle-text">Pesanan siap diteruskan ke gudang</span>
                        </div>
                    </div>

                    <div class="pesanan-details-table">
                        <div class="pesanan-details-table__row">
                            <span>Metode Pembayaran</span>
                            <strong>Transfer BCA</strong>
                        </div>
                        <div class="pesanan-details-table__row">
                            <span>Waktu Verifikasi</span>
                            <strong>{{ $pembayaran?->updated_at?->format('d M Y, H:i') ?? $order->updated_at->format('d M Y, H:i') }} WIB</strong>
                        </div>
                        <div class="pesanan-details-table__divider"></div>
                        <div class="pesanan-details-table__row pesanan-details-table__row--total">
                            <span>Total Dibayar</span>
                            <span class="pesanan-details-table__amount">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bukti Pembayaran (Dibawah Kartu Status) --}}
                @include('pesanan.partials.bukti-transfer')

                {{-- Card Estimasi Penyiapan Kurir (Dalam 1x24 jam kerja) --}}
                <div class="pesanan-info-card pesanan-info-card--delivery">
                    <div class="pesanan-info-card__icon-wrap">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                    </div>
                    <div>
                        <span class="pesanan-info-card__tag">Estimasi Penyiapan Kurir</span>
                        <div class="pesanan-info-card__highlight">Dalam 1x24 jam kerja</div>
                    </div>
                </div>

                {{-- Box Kebijakan Pengemasan --}}
                <div class="pesanan-packing-policy">
                    <span class="pesanan-packing-policy__title">KEBIJAKAN PENGEMASAN</span>
                    <ul class="pesanan-packing-policy__list">
                        <li>Termasuk zipper bag pelindung kain premium</li>
                        <li>Termasuk hangtag petunjuk perawatan serat kain</li>
                        <li>Pemeriksaan kontrol kualitas ganda sebelum dikirim</li>
                    </ul>
                </div>

                {{-- Action Buttons --}}
                <a href="{{ route('pesanan.invoice', $order->id_pesanan) }}" class="btn-outline-dark" style="margin-bottom: 8px;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Lihat Invoice
                </a>

                <a href="https://wa.me/6281212341234?text=Halo%20Admin%20Gavin%20Fashion,%20terkait%20pesanan%20nomor%20{{ $order->invoice?->no_invoice }}" target="_blank" rel="noopener noreferrer" class="btn-primary-dark">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    Hubungi Admin WA
                </a>

                <p class="pesanan-helper-subtext">
                    Perlu perubahan detail alamat atau pesanan sebelum kurir tiba? Hubungi kami langsung via WhatsApp.
                </p>

            {{-- ---------------------------------------------------- --}}
            {{-- 4. STATUS: PENGIRIMAN PRODUK (Dikirim)               --}}
            {{-- ---------------------------------------------------- --}}
            @elseif ($order->status === 'dikirim')
                {{-- Card Ringkasan Biaya --}}
                <div class="pesanan-card">
                    <div class="pesanan-card__head-status">
                        <div>
                            <h3 class="pesanan-card__title" style="margin: 0;">Ringkasan Biaya</h3>
                            <span class="pesanan-card__subtitle-text">Paket Dalam Pengiriman</span>
                        </div>
                        <span class="pesanan-badge-grey">TRANSFER LUNAS</span>
                    </div>

                    <div class="pesanan-cost-rows">
                        <div class="pesanan-cost-row">
                            <span>Subtotal Produk ({{ $order->detailPesanan->count() }})</span>
                            <span>Rp {{ number_format($order->total - $order->ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="pesanan-cost-row">
                            <span>Ongkos Kirim ({{ $order->jasa->nama_jasa }} Reguler)</span>
                            <span>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="pesanan-details-table__divider"></div>
                        <div class="pesanan-cost-row pesanan-cost-row--total">
                            <div>
                                <span class="pesanan-cost-row__total-label">TOTAL PEMBAYARAN</span>
                                <span class="pesanan-cost-row__bank-label">Bank Central Asia (BCA)</span>
                            </div>
                            <span class="pesanan-cost-row__total-val">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bukti Pembayaran (Dibawah Kartu Status) --}}
                @include('pesanan.partials.bukti-transfer')

                {{-- Card Estimasi Paket Tiba --}}
                <div class="pesanan-info-card pesanan-info-card--delivery">
                    <div class="pesanan-info-card__icon-wrap">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </div>
                    <div>
                        <span class="pesanan-info-card__tag">Estimasi Paket Tiba:</span>
                        <div class="pesanan-info-card__highlight">
                            {{ ($order->pengiriman?->tanggal_kirim ?? $order->updated_at)->addDays(2)->format('d') }} - {{ ($order->pengiriman?->tanggal_kirim ?? $order->updated_at)->addDays(3)->format('d F Y') }}
                        </div>
                    </div>
                </div>

                {{-- Card Konfirmasi Penerimaan --}}
                <div class="pesanan-card">
                    <h3 class="pesanan-card__title" style="margin-bottom: 12px;">Konfirmasi Penerimaan</h3>

                    <form action="{{ route('pesanan.konfirmasi-terima', $order->id_pesanan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang pesanan telah diterima dengan baik? Status pesanan akan diselesaikan.');">
                        @csrf
                        <button type="submit" class="btn-primary-dark">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Konfirmasi Pesanan Diterima
                        </button>
                    </form>

                    <p class="pesanan-helper-subtext" style="margin-bottom: 12px;">
                        Klik tombol di atas jika Anda telah menerima dan memeriksa kondisi paket dengan baik.
                    </p>

                    <a href="https://wa.me/6281212341234?text=Halo%20Admin%20Gavin%20Fashion,%20saya%20butuh%20bantuan%20terkait%20pengiriman%20pesanan%20nomor%20{{ $order->invoice?->no_invoice }}" target="_blank" rel="noopener noreferrer" class="btn-outline-dark">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        Bantuan WA
                    </a>
                </div>

                {{-- Garansi Gavin Fashion Store --}}
                <div class="pesanan-security-badge">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <div>
                        <p style="margin: 0; font-size: 11.5px; line-height: 1.45; color: #6b7280;">
                            Pesanan dilindungi <strong>Garansi Gavin Fashion Store</strong>. Retur diperbolehkan dalam 2x24 jam setelah status selesai dengan menyertakan video unboxing.
                        </p>
                    </div>
                </div>

            {{-- ---------------------------------------------------- --}}
            {{-- 5. STATUS: PESANAN SELESAI                           --}}
            {{-- ---------------------------------------------------- --}}
            @elseif ($order->status === 'selesai')
                {{-- Banner Transaksi Selesai --}}
                <div class="pesanan-success-banner">
                    <div class="pesanan-success-banner__icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <h3 class="pesanan-success-banner__title">Transaksi Selesai</h3>
                        <span class="pesanan-success-banner__subtitle">Pembayaran terverifikasi lunas</span>
                    </div>
                </div>

                {{-- Card Ringkasan Tagihan --}}
                <div class="pesanan-card">
                    <h3 class="pesanan-card__title">Ringkasan Tagihan</h3>
                    <span class="pesanan-card__subtitle-text" style="display: block; margin-bottom: 12px;">Metode: Transfer Manual BCA</span>

                    <div class="pesanan-cost-rows">
                        <div class="pesanan-cost-row">
                            <span>Subtotal ({{ $order->detailPesanan->count() }} Produk)</span>
                            <span>Rp {{ number_format($order->total - $order->ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="pesanan-cost-row">
                            <span>Ongkos Kirim ({{ $order->jasa->nama_jasa }} Reguler)</span>
                            <span>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="pesanan-details-table__divider"></div>
                        <div class="pesanan-cost-row pesanan-cost-row--total">
                            <div>
                                <span class="pesanan-cost-row__total-label">TOTAL PEMBAYARAN</span>
                                <span class="pesanan-cost-row__paid-label">LUNAS (1X24 Jam)</span>
                            </div>
                            <span class="pesanan-cost-row__total-val">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Tombol Beli Lagi Produk Serupa --}}
                    @php
                        $firstProduct = $order->detailPesanan->first()?->ukuran?->produk;
                    @endphp
                    <a href="{{ $firstProduct ? route('produk.show', $firstProduct->id_produk) : route('katalog') }}" class="btn-primary-dark" style="margin-top: 14px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        Beli Lagi Produk Serupa
                    </a>

                    {{-- Tombol Lihat Invoice (Requirement 6) --}}
                    <a href="{{ route('pesanan.invoice', $order->id_pesanan) }}" class="btn-outline-dark" style="margin-top: 8px;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Lihat Invoice
                    </a>

                    {{-- Link Bantuan WhatsApp --}}
                    <div class="pesanan-wa-help" style="margin-top: 14px;">
                        <span>Butuh bantuan dengan pesanan ini?</span>
                        <a href="https://wa.me/6281212341234?text=Halo%20Admin%20Gavin%20Fashion,%20terkait%20pesanan%20selesai%20nomor%20{{ $order->invoice?->no_invoice }}" target="_blank" rel="noopener noreferrer">
                            Hubungi WhatsApp Admin Official →
                        </a>
                    </div>
                </div>

                {{-- Bukti Pembayaran (Dibawah Kartu Status) --}}
                @include('pesanan.partials.bukti-transfer')

                {{-- Card Jaminan Kepuasan Gavin --}}
                <div class="pesanan-security-badge">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <div>
                        <strong>Jaminan Kepuasan Gavin</strong>
                        <p>Pesanan ini telah sukses diselesaikan. Terima kasih telah mempercayai koleksi busana Gavin Fashion Store.</p>
                    </div>
                </div>
            @endif
        </aside>
    </div>

    {{-- Lightbox Modal Bukti Transfer (Requirement 3) --}}
    @if ($pembayaran)
        <div id="lightboxBuktiTransfer" class="lightbox-modal" style="display: none;" role="dialog" aria-modal="true" aria-label="Bukti Transfer Ukuran Penuh">
            <div class="lightbox-modal__overlay" id="lightboxOverlay"></div>
            <div class="lightbox-modal__container">
                <button type="button" class="lightbox-modal__close" id="btnTutupLightbox" aria-label="Tutup pratinjau">&times;</button>
                <img src="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" alt="Bukti Transfer Ukuran Penuh" class="lightbox-modal__img">
                <div class="lightbox-modal__caption">
                    Bukti Transfer &middot; Diunggah {{ $pembayaran->tanggal_upload?->format('d F Y, H:i') }} WIB
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Script Countdown, Copy, Modal Upload, & Lightbox --}}
<script>
(function () {
    // 1. Tombol Salin
    document.querySelectorAll('.js-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var val = btn.getAttribute('data-copy');
            if (!val) return;

            var origHtml = btn.innerHTML;
            function success() {
                btn.textContent = 'Tersalin';
                btn.style.color = '#166534';
                setTimeout(function () {
                    btn.innerHTML = origHtml;
                    btn.style.color = '';
                }, 1500);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(val).then(success).catch(function () {
                    fallbackCopy(val, success);
                });
            } else {
                fallbackCopy(val, success);
            }
        });
    });

    function fallbackCopy(text, callback) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            callback();
        } catch (err) {
            console.error('Fallback copy error', err);
        }
        document.body.removeChild(textArea);
    }

    @if (in_array($order->status, ['menunggu_pembayaran', 'pembayaran_ditolak'], true))
    // 2. Countdown Timer Jam:Menit:Detik
    var wrap = document.getElementById('countdownWrap');
    if (wrap) {
        var serverNowStr = wrap.getAttribute('data-server-now');
        var expireAtStr = wrap.getAttribute('data-expire-at');

        if (serverNowStr && expireAtStr) {
            var serverNow = new Date(serverNowStr).getTime();
            var expireAt = new Date(expireAtStr).getTime();
            var clientStart = Date.now();
            var serverOffset = serverNow - clientStart;

            var hEl = document.getElementById('cdHours');
            var mEl = document.getElementById('cdMinutes');
            var sEl = document.getElementById('cdSeconds');
            var noteEl = document.getElementById('countdownNote');
            var uploadContainer = document.getElementById('uploadBtnContainer');

            function updateTimer() {
                var currentServerTime = Date.now() + serverOffset;
                var distance = expireAt - currentServerTime;

                if (distance <= 0) {
                    if (hEl) hEl.textContent = '00';
                    if (mEl) mEl.textContent = '00';
                    if (sEl) sEl.textContent = '00';
                    if (noteEl) {
                        noteEl.innerHTML = '<span style="color: #dc2626; font-weight: 700;">Waktu pembayaran habis</span>';
                    }
                    if (uploadContainer) {
                        uploadContainer.style.display = 'none';
                    }
                    return;
                }

                var hours = Math.floor(distance / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (hEl) hEl.textContent = String(hours).padStart(2, '0');
                if (mEl) mEl.textContent = String(minutes).padStart(2, '0');
                if (sEl) sEl.textContent = String(seconds).padStart(2, '0');
            }

            updateTimer();
            setInterval(updateTimer, 1000);
        }
    }

    // 3. Modal Upload Bukti & Validasi Client-side (Requirement 1 & 2)
    var modal = document.getElementById('modalUploadBukti');
    var btnBuka = document.getElementById('btnBukaUploadModal');
    var btnBatal = document.getElementById('btnBatalModal');
    var overlay = document.getElementById('modalOverlay');
    var input = document.getElementById('inputBuktiTransfer');
    var btnChoose = document.getElementById('btnChooseFile');
    var dropzone = document.getElementById('dropzoneUpload');
    var formUpload = document.getElementById('formUploadBukti');
    var btnSubmit = document.getElementById('btnSubmitUpload');
    var previewContainer = document.getElementById('previewContainer');
    var imagePreview = document.getElementById('imagePreview');
    var errorBox = document.getElementById('uploadErrorMessage');
    var errorText = document.getElementById('uploadErrorText');

    function showError(msg) {
        if (errorBox && errorText) {
            errorText.textContent = msg;
            errorBox.style.display = 'flex';
        }
        if (btnSubmit) {
            btnSubmit.disabled = true;
        }
    }

    function clearError() {
        if (errorBox) {
            errorBox.style.display = 'none';
        }
    }

    function resetModalState() {
        clearError();
        if (previewContainer) previewContainer.style.display = 'none';
        if (imagePreview) imagePreview.src = '';
        if (input) input.value = '';
        if (btnSubmit) btnSubmit.disabled = true;
    }

    function validateAndPreviewFile(file) {
        clearError();

        if (!file) {
            showError('Pilih file bukti pembayaran terlebih dahulu');
            if (previewContainer) previewContainer.style.display = 'none';
            if (imagePreview) imagePreview.src = '';
            return false;
        }

        // Cek ekstensi / MIME format gambar: JPG, JPEG, PNG
        var validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        var fileName = (file.name || '').toLowerCase();
        var hasValidExt = fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') || fileName.endsWith('.png');

        if (!validTypes.includes(file.type) && !hasValidExt) {
            showError('Format gambar harus JPG, JPEG, atau PNG.');
            if (input) input.value = '';
            if (previewContainer) previewContainer.style.display = 'none';
            if (imagePreview) imagePreview.src = '';
            return false;
        }

        // Cek ukuran maksimal 10 MB (10 * 1024 * 1024 bytes)
        var maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            showError('Ukuran file gambar maksimal 10 MB.');
            if (input) input.value = '';
            if (previewContainer) previewContainer.style.display = 'none';
            if (imagePreview) imagePreview.src = '';
            return false;
        }

        // Jika lolos validasi, aktifkan tombol kirim dan tampilkan pratinjau
        clearError();
        if (btnSubmit) {
            btnSubmit.disabled = false;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            if (imagePreview) imagePreview.src = e.target.result;
            if (previewContainer) previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
        return true;
    }

    if (modal && btnBuka) {
        function bukaModal() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            clearError();
        }

        function tutupModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            resetModalState();
        }

        btnBuka.addEventListener('click', bukaModal);
        if (btnBatal) btnBatal.addEventListener('click', tutupModal);
        if (overlay) overlay.addEventListener('click', tutupModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                tutupModal();
            }
        });

        if (btnChoose && input) {
            btnChoose.addEventListener('click', function (e) {
                e.stopPropagation();
                input.click();
            });
        }

        if (dropzone && input) {
            dropzone.addEventListener('click', function (e) {
                if (e.target !== btnChoose && !btnChoose.contains(e.target)) {
                    input.click();
                }
            });

            dropzone.addEventListener('dragover', function (e) {
                e.preventDefault();
                dropzone.style.borderColor = '#dfb169';
            });

            dropzone.addEventListener('dragleave', function (e) {
                e.preventDefault();
                dropzone.style.borderColor = '';
            });

            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                dropzone.style.borderColor = '';
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    validateAndPreviewFile(e.dataTransfer.files[0]);
                }
            });
        }

        if (input) {
            input.addEventListener('change', function () {
                var file = this.files && this.files[0];
                validateAndPreviewFile(file);
            });
        }

        if (formUpload) {
            formUpload.addEventListener('submit', function (e) {
                var file = input && input.files && input.files[0];
                if (!file) {
                    e.preventDefault();
                    showError('Pilih file bukti pembayaran terlebih dahulu');
                    return false;
                }
                if (!validateAndPreviewFile(file)) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }
    @endif

    // 4. Lightbox Modal Bukti Transfer (Requirement 3)
    var lightbox = document.getElementById('lightboxBuktiTransfer');
    var btnTutupLightbox = document.getElementById('btnTutupLightbox');
    var lightboxOverlay = document.getElementById('lightboxOverlay');

    function bukaLightbox() {
        if (!lightbox) return;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function tutupLightbox() {
        if (!lightbox) return;
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-open-lightbox').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            bukaLightbox();
        });
    });

    if (btnTutupLightbox) btnTutupLightbox.addEventListener('click', tutupLightbox);
    if (lightboxOverlay) lightboxOverlay.addEventListener('click', tutupLightbox);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (lightbox && lightbox.style.display === 'flex') {
                tutupLightbox();
            }
        }
    });
})();
</script>
@endsection
