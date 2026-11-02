@extends('layouts.admin')

@section('title', 'Pesanan & Invoice')
@section('active', 'pesanan')
@section('crumb_current', 'Pesanan & Invoice')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Pesanan &amp; Invoice</h1>
            <p class="ad-sub">{{ $ringkasan['semua'] }} pesanan tercatat. Invoice dibuat otomatis saat checkout.</p>
        </div>
    </div>

    <div class="ad-stats">
        <a href="{{ route('admin.pesanan.index', ['status' => 'menunggu_pembayaran']) }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--amber">@include('admin.partials.icon', ['name' => 'receipt'])</span>
            <div class="ad-stat__label">Menunggu Pembayaran</div>
            <div class="ad-stat__value">{{ $ringkasan['menunggu_pembayaran'] }}</div>
        </a>
        <a href="{{ route('admin.verifikasi.index') }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--blue">@include('admin.partials.icon', ['name' => 'check'])</span>
            <div class="ad-stat__label">Menunggu Verifikasi</div>
            <div class="ad-stat__value">{{ $ringkasan['menunggu_verifikasi'] }}</div>
        </a>
        <a href="{{ route('admin.kirim.index') }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--gold">@include('admin.partials.icon', ['name' => 'truck'])</span>
            <div class="ad-stat__label">Diproses</div>
            <div class="ad-stat__value">{{ $ringkasan['diproses'] }}</div>
        </a>
        <a href="{{ route('admin.pesanan.index', ['status' => 'selesai']) }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--rose">@include('admin.partials.icon', ['name' => 'star'])</span>
            <div class="ad-stat__label">Selesai</div>
            <div class="ad-stat__value">{{ $ringkasan['selesai'] }}</div>
        </a>
    </div>

    <section class="ad-card">
        <div class="ad-card__head">
            <form method="GET" action="{{ route('admin.pesanan.index') }}" class="ad-toolbar" style="flex:1">
                <label class="ad-search">
                    @include('admin.partials.icon', ['name' => 'search'])
                    <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Cari no. invoice atau nama pelanggan...">
                </label>
                <select name="status" class="ad-select" style="max-width:220px" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $kunci => $info)
                        <option value="{{ $kunci }}" @selected($status === $kunci)>{{ $info['label'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ad-btn ad-btn--ghost">Cari</button>
            </form>
            @if ($status || $q !== '')
                <a href="{{ route('admin.pesanan.index') }}" class="ad-btn ad-btn--ghost ad-btn--sm">Reset</a>
            @endif
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th class="is-right">Total</th>
                        <th>Status</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $p)
                        <tr>
                            <td class="is-strong">
                                <a href="{{ route('admin.pesanan.show', $p) }}">{{ $p->invoice?->no_invoice ?? '—' }}</a>
                            </td>
                            <td class="is-muted">
                                {{ $p->pelanggan?->nama ?? '—' }}
                                <span class="ad-table__sub">{{ $p->pelanggan?->email }}</span>
                            </td>
                            <td class="is-muted">{{ $p->tanggal_pesanan?->translatedFormat('d M Y H:i') }}</td>
                            <td class="is-right">{{ $rupiah($p->total) }}</td>
                            <td>@include('admin.partials.badge', ['status' => $p->status])</td>
                            <td>
                                <div class="ad-table__actions">
                                    <a href="{{ route('admin.pesanan.show', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Detail</a>
                                    <a href="{{ route('admin.pesanan.cetak', $p) }}" target="_blank" class="ad-btn ad-btn--ghost ad-btn--sm">Invoice</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ad-empty">
                                    <strong>Pesanan tidak ditemukan</strong>
                                    Coba ubah kata kunci atau filter status.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ad-card__foot">
            @include('admin.partials.pager', ['items' => $pesanan])
        </div>
    </section>
@endsection