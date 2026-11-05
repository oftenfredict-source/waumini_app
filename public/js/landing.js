(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function setActiveSection(id) {
        var body = document.body;
        if (!id || !body) return;

        if (body._x_dataStack && body._x_dataStack[0]) {
            body._x_dataStack[0].activeSection = id;
            return;
        }

        body.dispatchEvent(new CustomEvent('landing-section', { detail: { id: id } }));
    }

    function initReveals() {
        var nodes = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
        if (!nodes.length) return;

        // Enable hide-until-visible only after JS is confirmed running.
        document.documentElement.classList.add('js-reveal');

        if (reduceMotion || !('IntersectionObserver' in window)) {
            nodes.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var el = entry.target;
                    var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
                    window.setTimeout(function () {
                        el.classList.add('is-visible');
                    }, delay);
                    observer.unobserve(el);
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -6% 0px' }
        );

        nodes.forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight * 0.92) {
                var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
                window.setTimeout(function () {
                    el.classList.add('is-visible');
                }, delay);
                return;
            }
            observer.observe(el);
        });
    }

    function initSectionSpy() {
        var sections = Array.prototype.slice.call(document.querySelectorAll('[data-section]'));
        if (!sections.length || !('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(
            function (entries) {
                var visible = entries
                    .filter(function (entry) { return entry.isIntersecting; })
                    .sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });

                if (!visible.length) return;
                setActiveSection(visible[0].target.getAttribute('data-section'));
            },
            { threshold: [0.2, 0.4, 0.55], rootMargin: '-18% 0px -42% 0px' }
        );

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }

    function boot() {
        initReveals();
        initSectionSpy();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
