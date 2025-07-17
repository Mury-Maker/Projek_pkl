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

    let menuToDelete = null;
    const modalTitleElement = document.getElementById('modal-title');
    const currentCategory = window.initialBladeData && window.initialBladeData.currentCategory 
                            ? window.initialBladeData.currentCategory 
                            : 'epesantren'; // Fallback default

    const mainContentTitleElement = document.getElementById('main-content-title'); 

    const openMenuModal = async (mode, menuData = null, parentId = 0) => {
        if (!menuForm || !modalTitleElement) {
            showNotification('Elemen form menu tidak ditemukan.', 'error');
            console.error('Admin menu form elements not found in DOM.');
            return;
        }
        menuForm.reset();
        // Pastikan ID input hidden untuk menu_id adalah 'form_menu_id'
        document.getElementById('form_menu_id').value = ''; 
        // Pastikan ID input hidden untuk method adalah 'form_method'
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
                document.getElementById('form_menu_nama').value = '';
                document.getElementById('form_menu_icon').value = '';
                document.getElementById('form_menu_order').value = '0';
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
            const data = await fetchAPI(`/api/navigasi/all/${currentCategory}`);
            sidebarElement.innerHTML = data.html;
            attachAdminEventListeners();
            window.initSidebarDropdown();
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

            if (method === 'PUT') {
                url = `/kategori/${currentCategory}`;
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
                
                // closeMenuModalAdmin(); // Ini salah, seharusnya closeCategoryModal
                window.closeCategoryModal(); // Panggil fungsi penutup modal kategori

                if (data.success) {
                    showCentralSuccessPopup(data.success);
                    // Redirect ke kategori baru jika sukses
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
            console.log('Loading notification triggered:', loadingNotif);

            const formData = new FormData(menuForm);
            const method = document.getElementById('form_method').value; 
            const newMenuNama = formData.get('form_menu_nama'); 

            const dataToSend = {}; 
            formData.forEach((value, key) => {
                if (key === 'form_menu_status') {
                    dataToSend['menu_status'] = value === 'on' ? 1 : 0;
                } else if (key.startsWith('form_')) {
                    dataToSend[key.replace('form_', '')] = value;
                } else {
                    dataToSend[key] = value;
                }
            });
            if (!dataToSend.category) {
                dataToSend.category = currentCategory;
            }
            
            const menuIdFromData = dataToSend.menu_id; 
            const url = menuIdFromData ? `/api/navigasi/${menuIdFromData}` : '/api/navigasi'; 
            
            // ... (console.log debugging form submit Anda yang sebelumnya) ...

            const options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(dataToSend),
            };
    
            if (method === 'PUT') {
                options.headers['X-HTTP-Method-Override'] = 'PUT';
            }
    
            try {
                const data = await fetchAPI(url, options); 
                console.log('Respons Sukses dari Server (data objek penuh):', data); 
    
                console.log('Memanggil hideNotification untuk:', loadingNotif);
                hideNotification(loadingNotif); 

                console.log('Memanggil showCentralSuccessPopup dengan pesan:', data.success);
                showCentralSuccessPopup(data.success);
                
                closeMenuModalAdmin(); 

                // --- LOGIKA UPDATE FRONTEND TANPA REFRESH ---
                // Dapatkan content_type saat ini dari URL jika ada
                const urlParams = new URLSearchParams(window.location.search);
                const currentContentType = urlParams.get('content_type') || 'Default'; 

                // --- DEBUGGING isCurrentMenuOpened ---
                console.log('Nilai data.menu_id (dari respons sukses):', data.menu_id);
                console.log('Nilai window.initialBladeData:', window.initialBladeData);
                console.log('Nilai window.initialBladeData.selectedNavItemId:', window.initialBladeData ? window.initialBladeData.selectedNavItemId : 'undefined');

                const isCurrentMenuOpened = true; 
                console.log('Hasil isCurrentMenuOpened:', isCurrentMenuOpened);
                // --- AKHIR DEBUGGING isCurrentMenuOpened ---

                // 1. Update Judul di Halaman Konten (jika menu ini sedang dibuka DAN menu_status=1)
                if (mainContentTitleElement && data.menu_status == 1 && isCurrentMenuOpened) {
                    let newTitleText = data.new_menu_nama;
                    if (currentContentType !== 'Default') { 
                        newTitleText;
                    }
                    mainContentTitleElement.textContent = newTitleText;
                    console.log('Judul konten diperbarui.');
                } else {
                    console.log('Judul konten TIDAK diperbarui karena kondisi tidak terpenuhi.');
                    console.log('mainContentTitleElement:', mainContentTitleElement);
                    console.log('data.menu_status:', data.menu_status);
                    console.log('isCurrentMenuOpened:', isCurrentMenuOpened);
                }

                // 2. Update URL di Address Bar (jika menu ini sedang dibuka DAN menu_status=1)
                if (data.menu_status == 1 && isCurrentMenuOpened) {
                    const newUrl = `/docs/${data.current_category}/${data.new_menu_link}?content_type=${currentContentType}`;
                    if (window.location.href !== newUrl) { 
                        history.pushState(null, '', newUrl);
                        console.log('URL address bar diperbarui ke:', newUrl);
                    } else {
                        console.log('URL address bar tidak perlu diperbarui (sudah sama atau kondisi tidak terpenuhi).');
                    }
                } else {
                    console.log('URL address bar TIDAK diperbarui karena kondisi tidak terpenuhi.');
                    console.log('data.menu_status:', data.menu_status);
                    console.log('isCurrentMenuOpened:', isCurrentMenuOpened);
                }

                // 3. Update Link di Sidebar Navigasi secara parsial
                const $updatedLink = document.querySelector(`a[data-menu-id="${data.menu_id}"]`);
                const $updatedDiv = document.querySelector(`div[data-toggle="submenu-${data.menu_id}"]`);
                
                if ($updatedLink || $updatedDiv) {
                    const targetElement = $updatedLink ? $updatedLink.querySelector('span') : ($updatedDiv ? $updatedDiv.querySelector('span') : null);
                    if (targetElement) {
                        targetElement.textContent = data.new_menu_nama;
                        console.log('Teks menu di sidebar diperbarui.');
                    }

                    if ($updatedLink) {
                        let newHrefForSidebar = `/docs/${data.current_category}/${data.new_menu_link}`;
                        if (data.menu_status === 1) {
                            newHrefForSidebar += `?content_type=${currentContentType}`; 
                        }
                        $updatedLink.setAttribute('href', newHrefForSidebar);
                        $updatedLink.setAttribute('data-menu-link', data.new_menu_link);
                        console.log('Href link sidebar diperbarui ke:', newHrefForSidebar);
                    }
                    
                    const oldMenuStatusFromForm = dataToSend.menu_status; 
                    const oldMenuChildFromForm = dataToSend.menu_child; 

                    if (data.menu_status != oldMenuStatusFromForm || data.menu_child != oldMenuChildFromForm) {
                         console.log("Perubahan status/child terdeteksi, merefresh sidebar penuh.");
                         refreshSidebar();
                    }
                } else {
                    console.warn("Link menu tidak ditemukan di sidebar atau perubahan struktur kompleks, melakukan refresh sidebar penuh.");
                    refreshSidebar();
                }

            } catch (error) {
                console.error('Kesalahan saat menyimpan menu:', error);
                if (loadingNotif) {
                     hideNotification(loadingNotif); 
                }
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
                    // refreshSidebar(); // Ini akan direplace dengan redirect
                    
                    // === PERUBAHAN DI SINI ===
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url; // Mengalihkan ke URL yang diberikan backend
                    } else {
                        refreshSidebar(); // Fallback jika redirect_url tidak ada (jarang terjadi)
                    }
                    // =========================

                } catch (error) {
                    hideNotification(deleteLoadingNotif);    
                    showNotification(`Gagal menghapus: ${error.message || 'Terjadi kesalahan'}`, 'error');
                    console.error('Kesalahan menghapus menu:', error);
                    closeDeleteConfirmModal();
                }
            }
        });
    }
    
    // Global functions (ensure these are indeed global or accessible)
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