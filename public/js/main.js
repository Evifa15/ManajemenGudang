/**
 * --------------------------------------------------------------------------
 * Main JavaScript Entry Point
 * Menangani logika global: Sidebar, Flash Message, dan Global Event Listeners.
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function() {

    // 1. MOBILE SIDEBAR TOGGLE
    // Menangani buka-tutup sidebar saat mode mobile (layar kecil)
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.app-sidebar');
    
    if (menuToggle && sidebar) {
        // Buat elemen overlay gelap (latar belakang redup)
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        // Klik Tombol Menu -> Buka Sidebar
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        // Klik Overlay (Area gelap) -> Tutup Sidebar
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    // 2. SIDEBAR ACCORDION (Menu Dropdown)
    // Menangani klik pada menu sidebar yang memiliki sub-menu
    const menuItems = document.querySelectorAll('.sidebar-menu > ul > li > a');

    if (menuItems) {
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Cek apakah link ini punya submenu
                const submenu = this.nextElementSibling;

                if (submenu && submenu.classList.contains('submenu')) {
                    e.preventDefault(); // Mencegah pindah halaman
                    
                    // Toggle class active pada elemen <li> induknya
                    const parentLi = this.parentElement;
                    parentLi.classList.toggle('active');
                }
            });
        });
    }

    // 3. FLASH MESSAGE AUTO-HIDE
    // Menghilangkan notifikasi sukses/gagal secara otomatis setelah 3 detik
    const alertBox = document.querySelector('.flash-message');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => { alertBox.remove(); }, 500);
        }, 3000);
    }

    // 4. GLOBAL DELETE HANDLER (Delegation)
    // Menangani semua tombol dengan class '.btn-delete' di seluruh aplikasi
    // Menggunakan helper dari ui-helpers.js untuk menampilkan SweetAlert
    document.body.addEventListener('click', function(e) {
        // Deteksi klik pada tombol delete atau icon di dalamnya
        const deleteBtn = e.target.closest('.btn-delete');

        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();

            // Ambil URL dari atribut data-url
            const url = deleteBtn.getAttribute('data-url');

            if (url) {
                // Cek apakah helper UI sudah dimuat
                if (window.WMSUI) {
                    window.WMSUI.confirmDelete(url);
                } else {
                    // Fallback jika ui-helpers.js belum dimuat/error
                    if (confirm("Yakin hapus data ini? (Data tidak bisa dikembalikan)")) {
                        window.location.href = url;
                    }
                }
            }
        }
    });

});