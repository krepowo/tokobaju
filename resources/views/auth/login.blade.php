@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <header class="auth__head">
        <h2>Masuk ke Akun</h2>
        <p>Masuk untuk melanjutkan belanja Anda di Gavin Fashion Store</p>
    </header>

    @if (session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    @if (session('status'))
        <div class="alert alert--info">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" class="form" novalidate>
        @csrf

        <div class="field">
            <label for="identity">Email atau Nomor Telepon</label>
            <input type="text" id="identity" name="identity" value="{{ old('identity') }}"
                   placeholder="Masukkan email atau nomor telepon"
                   autocomplete="username"
                   class="@error('identity') is-invalid @enderror">
            @error('identity')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="password">Kata Sandi</label>
            <div class="field__password">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi"
                       autocomplete="current-password"
                       class="@error('password') is-invalid @enderror">
                <button type="button" class="field__toggle" data-toggle-password="password" aria-label="Tampilkan kata sandi">
                    <img src="{{ asset('images/figma/eye.svg') }}" alt="" aria-hidden="true">
                </button>
            </div>
            @error('password')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn--block">Masuk</button>
    </form>

    <p class="auth__switch">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
@endsection
