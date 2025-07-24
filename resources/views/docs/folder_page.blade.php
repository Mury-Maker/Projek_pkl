{{-- resources/views/docs/folder_page.blade.php --}}

@extends('docs.index')

{{-- Tidak ada action-buttons di halaman folder --}}
@section('action-buttons')
@endsection

@section('content')
    <div class="prose max-w-none">
        <div class="text-center p-8 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">
                @if(isset($selectedNavItem) && $selectedNavItem->menu_child == 0)
                    Ini adalah Menu Utama (Folder)
                @else
                    Ini adalah Menu Folder
                @endif
            </h3>
            <p class="text-gray-600">Menu ini berfungsi sebagai pengelompokan untuk sub-menu di bawahnya dan tidak memiliki konten yang dapat diedit langsung. Silakan pilih sub-menu di sidebar.</p>
            @auth
                @if(auth()->user()->role === 'admin')
                    <p class="mt-4 text-sm text-gray-500">Anda dapat mengubah status menu ini menjadi 'Memiliki Konten' melalui tombol edit menu di sidebar untuk membuat daftar tindakan (use case).</p>
                @endif
            @endauth
            @if(isset($fallbackMessage))
                <div class="mt-8 p-4 bg-yellow-100 text-yellow-700 rounded-lg">
                    {!! $fallbackMessage !!}
                </div>
            @endif
        </div>
    </div>
@endsection