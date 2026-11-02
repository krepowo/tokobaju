@php
    $paths = [
        'grid' => '<rect x="3" y="3" width="6" height="6" rx="1.5"/><rect x="11" y="3" width="6" height="6" rx="1.5"/><rect x="3" y="11" width="6" height="6" rx="1.5"/><rect x="11" y="11" width="6" height="6" rx="1.5"/>',
        'box' => '<path d="M3 7.5 8 4.5l5 3v5l-5 3-5-3z"/><path d="M3 7.5 8 10.5l5-3"/><path d="M8 10.5V15.5"/>',
        'layers' => '<path d="M8 2.5 14 6l-6 3.5L2 6z"/><path d="M2 10l6 3.5L14 10"/>',
        'receipt' => '<path d="M4 2.5h8v12l-2-1.5-2 1.5-2-1.5-2 1.5z"/><path d="M6.5 6h3M6.5 9h3"/>',
        'truck' => '<path d="M2 5h7v7H2z"/><path d="M9 7.5h3l2 2.5v2H9z"/><circle cx="4.5" cy="13" r="1.2"/><circle cx="11.5" cy="13" r="1.2"/>',
        'check' => '<circle cx="8" cy="8" r="6"/><path d="M5.5 8.2l1.8 1.8 3.4-3.6"/>',
        'star' => '<path d="M8 2.6l1.7 3.5 3.8.5-2.8 2.7.7 3.8L8 11.2l-3.4 1.9.7-3.8L2.5 6.6l3.8-.5z"/>',
        'chart' => '<path d="M2.5 13.5h11"/><rect x="4" y="8" width="2" height="4"/><rect x="7" y="5.5" width="2" height="6.5"/><rect x="10" y="3" width="2" height="9"/>',
        'logout' => '<path d="M6.5 3.5H4a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h2.5"/><path d="M10 5.5 12.5 8 10 10.5"/><path d="M12.5 8H6.5"/>',
        'search' => '<circle cx="7.2" cy="7.2" r="4"/><path d="M10.2 10.2 13.5 13.5"/>',
        'chev' => '<path d="M6 3.5 10.5 8 6 12.5"/>',
        'plus' => '<path d="M8 3.5v9M3.5 8h9"/>',
        'edit' => '<path d="M11.5 2.5 13.5 4.5 6 12H4v-2z"/>',
        'trash' => '<path d="M3 5h10"/><path d="M6 5V3.5h4V5"/><path d="M4.5 5l.7 8h5.6l.7-8"/>',
        'eye' => '<path d="M1.5 8s2.4-4 6.5-4 6.5 4 6.5 4-2.4 4-6.5 4S1.5 8 1.5 8z"/><circle cx="8" cy="8" r="1.8"/>',
        'print' => '<path d="M4.5 6V2.5h7V6"/><rect x="2.5" y="6" width="11" height="5.5" rx="1"/><path d="M4.5 9.5h7v4h-7z"/>',
        'money' => '<rect x="2" y="4" width="12" height="8" rx="1.5"/><circle cx="8" cy="8" r="2"/><path d="M4.5 6.5h.01M11.5 9.5h.01"/>',
        'upload' => '<path d="M8 10.5V3.5"/><path d="M5 6.5 8 3.5l3 3"/><path d="M3 11.5v1h10v-1"/>',
        'alert' => '<path d="M8 2.5 14 13H2z"/><path d="M8 6.5v3.2"/><path d="M8 11.2h.01"/>',
    ];
    $nama = $nama ?? ($name ?? 'chev');
    $ukuran = $size ?? 16;
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $ukuran }}" height="{{ $ukuran }}" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">{!! $paths[$nama] ?? $paths['chev'] !!}</svg>