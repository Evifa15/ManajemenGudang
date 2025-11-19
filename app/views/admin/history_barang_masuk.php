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
        <h1>Riwayat Barang Masuk</h1>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk" method="GET">
            <input type="text" name="search" class="search-input" 
            placeholder="Silahkan Lakukan Pencarian Disini..." 
            value="<?php echo htmlspecialchars($data['search']); ?>">
            
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Tanggal Input</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Supplier</th>
                    <th>Diinput oleh (Staff)</th>
                    <th>Lot/Batch</th>
                    <th>Tgl. Kedaluwarsa</th>
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['history'] as $his) : ?>
                <tr>
                    <td><?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                    <td><strong><?php echo (int)$his['jumlah']; ?></strong></td> 
                    <td><?php // echo htmlspecialchars($his['nama_satuan']); ?></td> 
                    <td><?php echo htmlspecialchars($his['nama_supplier']); ?></td>
                    <td><?php echo htmlspecialchars($his['staff_nama']); ?></td>
                    <td><?php echo htmlspecialchars($his['lot_number']); ?></td>
                    <td><?php echo $his['exp_date'] ? date('d-m-Y', strtotime($his['exp_date'])) : '-'; ?></td>
                    <td>
                        <?php if($his['bukti_foto']): ?>
                            <a href="<?php echo BASE_URL . 'uploads/bukti_transaksi/' . $his['bukti_foto']; ?>" target="_blank" class="btn btn-primary btn-sm">Lihat</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
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
                    
                    // Logic Paginasi disederhanakan
                    $filterQuery = !empty($data['search']) ? '?search=' . urlencode($data['search']) : '';
                ?>
                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk/<?php echo $currentPage - 1; ?><?php echo $filterQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk/<?php echo $i; ?><?php echo $filterQuery; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk/<?php echo $currentPage + 1; ?><?php echo $filterQuery; ?>">Next</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>