(function (window) {
    'use strict';

    if (typeof window.Swal === 'undefined') {
        return;
    }

    const defaults = {
        confirmButtonColor: '#940000',
    };

    function baseOptions(overrides) {
        return Object.assign({}, defaults, overrides || {});
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.WauminiAlert = {
        fire(options) {
            return window.Swal.fire(baseOptions(options));
        },

        success(message, options) {
            return this.fire(Object.assign({
                icon: 'success',
                title: options && options.title ? options.title : 'Success',
                text: message,
            }, options || {}));
        },

        error(message, options) {
            return this.fire(Object.assign({
                icon: 'error',
                title: options && options.title ? options.title : 'Error',
                text: message,
            }, options || {}));
        },

        info(message, options) {
            return this.fire(Object.assign({
                icon: 'info',
                title: options && options.title ? options.title : 'Notice',
                text: message,
            }, options || {}));
        },

        warning(message, options) {
            return this.fire(Object.assign({
                icon: 'warning',
                title: options && options.title ? options.title : 'Warning',
                text: message,
            }, options || {}));
        },

        credentials(title, email, password) {
            const html = '<div class="text-left">'
                + '<p class="mb-2">Save these credentials securely.</p>'
                + '<p class="mb-1"><strong>Login:</strong> <code>' + escapeHtml(email || '') + '</code></p>'
                + '<p class="mb-0"><strong>Password:</strong> <code>' + escapeHtml(password || '') + '</code></p>'
                + '</div>';

            return this.fire({
                icon: 'info',
                title: title || 'Account credentials',
                html: html,
                confirmButtonText: 'OK',
            });
        },

        fromFlashItem(item) {
            if (!item || !item.type) {
                return Promise.resolve();
            }

            if (item.type === 'credentials') {
                return this.credentials(item.title, item.email, item.password);
            }

            const options = {
                title: item.title || undefined,
            };

            if (item.html) {
                options.html = item.html;
            } else {
                options.text = item.message || '';
            }

            const map = {
                success: 'success',
                error: 'error',
                info: 'info',
                warning: 'warning',
            };

            return this.fire(Object.assign({
                icon: map[item.type] || 'info',
            }, options));
        },
    };
})(window);
