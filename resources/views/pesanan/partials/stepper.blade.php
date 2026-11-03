@php
    $currentStep = match($order->status) {
        'menunggu_pembayaran', 'pembayaran_ditolak' => 1,
        'menunggu_verifikasi' => 2,
        'diproses' => 3,
        'dikirim' => 4,
        'selesai' => 5,
        default => 0,
    };

    $stepsData = [
        1 => [
            'title' => 'Menunggu Pembayaran',
            'desc_pending' => 'Selesaikan transfer',
            'desc_active' => 'Selesaikan transfer',
            'desc_done' => 'Selesai Dibayar',
        ],
        2 => [
            'title' => 'Verifikasi',
            'desc_pending' => 'Pengecekan bukti',
            'desc_active' => 'Dalam Pengecekan',
            'desc_done' => 'Selesai Diverifikasi',
        ],
        3 => [
            'title' => $order->status === 'diproses' ? 'Sedang Diproses' : 'Diproses',
            'desc_pending' => 'Quality control',
            'desc_active' => 'Quality control',
            'desc_done' => 'Selesai Diproses',
        ],
        4 => [
            'title' => 'Dikirim',
            'desc_pending' => 'Dalam ekspedisi',
            'desc_active' => 'Dalam ekspedisi',
            'desc_done' => 'Selesai Dikirim',
        ],
        5 => [
            'title' => 'Selesai',
            'desc_pending' => 'Diterima pelanggan',
            'desc_active' => 'Diterima pelanggan',
            'desc_done' => 'Diterima pelanggan',
        ],
    ];
@endphp

@if ($order->status === 'dibatalkan')
    <div class="pesanan-stepper pesanan-stepper--cancelled">
        <div class="pesanan-stepper__cancelled-box">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <div>
                <strong>Pesanan Dibatalkan</strong>
                <span>Pesanan ini telah dibatalkan dan tidak dapat diproses lebih lanjut.</span>
            </div>
        </div>
    </div>
@else
    <section class="pesanan-stepper" aria-label="Alur Pemrosesan Pesanan">
        <div class="pesanan-stepper__header">
            <span class="pesanan-stepper__title">ALUR PEMROSESAN PESANAN</span>
            <span class="pesanan-stepper__badge">
                Langkah {{ $currentStep }} dari 5
            </span>
        </div>

        <div class="pesanan-stepper__track">
            @foreach ($stepsData as $num => $step)
                @php
                    $isDone = ($order->status === 'selesai') || ($num < $currentStep);
                    $isActive = ($order->status !== 'selesai') && ($num === $currentStep);

                    if ($isDone) {
                        $desc = $step['desc_done'];
                    } elseif ($isActive) {
                        $desc = $step['desc_active'];
                    } else {
                        $desc = $step['desc_pending'];
                    }
                @endphp

                <div class="pesanan-stepper__step {{ $isDone ? 'is-done' : '' }} {{ $isActive ? 'is-active' : '' }}">
                    <div class="pesanan-stepper__node-wrap">
                        @if ($num > 1)
                            <div class="pesanan-stepper__line pesanan-stepper__line--left {{ ($order->status === 'selesai') || ($num <= $currentStep) ? 'is-done' : '' }}"></div>
                        @endif

                        <div class="pesanan-stepper__node">
                            @if ($isDone)
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @else
                                <span>{{ $num }}</span>
                            @endif
                        </div>

                        @if ($num < 5)
                            <div class="pesanan-stepper__line pesanan-stepper__line--right {{ ($order->status === 'selesai') || ($num < $currentStep) ? 'is-done' : '' }}"></div>
                        @endif
                    </div>

                    <div class="pesanan-stepper__info">
                        <strong class="pesanan-stepper__step-title">{{ $step['title'] }}</strong>
                        <span class="pesanan-stepper__step-desc {{ $isActive ? 'is-active-desc' : '' }}">{{ $desc }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
