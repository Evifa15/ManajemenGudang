<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password Baru - Manajemen Gudang</title>
    <!-- Kita pakai CSS yang sama dengan login -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css"> 
</head>
<body>
    <div class="login-container" style="width: 400px;">
        <h2>Ganti Password Anda</h2>
        <p>Halo, <?php echo $_SESSION['nama_lengkap']; ?>! Karena ini login pertama Anda, harap ganti password Anda.</p>

        <!-- Tampilkan pesan error jika ada -->
        <?php 
            if (isset($_SESSION['flash_message'])) {
                $flash = $_SESSION['flash_message'];
                echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
                unset($_SESSION['flash_message']);
            }
        ?>

        <form action="<?php echo BASE_URL; ?>auth/processForceChangePassword" method="POST">
            
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-login">Simpan Password Baru</button>
            </div>
        </form>
    </div>
</body>
</html>