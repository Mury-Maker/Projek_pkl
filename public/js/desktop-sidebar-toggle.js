// public/js/desktop-sidebar-toggle.js

document.addEventListener('DOMContentLoaded', () => {
    const desktopSidebarToggle = document.getElementById('desktop-sidebar-toggle');
    const sidebarElement = document.getElementById('docs-sidebar'); // MENGGUNAKAN ID YANG DITAMBAHKAN PADA ASIDE

    if (desktopSidebarToggle && sidebarElement) {
        desktopSidebarToggle.addEventListener('click', () => {
            // Toggle class 'collapsed-desktop' pada elemen sidebar itu sendiri
            sidebarElement.classList.toggle('collapsed-desktop');

            // Toggle kelas 'sidebar-collapsed' pada elemen body
            // Kelas ini akan digunakan oleh CSS untuk menggeser konten utama (header dan main)
            document.body.classList.toggle('sidebar-collapsed');

            // Sesuaikan ikon tombol toggle desktop
            const icon = desktopSidebarToggle.querySelector('i');
            if (icon) {
                if (sidebarElement.classList.contains('collapsed-desktop')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-bars'); // Mengubah ikon menjadi panah kanan
                    icon.title = 'Perluas Sidebar'; // Mengubah judul tooltip
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-bars'); // Mengubah ikon kembali menjadi bar
                    icon.title = 'Sembunyikan Sidebar'; // Mengubah judul tooltip
                }
            }

            // Opsional: Tutup semua submenu saat sidebar desktop dilipat
            // Ini akan memanggil fungsi yang didefinisikan di mobile-sidebar.js (jika ada)
            // yang dapat menangani penutupan submenu.
            if (sidebarElement.classList.contains('collapsed-desktop')) {
                const allOpenSubmenus = sidebarElement.querySelectorAll('.submenu-container.open');
                allOpenSubmenus.forEach(openSubmenu => {
                    openSubmenu.classList.remove('open');
                    const relatedTrigger = openSubmenu.previousElementSibling.querySelector('[data-toggle^="submenu-"]');
                    if (relatedTrigger) {
                        relatedTrigger.setAttribute('aria-expanded', 'false');
                        relatedTrigger.querySelector('.menu-arrow-icon i')?.classList.remove('open');
                    }
                });
            }
        });
    }
});
