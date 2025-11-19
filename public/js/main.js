// Jalankan skrip setelah semua halaman (HTML) dimuat
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Bagian 1: Notifikasi Otomatis Hilang ---
    
    // 1. Cari elemen notifikasi
    const flashMessage = document.querySelector('.flash-message');

    // 2. Jika elemennya ada
    if (flashMessage) {
        
        // Timer 1: Mulai menghilang (setelah 3 detik)
        // Ini akan memicu transisi 'opacity' di CSS
        setTimeout(function() {
            flashMessage.classList.add('fade-out');
        }, 3000); // 3 detik

        // Timer 2: Hapus total dari layar (setelah 3.5 detik)
        // Ini menjamin elemennya hilang (display: none)
        // tidak peduli animasinya berhasil atau tidak.
        // (3000ms tunggu + 500ms untuk animasi)
        setTimeout(function() {
            flashMessage.style.display = 'none';
        }, 3500); // 3.5 detik
    }

    // 1. Cari SEMUA tombol dengan kelas .btn-delete
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    // 2. Loop setiap tombol dan tambahkan 'event listener'
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            
            // 3. Ambil URL hapus dari atribut 'data-url'
            const deleteUrl = this.getAttribute('data-url');

            // 4. Munculkan SweetAlert!
            Swal.fire({
                title: 'Anda Yakin?',
                text: "Data yang sudah dihapus tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus data!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                // 5. Jika user mengklik "Ya, hapus data!"
                if (result.isConfirmed) {
                    // 6. Alihkan browser ke URL hapus
                    window.location.href = deleteUrl;
                }
            });

        });
    });

});