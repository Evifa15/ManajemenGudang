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
    
    <div class="content-header">
        <h1>Manajemen Barang</h1>
        <div class="header-buttons">
            <button type="button" id="btnBulkDeleteBarang" class="btn btn-danger" style="display: none; margin-right: 10px;" 
                    data-url="<?php echo BASE_URL; ?>admin/deleteBulkBarang">
                🗑️ Hapus Terpilih (<span id="selectedCountBarang">0</span>)
            </button>

            <a href="<?php echo BASE_URL; ?>admin/masterDataConfig" class="btn" style="background-color: #6c757d; color: white;">
                ⚙️ Konfigurasi Data Atribut
            </a>
            <a href="<?php echo BASE_URL; ?>admin/addBarang" class="btn btn-primary">
                + Tambah Barang Baru
            </a>
        </div>
    </div>

    <div class="search-container" style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            
            <div style="flex: 2; min-width: 250px;">
                <input type="text" id="liveSearchBarang" class="form-control" 
                       placeholder="🔍 Cari Kode, Nama..." 
                       value="<?php echo htmlspecialchars($data['search']); ?>"
                       data-base-url="<?php echo BASE_URL; ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterKategori" class="filter-select live-filter-barang" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Kategori -</option>
                    <?php foreach($data['allKategori'] as $kat): ?>
                        <option value="<?php echo $kat['kategori_id']; ?>" <?php if($data['kategori_filter'] == $kat['kategori_id']) echo 'selected'; ?>>
                            <?php echo $kat['nama_kategori']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterMerek" class="filter-select live-filter-barang" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Merek -</option>
                     <?php foreach($data['allMerek'] as $mrk): ?>
                        <option value="<?php echo $mrk['merek_id']; ?>" <?php if($data['merek_filter'] == $mrk['merek_id']) echo 'selected'; ?>>
                            <?php echo $mrk['nama_merek']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterStatus" class="filter-select live-filter-barang" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Status -</option>
                     <?php foreach($data['allStatus'] as $stat): ?>
                        <option value="<?php echo $stat['status_id']; ?>" <?php if($data['status_filter'] == $stat['status_id']) echo 'selected'; ?>>
                            <?php echo $stat['nama_status']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterLokasi" class="filter-select live-filter-barang" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Lokasi -</option>
                     <?php foreach($data['allLokasi'] as $lok): ?>
                        <option value="<?php echo $lok['lokasi_id']; ?>" <?php if($data['lokasi_filter'] == $lok['lokasi_id']) echo 'selected'; ?>>
                            <?php echo $lok['kode_lokasi']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <a href="<?php echo BASE_URL; ?>admin/barang" class="btn btn-danger" style="padding: 10px 15px;" title="Reset Filter">↻</a>
            </div>
        </div>
    </div>

    <div class="content-table">
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
                    <th>Stok Min.</th>
                    <th>Status</th> <th>Lokasi (Contoh)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="barangTableBody">
                <?php if (empty($data['products'])): ?>
                    <tr><td colspan="11" style="text-align:center;">Data tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['products'] as $prod) : 
                        // Logika Status
                        $stok = (int)$prod['stok_saat_ini'];
                        $min = (int)$prod['stok_minimum'];
                        $statusBadge = '';
                        
                        if ($stok == 0) {
                            $statusBadge = '<span style="background:#dc3545; color:white; padding:3px 8px; border-radius:4px; font-size:0.8em;">Habis</span>';
                        } elseif ($stok <= $min) {
                            $statusBadge = '<span style="background:#ffc107; color:black; padding:3px 8px; border-radius:4px; font-size:0.8em;">Menipis</span>';
                        } else {
                            $statusBadge = '<span style="background:#28a745; color:white; padding:3px 8px; border-radius:4px; font-size:0.8em;">Aman</span>';
                        }
                    ?>
                    <tr>
                        <td style="text-align:center;">
                            <input type="checkbox" class="barang-checkbox" value="<?php echo $prod['product_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                        </td>
                        <td><?php echo htmlspecialchars($prod['kode_barang']); ?></td>
                        <td><?php echo htmlspecialchars($prod['nama_barang']); ?></td>
                        <td><?php echo htmlspecialchars($prod['nama_kategori']); ?></td>
                        <td><?php echo htmlspecialchars($prod['nama_merek']); ?></td>
                        <td><strong><?php echo $stok; ?></strong></td> 
                        <td><?php echo htmlspecialchars($prod['nama_satuan']); ?></td>
                        <td><?php echo htmlspecialchars($prod['stok_minimum']); ?></td>
                        <td><?php echo $statusBadge; ?></td> <td><?php echo htmlspecialchars($prod['kode_lokasi']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>admin/editBarang/<?php echo $prod['product_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                    data-url="<?php echo BASE_URL; ?>admin/deleteBarang/<?php echo $prod['product_id']; ?>">
                                Hapus
                            </button>  
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container" id="paginationContainerBarang">
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    // (Kita render awal via PHP, nanti JS akan handle update-nya)
                    
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    
                    if($totalPages > 0) {
                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $currentPage) ? 'active' : '';
                            echo '<li class="page-item '.$active.'"><a class="page-link" href="#" data-page="'.$i.'">'.$i.'</a></li>';
                        }
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