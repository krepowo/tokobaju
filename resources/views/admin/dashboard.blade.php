@extends('layouts.admin')

@section('title', 'Dashboard')
@section('nav', 'dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    @php
        $maks = max(1, (float) $grafik->max('omzet'));
        $plotTop = 18;
        $plotBottom = 158;
        $plotH = $plotBottom - $plotTop;
        $plotKiri = 54;
        $lebarPlot = 720 - $plotKiri;
        $slot = $lebarPlot / max(1, $grafik->count());
        $barW = 44;
        $hariIni = now()->toDateString();
        $ringkas = function (float $nilai): string {
            if ($nilai >= 1000000) {
                return 'Rp '.number_format($nilai / 1000000, 1, ',', '.').' jt';
            }
            if ($nilai >= 1000) {
                return 'Rp '.number_format($nilai / 1000, 0, ',', '.').' rb';
            }

            return 'Rp '.number_format($nilai, 0, ',', '.');
        };
    @endphp

    {{-- Kartu statistik --}}
    <div class="stat-grid">
        <article class="stat-card stat-card--accent">
            <span class="stat-card__icon">
                <svg viewBox="0 0 24 24"><path d="M4 19V5M4 19h16"/><path d="m7.5 15 3.6-4.2 3 2.4 4.4-5.2"/></svg>
            </span>
            <span class="stat-card__label">Omzet Bulan Ini</span>
            <span class="stat-card__value">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</span>
            <span class="stat-card__hint">{{ $jumlahPesananBulanIni }} pesanan selesai &middot; {{ now()->translatedFormat('F Y') }}</span>
        </article>

        <article class="stat-card">
            <span class="stat-card__icon stat-card__icon--amber">
                <svg viewBox="0 0 24 24"><path d="M12 3.2 19 6v6c0 4-3 7.2-7 8.8C8 19.2 5 16 5 12V6l7-2.8Z"/><path d="M12 8.4v3.4M12 15.2h.01"/></svg>
            </span>
            <span class="stat-card__label">Perlu Diverifikasi</span>
            <span class="stat-card__value">{{ $perluDiverifikasi }}</span>
            <span class="stat-card__hint">Bukti transfer menunggu persetujuan</span>
        </article>

        <article class="stat-card">
            <span class="stat-card__icon stat-card__icon--blue">
                <svg viewBox="0 0 24 24"><path d="M2.8 6.5h9.4v9.7H2.8z"/><path d="M12.2 9.8h4l3 3v3.4h-7z"/><circle cx="6.4" cy="18.2" r="1.6"/><circle cx="16.2" cy="18.2" r="1.6"/></svg>
            </span>
            <span class="stat-card__label">Perlu Dikirim</span>
            <span class="stat-card__value">{{ $perluDikirim }}</span>
            <span class="stat-card__hint">Sudah dibayar, belum ada nomor resi</span>
        </article>

        <article class="stat-card">
            <span class="stat-card__icon stat-card__icon--red">
                <svg viewBox="0 0 24 24"><path d="m12 3.5 8.5 4.4L12 12.3 3.5 7.9 12 3.5Z"/><path d="m3.5 12.3 8.5 4.4 8.5-4.4"/><path d="m3.5 16.6 8.5 4.4 8.5-4.4"/></svg>
            </span>
            <span class="stat-card__label">Stok Menipis</span>
            <span class="stat-card__value">{{ $stokMenipis }}</span>
            <span class="stat-card__hint">Varian aktif dengan stok &le; {{ $ambangStokMenipis }} pcs</span>
        </article>
    </div>

    {{-- Grafik omzet 7 hari --}}
    <section class="panel">
        <header class="panel__head">
            <h2>Omzet 7 Hari Terakhir</h2>
            <span class="panel__meta">
                Total Rp {{ number_format($totalGrafik, 0, ',', '.') }}
                &middot; {{ $grafik->first()['label'] }}–{{ $grafik->last()['label'] }}
            </span>
        </header>

        <div class="chart-wrap">
            <svg class="chart" viewBox="0 0 720 190" role="img"
                 aria-label="Grafik batang omzet tujuh hari terakhir">
                <line class="chart__grid" x1="{{ $plotKiri }}" y1="{{ $plotTop }}" x2="720" y2="{{ $plotTop }}"/>
                <line class="chart__grid" x1="{{ $plotKiri }}" y1="{{ ($plotTop + $plotBottom) / 2 }}" x2="720" y2="{{ ($plotTop + $plotBottom) / 2 }}"/>
                <line class="chart__grid" x1="{{ $plotKiri }}" y1="{{ $plotBottom }}" x2="720" y2="{{ $plotBottom }}"/>

                <text class="chart__y" x="{{ $plotKiri - 10 }}" y="{{ $plotTop + 4 }}" text-anchor="end">{{ $ringkas($maks) }}</text>
                <text class="chart__y" x="{{ $plotKiri - 10 }}" y="{{ ($plotTop + $plotBottom) / 2 + 4 }}" text-anchor="end">{{ $ringkas($maks / 2) }}</text>
                <text class="chart__y" x="{{ $plotKiri - 10 }}" y="{{ $plotBottom + 4 }}" text-anchor="end">Rp 0</text>

                @foreach ($grafik as $i => $titik)
                    @php
                        $tinggi = max(4, (int) round($plotH * ($titik['omzet'] / $maks)));
                        $x = (int) round($plotKiri + $i * $slot + ($slot - $barW) / 2);
                        $y = $plotBottom - $tinggi;
                        $terisi = $titik['omzet'] > 0;
                    @endphp

                    <g class="chart__bar {{ $titik['tanggal'] === $hariIni ? 'is-today' : '' }} {{ $terisi ? '' : 'is-empty' }}"
                       data-label="{{ $titik['label'] }}"
                       data-value="Rp {{ number_format($titik['omzet'], 0, ',', '.') }}">
                        <rect class="chart__hit" x="{{ $x - 12 }}" y="{{ $plotTop }}" width="{{ $barW + 24 }}" height="{{ $plotH }}"/>
                        <rect class="chart__rect" x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $tinggi }}" rx="5"/>
                        <text class="chart__x" x="{{ $x + $barW / 2 }}" y="{{ $plotBottom + 24 }}" text-anchor="middle">{{ $titik['label'] }}</text>
                    </g>
                @endforeach
            </svg>

            <div class="chart__tooltip" id="chart-tooltip" hidden></div>
        </div>
    </section>

    {{-- Pesanan terbaru --}}
    <section class="panel">
        <header class="panel__head">
            <h2>Pesanan Terbaru</h2>
            <span class="panel__link is-soon" title="Segera hadir">Lihat semua &rarr;</span>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Pelanggan</th>
                    <th class="is-right">Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesananTerbaru as $pesanan)
                    <tr>
                        <td class="mono">{{ $pesanan->invoice?->no_invoice ?? '—' }}</td>
                        <td>{{ $pesanan->user?->nama ?? '—' }}</td>
                        <td class="is-right mono">Rp {{ number_format((float) $pesanan->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge badge--{{ $pesanan->status }}">{{ $pesanan->statusLabel() }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">Belum ada pesanan masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var wrap = document.querySelector('.chart-wrap');
            var tip = document.getElementById('chart-tooltip');
            if (!wrap || !tip) return;

            wrap.querySelectorAll('.chart__bar').forEach(function (bar) {
                bar.addEventListener('mouseenter', function () {
                    var rect = bar.querySelector('.chart__rect').getBoundingClientRect();
                    var wrapRect = wrap.getBoundingClientRect();
                    tip.textContent = bar.dataset.label + ' · ' + bar.dataset.value;
                    tip.hidden = false;
                    tip.style.left = (rect.left - wrapRect.left + rect.width / 2) + 'px';
                    tip.style.top = (rect.top - wrapRect.top - 8) + 'px';
                });

                bar.addEventListener('mouseleave', function () {
                    tip.hidden = true;
                });
            });
        })();
    </script>
@endpush
