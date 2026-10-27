@extends('layouts.site')

@section('title', 'Beranda')
@section('active', 'beranda')

@section('content')
    <section class="panel">
        <div class="panel__head">
            @auth
                <h1>Halo, {{ auth()->user()->name }}</h1>
                <p class="muted">
                    Anda masuk sebagai
                    <strong>{{ auth()->user()->isPenjual() ? 'Penjual' : 'Pelanggan' }}</strong>
                    dengan akun {{ auth()->user()->identity }}.
                </p>
            @else
                <h1>Selamat datang di Gavin Fashion Store Indonesia</h1>
                <p class="muted">Masuk atau daftar untuk mulai berbelanja pakaian pilihan.</p>
            @endauth
        </div>

        <div class="row">
            @auth
                @if (auth()->user()->isPenjual())
                    <a href="{{ route('admin.home') }}" class="btn">Buka Halaman Penjual</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn--ghost">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn--ghost">Daftar</a>
            @endauth
        </div>

        <div class="alert alert--info">
            Tahap saat ini: sistem login dan registrasi. Halaman katalog, keranjang, pesanan, dan penjual
            akan dibangun pada tahap berikutnya.
        </div>
    </section>
@endsection
