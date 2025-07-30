document.getElementById('add-button').addEventListener('click', () => {
    const wrapper = document.createElement('div');
    wrapper.classList.add('image-group', 'mb-2');

    wrapper.innerHTML = `
        <input type="file" name="images[]" class="form-control image-input mb-1">
        <img src="#" class="preview-image hidden mb-2 max-h-40">
        <button type="button" class="remove-button bg-red-500 text-white px-2 py-1 rounded-lg" style="margin-bottom: 18px">Hapus</button>
    `;

    document.getElementById('image-fields').appendChild(wrapper);
});

// Hapus input group
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-button')) {
        e.target.parentElement.remove();
    }
});

// Preview saat file dipilih
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('image-input')) {
        const file = e.target.files[0];
        const preview = e.target.parentElement.querySelector('.preview-image');

        if (file && preview) {
            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
});

// Preview gambar

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeModal = document.getElementById('closeModal');
        const nextBtn = document.getElementById('nextImage');
        const prevBtn = document.getElementById('prevImage');

        const imgElements = Array.from(document.querySelectorAll('.preview-modal-img'));
        const imgSources = imgElements.map(el => el.dataset.imgSrc);
        let currentIndex = 0;

        function showModal(index) {
            currentIndex = index;
            modalImg.src = imgSources[currentIndex];
            modal.classList.remove('hidden');
        }

        imgElements.forEach((imgEl, index) => {
            imgEl.addEventListener('click', () => showModal(index));
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % imgSources.length;
            modalImg.src = imgSources[currentIndex];
        });

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + imgSources.length) % imgSources.length;
            modalImg.src = imgSources[currentIndex];
        });

        closeModal.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });

        // Optional: Keyboard support
        document.addEventListener('keydown', (e) => {
            if (!modal.classList.contains('hidden')) {
                if (e.key === 'ArrowRight') nextBtn.click();
                if (e.key === 'ArrowLeft') prevBtn.click();
                if (e.key === 'Escape') closeModal.click();
            }
        });
    });
