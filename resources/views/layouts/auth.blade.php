<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') &middot; Gavin Fashion Store</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">
    <div class="auth">
        {{-- Panel kiri: foto toko + overlay 80% + logo + tagline (sesuai Figma) --}}
        <aside class="auth__visual">
            <div class="auth__photo" role="img" aria-label="Interior Gavin Fashion Store"></div>
            <div class="auth__veil" aria-hidden="true"></div>
            <img class="auth__logo" src="{{ asset('images/figma/logo.png') }}" alt="Gavin Fashion Store">
            <p class="auth__tagline">Temukan gaya terbaikmu</p>
            <p class="auth__tagsub">Pakaian pilihan untuk gaya sehari-hari.</p>
        </aside>

        <main class="auth__panel">
            <div class="auth__card @yield('card_modifier')">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
