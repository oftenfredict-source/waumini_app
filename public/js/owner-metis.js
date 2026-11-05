(function () {
  'use strict';

  var MOBILE_BP = 992;

  function isMobile() {
    return window.innerWidth < MOBILE_BP;
  }

  function bridgeBootstrap4Attrs() {
    document.querySelectorAll('[data-toggle]').forEach(function (el) {
      if (!el.hasAttribute('data-bs-toggle')) {
        el.setAttribute('data-bs-toggle', el.getAttribute('data-toggle'));
      }
    });
    document.querySelectorAll('[data-target]').forEach(function (el) {
      if (!el.hasAttribute('data-bs-target')) {
        el.setAttribute('data-bs-target', el.getAttribute('data-target'));
      }
    });
    document.querySelectorAll('[data-dismiss]').forEach(function (el) {
      if (!el.hasAttribute('data-bs-dismiss')) {
        el.setAttribute('data-bs-dismiss', el.getAttribute('data-dismiss'));
      }
    });
    document.querySelectorAll('.dropdown-menu-right').forEach(function (el) {
      el.classList.add('dropdown-menu-end');
    });
  }

  function initSidebar() {
    var wrapper = document.getElementById('admin-wrapper');
    var sidebar = document.getElementById('admin-sidebar');
    var toggle = document.querySelector('[data-sidebar-toggle]');
    var backdrop = document.querySelector('.sidebar-backdrop');

    if (!wrapper || !sidebar || !toggle) {
      return;
    }

    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'sidebar-backdrop';
      wrapper.appendChild(backdrop);
    }

    function syncAria() {
      var expanded = isMobile()
        ? sidebar.classList.contains('show')
        : !wrapper.classList.contains('sidebar-collapsed');
      toggle.setAttribute('aria-expanded', String(expanded));
    }

    function closeMobile() {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
      document.body.style.overflow = '';
      syncAria();
    }

    function openMobile() {
      sidebar.classList.add('show');
      backdrop.classList.add('show');
      document.body.style.overflow = 'hidden';
      syncAria();
    }

    function toggleSidebar() {
      if (isMobile()) {
        if (sidebar.classList.contains('show')) {
          closeMobile();
        } else {
          openMobile();
        }
        return;
      }

      wrapper.classList.toggle('sidebar-collapsed');
      localStorage.setItem(
        'owner-sidebar-collapsed',
        wrapper.classList.contains('sidebar-collapsed') ? 'true' : 'false'
      );
      syncAria();
    }

    if (!isMobile() && localStorage.getItem('owner-sidebar-collapsed') === 'true') {
      wrapper.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', toggleSidebar);
    backdrop.addEventListener('click', closeMobile);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isMobile()) {
        closeMobile();
      }
    });

    window.addEventListener('resize', function () {
      if (!isMobile()) {
        closeMobile();
      }
      syncAria();
    });

    syncAria();
  }

  function initStickyActions() {
    var panel = document.querySelector('.owner-sticky-actions');
    if (!panel) {
      return;
    }

    var sentinel = document.createElement('div');
    sentinel.className = 'owner-sticky-sentinel';
    panel.parentNode.insertBefore(sentinel, panel);

    var headerOffset = function () {
      var header = document.querySelector('.admin-header');
      var h = header ? header.offsetHeight : 56;
      return h + 16;
    };

    var unpin = function () {
      panel.classList.remove('is-fixed');
      panel.style.top = '';
      panel.style.left = '';
      panel.style.width = '';
      sentinel.style.height = '0px';
    };

    var update = function () {
      if (window.innerWidth < 768) {
        unpin();
        return;
      }

      var offset = headerOffset();
      var sentinelTop = sentinel.getBoundingClientRect().top;

      if (sentinelTop <= offset) {
        if (!panel.classList.contains('is-fixed')) {
          var width = sentinel.offsetWidth || panel.offsetWidth;
          var left = sentinel.getBoundingClientRect().left;
          panel.style.width = width + 'px';
          panel.style.left = left + 'px';
          panel.style.top = offset + 'px';
          sentinel.style.height = panel.offsetHeight + 'px';
          panel.classList.add('is-fixed');
        } else {
          panel.style.left = sentinel.getBoundingClientRect().left + 'px';
          panel.style.width = (sentinel.offsetWidth || panel.offsetWidth) + 'px';
          panel.style.top = offset + 'px';
          sentinel.style.height = panel.offsetHeight + 'px';
        }
      } else {
        unpin();
      }
    };

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-sidebar-toggle]')) {
        window.setTimeout(update, 320);
      }
    });

    update();
  }

  document.addEventListener('DOMContentLoaded', function () {
    bridgeBootstrap4Attrs();
    initSidebar();
    initStickyActions();

    if (window.bootstrap && bootstrap.Tooltip) {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
      });
    }
  });
})();
