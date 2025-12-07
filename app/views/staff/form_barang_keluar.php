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

    <div class="content-header">
        <h1>Riwayat Barang Keluar</h1>
    </div>

    <div class="history-top-bar">
        
        <div class="history-search-wrapper">
            <input type="text" id="liveSearchKeluar" 
                   class="history-search-input" 
                   placeholder="Cari Barang, Tujuan, Lot, atau Staff..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   autocomplete="off">
        </div>

        <div class="history-header-buttons">
            
            <button type="button" id="btnToggleFilterKeluar" class="btn btn-secondary" 
                    style="height: 42px; display: flex; align-items: center; border: 1px solid #cbd5e1; color: #64748b; background: #fff; padding: 0 15px; font-weight: 600; white-space: nowrap;"
                    title="Buka/Tutup Filter Tanggal">
                 <i class="ph ph-funnel" style="font-size: 1.2rem; margin-right: 5px;"></i> Filter
            </button>

            <div class="dropdown-export-wrapper">
                <button type="button" id="btnToggleExportKeluar" class="btn btn-brand-dark" 
                        style="height: 42px; display: flex; align-items: center; padding: 0 18px; white-space: nowrap;">
                    <i class="ph ph-export" style="font-size: 1.2rem; margin-right: 8px;"></i> Export
                    <i class="ph ph-caret-down" style="margin-left: 5px; font-size: 1rem;"></i>
                </button>

                <div id="exportMenuKeluar" class="dropdown-menu-custom">
                    <a href="#" class="btn-export-keluar-action" data-type="excel">
                        <i class="ph ph-microsoft-excel-logo" style="color: #10b981; font-size: 1.2rem;"></i> 
                        Excel (.xls)
                    </a>
                    <a href="#" class="btn-export-keluar-action" data-type="csv">
                        <i class="ph ph-file-csv" style="color: #0ea5e9; font-size: 1.2rem;"></i> 
                        CSV (.csv)
                    </a>
                    <a href="#" class="btn-export-keluar-action" data-type="pdf">
                        <i class="ph ph-file-pdf" style="color: #ef4444; font-size: 1.2rem;"></i> 
                        PDF Document
                    </a>
                </div>
            </div>
            
        </div>
    </div>

    <div id="filterPanelKeluar" class="search-card compact-filter" style="display: none; margin-bottom: 20px;">
        
        <div class="filter-panel-row">
            <div class="filter-panel-item">
                <label class="filter-label">Dari Tanggal</label>
                <input type="date" id="startDateKeluar" class="filter-select-clean" 
                       value="<?php echo htmlspecialchars($data['start_date']); ?>">
            </div>

            <div class="filter-panel-item">
                <label class="filter-label">Sampai Tanggal</label>
                <input type="date" id="endDateKeluar" class="filter-select-clean" 
                       value="<?php echo htmlspecialchars($data['end_date']); ?>">
            </div>

            <div style="flex: 0 0 auto;">
                <button type="button" id="btnResetKeluar" class="btn-reset-filter" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise" style="font-size: 1.2rem; font-weight: bold;"></i>
                </button>
            </div>
        </div>
        
        <div style="padding-bottom: 10px;">
            <small style="color: #94a3b8;">*Data otomatis diperbarui saat tanggal dipilih.</small>
        </div>
    </div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Keluar</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Tujuan / Keterangan</th>
                        <th>Diambil Oleh</th>
                        <th>Lot/Batch</th>
                    </tr>
                </thead>
                <tbody id="tableBodyKeluar">
                    <?php if (empty($data['history'])): ?>
                        <tr><td colspan="7" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['history'] as $his) : ?>
                        <tr>
                            <td style="color: #334155; font-size: 0.9rem;">
                                <?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?>
                            </td>
                            <td>
                                <strong style="color: #152e4d;"><?php echo htmlspecialchars($his['nama_barang']); ?></strong>
                            </td>
                            <td style="font-size: 1rem; color: #ef4444;">
                                <strong>-<?php echo (int)$his['jumlah']; ?></strong>
                            </td> 
                            <td style="color: #64748b;">
                                <?php echo htmlspecialchars($his['nama_satuan']); ?>
                            </td> 
                            <td style="color: #475569;">
                                <?php echo htmlspecialchars($his['keterangan']); ?>
                            </td>
                            <td>
                                <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; color: #475569;">
                                    <?php echo htmlspecialchars($his['staff_nama']); ?>
                                </span>
                            </td>
                            <td style="font-family: monospace; color: #2f8ba9;">
                                <?php echo htmlspecialchars($his['lot_number']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-container custom-pagination" id="paginationContainerKeluar">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    // Render awal PHP (nanti akan digantikan otomatis oleh transactions.js jika user berpindah halaman)
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