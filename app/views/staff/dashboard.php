<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>
<main class="app-content">   
    
    <div class="dashboard-grid-custom">
        
        <div class="card welcome-card">
            <div class="welcome-content">
                
                <div class="profile-section">
                    <div class="profile-placeholder">
                        <span>FOTO</span>
                    </div>
                    
                    <div class="profile-text">
                        <span class="greeting">Halo, Semangat Bekerja!</span>
                        <h2 class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></h2>
                        <span class="user-role-badge">Staff Gudang</span>
                    </div>
                </div>

                <div class="attendance-section">
                    <?php $today = $data['today_attendance']; ?>

                    <?php if (!$today): ?>
                        <div style="text-align: center; margin-bottom: 10px; font-size: 0.9rem; color: #64748b;">
                            Anda belum presensi hari ini.
                        </div>
                        
                        <div style="display: flex; gap: 10px; width: 100%;">
                            <form action="<?php echo BASE_URL; ?>staff/processCheckIn" method="POST" style="flex: 1; margin: 0;">
                                <button type="submit" class="btn-checkout" style="background-color: #10b981;">
                                    ✅ HADIR
                                </button>
                            </form>
                            
                            <button type="button" 
                                    onclick="showIzinModal('<?php echo BASE_URL; ?>staff/processAbsenTidakHadir')" 
                                    class="btn-checkout" 
                                    style="flex: 1; background-color: #f59e0b;">
                                📩 IZIN
                            </button>
                        </div>

                    <?php elseif ($today['status'] == 'Hadir'): ?>
                        
                        <?php if ($today['waktu_pulang'] == null): ?>
                            <div class="attendance-status check-in">
                                <span class="icon">✅</span> Masuk: <?php echo date('H:i', strtotime($today['waktu_masuk'])); ?>
                            </div>
                            
                            <form action="<?php echo BASE_URL; ?>staff/processCheckOut" method="POST" style="width: 100%;">
                                <button type="submit" class="btn-checkout">
                                    <span class="icon">⭕</span> CHECK-OUT (PULANG)
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="attendance-status" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
                                <span>🎉</span> Selesai Bekerja
                            </div>
                            <p class="note">
                                Total: <?php echo date('H:i', strtotime($today['waktu_masuk'])); ?> - <?php echo date('H:i', strtotime($today['waktu_pulang'])); ?>
                            </p>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="attendance-status" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                            Status: <?php echo htmlspecialchars($today['status']); ?>
                        </div>
                        <p class="note">"<?php echo htmlspecialchars($today['keterangan']); ?>"</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="right-column">
            
            <div class="card stats-card" style="margin-bottom: 20px;">
                <h3 style="font-size: 1rem; margin-bottom: 15px; color: #64748b;">⚡ Akses Cepat</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo BASE_URL; ?>staff/barangMasuk" class="btn btn-primary" style="text-align: left; justify-content: start;">
                        📥 Input Barang Masuk
                    </a>
                    <a href="<?php echo BASE_URL; ?>staff/barangKeluar" class="btn btn-primary" style="background-color: #6c757d; text-align: left; justify-content: start;">
                        📤 Input Barang Keluar
                    </a>
                </div>
            </div>

            <div class="card stats-card">
                <h3 style="font-size: 1rem; margin-bottom: 15px; color: #64748b;">📋 Tugas Hari Ini</h3>
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: #334155;">
                    <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        🔹 Cek Permintaan Peminjaman
                    </li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        🔹 Cek Periode Stock Opname
                    </li>
                </ul>
            </div>

        </div>

    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>