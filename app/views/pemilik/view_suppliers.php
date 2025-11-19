<?php
    // 1. Panggil Header
    require_once APPROOT . '/views/templates/header.php';
    // 2. Panggil Sidebar KHUSUS PEMILIK
    require_once APPROOT . '/views/templates/sidebar_pemilik.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Lihat Daftar Supplier (Read-Only)</h1>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>pemilik/viewSuppliers" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Nama, Kontak, atau Email..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Nama Supplier</th>
                    <th>Kontak Person</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Loop data suppliers
                    foreach ($data['suppliers'] as $supplier) : 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($supplier['nama_supplier']); ?></td>
                    <td><?php echo htmlspecialchars($supplier['kontak_person']); ?></td>
                    <td><?php echo htmlspecialchars($supplier['telepon']); ?></td>
                    <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                    <td><?php echo htmlspecialchars($supplier['alamat']); ?></td>
                    </tr>
                <?php 
                    endforeach; 
                ?>
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