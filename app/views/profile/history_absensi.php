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
    <div class="search-card compact-filter" style="padding: 20px; margin-bottom: 20px;">
        <form id="formHistory" data-base-url="<?php echo BASE_URL; ?>" onsubmit="return false;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Pilih Periode:</span>
                    <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriodProfile('today')">Hari Ini</button>
                    <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriodProfile('this_week')">Minggu Ini</button>
                    <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriodProfile('this_month')">Bulan Ini</button>
                    <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriodProfile('last_month')">Bulan Lalu</button>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Dari:</span>
                        
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="date" id="startDateProfile" class="filter-select-clean" 
                                style="height: 35px; padding: 0 10px; width: 135px; cursor: pointer;"
                                value="<?php echo $data['filters']['start_date']; ?>"
                                onclick="this.showPicker()"
                                onchange="loadMyHistory(1)">
                            
                            <i class="ph ph-calendar-blank" 
                               style="position: absolute; right: 10px; color: #64748b; font-size: 1.1rem; pointer-events: none;"></i>
                        </div>
                    </div>

                    <span style="color: #cbd5e1;">—</span>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Sampai:</span>
                        
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="date" id="endDateProfile" class="filter-select-clean" 
                                style="height: 35px; padding: 0 10px; width: 135px; cursor: pointer;"
                                value="<?php echo $data['filters']['end_date']; ?>"
                                onclick="this.showPicker()"
                                onchange="loadMyHistory(1)">
                            
                            <i class="ph ph-calendar-blank" 
                               style="position: absolute; right: 10px; color: #64748b; font-size: 1.1rem; pointer-events: none;"></i>
                        </div>
                    </div>

                    <a href="<?php echo BASE_URL; ?>profile/absensi" class="btn" title="Reset Filter"
                    style="height: 35px; width: 35px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 6px; background-color: #152e4d; border-color: #152e4d; color: #ffffff;">
                        <i class="ph ph-arrow-counter-clockwise" style="font-weight: bold;"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #152e4d; color: #ffffff;">
                    <tr>
                        <th style="padding: 15px; font-size: 0.85rem;">Tanggal</th>
                        <th style="padding: 15px; font-size: 0.85rem;">Jam Masuk</th>
                        <th style="padding: 15px; font-size: 0.85rem;">Jam Pulang</th>
                        <th style="padding: 15px; font-size: 0.85rem;">Total Jam</th>
                        <th style="padding: 15px; font-size: 0.85rem;">Status</th>
                        <th style="padding: 15px; font-size: 0.85rem;">Keterangan / Bukti</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    <?php if(empty($data['absensi'])): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 30px; color: #999;">Belum ada data absensi bulan ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['absensi'] as $absen) : 
                            // --- Logika Warna Status (Sama dengan Admin) ---
                            $statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                            $statusText = $absen['status'];

                            if ($absen['status'] == 'Hadir') {
                                $statusStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                                
                                if ($absen['waktu_masuk'] && empty($absen['waktu_pulang'])) {
                                    // Cek jika hari ini
                                    if ($absen['tanggal'] == date('Y-m-d')) {
                                        $statusText = 'Masih Bekerja';
                                        $statusStyle = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;';
                                    }
                                }
                            } elseif ($absen['status'] == 'Sakit') {
                                $statusStyle = 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;';
                            } elseif ($absen['status'] == 'Izin') {
                                $statusStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;';
                                $statusText = 'Izin / Cuti';
                            } elseif ($absen['status'] == 'Alpa') {
                                $statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                                $statusText = 'Tanpa Keterangan';
                            }
                            
                            $jamMasuk = $absen['waktu_masuk'] ? date('H:i', strtotime($absen['waktu_masuk'])) : '-';
                            $jamPulang = $absen['waktu_pulang'] ? date('H:i', strtotime($absen['waktu_pulang'])) : '-';
                            
                            // Hitung Total Jam (PHP Side)
                            $totalJam = '-';
                            if($absen['waktu_masuk'] && $absen['waktu_pulang']) {
                                $t1 = new DateTime($absen['waktu_masuk']);
                                $t2 = new DateTime($absen['waktu_pulang']);
                                $totalJam = $t1->diff($t2)->format('%h jam %i mnt');
                            }
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px; color: #334155;"><?php echo date('d/m/Y', strtotime($absen['tanggal'])); ?></td>
                            <td style="padding: 15px; color: #444;"><?php echo $jamMasuk; ?></td>
                            <td style="padding: 15px; color: #444;"><?php echo $jamPulang; ?></td>
                            <td style="padding: 15px; color: #444;"><?php echo $totalJam; ?></td>
                            <td style="padding: 15px;">
                                <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; <?php echo $statusStyle; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <?php if($absen['bukti_foto']): ?>
                                    <a href="<?php echo BASE_URL . 'uploads/bukti_absen/' . $absen['bukti_foto']; ?>" target="_blank" 
                                       style="display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #2563eb; font-weight: 600; font-size: 0.85rem; background: #eff6ff; padding: 4px 8px; border-radius: 6px;">
                                       <i class="ph ph-file-text"></i> Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: #64748b;">
                                        <?php echo htmlspecialchars($absen['keterangan'] ?? '-'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-container-history custom-pagination" 
     id="paginationContainerHistory"
     data-total-pages="<?php echo $data['totalPages']; ?>" 
     data-current-page="<?php echo $data['currentPage']; ?>">
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>