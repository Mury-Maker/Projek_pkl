{{-- usecase_index.blade.php --}}

@extends('docs.index')

@section('content')
    <div class="max-w-7xl mx-auto px-2 sm:px-2 lg:px-2">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Daftar Use Case</h2>

            @auth
                @if(auth()->user()->role === 'admin')
                    <div class="mb-4">
                        <button id="addUseCaseBtn"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white mr-1 text-sm font-medium rounded-md shadow-sm transition"
                            data-menu-id="{{ $menu_id }}">
                            <i class="fa fa-plus-circle mr-2"></i>Tambah Data
                        </button>
                        <button class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black mr-1 text-sm font-medium rounded-md shadow transition">
                            <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                        </button>
                        <button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md shadow transition">
                            <i class="fas fa-print mr-2"></i> Print / Cetak PDF only Usecase
                        </button>
                    </div>
                @endif
            @endauth

            <div class="overflow-x-auto max-w-[100vw] min-h-[500px] max-h-[80vh]">
                <table class="max-w-[100vw] bg-white border border-gray-300 text-sm text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 border-r border-b">No</th>
                            <th class="py-2 px-4 border-r border-b">Nama Proses</th>
                            <th class="py-2 px-4 border-r border-b">Aktor</th>
                            <th class="py-2 px-4 border-r border-b">Kondisi Awal</th>
                            <th class="py-2 px-4 border-r border-b">Kondisi Akhir</th>
                            <th class="py-2 px-4 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="useCaseIndexTableBody">
                        @forelse($useCases as $useCase)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2 px-4 border-r border-b">{{ $loop->iteration }}</td>
                                <td class="py-2 px-4 text-sm text-gray-900 max-w-[200px] truncate whitespace-normal">{{ $useCase->nama_proses }}</td>
                                <td class="py-2 px-4 text-sm text-gray-900 max-w-[200px] truncate whitespace-normal">{{ $useCase->aktor }}</td>
                                <td class="py-2 px-4 text-sm text-gray-900 max-w-[200px] truncate whitespace-normal">{!! $useCase->kondisi_awal !!}</td>
                                <td class="py-2 px-4 text-sm text-gray-900 max-w-[200px] truncate whitespace-normal">{!! $useCase->kondisi_akhir !!}</td>
                                <td class="py-2 px-4 border-b text-center align-middle w-36 max-w-[9rem]">
                                    @auth
                                        <div class="relative inline-block text-left" id="dropdown-wrapper-{{ $useCase->id }}">
                                            <button onclick="toggleDropdown({{ $useCase->id }})"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded-md min-w-[100px] w-full flex items-center justify-between gap-2 overflow-hidden text-ellipsis whitespace-nowrap">
                                                Pilih Aksi
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            </button>                                            
                                            <div id="dropdown-menu-{{ $useCase->id }}"
                                                class="hidden absolute z-10 mt-2 w-[105px] origin-top-right rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none text-sm text-left">
                                                <ul class="py-1">
                                                    <li>
                                                        <a href="{{ route('docs.use_case_detail', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($useCase->nama_proses)]) }}"
                                                            class="block px-4 py-2 text-green-600 hover:bg-gray-100">
                                                            <i class="fas fa-eye mr-2"></i> Detail
                                                        </a>
                                                    </li>
                                                    @if(auth()->user()->role === 'admin')
                                                        <li>
                                                            <button class="block w-full text-left px-4 py-2 text-blue-600 hover:bg-gray-100 edit-usecase-index-btn"
                                                                data-id="{{ $useCase->id }}" data-menu-id="{{ $menu_id }}">
                                                                <i class="fas fa-edit mr-2"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 delete-usecase-index-btn"
                                                                data-id="{{ $useCase->id }}" data-nama="{{ $useCase->nama_proses }}">
                                                                <i class="fas fa-trash-alt mr-2"></i> Hapus
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    @endauth
                                </td>                                                                                            
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 px-4 text-center text-gray-500">Tidak ada tindakan (use case) yang didokumentasikan untuk menu ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
<script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById('dropdown-menu-' + id);
        dropdown.classList.toggle('hidden');

        // Tutup dropdown lain jika ada
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== 'dropdown-menu-' + id) {
                menu.classList.add('hidden');
            }
        });
    }

    // Klik di luar dropdown menutupnya
    document.addEventListener('click', function (e) {
        document.querySelectorAll('[id^="dropdown-wrapper-"]').forEach(wrapper => {
            if (!wrapper.contains(e.target)) {
                const menu = wrapper.querySelector('[id^="dropdown-menu-"]');
                if (menu) menu.classList.add('hidden');
            }
        });
    });
</script>

