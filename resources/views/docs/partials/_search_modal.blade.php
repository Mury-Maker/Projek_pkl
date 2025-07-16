<div id="search-overlay" class="search-modal">
    <div id="search-modal-content">
        <div id="search-input-container" class="flex items-center px-4 py-3">
            <i class="fa fa-search text-gray-400 mr-3"></i>
            <input type="text" id="search-overlay-input" placeholder="Cari dokumentasi..." class="flex-grow bg-transparent border-none focus:outline-none text-lg text-gray-800">
            <button id="clear-search-input-btn" class="text-gray-400 hover:text-gray-600 focus:outline-none hidden">
                <i class="fa fa-times-circle"></i>
            </button>
            <button id="close-search-overlay-btn" class="text-gray-500 hover:text-gray-700 ml-3 focus:outline-none p-1 rounded">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div id="search-results-list" class="empty-state">
            <p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>
            {{-- Hasil pencarian akan diisi di sini oleh JavaScript --}}
        </div>
    </div>
</div>