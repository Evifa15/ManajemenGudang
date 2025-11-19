<?php

class PemilikController extends Controller {

    /**
     * "Benteng" Keamanan (Gatekeeper)
     * Constructor ini akan otomatis berjalan SETIAP KALI PemilikController dipanggil.
     */
    public function __construct() {
        // 1. GATEKEEPER: Pastikan sudah login
        // (index.php sudah memulai session, jadi kita tinggal cek)
        if (!isset($_SESSION['is_logged_in'])) {
            // Jika belum login, tendang ke halaman login
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        // 2. GATEKEEPER: Pastikan role-nya 'pemilik'
        if ($_SESSION['role'] != 'pemilik') {
            // Jika sudah login TAPI BUKAN pemilik, tendang juga
            $_SESSION['flash_message'] = ['text' => 'Akses ditolak.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
        
        // 3. GATEKEEPER: Pastikan status 'aktif' (sudah ganti password)
        if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == 'baru') {
             header('Location: ' . BASE_URL . 'auth/forceChangePassword');
            exit;
        }
    }

    /**
     * Method default (dipanggil oleh /pemilik/dashboard atau /pemilik)
     */
    public function index() {
        // Jika URL-nya hanya /pemilik, kita arahkan ke /pemilik/dashboard
        $this->dashboard();
    }

    /**
     * Menampilkan Halaman Dashboard Pemilik
     * (Versi LENGKAP dengan data)
     */
    public function dashboard() {
        
        // Panggil semua model yang kita butuhkan
        $productModel = $this->model('Product_model');
        $loanModel = $this->model('Loan_model');
        $transModel = $this->model('Transaction_model');
        $absensiModel = $this->model('Absensi_model');
        $auditModel = $this->model('Audit_model');

        // 1. Ambil data untuk Widget Peringatan
        $stokMenipis = $productModel->getJumlahStokMenipis();
        $jatuhTempo = $loanModel->getJumlahJatuhTempo();
        $rusakBulanIni = $transModel->getJumlahRusakBulanIni();

        // 2. Ambil data untuk Widget KPI
        $keluarHariIni = $transModel->getJumlahTransaksiHariIni('keluar');
        $hadirHariIni = $absensiModel->getJumlahStafHadirHariIni();
        
        // 3. Ambil data untuk Grafik (dan format untuk Chart.js)
        $grafikData = $transModel->getGrafikBulanan();
        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];
        foreach ($grafikData as $row) {
            $labels[] = $row['bulan'];
            $dataMasuk[] = $row['total_masuk'];
            $dataKeluar[] = $row['total_keluar'];
        }

        // 4. Ambil data untuk Widget Pengawasan Cepat
        $stafHadir = $absensiModel->getStafHadirSaatIni();
        $logTerbaru = $auditModel->getLogTerbaru(5);

        // 5. Siapkan semua data untuk dikirim ke View
        $data = [
            'judul' => 'Dashboard Pemilik',
            'widget_peringatan' => [
                'stok_menipis' => $stokMenipis,
                'jatuh_tempo' => $jatuhTempo,
                'barang_rusak' => $rusakBulanIni
            ],
            'widget_kpi' => [
                'keluar_hari_ini' => $keluarHariIni,
                'hadir_hari_ini' => $hadirHariIni
            ],
            'grafik' => [
                'labels' => json_encode($labels),
                'dataMasuk' => json_encode($dataMasuk),
                'dataKeluar' => json_encode($dataKeluar)
            ],
            'widget_pengawasan' => [
                'staf_hadir' => $stafHadir,
                'log_terbaru' => $logTerbaru
            ]
        ];
        
        $this->view('pemilik/dashboard', $data);
    }
    /**
     * Menampilkan Halaman Laporan Stok Akhir (Read-Only)
     */
    public function laporanStok($page = 1) {
        // 1. Ambil filter (mirip Admin/Staff)
        $search = $_GET['search'] ?? '';
        $kategori = $_GET['kategori'] ?? '';
        $merek = $_GET['merek'] ?? '';
        $lokasi = $_GET['lokasi'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang SUDAH ADA
        $productModel = $this->model('Product_model');
        
        // 3. Hitung Total Data (Kita akan ambil semua status)
        $totalProducts = $productModel->getTotalProductCount($search, $kategori, $merek, '', '', $lokasi);
        $totalPages = ceil($totalProducts / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedProducts = $productModel->getProductsPaginated($limit, $offset, $search, $kategori, $merek, '', '', $lokasi);

        // 5. Siapkan data untuk view
        $data = [
            'judul' => 'Laporan Stok Akhir',
            'products' => $paginatedProducts,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'kategori_filter' => $kategori,
            'merek_filter' => $merek,
            'lokasi_filter' => $lokasi,
            // Ambil data untuk dropdown filter
            'allKategori' => $this->model('Kategori_model')->getAllKategori(),
            'allMerek' => $this->model('Merek_model')->getAllMerek(),
            'allLokasi' => $this->model('Lokasi_model')->getAllLokasi()
        ];
        
        // 6. Panggil view baru (yang akan kita buat)
        $this->view('pemilik/report_stok', $data);
    }
    /**
     * Menampilkan Halaman Laporan Transaksi (dengan TAB)
     */
    public function laporanTransaksi() {
        // Panggil model yang relevan
        $transModel = $this->model('Transaction_model');
        
        // Kita ambil 50 data terakhir untuk setiap tab
        $limit = 50;
        $offset = 0;
        $search = ''; // Pemilik bisa melihat semua

        $data = [
            'judul' => 'Laporan Transaksi',
            
            // Ambil data untuk setiap TAB
            'riwayat_masuk' => $transModel->getRiwayatMasukPaginated($limit, $offset, $search),
            'riwayat_keluar' => $transModel->getRiwayatKeluarPaginated($limit, $offset, $search),
            'riwayat_rusak' => $transModel->getRiwayatReturPaginated($limit, $offset, $search),
            
            // (Kita akan tambahkan data untuk TAB Analitik nanti)
            'grafik_data' => [] 
        ];
        
        // Panggil view baru (yang akan kita buat)
        $this->view('pemilik/report_transaksi', $data);
    }
    /**
     * Menampilkan Halaman Laporan Peminjaman (Read-Only)
     */
    public function laporanPeminjaman($page = 1) {
        // 1. Ambil filter
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? ''; // Filter per Status
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang SUDAH ADA
        $loanModel = $this->model('Loan_model');
        
        // 3. Gunakan method yang SAMA dengan Admin
        $totalHistory = $loanModel->getTotalRiwayatPeminjamanCount($search, $status);
        $totalPages = ceil($totalHistory / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedHistory = $loanModel->getRiwayatPeminjamanPaginated($limit, $offset, $search, $status);

        // 4. Siapkan data untuk view
        $data = [
            'judul' => 'Laporan Peminjaman',
            'history' => $paginatedHistory,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'status_filter' => $status
        ];
        
        // 6. Panggil view baru (yang akan kita buat)
        $this->view('pemilik/report_peminjaman', $data);
    }
    /**
     * Menampilkan Halaman Rekap Absensi (Read-Only)
     */
    public function rekapAbsensi($page = 1) {
        // 1. Ambil filter
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'month' => $_GET['month'] ?? date('m'), // Default bulan ini
            'year' => $_GET['year'] ?? date('Y')   // Default tahun ini
        ];
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang baru kita buat
        $absensiModel = $this->model('Absensi_model');
        
        // 3. Hitung Total Data
        $totalAbsensi = $absensiModel->getTotalAbsensiCount($filters);
        $totalPages = ceil($totalAbsensi / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedAbsensi = $absensiModel->getAbsensiPaginated($limit, $offset, $filters);

        // 5. Ambil data untuk dropdown filter (Staff & Admin)
        $userModel = $this->model('User_model');
        $staff = $userModel->getUsersByRole('staff');
        $admin = $userModel->getUsersByRole('admin');
        $allKaryawan = array_merge($admin, $staff); // Gabungkan

        // 6. Siapkan data untuk view
        $data = [
            'judul' => 'Rekap Absensi',
            'absensi' => $paginatedAbsensi,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'filters' => $filters,
            'allKaryawan' => $allKaryawan
        ];
        
        // 7. Panggil view baru
        $this->view('pemilik/view_absensi', $data);
    }
    /**
     * Menampilkan Halaman Audit Trail (Read-Only)
     */
    public function auditTrail($page = 1) {
        // 1. Ambil filter
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang baru kita buat
        $auditModel = $this->model('Audit_model');
        
        // 3. Hitung Total Data
        $totalLogs = $auditModel->getTotalAuditCount($filters);
        $totalPages = ceil($totalLogs / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedLogs = $auditModel->getAuditLogPaginated($limit, $offset, $filters);

        // 5. Ambil data untuk dropdown filter (Semua user)
        $userModel = $this->model('User_model');
        $allUsers = $userModel->getAllUsers(); // Kita butuh fungsi ini

        // 6. Siapkan data untuk view
        $data = [
            'judul' => 'Audit Trail',
            'logs' => $paginatedLogs,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'filters' => $filters,
            'allUsers' => $allUsers
        ];
        
        // 7. Panggil view baru
        $this->view('pemilik/view_audit_trail', $data);
    }
    /**
     * Menampilkan Halaman Lihat Daftar Barang (Read-Only)
     */
    public function viewBarang($page = 1) {
        // 1. Ambil filter (Sama seperti Laporan Stok)
        $search = $_GET['search'] ?? '';
        $kategori = $_GET['kategori'] ?? '';
        $merek = $_GET['merek'] ?? '';
        $lokasi = $_GET['lokasi'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang SUDAH ADA
        $productModel = $this->model('Product_model');
        
        // 3. Hitung Total Data
        $totalProducts = $productModel->getTotalProductCount($search, $kategori, $merek, '', '', $lokasi);
        $totalPages = ceil($totalProducts / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedProducts = $productModel->getProductsPaginated($limit, $offset, $search, $kategori, $merek, '', '', $lokasi);

        // 5. Siapkan data untuk view
        $data = [
            'judul' => 'Daftar Barang',
            'products' => $paginatedProducts,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'kategori_filter' => $kategori,
            'merek_filter' => $merek,
            'lokasi_filter' => $lokasi,
            // Ambil data untuk dropdown filter
            'allKategori' => $this->model('Kategori_model')->getAllKategori(),
            'allMerek' => $this->model('Merek_model')->getAllMerek(),
            'allLokasi' => $this->model('Lokasi_model')->getAllLokasi()
        ];
        
        // 6. Panggil view baru (yang akan kita buat)
        $this->view('pemilik/view_barang', $data);
    }
    /**
     * Menampilkan Halaman Lihat Daftar Supplier (Read-Only)
     */
    public function viewSuppliers($page = 1) {
        // 1. Ambil filter
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang SUDAH ADA
        $supplierModel = $this->model('Supplier_model');
        
        // 3. Gunakan method yang SAMA dengan Admin
        $totalSuppliers = $supplierModel->getTotalSupplierCount($search);
        $totalPages = ceil($totalSuppliers / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedSuppliers = $supplierModel->getSuppliersPaginated($limit, $offset, $search);

        // 5. Siapkan data untuk view
        $data = [
            'judul' => 'Daftar Supplier',
            'suppliers' => $paginatedSuppliers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        // 6. Panggil view baru (yang akan kita buat)
        $this->view('pemilik/view_suppliers', $data);
    }
    /**
     * Menampilkan Halaman Riwayat Transaksi (Log Mentah, Read-Only)
     */
    public function viewRiwayat() {
        // Panggil model yang relevan
        $transModel = $this->model('Transaction_model');
        $loanModel = $this->model('Loan_model');
        
        // Kita ambil 50 data terakhir untuk setiap tab
        $limit = 50;
        $offset = 0;
        $search = ''; // Pemilik bisa melihat semua
        $status = ''; // Semua status

        $data = [
            'judul' => 'Riwayat Transaksi',
            
            // Ambil data untuk setiap TAB
            'riwayat_masuk' => $transModel->getRiwayatMasukPaginated($limit, $offset, $search),
            'riwayat_keluar' => $transModel->getRiwayatKeluarPaginated($limit, $offset, $search),
            'riwayat_rusak' => $transModel->getRiwayatReturPaginated($limit, $offset, $search),
            'riwayat_peminjaman' => $loanModel->getRiwayatPeminjamanPaginated($limit, $offset, $search, $status)
        ];
        
        // Panggil view baru
        $this->view('pemilik/view_riwayat', $data);
    }
}