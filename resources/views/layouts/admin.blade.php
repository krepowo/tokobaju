<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &middot; Gavin Fashion Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <header class="admin-topbar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <span class="admin-brand__mark">G</span>
            <span class="admin-brand__text">
                <span class="admin-brand__name">Gavin Fashion Store</span>
                <span class="admin-brand__sub">Admin Seller</span>
            </span>
        </a>

        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <span>Admin</span>
            <span class="admin-breadcrumb__sep">&rsaquo;</span>
            <span class="admin-breadcrumb__now">@yield('breadcrumb', 'Dashboard')</span>
        </nav>

        <span class="admin-topbar__user">
            <span class="admin-topbar__avatar">{{ mb_strtoupper(mb_substr(auth()->user()->nama ?? 'A', 0, 1)) }}</span>
            {{ auth()->user()->isPenjual() ? 'Admin' : 'Pelanggan' }}
        </span>
    </header>

    <div class="admin-shell">
        @include('admin.partials.sidebar', [
            'nav' => trim($__env->yieldContent('nav')),
            'badgeBayar' => $badgeBayar ?? $perluDiverifikasi ?? null,
        ])

        <main class="admin-content">
            @if (session('success'))
                <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="admin-flash admin-flash--error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
