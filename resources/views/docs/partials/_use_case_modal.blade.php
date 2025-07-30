{{-- resources/views/docs/partials/_use_case_modal.blade.php --}}
<div id="useCaseModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="useCaseModalTitle">Detail Aksi</h3>
        <form id="useCaseForm">
            @csrf
            <input type="hidden" id="useCaseFormMenuId" name="menu_id" value="{{ $menu_id ?? '' }}">
            <input type="hidden" id="useCaseFormUseCaseId" name="id"> {{-- ID use_cases record --}}
            <input type="hidden" id="useCaseFormMethod" name="_method" value="POST"> {{-- Default for create --}}

            {{-- Grid Kontainer Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- ID Usecase (Sekarang Readonly) --}}
                <div class="mb-4">
                    <label for="form_usecase_id" class="block text-gray-700 text-sm font-bold mb-2">ID Usecase:</label>
                    <input type="text" id="form_usecase_id" name="usecase_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100" readonly>
                </div>
                <div class="mb-4">
                    <label for="form_nama_proses" class="block text-gray-700 text-sm font-bold mb-2">Nama Proses:</label>
                    <input type="text" id="form_nama_proses" name="nama_proses" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                
                {{-- Deskripsi Aksi (Mengambil 2 kolom) --}}
                <div class="mb-4 md:col-span-2">
                    <label for="form_deskripsi_aksi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Aksi:</label>
                    <textarea id="form_deskripsi_aksi" name="deskripsi_aksi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 ckeditor-small"></textarea>
                </div>

                {{-- Aktor dan Tujuan (Berbagi 2 kolom) --}}
                <div class="mb-4">
                    <label for="form_aktor" class="block text-gray-700 text-sm font-bold mb-2">Aktor:</label>
                    <input type="text" id="form_aktor" name="aktor" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
                <div class="mb-4">
                    <label for="form_tujuan" class="block text-gray-700 text-sm font-bold mb-2">Tujuan:</label>
                    <input id="form_tujuan" name="tujuan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 ckeditor-small"></input>
                </div>

                {{-- Kondisi Awal (Mengambil 2 kolom) --}}
                <div class="mb-4 md:col-span-2">
                    <label for="form_kondisi_awal" class="block text-gray-700 text-sm font-bold mb-2">Kondisi Awal:</label>
                    <input id="form_kondisi_awal" name="kondisi_awal" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 ckeditor-small"></input>
                </div>
                {{-- Kondisi Akhir (Mengambil 2 kolom) --}}
                <div class="mb-4 md:col-span-2">
                    <label for="form_kondisi_akhir" class="block text-gray-700 text-sm font-bold mb-2">Kondisi Akhir:</label>
                    <input id="form_kondisi_akhir" name="kondisi_akhir" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 ckeditor-small"></input>
                </div>
                {{-- Aksi Reaksi (Mengambil 2 kolom) --}}
                <div class="mb-4 md:col-span-2">
                    <label for="form_aksi_reaksi" class="block text-gray-700 text-sm font-bold mb-2">Aksi Aktor:</label>
                    <input id="form_aksi_reaksi" name="aksi_reaksi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 ckeditor-small"></input>
                </div>
                {{-- Reaksi Sistem (Mengambil 2 kolom) --}}
                <div class="mb-4 md:col-span-2">
                    <label for="form_reaksi_sistem" class="block text-gray-700 text-sm font-bold mb-2">Reaksi Sistem:</label>
                    <input id="form_reaksi_sistem" name="reaksi_sistem" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 ckeditor-small"></input>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" id="cancelUseCaseFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                <button type="submit" id="submitUseCaseFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof ClassicEditor !== 'undefined') {
            let editorInstances = {};

            window.initCkEditorForUseCaseModal = (elementId) => {
                if (!editorInstances[elementId]) {
                    const element = document.getElementById(elementId);
                    if (element) {
                        ClassicEditor
                            .create(element, window.CKEDITOR_CONFIG)
                            .then(editor => {
                                editorInstances[elementId] = editor;
                            })
                            .catch(error => {
                                console.error(`Error initializing CKEditor for ${elementId}:`, error);
                            });
                    }
                }
            };
            
            document.getElementById('useCaseModal').addEventListener('click', (e) => {
                if (e.target.closest('#useCaseModal') && e.target.closest('.modal-content')) {
                    if (e.currentTarget.classList.contains('show') && !e.target.closest('.ck.ck-editor')) {
                         // Inisialisasi semua editor saat modal terlihat
                         Object.values(ckEditorElements).forEach(window.initCkEditorForUseCaseModal);
                    }
                }
            });

            window.setCkEditorDataForUseCaseModal = (elementId, data) => {
                if (editorInstances[elementId]) {
                    editorInstances[elementId].setData(data);
                } else {
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.value = data;
                    }
                }
            };

            window.getCkEditorDataForUseCaseModal = (elementId) => {
                return editorInstances[elementId] ? editorInstances[elementId].getData() : '';
            };
        }
    });
</script>