@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Tambah Produk' : 'Ubah Produk')
@section('active', 'produk')
@section('crumb', 'Kelola Produk & Kategori')
@section('crumb_current', $mode === 'create' ? 'Tambah Produk' : 'Ubah Produk')

@section('content')
    <div class="ad-head">
        <div>
            <h1 class="ad-h1">{{ $mode === 'create' ? 'Tambah Produk' : 'Ubah Produk' }}</h1>
            <p class="ad-sub">Nama, kategori, harga, dan gambar tampil pada halaman katalog pelanggan.</p>
        </div>
        <a href="{{ route('admin.produk.index') }}" class="ad-btn ad-btn--ghost">Kembali</a>
    </div>

    <section class="ad-card">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $mode === 'create' ? route('admin.produk.store') : route('admin.produk.update', $produk) }}"
              class="ad-card__body">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="ad-form ad-form--2col">
                <div class="ad-field ad-form__full">
                    <label class="ad-label" for="nama_produk">Nama Produk <span>*</span></label>
                    <input type="text" id="nama_produk" name="nama_produk" class="ad-input @error('nama_produk') ad-input--error @enderror"
                           value="{{ old('nama_produk', $produk->nama_produk) }}" maxlength="150" required>
                    @error('nama_produk') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field">
                    <label class="ad-label" for="id_kategori">Kategori <span>*</span></label>
                    <select id="id_kategori" name="id_kategori" class="ad-select @error('id_kategori') ad-input--error @enderror" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $kat)
                            <option value="{{ $kat->id_kategori }}" @selected(old('id_kategori', $produk->id_kategori) === $kat->id_kategori)>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_kategori') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field">
                    <label class="ad-label" for="harga">Harga (Rp) <span>*</span></label>
                    <input type="number" id="harga" name="harga" class="ad-input @error('harga') ad-input--error @enderror"
                           value="{{ old('harga', (int) $produk->harga) }}" min="0" step="1000" required>
                    @error('harga') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field ad-form__full">
                    <label class="ad-label" for="deskripsi">Deskripsi <span>*</span></label>
                    <textarea id="deskripsi" name="deskripsi" class="ad-textarea @error('deskripsi') ad-input--error @enderror" required>{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                    @error('deskripsi') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field ad-form__full">
                    <label class="ad-label" for="spesifikasi_ukuran">Tabel Spesifikasi Ukuran</label>
                    <textarea id="spesifikasi_ukuran" name="spesifikasi_ukuran" class="ad-textarea" rows="6"
                              placeholder="Ukuran | Lingkar Dada | Panjang&#10;S | 84 cm | 110 cm">{{ old('spesifikasi_ukuran', $produk->spesifikasi_ukuran) }}</textarea>
                    <span class="ad-hint">Satu baris satu ukuran, kolom dipisah tanda |</span>
                    @error('spesifikasi_ukuran') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field ad-form__full">
                    <label class="ad-label" for="gambar">Gambar Produk</label>
                    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap">
                        @if ($produk->gambar)
                            <img src="{{ asset($produk->gambar) }}" alt="" class="ad-thumb" style="width:72px;height:72px">
                        @endif
                        <input type="file" id="gambar" name="gambar" class="ad-input" accept="image/png,image/jpeg" style="max-width:320px">
                    </div>
                    <span class="ad-hint">Format JPG, JPEG, atau PNG, maksimal 2 MB.</span>
                    @error('gambar') <span class="ad-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="ad-btn-row" style="margin-top:24px">
                <button type="submit" class="ad-btn">{{ $mode === 'create' ? 'Simpan Produk' : 'Simpan Perubahan' }}</button>
                <a href="{{ route('admin.produk.index') }}" class="ad-btn ad-btn--ghost">Batal</a>
            </div>
        </form>
    </section>
@endsection