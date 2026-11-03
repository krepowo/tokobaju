@extends('layouts.site')

@section('title', 'Profil Saya')
@section('active', 'profil')
@section('main-class', 'site-main--full')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
@endpush

@section('content')
    @php
        $inisial = strtoupper(\Illuminate\Support\Str::of($user->nama)->explode(' ')->map(fn ($k) => mb_substr($k, 0, 1))->take(2)->implode(''));
        $edit = $mode === 'edit';
    @endphp

    <div class="ps-page">
        <div class="ps-wrap">
            <div class="ps-head">
                <div>
                    <h1 class="ps-title">Profil Saya</h1>
                    <p class="ps-count">Kelola data diri dan alamat Anda.</p>
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

            <div class="ps-profil">
                <aside class="ps-side">
                    <div class="ps-avatar">{{ $inisial }}</div>
                    <div class="ps-side__name">{{ $user->nama }}</div>
                    <div class="ps-side__email">{{ $user->email }}</div>
                    <div class="ps-side__since">Bergabung sejak {{ $user->created_at?->translatedFormat('d F Y') }}</div>

                    <nav class="ps-side__menu" aria-label="Menu profil">
                        <a href="{{ route('profil.index') }}" class="ps-side__link {{ ! $edit ? 'is-active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="5.4" r="2.6"/><path d="M2.8 13.4c0-2.4 2.3-4 5.2-4s5.2 1.6 5.2 4"/>
                            </svg>
                            Data Profil
                            <svg class="chev" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 3.5 10.5 8 6 12.5"/>
                            </svg>
                        </a>

                        <a href="{{ route('pesanan.index') }}" class="ps-side__link">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 2.5h8v12l-2-1.5-2 1.5-2-1.5-2 1.5z"/><path d="M6.5 6h3M6.5 9h3"/>
                            </svg>
                            Pesanan Saya
                            <span class="chev" style="font-size:12px">{{ $jumlahPesanan }}</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="ps-side__link ps-side__link--danger" style="width:100%; background:none; border:0; cursor:pointer; font-family:inherit">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6.5 3.5H4a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h2.5"/><path d="M10 5.5 12.5 8 10 10.5"/><path d="M12.5 8H6.5"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </nav>
                </aside>

                <div>
                    <section class="ps-panel">
                        <div class="ps-panel__head">
                            <h2 class="ps-panel__title">Data Diri</h2>
                            @if ($edit)
                                <a href="{{ route('profil.index') }}" class="btn btn--ghost btn--sm">Batal</a>
                            @else
                                <a href="{{ route('profil.index', ['edit' => 1]) }}" class="btn btn--ghost btn--sm">Ubah Profil</a>
                            @endif
                        </div>

                        <div class="ps-panel__body">
                            @if ($edit)
                                <form method="POST" action="{{ route('profil.update') }}" style="display:contents">
                                    @csrf
                                    @method('PUT')

                                    <div class="ps-grid-3">
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="nama">Nama Lengkap <span style="color:#dc2626">*</span></label>
                                            <input type="text" id="nama" name="nama" class="ps-input @error('nama') ps-input--err @enderror"
                                                   value="{{ old('nama', $user->nama) }}" maxlength="100" required>
                                            @error('nama') <span class="ps-err">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="email">Email <span style="color:#dc2626">*</span></label>
                                            <input type="email" id="email" name="email" class="ps-input @error('email') ps-input--err @enderror"
                                                   value="{{ old('email', $user->email) }}" maxlength="100" required>
                                            @error('email') <span class="ps-err">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="no_telepon">Nomor Telepon</label>
                                            <input type="text" id="no_telepon" name="no_telepon" class="ps-input @error('no_telepon') ps-input--err @enderror"
                                                   value="{{ old('no_telepon', $user->no_telepon) }}" maxlength="20" inputmode="numeric">
                                            @error('no_telepon') <span class="ps-err">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="password">Kata Sandi Baru (opsional)</label>
                                            <input type="password" id="password" name="password" class="ps-input @error('password') ps-input--err @enderror"
                                                   autocomplete="new-password" placeholder="Kosongkan bila tidak diubah">
                                            @error('password') <span class="ps-err">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="ps-panel__head" style="padding:20px 0 14px; border-bottom:0">
                                        <h3 class="ps-panel__title" style="font-size:15px">Alamat Pengiriman</h3>
                                        <span class="ps-badge-utama">Alamat Utama</span>
                                    </div>

                                    <div class="ps-grid-2">
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="provinsi">Provinsi</label>
                                            <input type="text" id="provinsi" name="provinsi" class="ps-input" value="{{ old('provinsi', $user->provinsi) }}" maxlength="100" placeholder="Contoh: Jawa Tengah">
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="kota_kabupaten">Kota / Kabupaten</label>
                                            <input type="text" id="kota_kabupaten" name="kota_kabupaten" class="ps-input" value="{{ old('kota_kabupaten', $user->kota_kabupaten) }}" maxlength="100" placeholder="Contoh: Kota Surakarta">
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="kecamatan">Kecamatan</label>
                                            <input type="text" id="kecamatan" name="kecamatan" class="ps-input" value="{{ old('kecamatan', $user->kecamatan) }}" maxlength="100">
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="kelurahan_desa">Kelurahan / Desa</label>
                                            <input type="text" id="kelurahan_desa" name="kelurahan_desa" class="ps-input" value="{{ old('kelurahan_desa', $user->kelurahan_desa) }}" maxlength="100">
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="kode_pos">Kode Pos</label>
                                            <input type="text" id="kode_pos" name="kode_pos" class="ps-input" value="{{ old('kode_pos', $user->kode_pos) }}" maxlength="10" inputmode="numeric">
                                        </div>
                                        <div class="ps-field" style="margin-top:0">
                                            <label for="detail_alamat">Detail Alamat</label>
                                            <input type="text" id="detail_alamat" name="detail_alamat" class="ps-input" value="{{ old('detail_alamat', $user->detail_alamat) }}" maxlength="500" placeholder="Jalan, RT/RW, patokan">
                                        </div>
                                    </div>

                                    <div class="ps-actions" style="border-top:0; padding-top:8px">
                                        <a href="{{ route('profil.index') }}" class="btn btn--ghost">Batal</a>
                                        <button type="submit" class="btn">Simpan Perubahan</button>
                                    </div>
                                </form>
                            @else
                                <div class="ps-grid-3">
                                    <div class="ps-dt">
                                        <label>Nama Lengkap</label>
                                        <p>{{ $user->nama }}</p>
                                    </div>
                                    <div class="ps-dt">
                                        <label>Email</label>
                                        <p>{{ $user->email ?: '—' }}</p>
                                    </div>
                                    <div class="ps-dt">
                                        <label>Nomor Telepon</label>
                                        <p>{{ $user->no_telepon ?: '—' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="ps-panel">
                        <div class="ps-panel__head">
                            <h2 class="ps-panel__title">Alamat Pengiriman</h2>
                            <span class="ps-badge-utama">Alamat Utama</span>
                        </div>
                        <div class="ps-panel__body">
                            <div class="ps-grid-2">
                                <div class="ps-dt"><label>Provinsi</label><p>{{ $user->provinsi ?: '—' }}</p></div>
                                <div class="ps-dt"><label>Kota / Kabupaten</label><p>{{ $user->kota_kabupaten ?: '—' }}</p></div>
                                <div class="ps-dt"><label>Kecamatan</label><p>{{ $user->kecamatan ?: '—' }}</p></div>
                                <div class="ps-dt"><label>Kelurahan / Desa</label><p>{{ $user->kelurahan_desa ?: '—' }}</p></div>
                                <div class="ps-dt"><label>Kode Pos</label><p>{{ $user->kode_pos ?: '—' }}</p></div>
                                <div class="ps-dt"><label>Detail Alamat</label><p>{{ $user->detail_alamat ?: '—' }}</p></div>
                            </div>

                            <div class="ps-note-italic">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="8" cy="8" r="6"/><path d="M8 7.2v3.6"/><path d="M8 5.2h.01"/>
                                </svg>
                                Alamat ini akan terisi otomatis saat checkout.
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
