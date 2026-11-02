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
        <!-- Panel kiri: Brand visual dengan foto boutique & logo emas resmi Gavin -->
        <aside class="auth__brand" role="img" aria-label="Interior Gavin Fashion Store">
            <div class="auth__brand-overlay">
                <div class="auth__brand-content">
                    <img src="{{ asset('images/logo-gavin.png') }}" alt="Gavin Fashion Store" class="auth__brand-logo">
                    <h1 class="auth__brand-title">Temukan gaya terbaikmu</h1>
                    <p class="auth__brand-subtitle">Pakaian pilihan untuk gaya sehari-hari.</p>
                </div>
            </div>
        </aside>

        <main class="auth__panel">
            <div class="auth__card">
                @yield('content')
            </div>
            <p class="auth__foot">&copy; {{ date('Y') }} Gavin Fashion Store. Hak cipta dilindungi.</p>
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
