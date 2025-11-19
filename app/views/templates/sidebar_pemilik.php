<nav class="app-sidebar">
    <ul>
        <li class="<?php echo ($data['judul'] == 'Dashboard Pemilik') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pemilik/dashboard">Dashboard</a>
        </li>
        
        <li><a href="#">Menu Laporan</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Stok Akhir')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/laporanStok">Laporan Stok Akhir</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Transaksi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/laporanTransaksi">Laporan Transaksi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Peminjaman')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/laporanPeminjaman">Laporan Peminjaman</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Menu Pengawasan</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Rekap Absensi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/rekapAbsensi">Rekap Absensi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Audit Trail')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/auditTrail">Audit Trail</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Lihat Data (Read-Only)</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Daftar Barang')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/viewBarang">Lihat Daftar Barang</a>
                </li>
                 <li class="<?php echo (str_starts_with($data['judul'], 'Daftar Supplier')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/viewSuppliers">Lihat Daftar Supplier</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Transaksi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pemilik/viewRiwayat">Lihat Riwayat Transaksi</a>
                </li>
            </ul>
        </li>
        
    </ul>
    
    <div class="sidebar-profile-link">
        <a href="<?php echo BASE_URL; ?>profile/index" 
           class="<?php echo (str_starts_with($data['judul'], 'Profil Saya')) ? 'active' : ''; ?>">
            Profil Saya
        </a>
    </div>
</nav>