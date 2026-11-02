@extends('layouts.site')

@section('title', 'Checkout')
@section('active', 'keranjang')
@section('main-class', 'site-main--full')

@php
    $u = auth()->user();
    $jenisProduk = $items->unique(fn ($it) => $it->ukuran->produk->id_produk)->count();
    $defaultProvinsi = $u->provinsi ?: optional($provinsi->first())->nama_provinsi;
    $defaultPulau = $provinsi->firstWhere('nama_provinsi', $defaultProvinsi)?->pulau ?? 'Jawa';
@endphp

@section('content')
<form method="POST" action="{{ route('checkout.store') }}" class="checkout-page" id="checkoutForm">
    @csrf
    <div class="checkout-container">
        <div class="checkout-head">
            <div>
                <h1 class="checkout-title">Checkout</h1>
                <ol class="checkout-steps">
                    <li class="checkout-step"><a href="{{ route('keranjang.index') }}">1. Keranjang</a></li>
                    <li class="checkout-step is-current"><span>2. Checkout</span></li>
                    <li class="checkout-step is-muted"><span>3. Invoice</span></li>
                </ol>
            </div>
        </div>

        <div class="checkout-grid">
            <div class="checkout-main">
                {{-- Card 1: Alamat Pengiriman --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">Alamat Pengiriman</h2>

                    <div class="checkout-row">
                        <label class="field">
                            <span>Nama Penerima</span>
                            <input type="text" name="nama_penerima" value="{{ old('nama_penerima', $u->nama) }}" required>
                        </label>
                        <label class="field">
                            <span>Nomor Telepon</span>
                            <input type="text" name="no_telepon" value="{{ old('no_telepon', $u->no_telepon) }}" required>
                        </label>
                    </div>

                    <div class="checkout-row">
                        <label class="field">
                            <span>Provinsi</span>
                            <select name="provinsi" id="provinsi" required>
                                @foreach ($provinsi as $p)
                                    <option value="{{ $p->nama_provinsi }}" data-pulau="{{ $p->pulau }}"
                                        @selected($p->nama_provinsi === $defaultProvinsi)>{{ $p->nama_provinsi }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>Kota / Kabupaten</span>
                            <input type="text" name="kota_kabupaten" value="{{ old('kota_kabupaten', $u->kota_kabupaten) }}" required>
                        </label>
                    </div>

                    <div class="checkout-row">
                        <label class="field">
                            <span>Kecamatan</span>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan', $u->kecamatan) }}" required>
                        </label>
                        <label class="field">
                            <span>Kelurahan / Desa</span>
                            <input type="text" name="kelurahan_desa" value="{{ old('kelurahan_desa', $u->kelurahan_desa) }}" required>
                        </label>
                    </div>

                    <div class="checkout-row checkout-row--slim">
                        <label class="field field--kode">
                            <span>Kode Pos</span>
                            <input type="text" name="kode_pos" value="{{ old('kode_pos', $u->kode_pos) }}" required>
                        </label>
                    </div>

                    <label class="field">
                        <span>Detail Alamat</span>
                        <textarea name="detail_alamat" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, patokan." required>{{ old('detail_alamat', $u->detail_alamat) }}</textarea>
                    </label>
                </section>

                {{-- Card 2: Jasa Pengiriman --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">Jasa Pengiriman</h2>
                    <span class="checkout-card__sub">Reguler &amp; Instant</span>

                    <div class="checkout-couriers">
                        @foreach ($jasa as $j)
                            <label class="cour {{ $loop->first ? 'is-checked' : '' }}">
                                <input type="radio" name="id_jasa" value="{{ $j->id_jasa }}"
                                       data-tarif="{{ json_encode($tarif->get($j->id_jasa, [])) }}"
                                       @checked($loop->first)>
                                <span class="cour__radio"></span>
                                <b class="cour__name">{{ $j->nama_jasa }}</b>
                                <span class="cour__price">Rp {{ number_format($tarif->get($j->id_jasa, [])[$defaultPulau] ?? 0, 0, ',', '.') }}</span>
                            </label>
                        @endforeach
                    </div>

                    <p class="checkout-note">Ongkos kirim menggunakan tarif tetap sesuai jasa pengiriman yang dipilih.</p>
                </section>
            </div>

            <aside class="checkout-summary">
                <div class="checkout-summary__head">
                    <h2>Ringkasan Pesanan</h2>
                    <span class="checkout-summary__count">{{ $jenisProduk }} JENIS PRODUK</span>
                </div>

                <div class="checkout-summary__items">
                    @foreach ($items as $it)
                        <div class="sum-item">
                            <img src="{{ $it->ukuran->produk->gambar_url }}" alt="{{ $it->ukuran->produk->nama_produk }}">
                            <div class="sum-item__meta">
                                <b>{{ $it->ukuran->produk->nama_produk }}</b>
                                <span>Warna: {{ $it->ukuran->warna }} | Ukuran: {{ $it->ukuran->ukuran }}</span>
                            </div>
                            <div class="sum-item__right">
                                <span>{{ $it->jumlah }} &times; Rp {{ number_format($it->ukuran->produk->harga, 0, ',', '.') }}</span>
                                <b>Rp {{ number_format($it->subtotal, 0, ',', '.') }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="checkout-calc">
                    <div class="checkout-calc__row"><span>Total Produk</span><b id="sumQty">{{ $items->sum('jumlah') }}</b></div>
                    <div class="checkout-calc__row"><span>Subtotal Produk</span><b>Rp {{ number_format($subtotal, 0, ',', '.') }}</b></div>
                    <div class="checkout-calc__row"><span>Ongkos Kirim (<span id="ongkirJasa">{{ $jasa->first()->nama_jasa }}</span>)</span><b id="sumOngkir">Rp {{ number_format($tarif->get($jasa->first()->id_jasa, [])[$defaultPulau] ?? 0, 0, ',', '.') }}</b></div>
                    <div class="checkout-calc__row checkout-calc__row--total"><span>Total Pembayaran</span><b id="sumTotal">Rp {{ number_format($subtotal + ($tarif->get($jasa->first()->id_jasa, [])[$defaultPulau] ?? 0), 0, ',', '.') }}</b></div>
                </div>

                <div class="checkout-notice">Pembayaran dilakukan melalui transfer bank maksimal 1x24 jam setelah pesanan dibuat. Rekening tujuan ada di invoice.</div>

                <div class="checkout-actions">
                    <button type="submit" class="checkout-submit">Buat Pesanan</button>
                    <a href="{{ route('keranjang.index') }}" class="checkout-back">Kembali ke Keranjang</a>
                </div>
            </aside>
        </div>
    </div>
</form>

<script>
(function () {
    var SUBTOTAL = {{ $subtotal }};
    var provSel = document.getElementById('provinsi');
    var couriers = Array.prototype.slice.call(document.querySelectorAll('input[name="id_jasa"]'));
    var oJasa = document.getElementById('ongkirJasa');
    var oOngkir = document.getElementById('sumOngkir');
    var oTotal = document.getElementById('sumTotal');

    function fmt(n) { return 'Rp ' + n.toLocaleString('id-ID'); }

    function pulau() {
        var opt = provSel.options[provSel.selectedIndex];
        return opt ? (opt.getAttribute('data-pulau') || 'Jawa') : 'Jawa';
    }

    function render() {
        var p = pulau();
        var selected = null;
        couriers.forEach(function (r) {
            var tarif = JSON.parse(r.getAttribute('data-tarif') || '{}');
            var price = parseInt(tarif[p] || 0, 10);
            var lab = r.closest('.cour');
            if (lab) {
                var priceEl = lab.querySelector('.cour__price');
                if (priceEl) { priceEl.textContent = fmt(price); }
            }
            if (r.checked) { selected = { id: r.value, tarif: tarif, price: price, name: (r.closest('.cour').querySelector('.cour__name') || {}).textContent }; }
        });

        var ongkir = selected ? selected.price : 0;
        oJasa.textContent = selected ? selected.name : '';
        oOngkir.textContent = fmt(ongkir);
        oTotal.textContent = fmt(SUBTOTAL + ongkir);
    }

    provSel.addEventListener('change', render);
    couriers.forEach(function (r) {
        r.addEventListener('change', function () {
            Array.prototype.forEach.call(document.querySelectorAll('.cour'), function (c) {
                c.classList.toggle('is-checked', c.querySelector('input').checked);
            });
            render();
        });
    });

    render();
})();
</script>
@endsection
