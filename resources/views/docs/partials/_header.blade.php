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
                   <span class="truncate-text">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span> {{-- WRAP TEKS DENGAN SPAN --}}
                </a>
                                        
                @php use Illuminate\Support\Str; @endphp

                <div class="relative hidden md:block">
                    {{-- Batasi tampilan nama kategori di tombol dropdown --}}
                    <button id="category-dropdown-btn" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none">
                        <span id="category-button-text" class="truncate-text" title="{!! ucwords(str_replace('-',' ',$currentCategory)) !!}">{!! ucwords(str_replace('-',' ',$currentCategory)) !!}</span> {{-- TAMBAHKAN CLASS TRUNCATE-TEXT --}}
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
                                   class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                                   data-category-key="{{ $slug }}"
                                   data-category-name="{{ $cats }}"
                                   title="{!! ucwords(str_replace('-',' ',$cats)) !!}">
                                   <span class="truncate-text">{!! ucwords(str_replace('-',' ',$cats)) !!}</span> {{-- WRAP TEKS DENGAN SPAN --}}
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