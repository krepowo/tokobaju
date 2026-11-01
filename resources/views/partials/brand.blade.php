@php
    $variant = $variant ?? 'dark';
    $stacked = $stacked ?? false;
@endphp

<span class="brand brand--{{ $variant }}{{ $stacked ? ' brand--stacked' : '' }}">
    <img src="{{ asset('images/logo-gavin.png') }}" alt="Gavin Fashion Store" class="brand__logo-img" width="38" height="38">
    <span class="brand__text-gold">
        <span class="brand__name">GAVIN FASHION STORE</span>
    </span>
</span>
