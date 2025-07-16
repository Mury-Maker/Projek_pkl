document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebarElement = document.querySelector('aside');
    const backdrop = document.getElementById('sidebar-backdrop');

    const toggleSidebar = () => {
        sidebarElement.classList.toggle('show');
        backdrop.classList.toggle('show');
    };

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleSidebar);
    }
    if (backdrop) {
        backdrop.addEventListener('click', toggleSidebar);
    }
});