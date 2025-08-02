{{-- usecase_index.blade.php --}}

@extends('docs.index')

@section('content')
    <div class="max-w-7xl mx-auto px-2 sm:px-2 lg:px-2">
        <div class="bg-white rounded-lg shadow-md p-6">

            @if($sqlFile)
            <div class="sql">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">File SQL Tersedia</h2>
            <p>Nama File Saat ini: <strong>{{ $sqlFile->file_name }}</strong></p>

            <div class="parsing">
                <p>Tekan tombol berikut untuk menampilkan ERD</p>
                <form action="{{ route('sql.parse', ['navmenuId' => $menu_id]) }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="navmenu_id" value="{{ $menu_id }}">
                        <label for="parser">Metode Parsing:</label>
                        <select name="parser" id="parser" class="form-select">
                            <option value="phpmyadmin">phpMyAdmin</option>
                            <option value="sqlyog">SQLyog</option>
                            <option value="heidisql">HeidiSQL</option>
                        </select>
                    <button type="submit" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                        Parse Sql
                    </button>
                </form>
                <hr style="margin-bottom: 12px">
            </div>

            <div class="deleteSql">
                <p>Hapus File</p>
                <form action="{{ route('sql.delete', ['navmenuId' => $menu_id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file dan semua datanya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Hapus File & Data</button>
                </form>
                <hr style="margin-bottom: 12px">
            </div>

            <div class="updateSql">
                <p>Ganti file:</p>
                <form action="{{ route('sql.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="navmenu_id" value="{{ $menu_id }}" />
                    

                    <div class="mb-4">
                        <input type="file" name="sql_file" accept=".sql" required class="block w-full border rounded p-2" />
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
                </form>
            </div>
        </div>


            @else
            <div class="nosql">
            <p>Menu ID: {{ $menu_id }}</p>
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Belum ada file SQL</h2>
            <p>Silahkan upload file disini:</p>

            <form action="{{ route('sql.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="navmenu_id" value="{{ $menu_id }}" />

                <div class="mb-4">
                    <input type="file" name="sql_file" accept=".sql" required class="block w-full border rounded p-2" />
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
            </form>
            </div>

            @endif

        </div>
    </div>
@endsection

