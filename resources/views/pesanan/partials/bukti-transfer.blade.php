@if ($pembayaran && !in_array($order->status, ['menunggu_pembayaran', 'dibatalkan'], true))
    <div class="pesanan-proof-card">
        <div class="pesanan-proof-card__head">
            <span class="pesanan-proof-card__title">Bukti Pembayaran</span>
            @if ($pembayaran->status_verifikasi === 'disetujui')
                <span class="pesanan-badge-green">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Terverifikasi
                </span>
            @elseif ($pembayaran->status_verifikasi === 'ditolak')
                <span class="pesanan-badge-red">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    Ditolak
                </span>
            @else
                <span class="pesanan-badge-amber">
                    <i class="dot"></i>Sedang Diverifikasi
                </span>
            @endif
        </div>

        <div class="pesanan-proof-card__body">
            <div class="pesanan-proof-card__thumb-wrap js-open-lightbox" role="button" tabindex="0" title="Klik untuk memperbesar bukti transfer">
                <img src="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" alt="Bukti Transfer" class="pesanan-proof-card__thumb">
                <div class="pesanan-proof-card__overlay-btn">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>

            <div class="pesanan-proof-card__info">
                <span class="pesanan-proof-card__date">Diunggah: {{ $pembayaran->tanggal_upload?->format('d M Y, H:i') }} WIB</span>
                <button type="button" class="pesanan-proof-card__btn-view js-open-lightbox">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Lihat Bukti Transfer
                </button>
            </div>
        </div>
    </div>
@endif
