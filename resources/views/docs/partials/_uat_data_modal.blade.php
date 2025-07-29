{{-- resources/views/docs/partials/_uat_data_modal.blade.php --}}
<div id="uatDataModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="uatDataModalTitle">Tambah Data UAT</h3>
        <form id="uatDataForm"> {{-- Hapus enctype="multipart/form-data" --}}
            @csrf
            <input type="hidden" id="uatDataFormUseCaseId" name="use_case_id">
            <input type="hidden" id="uatDataFormId" name="id"> {{-- ID UAT Data record (id_uat) --}}
            <input type="hidden" id="uatDataFormMethod" name="_method" value="POST">

            <div class="mb-4">
                <label for="form_nama_proses_usecase" class="block text-gray-700 text-sm font-bold mb-2">Nama Proses Usecase:</label> {{-- Ubah ID dan name --}}
                <input type="text" id="form_nama_proses_usecase" name="nama_proses_usecase" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            <div class="mb-4">
                <label for="form_keterangan_uat" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                <textarea id="form_keterangan_uat" name="keterangan_uat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
            </div>
            <div class="mb-4">
                <label for="form_status_uat" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                <select id="form_status_uat" name="status_uat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <option value="">Pilih Status</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" id="cancelUatDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                <button type="submit" id="submitUatDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // CKEditor for UAT modal
        if (typeof ClassicEditor !== 'undefined') {
            let editorInstances = {}; // Menyimpan instansi editor

            window.initCkEditorForUatModal = (elementId) => { // Buat fungsi baru
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
            
            document.getElementById('uatDataModal').addEventListener('click', (e) => {
                if (e.target.closest('#uatDataModal') && e.target.closest('.modal-content')) {
                    if (e.currentTarget.classList.contains('show') && !e.target.closest('.ck.ck-editor')) {
                         window.initCkEditorForUatModal('form_gambar_uat');
                    }
                }
            });

            window.setCkEditorDataForUatModal = (elementId, data) => { // Buat fungsi baru
                if (editorInstances[elementId]) {
                    editorInstances[elementId].setData(data);
                } else {
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.value = data;
                    }
                }
            };

            window.getCkEditorDataForUatModal = (elementId) => { // Buat fungsi baru
                return editorInstances[elementId] ? editorInstances[elementId].getData() : '';
            };
        }
    });
</script>