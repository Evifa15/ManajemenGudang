</div> <script src="<?php echo BASE_URL; ?>js/main.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <?php if (isset($_SESSION['welcome_popup'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Selamat Datang!',
                    text: 'Halo, <?php echo htmlspecialchars($_SESSION['welcome_popup']); ?>!',
                    icon: 'success',
                    timer: 2000, // Hilang dalam 2 detik
                    showConfirmButton: false,
                    position: 'center'
                });
            });
        </script>
        
        <?php 
            // Hapus session agar popup tidak muncul terus saat refresh
            unset($_SESSION['welcome_popup']); 
        ?>
    <?php endif; ?>

</body>
</html>