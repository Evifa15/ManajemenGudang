<?php

class StaffController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['is_logged_in'])) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
        if ($_SESSION['role'] != 'staff') {
            $_SESSION['flash_message'] = ['text' => 'Anda tidak memiliki hak akses.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }       
    }

    /**
     * Halaman Dashboard (Beranda) untuk Staff
     */
    public function index() {
        $this->dashboard();
    }
    
    public function dashboard() {
    // Panggil model absensi
    $absensiModel = $this->model('Absensi_model');
    $todayAttendance = $absensiModel->getTodayAttendance($_SESSION['user_id']);

    $data = [
        'judul' => 'Dashboard Staff',
        'today_attendance' => $todayAttendance // <-- Kirim data absensi ke view
        // (Kita bisa tambahkan widget notifikasi peminjaman/opname di sini nanti)
    ];
    $this->view('staff/dashboard', $data);
}

    /* --- MEMPROSES INPUT TIDAK HADIR (SAKIT/IZIN) --- */
    public function processAbsenTidakHadir() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'staff/dashboard');
            exit;
        }
        $absensiModel = $this->model('Absensi_model');
        $today = $absensiModel->getTodayAttendance($_SESSION['user_id']);
        if (!$today) { 
            $buktiNama = null; 
            if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES['bukti_foto'];
                $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                if (in_array($fileExt, $allowed)) {
                    if ($file['size'] <= 2000000) {
                        $buktiNama = "izin_" . $_SESSION['user_id'] . "_" . time() . "." . $fileExt;
                        $destination = APPROOT . '/../public/uploads/bukti_absen/' . $buktiNama;
                        if (!move_uploaded_file($file['tmp_name'], $destination)) {
                            $_SESSION['flash_message'] = ['text' => 'Gagal mengupload bukti foto.', 'type' => 'error'];
                            header('Location: ' . BASE_URL . 'staff/dashboard');
                            exit;
                        }
                    } else {
                        $_SESSION['flash_message'] = ['text' => 'Ukuran file terlalu besar (Maks 2MB).', 'type' => 'error'];
                        header('Location: ' . BASE_URL . 'staff/dashboard');
                        exit;
                    }
                } else {
                    $_SESSION['flash_message'] = ['text' => 'Format file tidak didukung (Hanya JPG, PNG, PDF).', 'type' => 'error'];
                    header('Location: ' . BASE_URL . 'staff/dashboard');
                    exit;
                }
            }
            $data = [
                'user_id'    => $_SESSION['user_id'],
                'status'     => $_POST['status'],      
                'keterangan' => $_POST['keterangan'],
                'bukti_foto' => $buktiNama 
            ];
            if ($absensiModel->addIzinSakit($data)) {
                $_SESSION['flash_message'] = ['text' => 'Status kehadiran berhasil dicatat.', 'type' => 'success'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal mencatat data ke database.', 'type' => 'error'];
            }
        } else {
            $_SESSION['flash_message'] = ['text' => 'Anda sudah mengisi absensi hari ini.', 'type' => 'error'];
        }
        header('Location: ' . BASE_URL . 'staff/dashboard');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK TRANSAKSI BARANG MASUK
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman form input barang masuk
     */
    public function barangMasuk() {
        
        // Kita butuh data dari 4 model untuk mengisi dropdown
        $data = [
            'judul' => 'Form Barang Masuk',
            // Kita gunakan method 'getAll' yang sudah kita buat
            'products' => $this->model('Product_model')->getAllProductsList(),
            'suppliers' => $this->model('Supplier_model')->getAllSuppliers(),
            'lokasi' => $this->model('Lokasi_model')->getAllLokasi(),
            'status' => $this->model('Status_model')->getAllStatus()
        ];
        
        $this->view('staff/form_barang_masuk', $data);
    }

    /**
     * Memproses data dari form barang masuk
     */
    public function processBarangMasuk() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'staff/barangMasuk');
            exit;
        }

        // --- 1. Penanganan File Upload ---
        $buktiFotoNama = null;
        if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == UPLOAD_ERR_OK) {
            
            $file = $_FILES['bukti_foto'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array($fileExt, $allowed)) {
                if ($fileSize < 5000000) { // Maks 5MB
                    $buktiFotoNama = "bukti_" . time() . "." . $fileExt;
                    // Path absolut untuk memindahkan file
                    $fileDestination = APPROOT . '/../public/uploads/bukti_transaksi/' . $buktiFotoNama;
                    
                    if (!move_uploaded_file($fileTmpName, $fileDestination)) {
                         $_SESSION['flash_message'] = ['text' => 'Gagal memindahkan file yang diupload.', 'type' => 'error'];
                         header('Location: ' . BASE_URL . 'staff/barangMasuk');
                         exit;
                    }
                } else {
                    $_SESSION['flash_message'] = ['text' => 'Gagal: Ukuran file terlalu besar (Maks 5MB).', 'type' => 'error'];
                    header('Location: ' . BASE_URL . 'staff/barangMasuk');
                    exit;
                }
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal: Tipe file tidak diizinkan (Hanya JPG, PNG, PDF).', 'type' => 'error'];
                header('Location: ' . BASE_URL . 'staff/barangMasuk');
                exit;
            }
        }

        // --- 2. Kumpulkan Data Form ---
        $data = [
            'product_id'   => $_POST['product_id'],
            'user_id'      => $_SESSION['user_id'], // ID Staff yang sedang login
            'jumlah'       => (int)$_POST['jumlah'],
            'supplier_id'  => $_POST['supplier_id'],
            'lokasi_id'    => $_POST['lokasi_id'],
            'status_id'    => $_POST['status_id'],
            'lot_number'   => !empty($_POST['lot_number']) ? $_POST['lot_number'] : null,
            'exp_date'     => !empty($_POST['exp_date']) ? $_POST['exp_date'] : null,
            'keterangan'   => $_POST['keterangan'],
            'bukti_foto'   => $buktiFotoNama // Nama file yang sudah di-upload
        ];

        // --- 3. Kirim ke Model ---
        $transactionModel = $this->model('Transaction_model');

        try {
            // Panggil method addBarangMasuk yang sudah kita buat
            $transactionModel->addBarangMasuk($data);
            $_SESSION['flash_message'] = ['text' => 'Barang masuk berhasil dicatat.', 'type' => 'success'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Gagal mencatat barang masuk: ' . $e->getMessage(), 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'staff/barangMasuk');
        exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK TRANSAKSI BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman form input barang keluar
     */
    public function barangKeluar() {
        $data = [
            'judul' => 'Form Barang Keluar',
            // Hanya ambil daftar produk untuk dropdown awal
            'products' => $this->model('Product_model')->getAllProductsList()
        ];
        
        $this->view('staff/form_barang_keluar', $data);
    }

    /**
     * [AJAX] Mengambil info stok tersedia untuk satu produk.
     * Dipanggil oleh JavaScript di form barang keluar.
     * @param int $productId
     */
    public function getStockInfo($productId) {
        // Ini adalah endpoint API, jadi kita akan kirim JSON
        header('Content-Type: application/json');
        
        $productModel = $this->model('Product_model');
        $stockData = $productModel->getAvailableStockForProduct($productId);
        
        // Kirim data sebagai JSON
        echo json_encode($stockData);
        exit; // Hentikan eksekusi skrip
    }

    /**
     * Memproses data dari form barang keluar
     */
    public function processBarangKeluar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'staff/barangKeluar');
            exit;
        }

        // 1. Kumpulkan Data Form
        $data = [
            'product_id'   => $_POST['product_id'],
            'stock_id'     => $_POST['stock_id'], // Ini adalah ID stok spesifik (lot/lokasi)
            'jumlah'       => (int)$_POST['jumlah'],
            'keterangan'   => $_POST['keterangan'],
            'user_id'      => $_SESSION['user_id'] // ID Staff
        ];

        // 2. Validasi Sederhana
        if (empty($data['stock_id']) || $data['jumlah'] <= 0) {
            $_SESSION['flash_message'] = ['text' => 'Gagal: Harap pilih barang dan lot/lokasi yang valid, dan masukkan jumlah > 0.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'staff/barangKeluar');
            exit;
        }

        // 3. Kirim ke Model
        $transactionModel = $this->model('Transaction_model');
        
        try {
            $transactionModel->addBarangKeluar($data);
            $_SESSION['flash_message'] = ['text' => 'Barang keluar berhasil dicatat.', 'type' => 'success'];
        } catch (Exception $e) {
            // Tangkap pesan error dari Model (misal: "Stok tidak mencukupi!")
            $_SESSION['flash_message'] = ['text' => 'Gagal: ' . $e->getMessage(), 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'staff/barangKeluar');
        exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK TRANSAKSI RETUR / BARANG RUSAK
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman form input retur/rusak
     */
    public function returBarang() {
        // Ambil data untuk dropdown
        $statusModel = $this->model('Status_model');
        
        $data = [
            'judul' => 'Form Retur/Rusak',
            // Ambil daftar produk
            'products' => $this->model('Product_model')->getAllProductsList(),
            // Ambil SEMUA status (Tersedia, Rusak, Karantina, dll)
            'status' => $statusModel->getAllStatus()
        ];
        
        $this->view('staff/form_retur_rusak', $data);
    }

    /**
     * Memproses data dari form retur/rusak
     */
    public function processReturBarang() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'staff/returBarang');
            exit;
        }

        // 1. Kumpulkan Data Form
        $data = [
            'product_id'        => $_POST['product_id'],
            'stock_id_asal'     => $_POST['stock_id'], // ID stok 'Tersedia' yang dipilih
            'jumlah'            => (int)$_POST['jumlah'],
            'status_id_tujuan'  => $_POST['status_id_tujuan'], // ID status baru (misal 'Rusak')
            'keterangan'        => $_POST['keterangan'], // Keterangan/Sumber kerusakan
            'exp_date'          => $_POST['exp_date'] ?? null,
            'user_id'           => $_SESSION['user_id'] // ID Staff
        ];

        // 2. Validasi Sederhana
        if (empty($data['stock_id_asal']) || empty($data['status_id_tujuan']) || $data['jumlah'] <= 0) {
            $_SESSION['flash_message'] = ['text' => 'Gagal: Harap lengkapi semua field yang wajib diisi.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'staff/returBarang');
            exit;
        }

        // 3. Kirim ke Model
        $transactionModel = $this->model('Transaction_model');
        
        try {
            $transactionModel->addBarangRetur($data);
            $_SESSION['flash_message'] = ['text' => 'Laporan barang rusak/retur berhasil dicatat.', 'type' => 'success'];
        } catch (Exception $e) {
            // Tangkap pesan error dari Model (misal: "Stok tidak mencukupi!")
            $_SESSION['flash_message'] = ['text' => 'Gagal: ' . $e->getMessage(), 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'staff/returBarang');
        exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN PEMINJAMAN
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman workflow manajemen peminjaman (4 Tab)
     */
    public function manajemenPeminjaman() {
        $loanModel = $this->model('Loan_model');
        
        // Ambil data untuk setiap tab
        $data = [
            'judul' => 'Manajemen Peminjaman',
            'permintaan_baru' => $loanModel->getPeminjamanByStatus(['Diajukan']),
            'disetujui'       => $loanModel->getPeminjamanByStatus(['Disetujui']),
            'sedang_dipinjam' => $loanModel->getPeminjamanByStatus(['Sedang Dipinjam', 'Jatuh Tempo']),
            'riwayat'         => $loanModel->getPeminjamanByStatus(['Selesai', 'Ditolak'])
        ];
        
        $this->view('staff/manage_peminjaman', $data);
    }

    /**
     * (AKSI) Staff menyetujui peminjaman
     */
    public function approveLoan($peminjaman_id) {
        $loanModel = $this->model('Loan_model');
        if ($loanModel->updateLoanStatus($peminjaman_id, 'Disetujui', $_SESSION['user_id'])) {
            $_SESSION['flash_message'] = ['text' => 'Peminjaman berhasil disetujui.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal memproses.', 'type' => 'error'];
        }
        header('Location: ' . BASE_URL . 'staff/manajemenPeminjaman');
        exit;
    }

    /**
     * (AKSI) Staff menyerahkan barang
     */
    public function handoverLoan($peminjaman_id) {
        $loanModel = $this->model('Loan_model');
        if ($loanModel->updateLoanStatus($peminjaman_id, 'Sedang Dipinjam', $_SESSION['user_id'])) {
            $_SESSION['flash_message'] = ['text' => 'Barang berhasil diserahkan.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal memproses.', 'type' => 'error'];
        }
        header('Location: ' . BASE_URL . 'staff/manajemenPeminjaman');
        exit;
    }

    /**
     * (AKSI) Staff menerima pengembalian barang
     */
    public function returnLoan($peminjaman_id) {
        $loanModel = $this->model('Loan_model');
        if ($loanModel->updateLoanStatus($peminjaman_id, 'Selesai', $_SESSION['user_id'])) {
            $_SESSION['flash_message'] = ['text' => 'Barang telah dikembalikan.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal memproses.', 'type' => 'error'];
        }
        header('Location: ' . BASE_URL . 'staff/manajemenPeminjaman');
        exit;
    }

    /**
     * (AKSI) Staff menolak peminjaman
     */
    public function rejectLoan() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $peminjaman_id = $_POST['peminjaman_id'];
            $alasan = $_POST['alasan_penolakan'];

            $loanModel = $this->model('Loan_model');
            if ($loanModel->updateLoanStatus($peminjaman_id, 'Ditolak', $_SESSION['user_id'], $alasan)) {
                $_SESSION['flash_message'] = ['text' => 'Peminjaman telah ditolak.', 'type' => 'success'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memproses.', 'type' => 'error'];
            }
        }
        header('Location: ' . BASE_URL . 'staff/manajemenPeminjaman');
        exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK OPERASI STOCK OPNAME (STAFF INPUT)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman form input stock opname (physical count)
     */
    public function inputOpname() {
        
        // ⬇️ --- PERBAIKAN LOGIKA INI --- ⬇️
        $opnameModel = $this->model('Opname_model');
        $activePeriod = $opnameModel->getActivePeriod();
        $isOpnameActive = $activePeriod ? true : false; 
        // ⬆️ --- AKHIR PERBAIKAN --- ⬆️

        $data = [
            'judul' => 'Input Stock Opname',
            'isOpnameActive' => $isOpnameActive,
            'activePeriodId' => $activePeriod['period_id'] ?? null, // Kirim ID periode ke view
            'products' => $this->model('Product_model')->getAllProductsList()
        ];
        
        $this->view('staff/input_stock_opname', $data);
    }

    /**
     * Memproses hasil hitungan fisik dari Staff
     */
    public function processInputOpname() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'staff/inputOpname');
            exit;
        }

        // 1. Kumpulkan Data Form
        $data = [
            'product_id'   => $_POST['product_id'],
            'stok_fisik'   => (int)$_POST['stok_fisik'],
            'lot_number'   => $_POST['lot_number'] ?? null,
            'catatan'      => $_POST['catatan'],
            'user_id'      => $_SESSION['user_id'],
            'period_id'    => $_POST['period_id'] // ⬇️ --- TAMBAHKAN INI --- ⬇️
        ];
        
        // 2. Kirim ke Model
        $opnameModel = $this->model('Opname_model');
        
        try {
            $opnameModel->createOpnameEntry($data);
            $_SESSION['flash_message'] = ['text' => 'Hitungan stok berhasil dicatat.', 'type' => 'success'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Gagal mencatat hitungan: ' . $e->getMessage(), 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'staff/inputOpname');
        exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MENU LIHAT DATA (READ-ONLY)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Lihat Stok (Read-Only)
     */
    public function viewStok($page = 1) {
        // 1. Ambil filter (mirip Admin)
        $search = $_GET['search'] ?? '';
        $kategori = $_GET['kategori'] ?? '';
        $merek = $_GET['merek'] ?? '';
        $lokasi = $_GET['lokasi'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 2. Panggil Model yang SAMA dengan Admin
        $productModel = $this->model('Product_model');
        
        // (Kita anggap status 'Tersedia' saja yang relevan untuk Staff)
        $statusTersedia = $this->model('Status_model')->getStatusIdByName('Tersedia');
        $status_id = $statusTersedia['status_id'] ?? 0;

        // 3. Hitung Total Data
        $totalProducts = $productModel->getTotalProductCount($search, $kategori, $merek, $status_id, '', $lokasi);
        $totalPages = ceil($totalProducts / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedProducts = $productModel->getProductsPaginated($limit, $offset, $search, $kategori, $merek, $status_id, '', $lokasi);

        // 5. Siapkan data untuk view
        $data = [
            'judul' => 'Cek Stok Barang',
            'products' => $paginatedProducts,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'kategori_filter' => $kategori,
            'merek_filter' => $merek,
            'lokasi_filter' => $lokasi,
            'allKategori' => $this->model('Kategori_model')->getAllKategori(),
            'allMerek' => $this->model('Merek_model')->getAllMerek(),
            'allLokasi' => $this->model('Lokasi_model')->getAllLokasi()
        ];
        
        // 6. Panggil view baru
        $this->view('staff/view_stok', $data);
    }
    /**
     * Menampilkan halaman Cek Lokasi (Read-Only)
     */
    public function viewLokasi() {
        $search = $_GET['search'] ?? '';
        $data = [
            'judul' => 'Cek Lokasi Barang',
            'search' => $search,
            'results' => [] // Defaultnya array kosong
        ];

        // Hanya jalankan pencarian jika ada input
        if (!empty($search)) {
            // Kita gunakan model produk yang sudah ada
            $productModel = $this->model('Product_model');
            // Kita panggil fungsi yang sudah ada (getStockInfo)
            // tapi kita harus ambil product_id dulu
            
            // (Logika ini bisa disederhanakan, tapi untuk sekarang kita cari berdasarkan nama)
            // Mari kita buat fungsi baru di Product_model
            $data['results'] = $productModel->findStockLocationsByName($search);
        }
        
        $this->view('staff/view_lokasi', $data);
    }
    /**
     * Menampilkan halaman Riwayat Input Saya (Read-Only)
     */
    public function riwayatSaya() {
        // Ambil ID Staff yang sedang login
        $staffId = $_SESSION['user_id'];
        
        // Panggil model
        $transModel = $this->model('Transaction_model');
        
        $data = [
            'judul' => 'Riwayat Input Saya',
            // Panggil 3 fungsi baru yang kita buat di model
            'riwayat_masuk' => $transModel->getRiwayatMasukByUserId($staffId),
            'riwayat_keluar' => $transModel->getRiwayatKeluarByUserId($staffId),
            'riwayat_rusak' => $transModel->getRiwayatReturByUserId($staffId)
        ];
        
        // Panggil view baru
        $this->view('staff/view_riwayat_saya', $data);
    }
    /**
 * [AKSI] Memproses Check-in
 */
public function processCheckIn() {
    $absensiModel = $this->model('Absensi_model');
    $today = $absensiModel->getTodayAttendance($_SESSION['user_id']);

    if (!$today) {
        $absensiModel->checkInUser($_SESSION['user_id']);
        $_SESSION['flash_message'] = ['text' => 'Check-in berhasil.', 'type' => 'success'];
    }
    header('Location: ' . BASE_URL . 'staff/dashboard');
    exit;
}

/**
 * [AKSI] Memproses Check-out
 */
public function processCheckOut() {
    $absensiModel = $this->model('Absensi_model');
    $today = $absensiModel->getTodayAttendance($_SESSION['user_id']);

    if ($today && $today['waktu_pulang'] == null) {
        $absensiModel->checkOutUser($today['absen_id']);
        $_SESSION['flash_message'] = ['text' => 'Check-out berhasil.', 'type' => 'success'];
    }
    header('Location: ' . BASE_URL . 'staff/dashboard');
    exit;
}

}