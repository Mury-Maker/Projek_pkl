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
    <script src="https://kit.fontawesome.com/f898b05a2e.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- CKEditor CSS hanya dimuat jika user adalah admin --}}
    @auth
        @if(auth()->user()->role === 'admin')
            <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
            <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
        @endif
    @endauth
</head>
<body class="bg-gray-100">
    <div class="flex h-screen flex-col">
        {{-- Header --}}
        <header class="bg-white shadow-sm w-full border-b border-gray-200 z-20">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center header-content-wrapper">

                    {{-- Bagian Kiri Header (Logo + Kategori) --}}
                    <div class="header-spacer-left space-x-8">

                        <button id="mobile-menu-toggle" class="md:hidden text-gray-600 focus:outline-none focus:text-gray-900">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                        {{-- Batasi tampilan nama kategori di judul kiri header --}}
                        <a href="{{ route('docs', ['category' => $currentCategory]) }}" 
                           class="text-2xl font-bold text-blue-600 header-main-category-title" 
                           title="{!! ucwords(str_replace('-',' ',$currentCategory)) !!}">
                           {!! ucwords(str_replace('-',' ',$currentCategory)) !!}
                        </a>
                                                
                        @php use Illuminate\Support\Str; @endphp

                        <div class="relative hidden md:block">
                            {{-- Batasi tampilan nama kategori di tombol dropdown --}}
                            <button id="category-dropdown-btn" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none header-dropdown-button-text">
                                <span id="category-button-text" title="{!! ucwords(str_replace('-',' ',$currentCategory)) !!}">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span>
                                <i class="ml-2 fa fa-chevron-down text-xs"></i>
                            </button>

                            <div id="category-dropdown-menu" class="header-dropdown-menu">
                                @foreach ($categories as $cats)
                                    @php
                                        $slug = Str::slug($cats); // Slug untuk URL dan data attribute
                                        $isActive = $currentCategory === $slug;
                                    @endphp
                                    <div class="flex items-center justify-between">
                                        {{-- Nama kategori di item dropdown juga dibatasi jika terlalu panjang --}}
                                        <a href="{{ route('docs', ['category' => $slug]) }}"
                                           class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }} header-dropdown-item-text"
                                           data-category-key="{{ $slug }}"
                                           data-category-name="{{ $cats }}"
                                           title="{!! ucwords(str_replace('-',' ',$cats)) !!}">
                                            {!! ucwords(str_replace('-',' ',$cats)) !!}
                                        </a>
                                        @auth
                                            @if(auth()->user()->role === 'admin')
                                                <div class="flex-shrink-0 flex items-center space-x-1 pr-2">
                                                    <button type="button" onclick="openCategoryModal('edit', '{{ $cats }}', '{{ $slug }}')" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Button Delete Kategori --}}
                                                    @if($slug !== 'epesantren') 
                                                        <button type="button" onclick="confirmDeleteCategory('{{ $slug }}', '{{ Str::headline($cats) }}')" title="Hapus Kategori" class="text-red-500 hover:text-red-700 p-1">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        @endauth
                                    </div>
                                @endforeach
                                @auth
                                    @if(auth()->user()->role === 'admin')
                                        <div class="border-t border-gray-200 my-1"></div>
                                        
                                        <button onclick="openCategoryModal('create', '', '')" class="text-blue-600 hover:underline text-sm" style="padding:10px;">
                                            + Tambah Kategori
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Tengah Header (untuk Search Button) --}}
                    <div class="search-button-wrapper">
                        <button id="open-search-modal-btn-header" class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                            <span class="flex items-center space-x-2">
                                <i id="search-icon" class="fa fa-search text-gray-400"></i>
                                <span class="text-search">Cari menu & konten...</span>
                            </span>
                        </button>
                    </div>

                    {{-- Bagian Kanan Header (Login/Logout) --}}
                    <div class="relative hidden md:flex header-spacer-right space-x-4 flex items-center">
                        @auth
                            {{-- Tampilkan role pengguna --}}
                            <span class="user-role-badge">
                                Selamat Datang:
                                <span class="role-text">{{ ucfirst(auth()->user()->role) }}</span>
                            </span>
                        
                            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="logout-btn" id="logout-btn">Log Out</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition">Log In</a>
                        @endauth
                    </div>                        
                </div>
            </div>
        </header>
        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            <div id="sidebar-backdrop" class="backdrop"></div>

            <aside class="w-72 flex-shrink-0 overflow-y-auto bg-stone border-r border-gray-200 p-6">
                            
                {{-- Sidebar Tambahan Mobile (akan tampil saat responsif) --}}
                <div class="block md:hidden space-y-4 mb-6">
                    {{-- Kategori --}}

                    <div class="relative md:block">
                    @auth
                        <div class="text-sm">
                            Selamat Datang: <span class="font-semibold">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                    @endauth
                    
                        <button id="category-dropdown-btn-mobile" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none header-dropdown-button-text">
                            <span id="category-button-text-mobile" title="{!! ucwords(str_replace('-',' ',$currentCategory)) !!}">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span>
                            <i class="ml-2 fa fa-chevron-down text-xs"></i>
                        </button>

                        <div id="category-dropdown-menu-mobile" class="header-dropdown-menu">
                            @foreach ($categories as $cats)
                                @php
                                    $slug = Str::slug($cats); // Slug untuk URL dan data attribute
                                    $isActive = $currentCategory === $slug;
                                @endphp
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('docs', ['category' => $slug]) }}"
                                    class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }} header-dropdown-item-text"
                                    data-category-key="{{ $slug }}"
                                    data-category-name="{{ $cats }}"
                                    title="{!! ucwords(str_replace('-',' ',$cats)) !!}">
                                    {!! ucwords(str_replace('-',' ',$cats)) !!}
                                    </a>
                                    @auth
                                        @if(auth()->user()->role === 'admin')
                                            <div class="flex-shrink-0 flex items-center space-x-1 pr-2">
                                                <button type="button" onclick="openCategoryModal('edit', '{{ $cats }}', '{{ $slug }}')" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                {{-- Only allow deleting non-default categories --}}
                                                @if($slug !== 'epesantren')
                                                    <button type="button" onclick="confirmDeleteCategory('{{ $slug }}', '{{ Str::headline($cats) }}')" title="Hapus Kategori" class="text-red-500 hover:text-red-700 p-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>                                        
                                                @endif
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            @endforeach
                            @auth
                                @if(auth()->user()->role === 'admin')
                                    <div class="border-t border-gray-200 my-1"></div>
                                    
                                    <button onclick="openCategoryModal('create', '', '')" class="text-blue-600 hover:underline text-sm" style="padding:10px;">
                                        + Tambah Kategori
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>

            {{-- Navigasi Utama --}}
                {{-- Tombol "Tambah Menu Baru" hanya untuk admin --}}
                @auth
                    @if(auth()->user()->role === 'admin')
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Navigasi</h2>
                            <button id="add-parent-menu-btn" class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors" title="Tambah Menu Utama Baru">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    @else
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Navigasi</h2>
                    @endif
                @endauth
                @guest
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Navigasi</h2>
                @endguest
                
                {{-- Ini adalah container untuk notifikasi --}}
                <div id="notification-container"></div>
                
                <nav id="sidebar-navigation">
                    {{-- _menu_item akan menangani kondisional untuk tombol edit/delete/add-child --}}
                    @include('docs._menu_item', [
                        'items' => $navigation,
                        'editorMode' => auth()->check() && (auth()->user()->role ?? '') === 'admin',
                        'selectedNavItemId' => $selectedNavItem->menu_id ?? null,
                        'currentCategory' => $currentCategory // Pass currentCategory here
                    ])
                </nav>
                <div class="relative lg:hidden header-spacer-right space-x-4 flex items-center">
                                         <form method="POST" action="{{ route('logout') }}">
                        @auth
                            @csrf
                            <button type="submit"
                                class="mt-2 w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                                    Log Out
                            </button>
                        </form>
                        @else
                            <a href="{{ route('login') }}"
                                class="w-full inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm text-center">
                                    Log In
                            </a>
                                @endauth
                </div>

            </aside>

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                <div class="judul-halaman">
                    <h1> {!! ucfirst(Str::headline($currentPage)) !!} {{-- Use Str::headline for better display of slugs --}}
                    </h1>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            {{-- Hanya tampilkan tombol edit jika ada menu_id yang valid --}}
                            @if(isset($menu_id) && $menu_id > 0 && ($selectedNavItem->menu_status ?? 0) == 1)
                                <button id="editBtn" onclick="openEditor()"><i class="fa-solid fa-file-pen"></i></button>
                            @endif
                        @endif
                    @endauth
                </div>

                {{-- Content Tabs for UAT, Pengkodean, Database --}}
                @if(isset($selectedNavItem) && $selectedNavItem->menu_status == 1 && $selectedNavItem->menu_child !== 0 && !empty($contentTypes))
                    <div class="content-tabs" id="content-tabs">
                        @foreach($contentTypes as $type)
                            <button type="button" class="tab-button" data-content-type="{{ $type }}">{{ $type }}</button>
                        @endforeach
                    </div>
                @endif

                {{-- Konten Dokumentasi dan Editor (berada langsung di index.blade.php) --}}
                <div class="prose max-w-none" id="documentation-content">
                    {{-- Tampilan konten dari database --}}
                    <div id="kontenView" class="ck-content">
                        {{-- Menggunakan $contentDocs untuk menampilkan konten --}}
                        {!! $contentDocs->content ?? "Konten Belum Tersedia" !!}
                    </div>

                    {{-- Editor CKEditor (hanya untuk admin dan jika ada menu_id yang valid) --}}
                    @auth
                        @if(auth()->user()->role === 'admin')
                            {{-- Editor hanya ditampilkan jika ada item navigasi yang dipilih (menu_id > 0) DAN menu_statusnya 1 --}}
                            @if(isset($menu_id) && $menu_id > 0 && ($selectedNavItem->menu_status ?? 0) == 1)
                                <div class="main-container">
                                    <div class="editor-container hidden" id="editor-container">
                                        <form id="editor-form" action="{{ route('docs.save', ['menu_id' => $menu_id]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="currentCategoryFromForm" value="{{ $currentCategory }}">
                                            <input type="hidden" name="currentPageFromForm" value="{{ $currentPage }}">
                                            <input type="hidden" name="content_type" id="editor-content-type" value="{{ $contentDocs->title ?? 'Default' }}">

                                            <textarea name="content" id="editor" class="ckeditor">
                                                {{ $contentDocs->content ?? "" }}
                                            </textarea>
                                            <div class="buttons mt-4 flex justify-end space-x-3">
                                                <button type="button" id="cancel-editor-btn" class="btn btn-batal"><i class="fa fa-times-circle mr-2"></i>Batal</button>
                                                <button type="submit" class="btn btn-simpan"><i class="fa fa-save mr-2"></i>Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endauth
                    
                </div>
            </main>
        </div>
    </div>

    {{-- MODAL GLOBAL UNTUK PENCARIAN (BARU) --}}
    <div id="search-overlay" class="search-modal">
        <div id="search-modal-content">
            <div id="search-input-container" class="flex items-center px-4 py-3">
                <i class="fa fa-search text-gray-400 mr-3"></i>
                <input type="text" id="search-overlay-input" placeholder="Cari dokumentasi..." class="flex-grow bg-transparent border-none focus:outline-none text-lg text-gray-800">
                <button id="clear-search-input-btn" class="text-gray-400 hover:text-gray-600 focus:outline-none hidden">
                    <i class="fa fa-times-circle"></i>
                </button>
                <button id="close-search-overlay-btn" class="text-gray-500 hover:text-gray-700 ml-3 focus:outline-none p-1 rounded">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div id="search-results-list" class="empty-state">
                <p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>
                {{-- Hasil pencarian akan diisi di sini oleh JavaScript --}}
            </div>
        </div>
    </div>
    {{-- AKHIR MODAL GLOBAL UNTUK PENCARIAN --}}

    {{-- Modal for Add/Edit Menu (EXISTING) - HANYA DIMUAT JIKA USER ADALAH ADMIN --}}
    @auth
        @if(auth()->user()->role === 'admin')
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
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="form_menu_order" class="block text-gray-700 text-sm font-bold mb-2">Urutan:</label>
                            <input type="number" id="form_menu_order" name="menu_order" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="0" required>
                        </div>
                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="form_menu_status" name="menu_status" value="1" class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Centang Jika Ingin Menu Ini Memiliki Konten</span>
                            </label>
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" id="cancel-menu-form-btn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                            <button type="submit" id="submit-menu-form-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="categoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                    <h2 id="categoryModalTitle" class="text-lg font-semibold mb-4">Tambah Kategori</h2>
            
                    <form id="categoryForm">
                        @csrf
                        <input type="hidden" id="form_category_method" name="_method" value="POST">
                        <input type="hidden" id="form_original_category_slug" name="original_category_slug"> {{-- NEW HIDDEN INPUT --}}
            
                        <div class="mb-4">
                            <label for="form_category_nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                            <input type="text" id="form_category_nama" name="category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
            
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeCategoryModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- NEW: Delete Confirmation Modal --}}
            <div id="delete-confirm-modal" class="modal">
                <div id="delete-confirm-modal-content" class="modal-content">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Konfirmasi Hapus</h3>
                    <p id="delete-confirm-message" class="mb-6 text-gray-700"></p>
                    <div class="flex justify-center space-x-4">
                        <button id="cancel-delete-btn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                        <button id="confirm-delete-btn" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Hapus</button>
                    </div>
                </div>
            </div>
            {{-- END NEW: Delete Confirmation Modal --}}
        @endif
    @endauth

    {{-- CKEditor scripts - HANYA DIMUAT JIKA USER ADALAH ADMIN --}}
    @auth
        @if(auth()->user()->role === 'admin')
            <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.umd.js" crossorigin></script>
            <script src="{{ asset('ckeditor/main.js') }}"></script>
        @endif
    @endauth

    <script>
    // =================================
    // VARIABEL & FUNGSI UTILITAS GLOBAL
    // =================================
    // Variabel untuk token CSRF, dapat diakses secara global
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Variabel global untuk instance CKEditor
    let currentEditorInstance;

    // Variabel global untuk modal element (diakses di fungsi openMenuModal)
    let modalTitleElement = document.getElementById('modal-title');
    let categoryModal = document.getElementById('categoryModal'); // Deklarasikan secara global

    // Fungsi untuk menampilkan notifikasi toast
    function showNotification(message, type = 'success', duration = 3000) {
        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('Notification container not found!');
            return;
        }

        const notifId = 'notif-' + Date.now();
        const notifDiv = document.createElement('div');
        notifDiv.id = notifId;
        notifDiv.className = `notification-message ${type}`;

        let iconClass = '';
        let timeoutDuration = duration;
        let delayBeforeShow = 0;

        if (type === 'success') {
            iconClass = 'fa-solid fa-check-circle text-green-700';
        } else if (type === 'error') {
            iconClass = 'fa-solid fa-times-circle text-red-700';
        } else if (type === 'loading') {
            iconClass = 'fa-solid fa-spinner fa-spin text-blue-700';
            timeoutDuration = 0; // Loading notification won't auto-hide
            delayBeforeShow = 500; // Add 0.5 sec delay for loading notification
        }

        notifDiv.innerHTML = `<i class="notification-icon ${iconClass}"></i> ${message}`;
        
        const showTimeoutId = setTimeout(() => {
            container.appendChild(notifDiv);
            setTimeout(() => notifDiv.classList.add('show'), 10);
        }, delayBeforeShow);

        if (timeoutDuration > 0) {
            setTimeout(() => {
                notifDiv.classList.remove('show');
                setTimeout(() => notifDiv.remove(), 500);
            }, timeoutDuration + delayBeforeShow);
        }

        return { notifId: notifId, showTimeoutId: showTimeoutId, element: notifDiv };
    }

    // Fungsi untuk menyembunyikan notifikasi
    function hideNotification(notifInfo) {
        let notifElement;
        let showTimeoutId;

        if (typeof notifInfo === 'string') {
            notifElement = document.getElementById(notifInfo);
        } else if (typeof notifInfo === 'object' && notifInfo !== null) {
            notifElement = notifInfo.element;
            showTimeoutId = notifInfo.showTimeoutId;
        }

        if (showTimeoutId) {
            clearTimeout(showTimeoutId);
        }
        
        if (notifElement && notifElement.parentNode) {
            notifElement.classList.remove('show');
            setTimeout(() => notifElement.remove(), 500);
        }
    }

    // Fungsi untuk melakukan permintaan Fetch API
    async function fetchAPI(url, options = {}) {
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        };
        options.headers = { ...defaultHeaders, ...options.headers };

        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Kesalahan HTTP! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Kesalahan Fetch API:', error);
            throw error;
        }
    }

    // Fungsi untuk menampilkan pop-up sukses di tengah layar
    function showCentralSuccessPopup(message) {
        const centralSuccessPopup = document.getElementById('central-success-popup');
        const centralPopupMessage = document.getElementById('central-popup-message');

        if (centralPopupMessage) {
            centralPopupMessage.textContent = message;
        }
        if (centralSuccessPopup) {
            centralSuccessPopup.classList.add('show');
            setTimeout(() => {
                centralSuccessPopup.classList.remove('show');
            }, 1000);
        }
    }

    // Fungsi untuk membuka modal kategori (dipanggil via onclick)
    function openCategoryModal(mode = 'create', defaultName = '', originalCategorySlug = '') {
        const categoryForm = document.getElementById('categoryForm');
        const categoryModalTitle = document.getElementById('categoryModalTitle');
        const formOriginalCategorySlug = document.getElementById('form_original_category_slug'); // NEW

        if (!categoryForm || !categoryModalTitle || !categoryModal) { // Tambahkan categoryModal di sini
            showNotification('Form kategori tidak ditemukan!', 'error'); // Menggunakan notifikasi global
            return;
        }

        categoryForm.reset();
        document.getElementById('form_category_method').value = mode === 'edit' ? 'PUT' : 'POST';
        document.getElementById('form_category_nama').value = defaultName;
        formOriginalCategorySlug.value = originalCategorySlug; // Set original slug for edit

        categoryModalTitle.textContent = mode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori';
        categoryModal.classList.remove('hidden');
    }

    // Fungsi untuk menutup modal kategori (dipanggil via onclick)
    function closeCategoryModal() {
        const categoryModal = document.getElementById('categoryModal');
        if (categoryModal) { // Pastikan elemen ada sebelum mengaksesnya
            categoryModal.classList.add('hidden');
        }
    }

    // Fungsi untuk konfirmasi hapus kategori (dipanggil via onclick)
    function confirmDeleteCategory(categorySlug, categoryName) {
        console.log('Fungsi confirmDeleteCategory dipanggil untuk:', categoryName, 'dengan slug:', categorySlug);
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
                console.log('Konfirmasi SweetAlert2: Dihapus!');
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
                        // Arahkan kembali ke kategori default atau halaman utama setelah menghapus kategori
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
    }

    // Fungsi untuk membuka editor CKEditor (dipanggil via onclick)
    function openEditor() {
        const kontenView = document.getElementById('kontenView');
        const editorContainer = document.getElementById('editor-container');
        const editorContentTypeInput = document.getElementById('editor-content-type');
        const editBtn = document.getElementById('editBtn');

        kontenView.classList.add('hidden');
        editorContainer.classList.remove('hidden');
        if (editBtn) {
            editBtn.classList.add('hidden');
        }

        const activeTabButton = document.querySelector('.content-tabs button.active');
        let contentType = 'Default';
        if (activeTabButton) {
            contentType = activeTabButton.dataset.contentType;
        }
        editorContentTypeInput.value = contentType;

        // Pastikan $allDocsContents tersedia dari Blade
        const allDocsContents = @json($allDocsContents ?? []);
        const currentContent = allDocsContents.find(doc => doc.title === contentType);
        const contentToLoad = currentContent ? currentContent.content : '# Belum ada konten untuk ' + contentType;

        // Penting: Pastikan CKEditor library dimuat sebelum memanggil ClassicEditor
        if (typeof ClassicEditor === 'undefined') {
            showNotification('Pustaka CKEditor tidak dimuat. Periksa konsol browser untuk kesalahan.', 'error', 5000);
            console.error('ClassicEditor tidak terdefinisi. Pastikan skrip CKEditor UMD dimuat dengan benar.');
            return;
        }

        if (!currentEditorInstance) {
            const editorConfigFromMainJS = window.CKEDITOR_CONFIG || {};

            const finalEditorConfig = {
                ...editorConfigFromMainJS,
                ckfinder: {
                    uploadUrl: '{{ route('ckeditor.upload') . '?_token=' . csrf_token() }}'
                },
                simpleUpload: {
                    uploadUrl: '{{ route('ckeditor.upload') . '?_token=' . csrf_token() }}'
                },
                // Pastikan SimpleUploadAdapter didefinisikan (dari ckeditor/main.js atau CKEditor itu sendiri)
                plugins: [...(editorConfigFromMainJS.plugins || []), typeof SimpleUploadAdapter !== 'undefined' ? SimpleUploadAdapter : null].filter(Boolean),
                toolbar: editorConfigFromMainJS.toolbar
            };

            setTimeout(() => {
                ClassicEditor
                    .create(document.querySelector('#editor'), finalEditorConfig)
                    .then(editor => {
                        currentEditorInstance = editor;
                        editor.setData(contentToLoad);
                    })
                    .catch(error => {
                        console.error('Kesalahan saat menginisialisasi CKEditor:', error);
                        showNotification('Gagal menginisialisasi editor. Detail: ' + error.message, 'error', 5000);
                    });
            }, 50);
        } else {
            currentEditorInstance.setData(contentToLoad);
        }
    }

    // Fungsi untuk menutup editor CKEditor (dipanggil via onclick)
    function closeEditor() {
        const kontenView = document.getElementById('kontenView');
        const editorContainer = document.getElementById('editor-container');
        const editBtn = document.getElementById('editBtn');

        editorContainer.classList.add('hidden');
        kontenView.classList.remove('hidden');
        if (editBtn) {
            editBtn.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // =================================
        // Verifikasi Logout
        // =================================
        const logoutForm = document.getElementById('logout-form');
        const logoutBtn = document.getElementById('logout-btn');

        if (logoutBtn) {
            logoutBtn.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Yakin ingin logout?',
                    text: "Sesi Anda akan berakhir.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        logoutForm.submit();
                    }
                });
            });
        }

        // =================================
        // LOGIKA PENCARIAN (untuk semua user)
        // =================================
        const openSearchModalBtnHeader = document.getElementById('open-search-modal-btn-header');
        const searchOverlay = document.getElementById('search-overlay');
        const searchOverlayInput = document.getElementById('search-overlay-input');
        const searchResultsList = document.getElementById('search-results-list');
        const clearSearchInputBtn = document.getElementById('clear-search-input-btn');
        const closeSearchOverlayBtn = document.getElementById('close-search-overlay-btn');

        const openSearchModal = () => {
            searchOverlay.classList.add('open');
            searchOverlayInput.focus();
            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>';
            clearSearchInputBtn.classList.add('hidden');
        };

        const closeSearchModalSearchOverlay = () => {
            searchOverlay.classList.remove('open');
            searchOverlayInput.value = '';
        };

        if (openSearchModalBtnHeader) {
            openSearchModalBtnHeader.addEventListener('click', openSearchModal);
        }

        if (searchOverlay) {
            searchOverlay.addEventListener('click', (e) => {
                if (e.target === searchOverlay || e.target.closest('#close-search-overlay-btn')) {
                    closeSearchModalSearchOverlay();
                }
            });
        }
        if (closeSearchOverlayBtn) {
            closeSearchOverlayBtn.addEventListener('click', closeSearchModalSearchOverlay);
        }

        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                openSearchModal();
            }
            if (e.key === 'Escape' && searchOverlay.classList.contains('open')) {
                closeSearchModalSearchOverlay();
            }
        });

        let searchTimeout;
        if (searchOverlayInput) {
            searchOverlayInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                const query = searchOverlayInput.value.trim();
                
                if (query.length > 0) {
                    clearSearchInputBtn.classList.remove('hidden');
                } else {
                    clearSearchInputBtn.classList.add('hidden');
                    searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>'; // Clear results immediately if input is empty
                    return; // Stop here if query is empty
                }

                if (query.length < 2) {
                    searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Masukkan minimal 2 karakter untuk mencari.</p>';
                    return;
                }


                searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mencari...</p>';

                searchTimeout = setTimeout(async () => {
                    try {
                        const data = await fetchAPI(`/api/search?query=${query}`);    
                        searchResultsList.innerHTML = '';
                        
                        if (data.results && data.results.length > 0) {
                            const groupedResultsByMenuName = data.results.reduce((acc, result) => {
                                if (!acc[result.name]) {
                                    acc[result.name] = [];
                                }
                                acc[result.name].push(result);
                                return acc;
                            }, {});

                            for (const menuName in groupedResultsByMenuName) {
                                const menuGroupHeader = document.createElement('div');
                                menuGroupHeader.className = 'search-result-category';
                                menuGroupHeader.textContent = menuName;
                                searchResultsList.appendChild(menuGroupHeader);

                                groupedResultsByMenuName[menuName].forEach(result => {
                                    const itemLink = document.createElement('a');
                                    itemLink.href = result.url;
                                    itemLink.className = 'search-result-item px-6 py-3 block hover:bg-gray-100 rounded-md';
                                    itemLink.innerHTML = `
                                        <div class="search-title">${result.name}</div>
                                        <p class="search-category-info">${result.category_name}</p>
                                        ${result.context && result.context !== 'Judul Menu' ? `<p class="search-context">${result.context}</p>` : ''}
                                    `;
                                    itemLink.addEventListener('click', closeSearchModalSearchOverlay);
                                    searchResultsList.appendChild(itemLink);
                                });
                            }
                        } else {
                            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Tidak ada hasil yang ditemukan.</p>';
                        }

                    } catch (error) {
                        searchResultsList.innerHTML = '<p class="text-center text-red-500 p-8">Terjadi kesalahan saat mencari.</p>';
                        console.error('Kesalahan API Pencarian:', error);
                    }
                }, 300);
            });
        }
            // --- FIX FOR CLEAR SEARCH INPUT BUTTON ---
            // Add a click event listener to the clear button
            if (clearSearchInputBtn) {
                clearSearchInputBtn.addEventListener('click', () => {
                    searchOverlayInput.value = ''; // Clear the input field
                    searchOverlayInput.focus();    // Keep focus on the input for immediate re-typing
                    clearSearchInputBtn.classList.add('hidden'); // Hide the clear button
                    searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>'; // Reset search results display
                });
            }
            // --- END FIX ---


        // =================================
        // LOGIKA DROPDOWN SIDEBAR (untuk semua user)
        // =================================

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

        const initSidebarDropdown = () => {
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

        // =================================
        // LOGIKA DROPDOWN KATEGORI HEADER
        // =================================
        const categoryDropdownBtn = document.getElementById('category-dropdown-btn');
        const categoryDropdownText = document.getElementById('category-button-text');
        const categoryDropdownMenu = document.getElementById('category-dropdown-menu');

        const categoryDropdownBtnMobile = document.getElementById('category-dropdown-btn-mobile');
        const categoryDropdownTextMobile = document.getElementById('category-button-text-mobile');
        const categoryDropdownMenuMobile = document.getElementById('category-dropdown-menu-mobile');

        const currentCategoryFromBlade = "{{ $currentCategory ?? 'default' }}";    

        const updateCategoryButtonText = (categoryKey, targetTextElement) => {
            let categoryDisplayName = categoryKey.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            if (targetTextElement) {
                targetTextElement.textContent = categoryDisplayName;
            }
        };

        updateCategoryButtonText(currentCategoryFromBlade, categoryDropdownText);
        if (categoryDropdownTextMobile) {
            updateCategoryButtonText(currentCategoryFromBlade, categoryDropdownTextMobile);
        }
        
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

        // =================================
        // LOGIKA ADMIN (MODAL & CRUD) - HANYA DIJALANKAN JIKA USER ADALAH ADMIN
        // =================================
        @auth
            @if(auth()->user()->role === 'admin')
                const menuModal = document.getElementById('menu-modal');
                const menuForm = document.getElementById('menu-form');
                const deleteConfirmModal = document.getElementById('delete-confirm-modal');
                const deleteConfirmMessage = document.getElementById('delete-confirm-message');
                const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
                const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
                const categoryForm = document.getElementById('categoryForm'); // Dapatkan elemen categoryForm
                const deleteContentBtn = document.getElementById('delete-content-btn');

                let menuToDelete = null;
                                
                const openMenuModal = (mode, menuData = null, parentId = 0) => {
                    if (!menuForm || !modalTitleElement) {
                        showNotification('Elemen form menu tidak ditemukan.', 'error');
                        console.error('Elemen form menu admin tidak ada di DOM.');
                        return;
                    }
                    menuForm.reset();
                    document.getElementById('form_menu_id').value = '';
                    document.getElementById('form_method').value = mode === 'edit' ? 'PUT' : 'POST';

                    const formMenuChildSelect = document.getElementById('form_menu_child');
                    formMenuChildSelect.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>';

                    const editingMenuId = mode === 'edit' && menuData ? menuData.menu_id : null;
                    let parentApiUrl = `/api/navigasi/parents/{{ $currentCategory }}`; // Use $currentCategory from Blade
                    if (editingMenuId) {
                        parentApiUrl += `?editing_menu_id=${editingMenuId}`;
                    }

                    fetchAPI(parentApiUrl)
                        .then(parents => {
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
                        })
                        .catch(error => {
                            showNotification('Gagal memuat daftar parent menu.', 'error');
                            console.error('Kesalahan memuat menu parent:', error);
                        });

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
                        console.error('Elemen navigasi sidebar tidak ada.');
                        return;
                    }
                    try {
                        const data = await fetchAPI(`/api/navigasi/all/{{ $currentCategory }}`); // Use currentCategory
                        sidebarElement.innerHTML = data.html;
                        attachAdminEventListeners(); // Re-attach listeners after content update
                        initSidebarDropdown(); // Re-initialize dropdown logic
                    } catch (error) {
                        showNotification('Gagal memuat ulang sidebar.', 'error');
                        console.error('Kesalahan memuat ulang sidebar:', error);
                    }
                };

                const attachAdminEventListeners = () => {
                    console.log('Menambahkan event listener admin...');

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
                                console.error('Kesalahan mengambil data menu untuk edit:', error);
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
                
                // Kategori Form Submission
                if (categoryForm) {
                    categoryForm.addEventListener('submit', async function (e) {
                        e.preventDefault();

                        const loadingNotif = showNotification('Memproses kategori...', 'loading');

                        const categoryNameInput = document.getElementById('form_category_nama');
                        const categoryName = categoryNameInput.value;
                        const method = document.getElementById('form_category_method').value;
                        const originalCategorySlug = document.getElementById('form_original_category_slug').value; // Get original slug
                        
                        let url = '/kategori'; // For POST (create)
                        let httpMethod = 'POST';

                        if (method === 'PUT') {
                            url = `/kategori/${originalCategorySlug}`; // Use original slug for PUT
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
                            console.error('Kesalahan saat menyimpan menu:', error);
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
                                console.error('Kesalahan menghapus menu:', error);
                                closeDeleteConfirmModal();
                            }
                        }
                    });
                }
                
                if (deleteContentBtn) {
                    deleteContentBtn.addEventListener('click', async () => {
                        const menuId = '{{ $menu_id ?? 0 }}';
                        const contentType = document.getElementById('editor-content-type').value;

                        Swal.fire({
                            title: 'Yakin ingin menghapus konten ini?',
                            text: `Anda akan menghapus konten untuk "${contentType}".`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                const loadingNotif = showNotification('Menghapus konten...', 'loading');
                                try {
                                    const data = await fetchAPI(`/docs/delete/${menuId}`, {
                                        method: 'DELETE',
                                        body: JSON.stringify({ content_type: contentType })
                                    });

                                    hideNotification(loadingNotif);
                                    if (data.success) {
                                        showCentralSuccessPopup(data.success);
                                        window.location.reload();    
                                    } else {
                                        showNotification(data.error || 'Gagal menghapus konten.', 'error');
                                    }
                                } catch (error) {
                                    hideNotification(loadingNotif);
                                    showNotification(`Gagal menghapus konten: ${error.message || 'Terjadi kesalahan'}`, 'error');
                                    console.error('Error deleting content:', error);
                                }
                            }
                        });
                    });
                }

                attachAdminEventListeners();            
            @endif
        @endauth

        // Logic for content tabs (UAT, Pengkodean, Database)
        const contentTabsContainer = document.getElementById('content-tabs');
        const kontenView = document.getElementById('kontenView');
        const editorContainer = document.getElementById('editor-container');
        const editorForm = document.getElementById('editor-form');
        const editorContentTypeInput = document.getElementById('editor-content-type');
        const documentationContent = document.getElementById('documentation-content');
        const cancelEditorBtn = document.getElementById('cancel-editor-btn');
        
        const allDocsContents = @json($allDocsContents ?? []);    
        const initialActiveContentType = new URLSearchParams(window.location.search).get('content_type') || 'Default';

        if (contentTabsContainer) {
            const updateContentDisplay = (contentType) => {
                const contentData = allDocsContents.find(doc => doc.title === contentType);
                if (kontenView) {
                    kontenView.innerHTML = contentData ? contentData.content : '<h3>Konten belum tersedia.</h3><p>Silakan edit untuk menambahkan konten untuk ' + contentType + '.</p>';
                }
                if (editorContentTypeInput) {
                    editorContentTypeInput.value = contentType;
                }
                const url = new URL(window.location);
                url.searchParams.set('content_type', contentType);
                window.history.pushState({}, '', url);
            };

            contentTabsContainer.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', () => {
                    contentTabsContainer.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    const contentType = button.dataset.contentType;
                    updateContentDisplay(contentType);

                    if (!editorContainer.classList.contains('hidden') && typeof currentEditorInstance !== 'undefined' && currentEditorInstance !== null) {
                        const contentToLoad = allDocsContents.find(doc => doc.title === contentType)?.content || '# Belum ada konten untuk ' + contentType;
                        currentEditorInstance.setData(contentToLoad);
                    }
                });
            });

            let initialTabButton = contentTabsContainer.querySelector(`[data-content-type="${initialActiveContentType}"]`);
            if (!initialTabButton && contentTabsContainer.children.length > 0) {
                initialTabButton = contentTabsContainer.children[0];
            }
            if (initialTabButton) {
                initialTabButton.classList.add('active');
                updateContentDisplay(initialTabButton.dataset.contentType);
            }
        }

        if (editorForm) {
            editorForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!currentEditorInstance) {
                    showNotification('Editor belum diinisialisasi.', 'error');
                    return;
                }

                const loadingNotif = showNotification('Menyimpan konten...', 'loading');
                const menuId = '{{ $menu_id ?? 0 }}';    
                const content = currentEditorInstance.getData();
                const currentCategoryForm = document.querySelector('input[name="currentCategoryFromForm"]').value;
                const currentPageForm = document.querySelector('input[name="currentPageFromForm"]').value;
                const contentType = document.getElementById('editor-content-type').value;    

                try {
                    const data = await fetchAPI(`/docs/save/${menuId}`, {
                        method: 'POST',
                        body: JSON.stringify({
                            _token: csrfToken,    
                            content: content,
                            currentCategoryFromForm: currentCategoryForm,
                            currentPageFromForm: currentPageForm,
                            content_type: contentType    
                        })
                    });

                    hideNotification(loadingNotif);
                    showCentralSuccessPopup(data.success || 'Konten berhasil disimpan!');

                    window.location.reload();    

                } catch (error) {
                    console.error('Kesalahan saat menyimpan konten:', error);
                    hideNotification(loadingNotif);
                    showNotification(`Gagal menyimpan konten: ${error.message || 'Terjadi kesalahan'}`, 'error');
                }
            });
        }
        
        if (cancelEditorBtn) {
            cancelEditorBtn.addEventListener('click', closeEditor);
        }

    });

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

    </script>

    {{-- NEW: Success Pop-up Modal (without OK button) --}}
    <div id="central-success-popup" class="modal">
        <div class="modal-content central-popup-content">
            <div class="flex flex-col items-center justify-center p-6">
                <div class="rounded-full bg-green-100 p-4">
                    <svg class="h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-2xl font-bold text-gray-800">Berhasil!</h3>
                <p id="central-popup-message" class="mt-2 text-gray-700 text-center"></p>
            </div>
        </div>
    </div>
</body>
</html>