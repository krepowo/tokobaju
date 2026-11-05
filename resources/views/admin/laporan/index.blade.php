@extends('layouts.admin')

@section('title', 'Laporan Omzet')
@section('nav', 'omzet')
@section('breadcrumb', 'Laporan Omzet')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $ringkas = function (float $n): string {
            if ($n >= 1000000) return 'Rp '.number_format($n / 1000000, 1, ',', '.').' jt';
            if ($n >= 1000) return 'Rp '.number_format($n / 1000, 0, ',', '.').' rb';
            return 'Rp '.number_format($n, 0, ',', '.');
        };
        $nilaiTren = array_column($tren, 'value');
        $maks = $nilaiTren ? max($nilaiTren) : 0;
        $skala = $maks > 0 ? $maks : 100000;
        $plotTop = 18; $plotBottom = 158; $plotH = $plotBottom - $plotTop; $plotKiri = 60;
        $jmlBar = max(1, count($tren));
        $slot = (720 - $plotKiri) / $jmlBar;
        $barW = max(3, min(44, (int) round($slot * 0.6)));
        $palet = ['#dfb169', '#b88b42', '#f0c987', '#8a6d3b', '#cbb27c', '#a5844a', '#e6d3a8', '#6b5130'];
        $totalKat = array_sum(array_values($perKategori));
        $stops = []; $legend = []; $acc = 0.0; $iKat = 0;
        foreach ($perKategori as $kat => $nilai) {
            $pRaw = $totalKat > 0 ? ($nilai / $totalKat * 100) : 0.0;
            $color = $palet[$iKat % count($palet)];
            $stops[] = $color.' '.$acc.'% '.($acc + $pRaw).'%';
            $legend[] = ['nama' => $kat, 'pct' => round($pRaw), 'nilai' => $nilai, 'color' => $color];
            $acc += $pRaw; $iKat++;
        }
        $donutGrad = $stops ? 'conic-gradient('.implode(', ', $stops).')' : '#eef0f3';
    @endphp

    <div class="page-head">
        <div class="page-head__title">
            <div class="fin-label">Financial Audit &amp; Analytics</div>
            <h1>Laporan Omzet</h1>
            <p class="page-head__sub">Ringkasan penjualan per periode</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.laporan.cetak', request()->query()) }}" target="_blank" class="btn-gold">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
                Cetak Laporan
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.laporan.index') }}" class="admin-toolbar admin-toolbar--laporan">
        <div class="toolbar-filters">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="q" value="{{ $q }}">

            <div class="admin-tabs" role="tablist">
                <a href="{{ route('admin.laporan.index', array_filter(['periode' => 'harian', 'tanggal' => $tanggal, 'kategori' => $kategoriId ?: null])) }}" class="admin-tab {{ $periode === 'harian' ? 'is-active' : '' }}">Harian</a>
                <a href="{{ route('admin.laporan.index', array_filter(['periode' => 'bulanan', 'bulan' => $bulan, 'kategori' => $kategoriId ?: null])) }}" class="admin-tab {{ $periode === 'bulanan' ? 'is-active' : '' }}">Bulanan</a>
                <a href="{{ route('admin.laporan.index', array_filter(['periode' => 'tahunan', 'tahun' => $tahun, 'kategori' => $kategoriId ?: null])) }}" class="admin-tab {{ $periode === 'tahunan' ? 'is-active' : '' }}">Tahunan</a>
            </div>

            @if ($periode === 'harian')
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="plain-input" aria-label="Tanggal">
            @elseif ($periode === 'tahunan')
                <label class="select-field">
                    <select name="tahun" aria-label="Tahun">
                        @forelse ($tahunTersedia as $th)
                            <option value="{{ $th }}" @selected((int) $th === (int) $tahun)>{{ $th }}</option>
                        @empty
                            <option value="{{ now()->year }}">{{ now()->year }}</option>
                        @endforelse
                    </select>
                    <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
                </label>
            @else
                <input type="month" name="bulan" value="{{ $bulan }}" class="plain-input" aria-label="Bulan">
            @endif

            <label class="select-field">
                <select name="kategori" aria-label="Kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $k)
                        <option value="{{ $k->id_kategori }}" @selected($kategoriId === $k->id_kategori)>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
            </label>

            <button type="submit" class="btn-gold">Terapkan</button>
        </div>
    </form>

    <p class="panel__meta" style="margin:-6px 0 14px">Ringkasan penjualan &middot; <strong>{{ $label }}</strong></p>

    <div class="stat-grid">
        <article class="stat-card stat-card--accent stat-card--upper">
            <span class="stat-card__label">Total Omzet</span>
            <span class="stat-card__value">{{ $rupiah($totalOmzet) }}</span>
            <span class="stat-card__hint">Pesanan selesai (tanpa ongkir)</span>
        </article>
        <article class="stat-card stat-card--upper">
            <span class="stat-card__label">Pesanan Selesai</span>
            <span class="stat-card__value">{{ number_format($jumlahPesanan, 0, ',', '.') }}</span>
            <span class="stat-card__hint">pesanan</span>
        </article>
        <article class="stat-card stat-card--upper">
            <span class="stat-card__label">Produk Terjual</span>
            <span class="stat-card__value">{{ number_format($produkTerjual, 0, ',', '.') }} <small>pcs</small></span>
            <span class="stat-card__hint">Rata-rata {{ $jumlahPesanan > 0 ? number_format($produkTerjual / $jumlahPesanan, 1, ',', '.') : '0,0' }} pcs / pesanan</span>
        </article>
        <article class="stat-card stat-card--upper">
            <span class="stat-card__label">Rata-rata Nilai Pesanan</span>
            <span class="stat-card__value">{{ $rupiah($rataRata) }}</span>
            <span class="stat-card__hint">Nilai transaksi rata-rata</span>
        </article>
    </div>

    <section class="panel">
        <header class="panel__head">
            <h2>Tren Omzet {{ $periode === 'harian' ? 'Harian' : ($periode === 'tahunan' ? 'Tahunan' : 'Bulanan') }} &mdash; {{ $label }}</h2>
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

    <div class="laporan-cols">
        <section class="panel">
            <header class="panel__head">
                <div>
                    <div class="fin-label">Peringkat Produk</div>
                    <h2 style="margin:0">Produk Terlaris</h2>
                </div>
                <span class="panel__meta">Top {{ count($topProduk) }} &middot; {{ $kontribusiTop }}% omzet</span>
            </header>
            <ul class="rank-list">
                @forelse ($topProduk as $i => $p)
                    <li class="rank-item">
                        <span class="rank-item__no">{{ $loop->iteration }}</span>
                        <img class="rank-item__thumb" src="{{ $p['gambar_url'] ?? asset('images/prod-kaos.jpg') }}" alt="{{ $p['nama'] }}">
                        <div>
                            <div class="rank-item__name">{{ $p['nama'] }}</div>
                            <div class="rank-item__sub">{{ $p['kategori'] }} &middot; {{ $p['pcs'] }} pcs terjual</div>
                        </div>
                        <div>
                            <div class="rank-item__amt">{{ $rupiah($p['omzet']) }}</div>
                            <div class="rank-item__pct">{{ $produkTerjual > 0 ? round($p['pcs'] / $produkTerjual * 100, 1) : 0 }}% vol</div>
                        </div>
                    </li>
                @empty
                    <li class="empty">Belum ada penjualan.</li>
                @endforelse
            </ul>
            <div style="padding:10px 18px 16px">
                <a href="{{ route('admin.produk.index') }}" class="panel__link">Lihat Semua Produk &rsaquo;</a>
            </div>
        </section>

        <section class="panel">
            <header class="panel__head">
                <div>
                    <div class="fin-label">Distribusi Segmen</div>
                    <h2 style="margin:0">Omzet per Kategori</h2>
                </div>
            </header>
            <div class="donut-box">
                <div class="donut" style="background: {{ $donutGrad }}" role="img" aria-label="Donat omzet per kategori">
                    <div class="donut__hole">
                        <span class="donut__label">Total</span>
                        <span class="donut__total">100%</span>
                        <span class="donut__sub">{{ count($perKategori) }} Kategori</span>
                    </div>
                </div>
                <ul class="legend">
                    @forelse ($legend as $seg)
                        <li>
                            <span class="legend__name"><span class="legend__dot" style="background:{{ $seg['color'] }}"></span>{{ $seg['nama'] }}</span>
                            <span class="legend__pct">{{ $seg['pct'] }}%</span>
                            <span class="legend__val">{{ $rupiah($seg['nilai']) }}</span>
                        </li>
                    @empty
                        <li class="empty" style="border:0">Belum ada data kategori.</li>
                    @endforelse
                </ul>
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

    <div class="callout">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01"/><path d="M12 11v5"/></svg>
        <span>Omzet dihitung dari total <strong>subtotal produk</strong> pada pesanan berstatus <strong>selesai</strong>, tanpa ongkos kirim. Pesanan dibatalkan &amp; yang belum lunas tidak diperhitungkan.</span>
    </div>
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