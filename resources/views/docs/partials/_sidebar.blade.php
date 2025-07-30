<div id="sidebar-backdrop" class="backdrop"></div>

<aside id="docs-sidebar" class="w-72 flex-shrink-0 overflow-y-auto bg-stone border-r border-gray-200 p-6">
                {{-- Logo --}}


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
                                    <button type="button" onclick="openCategoryModal('edit', '{{ $cats }}')" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- Only allow deleting non-default categories --}}
                                    @if($slug !== 'epesantren')
                                        <button type="button" onclick="confirmDeleteCategory('{{ $slug }}', '{{ $cats }}')" title="Hapus Kategori" class="text-red-500 hover:text-red-700 p-1">
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
 
            <div class="flex justify-between items-center mb-4">
                {{-- Batasi tampilan nama kategori di judul kiri header --}}
                <div class="container-judul">
                {{-- LOOGO --}}
                <a href="{{ route('docs', ['category' => $currentCategory]) }}">
                    <img src="{{ asset('img/indoweb.png') }}" alt="Logo" class="h-10 w-auto">
                </a>
                {{-- END LOGO --}}

                {{-- JUDUL --}}
                <a href="{{ route('docs', ['category' => $currentCategory]) }}"
                    id="main-category-title"
                   class="text-2xl font-bold text-blue-600 header-main-category-title"
                   title="{!! ucwords(str_replace('-',' ',$currentCategory)) !!}">
                   <span class="truncate-text">Dokumentasi</span>
                </a>
                {{-- END JUDUL --}}
            @if(auth()->user()->role === 'admin')
                {{-- BUTTON TAMBAH --}}
                <button id="add-parent-menu-btn" class="bg-blue-500 text-white w-8 h-8 rounded-lg w-full flex items-center justify-center hover:bg-blue-600 transition-colors" title="Tambah Menu Utama Baru">
                    <i class="fa fa-plus"></i>
                </button>
                {{-- END BUTTON TAMBAH --}}


                </div>

            </div>
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
