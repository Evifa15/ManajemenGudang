<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>">
            <?php echo $_SESSION['flash_message']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="top-action-bar" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 20px; flex-wrap: nowrap;">
        
        <div class="search-hero-wrapper" style="flex-grow: 1; position: relative; height: 42px; min-width: 200px;">
            <i class="ph ph-magnifying-glass search-icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.2rem; z-index: 5;"></i>
            <input type="text" id="liveSearchBarang" 
                   class="search-hero-input" 
                   placeholder="Cari Kode atau Nama Barang..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   data-base-url="<?php echo BASE_URL; ?>"
                   style="width: 100%; height: 100%; padding: 0 15px 0 45px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;">
        </div>

        <div class="header-buttons" style="display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
            <button type="button" id="btnToggleFilter" class="btn btn-secondary" 
                    style="height: 42px; display: flex; align-items: center; border: 1px solid #cbd5e1; color: #64748b; background: #fff; padding: 0 15px; font-weight: 600; white-space: nowrap;">
                 <i class="ph ph-funnel" style="font-size: 1.2rem; margin-right: 5px;"></i> Filter
            </button>

            <button type="button" id="btnBulkDeleteBarang" class="btn btn-brand-dark" style="display: none; height: 42px; align-items: center; padding: 0 15px; background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; white-space: nowrap;" data-url="<?php echo BASE_URL; ?>admin/deleteBulkBarang">
                 <i class="ph ph-trash" style="font-size: 1.2rem; margin-right: 5px;"></i> Hapus (<span id="selectedCountBarang">0</span>)
            </button>

            <div class="dropdown-export-wrapper">
                <button type="button" id="btnToggleExportBarang" class="btn btn-export-toggle">
                    <i class="ph ph-export" style="font-size: 1.2rem; margin-right: 8px;"></i> 
                    Export 
                    <i class="ph ph-caret-down" style="margin-left: 5px; font-size: 1rem;"></i>
                </button>

                <div id="exportMenuBarang" class="dropdown-menu-custom">
                    <a href="#" class="btn-export-action" data-type="excel">
                        <i class="ph ph-microsoft-excel-logo" style="color: #10b981; font-size: 1.2rem;"></i> 
                        Excel (.xls)
                    </a>
                    <a href="#" class="btn-export-action" data-type="csv">
                        <i class="ph ph-file-csv" style="color: #0ea5e9; font-size: 1.2rem;"></i> 
                        CSV (.csv)
                    </a>
                    <a href="#" class="btn-export-action" data-type="pdf">
                        <i class="ph ph-file-pdf" style="color: #ef4444; font-size: 1.2rem;"></i> 
                        PDF Document
                    </a>
                </div>
            </div>
            <button type="button" id="btnImportCsv" class="btn btn-brand-dark" style="height: 42px; display: flex; align-items: center; padding: 0 15px; white-space: nowrap;">
                <i class="ph ph-upload-simple" style="font-size: 1.2rem; margin-right: 5px;"></i> Import
            </button>
            <a href="<?php echo BASE_URL; ?>admin/masterDataConfig" class="btn btn-brand-dark" style="height: 42px; display: flex; align-items: center; padding: 0 15px; white-space: nowrap; text-decoration: none;" title="Konfigurasi Master Data">
                <i class="ph ph-gear" style="font-size: 1.2rem; margin-right: 5px;"></i> Config
            </a>
            <a href="<?php echo BASE_URL; ?>admin/addBarang" class="btn btn-brand-dark" style="height: 42px; display: flex; align-items: center; padding: 0 20px; white-space: nowrap; text-decoration: none;">
                <i class="ph ph-plus" style="font-size: 1.2rem; margin-right: 5px;"></i> Tambah
            </a>
        </div>
    </div>

    <div id="filterPanel" class="filter-panel">
        <div class="filter-grid">
            
            <div class="filter-item">
                <label class="filter-label">Kategori</label>
                <div style="position: relative;">
                    <select id="filterKategori" class="filter-select-clean">
                        <option value="">- Semua Kategori -</option>
                        <?php foreach($data['allKategori'] as $kat): ?>
                            <option value="<?php echo $kat['kategori_id']; ?>" 
                                <?php if($data['kategori_filter'] == $kat['kategori_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ph ph-caret-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b;"></i>
                </div>
            </div>

            <div class="filter-item">
                <label class="filter-label">Merek</label>
                <div style="position: relative;">
                    <select id="filterMerek" class="filter-select-clean">
                        <option value="">- Semua Merek -</option>
                        <?php foreach($data['allMerek'] as $mrk): ?>
                            <option value="<?php echo $mrk['merek_id']; ?>" 
                                <?php if($data['merek_filter'] == $mrk['merek_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($mrk['nama_merek']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ph ph-caret-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b;"></i>
                </div>
            </div>
            
            <input type="hidden" id="filterStatus" value="">
            <input type="hidden" id="filterLokasi" value=""> 

            <div class="filter-item">
                <label class="filter-label">&nbsp;</label> 
                <button type="button" id="btnResetFilter" class="btn-reset-filter" title="Reset Semua Filter">
                    <i class="ph ph-arrow-counter-clockwise" style="margin-right: 5px; font-size: 1.2rem;"></i> Reset
                </button>
            </div>

        </div>
    </div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:center; width: 40px;">
                            <input type="checkbox" id="selectAllBarang" style="transform: scale(1.2); cursor: pointer;">
                        </th>
                        <th style="width: 15%;">Kode Barang</th>
                        <th style="width: 25%;">Nama Barang</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 15%;">Merek</th>
                        <th style="width: 10%;">Stok Min.</th>
                        <th style="min-width: 150px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="barangTableBody">
                    <?php if (empty($data['products'])): ?>
                        <tr><td colspan="7" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['products'] as $prod) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="barang-checkbox" value="<?php echo $prod['product_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                            </td>
                            <td style="font-weight: 500; color: #64748b; font-family: monospace;"><?php echo htmlspecialchars($prod['kode_barang']); ?></td>
                            <td><strong><?php echo htmlspecialchars($prod['nama_barang']); ?></strong></td>
                            <td><?php echo htmlspecialchars($prod['nama_kategori']); ?></td>
                            <td><?php echo htmlspecialchars($prod['nama_merek']); ?></td>
                            
                            <td><?php echo (int)$prod['stok_minimum']; ?></td> 
                            
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="<?php echo BASE_URL; ?>admin/detailBarang/<?php echo $prod['product_id']; ?>" class="btn-icon detail" title="Detail"><i class="ph ph-info"></i></a>
                                    <a href="<?php echo BASE_URL; ?>admin/cetakLabel/<?php echo $prod['product_id']; ?>" class="btn-icon print" title="Cetak Barcode"><i class="ph ph-printer"></i></a>
                                    <a href="<?php echo BASE_URL; ?>admin/editBarang/<?php echo $prod['product_id']; ?>" class="btn-icon edit" title="Edit"><i class="ph ph-pencil-simple"></i></a>
                                    <button type="button" class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteBarang/<?php echo $prod['product_id']; ?>" title="Hapus"><i class="ph ph-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="pagination-container custom-pagination" id="paginationContainerBarang">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    
                    if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    if($totalPages > 0) {
                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $currentPage) ? 'active' : '';
                            echo '<li class="page-item '.$active.'"><a class="page-link" href="#" data-page="'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    
                    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
                    echo '<li class="page-item '.$nextDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage + 1).'">Next</a></li>';
                ?>
            </ul>
        </nav>
    </div>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>