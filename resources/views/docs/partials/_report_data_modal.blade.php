{{-- resources/views/docs/partials/_report_data_modal.blade.php --}}
<div id="reportDataModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="reportDataModalTitle">Tambah Data Report</h3>
        <form id="reportDataForm">
            @csrf
            <input type="hidden" id="reportDataFormUseCaseId" name="use_case_id">
            <input type="hidden" id="reportDataFormId" name="id"> {{-- ID Report Data record (id_report) --}}
            <input type="hidden" id="reportDataFormMethod" name="_method" value="POST">

            <div class="mb-4">
                <label for="form_aktor_report" class="block text-gray-700 text-sm font-bold mb-2">Aktor:</label>
                <input type="text" id="form_aktor_report" name="aktor" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            <div class="mb-4">
                <label for="form_nama_report" class="block text-gray-700 text-sm font-bold mb-2">Nama Report:</label>
                <input type="text" id="form_nama_report" name="nama_report" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            <div class="mb-4">
                <label for="form_keterangan_report" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                <textarea id="form_keterangan_report" name="keterangan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" id="cancelReportDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                <button type="submit" id="submitReportDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>