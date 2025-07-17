document.addEventListener('DOMContentLoaded', function () {
    const logoutForm = document.getElementById('logout-form');
    const logoutBtn = document.getElementById('logout-btn');

    if (logoutForm && logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault(); // Hindari submit langsung

            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: 'Anda akan logout dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    logoutForm.submit();
                }
            });
        });
    }
});
