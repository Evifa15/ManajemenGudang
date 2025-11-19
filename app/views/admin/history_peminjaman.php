<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        // Blok Notifikasi
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    <div class="content-header">
        <h1>Riwayat Peminjaman Barang</h1>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/riwayatPeminjaman" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Nama Barang atau Nama Peminjam..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="Diajukan" <?php if($data['status_filter'] == 'Diajukan') echo 'selected'; ?>>Diajukan</option>
                <option value="Disetujui" <?php if($data['status_filter'] == 'Disetujui') echo 'selected'; ?>>Disetujui</option>
                <option value="Ditolak" <?php if($data['status_filter'] == 'Ditolak') echo 'selected'; ?>>Ditolak</option>
                <option value="Sedang Dipinjam" <?php if($data['status_filter'] == 'Sedang Dipinjam') echo 'selected'; ?>>Sedang Dipinjam</option>
                <option value="Selesai" <?php if($data['status_filter'] == 'Selesai') echo 'selected'; ?>>Selesai</option>
                <option value="Jatuh Tempo" <?php if($data['status_filter'] == 'Jatuh Tempo') echo 'selected'; ?>>Jatuh Tempo</option>
            </select>
            
            <button type="submit" class="btn btn-primary">Filter / Cari</button>
        </form>
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
            <tbody>
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
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    
                    $queryParams = [];
                    if (!empty($data['search'])) { $queryParams['search'] = $data['search']; }
                    if (!empty($data['status_filter'])) { $queryParams['status'] = $data['status_filter']; }
                    $filterQuery = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                ?>
                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman/<?php echo $currentPage - 1; ?><?php echo $filterQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman/<?php echo $i; ?><?php echo $filterQuery; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman/<?php echo $currentPage + 1; ?><?php echo $filterQuery; ?>">Next</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>