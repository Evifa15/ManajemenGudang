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
           Status Kondisi
        </a>
        <a href="#tab-lokasi" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addLokasi" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkLokasi">
           Lokasi 
        </a>
        <a href="#tab-supplier" class="tab-nav-link" 
           data-add-url="<?php echo BASE_URL; ?>admin/addSupplier" 
           data-delete-url="<?php echo BASE_URL; ?>admin/deleteBulkSupplier">
           Supplier
        </a>

       
    </div>

    <div class="master-toolbar">
        <div class="master-search-wrapper">
            <i class="ph ph-magnifying-glass master-search-icon"></i>
            <input type="text" id="universalSearchInput" class="master-search-input" placeholder="Cari data di tabel aktif...">
        </div>
        <div class="master-actions">
            <button type="button" id="btnBulkDeleteTab" class="btn" style="display: none; height: 42px; background-color: #fee2e2; color: #ef4444; border: 1px solid #fecaca; font-weight: 600; padding: 0 15px;">
                <i class="ph ph-trash" style="font-size: 1.2rem; margin-right: 5px;"></i> Hapus Terpilih
            </button>
            <a href="<?php echo BASE_URL; ?>admin/addKategori" id="btnMasterAdd" class="btn btn-brand-dark" style="height: 42px; padding: 0 20px; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-plus-circle" style="font-size: 1.2rem;"></i> 
                <span>Tambah Data</span>
            </a>
        </div>
    </div>

    <div class="tab-content">
        
        <div id="view-kategori" class="tab-pane active" data-tab-name="kategori">
            <div class="master-table-container">
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
                                            <a href="<?php echo BASE_URL; ?>admin/editKategori/<?php echo $row['kategori_id']; ?>" class="btn-icon edit"><i class="ph ph-pencil-simple"></i></a>
                                            <button class="btn-icon delete btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteKategori/<?php echo $row['kategori_id']; ?>"><i class="ph ph-trash"></i></button>
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

        <div id="view-merek" class="tab-pane" data-tab-name="merek">
    <div class="master-table-container">
        <div class="table-wrapper-flat">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width: 40px;">
                            <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                        </th>
                        <th style="width: 50px;">No</th> 
                        <th style="width: 25%;">Nama Merek</th>
                        <th>Deskripsi</th> <th style="width: 15%;">Status</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['merek'])): ?>
                        <tr><td colspan="6" style="text-align:center; color:#999;">Belum ada data merek.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($data['merek'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['merek_id']; ?>" style="transform: scale(1.2);"></td>
                            <td><?php echo $no++; ?></td>
                            
                            <td class="searchable">
                                <strong><?php echo htmlspecialchars($row['nama_merek']); ?></strong>
                            </td>
                            
                            <td class="searchable" style="color: #64748b; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($row['deskripsi'] ?? '-'); ?>
                            </td>

                            <td class="searchable">
                                <?php 
                                    $status = $row['status'] ?? 'Aktif'; 
                                    if($status == 'Aktif') {
                                        $bg = '#dcfce7'; $col = '#166534'; $bord = '#bbf7d0';
                                    } else {
                                        $bg = '#fee2e2'; $col = '#991b1b'; $bord = '#fecaca';
                                    }
                                ?>
                                <span style="
                                    padding: 4px 10px; 
                                    background: <?php echo $bg; ?>; 
                                    color: <?php echo $col; ?>; 
                                    border: 1px solid <?php echo $bord; ?>;
                                    border-radius: 20px; 
                                    font-weight: 700; 
                                    font-size: 0.8rem;
                                ">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>

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
    </div>
    <div class="pagination-container custom-pagination"></div>
</div>

        <div id="view-satuan" class="tab-pane" data-tab-name="satuan">
            <div class="master-table-container">
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
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-status" class="tab-pane" data-tab-name="status">
            <div class="master-table-container">
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
                                <?php $no = 1; foreach ($data['status'] as $row) : 
                                    // LOGIKA WARNA LABEL OTOMATIS
                                    $nama = strtolower($row['nama_status']);
                                    
                                    // Default (Abu-abu - Netral)
                                    $bg = '#f1f5f9'; $color = '#475569'; $border = '#e2e8f0';

                                    // Hijau (Positif)
                                    if (strpos($nama, 'tersedia') !== false || strpos($nama, 'baik') !== false || strpos($nama, 'aman') !== false) {
                                        $bg = '#dcfce7'; $color = '#166534'; $border = '#bbf7d0';
                                    }
                                    // Merah (Negatif)
                                    elseif (strpos($nama, 'rusak') !== false || strpos($nama, 'hilang') !== false || strpos($nama, 'reject') !== false) {
                                        $bg = '#fee2e2'; $color = '#991b1b'; $border = '#fecaca';
                                    }
                                    // Oranye (Warning)
                                    elseif (strpos($nama, 'kadaluwarsa') !== false || strpos($nama, 'expired') !== false || strpos($nama, 'menipis') !== false) {
                                        $bg = '#fff7ed'; $color = '#c2410c'; $border = '#ffedd5';
                                    }
                                    // Biru (Info)
                                    elseif (strpos($nama, 'karantina') !== false || strpos($nama, 'pending') !== false || strpos($nama, 'hold') !== false) {
                                        $bg = '#e0f2fe'; $color = '#075985'; $border = '#bae6fd';
                                    }
                                ?>
                                <tr>
                                    <td style="text-align:center;"><input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['status_id']; ?>" style="transform: scale(1.2);"></td>
                                    <td><?php echo $no++; ?></td>
                                    <td class="searchable">
                                        <span style="
                                            padding: 5px 10px; 
                                            background: <?php echo $bg; ?>; 
                                            color: <?php echo $color; ?>; 
                                            border: 1px solid <?php echo $border; ?>;
                                            border-radius: 20px; 
                                            font-weight: 700; 
                                            font-size: 0.85rem;
                                            display: inline-block;
                                            min-width: 80px;
                                            text-align: center;
                                        ">
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
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-lokasi" class="tab-pane" data-tab-name="lokasi">
            <div class="master-table-container">
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
            </div>
            <div class="pagination-container custom-pagination"></div>
        </div>

        <div id="view-supplier" class="tab-pane" data-tab-name="supplier">
    <div class="master-table-container">
        <div class="table-wrapper-flat">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width: 40px;">
                            <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                        </th>
                        <th>Perusahaan / Supplier</th>
                        <th>PIC (Penanggung Jawab)</th>
                        <th>Kontak & Keuangan</th>
                        <th>Status</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['suppliers'])): ?>
                        <tr><td colspan="6" style="text-align:center; color:#999;">Belum ada data supplier.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['suppliers'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['supplier_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            
                            <td class="searchable">
                                <strong style="font-size: 1rem; color: #152e4d;"><?php echo htmlspecialchars($row['nama_supplier']); ?></strong>
                                <br>
                                <small style="color: #64748b; font-size: 0.8rem;">
                                    <i class="ph ph-map-pin"></i> <?php echo htmlspecialchars($row['alamat'] ?? '-'); ?>
                                </small>
                            </td>
                            
                            <td class="searchable">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="background: #e0f2fe; color: #0369a1; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                        <?php echo strtoupper(substr($row['kontak_person'], 0, 1)); ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($row['kontak_person']); ?></span>
                                </div>
                            </td>

                            <td class="searchable" style="font-size: 0.85rem;">
                                <div style="margin-bottom: 2px;">
                                    <i class="ph ph-phone" style="color: #64748b;"></i> <?php echo htmlspecialchars($row['telepon']); ?>
                                </div>
                                <?php if(!empty($row['email'])): ?>
                                <div style="margin-bottom: 2px;">
                                    <i class="ph ph-envelope" style="color: #64748b;"></i> <?php echo htmlspecialchars($row['email']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if(!empty($row['bank'])): ?>
                                <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #e2e8f0; color: #059669;">
                                    <i class="ph ph-bank"></i> <strong><?php echo htmlspecialchars($row['bank']); ?></strong>: <?php echo htmlspecialchars($row['no_rekening']); ?>
                                </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php 
                                    $status = $row['status'] ?? 'Aktif'; 
                                    if($status == 'Aktif') {
                                        $bg = '#dcfce7'; $col = '#166534'; $bord = '#bbf7d0';
                                    } else {
                                        $bg = '#fee2e2'; $col = '#991b1b'; $bord = '#fecaca';
                                    }
                                ?>
                                <span style="
                                    padding: 4px 10px; 
                                    background: <?php echo $bg; ?>; 
                                    color: <?php echo $col; ?>; 
                                    border: 1px solid <?php echo $bord; ?>;
                                    border-radius: 20px; 
                                    font-weight: 700; 
                                    font-size: 0.75rem;
                                ">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>

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

    </div>
</main>
    
<?php
    require_once APPROOT . '/views/templates/footer.php';
    
?>