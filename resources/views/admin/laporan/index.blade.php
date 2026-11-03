@extends('layouts.admin')

@section('title', 'Laporan Omzet')
@section('nav', 'omzet')
@section('breadcrumb', 'Laporan Omzet')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $nilaiTren = array_column($tren, 'value');
        $maks = $nilaiTren ? max($nilaiTren) : 0;
        $skala = $maks > 0 ? $maks : 100000;
        $plotTop = 18; $plotBottom = 158; $plotH = $plotBottom - $plotTop; $plotKiri = 60;
        $jmlBar = max(1, count($tren));
        $slot = (720 - $plotKiri) / $jmlBar;
        $barW = max(3, min(44, (int) round($slot * 0.6)));
        $ringkas = function (float $n): string {
            if ($n >= 1000000) return 'Rp '.number_format($n / 1000000, 1, ',', '.').' jt';
            if ($n >= 1000) return 'Rp '.number_format($n / 1000, 0, ',', '.').' rb';
            return 'Rp '.number_format($n, 0, ',', '.');
        };
    @endphp

    <div class="admin-toolbar admin-toolbar--pesanan">
        <div class="admin-tabs">
            <a href="{{ route('admin.laporan.index', array_filter(['periode' => 'harian', 'tanggal' => $tanggal, 'kategori' => $kategoriId ?: null])) }}" class="admin-tab {{ $periode === 'harian' ? 'is-active' : '' }}">Harian</a>
            <a href="{{ route('admin.laporan.index', array_filter(['periode' => 'bulanan', 'bulan' => $bulan, 'kategori' => $kategoriId ?: null])) }}" class="admin-tab {{ $periode === 'bulanan' ? 'is-active' : '' }}">Bulanan</a>
            <a href="{{ route('admin.laporan.index', array_filter(['periode' => 'tahunan', 'tahun' => $tahun, 'kategori' => $kategoriId ?: null])) }}" class="admin-tab {{ $periode === 'tahunan' ? 'is-active' : '' }}">Tahunan</a>
        </div>

        <form method="GET" action="{{ route('admin.laporan.index') }}" class="toolbar-filters">
            <input type="hidden" name="periode" value="{{ $periode }}">
            @if ($periode === 'harian')
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="select-field" onchange="this.form.submit()">
            @elseif ($periode === 'tahunan')
                <select name="tahun" class="select-field" onchange="this.form.submit()">
                    @forelse ($tahunTersedia as $th)
                        <option value="{{ $th }}" @selected((int) $th === (int) $tahun)>{{ $th }}</option>
                    @empty
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                    @endforelse
                </select>
            @else
                <input type="month" name="bulan" value="{{ $bulan }}" class="select-field" onchange="this.form.submit()">
            @endif

            <select name="kategori" class="select-field" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($kategoriList as $k)
                    <option value="{{ $k->id_kategori }}" @selected($kategoriId === $k->id_kategori)>{{ $k->nama_kategori }}</option>
                @endforeach
            </select>

            <input type="search" name="q" value="{{ $q }}" class="search-field" placeholder="Cari invoice / pelanggan...">

            <a href="{{ route('admin.laporan.cetak', request()->query()) }}" target="_blank" class="btn-gold">Cetak Laporan</a>
        </form>
    </div>

    <p class="panel__meta" style="margin:-4px 0 14px">Ringkasan penjualan &middot; <strong>{{ $label }}</strong></p>

    <div class="stat-grid">
        <article class="stat-card stat-card--accent">
            <span class="stat-card__label">Total Omzet</span>
            <span class="stat-card__value">{{ $rupiah($totalOmzet) }}</span>
            <span class="stat-card__hint">Pesanan selesai (tanpa ongkir)</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Pesanan Selesai</span>
            <span class="stat-card__value">{{ number_format($jumlahPesanan, 0, ',', '.') }}</span>
            <span class="stat-card__hint">Transaksi lunas periode ini</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Produk Terjual</span>
            <span class="stat-card__value">{{ number_format($produkTerjual, 0, ',', '.') }} pcs</span>
            <span class="stat-card__hint">Total unit terjual</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Rata-rata / Pesanan</span>
            <span class="stat-card__value">{{ $rupiah($rataRata) }}</span>
            <span class="stat-card__hint">Nilai transaksi rata-rata</span>
        </article>
    </div>

    <section class="panel">
        <header class="panel__head">
            <h2>Tren Omzet</h2>
            <span class="panel__meta">{{ $label }}</span>
        </header>
        @if ($maks <= 0)
            <p class="empty">Belum ada omzet pada periode ini.</p>
        @else
            <div class="chart-wrap">
                <svg class="chart" viewBox="0 0 720 190" role="img" aria-label="Grafik tren omzet">
                    <line class="chart__grid" x1="{{ $plotKiri }}" y1="{{ $plotTop }}" x2="720" y2="{{ $plotTop }}"/>
                    <line class="chart__grid" x1="{{ $plotKiri }}" y1="{{ ($plotTop + $plotBottom) / 2 }}" x2="720" y2="{{ ($plotTop + $plotBottom) / 2 }}"/>
                    <line class="chart__grid" x1="{{ $plotKiri }}" y1="{{ $plotBottom }}" x2="720" y2="{{ $plotBottom }}"/>
                    <text class="chart__y" x="{{ $plotKiri - 8 }}" y="{{ $plotTop + 4 }}" text-anchor="end">{{ $ringkas($skala) }}</text>
                    <text class="chart__y" x="{{ $plotKiri - 8 }}" y="{{ $plotBottom + 4 }}" text-anchor="end">Rp 0</text>

                    @foreach ($tren as $i => $t)
                        @php
                            $tinggi = max(3, (int) round($plotH * ($t['value'] / $skala)));
                            $x = (int) round($plotKiri + $i * $slot + ($slot - $barW) / 2);
                            $y = $plotBottom - $tinggi;
                        @endphp
                        <g class="chart__bar {{ $t['value'] > 0 ? '' : 'is-empty' }}"
                           data-label="{{ $t['label'] }}" data-value="{{ $rupiah($t['value']) }} &middot; {{ $t['pesanan'] }} pesanan">
                            <rect class="chart__hit" x="{{ $x - 6 }}" y="{{ $plotTop }}" width="{{ $barW + 12 }}" height="{{ $plotH }}"/>
                            <rect class="chart__rect" x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $tinggi }}" rx="3"/>
                            @if ($jmlBar <= 12)
                                <text class="chart__x" x="{{ $x + $barW / 2 }}" y="{{ $plotBottom + 20 }}" text-anchor="middle">{{ $t['label'] }}</text>
                            @endif
                        </g>
                    @endforeach
                </svg>
                <div class="chart__tooltip" id="chart-tooltip" hidden></div>
            </div>
        @endif
    </section>

    <div class="stat-grid stat-grid--tiga">
        <section class="panel">
            <header class="panel__head"><h2>Produk Terlaris</h2><span class="panel__meta">Top {{ count($topProduk) }} &middot; {{ $kontribusiTop }}% omzet</span></header>
            <table class="data-table">
                <thead><tr><th>Produk</th><th class="is-right">Pcs</th><th class="is-right">Omzet</th></tr></thead>
                <tbody>
                    @forelse ($topProduk as $p)
                        <tr>
                            <td><span class="cell-title">{{ $p['nama'] }}</span><span class="cell-sub">{{ $p['kategori'] }}</span></td>
                            <td class="is-right mono">{{ $p['pcs'] }}</td>
                            <td class="is-right mono">{{ $rupiah($p['omzet']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Belum ada penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="panel">
            <header class="panel__head"><h2>Omzet per Kategori</h2></header>
            <div style="padding:6px 18px 18px">
                @php $totalKat = array_sum($perKategori) ?: 1; @endphp
                @forelse ($perKategori as $kat => $nilai)
                    <div style="margin:9px 0;font-size:13px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                            <span>{{ $kat }}</span>
                            <span style="color:var(--muted)">{{ $rupiah($nilai) }}</span>
                        </div>
                        <div class="progress"><span style="width:{{ round($nilai / $totalKat * 100) }}%"></span></div>
                    </div>
                @empty
                    <p class="empty">Belum ada data kategori.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="panel panel--table">
        <header class="panel__head"><h2>Rincian Transaksi</h2><span class="panel__meta">{{ $jumlahTransaksi }} transaksi</span></header>
        <table class="data-table">
            <thead><tr><th>No. Invoice</th><th>Pelanggan</th><th>Tanggal</th><th class="is-right">Total</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($transaksi as $t)
                    <tr>
                        <td class="mono">{{ $t->invoice?->no_invoice ?? '—' }}</td>
                        <td>{{ $t->pelanggan?->nama ?? '—' }}</td>
                        <td class="cell-sub">{{ $t->tanggal_pesanan?->format('d M Y H:i') }}</td>
                        <td class="is-right mono">{{ $rupiah($t->total) }}</td>
                        <td><span class="badge badge--{{ $t->status }}">{{ $t->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            <span>Halaman {{ $halaman }} dari {{ $totalHalaman }}</span>
            <div class="admin-pagination__links">
                @if ($halaman > 1)
                    <a href="{{ route('admin.laporan.index', array_filter(array_merge(request()->query(), ['page' => $halaman - 1]))) }}">Sebelumnya</a>
                @else
                    <span class="is-disabled">Sebelumnya</span>
                @endif
                @if ($halaman < $totalHalaman)
                    <a href="{{ route('admin.laporan.index', array_filter(array_merge(request()->query(), ['page' => $halaman + 1]))) }}">Selanjutnya</a>
                @else
                    <span class="is-disabled">Selanjutnya</span>
                @endif
            </div>
        </div>
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
                    var wr = wrap.getBoundingClientRect();
                    tip.textContent = bar.dataset.label + ' · ' + bar.dataset.value;
                    tip.hidden = false;
                    tip.style.left = (rect.left - wr.left + rect.width / 2) + 'px';
                    tip.style.top = (rect.top - wr.top - 8) + 'px';
                });
                bar.addEventListener('mouseleave', function () { tip.hidden = true; });
            });
        })();
    </script>
@endpush
