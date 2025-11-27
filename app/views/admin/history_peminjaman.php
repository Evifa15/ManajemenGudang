<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Riwayat Peminjaman Barang</h1>
    </div>

    <div class="search-container" style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            
            <div style="flex: 2; min-width: 200px;">
                <label style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Pencarian:</label>
                <input type="text" id="liveSearchPeminjaman" class="form-control" 
                       placeholder="🔍 Cari Nama Barang/Peminjam..." 
                       value="<?php echo htmlspecialchars($data['search']); ?>"
                       data-base-url="<?php echo BASE_URL; ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Filter Status:</label>
                <select id="filterStatusPeminjaman" class="form-control filter-select" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">-- Semua Status --</option>
                    <option value="Diajukan" <?php if($data['status_filter'] == 'Diajukan') echo 'selected'; ?>>Diajukan</option>
                    <option value="Disetujui" <?php if($data['status_filter'] == 'Disetujui') echo 'selected'; ?>>Disetujui</option>
                    <option value="Ditolak" <?php if($data['status_filter'] == 'Ditolak') echo 'selected'; ?>>Ditolak</option>
                    <option value="Sedang Dipinjam" <?php if($data['status_filter'] == 'Sedang Dipinjam') echo 'selected'; ?>>Sedang Dipinjam</option>
                    <option value="Selesai" <?php if($data['status_filter'] == 'Selesai') echo 'selected'; ?>>Selesai</option>
                    <option value="Jatuh Tempo" <?php if($data['status_filter'] == 'Jatuh Tempo') echo 'selected'; ?>>Jatuh Tempo</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Dari Tanggal:</label>
                <input type="date" id="startDatePeminjaman" class="form-control" 
                       value="<?php echo htmlspecialchars($data['start_date'] ?? ''); ?>"
                       style="width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label style="font-weight:bold; font-size:0.85em; color:#666; display:block; margin-bottom:5px;">Sampai Tanggal:</label>
                <input type="date" id="endDatePeminjaman" class="form-control" 
                       value="<?php echo htmlspecialchars($data['end_date'] ?? ''); ?>"
                       style="width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div>
                <a href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman" class="btn btn-danger" style="padding: 10px 15px; height: 42px; display: flex; align-items: center;" title="Reset">↻</a>
            </div>
        </div>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Tgl. Pengajuan</th>
                    <th>Nama Peminjam</th>
                    <th>Nama Barang</th>
                    <th>Tgl. Rencana Pinjam</th>
                    <th>Tgl. Rencana Kembali</th>
                    <th>Status</th>
                    <th>Divalidasi oleh (Staff)</th>
                </tr>
            </thead>
            <tbody id="tableBodyPeminjaman">
                <?php if (empty($data['history'])): ?>
                    <tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['history'] as $his) : ?>
                    <tr>
                        <td><?php echo date('d-m-Y H:i', strtotime($his['tgl_pengajuan'])); ?></td>
                        <td><?php echo htmlspecialchars($his['nama_peminjam']); ?></td>
                        <td><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($his['tgl_rencana_pinjam'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($his['tgl_rencana_kembali'])); ?></td>
                        <td><?php echo htmlspecialchars($his['status_pinjam']); ?></td>
                        <td><?php echo htmlspecialchars($his['nama_staff'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container" id="paginationContainerPeminjaman">
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