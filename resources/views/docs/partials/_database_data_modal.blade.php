{{-- resources/views/docs/partials/_database_data_modal.blade.php --}}
<div id="databaseDataModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="databaseDataModalTitle">Tambah Data Database</h3>
        <form id="databaseDataForm">
            @csrf
            <input type="hidden" id="databaseDataFormUseCaseId" name="use_case_id">
            <input type="hidden" id="databaseDataFormId" name="id"> {{-- ID Database Data record (id_database) --}}
            <input type="hidden" id="databaseDataFormMethod" name="_method" value="POST">
            <input type="hidden" id="form_gambar_database_current" name="gambar_database_current"> {{-- Input hidden untuk menyimpan path gambar lama saat edit --}}

            <div class="mb-4">
                <label for="form_keterangan_database" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                <textarea id="form_keterangan_database" name="keterangan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24 resize-y"></textarea>
            </div>
            <div class="mb-4">
                <label for="form_gambar_database" class="block text-gray-700 text-sm font-bold mb-2">Gambar Database:</label> {{-- Label diubah --}}
                <input type="file" id="form_gambar_database" name="gambar_database" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"> {{-- Input file baru --}}
                <p class="text-xs text-gray-500 mt-1">Ukuran maksimal 2MB. Format: JPG, PNG, GIF.</p>
                {{-- Area Pratinjau Gambar --}}
                <div id="form_gambar_database_preview" class="mt-2 border border-gray-200 rounded p-2 text-center bg-gray-50 flex justify-center items-center h-24 overflow-hidden">
                    <span class="text-gray-500 text-sm">Tidak ada gambar dipilih atau gambar lama.</span>
                </div>
            </div>
            <div class="mb-4">
                <label for="form_relasi_database" class="block text-gray-700 text-sm font-bold mb-2">Relasi:</label>
                <textarea id="form_relasi_database" name="relasi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24 resize-y"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" id="cancelDatabaseDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                <button type="submit" id="submitDatabaseDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT CKEDITOR UNTUK MODAL INI SUDAH DIHAPUS DAN TIDAK BOLEH ADA DI SINI --}} 