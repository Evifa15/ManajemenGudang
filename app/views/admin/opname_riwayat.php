<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Arsip / Riwayat Stock Opname</h1>
        <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
        ?>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>No. SP</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Status</th>
                    <th>Difinalisasi Oleh</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['completedPeriods'])): ?>
                    <tr><td colspan="6" style="text-align:center;">Belum ada arsip riwayat opname.</td></tr>
                <?php else: ?>
                    <?php foreach($data['completedPeriods'] as $period): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($period['nomor_sp'] ?? ('Period #' . $period['period_id'])); ?></strong>
                        </td>
                        <td><?php echo date('d-M-Y H:i', strtotime($period['start_date'])); ?></td>
                        <td><?php echo $period['end_date'] ? date('d-M-Y H:i', strtotime($period['end_date'])) : '-'; ?></td>
                        <td>
                            <span style="color: green; font-weight: bold; border: 1px solid green; padding: 3px 8px; border-radius: 4px;">
                                <?php echo $period['status']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($period['finalized_by'] ?? '-'); ?></td>
                        <td style="text-align: center;">
                            <a href="<?php echo BASE_URL; ?>admin/detailRiwayatOpname/<?php echo $period['period_id']; ?>" 
                               class="btn btn-info btn-sm" style="background:#17a2b8; color:white; text-decoration:none;">
                                📄 Laporan Lengkap
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>