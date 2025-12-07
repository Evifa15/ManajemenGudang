<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content" data-base-url="<?php echo BASE_URL; ?>">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>">
            <?php echo $_SESSION['flash_message']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="history-top-bar">
        
        <div class="history-search-wrapper">
            <input type="text" id="liveSearchMasuk" 
                   class="history-search-input" 
                   placeholder="Cari Barang, Supplier, Lot, atau Staff..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   data-base-url="<?php echo BASE_URL; ?>">
        </div>

        <div class="history-header-buttons">
            
            <button type="button" id="btnToggleFilterMasuk" class="btn btn-secondary" 
                    style="height: 42px; display: flex; align-items: center; border: 1px solid #cbd5e1; color: #64748b; background: #fff; padding: 0 15px; font-weight: 600; white-space: nowrap;"
                    title="Buka/Tutup Filter Tanggal">
                <i class="ph ph-funnel" style="font-size: 1.2rem; margin-right: 5px;"></i> Filter
            </button>

            <div class="dropdown-export-wrapper">
                <button type="button" id="btnToggleExportMasuk" class="btn btn-brand-dark" 
                        style="height: 42px; display: flex; align-items: center; padding: 0 18px; white-space: nowrap;">
                    <i class="ph ph-export" style="font-size: 1.2rem; margin-right: 8px;"></i> Export
                    <i class="ph ph-caret-down" style="margin-left: 5px; font-size: 1rem;"></i>
                </button>

                <div id="exportMenuMasuk" class="dropdown-menu-custom">
                    <a href="#" class="btn-export-masuk-action" data-type="excel">
                        <i class="ph ph-microsoft-excel-logo" style="color: #10b981; font-size: 1.2rem;"></i> 
                        Excel (.xls)
                    </a>
                    <a href="#" class="btn-export-masuk-action" data-type="csv">
                        <i class="ph ph-file-csv" style="color: #0ea5e9; font-size: 1.2rem;"></i> 
                        CSV (.csv)
                    </a>
                    <a href="#" class="btn-export-masuk-action" data-type="pdf">
                        <i class="ph ph-file-pdf" style="color: #ef4444; font-size: 1.2rem;"></i> 
                        PDF Document
                    </a>
                </div>
            </div>
            
        </div>
    </div>

    <div id="filterPanelMasuk" class="search-card compact-filter" style="display: none; margin-bottom: 20px;">
    
        <div class="filter-panel-row">
            <div class="filter-panel-item">
                <label class="filter-label">Dari Tanggal</label>
                <input type="date" id="startDateMasuk" class="filter-select-clean" 
                       value="<?php echo htmlspecialchars($data['start_date']); ?>">
            </div>

            <div class="filter-panel-item">
                <label class="filter-label">Sampai Tanggal</label>
                <input type="date" id="endDateMasuk" class="filter-select-clean" 
                       value="<?php echo htmlspecialchars($data['end_date']); ?>">
            </div>

            <div style="flex: 0 0 auto;">
                <button type="button" id="btnResetMasuk" class="btn-reset-filter" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise" style="font-size: 1.2rem; font-weight: bold;"></i>
                </button>
            </div>
        </div>
        
        <div style="padding-bottom: 10px;">
            <small style="color: #94a3b8;">*Filter tanggal akan otomatis diterapkan saat Anda memilih tanggal.</small>
        </div>
    </div>

    <div id="filterPanel" class="search-card compact-filter" style="display: none; margin-bottom: 20px;">
    
    <div class="filter-panel-row">
        <div class="filter-panel-item">
            <label class="filter-label">Dari Tanggal</label>
            <input type="date" id="startDateMasuk" class="filter-select-clean" 
                   value="<?php echo htmlspecialchars($data['start_date']); ?>">
        </div>

        <div class="filter-panel-item">
            <label class="filter-label">Sampai Tanggal</label>
            <input type="date" id="endDateMasuk" class="filter-select-clean" 
                   value="<?php echo htmlspecialchars($data['end_date']); ?>">
        </div>

        <div style="flex: 0 0 auto;">
            <button type="button" id="btnResetMasuk" class="btn-reset-filter" title="Reset Filter">
                <i class="ph ph-arrow-counter-clockwise" style="font-size: 1.2rem; font-weight: bold;"></i>
            </button>
        </div>
    </div>
    
    <div style="padding-bottom: 10px;">
        <small style="color: #94a3b8;">*Filter tanggal akan otomatis diterapkan saat Anda memilih tanggal.</small>
    </div>
</div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Input</th>
                        <th>Nama Barang</th>
                        <th>Status Exp</th>
                        <th>Jml</th>
                        <th>Satuan</th>
                        <th>Supplier</th>
                        <th>Diinput Oleh</th>
                        <th>Batch</th>
                        <th style="text-align: center; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBodyMasuk">
                    <?php if (empty($data['history'])): ?>
                        <tr><td colspan="9" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['history'] as $his) : ?>
                        <tr>
                            <td style="color: #334155; font-size: 0.9rem;">
                                <?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?>
                            </td>
                            
                            <td>
                                <strong style="color: #152e4d;"><?php echo htmlspecialchars($his['nama_barang']); ?></strong>
                            </td>
                            
                            <td>
                                <?php 
                                if (!empty($his['exp_date'])) {
                                    $tglExp = new DateTime($his['exp_date']);
                                    $hariIni = new DateTime();
                                    $selisih = $hariIni->diff($tglExp);
                                    $sisaHari = (int)$selisih->format('%r%a'); 

                                    if ($sisaHari < 0) {
                                        echo '<span class="badge-status badge-expired">EXPIRED</span>';
                                    } elseif ($sisaHari <= 90) { 
                                        echo '<span class="badge-status badge-warning">Warning</span>';
                                    } else {
                                        echo '<span class="badge-status badge-aman">Aman</span>';
                                    }
                                } else {
                                    echo '<span style="color:#cbd5e1;">-</span>';
                                }
                                ?>
                            </td>

                            <td style="font-size: 1rem; color: #152e4d;">
                                <strong><?php echo (int)$his['jumlah']; ?></strong>
                            </td> 
                            <td style="color: #64748b;"><?php echo htmlspecialchars($his['nama_satuan']); ?></td> 
                            <td style="color: #475569;"><?php echo htmlspecialchars($his['nama_supplier']); ?></td>
                            
                            <td>
                                <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; color: #475569;">
                                    <?php echo htmlspecialchars($his['staff_nama']); ?>
                                </span>
                            </td>
                            
                            <td style="font-family: monospace; color: #2f8ba9;">
                                <?php echo htmlspecialchars($his['lot_number']); ?>
                            </td>
                            
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="<?php echo BASE_URL; ?>admin/detailBarangMasuk/<?php echo $his['transaction_id']; ?>" 
                                       class="btn-icon detail" 
                                       title="Lihat Detail Lengkap">
                                        <i class="ph ph-info"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-container custom-pagination" id="paginationContainerMasuk">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    
                    if($totalPages > 0) {
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        
                        if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $currentPage) ? 'active' : '';
                            echo '<li class="page-item '.$active.'"><a class="page-link" href="#" data-page="'.$i.'">'.$i.'</a></li>';
                        }

                        if($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }

                    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
                    echo '<li class="page-item '.$nextDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage + 1).'">Next</a></li>';
                ?>
            </ul>
        </nav>
    </div>

</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>