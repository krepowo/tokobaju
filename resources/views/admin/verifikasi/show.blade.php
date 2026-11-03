@extends('layouts.admin')

@section('title', 'Detail Verifikasi Pembayaran')
@section('nav', 'bayar')
@section('breadcrumb', 'Verifikasi Bayar')

@section('content')
    @php
        $pesanan = $pembayaran->pesanan;
        $invoice = $pesanan->invoice;
        $pelanggan = $pesanan->user;
        $subtotal = $pesanan->detailPesanan->sum('subtotal');

        $bukti = $pembayaran->bukti_transfer;
        $urlBukti = null;
        $adaBukti = false;

        if ($bukti) {
            if (str_starts_with($bukti, 'images/')) {
                $adaBukti = file_exists(public_path($bukti));
                $urlBukti = asset($bukti);
            } elseif (str_starts_with($bukti, 'storage/')) {
                $adaBukti = file_exists(public_path($bukti)) || \Illuminate\Support\Facades\Storage::disk('public')->exists(substr($bukti, 8));
                $urlBukti = asset($bukti);
            } else {
                $adaBukti = \Illuminate\Support\Facades\Storage::disk('public')->exists($bukti) || file_exists(public_path('storage/' . $bukti)) || file_exists(public_path($bukti));
                $urlBukti = asset('storage/' . $bukti);
            }
        }
        $sudahDiproses = $pembayaran->status_verifikasi !== 'menunggu';

        $labelStatus = [
            'menunggu' => 'Menunggu Verifikasi',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ][$pembayaran->status_verifikasi] ?? ucfirst($pembayaran->status_verifikasi);
    @endphp

    <div class="invoice-actions">
        <a href="{{ route('admin.verifikasi.index') }}" class="btn-ghost">
            <svg viewBox="0 0 24 24"><path d="M15 5.5 8.5 12l6.5 6.5"/></svg>
            Kembali
        </a>

        <span class="verif-kode">
            Kode verifikasi <b class="mono">{{ strtoupper(substr($pembayaran->id_pembayaran, 0, 8)) }}</b>
        </span>
    </div>

    <div class="invoice-grid">
        <div class="verif-kiri">
            <section class="panel verif-bukti">
                <div class="panel__head">
                    <h2>Pratinjau Bukti Bayar</h2>
                    @if ($adaBukti)
                        <a href="{{ $urlBukti }}" target="_blank" rel="noopener" class="link-detail">
                            <svg viewBox="0 0 24 24"><path d="M14 4h6v6"/><path d="M20 4 10.5 13.5"/><path d="M18.5 14.5V20H4V5.5h5.5"/></svg>
                            Perbesar
                        </a>
                    @endif
                </div>

                @if ($adaBukti)
                    <a href="{{ $urlBukti }}" target="_blank" rel="noopener" class="verif-bukti__frame">
                        <img src="{{ $urlBukti }}" alt="Bukti transfer {{ $invoice?->no_invoice }}">
                    </a>
                @else
                    <div class="verif-bukti__kosong">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4.5" width="18" height="15" rx="2"/><circle cx="8.5" cy="10" r="1.6"/><path d="m4 17 5-4.5 3.5 3 3-2.5L20 17"/></svg>
                        <strong>Bukti transfer belum tersedia</strong>
                        <span>Berkas yang tersimpan: <code>{{ $bukti ?: '—' }}</code></span>
                    </div>
                @endif
            </section>

            <section class="panel">
                <div class="panel__head"><h2>Detail Pembayaran</h2></div>

                <dl class="detail-list">
                    <dt>Rekening Tujuan</dt>
                    <dd>{{ $invoice?->rekening_tujuan ?? '—' }}</dd>

                    <dt>Waktu Unggah</dt>
                    <dd>{{ $pembayaran->tanggal_upload?->format('d M Y, H:i') }} WIB</dd>

                    <dt>Status Verifikasi</dt>
                    <dd>
                        <span class="badge badge--{{ $pembayaran->status_verifikasi === 'menunggu' ? 'menunggu_verifikasi' : ($pembayaran->status_verifikasi === 'disetujui' ? 'selesai' : 'pembayaran_ditolak') }}">
                            {{ $labelStatus }}
                        </span>
                    </dd>

                    <dt>Waktu Verifikasi</dt>
                    <dd>{{ $sudahDiproses ? $pembayaran->updated_at?->format('d M Y, H:i').' WIB' : '—' }}</dd>

                    @if ($pembayaran->alasan_tolak)
                        <dt>Alasan Ditolak</dt>
                        <dd>{{ $pembayaran->alasan_tolak }}</dd>
                    @endif
                </dl>
            </section>
        </div>

        <div class="verif-kanan">
            <section class="panel">
                <div class="panel__head">
                    <h2 class="mono">{{ $invoice?->no_invoice ?? 'Tanpa invoice' }}</h2>
                    <span class="badge badge--{{ $pesanan->status }}">{{ $pesanan->statusLabel() }}</span>
                </div>

                <p class="verif-meta">
                    Dipesan {{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d M Y') }}
                    pukul {{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('H:i') }} WIB
                </p>

                <dl class="detail-list detail-list--rapat">
                    <dt>Nama Pelanggan</dt>
                    <dd>{{ $pelanggan?->nama ?? '—' }}</dd>

                    <dt>Nomor WhatsApp</dt>
                    <dd class="mono">{{ $pelanggan?->no_telepon ?? '—' }}</dd>

                    <dt>Email</dt>
                    <dd>{{ $pelanggan?->email ?? '—' }}</dd>

                    <dt>Alamat Kirim</dt>
                    <dd class="detail-list__alamat">{{ \App\Support\Alamat::tanpaNama($pesanan->alamat_tujuan) }}</dd>

                    <dt>Jasa Pengiriman</dt>
                    <dd>{{ $pesanan->jasa?->nama_jasa ?? '—' }} &middot; {{ $pesanan->pulau_tujuan }}</dd>
                </dl>
            </section>

            <section class="panel">
                <div class="panel__head">
                    <h2>Rincian Item ({{ $pesanan->detailPesanan->count() }})</h2>
                </div>

                <ul class="item-list">
                    @foreach ($pesanan->detailPesanan as $detail)
                        <li>
                            <span>{{ $detail->ukuran?->produk?->nama_produk ?? 'Produk dihapus' }}</span>
                            <small>{{ $detail->ukuran?->warna ?? '—' }} &middot; {{ $detail->ukuran?->ukuran ?? '—' }} &middot; {{ $detail->jumlah }} pcs</small>
                            <strong>Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</strong>
                        </li>
                    @endforeach
                </ul>

                <div class="invoice__totals verif-total">
                    <div><span>Subtotal Produk</span><span>Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span></div>
                    <div><span>Ongkos Kirim</span><span>Rp {{ number_format((float) $pesanan->ongkir, 0, ',', '.') }}</span></div>
                    <div class="invoice__total"><span>Total Tagihan</span><span>Rp {{ number_format((float) $pesanan->total, 0, ',', '.') }}</span></div>
                </div>
            </section>

            <section class="panel verif-aksi">
                @if ($sudahDiproses)
                    <p class="verif-aksi__selesai">
                        Pembayaran ini sudah <strong>{{ $pembayaran->status_verifikasi }}</strong>
                        @if ($pembayaran->verifikator)
                            oleh {{ $pembayaran->verifikator->nama }}
                        @endif
                        pada {{ $pembayaran->updated_at?->format('d/m/Y H:i') }}.
                    </p>
                @else
                    <div class="verif-aksi__baris">
                        <button type="button" class="btn-ghost btn-tolak" data-dialog-open="tolak-{{ $pembayaran->id_pembayaran }}">
                            Tolak
                        </button>

                        <form method="POST" action="{{ route('admin.verifikasi.setujui', $pembayaran) }}">
                            @csrf
                            <button type="submit" class="btn-gold btn-setujui">Setujui Pembayaran</button>
                        </form>
                    </div>

                    <p class="verif-aksi__hint">
                        Menyetujui akan mengubah status pesanan menjadi <strong>Diproses</strong> dan mengirimkannya ke halaman Proses Pengiriman.
                    </p>
                @endif
            </section>
        </div>
    </div>

    {{-- Dialog alasan penolakan --}}
    <dialog class="dialog dialog--sm" id="tolak-{{ $pembayaran->id_pembayaran }}">
        <form method="POST" action="{{ route('admin.verifikasi.tolak', $pembayaran) }}">
            @csrf
            <header class="dialog__head">
                <h3>Tolak Pembayaran</h3>
                <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup">
                    <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </header>

            <div class="dialog__body">
                <p class="dialog__teks">
                    Alasan penolakan akan ditampilkan kepada pelanggan pada halaman pesanan mereka.
                </p>

                <div class="field">
                    <label for="alasan-{{ $pembayaran->id_pembayaran }}">Alasan Penolakan <span class="req">*</span></label>
                    <textarea id="alasan-{{ $pembayaran->id_pembayaran }}" name="alasan_tolak" rows="3" maxlength="255" required
                              placeholder="Contoh: Nominal transfer tidak sesuai dengan total tagihan."></textarea>
                </div>
            </div>

            <footer class="dialog__foot">
                <button type="button" class="btn-ghost" data-dialog-close>Batal</button>
                <button type="submit" class="btn-dark btn-tolak">Tolak Pembayaran</button>
            </footer>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('[data-dialog-open]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var dialog = document.getElementById(trigger.dataset.dialogOpen);
                    if (dialog) dialog.showModal();
                });
            });

            document.querySelectorAll('dialog.dialog').forEach(function (dialog) {
                dialog.querySelectorAll('[data-dialog-close]').forEach(function (button) {
                    button.addEventListener('click', function () { dialog.close(); });
                });
                dialog.addEventListener('click', function (event) {
                    if (event.target === dialog) dialog.close();
                });
            });
        })();
    </script>
@endpush
