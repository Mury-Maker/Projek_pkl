{{-- resources/views/docs/use_case_detail_page.blade.php --}}

@extends('docs.index')

@section('action-buttons')
    {{-- Bagian ini dikosongkan karena tombol dipindahkan langsung ke dalam @section('content') --}}
@endsection

@section('content')
    @php
        $currentUseCase = $singleUseCase; // singleUseCase sudah diset di controller
        $hasUseCaseData = $currentUseCase && isset($currentUseCase['id']);
    @endphp

    {{-- Container utama untuk konten detail aksi dan report --}}
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div id="use-case-content-area">
            {{-- Bagian Detail Aksi --}}
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Detail Aksi</h2>
                @auth
                    @if(auth()->user()->role === 'admin')
                        {{-- Tombol Edit Detail Aksi --}}
                        <button id="editSingleUseCaseBtn"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm transition"
                            data-menu-id="{{ $menu_id }}">
                            <i class="fa-solid fa-file-pen mr-2"></i> Edit Detail Aksi
                        </button>
                    @endif
                @endauth
            </div>

            {{-- Struktur Tabel untuk Detail Aksi --}}
            <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <tbody>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">ID Usecase:</td>
                            <td class="py-2 px-4 text-gray-900">{{ $currentUseCase['usecase_id'] ?? 'N/A' }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Nama Proses:</td>
                            <td class="py-2 px-4 text-gray-900">{{ $currentUseCase['nama_proses'] ?? 'N/A' }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Deskripsi Aksi:</td>
                            <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                                {!! $currentUseCase['deskripsi_aksi'] ?? '<span class="text-gray-500 italic">Tidak ada deskripsi.</span>' !!}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Aktor:</td>
                            <td class="py-2 px-4 text-gray-900">{{ $currentUseCase['aktor'] ?? 'N/A' }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Tujuan:</td>
                            <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                                {!! $currentUseCase['tujuan'] ?? '<span class="text-gray-500 italic">Tidak ada tujuan.</span>' !!}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Kondisi Awal:</td>
                            <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                                {!! $currentUseCase['kondisi_awal'] ?? '<span class="text-gray-500 italic">Tidak ada kondisi awal.</span>' !!}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Kondisi Akhir:</td>
                            <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                                {!! $currentUseCase['kondisi_akhir'] ?? '<span class="text-gray-500 italic">Tidak ada kondisi akhir.</span>' !!}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Aksi Aktor:</td>
                            <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                                {!! $currentUseCase['aksi_reaksi'] ?? '<span class="text-gray-500 italic">Tidak ada aksi aktor.</span>' !!}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Reaksi Sistem:</td>
                            <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                                {!! $currentUseCase['reaksi_sistem'] ?? '<span class="text-gray-500 italic">Tidak ada reaksi sistem.</span>' !!}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            {{-- Tombol Print / Cetak (di bawah Detail Aksi) --}}
            <div class="flex justify-start items-center gap-3 mt-4 mb-8">
                <button class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-medium rounded-md shadow transition">
                    <i class="fas fa-print mr-2"></i> Only Data Usecase
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                    <i class="fas fa-print mr-2"></i> Semua Data (All table)
                </button>
            </div>

            {{-- Bagian Report, Database, UAT (berurutan) --}}
            @if($hasUseCaseData) {{-- Hanya tampilkan jika ada data use case --}}
                
                {{-- Report Data Section --}}
                <hr class="my-6 border-gray-200">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Report Data</h2>
                <div id="content-Report" class="mb-8">
                    <div class="flex flex-col items-start mb-4">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="flex justify-start items-center gap-x-2 mt-1 mb-2"> {{-- Tombol Tambah & Print untuk Report --}}
                                    <button id="addReportDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fa fa-plus-circle mr-2"></i>Tambah
                                    </button>
                                    <button class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-medium rounded-md shadow transition">
                                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                    </button>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm">
                        <table class="min-w-full bg-white divide-y divide-gray-200">
                            <thead class="bg-gray-50 border-b border-gray-300">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktor</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Report</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="reportDataTableBody" class="divide-y divide-gray-200">
                                @forelse($currentUseCase['report_data'] as $report)
                                    <tr data-id="{{ $report['id_report'] }}" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $report['aktor'] }}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $report['nama_report'] }}</td>
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-[200px] truncate whitespace-normal">{!! $report['keterangan'] !!}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-center text-sm font-medium flex justify-center items-center space-x-2">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- Detail (Mata): Biru dengan ikon putih --}}
                                                    <a href="{{ route('docs.use_case_report_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'reportId' => $report['id_report']
                                                    ]) }}" class="btn-action bg-blue-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Edit (Pensil): Kuning dengan ikon hitam --}}
                                                    <button class="edit-report-btn btn-action bg-yellow-400 text-black" data-id="{{ $report['id_report'] }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Hapus (Sampah): Merah dengan ikon putih --}}
                                                    <button class="delete-report-btn btn-action bg-red-600 text-white" data-id="{{ $report['id_report'] }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- Non-admin hanya tombol detail --}}
                                                    <a href="{{ route('docs.use_case_report_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'reportId' => $report['id_report']
                                                    ]) }}" class="btn-action bg-blue-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500 italic">Tidak ada data Report.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Database Data Section --}}
                <hr class="my-6 border-gray-200">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Database Data</h2>
                <div id="content-Database" class="mb-8">
                    <div class="flex flex-col items-start mb-4">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="flex justify-start items-center gap-x-2 mt-1 mb-2"> {{-- Tombol Tambah & Print untuk Database --}}
                                    <button id="addDatabaseDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fa fa-plus-circle mr-2"></i>Tambah
                                    </button>
                                    <button class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-medium rounded-md shadow transition">
                                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                    </button>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm">
                        <table class="min-w-full bg-white divide-y divide-gray-200">
                            <thead class="bg-gray-50 border-b border-gray-300">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relasi</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="databaseDataTableBody" class="divide-y divide-gray-200">
                                @forelse($currentUseCase['database_data'] as $database)
                                    <tr data-id="{{ $database['id_database'] }}" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-[200px] truncate whitespace-normal">{!! $database['keterangan'] !!}</td>  
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $database['relasi'] !!}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-center text-sm font-medium flex justify-center items-center space-x-2">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- Detail (Mata): Biru dengan ikon putih --}}
                                                    <a href="{{ route('docs.use_case_database_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'databaseId' => $database['id_database']
                                                    ]) }}" class="btn-action bg-blue-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Edit (Pensil): Kuning dengan ikon hitam --}}
                                                    <button class="edit-database-btn btn-action bg-yellow-400 text-black" data-id="{{ $database['id_database'] }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Hapus (Sampah): Merah dengan ikon putih --}}
                                                    <button class="delete-database-btn btn-action bg-red-600 text-white" data-id="{{ $database['id_database'] }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- Non-admin hanya tombol detail --}}
                                                    <a href="{{ route('docs.use_case_database_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'databaseId' => $database['id_database']
                                                    ]) }}" class="btn-action bg-blue-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500 italic">Tidak ada data Database.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- UAT Data Section --}}
                <hr class="my-6 border-gray-200">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">UAT Data</h2>
                <div id="content-UAT" class="mb-8">
                    <div class="flex flex-col items-start mb-4">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="flex justify-start items-center gap-x-2 mt-1 mb-2"> {{-- Tombol Tambah & Print untuk UAT --}}
                                    <button id="addUatDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fa fa-plus-circle mr-2"></i>Tambah
                                    </button>
                                    <button class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-medium rounded-md shadow transition">
                                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                    </button>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm">
                        <table class="min-w-full bg-white divide-y divide-gray-200">
                            <thead class="bg-gray-50 border-b border-gray-300">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Proses Usecase</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="uatDataTableBody" class="divide-y divide-gray-200">
                                @forelse($currentUseCase['uat_data'] as $uat)
                                    <tr data-id="{{ $uat['id_uat'] }}" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $uat['nama_proses_usecase'] }}</td>
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-[200px] truncate whitespace-normal">{!! $uat['keterangan_uat'] !!}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($uat['status_uat'] === 'Passed') bg-green-100 text-green-800
                                                @elseif($uat['status_uat'] === 'Failed') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $uat['status_uat'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 whitespace-nowrap text-center text-sm font-medium flex justify-center items-center space-x-2">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- Detail (Mata): Biru dengan ikon putih --}}
                                                    <a href="{{ route('docs.use_case_uat_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'uatId' => $uat['id_uat']
                                                    ]) }}" class="btn-action bg-blue-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Edit (Pensil): Kuning dengan ikon hitam --}}
                                                    <button class="edit-uat-btn btn-action bg-yellow-400 text-black" data-id="{{ $uat['id_uat'] }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Hapus (Sampah): Merah dengan ikon putih --}}
                                                    <button class="delete-uat-btn btn-action bg-red-600 text-white" data-id="{{ $uat['id_uat'] }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- Non-admin hanya tombol detail --}}
                                                    <a href="{{ route('docs.use_case_uat_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'uatId' => $uat['id_uat']
                                                    ]) }}" class="btn-action bg-blue-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500 italic">Tidak ada data UAT.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection