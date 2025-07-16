<div id="menu-modal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="modal-title">Tambah Menu Baru</h3>
        <form id="menu-form">
            <input type="hidden" id="form_menu_id" name="menu_id">
            <input type="hidden" id="form_method" name="_method" value="POST">
            <input type="hidden" id="form_category" name="category" value="{{ $currentCategory }}">
            <div class="mb-4">
                <label for="form_menu_nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Menu:</label>
                <input type="text" id="form_menu_nama" name="menu_nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            <div class="mb-4">
                <label for="form_menu_icon" class="block text-gray-700 text-sm font-bold mb-2">Ikon (Font Awesome Class):</label>
                <input type="text" id="form_menu_icon" name="menu_icon" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="Contoh: fa-solid fa-house">
            </div>
            <div class="mb-4">
                <label for="form_menu_child" class="block text-gray-700 text-sm font-bold mb-2">Parent Menu:</label>
                <select id="form_menu_child" name="menu_child" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <option value="0">Tidak Ada (Menu Utama)</option>
                    {{-- Opsi parent akan diisi oleh JavaScript saat modal dibuka --}}
                </select>
            </div>
            <div class="mb-4">
                <label for="form_menu_order" class="block text-gray-700 text-sm font-bold mb-2">Urutan:</label>
                <input type="number" id="form_menu_order" name="menu_order" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="0" required>
            </div>
            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="form_menu_status" name="menu_status" value="1" class="form-checkbox h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">Centang Jika Ingin Menu Ini Memiliki Konten</span>
                </label>
            </div>
            <div class="flex items-center justify-end space-x-3">
                <button type="button" id="cancel-menu-form-btn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                <button type="submit" id="submit-menu-form-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>