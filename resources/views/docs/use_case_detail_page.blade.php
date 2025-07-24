{{-- resources/views/docs/use_case_detail_page.blade.php --}}

@extends('docs.index')

@section('action-buttons')
    @auth
        @if(auth()->user()->role === 'admin')
            {{-- Tombol Edit Detail Aksi (untuk useCase yang sedang dilihat) --}}
            <button id="editSingleUseCaseBtn" class="btn btn-primary ml-auto">
                <i class="fa-solid fa-file-pen"></i> Edit Detail Aksi
            </button>
        @endif
    @endauth
@endsection

@section('content')
    @php
        $currentUseCase = $singleUseCase; // singleUseCase sudah diset di controller
        $hasUseCaseData = $currentUseCase && isset($currentUseCase['id']);
    @endphp

    <div id="use-case-content-area">
        {{-- Detail Aksi --}}
        <h2 class="text-2xl font-bold mb-4">Detail Aksi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="font-semibold text-gray-700">ID Usecase:</p>
                <p>{{ $currentUseCase['usecase_id'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Nama Proses:</p>
                <p>{{ $currentUseCase['nama_proses'] ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Deskripsi Aksi:</p>
                <p class="prose max-w-none">{!! $currentUseCase['deskripsi_aksi'] ?? 'N/A' !!}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Aktor:</p>
                <p>{{ $currentUseCase['aktor'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Tujuan:</p>
                <p class="prose max-w-none">{!! $currentUseCase['tujuan'] ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Kondisi Awal:</p>
                <p class="prose max-w-none">{!! $currentUseCase['kondisi_awal'] ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Kondisi Akhir:</p>
                <p class="prose max-w-none">{!! $currentUseCase['kondisi_akhir'] ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Aksi Reaksi:</p>
                <p class="prose max-w-none">{!! $currentUseCase['aksi_reaksi'] ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Reaksi Sistem:</p>
                <p class="prose max-w-none">{!! $currentUseCase['reaksi_sistem'] ?? 'N/A' !!}</p>
            </div>
        </div>

        {{-- REPORT Section with Tabs --}}
        @if(!empty($contentTypes))
            <h2 class="text-2xl font-bold mb-4">Report</h2>
            <div class="content-tabs" id="content-tabs">
                @foreach($contentTypes as $type)
                    <button type="button" class="tab-button {{ $activeContentType === $type ? 'active' : '' }}" data-content-type="{{ $type }}">{{ $type }}</button>
                @endforeach
            </div>

            {{-- Konten Dinamis Berdasarkan Tab yang Aktif --}}
            <div id="dynamic-content-area" class="mt-4">
                {{-- Data UAT --}}
                <div id="content-UAT" class="content-panel {{ $activeContentType === 'UAT' ? '' : 'hidden' }}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-700">DATA UAT</h3>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <button id="addUatDataBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah Data UAT
                                </button>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">No</th>
                                    <th class="py-2 px-4 border-b">Nama Proses Usecase</th>
                                    <th class="py-2 px-4 border-b">Keterangan</th>
                                    <th class="py-2 px-4 border-b">Status</th>
                                    <th class="py-2 px-4 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="uatDataTableBody">
                                @forelse($currentUseCase['uat_data'] as $uat)
                                    <tr data-id="{{ $uat['id_uat'] }}">
                                        <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 border-b">{{ $uat['nama_proses_usecase'] }}</td>
                                        <td class="py-2 px-4 border-b">{!! $uat['keterangan_uat'] !!}</td>
                                        <td class="py-2 px-4 border-b">{{ $uat['status_uat'] }}</td>
                                        <td class="py-2 px-4 border-b">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- PERBAIKAN: Kembali ke <a> untuk detail UAT (mengarah ke halaman baru) --}}
                                                    <a href="{{ route('docs.use_case_uat_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'uatId' => $uat['id_uat']
                                                    ]) }}" class="btn-action text-green-500 hover:text-green-700 mr-2" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="edit-uat-btn btn-action text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $uat['id_uat'] }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="delete-uat-btn btn-action text-red-500 hover:text-red-700" data-id="{{ $uat['id_uat'] }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- PERBAIKAN: Kembali ke <a> untuk detail UAT (non-admin, mengarah ke halaman baru) --}}
                                                    <a href="{{ route('docs.use_case_uat_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'uatId' => $uat['id_uat']
                                                    ]) }}" class="btn-action text-green-500 hover:text-green-700 mr-2" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada data UAT.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Data Report --}}
                <div id="content-Report" class="content-panel {{ $activeContentType === 'Report' ? '' : 'hidden' }}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-700">DATA REPORT</h3>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <button id="addReportDataBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah Data Report
                                </button>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">No</th>
                                    <th class="py-2 px-4 border-b">Aktor</th>
                                    <th class="py-2 px-4 border-b">Nama Report</th>
                                    <th class="py-2 px-4 border-b">Keterangan</th>
                                    <th class="py-2 px-4 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="reportDataTableBody">
                                @forelse($currentUseCase['report_data'] as $report)
                                    <tr data-id="{{ $report['id_report'] }}">
                                        <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 border-b">{{ $report['aktor'] }}</td>
                                        <td class="py-2 px-4 border-b">{{ $report['nama_report'] }}</td>
                                        <td class="py-2 px-4 border-b">{!! $report['keterangan'] !!}</td>
                                        <td class="py-2 px-4 border-b">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- PERBAIKAN: Kembali ke <a> untuk detail Report (mengarah ke halaman baru) --}}
                                                    <a href="{{ route('docs.use_case_report_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'reportId' => $report['id_report']
                                                    ]) }}" class="btn-action text-green-500 hover:text-green-700 mr-2" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="edit-report-btn btn-action text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $report['id_report'] }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="delete-report-btn btn-action text-red-500 hover:text-red-700" data-id="{{ $report['id_report'] }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada data Report.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Data Database --}}
                <div id="content-Database" class="content-panel {{ $activeContentType === 'Database' ? '' : 'hidden' }}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-700">DATA DATABASE</h3>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <button id="addDatabaseDataBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah Data Database
                                </button>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">No</th>
                                    <th class="py-2 px-4 border-b">Keterangan</th>
                                    <th class="py-2 px-4 border-b">Gambar Database</th>
                                    <th class="py-2 px-4 border-b">Relasi</th>
                                    <th class="py-2 px-4 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="databaseDataTableBody">
                                @forelse($currentUseCase['database_data'] as $database)
                                    <tr data-id="{{ $database['id_database'] }}">
                                        <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 border-b">{!! $database['keterangan'] !!}</td>
                                        <td class="py-2 px-4 border-b">
                                            @if($database['gambar_database'])
                                                <div class="max-w-[150px] max-h-[100px] overflow-hidden">
                                                    {!! $database['gambar_database'] !!}
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b">{!! $database['relasi'] !!}</td>
                                        <td class="py-2 px-4 border-b">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- PERBAIKAN: Kembali ke <a> untuk detail Database (mengarah ke halaman baru) --}}
                                                    <a href="{{ route('docs.use_case_database_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem['menu_nama']),
                                                        'useCaseSlug' => Str::slug($currentUseCase['nama_proses']),
                                                        'databaseId' => $database['id_database']
                                                    ]) }}" class="btn-action text-green-500 hover:text-green-700 mr-2" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="edit-database-btn btn-action text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $database['id_database'] }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="delete-database-btn btn-action text-red-500 hover:text-red-700" data-id="{{ $database['id_database'] }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada data Database.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection