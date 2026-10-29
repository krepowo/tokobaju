@extends('layouts.auth')

@section('title', 'Registrasi')

@section('card_modifier', 'auth__card--tall')

@section('content')
    <header class="auth__head">
        <h2>Buat Akun Baru</h2>
        <p>Daftar untuk mulai berbelanja di Gavin Fashion Store</p>
    </header>

    <form method="POST" action="{{ route('register.store') }}" class="form" novalidate>
        @csrf

        <div class="field">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   placeholder="Masukkan nama lengkap" autocomplete="name"
                   class="@error('name') is-invalid @enderror">
            @error('name')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="Masukkan email" autocomplete="email"
                   class="@error('email') is-invalid @enderror">
            @error('email')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="phone">Nomor Telepon</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                   placeholder="Masukkan No Telepon" autocomplete="tel" inputmode="tel"
                   class="@error('phone') is-invalid @enderror">
            @error('phone')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="password">Kata Sandi</label>
            <div class="field__password">
                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                       autocomplete="new-password"
                       class="@error('password') is-invalid @enderror">
                <button type="button" class="field__toggle" data-toggle-password="password" aria-label="Tampilkan kata sandi">
                    <img src="{{ asset('images/figma/eye.svg') }}" alt="" aria-hidden="true">
                </button>
            </div>
            <span class="field__hint">Gunakan minimal 8 karakter.</span>
            @error('password')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn--block">Daftar Sekarang</button>
    </form>

    <p class="auth__switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection
