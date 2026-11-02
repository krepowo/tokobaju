@extends('layouts.admin')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumb_current', 'Dashboard')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $ringkas = function (float $angka): string {
            if ($angka >= 1000000) {
                return rtrim(rtrim(number_format($angka / 1000000, 1, ',', '.'), '0'), ',').'M';
            }
            if ($angka >= 1000) {
                return rtrim(rtrim(number_format($angka / 1000, 1, ',', '.'), '0'), ',').'K';
            }
            return number_format($angka, 0, ',', '.');
        };
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Dashboard</h1>
            <p class="ad-sub">Ringkasan penjualan Gavin Fashion Store hari ini, {{ now()->translatedFormat('d F Y') }}.</p>
        </div>
    </div>

    <div class="ad-stats">
        <a href="{{ route('admin.laporan.index') }}" class="ad-stat ad-stat--active">
            <span class="ad-stat__icon ad-stat__icon--gold">@include('admin.partials.icon', ['name' => 'money'])</span>
            <span class="ad-stat__chevron">@include('admin.partials.icon', ['name' => 'chev'])</span>
            <div class="ad-stat__label">Omzet Bulan Ini</div>
            <div class="ad-stat__value">{{ $rupiah($omzetBulanIni) }}</div>
        </a>

        <a href="{{ route('admin.verifikasi.index') }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--blue">@include('admin.partials.icon', ['name' => 'check'])</span>
            <span class="ad-stat__chevron">@include('admin.partials.icon', ['name' => 'chev'])</span>
            <div class="ad-stat__label">Perlu Diverifikasi</div>
            <div class="ad-stat__value">{{ $perluDiverifikasi }}</div>
        </a>

        <a href="{{ route('admin.kirim.index') }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--amber">@include('admin.partials.icon', ['name' => 'truck'])</span>
            <span class="ad-stat__chevron">@include('admin.partials.icon', ['name' => 'chev'])</span>
            <div class="ad-stat__label">Perlu Dikirim</div>
            <div class="ad-stat__value">{{ $perluDikirim }}</div>
        </a>

        <a href="{{ route('admin.stok.index') }}" class="ad-stat">
            <span class="ad-stat__icon ad-stat__icon--rose">@include('admin.partials.icon', ['name' => 'alert'])</span>
            <span class="ad-stat__chevron">@include('admin.partials.icon', ['name' => 'chev'])</span>
            <div class="ad-stat__label">Stok Menipis</div>
            <div class="ad-stat__value">{{ $stokMenipis }}</div>
        </a>
    </div>

    <section class="ad-card">
        <div class="ad-card__head">
            <h2 class="ad-section-title">Omzet 7 Hari Terakhir</h2>
            <span class="ad-badge ad-badge--gold">Pembayaran disetujui</span>
        </div>
        <div class="ad-card__body">
            <div class="ad-chart">
                <div class="ad-chart__y">
                    @foreach ([1, 0.75, 0.5, 0.25, 0] as $bagian)
                        <span>{{ $ringkas($chartMax * $bagian) }}</span>
                    @endforeach
                </div>
                <div class="ad-chart__plot">
                    <div class="ad-chart__bars">
                        @foreach ($chart as $titik)
                            <div class="ad-chart__col">
                                <div class="ad-chart__tip"><span class="ad-chart__dot"></span>{{ $titik['label'] }} - {{ $rupiah($titik['value']) }}</div>
                                <div class="ad-chart__bar" style="height: {{ max(2, round($titik['value'] / $chartMax * 100)) }}%"></div>
                                <span class="ad-chart__x">{{ $titik['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    @unless ($adaOmzet)
                        <div class="ad-chart__kosong">
                            Belum ada omzet dari pesanan yang pembayarannya disetujui pada 7 hari terakhir.
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </section>

    <section class="ad-card">
        <div class="ad-card__head">
            <h2 class="ad-section-title">Pesanan Terbaru</h2>
            <a href="{{ route('admin.pesanan.index') }}" class="ad-btn ad-btn--ghost ad-btn--sm">Lihat semua</a>
        </div>
        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesananTerbaru as $p)
                        <tr>
                            <td class="is-strong">
                                <a href="{{ route('admin.pesanan.show', $p) }}">{{ $p->invoice?->no_invoice ?? '—' }}</a>
                            </td>
                            <td class="is-muted">{{ $p->pelanggan?->nama ?? '—' }}</td>
                            <td>{{ $rupiah($p->total) }}</td>
                            <td>@include('admin.partials.badge', ['status' => $p->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="ad-empty">
                                    <strong>Belum ada pesanan</strong>
                                    Pesanan pelanggan akan tampil di sini setelah checkout.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p class="ad-sub" style="margin-top:18px">
        {{ $totalProduk }} produk &middot; {{ $totalVarian }} varian ukuran/warna terdaftar.
    </p>
@endsection