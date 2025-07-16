document.addEventListener('DOMContentLoaded', () => {
    const openSearchModalBtnHeader = document.getElementById('open-search-modal-btn-header');
    const searchOverlay = document.getElementById('search-overlay');
    const searchOverlayInput = document.getElementById('search-overlay-input');
    const searchResultsList = document.getElementById('search-results-list');
    const clearSearchInputBtn = document.getElementById('clear-search-input-btn');
    const closeSearchOverlayBtn = document.getElementById('close-search-overlay-btn');

    const openSearchModal = () => {
        searchOverlay.classList.add('open');
        searchOverlayInput.focus();
        searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>';
        clearSearchInputBtn.classList.add('hidden');
    };

    const closeSearchModalSearchOverlay = () => {
        searchOverlay.classList.remove('open');
        searchOverlayInput.value = '';
    };

    if (openSearchModalBtnHeader) {
        openSearchModalBtnHeader.addEventListener('click', openSearchModal);
    }

    if (searchOverlay) {
        searchOverlay.addEventListener('click', (e) => {
            if (e.target === searchOverlay || e.target.closest('#close-search-overlay-btn')) {
                closeSearchModalSearchOverlay();
            }
        });
    }
    if (closeSearchOverlayBtn) {
        closeSearchOverlayBtn.addEventListener('click', closeSearchModalSearchOverlay);
    }

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openSearchModal();
        }
        if (e.key === 'Escape' && searchOverlay.classList.contains('open')) {
            closeSearchModalSearchOverlay();
        }
    });

    let searchTimeout;
    if (searchOverlayInput) {
        searchOverlayInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchOverlayInput.value.trim();
            
            if (query.length > 0) {
                clearSearchInputBtn.classList.remove('hidden');
            } else {
                clearSearchInputBtn.classList.add('hidden');
                searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>'; // Clear results immediately if input is empty
                return; // Stop here if query is empty
            }

            if (query.length < 2) {
                searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Masukkan minimal 2 karakter untuk mencari.</p>';
                return;
            }

            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mencari...</p>';

            searchTimeout = setTimeout(async () => {
                try {
                    const data = await fetchAPI(`/api/search?query=${query}`);    
                    searchResultsList.innerHTML = '';
                    
                    if (data.results && data.results.length > 0) {
                        const groupedResultsByMenuName = data.results.reduce((acc, result) => {
                            if (!acc[result.name]) {
                                acc[result.name] = [];
                            }
                            acc[result.name].push(result);
                            return acc;
                        }, {});

                        for (const menuName in groupedResultsByMenuName) {
                            const menuGroupHeader = document.createElement('div');
                            menuGroupHeader.className = 'search-result-category';
                            menuGroupHeader.textContent = menuName;
                            searchResultsList.appendChild(menuGroupHeader);

                            groupedResultsByMenuName[menuName].forEach(result => {
                                const itemLink = document.createElement('a');
                                itemLink.href = result.url;
                                itemLink.className = 'search-result-item px-6 py-3 block hover:bg-gray-100 rounded-md';
                                itemLink.innerHTML = `
                                    <div class="search-title">${result.name}</div>
                                    <p class="search-category-info">${result.category_name}</p>
                                    ${result.context && result.context !== 'Judul Menu' ? `<p class="search-context">${result.context}</p>` : ''}
                                `;
                                itemLink.addEventListener('click', closeSearchModalSearchOverlay);
                                searchResultsList.appendChild(itemLink);
                            });
                        }
                    } else {
                        searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Tidak ada hasil yang ditemukan.</p>';
                    }

                } catch (error) {
                    searchResultsList.innerHTML = '<p class="text-center text-red-500 p-8">Terjadi kesalahan saat mencari.</p>';
                    console.error('Search API Error:', error);
                }
            }, 300);
        });
    }
    // --- FIX FOR CLEAR SEARCH INPUT BUTTON ---
    if (clearSearchInputBtn) {
        clearSearchInputBtn.addEventListener('click', () => {
            searchOverlayInput.value = ''; // Clear the input field
            searchOverlayInput.focus();    // Keep focus on the input for immediate re-typing
            clearSearchInputBtn.classList.add('hidden'); // Hide the clear button
            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>'; // Reset search results display
        });
    }
    // --- END FIX ---
});