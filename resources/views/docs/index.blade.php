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
    @auth
        @if(auth()->user()->role === 'admin')
            <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
            <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
        @endif
    @endauth

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
        /* Styles for content tabs */
        .content-tabs {
            display: flex;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .content-tabs button {
            padding: 0.75rem 1rem;
            border: none;
            background-color: transparent;
            cursor: pointer;
            font-weight: 500;
            color: #6b7280; /* gray-500 */
            transition: all 0.2s ease-in-out;
            position: relative;
            outline: none;
        }

        .content-tabs button:hover {
            color: #1f2937; /* gray-800 */
        }

        .content-tabs button.active {
            color: #3b82f6; /* blue-500 */
            border-bottom: 2px solid #3b82f6;
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
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('docs', ['category' => $slug]) }}"
                                           class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                                           data-category-key="{{ $slug }}"
                                           data-category-name="{{ $cats }}">
                                            {!! ucwords(str_replace('-',' ',$cats)) !!}
                                        </a>
                                        @auth
                                            @if(auth()->user()->role === 'admin')
                                                <div class="flex-shrink-0 flex items-center space-x-1 pr-2">
                                                    <button type="button" onclick="openCategoryModal('edit', '{{ $slug }}', '{{ $cats }}')" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
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
                                        
                                        <button onclick="openCategoryModal('create')" class="text-blue-600 hover:underline text-sm" style="padding:10px;">
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
                    
                        <button id="category-dropdown-btn-mobile" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none">
                            <span id="category-button-text-mobile">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span>
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
                                    class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-category-key="{{ $slug }}"
                                    data-category-name="{{ $cats }}">
                                    {!! ucwords(str_replace('-',' ',$cats)) !!}
                                    </a>
                                    @auth
                                        @if(auth()->user()->role === 'admin')
                                            <div class="flex-shrink-0 flex items-center space-x-1 pr-2">
                                                <button type="button" onclick="openCategoryModal('edit', '{{ $slug }}', '{{ $cats }}')" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
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
                                    
                                    <button onclick="openCategoryModal()" class="text-blue-600 hover:underline text-sm" style="padding:10px;">
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
        let currentEditorInstance; // Deklarasikan variabel global untuk instance CKEditor

        function openEditor() {
            const kontenView = document.getElementById('kontenView');
            const editorContainer = document.getElementById('editor-container');
            const editorContentTypeInput = document.getElementById('editor-content-type');
            const editBtn = document.getElementById('editBtn'); // Get the edit button

            // Hide the view and show the editor
            kontenView.classList.add('hidden');
            editorContainer.classList.remove('hidden');
            if (editBtn) {
                editBtn.classList.add('hidden'); // Hide the edit button when editor is open
            }

            // Set content for the editor based on the active tab, or default
            const activeTabButton = document.querySelector('.content-tabs button.active');
            let contentType = 'Default'; // Default value if no tabs or no active tab
            if (activeTabButton) {
                contentType = activeTabButton.dataset.contentType;
            }
            editorContentTypeInput.value = contentType; // Update hidden input for form submission

            // Find content matching the active tab's content type
            const allDocsContents = @json($allDocsContents ?? []);
            const currentContent = allDocsContents.find(doc => doc.title === contentType);
            const contentToLoad = currentContent ? currentContent.content : '# Belum ada konten untuk ' + contentType;

            // Check if ClassicEditor is defined (from ckeditor5.umd.js)
            if (typeof ClassicEditor === 'undefined') {
                showNotification('Pustaka CKEditor tidak dimuat. Periksa konsol browser untuk kesalahan.', 'error', 5000);
                console.error('ClassicEditor tidak terdefinisi. Pastikan skrip CKEditor UMD dimuat dengan benar.');
                return;
            }

            // Initialize CKEditor if not initialized, or just set data if already exists
            if (!currentEditorInstance) {
                const editorConfigFromMainJS = window.CKEDITOR_CONFIG || {}; // Use a more specific global variable name

                const finalEditorConfig = {
                    ...editorConfigFromMainJS, // Merge config from main.js
                    ckfinder: {
                        uploadUrl: '{{ route('ckeditor.upload') . '?_token=' . csrf_token() }}'
                    },
                    simpleUpload: { // Ensure SimpleUploadAdapter is configured
                        uploadUrl: '{{ route('ckeditor.upload') . '?_token=' . csrf_token() }}'
                    },
                    // Ensure plugins and toolbar are also merged if your main.js sets them
                    plugins: [...(editorConfigFromMainJS.plugins || []), SimpleUploadAdapter].filter((v, i, a) => a.indexOf(v) === i), // Avoid plugin duplication
                    toolbar: editorConfigFromMainJS.toolbar // Use toolbar from main.js
                };

                // Add a slight delay to ensure textarea is fully rendered and visible
                setTimeout(() => {
                    ClassicEditor
                        .create(document.querySelector('#editor'), finalEditorConfig)
                        .then(editor => {
                            currentEditorInstance = editor;
                            editor.setData(contentToLoad); // Set content fetched from the server
                        })
                        .catch(error => {
                            console.error('Kesalahan saat menginisialisasi CKEditor:', error);
                            showNotification('Gagal menginisialisasi editor. Detail: ' + error.message, 'error', 5000);
                        });
                }, 50); // Small delay, e.g., 50ms
            } else {
                currentEditorInstance.setData(contentToLoad); // Just set data if editor is already initialized
            }
        }

        function closeEditor() {
            const kontenView = document.getElementById('kontenView');
            const editorContainer = document.getElementById('editor-container');
            const editBtn = document.getElementById('editBtn'); // Get the edit button

            editorContainer.classList.add('hidden');
            kontenView.classList.remove('hidden');
            if (editBtn) {
                editBtn.classList.remove('hidden'); // Show the edit button when editor is closed
            }
        }

        const categoryModal = document.getElementById('categoryModal');
        const categoryForm = document.getElementById('categoryForm');
        const categoryModalTitle = document.getElementById('categoryModalTitle');

        const cancelCategoryFormBtn = document.querySelector('#categoryModal button[onclick="closeCategoryModal()"]');

        // Remove any code that disables the 'cancelCategoryFormBtn' within this function
        function openCategoryModal(mode = 'create', originalSlug = '', displayName = '') {
            if (!categoryForm || !categoryModalTitle) {
                showNotification('Form kategori atau elemen tombol tidak ditemukan!', 'error');
                return;
            }

            categoryForm.reset();
            document.getElementById('form_category_method').value = mode === 'edit' ? 'PUT' : 'POST';
            document.getElementById('form_category_nama').value = displayName;
            document.getElementById('form_original_category_slug').value = originalSlug;

            categoryModalTitle.textContent = mode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori';
            categoryModal.classList.remove('hidden');

            // Make sure the cancel button is always enabled when the modal opens for any mode
            const cancelCategoryFormBtn = document.querySelector('#categoryModal button[onclick="closeCategoryModal()"]');
            if (cancelCategoryFormBtn) {
                cancelCategoryFormBtn.disabled = false;
                cancelCategoryFormBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        function closeCategoryModal() {
            const categoryModal = document.getElementById('categoryModal'); // Ensure we get the reference here if not global
            if (categoryModal) {
                categoryModal.classList.add('hidden');
            }
            // Also, if there was any logic that *enabled* other buttons (like editBtn in main content), ensure it's here
            // For category modal, just hiding it is sufficient for "cancel" behavior.
        }

    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // =================================
        // Verifikasi Logout
        // =================================
        const logoutForm = document.getElementById('logout-form');
        const logoutBtn = document.getElementById('logout-btn');

        if (logoutBtn) { // Check if logoutBtn exists before adding event listener
            logoutBtn.addEventListener('click', function (e) {
                e.preventDefault(); // always prevent submission first

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
                        logoutForm.submit(); // only submit if confirmed
                    }
                });
            });
        }

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
                            // Group results by menu name first
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
        // No need for currentPathname, we'll rely on the active class from PHP
        // No need for selectedMenuIdFromUrl, as the active state is handled by Blade and `initSidebarDropdown`

        if (sidebar) {
            sidebar.addEventListener('click', (e) => {
                const trigger = e.target.closest('.menu-arrow-icon');
                // const menuLink = e.target.closest('.menu-link'); // Keep if you need to intercept link clicks

                // Handle dropdown toggle for parent menus
                if (trigger) {
                    e.preventDefault();

                    const submenuId = trigger.dataset.toggle;
                    const submenu = document.getElementById(submenuId);
                    const icon = trigger.querySelector('i');

                    if (submenu) {
                        const isCurrentlyOpen = submenu.classList.contains('open');

                        // Close other top-level submenus to avoid too many open at once
                        // Only close if it's not the current submenu being toggled
                        // and not a parent of the current submenu being toggled
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
        const categoryDropdownBtn = document.getElementById('category-dropdown-btn');
        const categoryDropdownText = document.getElementById('category-button-text'); // Get the span element
        const categoryDropdownMenu = document.getElementById('category-dropdown-menu');

        // Mobile category dropdown elements
        const categoryDropdownBtnMobile = document.getElementById('category-dropdown-btn-mobile');
        const categoryDropdownTextMobile = document.getElementById('category-button-text-mobile');
        const categoryDropdownMenuMobile = document.getElementById('category-dropdown-menu-mobile');


        // Get current category name from Laravel ($currentCategory)
        const currentCategoryFromBlade = "{{ $currentCategory ?? 'default' }}";    

        // Function to update dropdown button text
        const updateCategoryButtonText = (categoryKey, targetTextElement) => {
            let categoryDisplayName = categoryKey.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            if (targetTextElement) {
                targetTextElement.textContent = categoryDisplayName;
            }
        };

        // Call this function on DOM load to set initial text
        updateCategoryButtonText(currentCategoryFromBlade, categoryDropdownText);
        if (categoryDropdownTextMobile) {
            updateCategoryButtonText(currentCategoryFromBlade, categoryDropdownTextMobile);
        }
        


        if (categoryDropdownBtn && categoryDropdownMenu) {
            categoryDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent click event from propagating to document and closing dropdown immediately
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
                    if (chevronIcon) { // Ensure icon exists before trying to remove class
                        chevronIcon.classList.remove('fa-chevron-up');
                        chevronIcon.classList.add('fa-chevron-down');
                    }
                }
            });

            // Handle clicks on dropdown items
            categoryDropdownMenu.querySelectorAll('a').forEach(item => {
                item.addEventListener('click', (e) => {
                    // NO need for e.preventDefault() because we want the link navigation to happen
                    // Get category key from href attribute
                    const href = item.getAttribute('href'); // Use 'item' not 'e.target' to ensure this is an <a>
                    const url = new URL(href);
                    const newCategoryKey = url.searchParams.get('category'); // Get 'category' value from URL

                    if (newCategoryKey) {
                        updateCategoryButtonText(newCategoryKey, categoryDropdownText); // Update button text based on new key
                    }

                    // Close the dropdown after item is clicked
                    categoryDropdownMenu.classList.remove('open');
                    const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                    if (chevronIcon) {
                        chevronIcon.classList.remove('fa-chevron-up');
                        chevronIcon.classList.add('fa-chevron-down');
                    }
                    // Navigation to the selected URL will happen automatically because it's an <a> tag
                });
            });
        }
        
        // Mobile Category Dropdown Logic
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
                const deleteContentBtn = document.getElementById('delete-content-btn'); // New delete content button

                let menuToDelete = null;

                // Category modal functions (already mostly correct, just ensuring scope)
                // These are already defined globally above, no need to redefine them here.
                // Re-added them for clarity on which functions are available.
                function openCategoryModal(mode = 'create', defaultName = '') {
                    if (!categoryForm || !categoryModalTitle) {
                        showNotification('Form kategori tidak ditemukan!', 'error');
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
                
                const confirmDeleteCategory = (categorySlug, categoryName) => {
                    Swal.fire({
                        title: 'Yakin ingin menghapus kategori?',
                        text: `Anda akan menghapus kategori "${categoryName}" beserta semua menu dan konten di dalamnya.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const loadingNotif = showNotification('Menghapus kategori...', 'loading');
                            try {
                                const response = await fetch(`/kategori/${categorySlug}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json',
                                    }
                                });
                                const data = await response.json();
                                hideNotification(loadingNotif);
                                if (data.success) {
                                    showCentralSuccessPopup(data.success);
                                    window.location.href = `/docs/epesantren`;
                                } else {
                                    showNotification(data.message || 'Gagal menghapus kategori.', 'error');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                hideNotification(loadingNotif);
                                showNotification('Terjadi kesalahan saat menghapus kategori.', 'error');
                            }
                        }
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
                        const originalCategorySlug = document.getElementById('form_original_category_slug').value; // Get the original slug

                        let url = '/kategori'; // For POST (create)
                        let httpMethod = 'POST';

                        if (method === 'PUT') {
                            // Use the originalCategorySlug for the URL
                            url = `/kategori/${originalCategorySlug}`;
                            httpMethod = 'POST'; // Laravel route uses POST with _method PUT for updates
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
                                // Redirect to the new category slug if it was an edit or new category
                                const newSlug = data.new_slug || categoryName.toLowerCase().replace(/\s+/g, '-');
                                // Ensure the redirect uses the correct, updated slug
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
                                dataToSend[key] = value === '1' ? 1 : 0; // Ensure boolean is sent correctly
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
                
                // New logic for deleting specific content type
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
                                        // Reload the page to reflect the content deletion and possibly show default content
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
        const cancelEditorBtn = document.getElementById('cancel-editor-btn'); // Get cancel button
        
        // This variable is passed from the server for all contents related to the current menu
        const allDocsContents = @json($allDocsContents ?? []);    
        // Get the initial content type that was displayed (from the URL query parameter)
        const initialActiveContentType = new URLSearchParams(window.location.search).get('content_type') || 'Default';

        if (contentTabsContainer) {
            // Function to update content based on selected tab
            const updateContentDisplay = (contentType) => {
                const contentData = allDocsContents.find(doc => doc.title === contentType);
                if (kontenView) {
                    kontenView.innerHTML = contentData ? contentData.content : '<h3>Konten belum tersedia.</h3><p>Silakan edit untuk menambahkan konten untuk ' + contentType + '.</p>';
                }
                // Update the hidden input for the editor form
                if (editorContentTypeInput) {
                    editorContentTypeInput.value = contentType;
                }
                // Update URL without reloading
                const url = new URL(window.location);
                url.searchParams.set('content_type', contentType);
                window.history.pushState({}, '', url);
            };

            // Add click listeners to tab buttons
            contentTabsContainer.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', () => {
                    // Remove 'active' class from all buttons
                    contentTabsContainer.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    // Add 'active' class to the clicked button
                    button.classList.add('active');

                    const contentType = button.dataset.contentType;
                    updateContentDisplay(contentType);

                    // If editor is open, update its content as well
                    if (!editorContainer.classList.contains('hidden') && typeof currentEditorInstance !== 'undefined' && currentEditorInstance !== null) {
                        const contentToLoad = allDocsContents.find(doc => doc.title === contentType)?.content || '# Belum ada konten untuk ' + contentType;
                        currentEditorInstance.setData(contentToLoad);
                    }
                });
            });

            // Set the initial active tab based on query parameter or default to the first
            let initialTabButton = contentTabsContainer.querySelector(`[data-content-type="${initialActiveContentType}"]`);
            if (!initialTabButton && contentTabsContainer.children.length > 0) {
                initialTabButton = contentTabsContainer.children[0];
            }
            if (initialTabButton) {
                initialTabButton.classList.add('active');
                updateContentDisplay(initialTabButton.dataset.contentType); // Ensure correct content is displayed initially
            }
        }


        // Override the form submission to include content_type
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

                    // After saving, reload allDocsContents to get the latest data
                    // This is a simplified approach, a more robust solution might involve updating `allDocsContents` directly
                    // and then calling `updateContentDisplay` with the new data. For now, a full reload is safer.
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