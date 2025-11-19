<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_peminjam.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Dashboard Peminjam</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="dashboard-widgets">
        
        <div class="widget widget-statistik" style="grid-column: span 3; text-align: center;">
            <h3>Barang yang Sedang Dipinjam</h3>
        </div>

        <?php 
        $activeLoans = array_filter($data['history'], function($h) {
            return $h['status_pinjam'] == 'Sedang Dipinjam' || $h['status_pinjam'] == 'Jatuh Tempo';
        });

        if (empty($activeLoans)): ?>
            <div style="grid-column: span 3; text-align: center; padding: 30px; background: #fff;">
                <p>Saat ini Anda tidak sedang meminjam barang.</p>
            </div>
        <?php else: ?>
            <?php foreach($activeLoans as $loan): 
                $isDue = $loan['status_pinjam'] == 'Jatuh Tempo';
            ?>
            <div class="widget" style="border-left: 5px solid <?php echo $isDue ? '#dc3545' : '#ffc107'; ?>;">
                <h4><?php echo htmlspecialchars($loan['nama_barang']); ?></h4>
                <p style="font-size: 0.9em; margin-top: 5px;">Status: 
                    <span style="color: <?php echo $isDue ? 'red' : 'orange'; ?>; font-weight: bold;">
                        <?php echo htmlspecialchars($loan['status_pinjam']); ?>
                    </span>
                </p>
                <p style="font-size: 0.9em;">Rencana Kembali: <?php echo date('d-M-Y', strtotime($loan['tgl_rencana_kembali'])); ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
    
    <div class="form-actions" style="margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>peminjam/katalog" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1em;">
            Lihat Katalog & Ajukan Peminjaman Baru
        </a>
    </div>

</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>