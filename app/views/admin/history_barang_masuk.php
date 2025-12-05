<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content" data-base-url="<?php echo BASE_URL; ?>">
    
    <div class="content-header">
        <h1>Riwayat Barang Masuk</h1>
        <button type="button" id="btnExportMasuk" class="btn" style="background-color: #28a745; color: white;">
            📄 Export Data
        </button>
    </div>

    <div class="search-container" style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            
            <div style="flex: 2; min-width: 250px;">
                <label style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Pencarian Universal:</label>
                <input type="text" id="liveSearchMasuk" class="form-control" 
                       placeholder="🔍 Cari Barang, Supplier, Lot, atau Staff..." 
                       value="<?php echo htmlspecialchars($data['search']); ?>"
                       data-base-url="<?php echo BASE_URL; ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label for="startDateMasuk" style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Dari Tanggal:</label>
                <input type="date" id="startDateMasuk" class="form-control filter-date-masuk" 
                       value="<?php echo htmlspecialchars($data['start_date']); ?>"
                       style="width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label for="endDateMasuk" style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Sampai Tanggal:</label>
                <input type="date" id="endDateMasuk" class="form-control filter-date-masuk" 
                       value="<?php echo htmlspecialchars($data['end_date']); ?>"
                       style="width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div>
                <a href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk" class="btn btn-danger" style="padding: 10px 15px; height: 42px; display: flex; align-items: center;" title="Reset Filter">↻</a>
            </div>
        </div>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Tanggal Input</th>
                    <th>Nama Barang</th>
                    <th>Status Exp</th> <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Supplier</th>
                    <th>Diinput oleh</th>
                    <th>Lot/Batch</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBodyMasuk">
                <?php if (empty($data['history'])): ?>
                    <tr><td colspan="9" style="text-align:center;">Data tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['history'] as $his) : ?>
                    <tr>
                        <td><?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?></td>
                        
                        <td style="font-weight: bold;"><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                        
                        <td>
                            <?php 
                            if (!empty($his['exp_date'])) {
                                $tglExp = new DateTime($his['exp_date']);
                                $hariIni = new DateTime();
                                $selisih = $hariIni->diff($tglExp);
                                $sisaHari = (int)$selisih->format('%r%a');

                                if ($sisaHari < 0) {
                                    echo '<span style="color:white; background:#dc3545; padding:3px 8px; border-radius:4px; font-size:0.85em; font-weight:bold;">EXPIRED</span>';
                                } elseif ($sisaHari <= 90) { 
                                    echo '<span style="color:black; background:#ffc107; padding:3px 8px; border-radius:4px; font-size:0.85em; font-weight:bold;">Warning (< 3 Bln)</span>';
                                } else {
                                    echo '<span style="color:green; font-size:0.85em;">Aman</span>';
                                }
                            } else {
                                echo '<span style="color:#999;">-</span>';
                            }
                            ?>
                        </td>

                        <td><strong><?php echo (int)$his['jumlah']; ?></strong></td> 
                        <td><?php echo htmlspecialchars($his['nama_satuan']); ?></td> 
                        <td><?php echo htmlspecialchars($his['nama_supplier']); ?></td>
                        <td><?php echo htmlspecialchars($his['staff_nama']); ?></td>
                        <td><?php echo htmlspecialchars($his['lot_number']); ?></td>
                        
                        <td style="text-align: center;">
                            <a href="<?php echo BASE_URL; ?>admin/detailBarangMasuk/<?php echo $his['transaction_id']; ?>" 
                               class="btn btn-sm" 
                               style="background-color: #17a2b8; color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px;"
                               title="Lihat Detail Lengkap">
                                🔍 Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container" id="paginationContainerMasuk">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    
                    // Logic simple paginasi
                    if($totalPages > 0) {
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
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