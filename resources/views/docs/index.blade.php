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
    
    {{-- CKEditor CSS hanya dimuat jika user adalah admin --}}
    @auth
        @if(auth()->user()->role === 'admin') {{-- MENGGUNAKAN auth()->user()->role --}}
            <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
            <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
        @endif
    @endauth

    <style>
        /* ... (CSS Anda yang sudah ada) ... */
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
        
        /* Modal asli untuk Add/Edit Menu */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 4000; visibility: hidden; opacity: 0; transition: visibility 0s, opacity 0.3s; }
        .modal.show { visibility: visible; opacity: 1; }
        .modal-content { background-color: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); width: 90%; max-width: 600px; transform: translateY(-50px); transition: transform 0.3s ease-out; }
        .modal.show .modal-content { transform: translateY(0); }
        
        /* CSS untuk Search Overlay/Modal BARU */
        #search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7); /* Overlay gelap */
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Mulai dari atas */
            padding-top: 5rem; /* Jarak dari atas */
            z-index: 5000; /* Pastikan di atas semua */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        #search-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        #search-modal-content {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 650px; /* Lebar maksimum seperti gambar */
            transform: translateY(-20px);
            transition: transform 0.3s ease-out;
        }
        #search-overlay.open #search-modal-content {
            transform: translateY(0);
        }
        #search-input-container {
            border-bottom: 1px solid #e2e8f0; /* border-b-gray-200 */
        }
        #search-results-list {
            max-height: 400px; /* Tinggi maksimum untuk daftar hasil */
            overflow-y: auto;
            padding: 1rem 0;
        }
        #search-results-list a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #4a5568; /* text-gray-700 */
        }
        #search-results-list a:hover {
            background-color: #edf2f7; /* bg-gray-100 */
        }
        .search-result-category {
            font-size: 0.875rem; /* text-sm */
            font-weight: 600; /* font-semibold */
            color: #2d3748; /* text-gray-800 */
            padding: 0.5rem 1.5rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .search-result-item .search-title {
            font-weight: 500; /* font-medium */
            color: #2c5282; /* text-blue-800 */
        }
        .search-result-item .search-context {
            font-size: 0.875rem; /* text-sm */
            color: #718096; /* text-gray-600 */
            margin-top: 0.25rem;
        }

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

        /* Menambahkan CSS untuk memastikan tinggi item menu yang seragam */
        .sidebar-menu-item-wrapper {
            /* Pastikan elemen ini memiliki tinggi yang konsisten */
            min-height: 40px; /* Contoh tinggi minimum yang sama untuk semua item */
            display: flex; /* Gunakan flexbox untuk alignment yang lebih baik */
            align-items: center; /* Pusatkan secara vertikal */
        }
        .header-content-wrapper {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .header-spacer-left,
        .header-spacer-right {
            flex-grow: 1; /* Coba kembali ke flex-grow: 1, karena kita akan memberi flex-grow ke bagian search */
            display: flex;
            align-items: center;
            min-width: 0; 
        }

        .header-spacer-left {
            justify-content: flex-start;
        }
        .header-spacer-right {
            justify-content: flex-end;
        }

        /* Tambahkan kelas baru untuk kontainer tombol search */
        .search-button-wrapper {
            flex-grow: 1; /* **PERUBAHAN PENTING: Beri flex-grow: 1 pada wrapper search** */
            display: flex; /* Jadikan flex container untuk memusatkan tombol di dalamnya */
            justify-content: center; /* Pusatkan tombol search di dalam wrappernya */
            align-items: center;
            min-width: 0; /* Pastikan bisa menyusut jika perlu */
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen flex-col">
        {{-- Header --}}
        <header class="bg-white shadow-sm w-full border-b border-gray-200 z-20">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center header-content-wrapper">

                    {{-- Bagian Kiri Header (Logo + Kategori) --}}
                    <div class="header-spacer-left space-x-8">
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">ProjekPKL</a>
                        <div class="hidden md:flex items-center space-x-2 rounded-lg bg-gray-100 p-1">
                            <a href="{{ route('docs', ['category' => 'epesantren']) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $currentCategory == 'epesantren' ? 'bg-white text-gray-800 shadow' : 'text-gray-600 hover:bg-gray-200' }}">Epesantren</a>
                            <a href="{{ route('docs', ['category' => 'adminsekolah']) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $currentCategory == 'adminsekolah' ? 'bg-white text-gray-800 shadow' : 'text-gray-600 hover:bg-gray-200' }}">Admin Sekolah</a>
                        </div>
                    </div>

                    {{-- Bagian Tengah Header (untuk Search Button) --}}
                    <div class="search-button-wrapper">
                        <button id="open-search-modal-btn-header" class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                            <span class="flex items-center space-x-2">
                                <i class="fa fa-search text-gray-400"></i>
                                <span>Cari menu & konten...</span>
                            </span>
                            <span class="text-xs text-gray-400">⌘K</span>
                        </button>
                    </div>

                    {{-- Bagian Kanan Header (Login/Logout) --}}
                    <div class="header-spacer-right space-x-4">
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
                {{-- Tombol "Tambah Menu Baru" hanya untuk admin --}}
                @auth
                    @if(auth()->user()->role === 'admin') {{-- MENGGUNAKAN auth()->user()->role --}}
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Navigasi</h2>
                            <button id="add-parent-menu-btn" class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors" title="Tambah Menu Utama Baru">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    @else
                        {{-- Jika bukan admin, hanya tampilkan judul Navigasi tanpa tombol tambah --}}
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Navigasi</h2>
                    @endif
                @endauth
                @guest
                    {{-- Jika belum login, hanya tampilkan judul Navigasi tanpa tombol tambah --}}
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Navigasi</h2>
                @endguest
                
                <div id="notification-container"></div>
                <nav id="sidebar-navigation">
                    {{-- _menu_item akan menangani kondisional untuk tombol edit/delete/add-child --}}
                    @include('docs._menu_item', [
                        'items' => $navigation,
                        'editorMode' => auth()->check() && auth()->user()->role === 'admin', // MENGGUNAKAN auth() helper
                        'selectedNavItemId' => $selectedNavItem->menu_id ?? null
                    ])
                </nav>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                {{-- Bagian ini hanya untuk menampilkan tombol edit/delete jika user adalah admin dan ada menu yang dipilih --}}
                @auth
                    @if(auth()->user()->role === 'admin') {{-- MENGGUNAKAN auth()->user()->role --}}
                        @if (isset($selectedNavItem))
                            <div class="absolute top-8 right-8 z-10">
                                {{-- Tombol Edit/Delete Konten ada di sini atau diinclude dari viewPath --}}
                                {{-- Pastikan tidak ada tombol edit di sini yang memicu editor --}}
                            </div>
                        @endif
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
        @if(auth()->user()->role === 'admin') {{-- MENGGUNAKAN auth()->user()->role --}}
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
        @endif
    @endauth

    {{-- CKEditor scripts - HANYA DIMUAT JIKA USER ADALAH ADMIN --}}
    @auth
        @if(auth()->user()->role === 'admin') {{-- MENGGUNAKAN auth()->user()->role --}}
            <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.umd.js" crossorigin></script>
            <script src="{{ asset('ckeditor/main.js') }}"></script>
        @endif
    @endauth
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
        
        {{-- CKEditor CSS hanya dimuat jika user adalah admin --}}
        @auth
            @if(auth()->user()->role === 'admin')
                <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
                <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
            @endif
        @endauth
    
        <style>
            /* ... (CSS Anda yang sudah ada) ... */
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
            
            /* Modal asli untuk Add/Edit Menu */
            .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 4000; visibility: hidden; opacity: 0; transition: visibility 0s, opacity 0.3s; }
            .modal.show { visibility: visible; opacity: 1; }
            .modal-content { background-color: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); width: 90%; max-width: 600px; transform: translateY(-50px); transition: transform 0.3s ease-out; }
            .modal.show .modal-content { transform: translateY(0); }
            
            /* CSS untuk Search Overlay/Modal BARU */
            #search-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7); /* Overlay gelap */
                display: flex;
                justify-content: center;
                align-items: flex-start; /* Mulai dari atas */
                padding-top: 5rem; /* Jarak dari atas */
                z-index: 5000; /* Pastikan di atas semua */
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            #search-overlay.open {
                opacity: 1;
                visibility: visible;
            }
            #search-modal-content {
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
                width: 90%;
                max-width: 650px; /* Lebar maksimum seperti gambar */
                transform: translateY(-20px);
                transition: transform 0.3s ease-out;
            }
            #search-overlay.open #search-modal-content {
                transform: translateY(0);
            }
            #search-input-container {
                border-bottom: 1px solid #e2e8f0; /* border-b-gray-200 */
            }
            #search-results-list {
                max-height: 400px; /* Tinggi maksimum untuk daftar hasil */
                overflow-y: auto;
                padding: 1rem 0;
            }
            #search-results-list a {
                display: block;
                padding: 0.75rem 1.5rem;
                color: #4a5568; /* text-gray-700 */
            }
            #search-results-list a:hover {
                background-color: #edf2f7; /* bg-gray-100 */
            }
            .search-result-category {
                font-size: 0.875rem; /* text-sm */
                font-weight: 600; /* font-semibold */
                color: #2d3748; /* text-gray-800 */
                padding: 0.5rem 1.5rem;
                margin-top: 1rem;
                margin-bottom: 0.5rem;
                border-bottom: 1px solid #e2e8f0;
            }
            .search-result-item .search-title {
                font-weight: 500; /* font-medium */
                color: #2c5282; /* text-blue-800 */
            }
            .search-result-item .search-context {
                font-size: 0.875rem; /* text-sm */
                color: #718096; /* text-gray-600 */
                margin-top: 0.25rem;
            }
    
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
    
            /* Menambahkan CSS untuk memastikan tinggi item menu yang seragam */
            .sidebar-menu-item-wrapper {
                /* Pastikan elemen ini memiliki tinggi yang konsisten */
                min-height: 40px; /* Contoh tinggi minimum yang sama untuk semua item */
                display: flex; /* Gunakan flexbox untuk alignment yang lebih baik */
                align-items: center; /* Pusatkan secara vertikal */
            }
            .header-content-wrapper {
                display: flex;
                align-items: center;
                width: 100%;
            }
    
            .header-spacer-left,
            .header-spacer-right {
                flex-grow: 1; /* Coba kembali ke flex-grow: 1, karena kita akan memberi flex-grow ke bagian search */
                display: flex;
                align-items: center;
                min-width: 0; 
            }
    
            .header-spacer-left {
                justify-content: flex-start;
            }
            .header-spacer-right {
                justify-content: flex-end;
            }
    
            /* Tambahkan kelas baru untuk kontainer tombol search */
            .search-button-wrapper {
                flex-grow: 1; /* **PERUBAHAN PENTING: Beri flex-grow: 1 pada wrapper search** */
                display: flex; /* Jadikan flex container untuk memusatkan tombol di dalamnya */
                justify-content: center; /* Pusatkan tombol search di dalam wrappernya */
                align-items: center;
                min-width: 0; /* Pastikan bisa menyusut jika perlu */
            }
        </style>
    </head>
    <body class="bg-gray-100">
        <div class="flex h-screen flex-col">
            {{-- Header --}}
            <header class="bg-white shadow-sm w-full border-b border-gray-200 z-20">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center header-content-wrapper">
    
                        {{-- Bagian Kiri Header (Logo + Kategori) --}}
                        <div class="header-spacer-left space-x-8">
                            <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">ProjekPKL</a>
                            <div class="hidden md:flex items-center space-x-2 rounded-lg bg-gray-100 p-1">
                                <a href="{{ route('docs', ['category' => 'epesantren']) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $currentCategory == 'epesantren' ? 'bg-white text-gray-800 shadow' : 'text-gray-600 hover:bg-gray-200' }}">Epesantren</a>
                                <a href="{{ route('docs', ['category' => 'adminsekolah']) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $currentCategory == 'adminsekolah' ? 'bg-white text-gray-800 shadow' : 'text-gray-600 hover:bg-gray-200' }}">Admin Sekolah</a>
                            </div>
                        </div>
    
                        {{-- Bagian Tengah Header (untuk Search Button) --}}
                        <div class="search-button-wrapper">
                            <button id="open-search-modal-btn-header" class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                                <span class="flex items-center space-x-2">
                                    <i class="fa fa-search text-gray-400"></i>
                                    <span>Cari menu & konten...</span>
                                </span>
                                <span class="text-xs text-gray-400">⌘K</span>
                            </button>
                        </div>
    
                        {{-- Bagian Kanan Header (Login/Logout) --}}
                        <div class="header-spacer-right space-x-4">
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
                            {{-- Jika bukan admin, hanya tampilkan judul Navigasi tanpa tombol tambah --}}
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Navigasi</h2>
                        @endif
                    @endauth
                    @guest
                        {{-- Jika belum login, hanya tampilkan judul Navigasi tanpa tombol tambah --}}
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Navigasi</h2>
                    @endguest
                    
                    <div id="notification-container"></div>
                    <nav id="sidebar-navigation">
                        {{-- _menu_item akan menangani kondisional untuk tombol edit/delete/add-child --}}
                        @include('docs._menu_item', [
                            'items' => $navigation,
                            'editorMode' => auth()->check() && (auth()->user()->role ?? '') === 'admin',
                            'selectedNavItemId' => $selectedNavItem->menu_id ?? null
                        ])
                    </nav>
                </aside>
    
                {{-- Main Content --}}
                <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                    {{-- Bagian ini hanya untuk menampilkan tombol edit/delete jika user adalah admin dan ada menu yang dipilih --}}
                    @auth
                        @if(auth()->user()->role === 'admin')
                            @if (isset($selectedNavItem))
                                <div class="absolute top-8 right-8 z-10">
                                    {{-- Tombol Edit/Delete Konten ada di sini atau diinclude dari viewPath --}}
                                    {{-- Pastikan tidak ada tombol edit di sini yang memicu editor --}}
                                </div>
                            @endif
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
        // Ini adalah satu-satunya DOMContentLoaded listener
        document.addEventListener('DOMContentLoaded', () => {
            // =================================
            // VARIABEL & FUNGSI UTILITAS (GLOBAL)
            // =================================
            // Definisi variabel global yang akan diakses oleh semua bagian skrip.
            // Dideklarasikan di sini agar pasti ada saat DOMContentLoaded.
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const modalTitleElement = document.getElementById('modal-title'); // ini bisa null jika bukan admin
            const formCategoryElement = document.getElementById('form_category'); // ini bisa null jika bukan admin
            const currentCategory = formCategoryElement ? formCategoryElement.value : 'epesantren';
    
            // Fungsi utilitas yang bersifat global
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
                options.headers = { ...defaultHeaders, ...options.headers };
    
                try {
                    const response = await fetch(url, options);
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Fetch API Error:', error);
                    throw error;
                }
            };
    
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
    
            if (clearSearchInputBtn) {
                clearSearchInputBtn.addEventListener('click', () => {
                    searchOverlayInput.value = '';
                    searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>';
                    clearSearchInputBtn.classList.add('hidden');
                    searchOverlayInput.focus();
                });
            }
    
            let searchTimeout;
            if (searchOverlayInput) {
                searchOverlayInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    const query = searchOverlayInput.value.trim();
                    
                    if (query.length > 0) {
                        clearSearchInputBtn.classList.remove('hidden');
                    } else {
                        clearSearchInputBtn.classList.add('hidden');
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
                            console.error('Search API Error:', error);
                        }
                    }, 300);
                });
            }
    
    
            // =================================
            // LOGIKA DROPDOWN SIDEBAR (untuk semua user)
            // =================================
    
            const sidebar = document.getElementById('sidebar-navigation');
            if (sidebar) {
                sidebar.addEventListener('click', (e) => {
                    const trigger = e.target.closest('.menu-arrow-icon');
                    if (!trigger) return;
    
                    e.preventDefault();
    
                    const submenuId = trigger.dataset.toggle;
                    const submenu = document.getElementById(submenuId);
                    const icon = trigger.querySelector('i');
    
                    if (submenu) {
                        const isCurrentlyOpen = submenu.classList.contains('open');
    
                        const allOpenSubmenus = sidebar.querySelectorAll('.submenu-container.open');
                        allOpenSubmenus.forEach(openSubmenu => {
                            if (openSubmenu !== submenu && !submenu.contains(openSubmenu) && !openSubmenu.contains(submenu)) {
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
                });
            }
    
            const initSidebarDropdown = () => {
                const sidebarElement = document.getElementById('sidebar-navigation');
                if (!sidebarElement) return;
    
                const openActiveMenuParents = () => {
                    const activeItemContainer = sidebarElement.querySelector('.bg-blue-100')?.closest('.my-1');
                    if (activeItemContainer) {
                        let currentSubmenu = activeItemContainer.closest('.submenu-container');
                        while (currentSubmenu) {
                            currentSubmenu.classList.add('open');
                            const triggerButton = sidebarElement.querySelector(`[data-toggle="${currentSubmenu.id}"]`);
                            if (triggerButton) {
                                const icon = triggerButton.querySelector('i');
                                if (icon) {
                                    icon.classList.add('open');
                                    triggerButton.setAttribute('aria-expanded', 'true');
                                }
                            }
                            currentSubmenu = currentSubmenu.parentElement.closest('.submenu-container');
                        }
                    }
                };
    
                openActiveMenuParents();
            };
    
            initSidebarDropdown(); // Panggil ini di dalam DOMContentLoaded utama agar selalu berfungsi
    
            // =================================
            // LOGIKA ADMIN (MODAL & CRUD) - HANYA DIJALANKAN JIKA USER ADALAH ADMIN
            // =================================
            @auth
                @if(auth()->user()->role === 'admin')
                    // VARIABEL & ELEMEN KHUSUS ADMIN (Pastikan ini ada di DOM)
                    const menuModal = document.getElementById('menu-modal');
                    const menuForm = document.getElementById('menu-form');
                    // Menggunakan `modalTitleElement` dan `formCategoryElement` yang sudah didefinisikan di atas,
                    // karena mereka akan diakses di sini setelah DOM siap, dan akan memiliki nilai null jika elemennya tidak ada (untuk non-admin).
                    // Tapi karena blok ini hanya untuk admin, elemennya seharusnya selalu ada.
    
                    const openMenuModal = (mode, menuData = null, parentId = 0) => {
                        if (!menuForm || !modalTitleElement) { // Safety check
                            showNotification('Elemen form menu tidak ditemukan.', 'error');
                            console.error('Admin menu form elements are missing from DOM.');
                            return;
                        }
                        menuForm.reset();
                        document.getElementById('form_menu_id').value = '';
                        document.getElementById('form_method').value = mode === 'edit' ? 'PUT' : 'POST';
    
                        const formMenuChildSelect = document.getElementById('form_menu_child');
                        formMenuChildSelect.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>';
    
                        const editingMenuId = mode === 'edit' && menuData ? menuData.menu_id : null;
                        let parentApiUrl = `/api/navigasi/parents/${currentCategory}`; // Menggunakan currentCategory global
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
                                    document.getElementById('form_menu_status').checked = true;
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
                                console.error('Error loading parent menus:', error);
                            });
    
                        menuModal.classList.add('show');
                    };
    
                    const closeMenuModalAdmin = () => menuModal.classList.remove('show');
    
                    const refreshSidebar = async () => {
                        const sidebarElement = document.getElementById('sidebar-navigation');
                        if (!sidebarElement) {
                            showNotification('Gagal memuat ulang sidebar: Elemen navigasi tidak ditemukan.', 'error');
                            console.error('Sidebar navigation element is missing.');
                            return;
                        }
                        try {
                            const data = await fetchAPI(`/api/navigasi/all/${currentCategory}`); // Menggunakan currentCategory global
                            sidebarElement.innerHTML = data.html;
                            attachAdminEventListeners(); // RE-ATTACH CRUD LISTENERS AFTER NEW HTML IS LOADED
                            initSidebarDropdown();       // RE-INITIALIZE DROPDOWN STATE (open active parents)
                            showNotification('Sidebar berhasil diperbarui!', 'success');
                        } catch (error) {
                            showNotification('Gagal memuat ulang sidebar.', 'error');
                            console.error('Error refreshing sidebar:', error);
                        }
                    };
    
                    const attachAdminEventListeners = () => {
                        console.log('Attaching admin event listeners...');
    
                        // Pastikan elemen-elemen ini ada sebelum attach event listeners
                        const addParentMenuBtn = document.getElementById('add-parent-menu-btn');
                        if (addParentMenuBtn) {
                            addParentMenuBtn.addEventListener('click', () => openMenuModal('create', null, 0));
                        }
                        const cancelMenuFormBtn = document.getElementById('cancel-menu-form-btn');
                        if (cancelMenuFormBtn) {
                            cancelMenuFormBtn.addEventListener('click', closeMenuModalAdmin);
                        }
    
                        // Edit Button
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
    
                        // Delete Button
                        document.querySelectorAll('.delete-menu-btn').forEach(button => {
                            button.addEventListener('click', (e) => {
                                e.stopPropagation();
                                const menuId = e.currentTarget.dataset.menuId;
                                const menuNama = e.currentTarget.dataset.menuNama;
                                if (confirm(`Yakin ingin menghapus menu "${menuNama}"? Ini akan menghapus semua sub-menunya.`)) {
                                    fetchAPI(`/api/navigasi/${menuId}`, { method: 'DELETE' })
                                        .then(data => {
                                            showNotification(data.success, 'success');
                                            refreshSidebar();
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
                            button.addEventListener('click', (e) => {
                                e.stopPropagation();
                                const parentId = e.currentTarget.dataset.parentId;
                                openMenuModal('create', null, parentId);
                            });
                        });
                    };
                    
                    // Event listener untuk submit form menu
                    if (menuForm) {
                        menuForm.addEventListener('submit', async (e) => {
                            e.preventDefault();
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
                                showNotification(data.success, 'success');
                                closeMenuModalAdmin();
                                refreshSidebar();
                            } catch (error) {
                                console.error('Error saat menyimpan menu:', error);
                                if (error.message) {
                                    showNotification(`Gagal menyimpan: ${error.message}`, 'error');
                                } else {
                                    showNotification('Terjadi kesalahan tidak dikenal saat menyimpan menu.', 'error');
                                }
                            }
                        });
    
                        // Panggil attachAdminEventListeners hanya jika menuForm ada
                        attachAdminEventListeners(); 
                    }
                @endif {{-- End of @if(auth()->user()->role === 'admin') --}}
            @endauth {{-- End of @auth for Admin Logic --}}
    
        }); // End of main DOMContentLoaded listener
        </script>
    </body>
    </html>  
</body>
</html>