document.addEventListener('DOMContentLoaded', () => {
    // Logic for content tabs (UAT, Pengkodean, Database)
    const contentTabsContainer = document.getElementById('content-tabs');
    const kontenView = document.getElementById('kontenView');
    const editorContainer = document.getElementById('editor-container');
    const editorForm = document.getElementById('editor-form');
    const editorContentTypeInput = document.getElementById('editor-content-type');
    const cancelEditorBtn = document.getElementById('cancel-editor-btn');
    
    // Access initialBladeData from global scope
    const allDocsContents = initialBladeData.allDocsContents;
    const initialActiveContentType = new URLSearchParams(window.location.search).get('content_type') || 'Default';

    if (contentTabsContainer) {
        const updateContentDisplay = (contentType) => {
            const contentData = allDocsContents.find(doc => doc.title === contentType);
            if (kontenView) {
                kontenView.innerHTML = contentData ? contentData.content : '<h3>Konten belum tersedia.</h3><p>Silakan edit untuk menambahkan konten untuk ' + contentType + '.</p>';
            }
            if (editorContentTypeInput) {
                editorContentTypeInput.value = contentType;
            }
            const url = new URL(window.location);
            url.searchParams.set('content_type', contentType);
            window.history.pushState({}, '', url);
        };

        contentTabsContainer.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                contentTabsContainer.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const contentType = button.dataset.contentType;
                updateContentDisplay(contentType);

                if (!editorContainer.classList.contains('hidden') && typeof currentEditorInstance !== 'undefined' && currentEditorInstance !== null) {
                    const contentToLoad = allDocsContents.find(doc => doc.title === contentType)?.content || '# Belum ada konten untuk ' + contentType;
                    currentEditorInstance.setData(contentToLoad);
                }
            });
        });

        let initialTabButton = contentTabsContainer.querySelector(`[data-content-type="${initialActiveContentType}"]`);
        if (!initialTabButton && contentTabsContainer.children.length > 0) {
            initialTabButton = contentTabsContainer.children[0];
        }
        if (initialTabButton) {
            initialTabButton.classList.add('active');
            updateContentDisplay(initialTabButton.dataset.contentType);
        }
    }

    if (editorForm) {
        editorForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!currentEditorInstance) {
                showNotification('Editor belum diinisialisasi.', 'error');
                return;
            }

            const loadingNotif = showNotification('Menyimpan konten...', 'loading');
            const menuId = initialBladeData.menu_id;    
            const content = currentEditorInstance.getData();
            const currentCategoryForm = document.querySelector('input[name="currentCategoryFromForm"]').value;
            const currentPageForm = document.querySelector('input[name="currentPageFromForm"]').value;
            const contentType = document.getElementById('editor-content-type').value;    

            try {
                const data = await fetchAPI(`/docs/save/${menuId}`, {
                    method: 'POST',
                    body: JSON.stringify({
                        _token: csrfToken,    
                        content: content,
                        currentCategoryFromForm: currentCategoryForm,
                        currentPageFromForm: currentPageForm,
                        content_type: contentType    
                    })
                });

                hideNotification(loadingNotif);
                showCentralSuccessPopup(data.success || 'Konten berhasil disimpan!');

                window.location.reload();    

            } catch (error) {
                console.error('Error saving content:', error);
                hideNotification(loadingNotif);    
                
                if (error.message) {
                    showNotification(`Gagal menyimpan konten: ${error.message}`, 'error');
                } else {
                    showNotification('Terjadi kesalahan tidak dikenal saat menyimpan konten.', 'error');
                }
            }
        });
    }
    
    if (cancelEditorBtn) {
        cancelEditorBtn.addEventListener('click', closeEditor);
    }
});