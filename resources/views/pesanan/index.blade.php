@extends('layouts.site')

@section('title', 'Pesanan Saya')
@section('active', 'pesanan')
@section('main_class', 'site-main--flush')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
@endpush

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $daftarTab = ['semua' => 'Semua'] + collect(\App\Support\StatusPesanan::semua())->map(fn ($s) => $s['label'])->all();
    @endphp

    <div class="ps-page">
        <div class="ps-wrap">
            <div class="ps-head">
                <div>
                    <h1 class="ps-title">Pesanan Saya</h1>
                    <p class="ps-count">{{ $total }} pesanan</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="ps-alert ps-alert--error">
                    <ul>
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <nav class="ps-tabs" aria-label="Filter status pesanan">
                @foreach ($daftarTab as $kunci => $label)
                    <a href="{{ route('pesanan.index', $kunci === 'semua' ? [] : ['tab' => $kunci]) }}"
                       class="ps-tab {{ $tab === $kunci ? 'is-active' : '' }}">
                        {{ $label }}
                        <span class="ps-tab__count">{{ $jumlah[$kunci] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            @forelse ($pesanan as $p)
                <article class="ps-card">
                    <div class="ps-card__head">
                        <div class="ps-invoice">
                            {{ $p->invoice?->no_invoice ?? 'INV-'.$p->id_pesanan }}
                            <span>&middot; {{ $p->tanggal_pesanan?->translatedFormat('d F Y') }}</span>
                        </div>
                        <span class="ps-status ps-status--{{ \App\Support\StatusPesanan::badge($p->status) }}">
                            {{ \App\Support\StatusPesanan::label($p->status) }}
                        </span>
                    </div>

                    @foreach ($p->detail as $d)
                        @php $produk = $d->ukuranStok?->produk; @endphp
                        <div class="ps-item">
                            <img src="{{ asset($produk?->gambar ?? 'images/produk/placeholder.png') }}" alt="" class="ps-item__thumb">

                            <div class="ps-item__body">
                                <div class="ps-item__name">{{ $produk?->nama_produk ?? 'Produk tidak tersedia' }}</div>
                                <div class="ps-item__meta">
                                    Warna: {{ $d->ukuranStok?->warna ?? '—' }}
                                    &bull; Ukuran: {{ $d->ukuranStok?->ukuran ?? '—' }}
                                </div>
                            </div>

                            <div class="ps-item__right">
                                <span class="ps-item__qty">{{ $d->jumlah }} &times; {{ $rupiah($d->harga_satuan) }}</span>
                                <span class="ps-item__price">{{ $rupiah($d->subtotal) }}</span>

                                @if ($p->status === 'selesai')
                                    @if ($d->ulasan)
                                        <span class="ps-status ps-status--success">Sudah diulas</span>
                                    @else
                                        <a href="{{ route('ulasan.create', $d) }}" class="btn btn--ghost btn--sm">Beri Ulasan</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="ps-shipping">
                        <div class="ps-shipping__left">
                            <span class="ps-shipping__check">
                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="8" cy="8" r="6"/><path d="M5.4 8.2l1.8 1.8 3.4-3.6"/>
                                </svg>
                            </span>
                            <span>
                                Jasa Pengiriman: {{ $p->jasa?->nama_jasa ?? '—' }}
                                &bull; Ongkos Kirim {{ $rupiah($p->ongkir) }}
                                @if ($p->status === 'selesai')
                                    &bull; <span class="ps-shipping__ok">Paket telah diterima pembeli</span>
                                @elseif ($p->status === 'dikirim')
                                    &bull; <span class="ps-shipping__ok">Paket sedang dalam perjalanan</span>
                                @elseif ($p->status === 'dibatalkan')
                                    &bull; <span style="color:#b42318">Pesanan dibatalkan</span>
                                @elseif ($p->status === 'menunggu_pembayaran' && $p->batas_bayar)
                                    &bull; <span style="color:#b45309">Selesaikan pembayaran sebelum {{ $p->batas_bayar->translatedFormat('d M Y H:i') }}</span>
                                @endif
                            </span>
                        </div>

                        <div class="ps-shipping__right">
                            @if ($p->pengiriman)
                                <span>Resi: {{ $p->pengiriman->no_resi }}</span>
                                <button type="button" class="ps-copy" data-copy="{{ $p->pengiriman->no_resi }}">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="5.5" y="5.5" width="8" height="8" rx="1.5"/><path d="M10.5 5.5V3.5a1 1 0 0 0-1-1h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2"/>
                                    </svg>
                                    <span>Salin</span>
                                </button>
                            @else
                                <span style="color:#9ca3af">Resi belum tersedia</span>
                            @endif
                        </div>
                    </div>

                    <div class="ps-card__foot">
                        <div>
                            <span class="ps-total">Total Pembayaran: <strong>{{ $rupiah($p->total) }}</strong></span>
                            @if ($p->status === 'selesai')
                                <span class="ps-received">Pesanan selesai {{ $p->updated_at?->translatedFormat('d F Y') }}</span>
                            @endif
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap">
                            @if ($p->status === 'dikirim')
                                <form method="POST" action="{{ route('pesanan.terima', $p) }}">
                                    @csrf
                                    <button type="submit" class="btn btn--sm">Konfirmasi Penerimaan</button>
                                </form>
                            @endif
                            <a href="{{ route('pesanan.invoice', $p) }}" class="btn btn--ghost btn--sm">Lihat Invoice</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="ps-empty">
                    <strong>Belum ada pesanan pada filter ini</strong>
                    <p>Yuk mulai belanja dan temukan pakaian favoritmu di katalog.</p>
                    <a href="{{ route('katalog') }}" class="btn btn--sm">Buka Katalog</a>
                </div>
            @endforelse

            @if ($pesanan->hasPages())
                <div style="display:flex; justify-content:center; margin-top:26px">
                    {{ $pesanan->links('partials.pager') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (e) {
            const tombol = e.target.closest('[data-copy]');
            if (!tombol) return;
            const label = tombol.querySelector('span');
            const selesai = () => {
                tombol.classList.add('is-copied');
                if (label) label.textContent = 'Tersalin';
                setTimeout(() => {
                    tombol.classList.remove('is-copied');
                    if (label) label.textContent = 'Salin';
                }, 1800);
            };
            if (navigator.clipboard) {
                navigator.clipboard.writeText(tombol.dataset.copy).then(selesai).catch(selesai);
            } else {
                selesai();
            }
        });
    </script>
@endpush
