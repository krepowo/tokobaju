<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') &middot; Gavin Fashion Store</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="site-body">
    @include('partials.header', ['active' => trim($__env->yieldContent('active'))])

    <main class="site-main {{ trim($__env->yieldContent('main_class')) }}">
        @if (session('success'))
            <div class="alert alert--success" style="margin-bottom:22px">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
