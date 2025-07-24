// public/js/mobile-sidebar.js

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('docs-sidebar'); // Menggunakan ID yang baru ditambahkan
    const backdrop = document.getElementById('sidebar-backdrop');

    if (mobileMenuToggle && sidebar && backdrop) {
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });

        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }

    // Fungsi untuk inisialisasi dropdown submenu sidebar (tetap di sini karena terkait struktur sidebar)
    // Ini memastikan dropdown bekerja baik di mobile maupun desktop (saat tidak collapsed)
    window.initSidebarDropdown = () => {
        const submenuTriggers = document.querySelectorAll('[data-toggle^="submenu-"]');

        submenuTriggers.forEach(trigger => {
            // Hapus listener lama jika ada untuk mencegah duplikasi saat refresh sidebar
            trigger.removeEventListener('click', handleSubmenuToggle);
            // Tambahkan listener baru
            trigger.addEventListener('click', handleSubmenuToggle);
        });
    };

    function handleSubmenuToggle(event) {
        // Hanya memproses jika sidebar tidak dalam mode collapsed-desktop
        const sidebarElement = document.getElementById('docs-sidebar');
        // PASTIKAN desktop-sidebar-toggle.js sudah memuat kelas 'collapsed-desktop' dengan benar
        if (sidebarElement && sidebarElement.classList.contains('collapsed-desktop')) {
            // Jika sidebar dilipat di desktop, jangan buka submenu dengan klik
            return;
        }

        event.preventDefault();
        event.stopPropagation(); // Mencegah event menyebar ke parent link

        const submenuId = event.currentTarget.dataset.toggle;
        const submenu = document.getElementById(submenuId);
        const arrowIcon = event.currentTarget.querySelector('.menu-arrow-icon i');

        if (submenu) {
            // Tutup semua submenu lain yang terbuka pada level yang sama
            const parentLi = event.currentTarget.closest('li');
            if (parentLi) {
                const siblingSubmenus = parentLi.parentElement.querySelectorAll('.submenu-container.open');
                siblingSubmenus.forEach(siblingSubmenu => {
                    if (siblingSubmenu !== submenu) {
                        siblingSubmenu.classList.remove('open');
                        const siblingTrigger = siblingSubmenu.previousElementSibling.querySelector('[data-toggle^="submenu-"]');
                        if (siblingTrigger) {
                            siblingTrigger.setAttribute('aria-expanded', 'false');
                            siblingTrigger.querySelector('.menu-arrow-icon i')?.classList.remove('open');
                        }
                    }
                });
            }

            // Toggle submenu yang diklik
            const isOpen = submenu.classList.toggle('open');
            event.currentTarget.setAttribute('aria-expanded', isOpen);

            if (arrowIcon) {
                if (isOpen) {
                    arrowIcon.classList.add('open'); // Menambahkan kelas 'open' untuk rotasi panah
                } else {
                    arrowIcon.classList.remove('open');
                }
            }
        }
    }

    // Panggil initSidebarDropdown saat DOM siap dan setiap kali sidebar di-refresh (misal setelah CRUD admin)
    window.initSidebarDropdown();
});
