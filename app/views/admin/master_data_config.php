<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content" id="masterDataPage">
    
    <?php
        // Blok Notifikasi
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    <div class="tab-nav">
        <a href="#tab-kategori" class="tab-nav-link active" 
           data-add-url="<?php echo BASE_URL; ?>admin/addKategori" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkKategori">
           Kategori
        </a>
        <a href="#tab-merek" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addMerek" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkMerek">
           Merek
        </a>
        <a href="#tab-satuan" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addSatuan" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkSatuan">
           Satuan
        </a>
        <a href="#tab-status" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addStatus" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkStatus">
           Status Barang
        </a>
        <a href="#tab-lokasi" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addLokasi" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkLokasi">
           Lokasi / Rak
        </a>
        <a href="#tab-supplier" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addSupplier" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkSupplier">
           Supplier
        </a>

        <a href="<?php echo BASE_URL; ?>admin/barang" class="tab-nav-action-right" title="Kembali ke Barang">
            <span>Kembali</span>
        </a>
    </div>

    <div class="table-toolbar">
        
        <div class="search-box-wrapper">
            <i class="ph ph-magnifying-glass search-icon"></i>
            <input type="text" id="universalSearchInput" class="table-search-input" placeholder="Cari data di tabel aktif...">
        </div>

        <div class="toolbar-actions">
            <button type="button" id="btnBulkDeleteTab" class="btn btn-danger btn-sm" style="display: none; padding: 8px 15px;">
                <i class="ph ph-trash"></i> Hapus Terpilih
            </button>
            
            <a href="<?php echo BASE_URL; ?>admin/addKategori" id="btnMasterAdd" class="btn btn-primary btn-sm" style="padding: 8px 20px;">
                <i class="ph ph-plus"></i> Tambah Data
            </a>
        </div>
    </div>

    <div class="tab-content">
        
        <div id="view-kategori" class="tab-pane active" data-tab-name="kategori">
            <div class="table-wrapper-flat">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['kategori'])): ?>
                            <tr><td colspan="5" style="text-align:center; color:#999;">Belum ada data kategori.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['kategori'] as $row) : ?>
                            <tr>
                                <td style="text-align:center;">
                                    <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['kategori_id']; ?>" style="transform: scale(1.2);">
                                </td>
                                <td><?php echo $no++; ?></td>
                                <td class="searchable"><strong><?php echo htmlspecialchars($row['nama_kategori']); ?></strong></td>
                                <td class="searchable" style="color: #666;"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>admin/editKategori/<?php echo $row['kategori_id']; ?>" class="btn-icon edit" title="Edit">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                        <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteKategori/<?php echo $row['kategori_id']; ?>" title="Hapus">
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
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-merek" class="tab-pane" data-tab-name="merek">
            <div class="table-wrapper-flat">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            
                            <th style="width: 80px;">No</th> 
                            
                            <th>Nama Merek</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['merek'])): ?>
                            <tr><td colspan="4" style="text-align:center; color:#999;">Belum ada data merek.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['merek'] as $row) : ?>
                            <tr>
                                <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['merek_id']; ?>" style="transform: scale(1.2);"></td>
                                <td><?php echo $no++; ?></td>
                                <td class="searchable"><strong><?php echo htmlspecialchars($row['nama_merek']); ?></strong></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>admin/editMerek/<?php echo $row['merek_id']; ?>" class="btn-icon edit"><i class="ph ph-pencil-simple"></i></a>
                                        <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteMerek/<?php echo $row['merek_id']; ?>"><i class="ph ph-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-satuan" class="tab-pane" data-tab-name="satuan">
            <div class="table-wrapper-flat">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;"><input type="checkbox" class="select-all-tab" style="transform: scale(1.2);"></th>
                            <th>No</th>
                            <th>Nama Satuan</th>
                            <th>Singkatan</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if(empty($data['satuan'])): ?>
                            <tr><td colspan="5" style="text-align:center; color:#999;">Belum ada data satuan.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['satuan'] as $row) : ?>
                            <tr>
                                <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['satuan_id']; ?>" style="transform: scale(1.2);"></td>
                                <td><?php echo $no++; ?></td>
                                <td class="searchable"><strong><?php echo htmlspecialchars($row['nama_satuan']); ?></strong></td>
                                <td class="searchable"><?php echo htmlspecialchars($row['singkatan']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>admin/editSatuan/<?php echo $row['satuan_id']; ?>" class="btn-icon edit"><i class="ph ph-pencil-simple"></i></a>
                                        <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteSatuan/<?php echo $row['satuan_id']; ?>"><i class="ph ph-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-status" class="tab-pane" data-tab-name="status">
            <div class="table-wrapper-flat">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;"><input type="checkbox" class="select-all-tab" style="transform: scale(1.2);"></th>
                            <th>No</th>
                            <th>Nama Status</th>
                            <th>Deskripsi</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if(empty($data['status'])): ?>
                            <tr><td colspan="5" style="text-align:center; color:#999;">Belum ada data status.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['status'] as $row) : ?>
                            <tr>
                                <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['status_id']; ?>" style="transform: scale(1.2);"></td>
                                <td><?php echo $no++; ?></td>
                                <td class="searchable">
                                    <span style="padding:4px 8px; background:#f1f5f9; border-radius:4px; font-weight:bold; font-size:0.9em;">
                                        <?php echo htmlspecialchars($row['nama_status']); ?>
                                    </span>
                                </td>
                                <td class="searchable" style="color:#666;"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>admin/editStatus/<?php echo $row['status_id']; ?>" class="btn-icon edit"><i class="ph ph-pencil-simple"></i></a>
                                        <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteStatus/<?php echo $row['status_id']; ?>"><i class="ph ph-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-lokasi" class="tab-pane" data-tab-name="lokasi">
            <div class="table-wrapper-flat">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;"><input type="checkbox" class="select-all-tab" style="transform: scale(1.2);"></th>
                            
                            <th style="width: 80px;">No</th>
                            
                            <th>Kode Lokasi</th>
                            <th>Nama Rak</th>
                            <th>Zona</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['lokasi'])): ?>
                            <tr><td colspan="6" style="text-align:center; color:#999;">Belum ada data lokasi.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['lokasi'] as $row) : ?>
                            <tr>
                                <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['lokasi_id']; ?>" style="transform: scale(1.2);"></td>
                                
                                <td><?php echo $no++; ?></td>
                                
                                <td class="searchable"><strong><?php echo htmlspecialchars($row['kode_lokasi']); ?></strong></td>
                                <td class="searchable"><?php echo htmlspecialchars($row['nama_rak']); ?></td>
                                <td class="searchable"><?php echo htmlspecialchars($row['zona']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>admin/editLokasi/<?php echo $row['lokasi_id']; ?>" class="btn-icon edit"><i class="ph ph-pencil-simple"></i></a>
                                        <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteLokasi/<?php echo $row['lokasi_id']; ?>"><i class="ph ph-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-supplier" class="tab-pane" data-tab-name="supplier">
            <div class="table-wrapper-flat">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;"><input type="checkbox" class="select-all-tab" style="transform: scale(1.2);"></th>
                            
                            <th style="width: 80px;">No</th>
                            
                            <th>Nama Supplier</th>
                            <th>Kontak</th>
                            <th>Telepon</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if(empty($data['suppliers'])): ?>
                            <tr><td colspan="6" style="text-align:center; color:#999;">Belum ada data supplier.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['suppliers'] as $row) : ?>
                            <tr>
                                <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['supplier_id']; ?>" style="transform: scale(1.2);"></td>
                                
                                <td><?php echo $no++; ?></td>
                                
                                <td class="searchable"><strong><?php echo htmlspecialchars($row['nama_supplier']); ?></strong></td>
                                <td class="searchable"><?php echo htmlspecialchars($row['kontak_person']); ?></td>
                                <td class="searchable"><?php echo htmlspecialchars($row['telepon']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>admin/editSupplier/<?php echo $row['supplier_id']; ?>" class="btn-icon edit"><i class="ph ph-pencil-simple"></i></a>
                                        <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteSupplier/<?php echo $row['supplier_id']; ?>"><i class="ph ph-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container custom-pagination"></div>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>