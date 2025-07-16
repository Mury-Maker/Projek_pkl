document.addEventListener('DOMContentLoaded', () => {
    // Check if the user is an admin by looking for elements only present for admins
    const isAdmin = document.body.querySelector('#menu-modal') !== null;

    if (!isAdmin) {
        return; // Stop execution if not admin
    }

    const menuModal = document.getElementById('menu-modal');
    const menuForm = document.getElementById('menu-form');
    const deleteConfirmModal = document.getElementById('delete-confirm-modal');
    const deleteConfirmMessage = document.getElementById('delete-confirm-message');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const categoryForm = document.getElementById('categoryForm');
    const categoryModal = document.getElementById('categoryModal');
    const categoryModalTitle = document.getElementById('categoryModalTitle');
    // const deleteContentBtn = document.getElementById('delete-content-btn'); // Moved to editor-logic if it's content specific

    let menuToDelete = null;
    const modalTitleElement = document.getElementById('modal-title');
    const formCategoryElement = document.getElementById('form_category');
    const currentCategory = formCategoryElement ? formCategoryElement.value : 'epesantren';

    const openMenuModal = async (mode, menuData = null, parentId = 0) => {
        if (!menuForm || !modalTitleElement) {
            showNotification('Elemen form menu tidak ditemukan.', 'error');
            console.error('Admin menu form elements not found in DOM.');
            return;
        }
        menuForm.reset();
        document.getElementById('form_menu_id').value = '';
        document.getElementById('form_method').value = mode === 'edit' ? 'PUT' : 'POST';

        const formMenuChildSelect = document.getElementById('form_menu_child');
        formMenuChildSelect.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>';

        const editingMenuId = mode === 'edit' && menuData ? menuData.menu_id : null;
        let parentApiUrl = `/api/navigasi/parents/${currentCategory}`;
        if (editingMenuId) {
            parentApiUrl += `?editing_menu_id=${editingMenuId}`;
        }

        try {
            const parents = await fetchAPI(parentApiUrl);
            parents.forEach(parent => {
                const option = document.createElement('option');
                option.value = parent.menu_id;
                option.textContent = parent.menu_nama;
                formMenuChildSelect.appendChild(option);
            });

            if (mode === 'create') {
                modalTitleElement.textContent = 'Tambah Menu Baru';
                formMenuChildSelect.value = parentId;
                document.getElementById('form_menu_status').checked = false;
            } else if (mode === 'edit' && menuData) {
                modalTitleElement.textContent = `Edit Menu: ${menuData.menu_nama}`;
                document.getElementById('form_menu_id').value = menuData.menu_id;
                document.getElementById('form_menu_nama').value = menuData.menu_nama;
                document.getElementById('form_menu_icon').value = menuData.menu_icon;
                formMenuChildSelect.value = menuData.menu_child;
                document.getElementById('form_menu_order').value = menuData.menu_order;
                document.getElementById('form_menu_status').checked = menuData.menu_status == 1;
            }
        } catch (error) {
            showNotification('Gagal memuat daftar parent menu.', 'error');
            console.error('Error loading parent menus:', error);
        }

        menuModal.classList.add('show');
    };
    
    const openDeleteConfirmModal = (menuId, menuNama) => {
        menuToDelete = { id: menuId, name: menuNama };
        if (deleteConfirmMessage) {
            deleteConfirmMessage.textContent = `Apakah Anda yakin ingin menghapus menu "${menuNama}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua sub-menu terkait.`;
        }
        if (deleteConfirmModal) {
            deleteConfirmModal.classList.add('show');
        }
    };

    const closeDeleteConfirmModal = () => {
        if (deleteConfirmModal) {
            deleteConfirmModal.classList.remove('show');
        }
        menuToDelete = null;
    };

    const closeMenuModalAdmin = () => menuModal.classList.remove('show');

    const refreshSidebar = async () => {
        const sidebarElement = document.getElementById('sidebar-navigation');
        if (!sidebarElement) {
            showNotification('Gagal memuat ulang sidebar: Elemen navigasi tidak ditemukan.', 'error');
            console.error('Sidebar navigation element not found.');
            return;
        }
        try {
            // Use initialBladeData.currentCategory for refresh
            const data = await fetchAPI(`/api/navigasi/all/${initialBladeData.currentCategory}`);
            sidebarElement.innerHTML = data.html;
            attachAdminEventListeners(); // Re-attach listeners after content update
            window.initSidebarDropdown(); // Re-initialize dropdown logic from category-dropdown.js
        } catch (error) {
            showNotification('Gagal memuat ulang sidebar.', 'error');
            console.error('Error reloading sidebar:', error);
        }
    };

    const attachAdminEventListeners = () => {
        console.log('Attaching admin event listeners...');

        const addParentMenuBtn = document.getElementById('add-parent-menu-btn');
        if (addParentMenuBtn) {
            addParentMenuBtn.addEventListener('click', () => openMenuModal('create', null, 0));
        }
        const cancelMenuFormBtn = document.getElementById('cancel-menu-form-btn');
        if (cancelMenuFormBtn) {
            cancelMenuFormBtn.addEventListener('click', closeMenuModalAdmin);
        }

        document.querySelectorAll('.edit-menu-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.stopPropagation();
                const menuId = e.currentTarget.dataset.menuId;
                try {
                    const menuData = await fetchAPI(`/api/navigasi/${menuId}`);
                    openMenuModal('edit', menuData);
                } catch (error) {
                    showNotification('Gagal memuat data menu untuk diedit.', 'error');
                    console.error('Error fetching menu data for edit:', error);
                }
            });
        });

        document.querySelectorAll('.delete-menu-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const menuId = e.currentTarget.dataset.menuId;
                const menuNama = e.currentTarget.dataset.menuNama;
                openDeleteConfirmModal(menuId, menuNama);
            });
        });

        document.querySelectorAll('.add-child-menu-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const parentId = e.currentTarget.dataset.parentId;
                openMenuModal('create', null, parentId);
            });
        });
    };
    
    // Category Form Submission
    if (categoryForm) {
        categoryForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const loadingNotif = showNotification('Memproses kategori...', 'loading');

            const categoryNameInput = document.getElementById('form_category_nama');
            const categoryName = categoryNameInput.value;
            const method = document.getElementById('form_category_method').value;
            
            let url = '/kategori'; // For POST (create)
            let httpMethod = 'POST';

            // currentCategory should be pulled from initialBladeData
            const currentCategoryForForm = initialBladeData.currentCategory;


            if (method === 'PUT') {
                url = `/kategori/${currentCategoryForForm}`; // Use the actual category slug being edited
                httpMethod = 'PUT';
            }

            try {
                const options = {
                    method: httpMethod,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ category: categoryName }),
                };

                if (method === 'PUT') {
                    options.headers['X-HTTP-Method-Override'] = 'PUT';
                }

                const data = await fetchAPI(url, options);
                
                hideNotification(loadingNotif);    
                if (data.success) {
                    showCentralSuccessPopup(data.success);
                    closeCategoryModal();
                    const newSlug = data.new_slug || categoryName.toLowerCase().replace(/\s+/g, '-');
                    window.location.href = `/docs/${newSlug}`;
                } else {
                    showNotification(data.message || 'Gagal memproses kategori.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                hideNotification(loadingNotif);    
                showNotification(error.message || 'Terjadi kesalahan saat memproses kategori.', 'error');
            }
        });
    }
    
    if (menuForm) {
        menuForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loadingNotif = showNotification('Menyimpan menu...', 'loading');

            const formData = new FormData(menuForm);
            const menuId = formData.get('menu_id');
            const method = document.getElementById('form_method').value;

            const dataToSend = {};
            formData.forEach((value, key) => {
                if (key === 'menu_status') {
                    dataToSend[key] = value === '1' ? 1 : 0;
                } else {
                    dataToSend[key] = value;
                }
            });

            const url = menuId ? `/api/navigasi/${menuId}` : '/api/navigasi';

            const options = {
                method: 'POST',
                body: JSON.stringify(dataToSend),
            };

            if (method === 'PUT') {
                options.headers = {
                    ...options.headers,
                    'X-HTTP-Method-Override': 'PUT'
                };
            }
            
            try {
                const data = await fetchAPI(url, options);
                
                hideNotification(loadingNotif);    
                showCentralSuccessPopup(data.success);
                
                closeMenuModalAdmin();
                refreshSidebar();
            } catch (error) {
                console.error('Error saving menu:', error);
                hideNotification(loadingNotif);    
                
                if (error.message) {
                    showNotification(`Gagal menyimpan: ${error.message}`, 'error');
                } else {
                    showNotification('Terjadi kesalahan tidak dikenal saat menyimpan menu.', 'error');
                }
            }
        });
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeDeleteConfirmModal);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async () => {
            if (menuToDelete) {
                const deleteLoadingNotif = showNotification('Menghapus menu...', 'loading');
                
                try {
                    const data = await fetchAPI(`/api/navigasi/${menuToDelete.id}`, { method: 'DELETE' });
                    
                    hideNotification(deleteLoadingNotif);    
                    showCentralSuccessPopup(data.success);
                    
                    closeDeleteConfirmModal();
                    refreshSidebar();
                } catch (error) {
                    hideNotification(deleteLoadingNotif);    
                    showNotification(`Gagal menghapus: ${error.message || 'Terjadi kesalahan'}`, 'error');
                    console.error('Error deleting menu:', error);
                    closeDeleteConfirmModal();
                }
            }
        });
    }
    
    // Moved to global-utils.js, exposed via window.openCategoryModal and window.closeCategoryModal
    // These functions should ideally be moved out of here or re-evaluated for access.
    // Making them global for now as they are called from Blade onclick.
    window.openCategoryModal = (mode = 'create', defaultName = '') => {
        if (!categoryForm || !categoryModalTitle) {
            showNotification('Form kategori tidak ditemukan!', 'error');
            return;
        }

        categoryForm.reset();
        document.getElementById('form_category_method').value = mode === 'edit' ? 'PUT' : 'POST';
        document.getElementById('form_category_nama').value = defaultName;

        categoryModalTitle.textContent = mode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori';
        categoryModal.classList.remove('hidden');
    };

    window.closeCategoryModal = () => {
        if (categoryModal) {
            categoryModal.classList.add('hidden');
        }
    };

    // Function for category delete confirmation (called via onclick)
    window.confirmDeleteCategory = (categorySlug, categoryName) => {
        console.log('confirmDeleteCategory function called for:', categoryName, 'with slug:', categorySlug);
        Swal.fire({
            title: 'Yakin ingin menghapus kategori?',
            text: `Anda akan menghapus kategori "${categoryName}" beserta semua menu dan konten di dalamnya. Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Kategori',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                console.log('SweetAlert2 confirmation: Deleted!');
                const loadingNotif = showNotification('Menghapus kategori...', 'loading');
                try {
                    const data = await fetchAPI(`/kategori/${categorySlug}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        }
                    });
                    hideNotification(loadingNotif);
                    if (data.success) {
                        showCentralSuccessPopup(data.success);
                        window.location.href = `/docs/epesantren`;
                    } else {
                        showNotification(data.message || 'Gagal menghapus kategori.', 'error');
                    }
                } catch (error) {
                    console.error('Error deleting category (Fetch API):', error);
                    hideNotification(loadingNotif);
                    showNotification(error.message || 'Terjadi kesalahan saat menghapus kategori.', 'error');
                }
            }
        });
    };

    // Initial attachment of event listeners when the admin-modals.js is loaded
    attachAdminEventListeners();
});