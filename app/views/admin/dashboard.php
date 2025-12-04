<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="dashboard-grid-custom">
        
        <div class="card welcome-card">
            <div class="welcome-content">
                
                <div class="profile-section">
                    <div class="profile-placeholder">
                        <?php 
                            // Logika Foto Profil
                            $fotoPath = 'uploads/profil/' . ($data['user']['foto_profil'] ?? 'default.png');
                            if (file_exists(APPROOT . '/../public/' . $fotoPath) && !empty($data['user']['foto_profil'])) {
                                echo '<img src="' . BASE_URL . $fotoPath . '" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
                            } else {
                                echo '<i class="ph ph-user" style="font-size: 2.5rem;"></i>';
                            }
                        ?>
                    </div>
                    <div class="profile-text">
                        <span class="greeting">Selamat Datang,</span>
                        <h2 class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></h2>
                        <span class="user-role-badge <?php echo $_SESSION['role']; ?>">
                        <?php echo ucfirst($_SESSION['role']); ?> </span>
                        </span>
                    </div>
                </div>

                <div style="border-top: 1px solid #f1f5f9; margin-bottom: 20px;"></div>

                <div class="attendance-section">
                    <?php $today = $data['today_attendance']; ?>

                    <?php if (!$today): ?>
                        <div style="text-align: center; margin-bottom: 15px; color: #64748b;">
                            <i class="ph ph-clock-afternoon" style="font-size: 2rem; display: block; margin-bottom: 5px; color: #cbd5e1;"></i>
                            Halo, Anda belum melakukan presensi hari ini.
                        </div>
                        
                        <div style="display: flex; gap: 15px; width: 100%;">
                            <form action="<?php echo BASE_URL; ?>admin/processCheckIn" method="POST" style="flex: 1; margin: 0;">
                                <button type="submit" class="btn-attendance hadir">
                                    <i class="ph ph-check-circle" style="font-size: 1.2rem;"></i> SAYA HADIR
                                </button>
                            </form>
                            
                            <button type="button" 
                                    onclick="showIzinModal('<?php echo BASE_URL; ?>admin/processAbsenTidakHadir')" 
                                    class="btn-attendance izin" 
                                    style="flex: 1;">
                                <i class="ph ph-envelope-open" style="font-size: 1.2rem;"></i> IZIN / SAKIT
                            </button>
                        </div>

                    <?php elseif ($today['status'] == 'Hadir'): ?>
                        
                        <?php if ($today['waktu_pulang'] == null): ?>
                            <div class="attendance-status check-in">
                                <i class="ph ph-clock"></i> Masuk Pukul: <?php echo date('H:i', strtotime($today['waktu_masuk'])); ?> WIB
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <form action="<?php echo BASE_URL; ?>admin/processCheckOut" method="POST" style="width: 100%;">
                                    <button type="submit" class="btn-attendance checkout">
                                        <i class="ph ph-sign-out" style="font-size: 1.2rem;"></i> CHECK-OUT (PULANG)
                                    </button>
                                </form>
                                <p class="note" style="margin-top: 10px; color: #94a3b8;">
                                    Selamat bekerja! Jangan lupa checkout sebelum pulang.
                                </p>
                            </div>
                        
                        <?php else: ?>
                            <div class="attendance-status finished">
                                <i class="ph ph-confetti"></i> Shift Selesai
                            </div>
                            <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <p style="margin: 0; color: var(--primer-darkblue); font-weight: 600;">
                                    Total Jam Kerja Hari Ini:
                                </p>
                                <?php 
                                    $masuk = new DateTime($today['waktu_masuk']);
                                    $pulang = new DateTime($today['waktu_pulang']);
                                    $interval = $masuk->diff($pulang);
                                ?>
                                <h2 style="margin: 5px 0; color: var(--primer-lightblue);">
                                    <?php echo $interval->format('%h Jam %i Menit'); ?>
                                </h2>
                                <small style="color: #64748b;">
                                    (<?php echo date('H:i', strtotime($today['waktu_masuk'])); ?> - <?php echo date('H:i', strtotime($today['waktu_pulang'])); ?>)
                                </small>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="attendance-status absent">
                            Status Hari Ini: <?php echo strtoupper(htmlspecialchars($today['status'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title" style="border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <i class="ph ph-rocket-launch" style="color: var(--primer-yellow); font-size: 1.5rem;"></i> Akses Cepat
            </h3>
            
            <div class="shortcut-list">
                <a href="<?php echo BASE_URL; ?>admin/addBarang" class="btn-shortcut">
                    <span class="icon">📦</span> Tambah Barang Baru
                </a>
                <a href="<?php echo BASE_URL; ?>admin/addUser" class="btn-shortcut">
                    <span class="icon">👤</span> Tambah User Baru
                </a>
                <a href="<?php echo BASE_URL; ?>admin/perintahOpname" class="btn-shortcut">
                    <span class="icon">📝</span> Mulai Stock Opname
                </a>
                <a href="<?php echo BASE_URL; ?>admin/laporanTransaksi" class="btn-shortcut">
                    <span class="icon">📊</span> Cek Laporan Transaksi
                </a>
            </div>
        </div>

    </div>
</main>

<div id="templateModalIzin" style="display: none;">
    <form method="POST" enctype="multipart/form-data">
        <div style="text-align: left;">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600; color: #152e4d; margin-bottom: 5px; display: block;">
                    <i class="ph ph-tag"></i> Status Ketidakhadiran
                </label>
                <select name="status" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin / Cuti</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600; color: #152e4d; margin-bottom: 5px; display: block;">
                    <i class="ph ph-text-align-left"></i> Keterangan / Alasan
                </label>
                <textarea name="keterangan" class="form-control" rows="3" required 
                          placeholder="Jelaskan alasan ketidakhadiran Anda..."
                          style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit;"></textarea>
            </div>

           <div class="form-group" style="margin-bottom: 5px;">
                <label style="font-weight: 600; color: #152e4d; margin-bottom: 5px; display: block;">
                    <i class="ph ph-paperclip"></i> Upload Bukti (Opsional)
                </label>
                <div id="drop_zone_izin" style="border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 8px; background: #f8fafc; position: relative; cursor: pointer; transition: all 0.2s;">
                    
                    <input type="file" name="bukti_foto" id="input_bukti_izin" accept="image/*,application/pdf" 
                           style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                    
                    <div id="label_file_izin">
                        <span style="color: #64748b; font-size: 0.9rem; display:block;">Klik atau Seret File ke Sini</span>
                        <small style="color: #94a3b8;">(JPG, PNG, PDF)</small>
                    </div>
                </div>
                <small style="color: #ef4444; font-size: 0.8rem; margin-top: 5px; display: block;">*Max ukuran file 2MB</small>
            </div>

        </div>
    </form>
</div>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>