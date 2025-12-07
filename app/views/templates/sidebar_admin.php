<?php
    // Helper sederhana untuk cek URL aktif
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
                <a href="<?php echo BASE_URL; ?>admin/dashboard">
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="<?php echo (in_array($method, ['barang', 'addBarang', 'editBarang', 'detailBarang'])) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/barang">
                    <span>Data Barang</span>
                </a>
            </li>

            <?php $isTransaksiActive = in_array($method, ['riwayatBarangMasuk', 'detailBarangMasuk', 'riwayatBarangKeluar', 'riwayatReturRusak', 'riwayatPeminjaman']); ?>
            <li class="<?php echo $isTransaksiActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);"> <span>Transaksi Barang</span>
                    <i class="ph ph-caret-down arrow-icon"></i> </a>
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

            <?php $isOpnameActive = in_array($method, ['perintahOpname', 'riwayatOpname', 'detailRiwayatOpname']); ?>
            <li class="<?php echo $isOpnameActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Operasi Kritis</span>
                    <i class="ph ph-caret-down arrow-icon"></i> </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'perintahOpname') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/perintahOpname">Stock Opname</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatOpname' || $method == 'detailRiwayatOpname') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/riwayatOpname">Arsip Opname</a>
                    </li>
                </ul>
            </li>

            <?php $isUserActive = (str_contains($method, 'user') || $method == 'rekapAbsensi'); ?>
            <li class="<?php echo $isUserActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Data Pengguna</span>
                    <i class="ph ph-caret-down arrow-icon"></i> </a>
                <ul class="submenu">
                    <li class="<?php echo (str_contains($method, 'user')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/users">Manajemen Pengguna</a>
                    </li>
                    <li class="<?php echo ($method == 'rekapAbsensi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/rekapAbsensi">Manajemen Absensi</a>
                    </li>
                </ul>
            </li>

            <?php $isLaporanActive = in_array($method, ['laporanStok', 'laporanTransaksi', 'auditTrail']); ?>
            <li class="<?php echo $isLaporanActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Laporan</span>
                    <i class="ph ph-caret-down arrow-icon"></i> </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'laporanStok') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/laporanStok">Stok Akhir</a>
                    </li>
                    <li class="<?php echo ($method == 'laporanTransaksi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>admin/laporanTransaksi">Arus Transaksi</a>
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
        <div class="app-header-actions" style="display: flex; align-items: center; gap: 10px;">

            <?php if (isset($data['back_button'])): ?>
                <a href="<?php echo $data['back_button']['url']; ?>" 
                   class="btn btn-sm" 
                   style="background-color: #152e4d; color: white; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; font-weight: 600; border-radius: 8px; padding: 8px 15px; height: 38px; border: 1px solid #152e4d; transition: all 0.2s;">
                    <i class="ph ph-arrow-left" style="font-weight: bold;"></i> 
                    <?php echo $data['back_button']['label']; ?>
                </a>
            <?php endif; ?>

            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn btn-outline-danger" 
               style="border-color: #ffcccc; color: #d63384; background: #fff0f6; border-radius: 8px; padding: 8px 15px; font-weight: 600; height: 38px; display: inline-flex; align-items: center; text-decoration: none;">
                Logout
            </a>

        </div>
    </header>
    
    <div class="app-content">