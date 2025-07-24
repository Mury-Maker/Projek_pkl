{{-- resources/views/docs/uat_detail_page.blade.php --}}

@extends('docs.index')

@section('action-buttons')
    {{-- Tombol untuk kembali ke halaman use case utama --}}
    <a href="{{ route('docs.use_case_detail', [
        'category' => $currentCategory,
        'page' => Str::slug($selectedNavItem->menu_nama),
        'useCaseSlug' => Str::slug($parentUseCase->nama_proses)
    ]) }}" class="btn btn-secondary ml-auto">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Aksi
    </a>
@endsection

@section('content')
    <div class="prose max-w-none">
        <h2 class="text-2xl font-bold mb-4">Detail Data UAT</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="font-semibold text-gray-700">ID UAT:</p>
                <p>{{ $uatData->id_uat ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Nama Proses Usecase:</p>
                <p>{{ $uatData->nama_proses_usecase ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Keterangan:</p>
                <p class="prose max-w-none">{!! $uatData->keterangan_uat ?? 'N/A' !!}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Status:</p>
                <p>{{ $uatData->status_uat ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Gambar UAT:</p>
                <div class="prose max-w-none">{!! $uatData->gambar_uat ?? 'Tidak ada gambar' !!}</div>
            </div>
        </div>

        {{-- Anda bisa menambahkan lebih banyak detail atau tombol edit/delete di sini jika diinginkan --}}
    </div>
@endsection