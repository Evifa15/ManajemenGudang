<?php
    require_once APPROOT . '/views/templates/header.php';
    
    // Load Sidebar Sesuai Role
    if ($_SESSION['role'] == 'admin') {
        require_once APPROOT . '/views/templates/sidebar_admin.php';
    } else if ($_SESSION['role'] == 'staff') {
        require_once APPROOT . '/views/templates/sidebar_staff.php';
    } else if ($_SESSION['role'] == 'pemilik') {
        require_once APPROOT . '/views/templates/sidebar_pemilik.php';
    } else {
        require_once APPROOT . '/views/templates/sidebar_peminjam.php';
    }

    // Helper Foto Profil
    $fotoPath = !empty($data['user']['foto_profil']) 
        ? BASE_URL . 'uploads/profil/' . $data['user']['foto_profil'] 
        : BASE_URL . 'img/default-user.png';

    if (!empty($data['user']['foto_profil']) && !file_exists(APPROOT . '/../public/uploads/profil/' . $data['user']['foto_profil'])) {
        $fotoPath = 'https://ui-avatars.com/api/?name=' . urlencode($data['user']['nama_lengkap']) . '&background=random';
    } elseif (empty($data['user']['foto_profil'])) {
        $fotoPath = 'https://ui-avatars.com/api/?name=' . urlencode($data['user']['nama_lengkap']) . '&background=e0f2fe&color=152e4d';
    }
?>

<main class="app-content">
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>">
            <?php echo $_SESSION['flash_message']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="profile-grid-3-cols">
        
        <div class="card card-profile-center">
            <div class="profile-photo-wrapper">
                <img src="<?php echo $fotoPath; ?>?v=<?php echo time(); ?>" id="previewFoto" alt="Foto Profil">
                
                <button type="button" class="btn-upload-trigger" onclick="document.getElementById('inputFoto').click()">
                    <i class="ph ph-camera"></i>
                </button>
            </div>

            <h3 class="user-name-title"><?php echo htmlspecialchars($data['user']['nama_lengkap']); ?></h3>
            <span class="user-role-pill <?php echo strtolower($data['user']['role']); ?>">
                <?php echo ucfirst($data['user']['role']); ?>
            </span>

            <div class="contact-info-list">
                <div class="contact-item">
                    <i class="ph ph-envelope-simple"></i>
                    <span><?php echo htmlspecialchars($data['user']['email']); ?></span>
                </div>
                <div class="contact-item">
                    <i class="ph ph-phone"></i>
                    <span><?php echo htmlspecialchars($data['user']['telepon'] ?? '-'); ?></span>
                </div>
            </div>

            <div style="margin-top: 25px; width: 100%;">
                <a href="<?php echo BASE_URL; ?>profile/absensi" class="btn btn-outline-primary btn-block">
                    <i class="ph ph-calendar-check"></i> Riwayat Absensi
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header-minimal">
                <h4><i class="ph ph-user-gear"></i> Biodata Diri</h4>
            </div>
            
            <form action="<?php echo BASE_URL; ?>profile/processProfileInfo" method="POST" enctype="multipart/form-data">
                <input type="file" id="inputFoto" name="foto_profil" accept="image/*" style="display: none;" onchange="previewImage(this)">
                
                <div class="form-group-sm">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($data['user']['nama_lengkap']); ?>" required class="form-control">
                </div>

                <div class="form-group-sm">
                    <label>Nomor Telepon / WA</label>
                    <input type="text" name="telepon" value="<?php echo htmlspecialchars($data['user']['telepon'] ?? ''); ?>" class="form-control">
                </div>

                <div class="row-grid-2">
                    <div class="form-group-sm">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($data['user']['tempat_lahir'] ?? ''); ?>" class="form-control">
                    </div>
                    <div class="form-group-sm">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($data['user']['tanggal_lahir'] ?? ''); ?>" class="form-control">
                    </div>
                </div>

                <div class="form-group-sm">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2" class="form-control"><?php echo htmlspecialchars($data['user']['alamat'] ?? ''); ?></textarea>
                </div>
                
                <div class="row-grid-2">
                     <div class="form-group-sm">
                        <label>Kota</label>
                        <input type="text" name="kota" value="<?php echo htmlspecialchars($data['user']['kota'] ?? ''); ?>" class="form-control">
                    </div>
                     <div class="form-group-sm">
                        <label>Provinsi</label>
                        <input type="text" name="provinsi" value="<?php echo htmlspecialchars($data['user']['provinsi'] ?? ''); ?>" class="form-control">
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-sm-block">
                        <i class="ph ph-floppy-disk"></i> Simpan Biodata
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header-minimal">
                <h4><i class="ph ph-lock-key"></i> Keamanan Akun</h4>
            </div>

            <form action="<?php echo BASE_URL; ?>profile/processChangePassword" method="POST">
                <div class="form-group-sm">
                    <label>Password Lama</label>
                    <input type="password" name="old_password" required class="form-control" placeholder="••••••••">
                </div>
                
                <div class="separator-dashed"></div>

                <div class="form-group-sm">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" required class="form-control" placeholder="Minimal 6 karakter">
                </div>
                
                <div class="form-group-sm">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required class="form-control" placeholder="Ulangi password baru">
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn btn-warning btn-sm-block">
                        <i class="ph ph-key"></i> Update Password
                    </button>
                </div>
            </form>

            <div class="security-alert">
                <i class="ph ph-shield-check"></i>
                <p>Gunakan password yang kuat dan jangan bagikan ke siapapun.</p>
            </div>
        </div>

    </div>

</main>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewFoto').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>