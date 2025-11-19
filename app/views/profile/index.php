<?php
    // 1. Memuat "Kepala" Halaman
    require_once APPROOT . '/views/templates/header.php';
?>

<?php
    // 2. Memuat "Sidebar" yang TEPAT
    // Cek role dari session untuk memuat sidebar yang benar
    if ($_SESSION['role'] == 'admin') {
        require_once APPROOT . '/views/templates/sidebar_admin.php';
    } else if ($_SESSION['role'] == 'staff') {
        require_once APPROOT . '/views/templates/sidebar_staff.php';
    }
    // (Nanti tambahkan else if untuk 'pemilik' dan 'peminjam')
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Profil Saya</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>profile/processProfileInfo" method="POST" enctype="multipart/form-data">
            
            <fieldset>
                <legend>Informasi Pribadi</legend>
                
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="foto_profil">
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap (Wajib)</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" 
                           value="<?php echo htmlspecialchars($data['user']['nama_lengkap']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email (Read-Only)</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($data['user']['email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="role">Role (Read-Only)</label>
                    <input type="text" id="role" name="role" 
                           value="<?php echo htmlspecialchars($data['user']['role']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" 
                           value="<?php echo htmlspecialchars($data['user']['tempat_lahir'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                           value="<?php echo htmlspecialchars($data['user']['tanggal_lahir'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="agama">Agama</label>
                    <select id="agama" name="agama">
                        <option value="">-- Pilih Agama --</option>
                        <option value="Islam" <?php if($data['user']['agama'] == 'Islam') echo 'selected'; ?>>Islam</option>
                        <option value="Kristen" <?php if($data['user']['agama'] == 'Kristen') echo 'selected'; ?>>Kristen</option>
                        <option value="Katolik" <?php if($data['user']['agama'] == 'Katolik') echo 'selected'; ?>>Katolik</option>
                        <option value="Hindu" <?php if($data['user']['agama'] == 'Hindu') echo 'selected'; ?>>Hindu</option>
                        <option value="Buddha" <?php if($data['user']['agama'] == 'Buddha') echo 'selected'; ?>>Buddha</option>
                        <option value="Konghucu" <?php if($data['user']['agama'] == 'Konghucu') echo 'selected'; ?>>Konghucu</option>
                    </select>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Info Kontak & Alamat</legend>
                
                <div class="form-group">
                    <label for="telepon">Nomor Telepon / HP</label>
                    <input type="text" id="telepon" name="telepon" 
                           value="<?php echo htmlspecialchars($data['user']['telepon'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($data['user']['alamat'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="kota">Kota</label>
                    <input type="text" id="kota" name="kota" 
                           value="<?php echo htmlspecialchars($data['user']['kota'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="provinsi">Provinsi</label>
                    <input type="text" id="provinsi" name="provinsi" 
                           value="<?php echo htmlspecialchars($data['user']['provinsi'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="kode_pos">Kode Pos</label>
                    <input type="text" id="kode_pos" name="kode_pos" 
                           value="<?php echo htmlspecialchars($data['user']['kode_pos'] ?? ''); ?>">
                </div>
            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan Profil</button>
            </div>
        </form>
    </div>

    <div class="form-container" style="margin-top: 30px;">
        <form action="<?php echo BASE_URL; ?>profile/processChangePassword" method="POST">
            <fieldset>
                <legend>Ganti Password Mandiri</legend>
                
                <div class="form-group">
                    <label for="old_password">Password Lama</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ubah Password Saya</button>
            </div>
        </form>
    </div>
</main>
<?php
    // 4. Memuat "Kaki" Halaman
    require_once APPROOT . '/views/templates/footer.php';
?>