(function () {
    // Drawer buka/tutup: tombol dengan data-drawer="#target"
    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-drawer]');
        if (opener) {
            const target = document.querySelector(opener.dataset.drawer);
            if (target) {
                target.classList.add('is-open');
                const first = target.querySelector('input, select, textarea');
                if (first) setTimeout(() => first.focus(), 60);
            }
            return;
        }

        if (event.target.closest('[data-drawer-close]') || event.target.classList.contains('ad-drawer__backdrop')) {
            document.querySelectorAll('.ad-drawer.is-open').forEach((d) => d.classList.remove('is-open'));
            return;
        }

        // Modal <dialog>: tombol dengan data-modal="#id"
        const modalOpener = event.target.closest('[data-modal]');
        if (modalOpener) {
            const dialog = document.querySelector(modalOpener.dataset.modal);
            if (dialog && typeof dialog.showModal === 'function') dialog.showModal();
            return;
        }

        const modalCloser = event.target.closest('[data-modal-close]');
        if (modalCloser) {
            const dialog = modalCloser.closest('dialog');
            if (dialog) dialog.close();
            return;
        }

        // Sidebar mobile
        const sideToggle = event.target.closest('[data-sidebar-toggle]');
        if (sideToggle) {
            const side = document.querySelector('.ad-side');
            if (side) side.classList.toggle('is-open');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.ad-drawer.is-open').forEach((d) => d.classList.remove('is-open'));
        }
    });

    // Konfirmasi hapus: form dengan data-confirm="pesan"
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('form[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
})();