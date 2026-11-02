@php
    $crumb = $crumb ?? '';
    $judul = $judul ?? '';
    $inisial = strtoupper(\Illuminate\Support\Str::of(auth()->user()?->nama ?? 'Admin')->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
@endphp
<header class="ad-top">
    <div class="ad-crumb">
        <span>Admin</span>
        <span class="ad-crumb__sep">›</span>
        @if ($crumb !== '')
            <span>{{ $crumb }}</span>
            <span class="ad-crumb__sep">›</span>
        @endif
        <span class="ad-crumb__current">{{ $judul !== '' ? $judul : 'Dashboard' }}</span>
    </div>

    <div class="ad-top__pill">
        <span class="ad-top__avatar">{{ $inisial }}</span>
        <span>Admin</span>
    </div>
</header>