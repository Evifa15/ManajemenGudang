<?php
    // 1. Panggil Header
    require_once APPROOT . '/views/templates/header.php';
    // 2. Panggil Sidebar KHUSUS PEMILIK
    require_once APPROOT . '/views/templates/sidebar_pemilik.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Laporan Peminjaman Barang (Read-Only)</h1>
        <div>
            <button class="btn btn-primary" onclick="window.print();">Cetak Laporan</button>
        </div>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>pemilik/laporanPeminjaman" method="GET">
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
                    <td><?php echo date('d-m-Y', strtotime($his['tgl_rencana_kembali'])); ?></td>
                    <td style="font-weight: bold; color: <?php 
                        if($his['status_pinjam'] == 'Jatuh Tempo' || $his['status_pinjam'] == 'Ditolak') echo 'red'; 
                        else if($his['status_pinjam'] == 'Selesai') echo 'green'; 
                        else echo 'inherit'; 
                    ?>;">
                        <?php echo htmlspecialchars($his['status_pinjam']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($his['nama_staff'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        </div>
</main>

<?php
    // 3. Panggil Footer
    require_once APPROOT . '/views/templates/footer.php';
?>