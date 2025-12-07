<?php
    // Helper untuk cek URL aktif
    $currentUrl = $_GET['url'] ?? 'pemilik/dashboard';
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
                <a href="<?php echo BASE_URL; ?>pemilik/dashboard">
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php 
                $isLaporanActive = in_array($method, ['laporanStok', 'laporanTransaksi', 'laporanPeminjaman']); 
            ?>
            <li class="<?php echo $isLaporanActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Menu Laporan</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'laporanStok') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/laporanStok">Laporan Stok Akhir</a>
                    </li>
                    <li class="<?php echo ($method == 'laporanTransaksi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/laporanTransaksi">Laporan Transaksi</a>
                    </li>
                    <li class="<?php echo ($method == 'laporanPeminjaman') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/laporanPeminjaman">Laporan Peminjaman</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isPengawasanActive = in_array($method, ['rekapAbsensi', 'auditTrail']); 
            ?>
            <li class="<?php echo $isPengawasanActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Menu Pengawasan</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'rekapAbsensi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/rekapAbsensi">Rekap Absensi</a>
                    </li>
                    <li class="<?php echo ($method == 'auditTrail') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/auditTrail">Audit Trail</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isMasterActive = in_array($method, ['viewBarang', 'viewSuppliers', 'viewRiwayat']); 
            ?>
            <li class="<?php echo $isMasterActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Lihat Data Master</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'viewBarang') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/viewBarang">Daftar Barang</a>
                    </li>
                     <li class="<?php echo ($method == 'viewSuppliers') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/viewSuppliers">Daftar Supplier</a>
                    </li>
                    <li class="<?php echo ($method == 'viewRiwayat') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>pemilik/viewRiwayat">Log Riwayat Transaksi</a>
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

            <span style="font-size: 0.9rem; color: #64748b; margin-right: 5px;" class="no-mobile">
                Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>
            </span>

            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn btn-outline-danger" 
               style="border-color: #ffcccc; color: #d63384; background: #fff0f6; border-radius: 8px; padding: 8px 15px; font-weight: 600; height: 38px; display: inline-flex; align-items: center; text-decoration: none;"
               onclick="return confirm('Yakin ingin keluar?');">
                Logout
            </a>
        </div>
    </header>
    
    <div class="app-content">