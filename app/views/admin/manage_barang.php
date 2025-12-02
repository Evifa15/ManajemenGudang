<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    <form action="<?php echo BASE_URL; ?>admin/barang" method="GET">

        <div class="toolbar-floating">
            
            <div class="search-box-wrapper">
                <i class="ph ph-magnifying-glass search-icon"></i>
                <input type="text" id="liveSearchBarang" class="table-search-input" 
                       placeholder="Cari Kode atau Nama Barang..." 
                       value="<?php echo htmlspecialchars($data['search']); ?>"
                       data-base-url="<?php echo BASE_URL; ?>">
            </div>

            <div class="toolbar-actions">
                
                <button type="button" id="btnToggleFilter" class="btn btn-secondary btn-sm" 
                        style="border: 1px solid #cbd5e1; color: #64748b; background: #fff;"
                        title="Buka/Tutup Filter">
                     <i class="ph ph-funnel"></i> Filter
                </button>

                <button type="button" id="btnBulkDeleteBarang" class="btn btn-brand-dark btn-sm" style="display: none;" 
                        data-url="<?php echo BASE_URL; ?>admin/deleteBulkBarang">
                     <i class="ph ph-trash"></i> Hapus (<span id="selectedCountBarang">0</span>)
                </button>
                
                <button type="button" id="btnImportCsv" class="btn btn-brand-dark btn-sm">
                    <i class="ph ph-file-csv"></i> Import
                </button>
                <button type="button" id="btnExportSelector" class="btn btn-brand-dark btn-sm">
                    <i class="ph ph-file-xls"></i> Export
                </button>
                
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig" class="btn btn-brand-dark btn-sm">
                    <i class="ph ph-gear"></i> Config
                </a>
                
                <a href="<?php echo BASE_URL; ?>admin/addBarang" class="btn btn-brand-dark btn-sm" style="padding: 8px 20px;">
                    <i class="ph ph-plus"></i> Tambah Barang
                </a>
            </div>
        </div>

        <div id="filterPanel" class="search-card compact-filter" style="display: none; margin-bottom: 20px;">
            <div class="filter-row" style="padding: 10px 0;">
                
                <div class="filter-item">
                    <label>Kategori</label>
                    <select id="filterKategori" class="filter-select-clean live-filter-barang">
                        <option value="">- Semua Kategori -</option>
                        <?php foreach($data['allKategori'] as $kat): ?>
                            <option value="<?php echo $kat['kategori_id']; ?>" <?php if($data['kategori_filter'] == $kat['kategori_id']) echo 'selected'; ?>>
                                <?php echo $kat['nama_kategori']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Merek</label>
                    <select id="filterMerek" class="filter-select-clean live-filter-barang">
                        <option value="">- Semua Merek -</option>
                        <?php foreach($data['allMerek'] as $mrk): ?>
                            <option value="<?php echo $mrk['merek_id']; ?>" <?php if($data['merek_filter'] == $mrk['merek_id']) echo 'selected'; ?>>
                                <?php echo $mrk['nama_merek']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Status Stok</label>
                    <select id="filterStatus" class="filter-select-clean live-filter-barang">
                        <option value="">- Semua Status -</option>
                        <?php foreach($data['allStatus'] as $stat): ?>
                            <option value="<?php echo $stat['status_id']; ?>" <?php if($data['status_filter'] == $stat['status_id']) echo 'selected'; ?>>
                                <?php echo $stat['nama_status']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Lokasi Rak</label>
                    <select id="filterLokasi" class="filter-select-clean live-filter-barang">
                        <option value="">- Semua Lokasi -</option>
                        <?php foreach($data['allLokasi'] as $lok): ?>
                            <option value="<?php echo $lok['lokasi_id']; ?>" <?php if($data['lokasi_filter'] == $lok['lokasi_id']) echo 'selected'; ?>>
                                <?php echo $lok['kode_lokasi']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item action" style="flex: 0 0 auto;">
                    <label>&nbsp;</label>
                    <a href="<?php echo BASE_URL; ?>admin/barang" class="btn-reset" title="Reset Filter">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </a>
                </div>
            </div>
        </div>

    </form>

    <script>
        document.getElementById('btnToggleFilter').addEventListener('click', function() {
            var panel = document.getElementById('filterPanel');
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
                this.style.backgroundColor = '#e2e8f0';
                this.style.color = '#152e4d';
            } else {
                panel.style.display = 'none';
                this.style.backgroundColor = '#fff'; 
                this.style.color = '#64748b';
            }
        });
    </script>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:center; width: 40px;">
                            <input type="checkbox" id="selectAllBarang" style="transform: scale(1.2); cursor: pointer;">
                        </th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Merek</th>
                        <th>Stok Saat Ini</th>
                        <th>Satuan</th>
                        <th>Status</th> 
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="barangTableBody">
                    <?php if (empty($data['products'])): ?>
                        <tr><td colspan="9" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['products'] as $prod) : 
                            $stok = (int)$prod['stok_saat_ini'];
                            $min = (int)$prod['stok_minimum'];
                            $statusBadge = '';
                            
                            if ($stok == 0) {
                                $statusBadge = '<span style="background:#fee2e2; color:#991b1b; padding:4px 10px; border-radius:20px; font-size:0.8rem; font-weight:700; border:1px solid #fecaca;">Habis</span>';
                            } elseif ($stok <= $min) {
                                $statusBadge = '<span style="background:#fef3c7; color:#92400e; padding:4px 10px; border-radius:20px; font-size:0.8rem; font-weight:700; border:1px solid #fde68a;">Menipis</span>';
                            } else {
                                $statusBadge = '<span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:20px; font-size:0.8rem; font-weight:700; border:1px solid #bbf7d0;">Aman</span>';
                            }
                        ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="barang-checkbox" value="<?php echo $prod['product_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                            </td>
                            <td style="font-weight: 500; color: #64748b;"><?php echo htmlspecialchars($prod['kode_barang']); ?></td>
                            <td><strong><?php echo htmlspecialchars($prod['nama_barang']); ?></strong></td>
                            <td><?php echo htmlspecialchars($prod['nama_kategori']); ?></td>
                            <td><?php echo htmlspecialchars($prod['nama_merek']); ?></td>
                            <td><strong><?php echo $stok; ?></strong></td> 
                            <td><?php echo htmlspecialchars($prod['nama_satuan']); ?></td>
                            <td><?php echo $statusBadge; ?></td> 
                            
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo BASE_URL; ?>admin/detailBarang/<?php echo $prod['product_id']; ?>" 
                                       class="btn-icon detail" title="Detail">
                                        <i class="ph ph-info"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>admin/cetakLabel/<?php echo $prod['product_id']; ?>" 
                                       class="btn-icon print" title="Cetak Barcode">
                                        <i class="ph ph-printer"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>admin/editBarang/<?php echo $prod['product_id']; ?>" 
                                       class="btn-icon edit" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn-icon delete btn-delete" 
                                            data-url="<?php echo BASE_URL; ?>admin/deleteBarang/<?php echo $prod['product_id']; ?>"
                                            title="Hapus">
                                        <i class="ph ph-trash"></i>
                                    </button>
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
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>