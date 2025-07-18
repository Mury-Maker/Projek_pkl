<div id="categoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
        <h2 id="categoryModalTitle" class="text-lg font-semibold mb-4">Tambah Kategori</h2>

        <form id="categoryForm">
            @csrf
            <input type="hidden" id="form_category_method" name="_method" value="POST">
            <input type="hidden" id="form_category_slug_to_edit" name="category_slug_to_edit" value="">

            <div class="mb-4">
                <label for="form_category_nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" id="form_category_nama" name="category" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeCategoryModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Batal</button>
                <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>