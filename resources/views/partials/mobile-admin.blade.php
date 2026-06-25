@once
@push('scripts')
<script>
(function () {
    if (!document.body.classList.contains('app')) return;

    var mobileQuery = window.matchMedia('(max-width: 767.98px)');

    function closeSidebarOnMobile() {
        if (!mobileQuery.matches) return;
        var app = document.querySelector('.app');
        if (app && app.classList.contains('sidenav-toggled')) {
            app.classList.remove('sidenav-toggled');
        }
    }

    function isSidebarNavLink(link) {
        if (link.matches('[data-toggle="treeview"]')) {
            return false;
        }

        if (link.classList.contains('treeview-item')) {
            return true;
        }

        if (link.classList.contains('app-menu__item')) {
            var href = (link.getAttribute('href') || '').trim();
            return href !== '' && href !== '#';
        }

        return false;
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.app-sidebar a');
        if (!link || !isSidebarNavLink(link)) return;
        closeSidebarOnMobile();
    });
})();
</script>
@endpush
@endonce
