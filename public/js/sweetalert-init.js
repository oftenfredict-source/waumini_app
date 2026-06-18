(function () {
    'use strict';

    function brandColor() {
        return document.querySelector('meta[name="brand-color"]')?.content || '#940000';
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showFlash(item) {
        if (!item || typeof Swal === 'undefined') {
            return Promise.resolve();
        }

        if (item.type === 'credentials') {
            const email = escapeHtml(item.email || '');
            const password = escapeHtml(item.password || '');
            const copyText = 'Login email: ' + (item.email || '') + '\nPassword: ' + (item.password || '');

            return Swal.fire({
                icon: 'warning',
                title: item.title || 'Save these credentials now',
                html:
                    '<p class="mb-3 text-muted">This password is shown only once. Copy and share it securely.</p>' +
                    '<table class="table table-sm table-borderless text-left mb-0">' +
                    '<tr><th class="pr-3">Email</th><td><code>' + email + '</code></td></tr>' +
                    '<tr><th class="pr-3">Password</th><td><code>' + password + '</code></td></tr>' +
                    '</table>',
                confirmButtonText: 'Copy credentials',
                showCancelButton: true,
                cancelButtonText: 'Close',
                confirmButtonColor: brandColor(),
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then(function (result) {
                if (!result.isConfirmed || !navigator.clipboard) {
                    return;
                }

                return navigator.clipboard.writeText(copyText).then(function () {
                    return Swal.fire({
                        icon: 'success',
                        title: 'Copied',
                        timer: 1200,
                        showConfirmButton: false,
                    });
                }).catch(function () {
                    return Swal.fire({
                        icon: 'error',
                        title: 'Copy failed',
                        text: 'Please copy the credentials manually.',
                    });
                });
            });
        }

        const iconMap = {
            success: 'success',
            error: 'error',
            warning: 'warning',
            info: 'info',
        };

        const options = {
            icon: iconMap[item.type] || 'info',
            title: item.title || (item.type === 'success' ? 'Success' : item.type === 'error' ? 'Error' : 'Notice'),
            confirmButtonColor: brandColor(),
        };

        if (item.html) {
            options.html = item.html;
        } else {
            options.text = item.message || '';
        }

        if (item.type === 'success') {
            options.timer = 3000;
            options.timerProgressBar = true;
            options.showConfirmButton = true;
        }

        return Swal.fire(options);
    }

    function initFlashMessages() {
        const el = document.getElementById('swal-flash-data');
        if (!el) {
            return;
        }

        let messages = [];
        try {
            messages = JSON.parse(el.textContent || '[]');
        } catch (error) {
            return;
        }

        if (!Array.isArray(messages) || messages.length === 0) {
            return;
        }

        messages.reduce(function (chain, item) {
            return chain.then(function () {
                return showFlash(item);
            });
        }, Promise.resolve());
    }

    function initFormConfirmations() {
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const message = form.getAttribute('data-swal-confirm');
            if (!message) {
                return;
            }

            if (form.dataset.swalConfirmed === '1') {
                delete form.dataset.swalConfirmed;
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const isDelete = form.hasAttribute('data-swal-delete');
            const title = form.getAttribute('data-swal-title') || (isDelete ? 'Are you sure?' : 'Confirm action');
            const confirmText = form.getAttribute('data-swal-confirm-text') || (isDelete ? 'Yes, proceed' : 'Yes, continue');

            Swal.fire({
                title: title,
                text: message,
                icon: isDelete ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: isDelete ? '#dc3545' : brandColor(),
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: form.getAttribute('data-swal-cancel-text') || 'Cancel',
                reverseButtons: true,
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                form.dataset.swalConfirmed = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        }, true);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initFlashMessages();
        initFormConfirmations();
    });
})();
