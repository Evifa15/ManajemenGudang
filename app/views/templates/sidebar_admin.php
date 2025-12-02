<?php
    // Helper sederhana untuk cek URL aktif
    // Ambil bagian terakhir dari URL (method name)
    $currentUrl = $_GET['url'] ?? 'admin/dashboard';
    $urlParts = explode('/', $currentUrl);
    $method = $urlParts[1] ?? 'dashboard'; 
?>

<nav class="app-sidebar">
    <div class="sidebar-header">
        <div class="brand-logo">📦 Gudang</div>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo ($method == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/dashboard">Dashboard</a>
            </li>
            
            <li class="<?php echo (in_array($method, ['barang', 'addBarang', 'editBarang', 'detailBarang'])) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/barang">Master Data Barang</a>
            </li>

            <?php 
                // Cek apakah halaman saat ini termasuk dalam grup transaksi
                $isTransaksiActive = in_array($method, ['riwayatBarangMasuk', 'detailBarangMasuk', 'riwayatBarangKeluar', 'riwayatReturRusak', 'riwayatPeminjaman']);
            ?>
            <li class="<?php echo $isTransaksiActive ? 'active' : ''; ?>">
                <a href="#">Menu Transaksi</a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'riwayatBarangMasuk' || $method == 'detailBarangMasuk') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk">Riwayat Masuk</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatBarangKeluar') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/riwayatBarangKeluar">Riwayat Keluar</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatReturRusak') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/riwayatReturRusak">Retur/Rusak</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatPeminjaman') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman">Peminjaman</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isOpnameActive = in_array($method, ['perintahOpname', 'riwayatOpname', 'detailRiwayatOpname']);
            ?>
            <li class="<?php echo $isOpnameActive ? 'active' : ''; ?>">
                <a href="#">Operasi Kritis</a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'perintahOpname') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/perintahOpname">Stock Opname</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatOpname' || $method == 'detailRiwayatOpname') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/riwayatOpname">Arsip Opname</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isAdminActive = in_array($method, ['users', 'addUser', 'editUser', 'masterDataConfig', 'addKategori', 'editKategori', 'manageBackup']);
            ?>
            <li class="<?php echo $isAdminActive ? 'active' : ''; ?>">
                <a href="#">Administrasi</a>
                <ul class="submenu">
                    <li class="<?php echo (str_contains($method, 'user')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/users">Manajemen User</a>
                    </li>
                    <li class="<?php echo ($method == 'masterDataConfig' || str_contains($method, 'Kategori') || str_contains($method, 'Satuan')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/masterDataConfig">Data Master Lain</a>
                    </li>
                    <li class="<?php echo ($method == 'manageBackup') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/manageBackup">Backup/Restore</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isLaporanActive = in_array($method, ['laporanStok', 'laporanTransaksi', 'rekapAbsensi', 'auditTrail']);
            ?>
            <li class="<?php echo $isLaporanActive ? 'active' : ''; ?>">
                <a href="#">Laporan</a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'laporanStok') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/laporanStok">Stok Akhir</a>
                    </li>
                    <li class="<?php echo ($method == 'laporanTransaksi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/laporanTransaksi">Arus Transaksi</a>
                    </li>
                    <li class="<?php echo ($method == 'rekapAbsensi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/rekapAbsensi">Absensi</a>
                    </li>
                    <li class="<?php echo ($method == 'auditTrail') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/auditTrail">Audit Trail</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="sidebar-profile-link">
        <a href="<?php echo BASE_URL; ?>profile/index">Profil Saya</a>
    </div>
</nav>

<div class="main-content">
    
    <header class="app-header">
        <div class="mobile-menu-toggle">
            ☰
        </div>
        
        <div class="page-title">
            <?php echo $data['judul']; ?> 
        </div>
        
        <a href="<?php echo BASE_URL; ?>auth/logout" class="btn-logout" onclick="return confirm('Yakin ingin keluar?');" title="Keluar Aplikasi">
            <span class="icon"></span> Logout
        </a>
    </header>
    
    <div class="app-content">