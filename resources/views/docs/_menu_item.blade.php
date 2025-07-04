@foreach($items as $item)
<div class="my-0.5 group">
    {{-- Container utama item menu --}}
    <div class="flex items-center justify-between py-1.5 rounded-md hover:bg-gray-100 transition-colors
        {{ (isset($selectedNavItemId) && $selectedNavItemId == $item->menu_id) ? 'bg-blue-100 font-semibold' : '' }}
        sidebar-menu-item-wrapper">

        {{-- Menentukan kelas font di sini, lalu menambahkannya ke atribut class A --}}
        @php
            $linkClasses = 'flex items-center space-x-2 flex-grow min-w-0'; // flex-grow dan min-w-0 agar nama menu bisa mengisi ruang dan menyusut
            
            // Penyesuaian indentasi berdasarkan level
            if (isset($level)) {
                $linkClasses .= ' pl-' . (($level + 1) * 3); // Indentasi
                // Penyesuaian ukuran dan ketebalan font berdasarkan level
                if ($level == 0) { // Parent Menu
                    $linkClasses .= ' text-sm font-semibold';
                } elseif ($level == 1) { // Child Menu
                    $linkClasses .= ' text-xs font-normal';
                } else { // Grand-child Menu ke bawah
                    $linkClasses .= ' text-[0.7rem] font-normal'; // Menggunakan ukuran kustom
                }
            } else { // Default untuk menu utama jika level tidak terdefinisi (biasanya level 0)
                $linkClasses .= ' pl-3 text-sm font-semibold';
            }
        @endphp

        {{-- Kontainer KIRI: Ikon Menu dan Nama Menu --}}
        {{-- Ini adalah A-tag itu sendiri, yang akan mengambil sebagian besar ruang. --}}
        <a href="{{ $item->menu_link }}" class="{{ $linkClasses }}" style="min-width: 0;">
            {{-- Placeholder atau Ikon --}}
            <div class="w-4 flex-shrink-0 text-center">
                @if($item->menu_nama == 'Detail Sub 1')
                    <i class="fas fa-circle text-[0.4em]"></i>
                @elseif($item->menu_icon)
                    <i class="{{ $item->menu_icon }}"></i>
                @else
                    <span class="w-4"></span> {{-- Placeholder jika tidak ada ikon --}}
                @endif
            </div>

            {{-- Nama Menu --}}
            <span class="ml-2 flex-grow min-w-0 truncate">{{ $item->menu_nama }}</span>
        </a>

        {{-- Kontainer KANAN: Tombol Admin dan Panah Dropdown --}}
        {{-- Ini akan memiliki lebar yang fleksibel saat tidak ada admin, dan lebar tetap saat ada admin --}}
        <div class="flex items-center flex-shrink-0">

            {{-- Tombol Admin (Add Child, Edit, Delete) --}}
            @if(isset($editorMode) && $editorMode)
            <div class="flex items-center space-x-0.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 mr-1"> {{-- **PERUBAHAN: mr-1 untuk jarak dari panah** --}}
                @if ($item->menu_child >= 0)
                <button
                    data-parent-id="{{ $item->menu_id }}"
                    class="add-child-menu-btn text-green-500 hover:text-green-700 p-1"
                    title="Tambah Sub Menu"
                    aria-label="Tambah Sub Menu">
                    <i class="fa-solid fa-plus-circle"></i>
                </button>
                @endif
                <button
                    data-menu-id="{{ $item->menu_id }}"
                    class="edit-menu-btn text-blue-500 hover:text-blue-700 p-1"
                    title="Edit Menu"
                    aria-label="Edit Menu">
                    <i class="fa-solid fa-pencil"></i>
                </button>
                <button
                    data-menu-id="{{ $item->menu_id }}"
                    data-menu-nama="{{ $item->menu_nama }}"
                    class="delete-menu-btn text-red-500 hover:text-red-700 p-1"
                    title="Hapus Menu"
                    aria-label="Hapus Menu {{ $item->menu_nama }}">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            @endif

            {{-- Panah Dropdown --}}
            {{-- Pastikan ini di paling kanan. Gunakan ml-auto saat tidak ada editorMode aktif. --}}
            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center pr-3 {{ !isset($editorMode) || !$editorMode ? 'ml-auto' : '' }}"> {{-- **PERUBAHAN: ml-auto kondisional** --}}
                @if(!empty($item->children))
                <button
                    type="button"
                    class="menu-arrow-icon text-gray-500 p-2"
                    data-toggle="submenu-{{ $item->menu_id }}"
                    aria-expanded="false"
                    aria-controls="submenu-{{ $item->menu_id }}"
                    aria-label="Toggle submenu for {{ $item->menu_nama }}">
                    <i class="fas fa-chevron-left transition-transform duration-300"></i>
                </button>
                @else
                    <span class="p-2"></span> {{-- Placeholder kosong agar tinggi tetap sama --}}
                @endif
            </div>
        </div>
    </div>

    @if(!empty($item->children))
        <div id="submenu-{{ $item->menu_id }}" class="submenu-container mt-1 border-l border-gray-200" role="region" aria-label="Submenu for {{ $item->menu_nama }}">
            @include('docs._menu_item', [
                'items' => $item->children,
                'editorMode' => $editorMode ?? false,
                'selectedNavItemId' => $selectedNavItemId ?? null,
                'level' => ($level ?? 0) + 1
            ])
        </div>
    @endif
</div>
@endforeach