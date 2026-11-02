@php
    use App\Support\StatusPesanan;
@endphp
<span class="ad-badge ad-badge--{{ StatusPesanan::badge($status) }}">{{ StatusPesanan::label($status) }}</span>