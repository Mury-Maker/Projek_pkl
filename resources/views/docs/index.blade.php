<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dokumentasi' }} - Projek PKL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .prose h1 { @apply text-3xl font-bold mb-4 text-gray-800; }
        .prose h2 { @apply text-2xl font-bold mb-3 text-gray-700; }
        .prose p { @apply text-gray-700 leading-relaxed mb-4; }
        .prose a { @apply text-blue-600 hover:underline; }
        .prose code:not(pre code) { @apply bg-gray-200 text-red-600 rounded px-1 py-0.5 text-sm; }
        .prose pre { @apply bg-gray-800 text-white rounded-lg p-4 overflow-x-auto; }
        .prose ul { @apply list-disc list-inside mb-4; }
        .prose ol { @apply list-decimal list-inside mb-4; }
        .notification-message { position: fixed; top: 1rem; right: 1rem; padding: 0.75rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 5000; opacity: 0; transform: translateY(-20px); transition: all 0.3s ease-out; }
        .notification-message.show { opacity: 1; transform: translateY(0); }
        .notification-message.success { background-color: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .notification-message.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 4000; visibility: hidden; opacity: 0; transition: visibility 0s, opacity 0.3s; }
        .modal.show { visibility: visible; opacity: 1; }
        .modal-content { background-color: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); width: 90%; max-width: 600px; transform: translateY(-50px); transition: transform 0.3s ease-out; }
        .modal.show .modal-content { transform: translateY(0); }
        .buttons{ min-width: 100%; margin: 10px; display: flex; padding: 10px; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .btn{ padding: 12px; border-radius: 8px; }
        .btn-simpan{ background-color: #45a65a; color: white; }
        .btn-batal{ background-color: #00c0ef; color: white; }
        .btn-hapus{ background-color: red; color: white; }
        .judul-halaman{ margin: 10px 10px 10px 0; }
        .judul-halaman h1{ font-size: 26px }

        /* CSS untuk Dropdown Sidebar */
        .submenu-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-in-out;
        }
        .submenu-container.open {
            max-height: 1000px; /* Nilai besar untuk menampung semua submenu */
        }
        /* PERBAIKAN: Target kelas 'open' pada elemen <i> di dalam .menu-arrow-icon */
        .menu-arrow-icon i.open {
            transform: rotate(-90deg); /* Dari kiri ke bawah */
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen flex-col">
        {{-- Header --}}
        <header class="bg-white shadow-sm w-full border-b border-gray-200 z-20">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center space-x-8">
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">ProjekPKL</a>
                        <div class="hidden md:flex items-center space-x-2 rounded-lg bg-gray-100 p-1">
                            <a href="{{ route('docs', ['category' => 'epesantren']) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $currentCategory == 'epesantren' ? 'bg-white text-gray-800 shadow' : 'text-gray-600 hover:bg-gray-200' }}">Epesantren</a>
                            <a href="{{ route('docs', ['category' => 'adminsekolah']) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $currentCategory == 'adminsekolah' ? 'bg-white text-gray-800 shadow' : 'text-gray-600 hover:bg-gray-200' }}">Admin Sekolah</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @guest
                            <a href="{{ route('login') }}" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700">Log In</a>
                        @else
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">Log Out</button></form>
                        @endguest
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            <aside class="w-72 flex-shrink-0 overflow-y-auto bg-stone border-r border-gray-200 p-6">
                @auth
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Navigasi</h2>
                    <button id="add-parent-menu-btn" class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors" title="Tambah Menu Utama Baru">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                @endauth
                <form id="search-form" class="relative mb-4">
                    <input type="text" id="menu-search-input" placeholder="Cari menu & konten..." class="w-full pl-4 pr-10 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-blue-600">
                        <i class="fa fa-search"></i>
                    </button>
                </form>
                <div id="search-results-modal" class="modal">
                    <div class="modal-content">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-800" id="search-modal-title">Hasil Pencarian</h3>
                            <button id="close-search-modal-btn" class="text-gray-500 hover:text-gray-800">&times;</button>
                        </div>
                        <div id="search-results-body" class="max-h-[60vh] overflow-y-auto">
                        </div>
                    </div>
                </div>
                <div id="notification-container"></div>
                <nav id="sidebar-navigation">
                    @include('docs._menu_item', [
                        'items' => $navigation,
                        'editorMode' => auth()->check(),
                        'selectedNavItemId' => $selectedNavItem->menu_id ?? null
                    ])
                </nav>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                @auth
                @if (isset($selectedNavItem))
                    <div class="absolute top-8 right-8 z-10"></div>
                @endif
                @endauth
                <div class="judul-halaman">
                    <h1> {!! ucfirst($currentPage) !!}</h1>
                </div>
                <div class="prose max-w-none" id="documentation-content" >
                    @include($viewPath)
                </div>
            </main>
        </div>
    </div>

    {{-- Modal for Add/Edit Menu --}}
    @auth
    <div id="menu-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="modal-title">Tambah Menu Baru</h3>
            <form id="menu-form">
                <input type="hidden" id="form_menu_id" name="menu_id">
                <input type="hidden" id="form_method" name="_method" value="POST">
                <input type="hidden" id="form_category" name="category" value="{{ $currentCategory }}">
                <div class="mb-4">
                    <label for="form_menu_nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Menu:</label>
                    <input type="text" id="form_menu_nama" name="menu_nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4">
                    <label for="form_menu_icon" class="block text-gray-700 text-sm font-bold mb-2">Ikon (Font Awesome Class):</label>
                    <input type="text" id="form_menu_icon" name="menu_icon" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="Contoh: fa-solid fa-house">
                </div>
                <div class="mb-4">
                    <label for="form_menu_child" class="block text-gray-700 text-sm font-bold mb-2">Parent Menu:</label>
                    <select id="form_menu_child" name="menu_child" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <option value="0">Tidak Ada (Menu Utama)</option>
                        {{-- Opsi parent akan diisi oleh JavaScript saat modal dibuka --}}
                        {{-- @foreach ($allParentMenus as $parent) <-- This PHP block is replaced by JS dynamic loading for this specific select --}}
                        {{--     <option value="{{ $parent->menu_id }}">{{ $parent->menu_nama }}</option> --}}
                        {{-- @endforeach --}}
                    </select>
                </div>
                <div class="mb-4">
                    <label for="form_menu_order" class="block text-gray-700 text-sm font-bold mb-2">Urutan:</label>
                    <input type="number" id="form_menu_order" name="menu_order" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="0" required>
                </div>
                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="form_menu_status" name="menu_status" value="1" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Aktifkan Menu</span>
                    </label>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" id="cancel-menu-form-btn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                    <button type="submit" id="submit-menu-form-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.umd.js" crossorigin></script>
    <script src="{{ asset('ckeditor/main.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // =================================
        // VARIABEL & FUNGSI UTILITAS
        // =================================
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const modalTitleElement = document.getElementById('modal-title');
        const currentCategory = document.getElementById('form_category')?.value || 'epesantren';

        const showNotification = (message, type = 'success') => {
            const container = document.getElementById('notification-container');
            const notifId = 'notif-' + Date.now();
            const notifDiv = document.createElement('div');
            notifDiv.id = notifId;
            notifDiv.className = `notification-message ${type}`;
            notifDiv.textContent = message;
            container.appendChild(notifDiv);

            setTimeout(() => notifDiv.classList.add('show'), 10);
            setTimeout(() => {
                notifDiv.classList.remove('show');
                setTimeout(() => notifDiv.remove(), 500);
            }, 3000);
        };

        const fetchAPI = async (url, options = {}) => {
            const defaultHeaders = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            };
            // Merge default headers with any custom headers provided in options
            options.headers = { ...defaultHeaders, ...options.headers };

            try {
                const response = await fetch(url, options);
                if (!response.ok) {
                    const errorData = await response.json();
                    // Return a more descriptive error message if available from backend
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Fetch API Error:', error);
                // Re-throw to be caught by the specific event listener's catch block
                throw error;
            }
        };

        // =================================
        // LOGIKA PENCARIAN
        // =================================
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('menu-search-input');
        const searchModal = document.getElementById('search-results-modal');
        const searchResultsBody = document.getElementById('search-results-body');
        const closeSearchModalBtn = document.getElementById('close-search-modal-btn');
        const searchModalTitle = document.getElementById('search-modal-title');

        if (searchForm) {
            searchForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query.length < 2) {
                    showNotification('Masukkan minimal 2 karakter untuk mencari.', 'error');
                    return;
                }
                searchModal.classList.add('show');
                searchModalTitle.textContent = `Mencari untuk: "${query}"`;
                searchResultsBody.innerHTML = '<p class="text-gray-500">Memuat hasil...</p>';
                try {
                    const data = await fetchAPI(`/api/search?query=${query}&category=${currentCategory}`);
                    searchResultsBody.innerHTML = '';
                    if (data.results && data.results.length > 0) {
                        const resultList = document.createElement('ul');
                        resultList.className = 'space-y-3';
                        data.results.forEach(result => {
                            const li = document.createElement('li');
                            li.innerHTML = `
                                <a href="${result.url}" class="block p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="font-semibold text-blue-600">${result.name}</div>
                                    <p class="text-sm text-gray-600 mt-1">${result.context}</p>
                                </a>
                            `;
                            resultList.appendChild(li);
                        });
                        searchResultsBody.appendChild(resultList);
                    } else {
                        searchResultsBody.innerHTML = '<p class="text-gray-500">Tidak ada hasil yang ditemukan.</p>';
                    }
                } catch (error) {
                    searchResultsBody.innerHTML = '<p class="text-red-500">Terjadi kesalahan saat mencari.</p>';
                }
            });
        }

        const closeSearchModal = () => searchModal.classList.remove('show');
        if(closeSearchModalBtn) closeSearchModalBtn.addEventListener('click', closeSearchModal);
        if(searchModal) searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) {
                closeSearchModal();
            }
        });

        // =================================
        // LOGIKA DROPDOWN SIDEBAR (Event Delegation)
        // =================================

        const sidebar = document.getElementById('sidebar-navigation');
        if (sidebar) {
            // Attach THIS single click listener ONCE for event delegation
            // This handles clicks on the arrow icons for dropdowns
            sidebar.addEventListener('click', (e) => {
                const trigger = e.target.closest('.menu-arrow-icon');
                if (!trigger) return;

                e.preventDefault(); // Prevent default link behavior if it's wrapped in an <a> or similar

                const submenuId = trigger.dataset.toggle;
                const submenu = document.getElementById(submenuId);
                const icon = trigger.querySelector('i');

                if (submenu) {
                    const isCurrentlyOpen = submenu.classList.contains('open');
                    submenu.classList.toggle('open');
                    trigger.setAttribute('aria-expanded', isCurrentlyOpen ? 'false' : 'true');
                    if (icon) {
                        icon.classList.toggle('open', !isCurrentlyOpen); // Toggle based on new state
                    }
                } else if (icon) {
                    icon.classList.toggle('open');
                }
            });
        }

        const initSidebarDropdown = () => {
            // This function will now primarily handle opening the active menu's parents
            // and ensure the correct arrow icon state on initial load or sidebar refresh.
            const sidebarElement = document.getElementById('sidebar-navigation'); // Get it again in case it was replaced
            if (!sidebarElement) return;

            // Function to open parents of the active menu item
            const openActiveMenuParents = () => {
                const activeItemContainer = sidebarElement.querySelector('.bg-blue-100')?.closest('.my-1');
                if (activeItemContainer) {
                    let currentSubmenu = activeItemContainer.closest('.submenu-container');
                    while (currentSubmenu) {
                        currentSubmenu.classList.add('open');
                        // Find the button that controls this specific submenu
                        const triggerButton = sidebarElement.querySelector(`[data-toggle="${currentSubmenu.id}"]`);
                        if (triggerButton) {
                            const icon = triggerButton.querySelector('i');
                            if (icon) {
                                icon.classList.add('open'); // Ensure arrow rotates for active parents
                                triggerButton.setAttribute('aria-expanded', 'true');
                            }
                        }
                        // Move up to the next parent submenu container
                        currentSubmenu = currentSubmenu.parentElement.closest('.submenu-container');
                    }
                }
            };

            openActiveMenuParents();
        };


        // =================================
        // LOGIKA ADMIN (MODAL & CRUD)
        // =================================
        const menuModal = document.getElementById('menu-modal');
        const menuForm = document.getElementById('menu-form');

        const openMenuModal = (mode, menuData = null, parentId = 0) => {
            menuForm.reset();
            document.getElementById('form_menu_id').value = '';
            document.getElementById('form_method').value = mode === 'edit' ? 'PUT' : 'POST';

            const formMenuChildSelect = document.getElementById('form_menu_child');
            formMenuChildSelect.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>'; // Reset opsi

            // Determine editing_menu_id for API call
            const editingMenuId = mode === 'edit' && menuData ? menuData.menu_id : null;
            let parentApiUrl = `/api/navigasi/parents/${currentCategory}`;
            if (editingMenuId) {
                parentApiUrl += `?editing_menu_id=${editingMenuId}`;
            }

            fetchAPI(parentApiUrl) // Fetch parent menus based on context
                .then(parents => {
                    parents.forEach(parent => {
                        const option = document.createElement('option');
                        option.value = parent.menu_id;
                        option.textContent = parent.menu_nama;
                        formMenuChildSelect.appendChild(option);
                    });

                    if (mode === 'create') {
                        if (modalTitleElement) modalTitleElement.textContent = 'Tambah Menu Baru';
                        formMenuChildSelect.value = parentId; // Set parent default if creating
                        document.getElementById('form_menu_status').checked = true;
                    } else if (mode === 'edit' && menuData) {
                        if (modalTitleElement) modalTitleElement.textContent = `Edit Menu: ${menuData.menu_nama}`;
                        document.getElementById('form_menu_id').value = menuData.menu_id;
                        document.getElementById('form_menu_nama').value = menuData.menu_nama;
                        document.getElementById('form_menu_icon').value = menuData.menu_icon;
                        formMenuChildSelect.value = menuData.menu_child; // Set parent that already exists
                        document.getElementById('form_menu_order').value = menuData.menu_order;
                        document.getElementById('form_menu_status').checked = menuData.menu_status == 1;
                    }
                })
                .catch(error => {
                    showNotification('Gagal memuat daftar parent menu.', 'error');
                    console.error('Error loading parent menus:', error);
                });

            menuModal.classList.add('show');
        };

        const closeMenuModal = () => menuModal.classList.remove('show');

        // THIS IS THE CRUCIAL FUNCTION THAT RE-ATTACHES CRUD LISTENERS
        const refreshSidebar = async () => {
            console.log('Refreshing sidebar and re-attaching listeners...');
            try {
                const data = await fetchAPI(`/api/navigasi/all/${currentCategory}`);
                document.getElementById('sidebar-navigation').innerHTML = data.html;
                attachAdminEventListeners(); // RE-ATTACH CRUD LISTENERS AFTER NEW HTML IS LOADED
                initSidebarDropdown();       // RE-INITIALIZE DROPDOWN STATE (open active parents)
                showNotification('Sidebar berhasil diperbarui!', 'success'); // Optional: notify user
            } catch (error) {
                showNotification('Gagal memuat ulang sidebar.', 'error');
                console.error('Error refreshing sidebar:', error);
            }
        };

        const attachAdminEventListeners = () => {
            console.log('Attaching admin event listeners...');

            // Edit Button
            document.querySelectorAll('.edit-menu-btn').forEach(button => {
                console.log('Found edit button:', button);
                button.addEventListener('click', async (e) => {
                    e.stopPropagation(); // Prevents click from bubbling up to parent elements
                    console.log('Edit button clicked for menu ID:', e.currentTarget.dataset.menuId);
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

            // Delete Button
            document.querySelectorAll('.delete-menu-btn').forEach(button => {
                console.log('Found delete button:', button);
                button.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevents click from bubbling up
                    console.log('Delete button clicked for menu ID:', e.currentTarget.dataset.menuId);
                    const menuId = e.currentTarget.dataset.menuId;
                    const menuNama = e.currentTarget.dataset.menuNama;
                    if (confirm(`Yakin ingin menghapus menu "${menuNama}"? Ini akan menghapus semua sub-menunya.`)) {
                        fetchAPI(`/api/navigasi/${menuId}`, { method: 'DELETE' })
                            .then(data => {
                                showNotification(data.success, 'success');
                                refreshSidebar(); // Refresh after delete
                            })
                            .catch(error => {
                                showNotification(`Gagal menghapus: ${error.message || 'Terjadi kesalahan'}`, 'error');
                                console.error('Error deleting menu:', error);
                            });
                    }
                });
            });

            // Add Child Button
            document.querySelectorAll('.add-child-menu-btn').forEach(button => {
                console.log('Found add child button:', button);
                button.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevents click from bubbling up
                    console.log('Add child button clicked for parent ID:', e.currentTarget.dataset.parentId);
                    const parentId = e.currentTarget.dataset.parentId;
                    openMenuModal('create', null, parentId);
                });
            });
        };

        // =================================
        // INISIALISASI SAAT HALAMAN DIMUAT
        // =================================
        initSidebarDropdown(); // Initial setup for dropdowns (opening active parents)

        // Only attach form-related listeners if the menuForm exists (i.e., if user is authenticated)
        if (menuForm) {
            document.getElementById('add-parent-menu-btn').addEventListener('click', () => {
                console.log('Add Parent Menu button clicked.');
                openMenuModal('create', null, 0);
            });
            document.getElementById('cancel-menu-form-btn').addEventListener('click', closeMenuModal);

            menuForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                console.log('Menu form submitted.');

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

                // Add _method explicitly to the body if method is PUT (for Laravel to recognize it)
                if (method === 'PUT') {
                    dataToSend._method = 'PUT';
                }

                const url = menuId ? `/api/navigasi/${menuId}` : '/api/navigasi';

                const options = {
                    method: 'POST', // Always POST for Fetch API, use X-HTTP-Method-Override for PUT/DELETE
                    body: JSON.stringify(dataToSend),
                };

                // Add X-HTTP-Method-Override if method is PUT
                if (method === 'PUT') {
                    options.headers = {
                        ...options.headers,
                        'X-HTTP-Method-Override': 'PUT'
                    };
                }

                try {
                    const data = await fetchAPI(url, options);
                    showNotification(data.success, 'success');
                    closeMenuModal();
                    refreshSidebar(); // REFRESH SIDEBAR AFTER A SUCCESSFUL SAVE/UPDATE
                } catch (error) {
                    console.error('Error saat menyimpan menu:', error);
                    if (error.message) {
                        showNotification(`Gagal menyimpan: ${error.message}`, 'error');
                    } else {
                        showNotification('Terjadi kesalahan tidak dikenal saat menyimpan menu.', 'error');
                    }
                }
            });

            // Initial attachment of admin event listeners when the page first loads and authentication is present
            attachAdminEventListeners();
        }
    });
    </script>
</body>
</html>