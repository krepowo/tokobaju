<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel Penjual') &middot; Gavin Fashion Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="ad-body">
    <div class="ad-shell">
        @include('admin.partials.sidebar', ['active' => trim($__env->yieldContent('active'))])

        <div class="ad-main">
            @include('admin.partials.topbar', [
                'crumb' => trim($__env->yieldContent('crumb')),
                'judul' => trim($__env->yieldContent('crumb_current')),
            ])

            <div class="ad-body-area">
                @include('admin.partials.flash')
                @yield('content')
            </div>
        </div>
    </div>

    @yield('extra')

    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>