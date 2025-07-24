{{-- resources/views/docs/partials/_use_case_detail.blade.php --}}

@php
    $currentUseCase = $useCaseData ?? null;
    $hasUseCaseData = $currentUseCase && $currentUseCase->id; // Cek apakah data use case sudah ada
@endphp

<div id="use-case-content-area">
    @if (!$hasUseCaseData)
        <div class="text-center p-8 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Data Detail Aksi Belum Tersedia</h3>
            <p class="text-gray-600">Menu ini diatur untuk memiliki konten (status 'Memiliki Konten'), tetapi data detail aksinya belum ditambahkan.</p>
            @auth
                @if(auth()->user()->role === 'admin')
                    <p class="mt-4 text-sm text-gray-500">Silakan klik tombol "Edit Detail Aksi" di atas untuk menambahkan data use case baru untuk menu ini.</p>
                @endif
            @endauth
        </div>
    @else
        {{-- Detail Aksi --}}
        <h2 class="text-2xl font-bold mb-4">Detail Aksi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="font-semibold text-gray-700">ID Usecase:</p>
                <p>{{ $currentUseCase->usecase_id ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Nama Proses:</p>
                <p>{{ $currentUseCase->nama_proses ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Deskripsi Aksi:</p>
                <p class="prose max-w-none">{!! $currentUseCase->deskripsi_aksi ?? 'N/A' !!}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Aktor:</p>
                <p>{{ $currentUseCase->aktor ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Tujuan:</p>
                <p class="prose max-w-none">{!! $currentUseCase->tujuan ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Kondisi Awal:</p>
                <p class="prose max-w-none">{!! $currentUseCase->kondisi_awal ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Kondisi Akhir:</p>
                <p class="prose max-w-none">{!! $currentUseCase->kondisi_akhir ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Aksi Reaksi:</p>
                <p class="prose max-w-none">{!! $currentUseCase->aksi_reaksi ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Reaksi Sistem:</p>
                <p class="prose max-w-none">{!! $currentUseCase->reaksi_sistem ?? 'N/A' !!}</p>
            </div>
        </div>

        {{-- Content Tabs for UAT, Pengkodean, Database --}}
        @if(!empty($contentTypes) && $hasUseCaseData)
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
                                    <th class="py-2 px-4 border-b">Nama Proses</th>
                                    <th class="py-2 px-4 border-b">Keterangan</th>
                                    <th class="py-2 px-4 border-b">Status</th>
                                    <th class="py-2 px-4 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="uatDataTableBody">
                                @forelse($currentUseCase->uatData as $uat)
                                    <tr data-id="{{ $uat->id }}">
                                        <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 border-b">{{ $uat->nama_proses_uat }}</td>
                                        <td class="py-2 px-4 border-b">{{ $uat->keterangan_uat }}</td>
                                        <td class="py-2 px-4 border-b">{{ $uat->status_uat }}</td>
                                        <td class="py-2 px-4 border-b">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    <button class="edit-uat-btn text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $uat->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="delete-uat-btn text-red-500 hover:text-red-700" data-id="{{ $uat->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
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

                {{-- Data Pengkodean (Sembunyikan secara default) --}}
                <div id="content-Pengkodean" class="content-panel hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-700">DATA PENGKODEAN</h3>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <button id="addPengkodeanDataBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah Data Pengkodean
                                </button>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">No</th>
                                    <th class="py-2 px-4 border-b">Nama Proses</th>
                                    <th class="py-2 px-4 border-b">Keterangan</th>
                                    <th class="py-2 px-4 border-b">Status</th>
                                    <th class="py-2 px-4 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pengkodeanDataTableBody">
                                @forelse($currentUseCase->pengkodeanData as $pengkodean)
                                    <tr data-id="{{ $pengkodean->id }}">
                                        <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 border-b">{{ $pengkodean->nama_proses_pengkodean }}</td>
                                        <td class="py-2 px-4 border-b">{{ $pengkodean->keterangan_pengkodean }}</td>
                                        <td class="py-2 px-4 border-b">{{ $pengkodean->status_pengkodean }}</td>
                                        <td class="py-2 px-4 border-b">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    <button class="edit-pengkodean-btn text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $pengkodean->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="delete-pengkodean-btn text-red-500 hover:text-red-700" data-id="{{ $pengkodean->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada data Pengkodean.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Data Database (Sembunyikan secara default) --}}
                <div id="content-Database" class="content-panel hidden">
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
                                    <th class="py-2 px-4 border-b">Nama Proses</th>
                                    <th class="py-2 px-4 border-b">Keterangan</th>
                                    <th class="py-2 px-4 border-b">Status</th>
                                    <th class="py-2 px-4 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="databaseDataTableBody">
                                @forelse($currentUseCase->databaseData as $database)
                                    <tr data-id="{{ $database->id }}">
                                        <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 border-b">{{ $database->nama_proses_database }}</td>
                                        <td class="py-2 px-4 border-b">{{ $database->keterangan_database }}</td>
                                        <td class="py-2 px-4 border-b">{{ $database->status_database }}</td>
                                        <td class="py-2 px-4 border-b">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    <button class="edit-database-btn text-blue-500 hover:text-blue-700 mr-2" data-id="{{ $database->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="delete-database-btn text-red-500 hover:text-red-700" data-id="{{ $database->id }}">
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
    @endif
</div>