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

    @auth
        @if(auth()->user()->role === 'admin')
            <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
            <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
        @endif
    @endauth
</head>
<body class="bg-gray-100">
    <div class="flex h-screen"> {{-- MENGUBAH 'flex-col' MENJADI 'flex' UNTUK TATA LETAK HORIZONTAL --}}

        {{-- Sidebar Component --}}
        {{-- DIPINDAHKAN: Sidebar sekarang menjadi anak langsung dari kontainer flex utama --}}
        @include('docs.partials._sidebar', [
            'currentCategory' => $currentCategory ?? 'epesantren',
            'selectedNavItemId' => $menu_id ?? null
        ])

        {{-- Wrapper Konten Utama (Header + Konten Utama) --}}
        {{-- BARU: Div ini membungkus header dan konten utama, dan akan melebar/menyusut secara horizontal --}}
        <div id="content-area-wrapper" class="flex-1 flex flex-col">
            {{-- Header Component --}}
            @include('docs.partials._header')

            {{-- Main Content Area --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                <nav class="flex items-center text-sm text-gray-600 mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                
                        {{-- Home --}}
                        <li class="inline-flex items-center">
                            @if (empty($selectedNavItem) && empty($parentUseCase))
                                <span class="inline-flex items-center text-gray-800 font-semibold" aria-current="page">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2L2 10h3v6h10v-6h3L10 2z" />
                                    </svg>
                                    Home
                                </span>
                            @else
                                <a href="{{ route('docs', ['category' => $currentCategory]) }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 transition">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2L2 10h3v6h10v-6h3L10 2z" />
                                    </svg>
                                    Home
                                </a>
                            @endif
                        </li>
                
                        {{-- Parent Menu (e.g., "Menu Utama") --}}
                        @if ($selectedNavItem && $selectedNavItem->parent)
                        <li>
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <a href="{{ route('docs', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->parent->menu_nama)]) }}"
                                class="text-gray-500 hover:text-blue-600 transition">
                                    {{ $selectedNavItem->parent->menu_nama }}
                                </a>
                            </div>
                        </li>
                        @endif
                
                        {{-- Menu Sekarang (e.g., "epesantren") --}}
                        @if ($selectedNavItem && empty($singleUseCase) && empty($parentUseCase))
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <span class="text-blue-600 font-semibold">
                                    {{ $selectedNavItem->menu_nama }}
                                </span>
                            </div>
                        </li>
                        @elseif($selectedNavItem && (isset($singleUseCase) || isset($parentUseCase)))
                        <li>
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <a href="{{ route('docs', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama)]) }}"
                                class="text-gray-500 hover:text-blue-600 transition">
                                    {{ $selectedNavItem->menu_nama }}
                                </a>
                            </div>
                        </li>
                        @endif
                
                        {{-- Nama Use Case (Detail Aksi) --}}
                        @if (!empty($singleUseCase))
                        <li>
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <a href="{{ route('docs.use_case_detail', [
                                    'category' => $currentCategory,
                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                    'useCaseSlug' => Str::slug($singleUseCase['nama_proses'])
                                ]) }}" class="text-blue-600 font-semibold">
                                    Detail - {{ $singleUseCase['nama_proses'] }}
                                </a>
                            </div>
                        </li>
                        @endif

                        {{-- Nama Parent Use Case (untuk detail Database/Report/UAT) --}}
                        @if (!empty($parentUseCase))
                        <li>
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <a href="{{ route('docs.use_case_detail', [
                                    'category' => $currentCategory,
                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                    'useCaseSlug' => Str::slug($parentUseCase->nama_proses)
                                ]) }}" class="text-gray-500 hover:text-blue-600 transition">
                                    Detail - {{ $parentUseCase->nama_proses }}
                                </a>
                            </div>
                        </li>
                        @endif
                
                        {{-- Detail Spesifik (Database, Report, UAT) --}}
                        @if (isset($databaseData))
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <span class="text-blue-600 font-semibold">
                                    Database
                                </span>
                            </div>
                        </li>
                        @elseif (isset($reportData))
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <span class="text-blue-600 font-semibold">
                                    Report
                                </span>
                            </div>
                        </li>
                        @elseif (isset($uatData))
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                </svg>
                                <span class="text-blue-600 font-semibold">
                                    UAT
                                </span>
                            </div>
                        </li>
                        @endif
                
                    </ol>
                </nav>                         
                <div class="judul-halaman">
                    {{-- BERIKAN ID PADA H1 INI --}}
                    <h1 id="main-content-title"> {!! ucfirst(Str::headline($currentPage)) !!}</h1>
                    {{-- Tombol aksi spesifik akan ditambahkan di yield content --}}
                    @yield('action-buttons')
                </div>
                {{-- Konten Dinamis Halaman --}}
                @yield('content')
            </main>
        </div>
    </div>
    @include('docs.partials._detail_data_modal')
    {{-- MODAL GLOBAL UNTUK PENCARIAN --}}
    @include('docs.partials._search_modal')

    {{-- Modal Admin (Menu, Kategori, Delete Confirm) --}}
    @auth
        @if(auth()->user()->role === 'admin')
            @include('docs.partials._menu_modal')
            @include('docs.partials._category_modal')
            @include('docs.partials._delete_confirm_modal')

            {{-- Modal untuk Add/Edit Use Case Detail (saat menambahkan/mengedit tindakan) --}}
            @include('docs.partials._use_case_modal')
            {{-- Modal untuk Add/Edit UAT Data Row --}}
            @include('docs.partials._uat_data_modal')
            {{-- MODAL REPORT DATA INI HARUS ADA --}}
            @include('docs.partials._report_data_modal')
            {{-- MODAL DATABASE DATA INI JUGA HARUS ADA --}}
            @include('docs.partials._database_data_modal')
        @endif
    @endauth

    {{-- Success Pop-up Modal --}}
    @include('docs.partials._success_popup')

    {{-- CKEditor scripts (hanya untuk modal use case) --}}
    @auth
        @if(auth()->user()->role === 'admin')
            <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.umd.js" crossorigin></script>
            <script src="{{ asset('ckeditor/main.js') }}"></script> {{-- Config CKEditor global --}}
        @endif
    @endauth

    {{-- Load all modular JS files --}}
    <script src="{{ asset('js/global-utils.js') }}"></script>
    <script src="{{ asset('js/mobile-sidebar.js') }}"></script>
    <script src="{{ asset('js/desktop-sidebar-toggle.js') }}"></script> {{-- BARU: File JavaScript untuk sidebar desktop --}}
    <script src="{{ asset('js/search-logic.js') }}"></script>
    <script src="{{ asset('js/category-dropdown.js') }}"></script>
    <script src="{{ asset('js/logout-form.js') }}"></script>

    @auth
        @if(auth()->user()->role === 'admin')
            <script src="{{ asset('js/admin-modals.js') }}"></script>
            <script src="{{ asset('js/use-case-logic.js') }}"></script>
        @endif
    @endauth

    <script>
        // Global data untuk JavaScript
        window.initialBladeData = {
            selectedNavItemId: {{ $selectedNavItemId ?? 'null' }},
            currentPage: "{{ $currentPage ?? '' }}",
            currentCategory: "{{ $currentCategory ?? '' }}",
            menu_id: {{ $menu_id ?? 'null' }},
            // singleUseCase akan diisi jika sedang di halaman detail, useCases jika di halaman daftar
            singleUseCase: {!! json_encode($singleUseCase ?? null) !!}, // Untuk halaman detail use case
            useCases: {!! json_encode($useCases ?? null) !!}, // Untuk halaman daftar use case
            contentTypes: {!! json_encode($contentTypes ?? []) !!},
            activeContentType: "{{ $activeContentType ?? 'UAT' }}"
        };
    </script>
</body>
</html>