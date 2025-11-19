<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_peminjam.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Riwayat Peminjaman Saya</h1>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Tgl. Pengajuan</th>
                    <th>Nama Barang</th>
                    <th>Rencana Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['history'] as $loan) : ?>
                <tr>
                    <td><?php echo date('d-m-Y H:i', strtotime($loan['tgl_pengajuan'])); ?></td>
                    <td><?php echo htmlspecialchars($loan['nama_barang']); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($loan['tgl_rencana_kembali'])); ?></td>
                    <td>
                        <span style="color: <?php 
                            if($loan['status_pinjam'] == 'Selesai') echo 'green'; 
                            else if($loan['status_pinjam'] == 'Ditolak') echo 'red'; 
                            else if($loan['status_pinjam'] == 'Jatuh Tempo') echo 'red';
                            else if($loan['status_pinjam'] == 'Diajukan') echo 'orange';
                            else echo 'blue';
                        ?>; font-weight: bold;">
                            <?php echo htmlspecialchars($loan['status_pinjam']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($loan['status_pinjam'] == 'Diajukan'): ?>
                             <a href="#" class="btn btn-danger btn-sm btn-delete" data-url="#" onclick="alert('Belum ada logika pembatalan.');">Batalkan</a>
                        <?php else: ?>
                            <span style="color: #666;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>
