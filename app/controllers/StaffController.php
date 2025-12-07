<?php

class StaffController extends Controller {
    public function __construct() {
        // Cek apakah permintaan ini adalah permintaan AJAX (agar tidak redirect ke HTML)
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!isset($_SESSION['is_logged_in'])) {
            if ($isAjax) {
                // 🔥 FIX: Kirim status 401 dan pesan JSON yang bersih, JANGAN REDIRECT
                header('Content-Type: application/json');
                http_response_code(401); 
                echo json_encode(['error' => 'Unauthorized: Session Expired', 'redirect' => BASE_URL . 'auth/index']);
                exit;
            } else {
                // Jika bukan AJAX, lakukan redirect normal (untuk akses langsung URL)
                header('Location: ' . BASE_URL . 'auth/index');
                exit;
            }
        }
        
        // Cek Role (Jika sesi ada, tapi bukan Staff)
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
        $absensiModel = $this->model('Absensi_model');
        $todayAttendance = $absensiModel->getTodayAttendance($_SESSION['user_id']);

        $data = [
            'judul' => 'Dashboard Staff',
            'today_attendance' => $todayAttendance 
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
   public function barangMasuk()
    {
        $data['judul'] = 'Barang Masuk';
        $data['products'] = $this->model('Product_model')->getAllProductsList(); 
        $data['suppliers'] = $this->model('Supplier_model')->getAllSuppliers();
        $data['lokasi'] = $this->model('Lokasi_model')->getAllLokasi();
        $data['status'] = $this->model('Status_model')->getAllStatus();
        $data['satuan'] = $this->model('Satuan_model')->getAllSatuan(); 
        
        $this->view('staff/form_barang_masuk', $data);
    }

    /**
     * Memproses data dari form barang masuk (STRICT MODE + MULTI UPLOAD)
     */
    public function processBarangMasuk() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'staff/barangMasuk');
            exit;
        }

        // --- 1. Validasi Wajib Diisi (Server Side) ---
        if (empty($_POST['lot_number']) || empty($_POST['production_date']) || empty($_POST['exp_date'])) {
            $_SESSION['flash_message'] = ['text' => 'Gagal: Nomor Batch, Tanggal Produksi, dan Expired Date WAJIB diisi!', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'staff/barangMasuk');
            exit;
        }

        // --- 2. Penanganan File Upload (MULTI-FILE) ---
        $uploadedFiles = [];
        
        if (isset($_FILES['bukti_foto']) && !empty($_FILES['bukti_foto']['name'][0])) {
            $files = $_FILES['bukti_foto'];
            $count = count($files['name']); 

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] == UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

                    if (in_array($ext, $allowed) && $files['size'][$i] < 5000000) {
                        $newName = "bukti_" . time() . "_{$i}." . $ext;
                        $dest = APPROOT . '/../public/uploads/bukti_transaksi/' . $newName;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                            $uploadedFiles[] = $newName; 
                        }
                    }
                }
            }
        }

        $buktiFotoJSON = !empty($uploadedFiles) ? json_encode($uploadedFiles) : null;

        // --- 3. Kumpulkan Data ---
        $data = [
            'product_id'      => $_POST['product_id'],
            'user_id'         => $_SESSION['user_id'],
            'jumlah'          => (int)$_POST['jumlah'],
            'supplier_id'     => $_POST['supplier_id'],
            'lokasi_id'       => $_POST['lokasi_id'],
            'status_id'       => $_POST['status_id'],
            'lot_number'      => trim($_POST['lot_number']),     
            'production_date' => $_POST['production_date'],
            'exp_date'        => $_POST['exp_date'],
            'keterangan'      => $_POST['keterangan'],
            'bukti_foto'      => $buktiFotoJSON 
        ];

        // --- 4. Kirim ke Model ---
        $transactionModel = $this->model('Transaction_model');

        try {
            $transactionModel->addBarangMasuk($data);
            $_SESSION['flash_message'] = ['text' => 'Barang masuk berhasil dicatat (Batch & Bukti Tersimpan).', 'type' => 'success'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Gagal: ' . $e->getMessage(), 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'staff/barangMasuk');
        exit;
    }
    
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK TRANSAKSI BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function barangKeluar() {
    $data['judul'] = 'Input Barang Keluar';
    $data['active_menu'] = 'barang_keluar';
    
    $data['products'] = $this->model('Product_model')->getAllProductsList();
    $data['satuan'] = $this->model('Satuan_model')->getAllSatuan(); 

    $this->view('staff/form_barang_keluar', $data);
}

    /**
     * [AJAX] Mengambil info stok tersedia untuk satu produk.
     * @param int $productId
     */
    public function getStockInfo($productId) {
        // 🔥 FIX 1: Pindahkan header ke awal dan gunakan OB untuk menangkap error
        header('Content-Type: application/json');
        
        ob_start(); // Mulai tangkap output
        
        try {
            $productModel = $this->model('Product_model');
            // Pastikan method ini sudah menggunakan query yang dilonggarkan
            $stockData = $productModel->getAvailableStockForProduct($productId);
        } catch (\Throwable $th) {
            // Tangkap semua error PHP (Fatal/Warning) yang mungkin terjadi di Model
            $stockData = []; // Default data kosong
            $errorThrown = true;
            $debugMessage = $th->getMessage();
        }

        // Bersihkan buffer dari error/warning yang mungkin muncul di Model
        $output = ob_get_clean();
        
        if (!empty($output) || (isset($errorThrown) && $errorThrown)) {
             // Jika ada output non-JSON (error), kirim response 500 dan pesan debug
             http_response_code(500); 
             $errorMessage = isset($debugMessage) ? $debugMessage : 'Output Corrupted (Warning/Notice)';
             echo json_encode(['error' => 'AJAX Output Corrupted', 'debug_message' => $errorMessage, 'raw_output' => substr($output, 0, 100)]);
             exit;
        }

        // Kirim data sebagai JSON yang bersih
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

        // 1. Kumpulkan Data Form (UPDATE DI SINI)
        $data = [
            'product_id'   => $_POST['product_id'],
            'stock_id'     => $_POST['stock_id'], 
            'jumlah'       => (int)$_POST['jumlah'],
            'satuan_id'    => $_POST['satuan_id'],
            'keterangan'   => $_POST['keterangan'],
            'user_id'      => $_SESSION['user_id']
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
        $statusModel = $this->model('Status_model');
        
        $data = [
            'judul' => 'Form Retur/Rusak',
            'products' => $this->model('Product_model')->getAllProductsList(),
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
            'stock_id_asal'     => $_POST['stock_id'], 
            'jumlah'            => (int)$_POST['jumlah'],
            'status_id_tujuan'  => $_POST['status_id_tujuan'], 
            'keterangan'        => $_POST['keterangan'], 
            'exp_date'          => $_POST['exp_date'] ?? null,
            'user_id'           => $_SESSION['user_id'] 
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

    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK OPERASI STOCK OPNAME (STAFF INPUT) - V2 (DELEGASI)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Stock Opname (Mode Lobi atau Mode Kerja)
     * URL: /staff/inputOpname/[task_id]
     */
    public function inputOpname($taskId = null) {
        $opnameModel = $this->model('Opname_model');
        $activePeriod = $opnameModel->getActivePeriod();
        
        $data = [
            'judul' => 'Input Stock Opname',
            'isOpnameActive' => false,
            'viewState' => 'idle', // idle, lobby, workspace
            'activePeriod' => null,
            'myTasks' => [],       // List tugas yang diambil (untuk Lobi)
            'availableTasks' => [], // List tugas nganggur (untuk Lobi)
            'currentTask' => null,  // Tugas yang sedang dikerjakan (untuk Workspace)
            'products' => [],       // Daftar produk di kategori terpilih
            'myEntries' => []       // [BARU] Riwayat inputan staff untuk checklist
        ];

        if ($activePeriod) {
            $data['isOpnameActive'] = true;
            $data['activePeriod'] = $activePeriod;

            if ($taskId) {
                $task = $opnameModel->getTaskById($taskId);
                
                if ($task && $task['assigned_to_user_id'] == $_SESSION['user_id'] && $task['status_task'] == 'In Progress') {
                    
                    $data['viewState'] = 'workspace';
                    $data['currentTask'] = $task;
                    
                    $productModel = $this->model('Product_model');
                    $data['products'] = $productModel->getAllProductsList($task['kategori_id']);
                    
                    $data['myEntries'] = $opnameModel->getMyEntriesByCategory(
                        $activePeriod['period_id'], 
                        $_SESSION['user_id'], 
                        $task['kategori_id']
                    );
                
                } else {
                    $_SESSION['flash_message'] = ['text' => 'Tugas tidak valid atau bukan milik Anda.', 'type' => 'error'];
                    header('Location: ' . BASE_URL . 'staff/inputOpname');
                    exit;
                }

            } else {
                $data['viewState'] = 'lobby';
                
                $data['myTasks'] = $opnameModel->getMyActiveTasks($activePeriod['period_id'], $_SESSION['user_id']);
                
                $data['availableTasks'] = $opnameModel->getTaskProgress($activePeriod['period_id']);
            }
        }
        
        $this->view('staff/input_stock_opname', $data);
    }

    /**
     * [AKSI] Mengambil Tugas (Claim)
     */
    public function claimTask($taskId) {
        $opnameModel = $this->model('Opname_model');
        
        if ($opnameModel->claimTask($taskId, $_SESSION['user_id'])) {
            $_SESSION['flash_message'] = ['text' => 'Tugas berhasil diambil. Selamat bekerja!', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal mengambil tugas. Mungkin sudah diambil orang lain.', 'type' => 'error'];
        }
        
        header('Location: ' . BASE_URL . 'staff/inputOpname');
        exit;
    }

    /**
     * [AKSI] Menyelesaikan Tugas (Submit)
     */
    public function submitTask($taskId) {
        $opnameModel = $this->model('Opname_model');
        
        if ($opnameModel->submitTask($taskId, $_SESSION['user_id'])) {
            $_SESSION['flash_message'] = ['text' => 'Tugas selesai dan disubmit. Terima kasih!', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal submit tugas.', 'type' => 'error'];
        }
        
        header('Location: ' . BASE_URL . 'staff/inputOpname');
        exit;
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
            'period_id'    => $_POST['period_id']
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
        $search = $_GET['search'] ?? '';
        $kategori = $_GET['kategori'] ?? '';
        $merek = $_GET['merek'] ?? '';
        $lokasi = $_GET['lokasi'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $productModel = $this->model('Product_model');
        
        $statusTersedia = $this->model('Status_model')->getStatusIdByName('Tersedia');
        $status_id = $statusTersedia['status_id'] ?? 0;

        $totalProducts = $productModel->getTotalProductCount($search, $kategori, $merek, $status_id, '', $lokasi);
        $totalPages = ceil($totalProducts / $limit);
        $offset = ($page - 1) * $limit;
        
        $paginatedProducts = $productModel->getProductsPaginated($limit, $offset, $search, $kategori, $merek, $status_id, '', $lokasi);

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
            'results' => [] 
        ];

        if (!empty($search)) {
            $productModel = $this->model('Product_model');
            $data['results'] = $productModel->findStockLocationsByName($search);
        }
        
        $this->view('staff/view_lokasi', $data);
    }
    /**
     * Menampilkan halaman Riwayat Input Saya (Read-Only)
     */
    public function riwayatSaya() {
        $staffId = $_SESSION['user_id'];
        
        $transModel = $this->model('Transaction_model');
        
        $data = [
            'judul' => 'Riwayat Input Saya',
            'riwayat_masuk' => $transModel->getRiwayatMasukByUserId($staffId),
            'riwayat_keluar' => $transModel->getRiwayatKeluarByUserId($staffId),
            'riwayat_rusak' => $transModel->getRiwayatReturByUserId($staffId)
        ];
        
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

/**
     * [AJAX] Get Auto Batch Number
     */
    public function getAutoBatchCode() {
        $model = $this->model('Transaction_model');
        $code = $model->generateBatchNumber();
        
        header('Content-Type: application/json');
        echo json_encode(['code' => $code]);
        exit;
    }
}