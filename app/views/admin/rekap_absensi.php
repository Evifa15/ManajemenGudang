<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // --- LOGIKA TANGGAL PHP (Untuk Mode Harian) ---
    // Hanya dipakai jika mode = harian
    $currentDate = $data['filters']['specific_date'] ?? date('Y-m-d'); 
    $dateObj = new DateTime($currentDate);
    $todayObj = new DateTime('today'); 
    $diff = $todayObj->diff($dateObj);
    $diffDays = (int)$diff->format('%R%a'); 

    // Array Hari & Bulan Indo
    $hariIndo = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $bulanIndo = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
        7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
    ];

    // Label Waktu (Hari Ini / Besok / Kemarin)
    $labelWaktu = "";
    $labelClass = "label-other"; 

    if ($diffDays === 0) {
        $labelWaktu = "Hari Ini";
        $labelClass = "label-today"; 
    } elseif ($diffDays === -1) {
        $labelWaktu = "Kemarin";
        $labelClass = "label-yesterday"; 
    } elseif ($diffDays === 1) {
        $labelWaktu = "Besok";
        $labelClass = "label-tomorrow"; 
    } else {
        $labelWaktu = $hariIndo[$dateObj->format('l')];
    }
    
    // Format Teks Tanggal Tampilan
    $hariText = $hariIndo[$dateObj->format('l')];
    $tgl = $dateObj->format('d');
    $bln = $bulanIndo[(int)$dateObj->format('m')];
    $thn = $dateObj->format('Y');
    $displayDate = "$hariText, $tgl $bln $thn";
?>

<main class="app-content" style="padding-top: 0;" data-base-url="<?php echo BASE_URL; ?>">
    
    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="tab-nav" style="margin-top: 20px !important; display: flex; justify-content: flex-start; align-items: flex-end; gap: 5px; border-bottom: 4px solid #152e4d;">
        <a href="?mode=harian" class="tab-nav-link <?php echo ($data['mode'] == 'harian') ? 'active' : ''; ?>">
            Monitoring Harian
        </a>
        <a href="?mode=laporan" class="tab-nav-link <?php echo ($data['mode'] == 'laporan') ? 'active' : ''; ?>">
            Riwayat Laporan 
        </a>
    </div>

    <?php if ($data['mode'] == 'harian'): ?>
        
        <div style="background: #ffffff; padding: 15px 20px; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            
            <div style="display: flex; align-items: center; gap: 10px;">
                
                <div style="position: relative; width: 40px; height: 40px;">
                    <div class="btn-icon-simple" 
                        style="width: 100%; height: 100%; background-color: #152e4d; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="ph ph-calendar-plus" style="font-size: 1.2rem;"></i>
                    </div>
                    <input type="date" id="datePickerNative" value="<?php echo $currentDate; ?>" 
                        onchange="navGoToDate(this.value)"
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;">
                </div>

                <button type="button" class="btn-arrow-simple" onclick="navChangeDate(-1)" style="width: 32px; height: 32px;">
                    <i class="ph ph-caret-left"></i>
                </button>
                
                <span class="date-text-simple" onclick="navShowPicker()" 
                      style="cursor: pointer; font-size: 0.95rem; font-weight: 700; color: #152e4d; min-width: 160px; text-align: center;">
                    <?php echo $displayDate; ?>
                </span>

                <button type="button" class="btn-arrow-simple" onclick="navChangeDate(1)" style="width: 32px; height: 32px;">
                    <i class="ph ph-caret-right"></i>
                </button>

                <span class="time-label-simple <?php echo $labelClass; ?>" 
                      onclick="navGoToDate('<?php echo date('Y-m-d'); ?>')" 
                      style="cursor: pointer; margin-left: 5px; font-size: 0.7rem; padding: 4px 10px;" 
                      title="Klik untuk kembali ke Hari Ini">
                    <?php echo $labelWaktu; ?>
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex: 1; justify-content: flex-end; min-width: 300px;">
                
                <select id="filterRole" class="filter-select-clean" 
                        style="height: 40px; width: 140px; font-size: 0.85rem;">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="peminjam">Peminjam</option>
                </select>

                <div class="search-box-wrapper" style="width: 250px; margin: 0;">
                    <i class="ph ph-magnifying-glass search-icon" 
                       style="top: 50%; transform: translateY(-50%); left: 12px; font-size: 1rem;"></i>
                    <input type="text" id="searchAbsensi" 
                           class="table-search-input"
                           style="width: 100%; height: 40px; padding-left: 38px; font-size: 0.85rem;" 
                           placeholder="Cari karyawan..." 
                           data-base-url="<?php echo BASE_URL; ?>">
                    <input type="hidden" id="filterDate" value="<?php echo $currentDate; ?>">
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-wrapper-flat">
                <table id="tableAbsensi" style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: #152e4d; color: #ffffff;">
                        <tr>
                            <th style="padding: 15px; font-size: 0.85rem; border-top-left-radius: 8px;">Tanggal</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Nama Karyawan</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Role</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Masuk</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Pulang</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Total Jam</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Status</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Bukti</th> 
                            <th class="no-print" style="padding: 15px; text-align: center; font-size: 0.85rem; border-top-right-radius: 8px;">Aksi</th> 
                        </tr>
                    </thead>
                    
                    <tbody id="absensiTableBody">
                        <?php if(empty($data['absensi'])): ?>
                            <tr><td colspan="9" style="text-align:center; padding: 30px; color: #999;">Tidak ada data absensi pada tanggal ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['absensi'] as $absen) : 
                                // ... (Logika warna status sama seperti file asli) ...
                                $totalJam = '-';
                                $waktuMasuk = '-'; $waktuPulang = '-';
                                $statusStyle = ''; 
                                $statusText = $absen['status']; 

                                if ($absen['status'] == 'Hadir') {
                                // Default Hijau
                                $statusStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                                $statusText = 'Hadir';

                                if ($absen['waktu_pulang']) {
                                    // Jika sudah pulang (Normal)
                                    $waktuMasuk = date('H:i', strtotime($absen['waktu_masuk']));
                                    $waktuPulang = date('H:i', strtotime($absen['waktu_pulang']));
                                    $totalJam = (new DateTime($absen['waktu_masuk']))->diff(new DateTime($absen['waktu_pulang']))->format('%h jam %i mnt');
                                } else {
                                    // Jika belum pulang
                                    $waktuMasuk = date('H:i', strtotime($absen['waktu_masuk']));
                                    
                                    // [LOGIKA BARU] Cek apakah tanggal absen == hari ini
                                    if ($absen['tanggal'] == date('Y-m-d')) {
                                        $statusText = 'Masih Bekerja';
                                        $statusStyle = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;'; // Biru
                                    }
                                    // Jika tanggal lampau, biarkan $statusText 'Hadir' (Hijau)
                                }

                                } elseif ($absen['status'] == 'Sakit') {
                                    $statusStyle = 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;'; 
                                } elseif ($absen['status'] == 'Izin') {
                                    $statusStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;'; 
                                    $statusText = 'Izin / Cuti'; 
                                } else { 
                                    $statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;'; 
                                    $statusText = 'Tanpa Keterangan'; 
                                }

                                $roleRaw = strtolower($absen['role']);
                                $roleBadgeStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                                if($roleRaw == 'admin') $roleBadgeStyle = 'background:#f3e8ff; color:#7c3aed; border:1px solid #d8b4fe;';
                                if($roleRaw == 'staff') $roleBadgeStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                                if($roleRaw == 'pemilik') $roleBadgeStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;';
                                if($roleRaw == 'peminjam') $roleBadgeStyle = 'background:#f3f4f6; color:#4b5563; border:1px solid #d1d5db;';
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 15px; color: #334155;"><?php echo date('d/m/Y', strtotime($absen['tanggal'])); ?></td>
                                <td style="padding: 15px; color: #152e4d;"><strong><?php echo htmlspecialchars($absen['nama_lengkap']); ?></strong></td>
                                <td style="padding: 15px;">
                                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; <?php echo $roleBadgeStyle; ?>">
                                        <?php echo htmlspecialchars($absen['role']); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #444;"><?php echo $waktuMasuk; ?></td>
                                <td style="padding: 15px; color: #444;"><?php echo $waktuPulang; ?></td>
                                <td style="padding: 15px; color: #444;"><?php echo $totalJam; ?></td>
                                <td style="padding: 15px;">
                                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; <?php echo $statusStyle; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <?php if(!empty($absen['bukti_foto'])): ?>
                                        <a href="<?php echo BASE_URL . 'uploads/bukti_absen/' . $absen['bukti_foto']; ?>" 
                                        target="_blank" 
                                        style="display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #2563eb; font-weight: 600; font-size: 0.85rem; background: #eff6ff; padding: 4px 8px; border-radius: 6px;">
                                        <i class="ph ph-file-text"></i> Lihat File
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="no-print" style="text-align: center;">
                                    <?php 
                                        // Format Jam
                                        $jamMasukRaw = !empty($absen['waktu_masuk']) ? substr($absen['waktu_masuk'], 0, 5) : '';
                                        $jamPulangRaw = !empty($absen['waktu_pulang']) ? substr($absen['waktu_pulang'], 0, 5) : '';
                                        
                                        // FIX 1: Bersihkan Nama (Kutip & Enter)
                                        $namaRaw = isset($absen['nama_lengkap']) ? $absen['nama_lengkap'] : '';
                                        $namaRaw = preg_replace("/\r|\n/", " ", $namaRaw); // Hapus Enter
                                        $namaRaw = htmlspecialchars($namaRaw, ENT_QUOTES); // Aman dari kutip

                                        // FIX 2: Bersihkan Keterangan (Kutip & Enter)
                                        $ketRaw = isset($absen['keterangan']) ? $absen['keterangan'] : '';
                                        $ketRaw = preg_replace("/\r|\n/", " ", $ketRaw); // Hapus Enter
                                        $ketRaw = htmlspecialchars($ketRaw, ENT_QUOTES); // Aman dari kutip
                                    ?>
                                    <button type="button" class="btn-icon edit btn-edit-manual" 
                                            data-id="<?php echo $absen['absen_id']; ?>"
                                            data-nama="<?php echo htmlspecialchars($absen['nama_lengkap'], ENT_QUOTES); ?>"
                                            data-masuk="<?php echo $jamMasukRaw; ?>"
                                            data-pulang="<?php echo $jamPulangRaw; ?>"
                                            data-status="<?php echo $absen['status']; ?>"
                                            data-ket="<?php echo htmlspecialchars($absen['keterangan'] ?? '', ENT_QUOTES); ?>">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="pagination-container custom-pagination" 
             style="padding: 15px 0; margin-top: 10px;"
             data-total-pages="<?php echo $data['totalPages']; ?>" 
             data-current-page="<?php echo $data['currentPage']; ?>">
        </div>

    <?php else: ?>
        
        <div class="search-card compact-filter" style="margin-top: 20px; padding: 25px;">
            <form id="formLaporan" action="<?php echo BASE_URL; ?>admin/rekapAbsensi" method="GET">
                <input type="hidden" name="mode" value="laporan">

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                    
                    <div style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Pilih Periode:</span> 
                        <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriod('today')">Hari Ini</button>
                        <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriod('this_week')">Minggu Ini</button>
                        <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriod('this_month')">Bulan Ini</button>
                        <button type="button" class="btn btn-sm btn-secondary btn-period" onclick="setPeriod('last_month')">Bulan Lalu</button>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Dari:</span>
                        
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="date" id="startDate" name="start_date" class="filter-select-clean" 
                                style="height: 35px; padding: 0 10px; width: 135px; cursor: pointer;"
                                value="<?php echo $data['filters']['start_date']; ?>"
                                onclick="this.showPicker()" 
                                onchange="if(typeof window.loadAbsensiGlobal === 'function') window.loadAbsensiGlobal(1);">
                                <i class="ph ph-calendar-blank" 
                                   style="position: absolute; right: 10px; color: #64748b; font-size: 1.1rem; pointer-events: none;"></i>
                        </div>
                    </div>

                    <span style="color: #cbd5e1;">—</span>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Sampai:</span>
                        
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="date" id="endDate" name="end_date" class="filter-select-clean" 
                                style="height: 35px; padding: 0 10px; width: 135px; cursor: pointer;"
                                value="<?php echo $data['filters']['end_date']; ?>"
                                onclick="this.showPicker()"
                                onchange="if(typeof window.loadAbsensiGlobal === 'function') window.loadAbsensiGlobal(1);">
                                <i class="ph ph-calendar-blank" 
                                   style="position: absolute; right: 10px; color: #64748b; font-size: 1.1rem; pointer-events: none;"></i>
                        </div>
                    </div>

                    <a href="?mode=laporan" class="btn btn-secondary" 
                    title="Reset Filter" 
                    style="height: 35px; width: 35px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 6px; background-color: #152e4d; border-color: #152e4d; color: #ffffff;">
                        <i class="ph ph-arrow-counter-clockwise" style="font-weight: bold;"></i>
                    </a>

                </div>
                    

                </div>

                <div class="filter-row">
                    <div class="filter-item" style="flex: 2;">
                        <input type="text" id="searchAbsensi" name="search" class="filter-select-clean" 
                               placeholder="Cari Nama Karyawan..." 
                               value="<?php echo htmlspecialchars($data['filters']['search']); ?>"
                               data-base-url="<?php echo BASE_URL; ?>">
                    </div>

                    <div class="filter-item" style="flex: 1;">
                        <select name="role" id="filterRoleLaporan" class="filter-select-clean" 
                                onchange="if(typeof window.loadAbsensiGlobal === 'function') window.loadAbsensiGlobal(1);">
                            <option value="">Semua Role</option>
                            <option value="staff" <?php if($data['filters']['role'] == 'staff') echo 'selected'; ?>>Staff</option>
                            <option value="admin" <?php if($data['filters']['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                            <option value="peminjam" <?php if($data['filters']['role'] == 'peminjam') echo 'selected'; ?>>Peminjam</option>
                        </select>
                    </div>
                    
                    <div class="filter-item action" style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportLaporan()" class="btn btn-primary btn-sm" 
                                style="height: 42px; background-color: #152e4d; border-color: #152e4d; font-weight: 600; padding: 0 25px;" 
                                title="Download Excel/CSV">
                            <i class="ph ph-microsoft-excel-logo" style="font-size: 1.2rem; margin-right: 5px;"></i> Export
                        </button>
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
                            <th style="padding: 15px; font-size: 0.85rem;">Nama Karyawan</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Role</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Masuk</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Pulang</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Status</th>
                            <th style="padding: 15px; font-size: 0.85rem;">Ket</th>
                        </tr>
                    </thead>
                    <tbody id="absensiTableBody">
                        <?php if(empty($data['absensi'])): ?>
                            <tr><td colspan="7" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan untuk periode ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['absensi'] as $absen) : 
                            // 1. Logika Status (Sudah Ada)
                            $statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                            $statusText = $absen['status'];

                            if ($absen['status'] == 'Hadir') {
                                $statusStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                                if (empty($absen['waktu_pulang']) && $absen['tanggal'] == date('Y-m-d')) {
                                    $statusText = 'Masih Bekerja';
                                    $statusStyle = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;';
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
                            
                            // 2. [BARU] TAMBAHKAN LOGIKA WARNA ROLE DISINI
                            $roleRaw = strtolower($absen['role']);
                            $roleBadgeStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;'; // Default Abu

                            if($roleRaw == 'admin') {
                                $roleBadgeStyle = 'background:#f3e8ff; color:#7c3aed; border:1px solid #d8b4fe;'; // Ungu
                            } elseif($roleRaw == 'staff') {
                                $roleBadgeStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;'; // Hijau
                            } elseif($roleRaw == 'pemilik') {
                                $roleBadgeStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;'; // Kuning
                            } elseif($roleRaw == 'peminjam') {
                                $roleBadgeStyle = 'background:#f3f4f6; color:#4b5563; border:1px solid #d1d5db;'; // Abu Gelap
                            }
                            
                            $jamMasuk = $absen['waktu_masuk'] ? date('H:i', strtotime($absen['waktu_masuk'])) : '-';
                            $jamPulang = $absen['waktu_pulang'] ? date('H:i', strtotime($absen['waktu_pulang'])) : '-';
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px;"><?php echo date('d/m/Y', strtotime($absen['tanggal'])); ?></td>
                            <td style="padding: 12px; font-weight: 600; color: #152e4d;"><?php echo htmlspecialchars($absen['nama_lengkap']); ?></td>
                            
                            <td style="padding: 12px;">
                                <span style="font-size: 0.75rem; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; font-weight: 700; <?php echo $roleBadgeStyle; ?>">
                                    <?php echo $absen['role']; ?>
                                </span>
                            </td>
                                                            <td style="padding: 12px; color: #444;"><?php echo $jamMasuk; ?></td>
                                <td style="padding: 12px; color: #444;"><?php echo $jamPulang; ?></td>
                                <td style="padding: 12px;">
                                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; <?php echo $statusStyle; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php 
                                        $ketRaw = $absen['keterangan'] ?? '';
                                        // Tampilkan tombol jika keterangan tidak kosong dan bukan '-'
                                        if (!empty($ketRaw) && $ketRaw !== '-') {
                                            
                                            // Bersihkan karakter enter dan kutip agar tidak merusak Javascript
                                            $ketClean = preg_replace("/\r|\n/", " ", $ketRaw);
                                            $ketClean = htmlspecialchars($ketClean, ENT_QUOTES);
                                            
                                            // Cetak tombol
                                            echo '<button type="button" class="btn btn-sm" 
                                                    onclick="showDetailKeterangan(\'' . $ketClean . '\')"
                                                    style="background-color: #e0f2fe; color: #0ea5e9; border: 1px solid #bae6fd; padding: 2px 10px; font-size: 0.75rem; border-radius: 20px; font-weight: 600; cursor: pointer;">
                                                    <i class="ph ph-eye"></i> Lihat
                                                </button>';
                                        } else {
                                            // Jika kosong, tampilkan strip
                                            echo '<span style="color: #cbd5e1;">-</span>';
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-container custom-pagination" 
             style="padding: 15px 0; margin-top: 10px;"
             data-total-pages="<?php echo $data['totalPages']; ?>" 
             data-current-page="<?php echo $data['currentPage']; ?>">
        </div>

    <?php endif; ?>

</main>

<div id="templateEditAbsenAdmin" style="display: none;">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="absen_id" id="edit_absen_id">
        
        <div style="text-align: left; padding: 5px;">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 0.75rem; font-weight: 800; color: #152e4d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; display: block;">
                    Nama Karyawan
                </label>
                <div style="background: #f1f5f9; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-user"></i>
                    <input type="text" id="edit_nama" readonly 
                           style="background: transparent; border: none; width: 100%; outline: none; color: inherit; font-weight: inherit;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 0.75rem; font-weight: 800; color: #152e4d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; display: block;">
                    Status Kehadiran
                </label>
                <div style="position: relative;">
                    <select name="status" id="edit_status" 
                            style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #cbd5e1; background-color: #fff; color: #152e4d; font-weight: 500; outline: none; appearance: none;">
                        <option value="Hadir">✅ Hadir / Masuk Kerja</option>
                        <option value="Sakit">💊 Sakit</option>
                        <option value="Izin">📩 Izin / Cuti</option>
                        <option value="Alpa">❌ Alpa (Tanpa Keterangan)</option>
                    </select>
                    <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #152e4d; pointer-events: none;"></i>
                </div>
            </div>

            <div id="row_jam" style="display: flex; gap: 15px; margin-bottom: 15px; background: #ecfdf5; padding: 15px; border-radius: 8px; border: 1px dashed #10b981;">
                <div style="flex:1;">
                    <label style="font-size: 0.7rem; font-weight: 700; color: #065f46; margin-bottom: 5px; display: block;">JAM MASUK</label>
                    <input type="time" name="waktu_masuk" id="edit_masuk" class="form-control" 
                           style="border: 1px solid #a7f3d0; color: #065f46; font-weight: bold;">
                </div>
                <div style="flex:1;">
                    <label style="font-size: 0.7rem; font-weight: 700; color: #065f46; margin-bottom: 5px; display: block;">JAM PULANG</label>
                    <input type="time" name="waktu_pulang" id="edit_pulang" class="form-control"
                           style="border: 1px solid #a7f3d0; color: #065f46; font-weight: bold;">
                </div>
            </div>

            <div id="row_keterangan" style="display: none;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #152e4d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; display: block;">
                        Keterangan / Alasan
                    </label>
                    <textarea name="keterangan" id="edit_keterangan" rows="2" 
                              placeholder="Tulis alasan ketidakhadiran..."
                              style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; resize: none; font-family: inherit;"></textarea>
                </div>

                <div class="form-group">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #152e4d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; display: block;">
                        Upload Bukti Baru (Opsional)
                    </label>
                    
                    <div id="drop_zone_area" 
                         style="position: relative; border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 8px; padding: 20px; text-align: center; transition: all 0.2s; cursor: pointer;">
                        
                        <input type="file" name="bukti_foto" id="input_bukti_file" accept=".jpg, .jpeg, .png, .pdf"
                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                        
                        <i class="ph ph-upload-simple" style="font-size: 1.5rem; color: #152e4d; margin-bottom: 5px;"></i>
                        
                        <p id="label_file_name" style="margin: 0; font-size: 0.85rem; color: #64748b;">
                            Klik atau Seret File ke Sini (JPG/PNG/PDF)
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>