@extends('layouts.site')

@section('title', 'Checkout')
@section('active', 'keranjang')
@section('main_class', 'site-main--catalog')

@section('content')
<div class="checkout">
    <h1 class="checkout__title">Checkout</h1>

    <form method="POST" action="{{ route('checkout.store') }}" class="checkout__layout" id="checkoutForm">
        @csrf

        <div class="checkout__main">
            <section class="checkout__section">
                <h2>Alamat Pengiriman</h2>
                <div class="checkout__grid">
                    <label class="field">
                        <span>Provinsi</span>
                        <select name="provinsi" id="provinsi" required>
                            @foreach ($provinsi as $p)
                                <option value="{{ $p->nama_provinsi }}" data-pulau="{{ $p->pulau }}"
                                    @selected($p->nama_provinsi === auth()->user()->provinsi)>{{ $p->nama_provinsi }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">
                        <span>Kota / Kabupaten</span>
                        <input type="text" name="kota_kabupaten" value="{{ old('kota_kabupaten', auth()->user()->kota_kabupaten) }}" required>
                    </label>
                    <label class="field">
                        <span>Kecamatan</span>
                        <input type="text" name="kecamatan" value="{{ old('kecamatan', auth()->user()->kecamatan) }}" required>
                    </label>
                    <label class="field">
                        <span>Kelurahan / Desa</span>
                        <input type="text" name="kelurahan_desa" value="{{ old('kelurahan_desa', auth()->user()->kelurahan_desa) }}" required>
                    </label>
                    <label class="field">
                        <span>Kode Pos</span>
                        <input type="text" name="kode_pos" value="{{ old('kode_pos', auth()->user()->kode_pos) }}" required>
                    </label>
                </div>
                <label class="field">
                    <span>Detail Alamat</span>
                    <textarea name="detail_alamat" rows="2" required>{{ old('detail_alamat', auth()->user()->detail_alamat) }}</textarea>
                </label>
            </section>

            <section class="checkout__section">
                <h2>Jasa Pengiriman</h2>
                <div class="checkout__jasa">
                    @foreach ($jasa as $j)
                        <label class="checkout__jasa-item">
                            <input type="radio" name="id_jasa" value="{{ $j->id_jasa }}"
                                   data-tarif="{{ json_encode($tarif->get($j->id_jasa, [])) }}"
                                   @checked($loop->first)>
                            <span>{{ $j->nama_jasa }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="checkout__summary">
            <h2>Ringkasan</h2>

            @foreach ($items as $it)
                <div class="checkout__item">
                    <img src="{{ asset($it->ukuran->produk->gambar) }}" alt="{{ $it->ukuran->produk->nama_produk }}">
                    <div class="checkout__item-info">
                        <span class="checkout__item-name">{{ $it->ukuran->produk->nama_produk }}</span>
                        <span class="checkout__item-var">{{ $it->ukuran->warna }} · {{ $it->ukuran->ukuran }} × {{ $it->jumlah }}</span>
                    </div>
                    <span class="checkout__item-sub">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach

            <div class="checkout__row"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
            <div class="checkout__row"><span>Ongkir</span><span id="sumOngkir">Rp 0</span></div>
            <div class="checkout__row checkout__row--total"><span>Total</span><span id="sumTotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>

            <button type="submit" class="checkout__submit">Buat Pesanan</button>
        </aside>
    </form>
</div>

<script>
(function () {
    var SUBTOTAL = {{ $subtotal }};
    var provSel = document.getElementById('provinsi');
    var ongkirEl = document.getElementById('sumOngkir');
    var totalEl = document.getElementById('sumTotal');

    function fmt(n) { return 'Rp ' + n.toLocaleString('id-ID'); }

    function pulauTerpilih() {
        var opt = provSel.options[provSel.selectedIndex];
        return opt ? (opt.getAttribute('data-pulau') || 'Jawa') : 'Jawa';
    }

    function tarifTerpilih() {
        var sel = document.querySelector('input[name="id_jasa"]:checked');
        if (!sel) { return 0; }
        try {
            var t = JSON.parse(sel.getAttribute('data-tarif') || '{}');
            return parseInt(t[pulauTerpilih()] || 0, 10);
        } catch (e) { return 0; }
    }

    function render() {
        var ongkir = tarifTerpilih();
        ongkirEl.textContent = fmt(ongkir);
        totalEl.textContent = fmt(SUBTOTAL + ongkir);
    }

    provSel.addEventListener('change', render);
    Array.prototype.forEach.call(document.querySelectorAll('input[name="id_jasa"]'), function (i) {
        i.addEventListener('change', render);
    });
    render();
})();
</script>
@endsection
