document.addEventListener('DOMContentLoaded', () => {
    // Logic for sidebar dropdown (general user)
    const sidebar = document.getElementById('sidebar-navigation');

    if (sidebar) {
        sidebar.addEventListener('click', (e) => {
            const trigger = e.target.closest('.menu-arrow-icon');
            if (trigger) {
                e.preventDefault();

                const submenuId = trigger.dataset.toggle;
                const submenu = document.getElementById(submenuId);
                const icon = trigger.querySelector('i');

                if (submenu) {
                    const isCurrentlyOpen = submenu.classList.contains('open');

                    const allOpenSubmenus = sidebar.querySelectorAll('.submenu-container.open');
                    allOpenSubmenus.forEach(openSubmenu => {
                        let isAncestorOfSelectedItem = false;
                        let tempParent = submenu;
                        while (tempParent && tempParent !== sidebar) {
                            if (tempParent === openSubmenu) {
                                isAncestorOfSelectedItem = true;
                                break;
                            }
                            tempParent = tempParent.parentElement.closest('.submenu-container');
                        }
                        if (!isAncestorOfSelectedItem) {
                            openSubmenu.classList.remove('open');
                            const relatedTrigger = sidebar.querySelector(`[data-toggle="${openSubmenu.id}"]`);
                            if (relatedTrigger) {
                                relatedTrigger.setAttribute('aria-expanded', 'false');
                                relatedTrigger.querySelector('i')?.classList.remove('open');
                            }
                        }
                    });

                    submenu.classList.toggle('open');
                    trigger.setAttribute('aria-expanded', isCurrentlyOpen ? 'false' : 'true');
                    if (icon) {
                        icon.classList.toggle('open', !isCurrentlyOpen);
                    }
                }
            }
        });
    }

    window.initSidebarDropdown = () => {
        const sidebarElement = document.getElementById('sidebar-navigation');
        if (!sidebarElement) return;

        const openActiveMenuParents = () => {
            const activeItemElement = sidebarElement.querySelector('.bg-blue-100');

            if (activeItemElement) {
                let currentElement = activeItemElement;
                while (currentElement && currentElement !== sidebarElement) {
                    if (currentElement.classList.contains('submenu-container')) {
                        currentElement.classList.add('open');
                        const triggerButton = sidebarElement.querySelector(`[data-toggle="${currentElement.id}"]`);
                        if (triggerButton) {
                            const icon = triggerButton.querySelector('i');
                            if (icon) {
                                icon.classList.add('open');
                                triggerButton.setAttribute('aria-expanded', 'true');
                            }
                        }
                    }
                    currentElement = currentElement.parentElement;
                }
            }
        };

        openActiveMenuParents();
    };

    initSidebarDropdown();

    // Logic for Header Category Dropdown
    const categoryDropdownBtn = document.getElementById('category-dropdown-btn');
    const categoryDropdownText = document.getElementById('category-button-text');
    const categoryDropdownMenu = document.getElementById('category-dropdown-menu');

    const categoryDropdownBtnMobile = document.getElementById('category-dropdown-btn-mobile');
    const categoryDropdownTextMobile = document.getElementById('category-button-text-mobile');
    const categoryDropdownMenuMobile = document.getElementById('category-dropdown-menu-mobile');

    const currentCategoryFromBlade = initialBladeData.currentCategory; // Access from global data
    
    if (categoryDropdownBtn && categoryDropdownMenu) {
        categoryDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            categoryDropdownMenu.classList.toggle('open');
            const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (categoryDropdownMenu.classList.contains('open')) {
                chevronIcon.classList.remove('fa-chevron-down');
                chevronIcon.classList.add('fa-chevron-up');
            } else {
                chevronIcon.classList.remove('fa-chevron-up');
                chevronIcon.classList.add('fa-chevron-down');
            }
        });

        document.addEventListener('click', (event) => {
            if (!categoryDropdownBtn.contains(event.target) && !categoryDropdownMenu.contains(event.target)) {
                categoryDropdownMenu.classList.remove('open');
                const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    chevronIcon.classList.remove('fa-chevron-up');
                    chevronIcon.classList.add('fa-chevron-down');
                }
            }
        });

        categoryDropdownMenu.querySelectorAll('a').forEach(item => {
            item.addEventListener('click', (e) => {
                const href = item.getAttribute('href');
                const url = new URL(href);
                const newCategoryKey = url.searchParams.get('category');

                if (newCategoryKey) {
                    updateCategoryButtonText(newCategoryKey, categoryDropdownText);
                }
                categoryDropdownMenu.classList.remove('open');
                const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    chevronIcon.classList.remove('fa-chevron-up');
                    chevronIcon.classList.add('fa-chevron-down');
                }
            });
        });
    }
    
    if (categoryDropdownBtnMobile && categoryDropdownMenuMobile) {
        categoryDropdownBtnMobile.addEventListener('click', (e) => {
            e.stopPropagation();
            categoryDropdownMenuMobile.classList.toggle('open');
            const chevronIcon = categoryDropdownBtnMobile.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (categoryDropdownMenuMobile.classList.contains('open')) {
                chevronIcon.classList.remove('fa-chevron-down');
                chevronIcon.classList.add('fa-chevron-up');
            } else {
                chevronIcon.classList.remove('fa-chevron-up');
                chevronIcon.classList.add('fa-chevron-down');
            }
        });

        document.addEventListener('click', (event) => {
            if (!categoryDropdownBtnMobile.contains(event.target) && !categoryDropdownMenuMobile.contains(event.target)) {
                categoryDropdownMenuMobile.classList.remove('open');
                const chevronIcon = categoryDropdownBtnMobile.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    chevronIcon.classList.remove('fa-chevron-up');
                    chevronIcon.classList.add('fa-chevron-down');
                }
            }
        });

        categoryDropdownMenuMobile.querySelectorAll('a').forEach(item => {
            item.addEventListener('click', (e) => {
                const href = item.getAttribute('href');
                const url = new URL(href);
                const newCategoryKey = url.searchParams.get('category');

                if (newCategoryKey) {
                    updateCategoryButtonText(newCategoryKey, categoryDropdownTextMobile);
                }
                categoryDropdownMenuMobile.classList.remove('open');
                const chevronIcon = categoryDropdownBtnMobile.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    chevronIcon.classList.remove('fa-chevron-up');
                    chevronIcon.classList.add('fa-chevron-down');
                }
            });
        });
    }
});