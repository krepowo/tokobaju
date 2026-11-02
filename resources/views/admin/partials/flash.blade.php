@if (session('success'))
    <div class="ad-alert ad-alert--success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="ad-alert ad-alert--error">
        <div>
            <strong>{{ $errors->count() > 1 ? 'Periksa kembali isian berikut:' : 'Periksa kembali isian berikut:' }}</strong>
            <ul>
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif