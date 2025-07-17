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
    <link rel="stylesheet" href="{{ asset('ckeditor/style.css') }}">
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.css" crossorigin>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen flex-col">
        {{-- Header --}}
        @include('docs.partials._header')

        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            {{-- Pastikan _sidebar menerima selectedNavItemId dan currentCategory --}}
            @include('docs.partials._sidebar', [
                'currentCategory' => $currentCategory ?? 'epesantren',
                'selectedNavItemId' => $menu_id ?? null // Ini adalah ID menu yang sedang aktif
            ])

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                <div class="judul-halaman">
                    {{-- BERIKAN ID PADA H1 INI --}}
                    <h1 id="main-content-title"> {!! ucfirst(Str::headline($currentPage)) !!}
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
                        {!! $contentDocs->content ?? "Konten Belum Tersedia" !!}
                    </div>

                    {{-- Editor CKEditor (hanya untuk admin dan jika ada menu_id yang valid) --}}
                    @auth
                        @if(auth()->user()->role === 'admin')
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
    @include('docs.partials._search_modal')

    {{-- Modal for Add/Edit Menu (EXISTING) - HANYA DIMUAT JIKA USER ADALAH ADMIN --}}
    @auth
        @if(auth()->user()->role === 'admin')
            @include('docs.partials._menu_modal')
            @include('docs.partials._category_modal')
            @include('docs.partials._delete_confirm_modal')
        @endif
    @endauth

    {{-- NEW: Success Pop-up Modal (without OK button) --}}
    @include('docs.partials._success_popup')

    {{-- CKEditor scripts - HANYA DIMUAT JIKA USER ADALAH ADMIN --}}
    @auth
        @if(auth()->user()->role === 'admin')
            <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/ckeditor5.umd.js" crossorigin></script>
            <script src="{{ asset('ckeditor/main.js') }}"></script>
        @endif
    @endauth

    {{-- Load all modular JS files --}}
    <script src="{{ asset('js/global-utils.js') }}"></script>
    <script src="{{ asset('js/mobile-sidebar.js') }}"></script>
    <script src="{{ asset('js/search-logic.js') }}"></script>
    <script src="{{ asset('js/category-dropdown.js') }}"></script>
    <script src="{{ asset('js/editor-logic.js') }}"></script>
    <script src="{{ asset('js/logout-form.js') }}"></script>

    @auth
        @if(auth()->user()->role === 'admin')
            <script src="{{ asset('js/admin-modals.js') }}"></script>
        @endif
    @endauth

    <script>
        window.initialBladeData = {
            selectedNavItemId: {{ $selectedNavItemId ?? 'null' }},
            currentPage: "{{ $currentPage ?? '' }}",
            currentCategory: "{{ $currentCategory ?? '' }}",
            menu_id: {{ $menu_id ?? 'null' }},
            allDocsContents: {!! json_encode($allDocsContents ?? []) !!}
        };
    </script>
    
</body>
</html>