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
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Profil Saya</h1>
        <a href="<?php echo BASE_URL; ?>profile/absensi" class="btn btn-primary">
            📅 Lihat Riwayat Absensi
        </a>
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
                
                <div class="form-group" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                    <div style="
                        width: 120px; 
                        height: 120px; 
                        border-radius: 50%; 
                        overflow: hidden; 
                        border: 3px solid #ddd; 
                        background: #f0f0f0;
                        flex-shrink: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                    ">
                        <?php if (!empty($data['user']['foto_profil'])): ?>
                            <img src="<?php echo BASE_URL; ?>uploads/profil/<?php echo $data['user']['foto_profil']; ?>?v=<?php echo time(); ?>" 
                                 alt="Foto Profil" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 50px; color: #ccc;">👤</span>
                        <?php endif; ?>
                    </div>

                    <div style="flex: 1;">
                        <label for="foto_profil" style="font-weight: bold;">Ganti Foto Profil</label>
                        <input type="file" id="foto_profil" name="foto_profil" accept="image/png, image/jpeg, image/jpg" class="form-control" style="padding: 5px;">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Format: JPG, JPEG, PNG. Maksimal 2MB.<br>
                            (Biarkan kosong jika tidak ingin mengganti foto)
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap (Wajib)</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" 
                           value="<?php echo htmlspecialchars($data['user']['nama_lengkap']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email (Login)</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($data['user']['email']); ?>" 
                           readonly 
                           style="background-color: #e9ecef; cursor: not-allowed; color: #6c757d;">
                    <small>Hanya Admin yang dapat mengubah email.</small>
                </div>

                <div class="form-group">
                    <label for="role">Role / Jabatan</label>
                    <input type="text" id="role" name="role" 
                           value="<?php echo ucfirst(htmlspecialchars($data['user']['role'])); ?>" 
                           readonly 
                           style="background-color: #e9ecef; cursor: not-allowed; color: #6c757d; font-weight: bold;">
                    <small>Hubungi Admin jika ada perubahan jabatan.</small>
                </div>

                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" 
                           value="<?php echo htmlspecialchars($data['user']['tempat_lahir'] ?? ''); ?>"
                           placeholder="Contoh: Bandung">
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
                        <option value="Islam" <?php if(($data['user']['agama'] ?? '') == 'Islam') echo 'selected'; ?>>Islam</option>
                        <option value="Kristen" <?php if(($data['user']['agama'] ?? '') == 'Kristen') echo 'selected'; ?>>Kristen</option>
                        <option value="Katolik" <?php if(($data['user']['agama'] ?? '') == 'Katolik') echo 'selected'; ?>>Katolik</option>
                        <option value="Hindu" <?php if(($data['user']['agama'] ?? '') == 'Hindu') echo 'selected'; ?>>Hindu</option>
                        <option value="Buddha" <?php if(($data['user']['agama'] ?? '') == 'Buddha') echo 'selected'; ?>>Buddha</option>
                        <option value="Konghucu" <?php if(($data['user']['agama'] ?? '') == 'Konghucu') echo 'selected'; ?>>Konghucu</option>
                    </select>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Info Kontak & Alamat</legend>
                
                <div class="form-group">
                    <label for="telepon">Nomor Telepon / HP</label>
                    <input type="tel" id="telepon" name="telepon" 
                           value="<?php echo htmlspecialchars($data['user']['telepon'] ?? ''); ?>"
                           placeholder="Contoh: 08123456789"
                           inputmode="numeric" 
                           pattern="[0-9]*">
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" rows="3" 
                              placeholder="Nama Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan"><?php echo htmlspecialchars($data['user']['alamat'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="kota">Kota / Kabupaten</label>
                        <input type="text" id="kota" name="kota" 
                               value="<?php echo htmlspecialchars($data['user']['kota'] ?? ''); ?>"
                               placeholder="Contoh: Bandung">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="provinsi">Provinsi</label>
                        <input type="text" id="provinsi" name="provinsi" 
                               value="<?php echo htmlspecialchars($data['user']['provinsi'] ?? ''); ?>"
                               placeholder="Contoh: Jawa Barat">
                    </div>
                </div>

                <div class="form-group">
                    <label for="kode_pos">Kode Pos</label>
                    <input type="text" id="kode_pos" name="kode_pos" 
                           value="<?php echo htmlspecialchars($data['user']['kode_pos'] ?? ''); ?>"
                           placeholder="Contoh: 40123"
                           inputmode="numeric" 
                           pattern="[0-9]*"
                           maxlength="5">
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
                    <label for="old_password">
                        Password Lama 
                        <span style="cursor: pointer; color: #007bff; margin-left: 5px; font-size: 1.1em;" 
                              title="Jika lupa password lama silahkan hubungi admin"
                              onclick="Swal.fire({
                                  icon: 'info',
                                  title: 'Lupa Password?',
                                  text: 'Jika Anda lupa password lama, silakan hubungi Admin untuk mereset password Anda.',
                                  confirmButtonText: 'Mengerti'
                              })">
                            ⓘ
                        </span>
                    </label>
                    <input type="password" id="old_password" name="old_password" required placeholder="Masukkan password saat ini">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="Minimal 6 karakter">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru">
                </div>

            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ubah Password Saya</button>
            </div>
        </form>
    </div>
</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>