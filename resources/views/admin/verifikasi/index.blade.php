@extends('layouts.admin')

@section('title', 'Verifikasi Bayar')
@section('nav', 'bayar')
@section('breadcrumb', 'Verifikasi Bayar')

@section('content')
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('admin.verifikasi.index') }}" class="toolbar-filters">
            <label class="search-field">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari no. invoice atau pelanggan..." aria-label="Cari pembayaran">
            </label>

            <span class="date-range">
                <label class="date-range__item">
                    <span>Dari</span>
                    <input type="date" name="dari" value="{{ $dari }}" aria-label="Dari tanggal upload">
                </label>
                <label class="date-range__item">
                    <span>Sampai</span>
                    <input type="date" name="sampai" value="{{ $sampai }}" aria-label="Sampai tanggal upload">
                </label>
            </span>

            <button type="submit" class="btn-ghost btn-filter">Terapkan</button>
        </form>

        <button type="button" class="btn-ghost btn-refresh" onclick="window.location.reload()" title="Muat ulang antrean">
            <svg viewBox="0 0 24 24"><path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4.5V11h-6.5"/></svg>
            Muat Ulang
        </button>
    </div>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Upload</th>
                    <th class="is-right">Total Transfer</th>
                    <th>Status</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $bayar)
                    @php
                        $pesanan = $bayar->pesanan;
                        $inisial = collect(preg_split('/\s+/', trim($pesanan->user?->nama ?? '-')))
                            ->filter()
                            ->take(2)
                            ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
                            ->implode('');
                    @endphp

                    <tr>
                        <td>
                            <span class="cell-title mono">{{ $pesanan->invoice?->no_invoice ?? '—' }}</span>
                            <span class="cell-sub">{{ $pesanan->jasa?->nama_jasa ?? '—' }} &middot; {{ $pesanan->pulau_tujuan }}</span>
                        </td>
                        <td>
                            <span class="pelanggan-cell">
                                <span class="pelanggan-cell__avatar">{{ $inisial ?: '—' }}</span>
                                <span>
                                    <span class="cell-title">{{ $pesanan->user?->nama ?? '—' }}</span>
                                    <span class="cell-sub">{{ $pesanan->user?->email ?? '—' }}</span>
                                </span>
                            </span>
                        </td>
                        <td>
                            <span class="mono">{{ $bayar->tanggal_upload?->format('d M Y') }}</span>
                            <span class="cell-sub">
                                {{ $bayar->tanggal_upload?->format('H:i') }} WIB
                                &middot; {{ $bayar->tanggal_upload?->locale('id')->diffForHumans(short: true) }}
                            </span>
                        </td>
                        <td class="is-right mono">Rp {{ number_format((float) $pesanan->total, 0, ',', '.') }}</td>
                        <td><span class="badge badge--menunggu_verifikasi">Menunggu Verifikasi</span></td>
                        <td class="is-right col-aksi">
                            <a href="{{ route('admin.verifikasi.show', $bayar) }}" class="btn-dark">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            Tidak ada pembayaran yang menunggu verifikasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>
                @if ($daftar->total() > 0)
                    Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} pesanan menunggu verifikasi
                @else
                    Menampilkan 0 dari 0 pesanan menunggu verifikasi
                @endif
            </span>

            <div class="admin-pagination__links">
                @if ($daftar->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $daftar->previousPageUrl() }}">Sebelumnya</a>
                @endif

                @if ($daftar->hasMorePages())
                    <a href="{{ $daftar->nextPageUrl() }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>
@endsection
