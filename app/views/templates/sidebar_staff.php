<nav class="app-sidebar">
    <div class="sidebar-header">
        <div class="brand-logo">📦 Gudang</div>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo ($data['judul'] == 'Dashboard Staff') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>staff/dashboard">Dashboard</a>
            </li>
            
            <li><a href="#">Menu Transaksi</a>
                <ul class="submenu">
                    <li class="<?php echo (str_starts_with($data['judul'], 'Form Barang Masuk')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/barangMasuk">Input Barang Masuk</a>
                    </li>
                    <li class="<?php echo (str_starts_with($data['judul'], 'Form Barang Keluar')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/barangKeluar">Input Barang Keluar</a>
                    </li>
                    <li class="<?php echo (str_starts_with($data['judul'], 'Form Retur/Rusak')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/returBarang">Lapor Retur/Rusak</a>
                    </li>
                </ul>
            </li>

            <li><a href="#">Menu Operasional</a>
                <ul class="submenu">
                    <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Peminjaman')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/manajemenPeminjaman">Manajemen Peminjaman</a>
                    </li>
                    <li class="<?php echo (str_starts_with($data['judul'], 'Input Stock Opname')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/inputOpname">Input Stock Opname</a>
                    </li>
                </ul>
            </li>

            <li><a href="#">Lihat Data</a>
                <ul class="submenu">
                    <li class="<?php echo ($data['judul'] == 'Cek Stok Barang') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/viewStok">Cek Stok Barang</a>
                    </li>
                    <li class="<?php echo ($data['judul'] == 'Cek Lokasi Barang') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/viewLokasi">Cek Lokasi Barang</a>
                    </li>
                    <li class="<?php echo ($data['judul'] == 'Riwayat Input Saya') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/riwayatSaya">Riwayat Input Saya</a>
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
        <div class="page-title">
            <?php echo $data['judul']; ?> 
        </div>
        <div class="user-profile">
            <span>Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong> (Staff)</span>
            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn-logout" onclick="return confirm('Yakin ingin keluar?');">Logout</a>
        </div>
    </header>