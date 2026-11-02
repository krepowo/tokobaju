@extends('layouts.admin')

@section('title', 'Pesanan & Invoice')
@section('nav', 'pesanan')
@section('breadcrumb', 'Pesanan & Invoice')

@section('content')
    @php
        $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    @endphp

    <form method="GET" action="{{ route('admin.pesanan.index') }}" class="admin-toolbar admin-toolbar--pesanan">
        <div class="toolbar-filters">
            <label class="search-field">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                <input type="search" name="cari" value="{{ $filter['cari'] }}"
                       placeholder="Cari no. invoice atau pelanggan..." aria-label="Cari pesanan">
            </label>

            <label class="select-field">
                <select name="status" onchange="this.form.submit()" aria-label="Filter status">
                    <option value="semua">Semua Status</option>
                    @foreach ($daftarStatus as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
            </label>

            <span class="date-range">
                <label class="date-range__item">
                    <span>Dari</span>
                    <input type="date" name="dari" value="{{ $filter['dari'] }}" aria-label="Dari tanggal">
                </label>
                <label class="date-range__item">
                    <span>Sampai</span>
                    <input type="date" name="sampai" value="{{ $filter['sampai'] }}" aria-label="Sampai tanggal">
                </label>
            </span>

            <button type="submit" class="btn-ghost">Terapkan</button>
        </div>

        <a href="{{ route('admin.pesanan.cetak', request()->query()) }}" target="_blank" class="btn-dark">
            <svg viewBox="0 0 24 24"><path d="M6.5 9V4h11v5"/><path d="M6.5 18H5a1.5 1.5 0 0 1-1.5-1.5v-5A1.5 1.5 0 0 1 5 10h14a1.5 1.5 0 0 1 1.5 1.5v5A1.5 1.5 0 0 1 19 18h-1.5"/><path d="M6.5 14h11v6h-11z"/></svg>
            Cetak Laporan
        </a>
    </form>

    <div class="panel panel--table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th class="is-right">Total</th>
                    <th>Status</th>
                    <th class="is-right col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarPesanan as $pesanan)
                    @php $tanggal = \Carbon\Carbon::parse($pesanan->tanggal_pesanan); @endphp

                    <tr>
                        <td class="mono">{{ $pesanan->invoice?->no_invoice ?? '—' }}</td>
                        <td class="mono">{{ $tanggal->day.' '.$bulan[(int) $tanggal->month].' '.$tanggal->year }}</td>
                        <td>
                            <span class="cell-title">{{ $pesanan->user?->nama ?? '—' }}</span>
                            <span class="cell-sub">{{ $pesanan->pulau_tujuan }}</span>
                        </td>
                        <td class="is-right mono">Rp {{ number_format((float) $pesanan->total, 0, ',', '.') }}</td>
                        <td><span class="badge badge--{{ $pesanan->status }}">{{ $pesanan->statusLabel() }}</span></td>
                        <td class="is-right col-aksi">
                            <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="link-detail">
                                Lihat Detail
                                <svg viewBox="0 0 24 24"><path d="M5 12h13M13 6.5 18.5 12 13 17.5"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            Tidak ada pesanan pada filter ini.
                            @if ($filter['cari'] === '' && $filter['status'] === 'semua')
                                Coba longgarkan rentang tanggal.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>
                @if ($daftarPesanan->total() > 0)
                    Menampilkan {{ $daftarPesanan->firstItem() }}–{{ $daftarPesanan->lastItem() }} dari {{ $daftarPesanan->total() }} pesanan
                    &middot; total Rp {{ number_format($ringkasan['total'], 0, ',', '.') }}
                @else
                    Menampilkan 0 dari 0 pesanan
                @endif
            </span>

            <div class="admin-pagination__links">
                @if ($daftarPesanan->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $daftarPesanan->previousPageUrl() }}">Sebelumnya</a>
                @endif

                @if ($daftarPesanan->hasMorePages())
                    <a href="{{ $daftarPesanan->nextPageUrl() }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>
@endsection
