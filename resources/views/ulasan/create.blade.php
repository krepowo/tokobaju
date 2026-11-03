@extends('layouts.site')

@section('title', 'Beri Ulasan Produk')
@section('active', 'pesanan')
@section('main_class', 'site-main--flush')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
@endpush

@section('content')
    <div class="ps-page">
        <div class="ps-wrap">
            <div class="ps-form-head">
                <div>
                    <h1 class="ps-title">Beri Ulasan Produk</h1>
                    <p class="ps-count">Bantu pembeli lain dengan pengalaman Anda.</p>
                </div>
                <div class="ps-order-ref">
                    Nomor Pesanan: <strong>{{ $pesanan->invoice?->no_invoice }}</strong>
                    <span class="ps-status ps-status--success">Selesai</span>
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

            <form method="POST" action="{{ route('ulasan.store', $detail) }}" class="ps-form-card">
                @csrf

                <div class="ps-produk">
                    <img src="{{ asset($produk?->gambar ?? 'images/produk/placeholder.png') }}" alt="" class="ps-produk__thumb">
                    <div class="ps-produk__body">
                        <div class="ps-produk__name">{{ $produk?->nama_produk ?? 'Produk' }}</div>
                        <div class="ps-produk__meta">
                            <span>Ukuran: {{ $detail->ukuranStok?->ukuran }}</span>
                            <span>Warna: {{ $detail->ukuranStok?->warna }}</span>
                            <span>Dibeli: {{ $pesanan->tanggal_pesanan?->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                    <span class="ps-produk__inv">{{ $pesanan->invoice?->no_invoice }}</span>
                </div>

                <div class="ps-field">
                    <div class="ps-label-row">
                        <span class="ps-label">Kualitas &amp; Kepuasan Produk <span class="req">*</span></span>
                        <span class="ps-hint">Ketik bintang untuk menilai</span>
                    </div>

                    <div class="ps-rating">
                        <div class="ps-stars" role="radiogroup" aria-label="Rating bintang">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="ps-star {{ (int) old('rating', 0) >= $i ? 'is-on' : '' }}"
                                        data-star="{{ $i }}" aria-label="{{ $i }} bintang">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="{{ (int) old('rating', 0) >= $i ? 'currentColor' : 'none' }}"
                                         stroke="currentColor" stroke-width="1.4" stroke-linejoin="round">
                                        <path d="M12 3.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.6 9.7l5.8-.8z"/>
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        <span class="ps-rating__status {{ (int) old('rating', 0) > 0 ? 'is-set' : '' }}" id="status-rating">
                            {{ (int) old('rating', 0) > 0 ? (int) old('rating').' / 5 bintang' : 'Status: Pilih bintang 1 - 5' }}
                        </span>
                    </div>
                    <input type="hidden" name="rating" id="input-rating" value="{{ old('rating') }}">
                    @error('rating') <span class="ps-err">{{ $message }}</span> @enderror
                </div>

                <div class="ps-field">
                    <div class="ps-label-row">
                        <span class="ps-label">Ulasan Tertulis (Opsional)</span>
                        <span class="ps-hint" id="hitung-karakter">0/500</span>
                    </div>
                    <textarea name="komentar" id="komentar" class="ps-textarea" maxlength="500"
                              placeholder="Ceritakan pengalaman Anda tentang kualitas bahan, potongan, kenyamanan, serta kesesuaian ukuran produk ini...">{{ old('komentar') }}</textarea>
                    <div class="ps-note">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 2.5 14 5.2v4.1c0 2.6-2.5 4.2-6 4.2s-6-1.6-6-4.2V5.2z"/><path d="M5.6 8.1l1.7 1.7 3.2-3.4"/>
                        </svg>
                        Ulasan Anda akan membantu pembeli lain memilih ukuran dan bahan terbaik.
                    </div>
                </div>

                <div class="ps-actions">
                    <a href="{{ route('pesanan.index') }}" class="btn btn--ghost">Batal</a>
                    <button type="submit" class="btn">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const bintang = document.querySelectorAll('.ps-star');
            const input = document.getElementById('input-rating');
            const status = document.getElementById('status-rating');

            function render(nilai) {
                bintang.forEach((b, i) => {
                    const on = i < nilai;
                    b.classList.toggle('is-on', on);
                    const svg = b.querySelector('svg');
                    if (svg) svg.setAttribute('fill', on ? 'currentColor' : 'none');
                });
                input.value = nilai > 0 ? nilai : '';
                status.textContent = nilai > 0 ? nilai + ' / 5 bintang' : 'Status: Pilih bintang 1 - 5';
                status.classList.toggle('is-set', nilai > 0);
            }

            bintang.forEach((b) => b.addEventListener('click', () => render(Number(b.dataset.star))));

            const area = document.getElementById('komentar');
            const hitung = document.getElementById('hitung-karakter');
            const perbarui = () => { hitung.textContent = area.value.length + '/500'; };
            area.addEventListener('input', perbarui);
            perbarui();
        })();
    </script>
@endpush
