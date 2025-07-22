{{-- resources/views/docs/partials/_detail_data_modal.blade.php --}}
<div id="detailDataModal" class="modal">
    <div class="modal-content modal-detail-content">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="detailDataModalTitle">Detail Data</h3>
        <div class="detail-content-wrapper">
            {{-- Konten detail akan diisi oleh JavaScript --}}
        </div>
        <div class="flex justify-end mt-4">
            <button type="button" id="closeDetailDataModalBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Tutup</button>
        </div>
    </div>
</div>

<style>
    /* Tambahan CSS untuk modal detail view */
    .modal-detail-content {
        max-width: 800px; /* Lebih lebar untuk detail konten */
        max-height: 90vh;
        overflow-y: auto;
    }
    .detail-item {
        margin-bottom: 1rem;
    }
    .detail-item label {
        font-weight: 600;
        color: #4a5568;
        display: block;
        margin-bottom: 0.25rem;
    }
    .detail-item p {
        color: #2d3748;
        background-color: #f7fafc; /* gray-50 */
        padding: 0.5rem 0.75rem;
        border-radius: 0.25rem;
        border: 1px solid #e2e8f0;
    }
    .detail-item .ck-content {
        /* Gaya untuk konten CKEditor yang ditampilkan */
        border: 1px solid #e2e8f0;
        padding: 0.75rem;
        border-radius: 0.25rem;
        background-color: #f7fafc;
        min-height: 100px; /* Agar cukup ruang */
    }
</style>