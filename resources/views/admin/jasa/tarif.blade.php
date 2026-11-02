@extends('layouts.admin')

@section('title', 'Tarif ' . $jasa->nama_jasa)
@section('active', 'produk')
@section('crumb', 'Jasa Pengiriman')
@section('crumb_current', 'Atur Tarif')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Atur Tarif — {{ $jasa->nama_jasa }}</h1>
            <p class="ad-sub">Tarif berlaku tetap per pulau. Kosongkan kolom bila jasa tidak melayani pulau tersebut.</p>
        </div>
        <a href="{{ route('admin.jasa.index') }}" class="ad-btn ad-btn--ghost">Kembali</a>
    </div>

    <section class="ad-card">
        <form method="POST" action="{{ route('admin.jasa.tarif.simpan', $jasa) }}">
            @csrf
            @method('PUT')

            <div class="ad-card__body">
                <div class="ad-form ad-form--2col">
                    @foreach ($pulau as $p)
                        @php
                            $nilai = $tarif[$p] ?? null;
                        @endphp
                        <div class="ad-field">
                            <label class="ad-label" for="tarif-{{ $loop->index }}">{{ $p }}</label>
                            <input type="number" id="tarif-{{ $loop->index }}" name="tarif[{{ $p }}]" class="ad-input"
                                   value="{{ $nilai ? (int) $nilai->tarif : '' }}" min="0" step="500" placeholder="Contoh: 15000">
                            <span class="ad-hint">
                                {{ $nilai ? 'Tarif saat ini Rp '.number_format((float) $nilai->tarif, 0, ',', '.') : 'Belum diatur' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="ad-card__foot">
                <span>Tarif dihitung satu kali per pesanan, tanpa mempertimbangkan berat barang.</span>
                <div class="ad-btn-row">
                    <a href="{{ route('admin.jasa.index') }}" class="ad-btn ad-btn--ghost">Batal</a>
                    <button type="submit" class="ad-btn">Simpan Tarif</button>
                </div>
            </div>
        </form>
    </section>
@endsection