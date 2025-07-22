{{-- resources/views/docs/use_case_index.blade.php --}}

@extends('docs.index')

@section('action-buttons')
    @auth
        @if(auth()->user()->role === 'admin')
            {{-- Tombol Tambah Tindakan (Use Case) baru --}}
            <button id="addUseCaseBtn" class="btn btn-primary ml-auto" data-menu-id="{{ $menu_id }}">
                <i class="fa fa-plus-circle mr-2"></i>Tambah Tindakan
            </button>
        @endif
    @endauth
@endsection

@section('content')
    <div class="prose max-w-none">
        <h2 class="text-2xl font-bold mb-4">Daftar Tindakan (Use Cases)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">No</th>
                        <th class="py-2 px-4 border-b">ID Usecase</th>
                        <th class="py-2 px-4 border-b">Nama Proses</th>
                        <th class="py-2 px-4 border-b">Aktor</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody id="useCaseIndexTableBody">
                    @forelse($useCases as $useCase)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4 border-b">{{ $useCase->usecase_id }}</td>
                            <td class="py-2 px-4 border-b">{{ $useCase->nama_proses }}</td>
                            <td class="py-2 px-4 border-b">{{ $useCase->aktor }}</td>
                            <td class="py-2 px-4 border-b">
                                @auth
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('docs.use_case_detail', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($useCase->nama_proses)]) }}" 
                                           class="btn-action text-green-500 hover:text-green-700 mr-2" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="edit-usecase-index-btn btn-action text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $useCase->id }}" data-menu-id="{{ $menu_id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="delete-usecase-index-btn btn-action text-red-500 hover:text-red-700" data-id="{{ $useCase->id }}" data-nama="{{ $useCase->nama_proses }}" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('docs.use_case_detail', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($useCase->nama_proses)]) }}" 
                                           class="btn-action text-green-500 hover:text-green-700 mr-2" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada tindakan (use case) yang didokumentasikan untuk menu ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection