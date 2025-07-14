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
    
    {{-- CKEditor CSS hanya dimuat jika user adalah admin --}}
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

        /* Notifikasi Pop-up */
        .notification-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) translateY(-20px);
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 5000;
            opacity: 0;
            transition: all 0.3s ease-out;
            font-size: 1rem;
            display: flex;
            align-items: center;
            min-width: 250px;
            max-width: 90%;
        }
        .notification-message.show {
            opacity: 1;
            transform: translate(-50%, -50%) translateY(0);
        }
        .notification-message.success { background-color: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .notification-message.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* CSS untuk Notifikasi Loading */
        .notification-message.loading {
            background-color: #e0f2fe; /* Light blue background */
            color: #0c4a6e; /* Darker blue text */
            border: 1px solid #60a5fa; /* Blue border */
        }

        .notification-message .notification-icon {
            margin-right: 0.75rem;
            font-size: 1.5rem;
            line-height: 1;
        }

        /* CSS untuk efek spinner loading */
        .notification-icon.fa-spin {
            animation: fa-spin 1s infinite linear;
        }

        @keyframes fa-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modal asli untuk Add/Edit Menu */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 4000; visibility: hidden; opacity: 0; transition: visibility 0s, opacity 0.3s; }
        .modal.show { visibility: visible; opacity: 1; }
        .modal-content { background-color: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); width: 90%; max-width: 600px; transform: translateY(-50px); transition: transform 0.3s ease-out; }
        .modal.show .modal-content { transform: translateY(0); }
        
        /* CSS for the new central success popup */
        .modal-content.central-popup-content {
            max-width: 400px; /* Lebar maksimum seperti gambar */
            padding: 2rem; /* Sesuaikan padding */
            text-align: center;
            /* Pastikan transisi untuk muncul/hilang halus */
            transform: translateY(-50px);
            transition: transform 0.3s ease-out;
        }
        /* Style when the modal is shown */
        #central-success-popup.show .central-popup-content {
            transform: translateY(0);
        }
        /* Tambahan: Pastikan background modal transparan untuk pop-up ini */
        #central-success-popup {
            background-color: rgba(0, 0, 0, 0.2); /* Sedikit transparan agar konten di belakang masih terlihat samar */
        }
        /* Style untuk teks "Berhasil!" */
        .central-popup-content h3 {
            font-size: 2rem; /* Menyesuaikan ukuran font dengan screenshot */
            font-weight: 700; /* Font bold */
            color: #2d3748; /* Warna teks gray-800 */
        }
        /* Style untuk pesan di bawah "Berhasil!" */
        .central-popup-content p {
            font-size: 1.125rem; /* Ukuran font lebih besar dari default p */
            color: #4a5568; /* Warna teks gray-700 */
        }

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
        .judul-halaman{ 
            margin: 10px 10px 10px 0; 
            display: flex;
            gap: 12px;
        }
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
        /* CSS for the new delete confirmation modal */
        #delete-confirm-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 6000; /* Higher than other modals */
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s;
        }
        #delete-confirm-modal.show {
            visibility: visible;
            opacity: 1;
        }
        #delete-confirm-modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 450px;
            text-align: center;
            transform: translateY(-50px);
            transition: transform 0.3s ease-out;
        }
        #delete-confirm-modal.show #delete-confirm-modal-content {
            transform: translateY(0);
        }

        /* Dropdown untuk Kategori di Header */
        .header-dropdown-menu {
            display: none;
            position: absolute;
            background-color: white; /* Warna latar belakang putih */
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 0.5rem;
            overflow: hidden;
            top: 100%; /* Posisi di bawah tombol induk */
            left: 0;
            margin-top: 0.5rem;
            border: 1px solid #4a5568; /* Garis luar gelap (warna gray-700 dari Tailwind) */
        }

        .header-dropdown-menu.open {
            display: block;
        }

        .header-dropdown-menu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .header-dropdown-menu a:hover {
            background-color: #f1f1f1;
        }

        /* Kustomisasi untuk tombol dropdown Kategori */
        #category-dropdown-btn {
            background: linear-gradient(135deg, #4f46e5, #3b82f6); /* ungu ke biru */
            color: #fff;
            border: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        #category-dropdown-btn:hover {
            background: linear-gradient(135deg, #6366f1, #60a5fa);
            transform: translateY(-1px);
        }

        /* Dropdown menu */
        #category-dropdown-menu {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 4px 0;
        }

        /* Link dalam dropdown */
        #category-dropdown-menu a {
            padding: 10px 16px;
            display: block;
            font-size: 14px;
            color: #4b5563;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease;
            position: relative;
        }

        #category-dropdown-menu a:hover {
            background-color: #f3f4f6;
            color: #111827;
        }

        #category-dropdown-btn .fa-chevron-down,
        #category-dropdown-btn .fa-chevron-up {
            margin-left: 8px; /* Jarak antara teks dan ikon */
            font-size: 0.75rem; /* Ukuran ikon lebih kecil */
            transition: transform 0.2s ease-in-out;
        }

        /* Aktif state */
        #category-dropdown-menu a.bg-gray-100 {
            background-color: #e0f2fe !important;
            color: #1d4ed8 !important;
            font-weight: 600;
        }

        /* Tambahan efek garis warna samping aktif */
        #category-dropdown-menu a.bg-gray-100::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background-color: #3b82f6;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .hidden {
            display: none;
        }

        #editBtn:hover{
            color: blue;

        }
        /* Tombol Logout */
        .logout-btn {
            background-color: #ef4444; /* merah */
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .logout-btn:hover {
            background-color: #dc2626; /* merah lebih gelap saat hover */
            transform: translateY(-1px);
        }

        /* Responsif tambahan opsional */
        @media (max-width: 640px) {
            .header-spacer-right {
                flex-direction: column;
                gap: 8px;
            }

            aside {
                position: fixed;
                top: 4rem; /* tinggi header */
                left: 0;
                height: calc(100% - 4rem);
                background: white;
                z-index: 40;
                width: 16rem; /* 256px */
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            aside.show {
                transform: translateX(0);
            }
            .backdrop {
                display: none;
            }
            .backdrop.show {
                display: block;
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.4);
                z-index: 30;
            }

            .search-button-wrapper {
                max-width: auto ;
                justify-content: flex-end;
                align-content: flex-end;
                align-items: flex-end;
            }
            
            .text-search{
                display: none;
                max-width: 0;
            }

            .page-sidebar{
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
            }

            .page-title{
            display: flex;
            }

            .title-page{
                display: none;
            }
            #search-icon{
                background-color: none;
                flex-grow: 1;
            }
            #open-search-modal-btn-header{
                align-items: center;
                display:block;
            }
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

                        <button id="mobile-menu-toggle" class="md:hidden text-gray-600 focus:outline-none focus:text-gray-900">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                        <a href="{{ route('docs', ['category' => $currentCategory]) }}" class="text-2xl font-bold text-blue-600">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</a>
                                                
                        @php use Illuminate\Support\Str; @endphp

                        <div class="relative hidden md:block">
                            <button id="category-dropdown-btn" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none">
                                <span id="category-button-text">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span>
                                <i class="ml-2 fa fa-chevron-down text-xs"></i>
                            </button>

                            <div id="category-dropdown-menu" class="header-dropdown-menu">
                                @foreach ($categories as $cats)
                                    @php
                                        $slug = Str::slug($cats); // Slug untuk URL dan data attribute
                                        $isActive = $currentCategory === $slug;
                                    @endphp
                                    <a href="{{ route('docs', ['category' => $slug]) }}"
                                    class="px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-category-key="{{ $slug }}"
                                    data-category-name="{{ $cats }}">
                                     {!! ucwords(str_replace('-',' ',$cats)) !!}
                                    </a>
                                @endforeach
                                @auth
                                    @if(auth()->user()->role === 'admin')
                                        <div class="border-t border-gray-200 my-1"></div>
                                        
                                        <button onclick="openCategoryModal()" class="text-blue-600 hover:underline text-sm" style="padding:10px;">
                                            + Tambah Kategori
                                        </button>
                                    @endif
                                @endauth
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
                    
                            <button id="category-dropdown-btn" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none">
                                <span id="category-button-text">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span>
                                <i class="ml-2 fa fa-chevron-down text-xs"></i>
                            </button>

                            <div id="category-dropdown-menu" class="header-dropdown-menu">
                                @foreach ($categories as $cats)
                                    @php
                                        $slug = Str::slug($cats); // Slug untuk URL dan data attribute
                                        $isActive = $currentCategory === $slug;
                                    @endphp
                                    <a href="{{ route('docs', ['category' => $slug]) }}"
                                    class="px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-category-key="{{ $slug }}"
                                    data-category-name="{{ $cats }}">
                                     {!! ucwords(str_replace('-',' ',$cats)) !!}
                                    </a>
                                @endforeach
                                @auth
                                    @if(auth()->user()->role === 'admin')
                                        <div class="border-t border-gray-200 my-1"></div>
                                        
                                        <button onclick="openCategoryModal()" class="text-blue-600 hover:underline text-sm" style="padding:10px;">
                                            + Tambah Kategori
                                        </button>
                                    @endif
                                @endauth
                            </div>
                     </div>
                </div>

    {{-- Navigasi Utama --}
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
                        'selectedNavItemId' => $selectedNavItem->menu_id ?? null
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
                            @if(isset($menu_id) && $menu_id > 0)
                                <button id="editBtn" onclick="openEditor()"><i class="fa-solid fa-file-pen"></i></button>
                            @endif
                        @endif
                    @endauth
                </div>

                {{-- Konten Dokumentasi dan Editor (berada langsung di index.blade.php) --}}
                <div class="prose max-w-none" id="documentation-content">
                    {{-- Tampilan konten dari database --}}
                    <div id="kontenView" class="ck-content">
                        {{-- Menggunakan $contentDocs untuk menampilkan konten --}}
                        {!! $contentDocs->docsContent->content ?? "Konten Belum Tersedia" !!}
                    </div>

                    {{-- Editor CKEditor (hanya untuk admin dan jika ada menu_id yang valid) --}}
                    @auth
                        @if(auth()->user()->role === 'admin')
                            {{-- Editor hanya ditampilkan jika ada item navigasi yang dipilih (menu_id > 0) --}}
                            @if(isset($menu_id) && $menu_id > 0)
                                <div class="main-container">
                                    <div class="editor-container hidden" id="editor-container">
                                        <form id="editor-form" action="{{ route('docs.save', ['menu_id' => $menu_id]) }}" method="POST">
                                            @csrf
                                            {{-- Tambahkan hidden input untuk category dan page untuk redirect setelah save --}}
                                            <input type="hidden" name="currentCategoryFromForm" value="{{ $currentCategory }}">
                                            <input type="hidden" name="currentPageFromForm" value="{{ $currentPage }}">

                                            <textarea name="content" id="editor" class="ckeditor">
                                                {{ $contentDocs->docsContent->content ?? "" }}
                                            </textarea>
                                            <div class="buttons">
                                                <button type="submit" class="btn btn-simpan">Update</button>
                                                <button type="button" id="cancel-editor-btn" class="btn btn-batal"><a href="{{ route('docs', ['category' => $currentCategory, 'page' => $currentPage]) }}">Batal</a></button>
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
                                <input type="checkbox" id="form_menu_status" name="menu_status" value="0" class="form-checkbox h-5 w-5 text-blue-600">
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
                        <!-- Modal Tambah Kategori -->
            <div id="categoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                    <h2 id="categoryModalTitle" class="text-lg font-semibold mb-4">Tambah Kategori</h2>

                    <form id="categoryForm">
                        @csrf
                        <input type="hidden" id="form_category_method" name="_method" value="POST">

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
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-toggle^="submenu-"]').forEach(el => {
                el.addEventListener('click', () => {
                    const targetId = el.getAttribute('data-toggle');
                    const submenu = document.getElementById(targetId);
                    const icon = el.closest('.sidebar-menu-item-wrapper')?.querySelector('.fa-chevron-left');

                    if (submenu) {
                        const expanded = el.getAttribute('aria-expanded') === 'true';
                        el.setAttribute('aria-expanded', !expanded);
                        submenu.classList.toggle('hidden');

                        if (icon) {
                            icon.classList.toggle('rotate-[-90deg]', !expanded);
                            icon.classList.toggle('rotate-0', expanded);
                        }
                    }
                });
            });
        });
    </script>

    

    <script>
        function openEditor() {
            const kontenView = document.getElementById('kontenView');
            const editorContainer = document.getElementById('editor-container');

        // Sembunyikan tampilan view dan tampilkan editor
            kontenView.classList.add('hidden');
            editorContainer.classList.remove('hidden');

        // Inisialisasi CKEditor jika belum
            if (!CKEDITOR.instances.editor) {
                CKEDITOR.replace('editor');
            }
        }
        function promptNewCategory() {
            const name = prompt("Masukkan nama kategori baru:");
            if (name && name.trim() !== '') {
                const slug = name.trim().toLowerCase().replace(/\s+/g, '-');
                window.location.href = `/docs/${slug}`;
            }
        }

        const categoryModal = document.getElementById('categoryModal');
        const categoryForm = document.getElementById('categoryForm');
        const categoryModalTitle = document.getElementById('categoryModalTitle');

        function openCategoryModal(mode = 'create', defaultName = '') {
            if (!categoryForm || !categoryModalTitle) {
                alert('Form kategori tidak ditemukan!');
                return;
            }

            categoryForm.reset();
            document.getElementById('form_category_method').value = mode === 'edit' ? 'PUT' : 'POST';
            document.getElementById('form_category_nama').value = defaultName;

            categoryModalTitle.textContent = mode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori';
            categoryModal.classList.remove('hidden');
        }

        function closeCategoryModal() {
            categoryModal.classList.add('hidden');
        }

        categoryForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const categoryName = document.getElementById('form_category_nama').value;
            const method = document.getElementById('form_category_method').value;

            fetch('/kategori/store', {
                method: method === 'PUT' ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ category: categoryName }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Kategori berhasil ditambahkan.');
                    closeCategoryModal();
                    location.reload(); // atau perbarui dropdown kategori
                } else {
                    alert('Gagal menambahkan kategori.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan.');
            });
        });



    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // =================================
        // Verifikasi Logout
        // =================================
        const logoutForm = document.getElementById('logout-form');
        const logoutBtn = document.getElementById('logout-btn');

        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault(); // selalu hentikan submit dulu

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
                    logoutForm.submit(); // hanya submit jika dikonfirmasi
                }
            });
        });

        // =================================
        // VARIABEL & FUNGSI UTILITAS (GLOBAL)
        // =================================
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const modalTitleElement = document.getElementById('modal-title');
        const formCategoryElement = document.getElementById('form_category');
        const currentCategory = formCategoryElement ? formCategoryElement.value : 'epesantren';

        // === Notifikasi Toast Kecil (untuk loading/error) ===
        const showNotification = (message, type = 'success', duration = 3000) => {
            const container = document.getElementById('notification-container');
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
        };

        const hideNotification = (notifInfo) => {
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
        };

        // === Fungsi Fetch API ===
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
                    throw new Error(errorData.message || `Kesalahan HTTP! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Kesalahan Fetch API:', error);
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
                        console.error('Kesalahan API Pencarian:', error);
                    }
                }, 300);
            });
        }

        // =================================
        // LOGIKA DROPDOWN SIDEBAR (untuk semua user)
        // =================================

        const sidebar = document.getElementById('sidebar-navigation');
        const currentPathname = window.location.pathname; // Dapatkan path URL saat ini
        const urlParams = new URLSearchParams(window.location.search);
        const selectedMenuIdFromUrl = urlParams.get('page'); // Asumsi 'page' param mengandung menu_id yang dipilih

        if (sidebar) {
            sidebar.addEventListener('click', (e) => {
                const trigger = e.target.closest('.menu-arrow-icon');
                const menuLink = e.target.closest('.menu-link'); // Tangkap klik pada link menu juga

                // Handle dropdown toggle for parent menus
                if (trigger) {
                    e.preventDefault();

                    const submenuId = trigger.dataset.toggle;
                    const submenu = document.getElementById(submenuId);
                    const icon = trigger.querySelector('i');

                    if (submenu) {
                        const isCurrentlyOpen = submenu.classList.contains('open');

                        // Close other top-level submenus to avoid too many open at once
                        // This part is good, keep it as is if you want only one top-level submenu open
                        const allOpenSubmenus = sidebar.querySelectorAll('.submenu-container.open');
                        allOpenSubmenus.forEach(openSubmenu => {
                            // Only close if it's not the current submenu being toggled
                            // and not a parent of the current submenu being toggled
                            if (openSubmenu !== submenu && !submenu.contains(openSubmenu)) {
                                // Check if openSubmenu is NOT an ancestor of the currently selected item
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
                            }
                        });

                        submenu.classList.toggle('open');
                        trigger.setAttribute('aria-expanded', isCurrentlyOpen ? 'false' : 'true');
                        if (icon) {
                            icon.classList.toggle('open', !isCurrentlyOpen);
                        }
                    }
                } else if (menuLink) {
                    // If a menu link is clicked, ensure its parent is open before navigation
                    // This is less about keeping it open after reload, and more about visual state before navigation
                    // However, the `openActiveMenuParents` will handle persistence after reload.
                    // No `e.preventDefault()` here if you want the link to navigate normally.
                }
            });
        }

        const initSidebarDropdown = () => {
            const sidebarElement = document.getElementById('sidebar-navigation');
            if (!sidebarElement) return;

            const openActiveMenuParents = () => {
                // Find the currently active menu item based on the selectedNavItemId from Blade
                // We assume $selectedNavItem->menu_id is passed from Laravel to JS via Blade.
                // Example: const selectedMenuItemElement = sidebarElement.querySelector(`[data-menu-id="${selectedNavItemId}"]`);
                // You already have a .bg-blue-100 class for the active item.
                const activeItemElement = sidebarElement.querySelector('.bg-blue-100');

                if (activeItemElement) {
                    // Find all parent submenu containers for the active item
                    let currentElement = activeItemElement;
                    while (currentElement && currentElement !== sidebarElement) {
                        if (currentElement.classList.contains('submenu-container')) {
                            currentElement.classList.add('open');
                            // Also update the arrow icon of its trigger
                            const triggerButton = sidebarElement.querySelector(`[data-toggle="${currentElement.id}"]`);
                            if (triggerButton) {
                                const icon = triggerButton.querySelector('i');
                                if (icon) {
                                    icon.classList.add('open'); // Rotate the arrow
                                    triggerButton.setAttribute('aria-expanded', 'true');
                                }
                            }
                        }
                        currentElement = currentElement.parentElement; // Move up the DOM tree
                    }
                }
            };

            openActiveMenuParents();
        };

        initSidebarDropdown();

        // =================================
        // LOGIKA DROPDOWN KATEGORI HEADER
        // =================================
        const button = document.getElementById('category-dropdown-btn');
        const menu = document.getElementById('category-dropdown-menu');

        button.addEventListener('click', () => {
            const isVisible = menu.classList.contains('opacity-100');

            if (isVisible) {
                menu.classList.remove('opacity-100', 'visible');
                menu.classList.add('opacity-0', 'invisible');
            } else {
                menu.classList.remove('opacity-0', 'invisible');
                menu.classList.add('opacity-100', 'visible');
            }
        });

        // Optional: close menu if clicking outside
        document.addEventListener('click', function (e) {
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('opacity-100', 'visible');
                menu.classList.add('opacity-0', 'invisible');
            }
        });
        const categoryDropdownBtn = document.getElementById('category-dropdown-btn');
        const categoryDropdownText = document.getElementById('category-button-text'); // Dapatkan elemen span
        const categoryDropdownMenu = document.getElementById('category-dropdown-menu');

        // Dapatkan nama kategori saat ini dari Laravel ($currentCategory)
        // Ini akan mengambil nilai dari PHP saat halaman dimuat
        const currentCategoryFromBlade = "{{ $currentCategory ?? 'default' }}"; 

        // Fungsi untuk memperbarui teks tombol dropdown
        const updateCategoryButtonText = (categoryKey) => {
            let categoryDisplayName = 'Kategori'; // Default jika tidak cocok
            if (categoryKey === 'epesantren') {
                categoryDisplayName = 'Epesantren';
            } else if (categoryKey === 'adminsekolah') {
                categoryDisplayName = 'Admin Sekolah';
            }
            // Tambahkan kondisi lain jika ada kategori baru di masa mendatang
            // else if (categoryKey === 'nama_kategori_baru') {
            //     categoryDisplayName = 'Nama Kategori Baru';
            // }

            if (categoryDropdownText) {
                categoryDropdownText.textContent = categoryDisplayName;
            }
        };

        // Panggil fungsi ini saat DOM dimuat untuk mengatur teks awal
        // Ini adalah bagian penting yang memastikan teks tombol benar setelah reload halaman
        updateCategoryButtonText(currentCategoryFromBlade);


        if (categoryDropdownBtn && categoryDropdownMenu) {
            categoryDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Mencegah event click menyebar ke document dan langsung menutup dropdown
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

            // Close the dropdown if the user clicks outside of it
            document.addEventListener('click', (event) => {
                if (!categoryDropdownBtn.contains(event.target) && !categoryDropdownMenu.contains(event.target)) {
                    categoryDropdownMenu.classList.remove('open');
                    const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                    if (chevronIcon) { // Pastikan ikon ada sebelum mencoba menghapus kelas
                        chevronIcon.classList.remove('fa-chevron-up');
                        chevronIcon.classList.add('fa-chevron-down');
                    }
                }
            });

            // Menangani klik pada item dropdown
            categoryDropdownMenu.querySelectorAll('a').forEach(item => {
                item.addEventListener('click', (e) => {
                    // TIDAK perlu e.preventDefault() karena kita ingin link navigasi berjalan
                    // Ambil category key dari atribut href
                    const href = item.getAttribute('href'); // Gunakan 'item' bukan 'e.target' untuk memastikan ini adalah <a>
                    const url = new URL(href);
                    const newCategoryKey = url.searchParams.get('category'); // Dapatkan nilai 'category' dari URL

                    if (newCategoryKey) {
                        updateCategoryButtonText(newCategoryKey); // Update teks tombol berdasarkan key baru
                    }

                    // Tutup dropdown setelah item diklik
                    categoryDropdownMenu.classList.remove('open');
                    const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                    if (chevronIcon) {
                        chevronIcon.classList.remove('fa-chevron-up');
                        chevronIcon.classList.add('fa-chevron-down');
                    }
                    // Navigasi ke URL yang dipilih akan terjadi secara otomatis karena ini adalah tag <a>
                });
            });
        }

        // =================================
        // NEW CENTRAL SUCCESS POPUP LOGIC
        // =================================
        const centralSuccessPopup = document.getElementById('central-success-popup');
        const centralPopupMessage = document.getElementById('central-popup-message');

        const showCentralSuccessPopup = (message) => {
            if (centralPopupMessage) {
                centralPopupMessage.textContent = message;
            }
            if (centralSuccessPopup) {
                centralSuccessPopup.classList.add('show');
                // Auto-hide after 1 second
                setTimeout(() => {
                    centralSuccessPopup.classList.remove('show');
                }, 1000); // 1000 ms = 1 detik
            }
        };

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
                const categoryModal = document.getElementById('categoryModal');
                const categoryForm = document.getElementById('categoryForm');
                const categoryModalTitle = document.getElementById('categoryModalTitle');

                let menuToDelete = null;

                function openCategoryModal(mode = 'create', defaultName = '') {
                    if (!categoryForm || !categoryModalTitle) {
                        alert('Form kategori tidak ditemukan!');
                        return;
                    }

                    categoryForm.reset();
                    document.getElementById('form_category_method').value = mode === 'edit' ? 'PUT' : 'POST';
                    document.getElementById('form_category_nama').value = defaultName;

                    categoryModalTitle.textContent = mode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori';
                    categoryModal.classList.remove('hidden');
                }

                function closeCategoryModal() {
                categoryModal.classList.add('hidden');
            }
                

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
                    let parentApiUrl = `/api/navigasi/parents/${currentCategory}`;
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
                        const data = await fetchAPI(`/api/navigasi/all/${currentCategory}`);
                        sidebarElement.innerHTML = data.html;
                        attachAdminEventListeners();
                        initSidebarDropdown();
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
                            showCentralSuccessPopup(data.success); // Use the new central success popup
                            
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
                                showCentralSuccessPopup(data.success); // Use the new central success popup
                                
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

                attachAdminEventListeners(); 
            @endif
        @endauth

    });

        // Responsive Side Menu
    
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebarElement = document.querySelector('aside');
    const backdrop = document.getElementById('sidebar-backdrop');

    const toggleSidebar = () => {
        sidebarElement.classList.toggle('show');
        backdrop.classList.toggle('show');
    };

    // Toggle open/close
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