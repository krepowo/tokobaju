@extends('layouts.admin')

@section('title', 'Verifikasi Bayar')
@section('active', 'verifikasi')
@section('crumb_current', 'Verifikasi Bayar')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Verifikasi Bayar</h1>
            <p class="ad-sub">Periksa bukti transfer, lalu setujui atau tolak beserta alasannya.</p>
        </div>
    </div>

    <section class="ad-card">
        <nav class="ad-tabs">
            <a href="{{ route('admin.verifikasi.index') }}" class="ad-tab {{ $tab === 'menunggu' ? 'is-active' : '' }}">
                Menunggu Verifikasi
                <span class="ad-tab__count">{{ $jumlahMenunggu }}</span>
            </a>
            <a href="{{ route('admin.verifikasi.index', ['tab' => 'riwayat']) }}" class="ad-tab {{ $tab === 'riwayat' ? 'is-active' : '' }}">
                Riwayat
            </a>
        </nav>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th class="is-right">Total</th>
                        <th>Diunggah</th>
                        <th>Status</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $p)
                        @php $bayar = $p->pembayaranTerakhir; @endphp
                        <tr>
                            <td class="is-strong">{{ $p->invoice?->no_invoice ?? '—' }}</td>
                            <td class="is-muted">{{ $p->pelanggan?->nama ?? '—' }}</td>
                            <td class="is-right">{{ $rupiah($p->total) }}</td>
                            <td class="is-muted">{{ $bayar?->tanggal_upload?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                            <td>@include('admin.partials.badge', ['status' => $p->status])</td>
                            <td>
                                <div class="ad-table__actions">
                                    @if ($tab === 'menunggu')
                                        <a href="{{ route('admin.verifikasi.show', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Periksa</a>
                                    @else
                                        <a href="{{ route('admin.pesanan.show', $p) }}" class="ad-btn ad-btn--ghost ad-btn--sm">Detail</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ad-empty">
                                    <strong>{{ $tab === 'menunggu' ? 'Tidak ada pembayaran menunggu' : 'Belum ada riwayat verifikasi' }}</strong>
                                    {{ $tab === 'menunggu' ? 'Semua bukti transfer sudah diperiksa.' : 'Riwayat akan tampil setelah ada pembayaran yang diverifikasi.' }}
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