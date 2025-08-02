{{-- resources/views/docs/uat_detail_page.blade.php --}}

@extends('docs.index')


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
                <p>Daftar Gambar yang sudah ada:</p>

                {{-- Menampilkan daftar gambar --}}
                <div class="daftar-gambar flex flex-wrap gap-4">

                    @foreach ($uatImgs as $index => $uimg)
                        <div class="relative group w-fit">
                        <img src="{{ asset($uimg->link) }}"
                            alt="Gambar UAT"
                            class="max-h-40 rounded shadow cursor-pointer preview-modal-img"
                            data-img-index="{{ $index }}"
                            data-img-src="{{ asset($uimg->link) }}">


                            @auth
                            @if(auth()->user()->role === 'admin')
                            <form action="{{ route('uats.deleteImage', $uimg->id) }}" method="POST" class="absolute top-1 right-1 hidden group-hover:block">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-600 text-white px-2 py-1 text-xs rounded shadow">Hapus</button>
                            </form>
                            @endif
                            @endauth


                        </div>
                    @endforeach
                </div>

                <!-- Modal Preview Gambar -->
                <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
                    <span id="closeModal" class="absolute top-5 right-6 text-white text-4xl cursor-pointer z-50">&times;</span>

                    <!-- Navigasi Panah -->
                    <button id="prevImage" class="absolute left-4 text-white text-5xl z-50">&#10094;</button>
                    <button id="nextImage" class="absolute right-4 text-white text-5xl z-50">&#10095;</button>

                    <!-- Gambar -->
                    <img id="modalImage" src="#" class="max-h-[90vh] max-w-[90vw] rounded shadow-lg z-40 transition-all duration-300">
                </div>
                
                @auth
                @if(auth()->user()->role === 'admin')
                <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">
                <p>Tambahkan Gambar disini:</p>

                {{-- FORM Tambah gambar --}}
                    <form action="{{ route('uats.storeImages') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="uats_id" value="{{ $uatData->id_uat }}">

                        <div id="image-fields">
                            <div class="image-group mb-2">
                                <input type="file" name="images[]" class="form-control image-input mb-1">
                                <img src="#" class="preview-image mb-2 hidden max-h-40" >
                                <button type="button" class="remove-button bg-red-500 text-white px-2 py-1 rounded-lg" style="margin-bottom: 18px">Hapus</button>
                            </div>
                        </div>

                        <button type="button" id="add-button" class="bg-blue-500 text-white px-4 py-2 mt-2 rounded-lg">Tambah Gambar</button>
                        <br>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 mt-4 rounded-lg">Simpan Gambar</button>
                    </form>
                    @endif
                    @endauth
                {{--  --}}
            </div>
        </div>
    </div>
@endsection