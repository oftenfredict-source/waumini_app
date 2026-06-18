/*!
    * Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2023 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Check if handler is already attached (to avoid duplicate handlers)
        if (!sidebarToggle.hasAttribute('data-toggle-handler-attached')) {
            sidebarToggle.setAttribute('data-toggle-handler-attached', 'true');
            
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const layoutSidenav = document.getElementById('layoutSidenav');
                
                // Toggle on the main container
                if (layoutSidenav) {
                    layoutSidenav.classList.toggle('sb-sidenav-toggled');
                }
                
                // Also toggle on body as fallback
                document.body.classList.toggle('sb-sidenav-toggled');
                
                // Save state to localStorage (as string 'true' or 'false' to match layout handler)
                const isToggled = layoutSidenav ? layoutSidenav.classList.contains('sb-sidenav-toggled') : document.body.classList.contains('sb-sidenav-toggled');
                localStorage.setItem('sb-sidebar-toggle', isToggled ? 'true' : 'false');
                
                console.log('Sidebar toggled:', isToggled);
                
                return false;
            });
            
            // Restore sidebar state from localStorage
            // On mobile, always start with sidebar closed (remove toggled class)
            // On desktop, sb-sidenav-toggled means CLOSED, on mobile it means OPEN
            if (window.innerWidth <= 768) {
                const layoutSidenav = document.getElementById('layoutSidenav');
                if (layoutSidenav) {
                    layoutSidenav.classList.remove('sb-sidenav-toggled');
                }
                document.body.classList.remove('sb-sidenav-toggled');
                localStorage.setItem('sb-sidebar-toggle', 'false');
            } else {
                // On desktop, restore saved state (true = closed, false = open)
                const savedState = localStorage.getItem('sb-sidebar-toggle');
                if (savedState === 'true') {
                    const layoutSidenav = document.getElementById('layoutSidenav');
                    if (layoutSidenav) {
                        layoutSidenav.classList.add('sb-sidenav-toggled');
                    }
                    document.body.classList.add('sb-sidenav-toggled');
                }
            }
        }
    }

});
