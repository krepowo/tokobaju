@extends('layouts.admin')

@section('title', 'Laporan Omzet')
@section('active', 'laporan')
@section('crumb_current', 'Laporan Omzet')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $ringkas = function (float $angka): string {
            if ($angka >= 1000000000) {
                return 'Rp '.rtrim(rtrim(number_format($angka / 1000000000, 1, ',', '.'), '0'), ',').'M';
            }
            if ($angka >= 1000000) {
                return 'Rp '.rtrim(rtrim(number_format($angka / 1000000, 1, ',', '.'), '0'), ',').'jt';
            }
            if ($angka >= 1000) {
                return 'Rp '.rtrim(rtrim(number_format($angka / 1000, 1, ',', '.'), '0'), ',').'rb';
            }
            return 'Rp '.number_format($angka, 0, ',', '.');
        };
        $parameter = array_filter([
            'periode' => $periode,
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategoriId ?: null,
        ]);
        $tautan = fn (array $ubah = []) => route('admin.laporan.index', array_filter(array_merge($parameter, $ubah)));

        $nilaiTren = array_column($tren, 'value');
        $maksTren = $nilaiTren ? max($nilaiTren) : 0;
        $skala = $maksTren > 0 ? $maksTren : 100000;
        $indeksPuncak = $maksTren > 0 ? array_search($maksTren, $nilaiTren, true) : false;
        $puncak = $indeksPuncak !== false ? $tren[$indeksPuncak] : null;

        $palet = ['#b8892b', '#d9b26a', '#8a6a12', '#e8d7ad', '#6b7280', '#94a3b8', '#c2a878'];
        $totalKategori = array_sum($perKategori) ?: 1;
        $potongan = [];
        $mulaiSudut = 0.0;
        foreach (array_values($perKategori) as $i => $nilai) {
            $derajat = $nilai / $totalKategori * 360;
            $potongan[] = $palet[$i % count($palet)].' '.round($mulaiSudut, 2).'deg '.round($mulaiSudut + $derajat, 2).'deg';
            $mulaiSudut += $derajat;
        }
        $gradienDonut = $potongan ? 'conic-gradient('.implode(', ', $potongan).')' : '#f2f4f6';
    @endphp

    <div class="ad-head">
        <div>
            <div class="ps-eyebrow" style="color:#9ca3af">Financial Audit &amp; Analytics</div>
            <h1 class="ad-h1">Laporan Omzet</h1>
            <p class="ad-sub">Ringkasan penjualan per periode &middot; {{ $label }}</p>
        </div>
        <a href="{{ route('admin.laporan.cetak', $parameter) }}" target="_blank" class="ad-btn ad-btn--dark">
            @include('admin.partials.icon', ['name' => 'print'])
            Cetak Laporan
        </a>
    </div>

    <section class="ad-card">
        <div class="ad-card__body" style="padding-bottom:0">
            <div class="ad-toolbar" style="gap:12px">
                <div class="ad-toolbar" style="gap:4px; background:#f2f4f6; padding:4px; border-radius:8px">
                    <a href="{{ $tautan(['periode' => 'harian']) }}" class="ad-btn {{ $periode === 'harian' ? 'ad-btn--dark' : 'ad-btn--ghost' }} ad-btn--sm" style="border:0">Harian</a>
                    <a href="{{ $tautan(['periode' => 'bulanan']) }}" class="ad-btn {{ $periode === 'bulanan' ? 'ad-btn--dark' : 'ad-btn--ghost' }} ad-btn--sm" style="border:0">Bulanan</a>
                    <a href="{{ $tautan(['periode' => 'tahunan']) }}" class="ad-btn {{ $periode === 'tahunan' ? 'ad-btn--dark' : 'ad-btn--ghost' }} ad-btn--sm" style="border:0">Tahunan</a>
                </div>

                <form method="GET" action="{{ route('admin.laporan.index') }}" class="ad-toolbar">
                    <input type="hidden" name="periode" value="{{ $periode }}">
                    @if ($periode === 'harian')
                        <input type="date" name="tanggal" value="{{ $tanggal }}" class="ad-input" style="max-width:170px">
                    @elseif ($periode === 'bulanan')
                        <input type="month" name="bulan" value="{{ $bulan }}" class="ad-input" style="max-width:170px">
                    @else
                        <select name="tahun" class="ad-select" style="max-width:130px">
                            @forelse ($tahunTersedia as $th)
                                <option value="{{ $th }}" @selected((int) $th === (int) $tahun)>{{ $th }}</option>
                            @empty
                                <option value="{{ $tahun }}">{{ $tahun }}</option>
                            @endforelse
                        </select>
                    @endif

                    <select name="kategori" class="ad-select" style="max-width:190px">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat->id_kategori }}" @selected($kategoriId === $kat->id_kategori)>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="ad-btn ad-btn--ghost">Terapkan</button>
                </form>
            </div>
        </div>

        <div class="ad-card__body" style="padding-top:18px; padding-bottom:6px">
            <div class="lp-kpi">
                <div class="lp-kpi__card">
                    <div class="lp-kpi__label">Total Omzet</div>
                    <div class="lp-kpi__value">{{ $rupiah($totalOmzet) }}</div>
                    <div class="lp-kpi__sub">Tanpa ongkos kirim</div>
                </div>
                <div class="lp-kpi__card">
                    <div class="lp-kpi__label">Pesanan Selesai</div>
                    <div class="lp-kpi__value">{{ $jumlahPesanan }} pesanan</div>
                    <div class="lp-kpi__sub">Periode {{ $label }}</div>
                </div>
                <div class="lp-kpi__card">
                    <div class="lp-kpi__label">Produk Terjual</div>
                    <div class="lp-kpi__value">{{ $produkTerjual }} pcs</div>
                    <div class="lp-kpi__sub">Rata-rata {{ $jumlahPesanan > 0 ? number_format($produkTerjual / $jumlahPesanan, 1, ',', '.') : '0' }} pcs / pesanan</div>
                </div>
                <div class="lp-kpi__card">
                    <div class="lp-kpi__label">Rata-rata Nilai Pesanan</div>
                    <div class="lp-kpi__value">{{ $rupiah($rataRata) }}</div>
                    <div class="lp-kpi__sub">Per pesanan selesai</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ad-card">
        <div class="ad-card__head">
            <h2 class="ad-section-title">Tren Omzet {{ $periode === 'bulanan' ? 'Harian' : ($periode === 'tahunan' ? 'Bulanan' : 'per Pesanan') }} &mdash; {{ $label }}</h2>
            <span class="ad-badge ad-badge--gold">Total Akumulasi: {{ $rupiah($totalOmzet) }}</span>
        </div>

        <div class="ad-card__body">
            @if ($maksTren <= 0)
                <div class="ad-empty" style="padding:32px 0">
                    <strong>Belum ada omzet pada periode ini</strong>
                    Semua pesanan pada periode ini belum berstatus selesai.
                </div>
            @else
                <div class="lp-chart">
                    <div class="lp-chart__y">
                        @foreach ([1, 0.75, 0.5, 0.25, 0] as $bagian)
                            <span>{{ $ringkas($skala * $bagian) }}</span>
                        @endforeach
                    </div>
                    <div class="lp-chart__plot">
                        <div class="lp-chart__bars">
                            @foreach ($tren as $i => $titik)
                                <div class="lp-chart__col {{ $i === $indeksPuncak ? 'is-peak' : '' }}">
                                    <div class="lp-chart__tip">
                                        {{ $titik['label'] }} &bull; {{ $rupiah($titik['value']) }} &bull; {{ $titik['pesanan'] }} pesanan
                                    </div>
                                    <div class="lp-chart__bar" style="height: {{ max(1, round($titik['value'] / $skala * 100)) }}%"></div>
                                    @if ($i % 5 === 0 || $i === count($tren) - 1)
                                        <span class="lp-chart__label">{{ $titik['label'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($puncak)
                    <div class="ad-note" style="margin-top:22px; display:inline-flex; gap:8px; align-items:center">
                        <strong>Puncak penjualan:</strong>
                        {{ $puncak['label'] }} &bull; {{ $rupiah($puncak['value']) }} &bull; {{ $puncak['pesanan'] }} pesanan
                    </div>
                @endif
            @endif
        </div>
    </section>

    <div class="lp-grid">
        <section class="ad-card">
            <div class="ad-card__head">
                <div>
                    <div class="ul-produk__label">Peringkat Produk</div>
                    <h2 class="ad-section-title" style="margin-top:4px">Produk Terlaris</h2>
                </div>
                <span class="ad-badge ad-badge--muted">Top 5 Item</span>
            </div>

            <div class="ad-card__body" style="padding-top:6px">
                @forelse ($topProduk as $i => $item)
                    <div class="lp-top__row">
                        <span class="lp-top__rank">{{ $loop->iteration }}</span>
                        <div style="flex:1 1 auto; min-width:0">
                            <div class="lp-top__nama">{{ $item['nama'] }}</div>
                            <div class="lp-top__meta">{{ $item['kategori'] }} &bull; {{ $item['pcs'] }} pcs terjual</div>
                            <div class="lp-top__bar">
                                <span class="lp-top__fill" style="width: {{ $totalOmzet > 0 ? round($item['omzet'] / $totalOmzet * 100) : 0 }}%"></span>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <div class="lp-top__nilai">{{ $rupiah($item['omzet']) }}</div>
                            <div class="lp-top__vol">{{ $totalOmzet > 0 ? number_format($item['omzet'] / $totalOmzet * 100, 1, ',', '.') : '0' }}% vol</div>
                        </div>
                    </div>
                @empty
                    <div class="ad-empty" style="padding:32px 0">
                        <strong>Belum ada produk terjual</strong>
                        Data muncul setelah ada pesanan selesai pada periode ini.
                    </div>
                @endforelse
            </div>

            @if (count($topProduk) > 0)
                <div class="ad-card__foot">
                    <span>Kontribusi 5 besar terhadap omzet: <strong>{{ number_format($kontribusiTop, 1, ',', '.') }}%</strong></span>
                    <a href="{{ route('admin.produk.index') }}" class="ad-btn ad-btn--ghost ad-btn--sm">Lihat Semua Produk</a>
                </div>
            @endif
        </section>

        <section class="ad-card">
            <div class="ad-card__head">
                <div>
                    <div class="ul-produk__label">Distribusi Segmen</div>
                    <h2 class="ad-section-title" style="margin-top:4px">Omzet per Kategori</h2>
                </div>
            </div>

            <div class="ad-card__body">
                @if (count($perKategori) === 0)
                    <div class="ad-empty" style="padding:24px 0">
                        <strong>Belum ada data kategori</strong>
                    </div>
                @else
                    <div class="lp-donut-wrap">
                        <div class="lp-donut" style="background: {{ $gradienDonut }}">
                            <div class="lp-donut__hole">
                                <div>
                                    <span>Total</span>
                                    <strong>{{ count($perKategori) }} Kategori</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-legend">
                        @foreach ($perKategori as $nama => $nilai)
                            <div class="lp-legend__row">
                                <span class="lp-legend__dot" style="background: {{ $palet[$loop->index % count($palet)] }}"></span>
                                <span class="lp-legend__nama">{{ $nama }}</span>
                                <span class="lp-legend__pct">{{ number_format($nilai / $totalKategori * 100, 0, ',', '.') }}%</span>
                                <span class="lp-legend__val">{{ $rupiah($nilai) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <section class="ad-card">
        <div class="ad-card__head">
            <div>
                <div class="ul-produk__label">Rincian Buku Kas</div>
                <h2 class="ad-section-title" style="margin-top:4px">Rincian Transaksi Selesai</h2>
            </div>
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="ad-toolbar">
                @foreach ($parameter as $kunci => $nilai)
                    <input type="hidden" name="{{ $kunci }}" value="{{ $nilai }}">
                @endforeach
                <label class="ad-search" style="min-width:240px">
                    @include('admin.partials.icon', ['name' => 'search'])
                    <input type="search" name="q" value="{{ $q }}" class="ad-input" placeholder="Cari invoice atau nama...">
                </label>
            </form>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th class="is-center">Jumlah Item</th>
                        <th class="is-right">Total Produk (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksi as $p)
                        <tr>
                            <td class="is-muted">{{ $p->tanggal_pesanan?->translatedFormat('d M Y, H:i') }}</td>
                            <td class="is-strong">{{ $p->invoice?->no_invoice ?? '—' }}</td>
                            <td>
                                <div class="ad-produk">
                                    <span class="ad-avatar">{{ strtoupper(\Illuminate\Support\Str::of($p->pelanggan?->nama ?? 'P')->explode(' ')->map(fn ($k) => mb_substr($k, 0, 1))->take(2)->implode('')) }}</span>
                                    <span class="ad-produk__nama">{{ $p->pelanggan?->nama ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="is-center">{{ $p->detail->count() }} item</td>
                            <td class="is-right is-strong">{{ number_format((float) $p->detail->sum('subtotal'), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ad-empty">
                                    <strong>Tidak ada transaksi</strong>
                                    Belum ada pesanan selesai yang cocok dengan filter ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($jumlahTransaksi > 0)
                    <tfoot>
                        <tr>
                            <td class="is-strong">Total Keseluruhan ({{ $jumlahTransaksi }} transaksi)</td>
                            <td></td>
                            <td></td>
                            <td class="is-center is-strong">{{ $produkTerjual }} pcs</td>
                            <td class="is-right is-strong">{{ number_format((float) $totalOmzet, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="ad-card__foot">
            <span>
                @if ($jumlahTransaksi > 0)
                    Menampilkan {{ ($halaman - 1) * 5 + 1 }}&ndash;{{ min($halaman * 5, $jumlahTransaksi) }} dari {{ $jumlahTransaksi }} transaksi
                @else
                    Tidak ada transaksi
                @endif
            </span>

            @if ($totalHalaman > 1)
                <div class="ad-pager__pages">
                    @php $tautanHalaman = fn ($n) => route('admin.laporan.index', array_filter(array_merge($parameter, ['q' => $q ?: null, 'page' => $n]))); @endphp
                    <a href="{{ $tautanHalaman(max(1, $halaman - 1)) }}" class="ad-pager__link {{ $halaman === 1 ? 'is-disabled' : '' }}">&lsaquo;</a>
                    @for ($n = 1; $n <= min($totalHalaman, 5); $n++)
                        <a href="{{ $tautanHalaman($n) }}" class="ad-pager__link {{ $n === $halaman ? 'is-active' : '' }}">{{ $n }}</a>
                    @endfor
                    @if ($totalHalaman > 5)
                        <span class="ad-pager__link is-disabled">...</span>
                        <a href="{{ $tautanHalaman($totalHalaman) }}" class="ad-pager__link {{ $halaman === $totalHalaman ? 'is-active' : '' }}">{{ $totalHalaman }}</a>
                    @endif
                    <a href="{{ $tautanHalaman(min($totalHalaman, $halaman + 1)) }}" class="ad-pager__link {{ $halaman === $totalHalaman ? 'is-disabled' : '' }}">&rsaquo;</a>
                </div>
            @endif
        </div>

        <div class="ad-card__body" style="padding-top:0">
            <p class="lp-notes">
                <strong>Ketentuan Kalkulasi Finansial:</strong>
                omzet dihitung dari pesanan berstatus <em>Selesai</em>, tanpa ongkos kirim.
            </p>
        </div>
    </section>
@endsection
