/**
 * --------------------------------------------------------------------------
 * Auth Module
 * Menangani logika di halaman Login.
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    // Deteksi apakah kita sedang di halaman login dengan mencari form login
    const loginForm = document.querySelector('form[action*="auth/processLogin"]');

    if (loginForm) {
        initLoginLogic(loginForm);
    }
});

function initLoginLogic(form) {
    const btnSubmit = form.querySelector('button[type="submit"]');
    const emailInput = form.querySelector('input[name="email"]');
    const passInput = form.querySelector('input[name="password"]');

    // 1. Cegah Double Submit & Tampilkan Loading
    form.addEventListener('submit', function(e) {
        // Validasi Sederhana
        if (!emailInput.value.trim() || !passInput.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Harap isi Email dan Password.',
                confirmButtonColor: '#152e4d'
            });
            return;
        }

        // Efek Loading pada Tombol
        if (btnSubmit) {
            const originalText = btnSubmit.innerText;
            
            // Ubah tombol jadi disabled & loading
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="ph ph-spinner" style="animation: spin 1s linear infinite;"></i> Memproses...';
            btnSubmit.style.opacity = '0.7';
            btnSubmit.style.cursor = 'not-allowed';

            // Safety: Kembalikan tombol jika server tidak merespon dalam 10 detik
            setTimeout(() => {
                btnSubmit.disabled = false;
                btnSubmit.innerText = originalText;
                btnSubmit.style.opacity = '1';
                btnSubmit.style.cursor = 'pointer';
            }, 10000);
        }
    });

    // 2. Fitur Toggle Password (Mata) - Opsional
    // Jika Anda ingin menambahkan ikon mata di input password pada login.php
    const togglePassBtn = document.getElementById('togglePassword');
    
    if (togglePassBtn && passInput) {
        togglePassBtn.addEventListener('click', function() {
            // Ubah tipe input
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            
            // Ubah Ikon (Gunakan Phosphor Icons)
            const icon = this.querySelector('i');
            if (icon) {
                if (type === 'text') {
                    icon.classList.replace('ph-eye-slash', 'ph-eye');
                } else {
                    icon.classList.replace('ph-eye', 'ph-eye-slash');
                }
            }
        });
    }
}