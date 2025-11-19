<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_peminjam.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Katalog Barang yang Bisa Dipinjam</h1>
    </div>

    <div class="dashboard-widgets">
        <?php if (empty($data['products'])): ?>
             <div style="grid-column: span 3; text-align: center; padding: 30px; background: #fff;">
                <p>Tidak ada barang yang saat ini ditandai sebagai 'Bisa Dipinjam'.</p>
            </div>
        <?php endif; ?>
        
        <?php foreach($data['products'] as $product): ?>
        <div class="widget product-card" style="border-left: 5px solid #007bff;">
            <h4><?php echo htmlspecialchars($product['nama_barang']); ?></h4>
            <p style="color: #666; margin-top: 5px;"><?php echo htmlspecialchars($product['nama_kategori']); ?></p>
            <p style="font-size: 0.9em; margin-bottom: 15px;"><?php echo htmlspecialchars($product['deskripsi']); ?></p>
            
            <a href="<?php echo BASE_URL; ?>peminjam/ajukan/<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm">
                Ajukan Peminjaman
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>