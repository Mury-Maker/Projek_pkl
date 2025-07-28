// public/js/use-case-logic.js (Full Code dengan Perbaikan Akhir)

document.addEventListener('DOMContentLoaded', () => {
    // Pastikan csrfToken tersedia secara global, contoh:
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- Elemen Umum untuk Use Case ---
    const useCaseModal = document.getElementById('useCaseModal');
    const useCaseModalTitle = document.getElementById('useCaseModalTitle');
    const useCaseForm = document.getElementById('useCaseForm');
    const useCaseFormMenuId = document.getElementById('useCaseFormMenuId');
    const useCaseFormUseCaseId = document.getElementById('useCaseFormUseCaseId');
    const useCaseFormMethod = document.getElementById('useCaseFormMethod');
    const cancelUseCaseFormBtn = document.getElementById('cancelUseCaseFormBtn');

    // --- Elemen Khusus untuk Halaman Daftar Use Case (use_case_index) ---
    const addUseCaseBtn = document.getElementById('addUseCaseBtn');
    const useCaseIndexTableBody = document.getElementById('useCaseIndexTableBody');

    // --- Elemen Khusus untuk Halaman Detail Use Case (use_case_detail_page) ---
    const editSingleUseCaseBtn = document.getElementById('editSingleUseCaseBtn');

    // UAT Data Elements
    const uatDataModal = document.getElementById('uatDataModal');
    const uatDataModalTitle = document.getElementById('uatDataModalTitle');
    const uatDataForm = document.getElementById('uatDataForm');
    const uatDataFormUseCaseId = document.getElementById('uatDataFormUseCaseId');
    const uatDataFormId = document.getElementById('uatDataFormId');
    const uatDataFormMethod = document.getElementById('uatDataFormMethod');
    const cancelUatDataFormBtn = document.getElementById('cancelUatDataFormBtn');
    const addUatDataBtn = document.getElementById('addUatDataBtn');
    const uatDataTableBody = document.getElementById('uatDataTableBody');
    const formNamaProsesUsecase = document.getElementById('form_nama_proses_usecase');
    // Elemen untuk Input File Gambar UAT dan Pratinjau
    const formGambarUatInput = document.getElementById('form_gambar_uat');
    const formGambarUatPreview = document.getElementById('form_gambar_uat_preview');
    const formGambarUatCurrent = document.getElementById('form_gambar_uat_current');

    // Report Data Elements
    const reportDataModal = document.getElementById('reportDataModal');
    const reportDataModalTitle = document.getElementById('reportDataModalTitle');
    const reportDataForm = document.getElementById('reportDataForm');
    const reportDataFormUseCaseId = document.getElementById('reportDataFormUseCaseId');
    const reportDataFormId = document.getElementById('reportDataFormId');
    const reportDataFormMethod = document.getElementById('reportDataFormMethod');
    const cancelReportDataFormBtn = document.getElementById('cancelReportDataFormBtn');
    const addReportDataBtn = document.getElementById('addReportDataBtn');
    const reportDataTableBody = document.getElementById('reportDataTableBody');

    // Database Data Elements
    const databaseDataModal = document.getElementById('databaseDataModal');
    const databaseDataModalTitle = document.getElementById('databaseDataModalTitle');
    const databaseDataForm = document.getElementById('databaseDataForm');
    const databaseDataFormUseCaseId = document.getElementById('databaseDataFormUseCaseId');
    const databaseDataFormId = document.getElementById('databaseDataFormId');
    const databaseDataFormMethod = document.getElementById('databaseDataFormMethod');
    const cancelDatabaseDataFormBtn = document.getElementById('cancelDatabaseDataFormBtn');
    const addDatabaseDataBtn = document.getElementById('addDatabaseDataBtn');
    const databaseDataTableBody = document.getElementById('databaseDataTableBody');
    // Elemen untuk Input File Gambar Database dan Pratinjau
    const formGambarDatabaseInput = document.getElementById('form_gambar_database');
    const formGambarDatabasePreview = document.getElementById('form_gambar_database_preview');
    const formGambarDatabaseCurrent = document.getElementById('form_gambar_database_current');

    // --- Detail Data Modal Elements ---
    const detailDataModal = document.getElementById('detailDataModal');
    const detailDataModalTitle = document.getElementById('detailDataModalTitle');
    const detailContentWrapper = detailDataModal ? detailDataModal.querySelector('.detail-content-wrapper') : null;
    const closeDetailDataModalBtn = document.getElementById('closeDetailDataModalBtn');

    // --- Global Data from Blade ---
    const currentMenuId = window.initialBladeData.menu_id;
    const useCasesList = window.initialBladeData.useCases || [];
    const singleUseCaseData = window.initialBladeData.singleUseCase || {};
    // const activeContentType = window.initialBladeData.activeContentType; // Tidak digunakan lagi karena tidak ada tabs

    // --- CKEditor Elements IDs for UseCase Modal (Detail Aksi) ---
    // CKEditor tetap digunakan di modal UseCase utama
    const useCaseCkEditorElements = {
        deskripsi_aksi: 'form_deskripsi_aksi',
        tujuan: 'form_tujuan',
        kondisi_awal: 'form_kondisi_awal',
        kondisi_akhir: 'form_kondisi_akhir',
        aksi_reaksi: 'form_aksi_reaksi',
        reaksi_sistem: 'form_reaksi_sistem',
    };

    // --- General Functions for Use Case Modal (Add/Edit Action) ---
    const openUseCaseModal = (mode, useCase = null) => {
        if (!useCaseForm) {
            console.error("Elemen 'useCaseForm' tidak ditemukan di DOM.");
            showNotification("Gagal membuka modal Tindakan: Elemen form tidak ditemukan.", "error");
            return;
        }

        useCaseForm.reset();
        useCaseFormMenuId.value = currentMenuId;

        if (mode === 'create') {
            useCaseModalTitle.textContent = 'Tambah Tindakan Baru';
            useCaseFormMethod.value = 'POST';
            useCaseFormUseCaseId.value = '';
            document.getElementById('form_usecase_id').value = '';
            if (window.setCkEditorDataForUseCaseModal) {
                Object.values(useCaseCkEditorElements).forEach(id => window.setCkEditorDataForUseCaseModal(id, ''));
            }
        } else if (mode === 'edit' && useCase) {
            useCaseModalTitle.textContent = `Edit Tindakan: ${useCase.nama_proses}`;
            useCaseFormMethod.value = 'PUT';
            useCaseFormUseCaseId.value = useCase.id;

            document.getElementById('form_usecase_id').value = useCase.usecase_id || '';
            document.getElementById('form_nama_proses').value = useCase.nama_proses || '';

            if (window.setCkEditorDataForUseCaseModal) {
                for (const key in useCaseCkEditorElements) {
                    window.setCkEditorDataForUseCaseModal(useCaseCkEditorElements[key], useCase[key] || '');
                }
            }
        }
        useCaseModal.classList.add('show');
        setTimeout(() => {
            if (window.initCkEditorForUseCaseModal) {
                Object.values(useCaseCkEditorElements).forEach(window.initCkEditorForUseCaseModal);
            }
        }, 100);
    };

    const closeUseCaseModal = () => {
        useCaseModal.classList.remove('show');
    };

    if (cancelUseCaseFormBtn) {
        cancelUseCaseFormBtn.addEventListener('click', closeUseCaseModal);
    }

    if (useCaseForm) {
        useCaseForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loadingNotif = showNotification('Menyimpan tindakan...', 'loading');
            const method = useCaseFormMethod.value;
            const useCaseId = useCaseFormUseCaseId.value;
            let url = useCaseId ? `/api/usecase/${useCaseId}` : '/api/usecase';

            const formData = new FormData(useCaseForm);

            for (const key in useCaseCkEditorElements) {
                if (window.getCkEditorDataForUseCaseModal) {
                    formData.set(key, window.getCkEditorDataForUseCaseModal(useCaseCkEditorElements[key]));
                }
            }
            
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            try {
                const options = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(jsonData),
                };

                if (method === 'PUT') {
                    options.headers['X-HTTP-Method-Override'] = 'PUT';
                }

                const data = await fetchAPI(url, options);

                hideNotification(loadingNotif);
                showCentralSuccessPopup(data.success);
                closeUseCaseModal();
                window.location.reload();
            } catch (error) {
                console.error('Error saving use case:', error);
                hideNotification(loadingNotif);
                showNotification(`Gagal menyimpan tindakan: ${error.message || 'Terjadi kesalahan'}`, 'error');
            }
        });
    }


    // --- Logic for Use Case List Page (use_case_index) ---
    if (addUseCaseBtn) {
        addUseCaseBtn.addEventListener('click', () => {
            openUseCaseModal('create');
        });
    }

    if (useCaseIndexTableBody) {
        useCaseIndexTableBody.addEventListener('click', async (e) => {
            if (e.target.closest('.edit-usecase-index-btn')) {
                const useCaseId = e.target.closest('.edit-usecase-index-btn').dataset.id;
                const useCase = useCasesList.find(uc => uc.id == useCaseId);
                if (useCase) {
                    openUseCaseModal('edit', useCase);
                } else {
                    showNotification('Data tindakan tidak ditemukan.', 'error');
                }
            }

            if (e.target.closest('.delete-usecase-index-btn')) {
                const useCaseId = e.target.closest('.delete-usecase-index-btn').dataset.id;
                const useCaseNama = e.target.closest('.delete-usecase-index-btn').dataset.nama;
                Swal.fire({
                    title: 'Yakin ingin menghapus tindakan ini?',
                    text: `Anda akan menghapus tindakan "${useCaseNama}" beserta semua data UAT, Report, dan Database terkait. Tindakan ini tidak dapat dibatalkan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const loadingNotif = showNotification('Menghapus tindakan...', 'loading');
                        try {
                            const data = await fetchAPI(`/api/usecase/${useCaseId}`, { method: 'DELETE' });
                            hideNotification(loadingNotif);
                            showCentralSuccessPopup(data.success);
                            window.location.reload();
                        }
                        catch (error) {
                            console.error('Error deleting use case:', error);
                            hideNotification(loadingNotif);
                            showNotification(`Gagal menghapus tindakan: ${error.message || 'Terjadi kesalahan'}`, 'error');
                        }
                    }
                });
            }
        });
    }


    // --- Logic for Use Case Detail Page (use_case_detail_page) ---
    if (editSingleUseCaseBtn) {
        editSingleUseCaseBtn.addEventListener('click', () => {
            if (singleUseCaseData && singleUseCaseData.id) {
                openUseCaseModal('edit', singleUseCaseData);
            } else {
                showNotification('Data use case tidak ditemukan.', 'error');
            }
        });
    }

    // --- UAT Data Modal Logic ---
    const openUatDataModal = (mode, uat = null) => {
        if (!uatDataForm) {
            console.error("Elemen 'uatDataForm' tidak ditemukan di DOM.");
            showNotification("Gagal membuka modal UAT: Elemen form tidak ditemukan.", "error");
            return;
        }

        uatDataForm.reset();
        uatDataFormUseCaseId.value = singleUseCaseData.id || '';

        // Bersihkan pratinjau gambar dan input file
        if (formGambarUatInput) formGambarUatInput.value = ''; // Clear file input
        if (formGambarUatPreview) formGambarUatPreview.innerHTML = ''; // Clear preview area
        if (formGambarUatCurrent) formGambarUatCurrent.value = ''; // Clear hidden input for current image path

        if (!uatDataFormUseCaseId.value) {
            showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data UAT.', 'error');
            return;
        }

        if (mode === 'create') {
            uatDataModalTitle.textContent = 'Tambah Data UAT Baru';
            uatDataFormMethod.value = 'POST';
            uatDataFormId.value = '';
            if (formNamaProsesUsecase) {
                formNamaProsesUsecase.value = singleUseCaseData.nama_proses || '';
            }
            // Hapus CKEditor init di sini
            // if (window.setCkEditorDataForUatModal) { Object.values(uatCkEditorElements).forEach(id => window.setCkEditorDataForUatModal(id, '')); }
        } else if (mode === 'edit' && uat) {
            uatDataModalTitle.textContent = 'Edit Data UAT';
            uatDataFormMethod.value = 'POST'; // Tetap POST karena akan pakai FormData (untuk PUT dengan file)
            uatDataFormId.value = uat.id_uat;

            if (formNamaProsesUsecase) {
                formNamaProsesUsecase.value = uat.nama_proses_usecase || '';
            }
            document.getElementById('form_keterangan_uat').value = uat.keterangan_uat || '';
            document.getElementById('form_status_uat').value = uat.status_uat || '';

            // Tampilkan gambar UAT yang ada (jika ada)
            if (formGambarUatPreview && uat.gambar_uat) {
                formGambarUatPreview.innerHTML = `<img src="${uat.gambar_uat}" alt="Gambar UAT" class="max-w-full h-auto rounded">`;
                formGambarUatPreview.classList.remove('hidden'); // Pastikan pratinjau terlihat
                formGambarUatCurrent.value = uat.gambar_uat; // Simpan path gambar lama di hidden input
            } else if (formGambarUatPreview) {
                formGambarUatPreview.innerHTML = 'Tidak ada gambar lama.';
                formGambarUatPreview.classList.remove('hidden');
            }
            // Hapus CKEditor init di sini
            // if (window.setCkEditorDataForUatModal) { window.setCkEditorDataForUatModal('form_gambar_uat', uat.gambar_uat || ''); }
        }
        uatDataModal.classList.add('show');
        // Hapus CKEditor setTimeout init di sini
        // setTimeout(() => { if (window.initCkEditorForUatModal) { Object.values(uatCkEditorElements).forEach(window.initCkEditorForUatModal); } }, 100);
    };

    const closeUatDataModal = () => {
        uatDataModal.classList.remove('show');
    };

    if (addUatDataBtn) {
        addUatDataBtn.addEventListener('click', () => {
            openUatDataModal('create');
        });
    }

    if (cancelUatDataFormBtn) {
        cancelUatDataFormBtn.addEventListener('click', closeUatDataModal);
    }

    if (uatDataTableBody) {
        uatDataTableBody.addEventListener('click', async (e) => {
            // View Detail UAT
            if (e.target.closest('.view-uat-btn')) {
                const uatId = parseInt(e.target.closest('.view-uat-btn').dataset.id);
                const uat = (singleUseCaseData.uat_data || []).find(item => item.id_uat === uatId);
                if (uat) {
                    openDetailDataModal('Detail Data UAT', `
                        <div class="detail-item">
                            <label>ID UAT:</label>
                            <p>${uat.id_uat}</p>
                        </div>
                        <div class="detail-item">
                            <label>Nama Proses Usecase:</label>
                            <p>${uat.nama_proses_usecase || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label>
                            <p>${uat.keterangan_uat || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <p>${uat.status_uat || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Gambar UAT:</label>
                            ${uat.gambar_uat ? `<img src="${uat.gambar_uat}" alt="Gambar UAT" class="max-w-full h-auto rounded">` : '<p>Tidak ada gambar</p>'}
                        </div>
                    `);
                } else {
                    showNotification('Detail data UAT untuk entri ini tidak tersedia. Silakan cek data.', 'error');
                }
            }
            // Edit UAT
            else if (e.target.closest('.edit-uat-btn')) {
                const uatId = parseInt(e.target.closest('.edit-uat-btn').dataset.id);
                const uat = (singleUseCaseData.uat_data || []).find(item => item.id_uat === uatId);
                if (uat) {
                    openUatDataModal('edit', uat);
                } else {
                    showNotification('Data UAT yang ingin diedit tidak ditemukan. Mungkin ID tidak cocok.', 'error');
                }
            }
            // Delete UAT
            else if (e.target.closest('.delete-uat-btn')) {
                const uatId = e.target.closest('.delete-uat-btn').dataset.id;
                Swal.fire({
                    title: 'Yakin ingin menghapus data UAT ini?',
                    text: 'Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const loadingNotif = showNotification('Menghapus data UAT...', 'loading');
                        try {
                            const data = await fetchAPI(`/api/usecase/uat/${uatId}`, { method: 'DELETE' });
                            hideNotification(loadingNotif);
                            showCentralSuccessPopup(data.success);
                            window.location.reload();
                        }
                        catch (error) {
                            console.error('Error deleting UAT data:', error);
                            hideNotification(loadingNotif);
                            showNotification(`Gagal menghapus data UAT: ${error.message || 'Terjadi kesalahan'}`, 'error');
                        }
                    }
                });
            }
        });
    }

    if (uatDataForm) {
        uatDataForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loadingNotif = showNotification('Menyimpan data UAT...', 'loading');
            const uatId = document.getElementById('uatDataFormId').value;
            const method = document.getElementById('uatDataFormMethod').value;
            let url = uatId ? `/api/usecase/uat/${uatId}` : '/api/usecase/uat';

            const formData = new FormData(uatDataForm);
            
            // Jika tidak ada file baru yang dipilih, tambahkan path gambar lama agar backend tahu
            if (formGambarUatInput && formGambarUatInput.files.length === 0 && formGambarUatCurrent && formGambarUatCurrent.value) {
                formData.append('gambar_uat_current', formGambarUatCurrent.value);
            }

            try {
                const options = {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        // PENTING: Hapus baris 'Content-Type': 'application/json' ini!
                        // Browser akan otomatis mengatur 'multipart/form-data' saat FormData dikirim dengan file
                        // 'Content-Type': 'application/json',
                    },
                    body: formData, // Kirim FormData langsung
                };

                if (method === 'PUT') {
                    options.method = 'POST'; // Untuk file upload, sering pakai POST + X-HTTP-Method-Override
                    options.headers['X-HTTP-Method-Override'] = 'PUT';
                }

                const data = await fetchAPI(url, options);

                hideNotification(loadingNotif);
                showCentralSuccessPopup(data.success);
                closeUatDataModal();
                window.location.reload();
            } catch (error) {
                console.error('Error saving UAT data:', error);
                hideNotification(loadingNotif);
                showNotification(`Gagal menyimpan data UAT: ${error.message || 'Terjadi kesalahan'}`, 'error');
            }
        });
    }

    // --- Report Data Modal Logic ---
    const openReportDataModal = (mode, report = null) => {
        if (!reportDataForm) {
            console.error("Elemen 'reportDataForm' tidak ditemukan di DOM.");
            showNotification("Gagal membuka modal Report: Elemen form tidak ditemukan.", "error");
            return;
        }
        reportDataForm.reset();
        reportDataFormUseCaseId.value = singleUseCaseData.id || '';

        if (!reportDataFormUseCaseId.value) {
            showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Report.', 'error');
            return;
        }

        if (mode === 'create') {
            reportDataModalTitle.textContent = 'Tambah Data Report Baru';
            reportDataFormMethod.value = 'POST';
            reportDataFormId.value = '';
        } else if (mode === 'edit' && report) {
            reportDataModalTitle.textContent = 'Edit Data Report';
            reportDataFormMethod.value = 'PUT'; // Ini tetap PUT karena tidak ada file
            reportDataFormId.value = report.id_report;

            document.getElementById('form_aktor_report').value = report.aktor || '';
            document.getElementById('form_nama_report').value = report.nama_report || '';
            document.getElementById('form_keterangan_report').value = report.keterangan || '';
        }
        reportDataModal.classList.add('show');
    };

    const closeReportDataModal = () => {
        reportDataModal.classList.remove('show');
    };

    if (addReportDataBtn) {
        addReportDataBtn.addEventListener('click', () => {
            openReportDataModal('create');
        });
    }

    if (cancelReportDataFormBtn) {
        cancelReportDataFormBtn.addEventListener('click', closeReportDataModal);
    }

    if (reportDataTableBody) {
        reportDataTableBody.addEventListener('click', async (e) => {
            // View Detail Report
            if (e.target.closest('.view-report-btn')) {
                const reportId = parseInt(e.target.closest('.view-report-btn').dataset.id);
                const report = (singleUseCaseData.report_data || []).find(item => item.id_report === reportId);
                if (report) {
                    openDetailDataModal('Detail Data Report', `
                        <div class="detail-item">
                            <label>ID Report:</label>
                            <p>${report.id_report}</p>
                        </div>
                        <div class="detail-item">
                            <label>Aktor:</label>
                            <p>${report.aktor || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Nama Report:</label>
                            <p>${report.nama_report || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label>
                            <p>${report.keterangan || 'N/A'}</p>
                        </div>
                    `);
                } else {
                    showNotification('Data Report tidak ditemukan.', 'error');
                }
            }
            // Edit Report
            else if (e.target.closest('.edit-report-btn')) {
                const reportId = parseInt(e.target.closest('.edit-report-btn').dataset.id);
                const report = (singleUseCaseData.report_data || []).find(item => item.id_report === reportId);
                if (report) {
                    openReportDataModal('edit', report);
                } else {
                    showNotification('Data Report tidak ditemukan.', 'error');
                }
            }
            // Delete Report
            else if (e.target.closest('.delete-report-btn')) {
                const reportId = e.target.closest('.delete-report-btn').dataset.id;
                Swal.fire({
                    title: 'Yakin ingin menghapus data Report ini?',
                    text: 'Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const loadingNotif = showNotification('Menghapus data Report...', 'loading');
                        try {
                            const data = await fetchAPI(`/api/usecase/report/${reportId}`, { method: 'DELETE' });
                            hideNotification(loadingNotif);
                            showCentralSuccessPopup(data.success);
                            window.location.reload();
                        } catch (error) {
                            console.error('Error deleting Report data:', error);
                            hideNotification(loadingNotif);
                            showNotification(`Gagal menghapus data Report: ${error.message || 'Terjadi kesalahan'}`, 'error');
                        }
                    }
                });
            }
        });
    }

    if (reportDataForm) {
        reportDataForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loadingNotif = showNotification('Menyimpan data Report...', 'loading');
            const reportId = document.getElementById('reportDataFormId').value;
            const method = document.getElementById('reportDataFormMethod').value;
            let url = reportId ? `/api/usecase/report/${reportId}` : '/api/usecase/report';

            const formData = new FormData(reportDataForm); // Menggunakan FormData untuk kirim data form

            try {
                const options = {
                    method: 'POST', // Default POST
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        // 'Content-Type' tidak perlu diset jika mengirim FormData
                    },
                    body: formData, // Kirim FormData langsung
                };
                if (method === 'PUT') {
                    options.method = 'POST'; // POST dengan X-HTTP-Method-Override untuk PUT dengan FormData
                    options.headers['X-HTTP-Method-Override'] = 'PUT';
                }

                const data = await fetchAPI(url, options);

                hideNotification(loadingNotif);
                showCentralSuccessPopup(data.success);
                closeReportDataModal();
                window.location.reload();
            } catch (error) {
                console.error('Error saving Report data:', error);
                hideNotification(loadingNotif);
                showNotification(`Gagal menyimpan data Report: ${error.message || 'Terjadi kesalahan'}`, 'error');
            }
        });
    }

    // --- Database Data Modal Logic ---
    const openDatabaseDataModal = (mode, database = null) => {
        if (!databaseDataForm) {
            console.error("Elemen 'databaseDataForm' tidak ditemukan di DOM.");
            showNotification("Gagal membuka modal Database: Elemen form tidak ditemukan.", "error");
            return;
        }
        databaseDataForm.reset();
        databaseDataFormUseCaseId.value = singleUseCaseData.id || '';

        // Bersihkan pratinjau gambar dan input file
        if (formGambarDatabaseInput) formGambarDatabaseInput.value = ''; // Clear file input
        if (formGambarDatabasePreview) formGambarDatabasePreview.innerHTML = ''; // Clear preview area
        if (formGambarDatabaseCurrent) formGambarDatabaseCurrent.value = ''; // Clear hidden input for current image path

        if (!databaseDataFormUseCaseId.value) {
            showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Database.', 'error');
            return;
        }

        if (mode === 'create') {
            databaseDataModalTitle.textContent = 'Tambah Data Database Baru';
            databaseDataFormMethod.value = 'POST';
            databaseDataFormId.value = '';
            // Hapus CKEditor init di sini
            // if (window.setCkEditorDataForDatabaseModal) { Object.values(databaseCkEditorElements).forEach(id => window.setCkEditorDataForDatabaseModal(id, '')); }
        } else if (mode === 'edit' && database) {
            databaseDataModalTitle.textContent = 'Edit Data Database';
            databaseDataFormMethod.value = 'PUT'; // Tetap POST karena akan pakai FormData (untuk PUT dengan file)
            databaseDataFormId.value = database.id_database;

            document.getElementById('form_keterangan_database').value = database.keterangan || '';
            document.getElementById('form_relasi_database').value = database.relasi || '';

            // Tampilkan gambar Database yang ada (jika ada)
            if (formGambarDatabasePreview && database.gambar_database) {
                formGambarDatabasePreview.innerHTML = `<img src="${database.gambar_database}" alt="Gambar Database" class="max-w-full h-auto rounded">`;
                formGambarDatabasePreview.classList.remove('hidden'); // Pastikan pratinjau terlihat
                formGambarDatabaseCurrent.value = database.gambar_database; // Simpan path gambar lama di hidden input
            } else if (formGambarDatabasePreview) {
                formGambarDatabasePreview.innerHTML = 'Tidak ada gambar lama.';
                formGambarDatabasePreview.classList.remove('hidden');
            }
            // Hapus CKEditor init di sini
            // if (window.setCkEditorDataForDatabaseModal) { window.setCkEditorDataForDatabaseModal('form_gambar_database', database.gambar_database || ''); }
        }
        databaseDataModal.classList.add('show');
        // Hapus CKEditor setTimeout init di sini
        // setTimeout(() => { if (window.initCkEditorForDatabaseModal) { Object.values(databaseCkEditorElements).forEach(window.initCkEditorForDatabaseModal); } }, 100);
    };

    const closeDatabaseDataModal = () => {
        databaseDataModal.classList.remove('show');
    };

    if (addDatabaseDataBtn) {
        addDatabaseDataBtn.addEventListener('click', () => {
            openDatabaseDataModal('create');
        });
    }

    if (cancelDatabaseDataFormBtn) {
        cancelDatabaseDataFormBtn.addEventListener('click', closeDatabaseDataModal);
    }

    if (databaseDataTableBody) {
        databaseDataTableBody.addEventListener('click', async (e) => {
            // View Detail Database
            if (e.target.closest('.view-database-btn')) {
                const databaseId = parseInt(e.target.closest('.view-database-btn').dataset.id);
                const database = (singleUseCaseData.database_data || []).find(item => item.id_database === databaseId);
                if (database) {
                    openDetailDataModal('Detail Data Database', `
                        <div class="detail-item">
                            <label>ID Database:</label>
                            <p>${database.id_database}</p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label>
                            <p>${database.keterangan || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Gambar Database:</label>
                            ${database.gambar_database ? `<img src="${database.gambar_database}" alt="Gambar Database" class="max-w-full h-auto rounded">` : '<p>Tidak ada gambar</p>'}
                        </div>
                        <div class="detail-item">
                            <label>Relasi:</label>
                            <p>${database.relasi || 'N/A'}</p>
                        </div>
                    `);
                } else {
                    showNotification('Data Database tidak ditemukan.', 'error');
                }
            }
            // Edit Database
            else if (e.target.closest('.edit-database-btn')) {
                const databaseId = parseInt(e.target.closest('.edit-database-btn').dataset.id);
                const database = (singleUseCaseData.database_data || []).find(item => item.id_database === databaseId);
                if (database) {
                    openDatabaseDataModal('edit', database);
                } else {
                    showNotification('Data Database tidak ditemukan.', 'error');
                }
            }
            // Delete Database
            else if (e.target.closest('.delete-database-btn')) {
                const databaseId = e.target.closest('.delete-database-btn').dataset.id;
                Swal.fire({
                    title: 'Yakin ingin menghapus data Database ini?',
                    text: 'Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const loadingNotif = showNotification('Menghapus data Database...', 'loading');
                        try {
                            const data = await fetchAPI(`/api/usecase/database/${databaseId}`, { method: 'DELETE' });
                            hideNotification(loadingNotif);
                            showCentralSuccessPopup(data.success);
                            window.location.reload();
                        } catch (error) {
                            console.error('Error deleting Database data:', error);
                            hideNotification(loadingNotif);
                            showNotification(`Gagal menghapus data Database: ${error.message || 'Terjadi kesalahan'}`, 'error');
                        }
                    }
                });
            }
        });
    }

    if (databaseDataForm) {
        databaseDataForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loadingNotif = showNotification('Menyimpan data Database...', 'loading');
            const databaseId = document.getElementById('databaseDataFormId').value;
            const method = document.getElementById('databaseDataFormMethod').value;
            let url = databaseId ? `/api/usecase/database/${databaseId}` : '/api/usecase/database';

            const formData = new FormData(databaseDataForm); // Menggunakan FormData langsung
            // Jika tidak ada file baru yang dipilih, tambahkan path gambar lama agar backend tahu
            if (formGambarDatabaseInput && formGambarDatabaseInput.files.length === 0 && formGambarDatabaseCurrent && formGambarDatabaseCurrent.value) {
                formData.append('gambar_database_current', formGambarDatabaseCurrent.value);
            }

            try {
                const options = {
                    method: 'POST', // Default POST, akan di-override jika PUT
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        // 'Content-Type' TIDAK perlu diset karena mengirim FormData dengan file
                    },
                    body: formData, // Mengirim FormData langsung
                };

                if (method === 'PUT') {
                    options.method = 'POST'; // Untuk file upload, sering pakai POST + X-HTTP-Method-Override
                    options.headers['X-HTTP-Method-Override'] = 'PUT';
                }

                const data = await fetchAPI(url, options);

                hideNotification(loadingNotif);
                showCentralSuccessPopup(data.success);
                closeDatabaseDataModal();
                window.location.reload();
            } catch (error) {
                console.error('Error saving Database data:', error);
                hideNotification(loadingNotif);
                showNotification(`Gagal menyimpan data Database: ${error.message || 'Terjadi kesalahan'}`, 'error');
            }
        });
    }


    // --- Global Functions for Detail Modal ---
    function openDetailDataModal(title, contentHtml) {
        if (!detailDataModal || !detailDataModalTitle || !detailContentWrapper) {
            console.error('Elemen modal detail tidak ditemukan. Pastikan _detail_data_modal.blade.php sudah disertakan.');
            return;
        }
        detailDataModalTitle.textContent = title;
        detailContentWrapper.innerHTML = contentHtml;
        detailDataModal.classList.add('show');
    }

    function closeDetailDataModal() {
        if (detailDataModal) {
            detailDataModal.classList.remove('show');
        }
    }

    if (closeDetailDataModalBtn) {
        closeDetailDataModalBtn.addEventListener('click', closeDetailDataModal);
    }
});