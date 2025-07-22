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
    <div class="flex h-screen flex-col">
        {{-- Header --}}
        @include('docs.partials._header')

        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            @include('docs.partials._sidebar', [
                'currentCategory' => $currentCategory ?? 'epesantren',
                'selectedNavItemId' => $menu_id ?? null
            ])

            {{-- Main Content Area --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                <div class="judul-halaman">
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