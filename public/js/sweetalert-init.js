(function () {
    'use strict';

    function runFlashMessages() {
        const dataEl = document.getElementById('swal-flash-data');

        if (!dataEl || typeof window.Swal === 'undefined') {
            return;
        }

        let messages = [];

        try {
            messages = JSON.parse(dataEl.textContent || '[]');
        } catch (error) {
            console.error('Invalid SweetAlert flash data.', error);
            return;
        }

        if (!Array.isArray(messages) || messages.length === 0) {
            return;
        }

        const show = window.WauminiAlert && typeof window.WauminiAlert.fromFlashItem === 'function'
            ? function (item) { return window.WauminiAlert.fromFlashItem(item); }
            : function (item) {
                return window.Swal.fire({
                    icon: item.type === 'credentials' ? 'info' : (item.type || 'info'),
                    title: item.title || (item.type === 'error' ? 'Error' : 'Notice'),
                    text: item.message || undefined,
                    html: item.html || undefined,
                    confirmButtonColor: '#940000',
                });
            };

        messages.reduce(function (chain, item) {
            return chain.then(function () {
                return show(item);
            });
        }, Promise.resolve());
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runFlashMessages);
    } else {
        runFlashMessages();
    }
})();
