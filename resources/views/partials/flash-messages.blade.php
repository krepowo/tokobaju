@if (session('success') || session('error') || (isset($errors) && $errors->any()))
<div class="floating-alerts-container" id="floatingAlertsContainer" aria-live="polite">
    @if (session('success'))
        <div class="alert alert--success alert--floating js-auto-alert" data-timeout="4000" role="alert">
            <div class="alert__content">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="alert__icon" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="alert__close-btn" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert--danger alert--error alert--floating js-auto-alert" data-timeout="7000" role="alert">
            <div class="alert__content">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="alert__icon" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="alert__close-btn" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert--danger alert--floating js-auto-alert" data-timeout="7000" role="alert">
            <div class="alert__content">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="alert__icon" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <ul class="alert__error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="alert__close-btn" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif
</div>

<script>
(function() {
    if (window.__flashAlertsInit) return;
    window.__flashAlertsInit = true;

    function dismissAlert(alertEl) {
        if (!alertEl || alertEl.classList.contains('is-dismissing')) return;
        alertEl.classList.add('is-dismissing');
        alertEl.style.opacity = '0';
        alertEl.style.transform = 'translateY(-12px) scale(0.98)';
        alertEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease, margin 0.4s ease, max-height 0.4s ease, padding 0.4s ease';
        setTimeout(function() {
            if (alertEl.parentNode) {
                alertEl.parentNode.removeChild(alertEl);
            }
        }, 400);
    }

    document.querySelectorAll('.js-auto-alert').forEach(function(alertEl) {
        var timeout = parseInt(alertEl.getAttribute('data-timeout') || '4000', 10);

        var timer = setTimeout(function() {
            dismissAlert(alertEl);
        }, timeout);

        var closeBtn = alertEl.querySelector('.alert__close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearTimeout(timer);
                dismissAlert(alertEl);
            });
        }
    });
})();
</script>
@endif

