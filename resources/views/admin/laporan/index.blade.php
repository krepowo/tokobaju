@extends('layouts.admin')

@section('title', 'Laporan Omzet')
@section('active', 'laporan')
@section('crumb_current', 'Laporan Omzet')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $query = ['periode' => $periode, 'tanggal' => $tanggal, 'bulan' => $bulan, 'tahun' => $tahun];
    @endphp

    <div class="ad-head">
        <div>
            <h1 class="ad-h1">Laporan Omzet</h1>
            <p class="ad-sub">Periode {{ $label }} &middot; hanya pesanan yang pembayarannya sudah disetujui.</p>
        </div>
        <a href="{{ route('admin.laporan.cetak', $query) }}" target="_blank" class="ad-btn">
            @include('admin.partials.icon', ['name' => 'print'])
            Cetak Laporan
        </a>
    </div>

    <section class="ad-card">
        <div class="ad-card__body">
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="ad-toolbar">
                <select name="periode" class="ad-select" style="max-width:180px" onchange="this.form.submit()">
                    <option value="harian" @selected($periode === 'harian')>Harian</option>
                    <option value="bulanan" @selected($periode === 'bulanan')>Bulanan</option>
                    <option value="tahunan" @selected($periode === 'tahunan')>Tahunan</option>
                </select>

                @if ($periode === 'harian')
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="ad-input" style="max-width:190px" onchange="this.form.submit()">
                @elseif ($periode === 'bulanan')
                    <input type="month" name="bulan" value="{{ $bulan }}" class="ad-input" style="max-width:190px" onchange="this.form.submit()">
                @else
                    <select name="tahun" class="ad-select" style="max-width:150px" onchange="this.form.submit()">
                        @forelse ($tahunTersedia as $th)
                            <option value="{{ $th }}" @selected((int) $th === (int) $tahun)>{{ $th }}</option>
                        @empty
                            <option value="{{ $tahun }}">{{ $tahun }}</option>
                        @endforelse
                    </select>
                @endif

                <button type="submit" class="ad-btn ad-btn--ghost">Terapkan</button>
            </form>
        </div>

        <div class="ad-card__body" style="padding-top:0">
            <div class="ad-stats" style="margin-bottom:0">
                <div class="ad-stat ad-stat--active">
                    <span class="ad-stat__icon ad-stat__icon--gold">@include('admin.partials.icon', ['name' => 'money'])</span>
                    <div class="ad-stat__label">Total Omzet</div>
                    <div class="ad-stat__value">{{ $rupiah($totalOmzet) }}</div>
                </div>
                <div class="ad-stat">
                    <span class="ad-stat__icon ad-stat__icon--blue">@include('admin.partials.icon', ['name' => 'receipt'])</span>
                    <div class="ad-stat__label">Jumlah Pesanan</div>
                    <div class="ad-stat__value">{{ $jumlahPesanan }}</div>
                </div>
                <div class="ad-stat">
                    <span class="ad-stat__icon ad-stat__icon--amber">@include('admin.partials.icon', ['name' => 'chart'])</span>
                    <div class="ad-stat__label">Rata-rata per Pesanan</div>
                    <div class="ad-stat__value">{{ $rupiah($rataRata) }}</div>
                </div>
                <div class="ad-stat">
                    <span class="ad-stat__icon ad-stat__icon--rose">@include('admin.partials.icon', ['name' => 'star'])</span>
                    <div class="ad-stat__label">Periode</div>
                    <div class="ad-stat__value" style="font-size:14px">{{ $label }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ad-card">
        <div class="ad-card__head">
            <h2 class="ad-section-title">Rincian Omzet {{ $periode === 'harian' ? 'per Pesanan' : ($periode === 'bulanan' ? 'per Hari' : 'per Bulan') }}</h2>
            <span class="ad-badge ad-badge--muted">{{ $label }}</span>
        </div>

        @if ($periode === 'harian')
            <div class="ad-table-wrap">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Pelanggan</th>
                            <th>Jam</th>
                            <th class="is-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pesanan as $p)
                            <tr>
                                <td class="is-strong">{{ $p->invoice?->no_invoice ?? '—' }}</td>
                                <td class="is-muted">{{ $p->pelanggan?->nama ?? '—' }}</td>
                                <td class="is-muted">{{ $p->tanggal_pesanan?->format('H:i') }}</td>
                                <td class="is-right is-strong">{{ $rupiah($p->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="ad-empty">
                                        <strong>Data tidak ditemukan</strong>
                                        Belum ada penjualan dengan pembayaran disetujui pada tanggal ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="ad-table-wrap">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>{{ $periode === 'bulanan' ? 'Tanggal' : 'Bulan' }}</th>
                            <th class="is-center">Jumlah Pesanan</th>
                            <th class="is-right">Omzet</th>
                            <th class="is-right">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($baris as $b)
                            <tr>
                                <td class="is-strong">{{ $b['label'] }}</td>
                                <td class="is-center">{{ $b['jumlah'] }}</td>
                                <td class="is-right">{{ $rupiah($b['total']) }}</td>
                                <td class="is-right is-muted">{{ $totalOmzet > 0 ? number_format($b['total'] / $totalOmzet * 100, 1, ',', '.') : '0' }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="ad-empty">
                                        <strong>Data tidak ditemukan</strong>
                                        Belum ada penjualan dengan pembayaran disetujui pada periode ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($baris) > 0)
                        <tfoot>
                            <tr>
                                <td class="is-strong">Total</td>
                                <td class="is-center is-strong">{{ $jumlahPesanan }}</td>
                                <td class="is-right is-strong">{{ $rupiah($totalOmzet) }}</td>
                                <td class="is-right is-strong">100%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif
    </section>
@endsection