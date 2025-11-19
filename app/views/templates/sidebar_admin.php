<nav class="app-sidebar">
    <ul>
        <li class="<?php echo ($data['judul'] == 'Dashboard Admin') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>admin/dashboard">Dashboard</a>
        </li>
        
        <li><a href="#">Menu Master Data</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Barang') || str_starts_with($data['judul'], 'Tambah Barang')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/barang">Manajemen Barang</a>
                </li>
                <li class="<?php echo ($data['judul'] == 'Manajemen Supplier' || $data['judul'] == 'Tambah Supplier' || $data['judul'] == 'Edit Supplier') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/suppliers">Manajemen Supplier</a>
                </li>
                <li class="<?php echo ($data['judul'] == 'Manajemen Lokasi' || $data['judul'] == 'Tambah Lokasi' || $data['judul'] == 'Edit Lokasi') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/lokasi">Manajemen Lokasi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Kategori') || str_starts_with($data['judul'], 'Tambah Kategori') || str_starts_with($data['judul'], 'Edit Kategori')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/kategori">Manajemen Kategori</a>
                </li> 
                <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Merek') || str_starts_with($data['judul'], 'Tambah Merek') || str_starts_with($data['judul'], 'Edit Merek')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/merek">Manajemen Merek</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Satuan') || str_starts_with($data['judul'], 'Tambah Satuan') || str_starts_with($data['judul'], 'Edit Satuan')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/satuan">Manajemen Satuan</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Status') || str_starts_with($data['judul'], 'Tambah Status') || str_starts_with($data['judul'], 'Edit Status')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/status">Manajemen Status</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Menu Transaksi</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Barang Masuk')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk">Riwayat Barang Masuk</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Barang Keluar')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatBarangKeluar">Riwayat Barang Keluar</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Retur/Rusak')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatReturRusak">Riwayat Retur/Rusak</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Peminjaman')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman">Riwayat Peminjaman</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Menu Operasi Kritis</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Stock Opname')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/stockOpname">Stock Opname</a>
                </li>
                </ul>
        </li>

        <li><a href="#">Administrasi Sistem</a>
            <ul class="submenu">
                <li class="<?php echo ($data['judul'] == 'Manajemen Pengguna') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/users">Manajemen Pengguna</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Backup & Restore')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/manageBackup">Backup & Restore</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Laporan & Pengawasan</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Stok Akhir')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/laporanStok">Laporan Stok Akhir</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Transaksi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/laporanTransaksi">Laporan Transaksi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Peminjaman')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/laporanPeminjaman">Laporan Peminjaman</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Rekap Absensi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/rekapAbsensi">Rekap Absensi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Audit Trail')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/auditTrail">Audit Trail</a>
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