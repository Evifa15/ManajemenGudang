<?php

class AdminController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['is_logged_in'])) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
        if ($_SESSION['role'] != 'admin') {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
    }

    public function index() {
        $this->dashboard();
    }

    /**
 * Menampilkan Halaman Dashboard Admin
 * (Versi LENGKAP dengan data Absensi)
 */
public function dashboard() {

    // Panggil semua model yang kita butuhkan
    $productModel = $this->model('Product_model');
    $loanModel = $this->model('Loan_model');
    $transModel = $this->model('Transaction_model');
    $absensiModel = $this->model('Absensi_model'); // <-- Model ini sudah ada
    $auditModel = $this->model('Audit_model');

    // --- Tambahan Logika Absensi ---
    $todayAttendance = $absensiModel->getTodayAttendance($_SESSION['user_id']);
    // ---------------------------------

    // 1. Ambil data untuk Widget Peringatan
    // ... (kode $stokMenipis, $jatuhTempo, $rusakBulanIni tetap sama) ...
    $stokMenipis = $productModel->getJumlahStokMenipis();
    $jatuhTempo = $loanModel->getJumlahJatuhTempo();
    $rusakBulanIni = $transModel->getJumlahRusakBulanIni();

    // 2. Ambil data untuk Widget KPI
    // ... (kode $keluarHariIni, $hadirHariIni tetap sama) ...
    $keluarHariIni = $transModel->getJumlahTransaksiHariIni('keluar');
    $hadirHariIni = $absensiModel->getJumlahStafHadirHariIni();

    // 3. Ambil data untuk Grafik
    // ... (kode $grafikData, $labels, dll tetap sama) ...
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
    // ... (kode $stafHadir, $logTerbaru tetap sama) ...
    $stafHadir = $absensiModel->getStafHadirSaatIni();
    $logTerbaru = $auditModel->getLogTerbaru(5);

    // 5. Siapkan semua data untuk dikirim ke View
    $data = [
        'judul' => 'Dashboard Admin',
        'today_attendance' => $todayAttendance, // <-- DATA BARU UNTUK VIEW
        'widget_peringatan' => [
            'stok_menipis' => $stokMenipis,
            // ... (sisa data widget) ...
            'jatuh_tempo' => $jatuhTempo,
            'barang_rusak' => $rusakBulanIni
        ],
        'widget_kpi' => [
            // ... (sisa data kpi) ...
            'keluar_hari_ini' => $keluarHariIni,
            'hadir_hari_ini' => $hadirHariIni
        ],
        'grafik' => [
            // ... (sisa data grafik) ...
            'labels' => json_encode($labels),
            'dataMasuk' => json_encode($dataMasuk),
            'dataKeluar' => json_encode($dataKeluar)
        ],
        'widget_pengawasan' => [
            // ... (sisa data pengawasan) ...
            'staf_hadir' => $stafHadir,
            'log_terbaru' => $logTerbaru
        ]
    ];

    $this->view('admin/dashboard', $data);
}

    /* --- MEMPROSES INPUT TIDAK HADIR (SAKIT/IZIN) --- */
    public function processAbsenTidakHadir() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/dashboard');
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
                            header('Location: ' . BASE_URL . 'admin/dashboard');
                            exit;
                        }
                    } else {
                        $_SESSION['flash_message'] = ['text' => 'Ukuran file terlalu besar (Maks 2MB).', 'type' => 'error'];
                        header('Location: ' . BASE_URL . 'admin/dashboard');
                        exit;
                    }
                } else {
                    $_SESSION['flash_message'] = ['text' => 'Format file tidak didukung (Hanya JPG, PNG, PDF).', 'type' => 'error'];
                    header('Location: ' . BASE_URL . 'admin/dashboard');
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
        header('Location: ' . BASE_URL . 'admin/dashboard');
        exit;
    }


    /**
     * Menampilkan halaman Manajemen Pengguna (DENGAN PAGINASI, SEARCH & FILTER)
     * URL: /admin/users/[halaman]
     * @param int $page Nomor halaman saat ini
     */
    public function users($page = 1) {
        
        // 1. Ambil semua parameter GET
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';

        // 2. Tentukan Limit
        $limit = 10; 

        // 3. Bersihkan nomor halaman
        $page = (int)$page;
        if ($page < 1) {
            $page = 1;
        }

        // 4. Panggil Model
        $userModel = $this->model('User_model');
        
        // 5. Hitung Total Data (dengan filter)
        $totalUsers = $userModel->getTotalUserCount($search, $role);
        $totalPages = ceil($totalUsers / $limit);

        // 6. Hitung Offset
        $offset = ($page - 1) * $limit;

        // 7. Ambil data untuk halaman saat ini (dengan filter)
        $paginatedUsers = $userModel->getUsersPaginated($limit, $offset, $search, $role);

        // 🔥 [BARU] LOGIKA AJAX REQUEST UNTUK LIVE SEARCH 🔥
        // Jika request ini dikirim oleh Javascript (ada parameter 'ajax'), 
        // kirimkan data JSON saja, jangan load view HTML.
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'users' => $paginatedUsers,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
            exit; // Stop eksekusi di sini!
        }
        // 🔥 AKHIR LOGIKA BARU 🔥

        // 8. Siapkan data untuk dikirim ke view
        $data = [
            'judul' => 'Manajemen Pengguna',
            'users' => $paginatedUsers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search, // Kirim term ke view
            'role' => $role,     // Kirim role ke view
        ];
        
        // 9. Muat file view
        $this->view('admin/manage_users', $data);
    }

    /**
     * Menampilkan halaman form tambah pengguna
     */
    public function addUser() {
        $data = [
            'judul' => 'Tambah Pengguna'
        ];
        $this->view('admin/form_user', $data);
    }

    /**
     * Memproses data dari form tambah pengguna
     */
    public function processAddUser() {
        // Pastikan ini adalah request POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // 1. Kumpulkan data dari form
            $data = [
                'nama_lengkap' => $_POST['nama_lengkap'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role']
            ];

            $userModel = $this->model('User_model');

            try {
                // 2. Coba simpan ke database
                if ($userModel->createUser($data)) {
                    $_SESSION['flash_message'] = [
                        'text' => 'Data pengguna baru berhasil ditambahkan.',
                        'type' => 'success'
                    ];
                }
            } catch (PDOException $e) {
                // 3. TANGKAP ERROR JIKA GAGAL
                
                // Cek Kode Error 1062 (Duplicate Entry / Email Kembar)
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                    $_SESSION['flash_message'] = [
                        'text' => 'Gagal! Email "' . htmlspecialchars($_POST['email']) . '" sudah terdaftar.',
                        'type' => 'error'
                    ];
                } else {
                    // Error database lainnya
                    $_SESSION['flash_message'] = [
                        'text' => 'Gagal menambahkan pengguna: ' . $e->getMessage(),
                        'type' => 'error'
                    ];
                }
            }

            // 4. Kembali ke halaman daftar user
            header('Location: ' . BASE_URL . 'admin/users');
            exit;

        } else {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }
    }
    /**
     * Menampilkan halaman form edit pengguna (berdasarkan ID)
     * @param int $id ID user dari URL
     */
    public function editUser($id) {
        // 1. Ambil data user tunggal dari model
        $userModel = $this->model('User_model');
        // Kita gunakan fungsi yang sudah kita buat sebelumnya!
        $userData = $userModel->getUserById($id);

        // 2. Siapkan data untuk dikirim ke view
        $data = [
            'judul' => 'Edit Pengguna',
            'user'  => $userData // Kirim data user ke view
        ];
        
        // 3. Muat file view form edit
        $this->view('admin/form_edit_user', $data);
    }

    /**
     * Memproses data dari form edit pengguna
     */
    public function processUpdateUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $data = [
                'user_id' => $_POST['user_id'],
                'nama_lengkap' => $_POST['nama_lengkap'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role']
            ];

            $userModel = $this->model('User_model');

            try {
                // Coba Update
                if ($userModel->updateUser($data)) {
                    $_SESSION['flash_message'] = [
                        'text' => 'Data pengguna berhasil di-update.',
                        'type' => 'success'
                    ];
                }
            } catch (PDOException $e) {
                // Tangkap Error Duplikat saat Edit
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                    $_SESSION['flash_message'] = [
                        'text' => 'Gagal Update! Email "' . htmlspecialchars($_POST['email']) . '" sudah dipakai user lain.',
                        'type' => 'error'
                    ];
                } else {
                    $_SESSION['flash_message'] = [
                        'text' => 'Gagal mengupdate user: ' . $e->getMessage(),
                        'type' => 'error'
                    ];
                }
            }

            header('Location: ' . BASE_URL . 'admin/users');
            exit;

        } else {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }
    }

    /**
     * Menghapus pengguna (dipanggil oleh link)
     * @param int $id ID user dari URL
     */
    public function deleteUser($id) {
        
        // --- PENTING: Cek Keamanan ---
        // Kita tidak boleh membiarkan admin menghapus dirinya sendiri
        if ($id == $_SESSION['user_id']) {
            $_SESSION['flash_message'] = [
                'text' => 'Anda tidak bisa menghapus akun Anda sendiri.',
                'type' => 'error' // Tipe 'error' (merah)
            ];
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }
        
        // Panggil model untuk menghapus
        $userModel = $this->model('User_model');
        if ($userModel->deleteUserById($id)) {
            // Jika berhasil, set notifikasi sukses
            $_SESSION['flash_message'] = [
                'text' => 'Data pengguna berhasil dihapus.',
                'type' => 'success'
            ];
        } else {
            // Jika gagal
            $_SESSION['flash_message'] = [
                'text' => 'Gagal menghapus pengguna.',
                'type' => 'error'
            ];
        }

        // Kembalikan ke halaman daftar user
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    /**
     * Memproses file CSV yang di-upload untuk import pengguna
     * Format CSV Wajib: Nama Lengkap, Email, Password, Role
     */
    public function importUsers() {
        // 1. Validasi request
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_FILES['csv_file'])) {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        // 2. Cek error upload
        if ($_FILES['csv_file']['error'] != UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = ['text' => 'Gagal mengupload file.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        // 3. Baca dan Parsing File CSV
        $filePath = $_FILES['csv_file']['tmp_name'];
        $usersToImport = [];
        
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Validasi minimal 4 kolom
                if (count($data) >= 4) {
                    $nama = trim($data[0]);
                    $email = trim($data[1]);
                    $pass = trim($data[2]);
                    $role = strtolower(trim($data[3]));

                    // Filter baris header (jika ada kata 'email' di kolom email)
                    if (strtolower($email) == 'email') continue;

                    if (!empty($nama) && !empty($email) && !empty($pass) && !empty($role)) {
                        $usersToImport[] = [
                            'nama_lengkap' => $nama,
                            'email'        => $email,
                            'password'     => $pass,
                            'role'         => $role
                        ];
                    }
                }
            }
            fclose($handle); 
        }

        // 4. Kirim ke Model & Proses Hasil
        if (!empty($usersToImport)) {
            $userModel = $this->model('User_model');
            
            // Panggil model yang baru (Smart Import)
            $result = $userModel->importUsers($usersToImport);

            // Buat Pesan Laporan
            $msgText = "Import Selesai. <br>";
            $msgType = 'success';

            // Rincian Sukses
            if ($result['success'] > 0) {
                $msgText .= "✅ <b>{$result['success']}</b> data berhasil masuk.<br>";
            }

            // Rincian Skipped (Duplikat)
            if ($result['skipped'] > 0) {
                $msgText .= "⚠️ <b>{$result['skipped']}</b> data dilewati karena email sudah ada.<br>";
                // Opsional: Tampilkan list email yg skip (bisa dihapus jika terlalu panjang)
                // $msgText .= "<small>(" . implode(', ', $result['skipped_list']) . ")</small>";
                
                // Jika tidak ada yang sukses sama sekali, ubah warna jadi warning
                if ($result['success'] == 0) $msgType = 'warning';
            }

            // Rincian Error Lain
            if ($result['errors'] > 0) {
                $msgText .= "❌ <b>{$result['errors']}</b> data gagal karena error sistem.";
                $msgType = 'warning';
            }

            if ($result['success'] == 0 && $result['skipped'] == 0 && $result['errors'] == 0) {
                 $msgText = "Tidak ada data valid yang ditemukan dalam CSV.";
                 $msgType = 'error';
            }

            $_SESSION['flash_message'] = ['text' => $msgText, 'type' => $msgType];

        } else {
            $_SESSION['flash_message'] = ['text' => 'File CSV kosong atau format salah.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }
    
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN SUPPLIER
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Supplier (dengan Paginasi & Search)
     */
    public function suppliers($page = 1) {
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $supplierModel = $this->model('Supplier_model');
        
        $totalSuppliers = $supplierModel->getTotalSupplierCount($search);
        $totalPages = ceil($totalSuppliers / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedSuppliers = $supplierModel->getSuppliersPaginated($limit, $offset, $search);

        $data = [
            'judul' => 'Manajemen Supplier',
            'suppliers' => $paginatedSuppliers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        $this->view('admin/manage_suppliers', $data);
    }

    /**
     * Menampilkan halaman form tambah supplier
     */
    public function addSupplier() {
        $data = [
            'judul' => 'Tambah Supplier',
            'supplier' => null // Data kosong untuk form
        ];
        $this->view('admin/form_supplier', $data);
    }

    /**
     * Menampilkan halaman form edit supplier (berdasarkan ID)
     */
    public function editSupplier($id) {
        $supplierModel = $this->model('Supplier_model');
        $supplierData = $supplierModel->getSupplierById($id);

        if (!$supplierData) {
            $_SESSION['flash_message'] = ['text' => 'Supplier tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/suppliers');
            exit;
        }

        $data = [
            'judul' => 'Edit Supplier',
            'supplier'  => $supplierData // Kirim data supplier ke view
        ];
        
        $this->view('admin/form_supplier', $data); // Kita gunakan view form yang sama
    }

    /**
     * Memproses data dari form tambah/edit supplier
     */
    public function processSupplier() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/suppliers');
            exit;
        }

        // 1. Kumpulkan data
        $data = [
            'supplier_id'   => $_POST['supplier_id'] ?? null, // Akan null jika 'Tambah'
            'nama_supplier' => $_POST['nama_supplier'],
            'kontak_person' => $_POST['kontak_person'],
            'telepon'       => $_POST['telepon'],
            'email'         => $_POST['email'],
            'alamat'        => $_POST['alamat']
        ];

        $supplierModel = $this->model('Supplier_model');

        // 2. Tentukan apakah ini 'Update' (jika ada ID) or 'Create'
        if (!empty($data['supplier_id'])) {
            // --- Proses Update ---
            if ($supplierModel->updateSupplier($data)) {
                $_SESSION['flash_message'] = ['text' => 'Data supplier berhasil di-update.', 'type' => 'success'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal mengupdate supplier.', 'type' => 'error'];
            }
        } else {
            // --- Proses Create ---
            if ($supplierModel->createSupplier($data)) {
                $_SESSION['flash_message'] = ['text' => 'Supplier baru berhasil ditambahkan.', 'type' => 'success'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal menambahkan supplier.', 'type' => 'error'];
            }
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-supplier');
exit;
    }

    /**
     * Menghapus supplier (dipanggil oleh link)
     */
    public function deleteSupplier($id) {
        $supplierModel = $this->model('Supplier_model');
        
        // (Di sini kita bisa tambahkan cek, apakah supplier ini pernah dipakai
        // di tabel 'barang_masuk' sebelum dihapus. Tapi untuk sekarang,
        // kita hapus langsung)

        if ($supplierModel->deleteSupplierById($id)) {
            $_SESSION['flash_message'] = ['text' => 'Data supplier berhasil dihapus.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal menghapus supplier.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-supplier');
exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN LOKASI (VERSI PERBAIKAN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Lokasi (dengan Paginasi & Search)
     */
    public function lokasi($page = 1) {
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $lokasiModel = $this->model('Lokasi_model'); // PERBAIKAN: ->
        
        $totalLokasi = $lokasiModel->getTotalLokasiCount($search); // PERBAIKAN: ->
        $totalPages = ceil($totalLokasi / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedLokasi = $lokasiModel->getLokasiPaginated($limit, $offset, $search); // PERBAIKAN: ->

        $data = [
            'judul' => 'Manajemen Lokasi',
            'lokasi' => $paginatedLokasi,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        $this->view('admin/manage_lokasi', $data); // PERBAIKAN: ->
    }

    /**
     * Menampilkan halaman form tambah lokasi
     */
    public function addLokasi() {
        $data = [
            'judul' => 'Tambah Lokasi',
            'lokasi' => null
        ];
        $this->view('admin/form_lokasi', $data); // PERBAIKAN: ->
    }

    /**
     * Menampilkan halaman form edit lokasi (berdasarkan ID)
     */
    public function editLokasi($id) {
        $lokasiModel = $this->model('Lokasi_model'); // PERBAIKAN: ->
        $lokasiData = $lokasiModel->getLokasiById($id); // PERBAIKAN: ->

        if (!$lokasiData) {
            $_SESSION['flash_message'] = ['text' => 'Lokasi tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/lokasi');
            exit;
        }

        $data = [
            'judul' => 'Edit Lokasi',
            'lokasi'  => $lokasiData
        ];
        
        $this->view('admin/form_lokasi', $data); // PERBAIKAN: ->
    }

    /**
     * Memproses data dari form tambah/edit lokasi
     */
    public function processLokasi() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/lokasi');
            exit;
        }

        $data = [
            'lokasi_id'   => $_POST['lokasi_id'] ?? null,
            'kode_lokasi' => $_POST['kode_lokasi'],
            'nama_rak'    => $_POST['nama_rak'],
            'zona'        => $_POST['zona'],
            'deskripsi'   => $_POST['deskripsi']
        ];

        $lokasiModel = $this->model('Lokasi_model'); // PERBAIKAN: ->

        try {
            if (!empty($data['lokasi_id'])) {
                $lokasiModel->updateLokasi($data); // PERBAIKAN: ->
                $_SESSION['flash_message'] = ['text' => 'Data lokasi berhasil di-update.', 'type' => 'success'];
            } else {
                $lokasiModel->createLokasi($data); // PERBAIKAN: ->
                $_SESSION['flash_message'] = ['text' => 'Lokasi baru berhasil ditambahkan.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['flash_message'] = ['text' => 'Gagal! Kode Lokasi "' . $data['kode_lokasi'] . '" sudah ada.', 'type' => 'error'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memproses data lokasi.', 'type' => 'error'];
            }
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-lokasi');
exit;
    }

    /**
     * Menghapus lokasi (dipanggil oleh link)
     */
    public function deleteLokasi($id) {
        $lokasiModel = $this->model('Lokasi_model'); // PERBAIKAN: ->
        
        if ($lokasiModel->deleteLokasiById($id)) { // PERBAIKAN: ->
            $_SESSION['flash_message'] = ['text' => 'Data lokasi berhasil dihapus.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal menghapus lokasi.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-lokasi');
exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN KATEGORI
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Kategori (dengan Paginasi & Search)
     */
    public function kategori($page = 1) {
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $kategoriModel = $this->model('Kategori_model');
        
        $totalKategori = $kategoriModel->getTotalKategoriCount($search);
        $totalPages = ceil($totalKategori / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedKategori = $kategoriModel->getKategoriPaginated($limit, $offset, $search);

        $data = [
            'judul' => 'Manajemen Kategori', // Untuk highlight sidebar
            'kategori' => $paginatedKategori,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        $this->view('admin/manage_kategori', $data);
    }

    /**
     * Menampilkan halaman form tambah kategori
     */
    public function addKategori() {
        $data = [
            'judul' => 'Tambah Kategori', // Untuk highlight sidebar
            'kategori' => null 
        ];
        $this->view('admin/form_kategori', $data);
    }

    /**
     * Menampilkan halaman form edit kategori (berdasarkan ID)
     */
    public function editKategori($id) {
        $kategoriModel = $this->model('Kategori_model');
        $kategoriData = $kategoriModel->getKategoriById($id);

        if (!$kategoriData) {
            $_SESSION['flash_message'] = ['text' => 'Kategori tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/kategori');
            exit;
        }

        $data = [
            'judul' => 'Edit Kategori', // Untuk highlight sidebar
            'kategori'  => $kategoriData 
        ];
        
        $this->view('admin/form_kategori', $data);
    }

    /**
     * Memproses data dari form tambah/edit kategori
     */
    public function processKategori() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/kategori');
            exit;
        }

        $data = [
            'kategori_id'   => $_POST['kategori_id'] ?? null,
            'nama_kategori' => $_POST['nama_kategori'],
            'deskripsi'     => $_POST['deskripsi']
        ];

        $kategoriModel = $this->model('Kategori_model');

        try {
            if (!empty($data['kategori_id'])) {
                $kategoriModel->updateKategori($data);
                $_SESSION['flash_message'] = ['text' => 'Data kategori berhasil di-update.', 'type' => 'success'];
            } else {
                $kategoriModel->createKategori($data);
                $_SESSION['flash_message'] = ['text' => 'Kategori baru berhasil ditambahkan.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            // Tangkap error jika NAMA KATEGORI sudah ada (UNIQUE KEY)
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['flash_message'] = ['text' => 'Gagal! Nama Kategori "' . $data['nama_kategori'] . '" sudah ada.', 'type' => 'error'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memproses data kategori.', 'type' => 'error'];
            }
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-kategori');
    exit;
    }

    /**
     * Menghapus kategori (dipanggil oleh link)
     */
    public function deleteKategori($id) {
        $kategoriModel = $this->model('Kategori_model');
        
        // (Nanti kita harus tambahkan cek: "Apakah kategori ini sedang dipakai
        // oleh 'Manajemen Barang'?" Jika ya, jangan boleh dihapus)
        
        if ($kategoriModel->deleteKategoriById($id)) {
            $_SESSION['flash_message'] = ['text' => 'Data kategori berhasil dihapus.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal menghapus kategori.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-kategori');
    exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN MEREK
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Merek (dengan Paginasi & Search)
     */
    public function merek($page = 1) {
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $merekModel = $this->model('Merek_model');
        
        $totalMerek = $merekModel->getTotalMerekCount($search);
        $totalPages = ceil($totalMerek / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedMerek = $merekModel->getMerekPaginated($limit, $offset, $search);

        $data = [
            'judul' => 'Manajemen Merek', // Untuk highlight sidebar
            'merek' => $paginatedMerek,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        $this->view('admin/manage_merek', $data);
    }

    /**
     * Menampilkan halaman form tambah merek
     */
    public function addMerek() {
        $data = [
            'judul' => 'Tambah Merek', // Untuk highlight sidebar
            'merek' => null 
        ];
        $this->view('admin/form_merek', $data);
    }

    /**
     * Menampilkan halaman form edit merek (berdasarkan ID)
     */
    public function editMerek($id) {
        $merekModel = $this->model('Merek_model');
        $merekData = $merekModel->getMerekById($id);

        if (!$merekData) {
            $_SESSION['flash_message'] = ['text' => 'Merek tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/merek');
            exit;
        }

        $data = [
            'judul' => 'Edit Merek', // Untuk highlight sidebar
            'merek'  => $merekData 
        ];
        
        $this->view('admin/form_merek', $data);
    }

    /**
     * Memproses data dari form tambah/edit merek
     */
    public function processMerek() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/merek');
            exit;
        }

        $data = [
            'merek_id'   => $_POST['merek_id'] ?? null,
            'nama_merek' => $_POST['nama_merek']
        ];

        $merekModel = $this->model('Merek_model');

        try {
            if (!empty($data['merek_id'])) {
                $merekModel->updateMerek($data);
                $_SESSION['flash_message'] = ['text' => 'Data merek berhasil di-update.', 'type' => 'success'];
            } else {
                $merekModel->createMerek($data);
                $_SESSION['flash_message'] = ['text' => 'Merek baru berhasil ditambahkan.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            // Tangkap error jika NAMA MEREK sudah ada (UNIQUE KEY)
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['flash_message'] = ['text' => 'Gagal! Nama Merek "' . $data['nama_merek'] . '" sudah ada.', 'type' => 'error'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memproses data merek.', 'type' => 'error'];
            }
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-merek');
    exit;
    }

    /**
     * Menghapus merek (dipanggil oleh link)
     */
    public function deleteMerek($id) {
        $merekModel = $this->model('Merek_model');
        
        // (Nanti kita harus tambahkan cek: "Apakah merek ini sedang dipakai
        // oleh 'Manajemen Barang'?" Jika ya, jangan boleh dihapus)
        
        if ($merekModel->deleteMerekById($id)) {
            $_SESSION['flash_message'] = ['text' => 'Data merek berhasil dihapus.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal menghapus merek.', 'type' => 'error'];
        }

            header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-merek');
    exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN SATUAN
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Satuan (dengan Paginasi & Search)
     */
    public function satuan($page = 1) {
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $satuanModel = $this->model('Satuan_model');
        
        $totalSatuan = $satuanModel->getTotalSatuanCount($search);
        $totalPages = ceil($totalSatuan / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedSatuan = $satuanModel->getSatuanPaginated($limit, $offset, $search);

        $data = [
            'judul' => 'Manajemen Satuan', // Untuk highlight sidebar
            'satuan' => $paginatedSatuan,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        $this->view('admin/manage_satuan', $data);
    }

    /**
     * Menampilkan halaman form tambah satuan
     */
    public function addSatuan() {
        $data = [
            'judul' => 'Tambah Satuan', // Untuk highlight sidebar
            'satuan' => null 
        ];
        $this->view('admin/form_satuan', $data);
    }

    /**
     * Menampilkan halaman form edit satuan (berdasarkan ID)
     */
    public function editSatuan($id) {
        $satuanModel = $this->model('Satuan_model');
        $satuanData = $satuanModel->getSatuanById($id);

        if (!$satuanData) {
            $_SESSION['flash_message'] = ['text' => 'Satuan tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/satuan');
            exit;
        }

        $data = [
            'judul' => 'Edit Satuan', // Untuk highlight sidebar
            'satuan'  => $satuanData 
        ];
        
        $this->view('admin/form_satuan', $data);
    }

    /**
     * Memproses data dari form tambah/edit satuan
     */
    public function processSatuan() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/satuan');
            exit;
        }

        $data = [
            'satuan_id'   => $_POST['satuan_id'] ?? null,
            'nama_satuan' => $_POST['nama_satuan'],
            'singkatan'   => $_POST['singkatan']
        ];

        $satuanModel = $this->model('Satuan_model');

        try {
            if (!empty($data['satuan_id'])) {
                $satuanModel->updateSatuan($data);
                $_SESSION['flash_message'] = ['text' => 'Data satuan berhasil di-update.', 'type' => 'success'];
            } else {
                $satuanModel->createSatuan($data);
                $_SESSION['flash_message'] = ['text' => 'Satuan baru berhasil ditambahkan.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            // Tangkap error jika NAMA SATUAN sudah ada (UNIQUE KEY)
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['flash_message'] = ['text' => 'Gagal! Nama Satuan "' . $data['nama_satuan'] . '" sudah ada.', 'type' => 'error'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memproses data satuan.', 'type' => 'error'];
            }
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-satuan');
exit;;
    }

    /**
     * Menghapus satuan (dipanggil oleh link)
     */
    public function deleteSatuan($id) {
        $satuanModel = $this->model('Satuan_model');
        
        // (Nanti kita harus tambahkan cek: "Apakah satuan ini sedang dipakai
        // oleh 'Manajemen Barang'?" Jika ya, jangan boleh dihapus)
        
        if ($satuanModel->deleteSatuanById($id)) {
            $_SESSION['flash_message'] = ['text' => 'Data satuan berhasil dihapus.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal menghapus satuan.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-satuan');
exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN STATUS BARANG
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Status (dengan Paginasi & Search)
     */
    public function status($page = 1) {
        $search = $_GET['search'] ?? '';
        $limit = 50;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $statusModel = $this->model('Status_model');
        
        $totalStatus = $statusModel->getTotalStatusCount($search);
        $totalPages = ceil($totalStatus / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedStatus = $statusModel->getStatusPaginated($limit, $offset, $search);

        $data = [
            'judul' => 'Manajemen Status', // Untuk highlight sidebar
            'status' => $paginatedStatus,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ];
        
        $this->view('admin/manage_status', $data);
    }

    /**
     * Menampilkan halaman form tambah status
     */
    public function addStatus() {
        $data = [
            'judul' => 'Tambah Status', // Untuk highlight sidebar
            'status' => null 
        ];
        $this->view('admin/form_status', $data);
    }

    /**
     * Menampilkan halaman form edit status (berdasarkan ID)
     */
    public function editStatus($id) {
        $statusModel = $this->model('Status_model');
        $statusData = $statusModel->getStatusById($id);

        if (!$statusData) {
            $_SESSION['flash_message'] = ['text' => 'Status tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/status');
            exit;
        }

        $data = [
            'judul' => 'Edit Status', // Untuk highlight sidebar
            'status'  => $statusData 
        ];
        
        $this->view('admin/form_status', $data);
    }

    /**
     * Memproses data dari form tambah/edit status
     */
    public function processStatus() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/status');
            exit;
        }

        $data = [
            'status_id'   => $_POST['status_id'] ?? null,
            'nama_status' => $_POST['nama_status'],
            'deskripsi'   => $_POST['deskripsi']
        ];

        $statusModel = $this->model('Status_model');

        try {
            if (!empty($data['status_id'])) {
                $statusModel->updateStatus($data);
                $_SESSION['flash_message'] = ['text' => 'Data status berhasil di-update.', 'type' => 'success'];
            } else {
                $statusModel->createStatus($data);
                $_SESSION['flash_message'] = ['text' => 'Status baru berhasil ditambahkan.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            // Tangkap error jika NAMA STATUS sudah ada (UNIQUE KEY)
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['flash_message'] = ['text' => 'Gagal! Nama Status "' . $data['nama_status'] . '" sudah ada.', 'type' => 'error'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memproses data status.', 'type' => 'error'];
            }
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-status');
exit;
    }

    /**
     * Menghapus status (dipanggil oleh link)
     */
    public function deleteStatus($id) {
        $statusModel = $this->model('Status_model');
        
        // (Nanti kita harus tambahkan cek: "Apakah status ini sedang dipakai
        // oleh 'Manajemen Barang'?" Jika ya, jangan boleh dihapus)
        
        if ($statusModel->deleteStatusById($id)) {
            $_SESSION['flash_message'] = ['text' => 'Data status berhasil dihapus.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal menghapus status.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/masterDataConfig#tab-status');
exit;
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MANAJEMEN BARANG (VERSI PERBAIKAN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman Manajemen Barang (Updated: Remove Columns from AJAX)
     */
    public function barang($page = 1) {
        // 1. Ambil parameter filter dari URL
        $search = $_GET['search'] ?? '';
        $kategori = $_GET['kategori'] ?? '';
        $merek = $_GET['merek'] ?? '';
        $status = $_GET['status'] ?? '';
        $satuan = $_GET['satuan'] ?? '';
        $lokasi = $_GET['lokasi'] ?? '';
        
        $limit = 10; // Limit per halaman
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $productModel = $this->model('Product_model');
        
        // 2. Hitung Total Data (untuk Paginasi)
        $totalProducts = $productModel->getTotalProductCount($search, $kategori, $merek, $status, $satuan, $lokasi);
        $totalPages = ceil($totalProducts / $limit);
        $offset = ($page - 1) * $limit;
        
        // 3. Ambil Data Produk
        $paginatedProducts = $productModel->getProductsPaginated($limit, $offset, $search, $kategori, $merek, $status, $satuan, $lokasi);

        // ----------------------------------------------------------------
        // 4. [AJAX] Render HTML Tabel (KHUSUS REQUEST LIVE SEARCH)
        // ----------------------------------------------------------------
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            
            $html = '';
            if (empty($paginatedProducts)) {
                // Colspan 9 karena kolom sudah dikurangi (dari 11 jadi 9)
                $html = '<tr><td colspan="9" style="text-align:center;">Data tidak ditemukan.</td></tr>';
            } else {
                foreach ($paginatedProducts as $prod) {
                    // URL Aksi
                    $detailUrl = BASE_URL . 'admin/detailBarang/' . $prod['product_id'];
                    $editUrl = BASE_URL . 'admin/editBarang/' . $prod['product_id'];
                    $cetakUrl = BASE_URL . 'admin/cetakLabel/' . $prod['product_id'];
                    $deleteUrl = BASE_URL . 'admin/deleteBarang/' . $prod['product_id'];
                    
                    // Logika Status Badge
                    $stok = (int)$prod['stok_saat_ini'];
                    $min = (int)$prod['stok_minimum'];
                    $statusBadge = '';
                    
                    if ($stok == 0) {
                        $statusBadge = '<span style="background:#dc3545; color:white; padding:3px 8px; border-radius:4px; font-size:0.8em;">Habis</span>';
                    } elseif ($stok <= $min) {
                        $statusBadge = '<span style="background:#ffc107; color:black; padding:3px 8px; border-radius:4px; font-size:0.8em;">Menipis</span>';
                    } else {
                        $statusBadge = '<span style="background:#28a745; color:white; padding:3px 8px; border-radius:4px; font-size:0.8em;">Aman</span>';
                    }

                    // Susun HTML Baris (TR)
                    $html .= '<tr>';
                    
                    // 1. Checkbox
                    $html .= '<td style="text-align:center;">
                                <input type="checkbox" class="barang-checkbox" value="'.$prod['product_id'].'" style="transform: scale(1.2); cursor: pointer;">
                              </td>';
                    
                    // 2-7. Data Kolom Utama
                    $html .= '<td>' . htmlspecialchars($prod['kode_barang']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($prod['nama_barang']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($prod['nama_kategori']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($prod['nama_merek']) . '</td>';
                    $html .= '<td><strong>' . $stok . '</strong></td>';
                    $html .= '<td>' . htmlspecialchars($prod['nama_satuan']) . '</td>';
                    
                    // [DIHAPUS] Kolom Stok Minimum
                    // $html .= '<td>...</td>'; 
                    
                    // 8. Status
                    $html .= '<td>' . $statusBadge . '</td>';
                    
                    // [DIHAPUS] Kolom Lokasi
                    // $html .= '<td>...</td>';
                    
                    // 9. Aksi
                    $html .= '<td>
                                <div class="action-buttons">
                                    <a href="'.$detailUrl.'" class="btn-icon detail" title="Detail"><i class="ph ph-info"></i></a>
                                    <a href="'.$cetakUrl.'" class="btn-icon print" title="Cetak Barcode"><i class="ph ph-printer"></i></a>
                                    <a href="'.$editUrl.'" class="btn-icon edit" title="Edit"><i class="ph ph-pencil-simple"></i></a>
                                    <button type="button" class="btn-icon delete btn-delete" data-url="'.$deleteUrl.'" title="Hapus"><i class="ph ph-trash"></i></button>
                                </div>
                              </td>';
                    $html .= '</tr>';
                }
            }

            // Kirim JSON ke JS
            echo json_encode(['html' => $html, 'totalPages' => $totalPages]);
            exit; // Stop agar tidak load view di bawah
        }

        // ----------------------------------------------------------------
        // 5. View Normal (Load Halaman Awal)
        // ----------------------------------------------------------------
        $data = [
            'judul' => 'Manajemen Barang',
            'products' => $paginatedProducts,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            
            // Kirim nilai filter balik ke view agar dropdown selected
            'kategori_filter' => $kategori,
            'merek_filter' => $merek,
            'status_filter' => $status,
            'satuan_filter' => $satuan,
            'lokasi_filter' => $lokasi,
            
            // Data Master untuk Dropdown
            'allKategori' => $this->model('Kategori_model')->getAllKategori(),
            'allMerek' => $this->model('Merek_model')->getAllMerek(),
            'allStatus' => $this->model('Status_model')->getAllStatus(),
            'allSatuan' => $this->model('Satuan_model')->getAllSatuan(),
            'allLokasi' => $this->model('Lokasi_model')->getAllLokasi()
        ];
        
        $this->view('admin/manage_barang', $data);
    }

    /**
     * Menampilkan halaman form tambah barang
     */
    public function addBarang() {
        $data = [
            'judul' => 'Tambah Barang Baru',
            'kategori' => $this->model('Kategori_model')->getAllKategori(), // PERBAIKAN: ->
            'merek' => $this->model('Merek_model')->getAllMerek(), // PERBAIKAN: ->
            'satuan' => $this->model('Satuan_model')->getAllSatuan(), // PERBAIKAN: ->
            'lokasi' => $this->model('Lokasi_model')->getAllLokasi(), // PERBAIKAN: ->
            'status' => $this->model('Status_model')->getAllStatus(), // PERBAIKAN: ->
            'barang' => null
        ];
        $this->view('admin/form_barang', $data); // PERBAIKAN: ->
    }

    /**
     * Memproses data dari form tambah/edit barang (TANPA STOK AWAL)
     */
    public function processBarang() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/barang');
            exit;
        }

        $isUpdate = !empty($_POST['product_id']);
        $fotoNama = $_POST['foto_lama'] ?? null; 

        // 1. PROSES UPLOAD FOTO 
        if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['foto_barang'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed) && $file['size'] <= 2000000) {
                $newName = "produk_" . time() . "." . $ext;
                $dest = APPROOT . '/../public/uploads/barang/' . $newName;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    if ($fotoNama && file_exists(APPROOT . '/../public/uploads/barang/' . $fotoNama)) {
                        unlink(APPROOT . '/../public/uploads/barang/' . $fotoNama);
                    }
                    $fotoNama = $newName;
                }
            }
        }

        // 2. Kumpulkan Data Master Barang Saja
        $data = [
            'product_id' => $_POST['product_id'] ?? null,
            'kode_barang' => $_POST['kode_barang'],
            'nama_barang' => $_POST['nama_barang'],
            'foto_barang' => $fotoNama,
            'deskripsi' => $_POST['deskripsi'],
            'kategori_id' => $_POST['kategori_id'],
            'merek_id' => $_POST['merek_id'],
            'satuan_id' => $_POST['satuan_id'],
            'stok_minimum' => (int)$_POST['stok_minimum'],
            'bisa_dipinjam' => isset($_POST['bisa_dipinjam']) ? 1 : 0,
            'lacak_lot_serial' => isset($_POST['lacak_lot_serial']) ? 1 : 0,
        ];

        $productModel = $this->model('Product_model');

        try {
            if ($isUpdate) {
                $productModel->updateProduct($data);
                $_SESSION['flash_message'] = ['text' => 'Data barang berhasil di-update.', 'type' => 'success'];
            } else {
                // REVISI: Tidak lagi mengirim stok_awal ke model
                $productModel->createProduct($data);
                $_SESSION['flash_message'] = ['text' => 'Barang baru berhasil didaftarkan (Stok 0). Silakan input stok via Menu Transaksi Masuk.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { 
                $_SESSION['flash_message'] = ['text' => 'Gagal! Kode Barang sudah ada.', 'type' => 'error'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal: ' . $e->getMessage(), 'type' => 'error'];
            }
            $url = $isUpdate ? 'admin/editBarang/' . $data['product_id'] : 'admin/addBarang';
            header('Location: ' . BASE_URL . $url);
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/barang');
        exit;
    }

    /**
     * Menampilkan halaman form edit barang (berdasarkan ID)
     */
    public function editBarang($id) {
        $productModel = $this->model('Product_model');
        $productData = $productModel->getProductById($id);

        if (!$productData) {
            $_SESSION['flash_message'] = ['text' => 'Barang tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/barang');
            exit;
        }

        // Kita butuh SEMUA data dropdown, sama seperti addBarang
        $data = [
            'judul' => 'Edit Barang',
            'kategori' => $this->model('Kategori_model')->getAllKategori(),
            'merek' => $this->model('Merek_model')->getAllMerek(),
            'satuan' => $this->model('Satuan_model')->getAllSatuan(),
            'lokasi' => $this->model('Lokasi_model')->getAllLokasi(),
            'status' => $this->model('Status_model')->getAllStatus(),
            'barang'  => $productData // Kirim data barang yang akan diedit
        ];
        
        // Kita gunakan view form_barang.php yang sama
        $this->view('admin/form_barang', $data);
    }
    /**
     * Menghapus barang (dipanggil oleh link/tombol)
     * @param int $id ID barang dari URL
     */
    public function deleteBarang($id) {
        
        $productModel = $this->model('Product_model');
        
        // (PENTING: Nanti kita harus tambahkan cek di sini:
        // "Apakah barang ini pernah ada di tabel transaksi?"
        // Jika ya, sebaiknya jangan dihapus, tapi di-nonaktifkan)
        
        if ($productModel->deleteProductById($id)) {
            // Jika berhasil, set notifikasi sukses
            $_SESSION['flash_message'] = [
                'text' => 'Data barang (termasuk semua stoknya) berhasil dihapus.',
                'type' => 'success'
            ];
        } else {
            // Jika gagal (kemungkinan karena foreign key constraint)
            $_SESSION['flash_message'] = [
                'text' => 'Gagal menghapus barang. Barang ini mungkin sudah digunakan dalam transaksi.',
                'type' => 'error'
            ];
        }

        // Kembalikan ke halaman daftar barang
        header('Location: ' . BASE_URL . 'admin/barang');
        exit;
    }

    /**
     * Menampilkan halaman Riwayat Barang Masuk (AJAX SUPPORT + DETAIL + EXPIRED WARNING)
     */
    public function riwayatBarangMasuk($page = 1) {
        $search = $_GET['search'] ?? '';
        // Tangkap filter tanggal
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        $limit = 20;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $transactionModel = $this->model('Transaction_model');
        
        // Kirim parameter ke Model
        $totalHistory = $transactionModel->getTotalRiwayatMasukCount($search, $startDate, $endDate);
        $totalPages = ceil($totalHistory / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedHistory = $transactionModel->getRiwayatMasukPaginated($limit, $offset, $search, $startDate, $endDate);

        // --- LOGIKA AJAX (Saat User Mencari/Filter) ---
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            
            $html = '';
            // Perhatikan colspan jadi 9 karena ada kolom baru 'Status Exp'
            if (empty($paginatedHistory)) {
                $html = '<tr><td colspan="9" style="text-align:center;">Data tidak ditemukan.</td></tr>';
            } else {
                foreach ($paginatedHistory as $his) {
                    $tglInput = date('d-m-Y H:i', strtotime($his['created_at']));
                    
                    // 1. Logika Indikator Expired
                    $indikator = '<span style="color:#999;">-</span>';
                    if (!empty($his['exp_date'])) {
                        $tglExp = new DateTime($his['exp_date']);
                        $hariIni = new DateTime();
                        $selisih = $hariIni->diff($tglExp);
                        $sisaHari = (int)$selisih->format('%r%a'); // %r untuk minus

                        if ($sisaHari < 0) {
                            $indikator = '<span style="color:white; background:#dc3545; padding:3px 8px; border-radius:4px; font-size:0.85em; font-weight:bold;">EXPIRED</span>';
                        } elseif ($sisaHari <= 90) { 
                            $indikator = '<span style="color:black; background:#ffc107; padding:3px 8px; border-radius:4px; font-size:0.85em; font-weight:bold;">Warning</span>';
                        } else {
                            $indikator = '<span style="color:green; font-size:0.85em;">Aman</span>';
                        }
                    }

                    // 2. Tombol Detail (Link ke Halaman Detail)
                    $tombolDetail = '<a href="' . BASE_URL . 'admin/detailBarangMasuk/' . $his['transaction_id'] . '" 
                                       class="btn btn-sm" 
                                       style="background-color: #17a2b8; color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px;"
                                       title="Lihat Detail Lengkap">
                                        🔍 Detail
                                     </a>';

                    $html .= '<tr>';
                    $html .= '<td>' . $tglInput . '</td>';
                    $html .= '<td style="font-weight: bold;">' . htmlspecialchars($his['nama_barang']) . '</td>';
                    
                    // Kolom Status Expired (Baru)
                    $html .= '<td>' . $indikator . '</td>';
                    
                    $html .= '<td><strong>' . (int)$his['jumlah'] . '</strong></td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_satuan']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_supplier']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['staff_nama']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['lot_number']) . '</td>';
                    
                    // Kolom Aksi
                    $html .= '<td style="text-align: center;">' . $tombolDetail . '</td>';
                    
                    $html .= '</tr>';
                }
            }

            echo json_encode(['html' => $html, 'totalPages' => $totalPages, 'currentPage' => $page]);
            exit; 
        }

        // --- LOGIKA VIEW BIASA (Load Awal) ---
        $data = [
            'judul' => 'Riwayat Barang Masuk',
            'history' => $paginatedHistory,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->view('admin/history_barang_masuk', $data);
    }

    /**
     * Menampilkan halaman Riwayat Barang Keluar (AJAX SUPPORT)
     */
    public function riwayatBarangKeluar($page = 1) {
        $search = $_GET['search'] ?? '';
        // TANGKAP DATE FILTER
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $limit = 20;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $transactionModel = $this->model('Transaction_model');
        
        // KIRIM DATE FILTER KE MODEL
        $totalHistory = $transactionModel->getTotalRiwayatKeluarCount($search, $startDate, $endDate);
        $totalPages = ceil($totalHistory / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedHistory = $transactionModel->getRiwayatKeluarPaginated($limit, $offset, $search, $startDate, $endDate);

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            $html = '';
            if (empty($paginatedHistory)) {
                $html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
            } else {
                foreach ($paginatedHistory as $his) {
                    $tglInput = date('d-m-Y H:i', strtotime($his['created_at']));
                    $html .= '<tr>';
                    $html .= '<td>' . $tglInput . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_barang']) . '</td>';
                    $html .= '<td><strong>' . (int)$his['jumlah'] . '</strong></td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_satuan']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['keterangan']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['staff_nama']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['lot_number']) . '</td>';
                    $html .= '</tr>';
                }
            }
            echo json_encode(['html' => $html, 'totalPages' => $totalPages, 'currentPage' => $page]);
            exit; 
        }

        $data = [
            'judul' => 'Riwayat Barang Keluar',
            'history' => $paginatedHistory,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'start_date' => $startDate, // KIRIM BALIK KE VIEW
            'end_date' => $endDate      // KIRIM BALIK KE VIEW
        ];
        
        $this->view('admin/history_barang_keluar', $data);
    }

    /**
     * Menampilkan halaman Riwayat Retur / Barang Rusak (AJAX SUPPORT)
     */
    public function riwayatReturRusak($page = 1) {
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $limit = 20; 
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $transactionModel = $this->model('Transaction_model');
        
        $totalHistory = $transactionModel->getTotalRiwayatReturCount($search, $startDate, $endDate);
        $totalPages = ceil($totalHistory / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedHistory = $transactionModel->getRiwayatReturPaginated($limit, $offset, $search, $startDate, $endDate);

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            $html = '';
            if (empty($paginatedHistory)) {
                $html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
            } else {
                foreach ($paginatedHistory as $his) {
                    $tglLapor = date('d-m-Y H:i', strtotime($his['created_at']));
                    $html .= '<tr>';
                    $html .= '<td>' . $tglLapor . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_barang']) . '</td>';
                    $html .= '<td><strong>' . (int)$his['jumlah'] . '</strong></td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_status']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['keterangan']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['staff_nama']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['lot_number']) . '</td>';
                    $html .= '</tr>';
                }
            }
            echo json_encode(['html' => $html, 'totalPages' => $totalPages, 'currentPage' => $page]);
            exit; 
        }

        $data = [
            'judul' => 'Riwayat Retur/Rusak',
            'history' => $paginatedHistory,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->view('admin/history_retur_rusak', $data);
    }

    /**
     * Menampilkan halaman Riwayat Peminjaman (AJAX SUPPORT)
     */
    public function riwayatPeminjaman($page = 1) {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? ''; 
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $limit = 20; 
        $page = (int)$page;
        if ($page < 1) $page = 1;

        $loanModel = $this->model('Loan_model');
        
        $totalHistory = $loanModel->getTotalRiwayatPeminjamanCount($search, $status, $startDate, $endDate);
        $totalPages = ceil($totalHistory / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedHistory = $loanModel->getRiwayatPeminjamanPaginated($limit, $offset, $search, $status, $startDate, $endDate);

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            $html = '';
            if (empty($paginatedHistory)) {
                $html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
            } else {
                foreach ($paginatedHistory as $his) {
                    $tglAjuan = date('d-m-Y H:i', strtotime($his['tgl_pengajuan']));
                    $tglPinjam = date('d-m-Y', strtotime($his['tgl_rencana_pinjam']));
                    $tglKembali = date('d-m-Y', strtotime($his['tgl_rencana_kembali']));
                    $staff = $his['nama_staff'] ?? '-';

                    $html .= '<tr>';
                    $html .= '<td>' . $tglAjuan . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_peminjam']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['nama_barang']) . '</td>';
                    $html .= '<td>' . $tglPinjam . '</td>';
                    $html .= '<td>' . $tglKembali . '</td>';
                    $html .= '<td>' . htmlspecialchars($his['status_pinjam']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($staff) . '</td>';
                    $html .= '</tr>';
                }
            }
            echo json_encode(['html' => $html, 'totalPages' => $totalPages, 'currentPage' => $page]);
            exit; 
        }

        $data = [
            'judul' => 'Riwayat Peminjaman',
            'history' => $paginatedHistory,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'status_filter' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->view('admin/history_peminjaman', $data);
    }
    
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK OPERASI Kritis STOCK OPNAME (ADMIN)
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK OPERASI Kritis STOCK OPNAME (ADMIN) - V2 (DELEGASI)
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK OPERASI Kritis STOCK OPNAME (ADMIN) - V2 (TERPISAH)
    |--------------------------------------------------------------------------
    */

    /**
     * Halaman 1: Perintah Opname (Buat Baru & Monitoring)
     */
    public function perintahOpname() {
        $opnameModel = $this->model('Opname_model');
        $activePeriod = $opnameModel->getActivePeriod();
        
        $data = [
            'judul' => 'Perintah Opname',
            'activePeriod' => $activePeriod,
            'reconciliationReport' => null,
            'allKategori' => [],
            'taskProgress' => []
        ];

        // Jika BELUM ADA opname aktif -> Siapkan data untuk Form Buat Baru
        if (!$activePeriod) {
            $data['allKategori'] = $this->model('Kategori_model')->getAllKategori();
        } 
        // Jika SUDAH ADA opname aktif -> Siapkan data Monitoring
        else {
            $data['taskProgress'] = $opnameModel->getTaskProgress($activePeriod['period_id']);
            
            // Jika mode Laporan Rekonsiliasi
            if (isset($_GET['view_report'])) {
                $data['reconciliationReport'] = $opnameModel->getReconciliationReport($activePeriod['period_id']);
            }
        }

        $this->view('admin/opname_perintah', $data);
    }

    
    /**
     * Halaman 2: Riwayat / Arsip Opname
     */
    public function riwayatOpname($page = 1) {
        $opnameModel = $this->model('Opname_model');
        
        // Logika Paginasi Sederhana (Bisa dikembangkan nanti)
        $limit = 20;
        
        $data = [
            'judul' => 'Riwayat Opname',
            'completedPeriods' => $opnameModel->getCompletedPeriods($limit) 
        ];

        $this->view('admin/opname_riwayat', $data);
    }
    
    /**
     * [AKSI] Membuat Surat Perintah Opname
     */
    public function startNewOpname() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/perintahOpname');
            exit;
        }

        $scope = 'ALL';
        if (isset($_POST['kategori_ids'])) {
            if (in_array('ALL', $_POST['kategori_ids'])) {
                $scope = 'ALL';
            } else {
                $scope = implode(',', $_POST['kategori_ids']);
            }
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'nomor_sp' => $_POST['nomor_sp'],
            'target_selesai' => !empty($_POST['target_selesai']) ? $_POST['target_selesai'] : null,
            'catatan_admin' => $_POST['catatan_admin'],
            'scope_kategori' => $scope
        ];

        $opnameModel = $this->model('Opname_model');
        
        try {
            $opnameModel->createOpnameCommand($data);
            $_SESSION['flash_message'] = ['text' => 'Surat Perintah Opname berhasil dibuat.', 'type' => 'success'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Gagal: ' . $e->getMessage(), 'type' => 'error'];
        }
        
        // Redirect kembali ke halaman Perintah
        header('Location: ' . BASE_URL . 'admin/perintahOpname');
        exit;
    }

    /**
     * [AKSI] Finalisasi Opname
     */
    public function finalizeOpname() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'admin/perintahOpname');
            exit;
        }
        
        $opnameModel = $this->model('Opname_model');
        $activePeriod = $opnameModel->getActivePeriod();
        
        if (!$activePeriod) {
            $_SESSION['flash_message'] = ['text' => 'Gagal: Tidak ada periode Opname yang aktif.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/perintahOpname');
            exit;
        }

        $adjustments = $_POST['adjustment'] ?? []; 

        try {
            foreach ($adjustments as $product_id => $selisih) {
                if ($selisih != 0) { 
                    $opnameModel->processAdjustment(
                        $product_id, 
                        (int)$selisih, 
                        $_SESSION['user_id'], 
                        $activePeriod['period_id']
                    );
                }
            }

            $opnameModel->closeActivePeriod($activePeriod['period_id'], $_SESSION['user_id']);
            
            $_SESSION['flash_message'] = ['text' => 'Stock Opname Selesai & Ditutup. Cek di menu Riwayat.', 'type' => 'success'];

        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Finalisasi Gagal: ' . $e->getMessage(), 'type' => 'error'];
        }
        
        // Redirect ke Riwayat setelah selesai
        header('Location: ' . BASE_URL . 'admin/riwayatOpname');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK MENU LAPORAN & PENGAWASAN (ADMIN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan Halaman Laporan Stok Akhir
     * (Logika ini SAMA PERSIS dengan PemilikController)
     */
    public function laporanStok($page = 1) {
        // 1. Ambil filter
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
            'judul' => 'Laporan Stok Akhir',
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
        
        // 6. Panggil view baru (yang akan kita buat)
        $this->view('admin/report_stok', $data);
    }

    /**
     * Menampilkan Halaman Laporan Transaksi (LENGKAP: History + Analitik)
     */
    public function laporanTransaksi() {
        $transModel = $this->model('Transaction_model');
        
        // --- BAGIAN 1: DATA TAB RIWAYAT ---
        $limit = 50;
        $offset = 0;
        $search = ''; 

        // --- BAGIAN 2: DATA TAB ANALITIK ---
        $fastMoving = $transModel->getFastMovingItems(5);
        $slowMoving = $transModel->getSlowMovingItems(5);
        
        // Ambil Data Grafik Bulanan
        $grafikDataRaw = $transModel->getGrafikBulanan();
        
        // Format untuk Chart.js
        $grafikLabels = [];
        $grafikMasuk = [];
        $grafikKeluar = [];
        
        foreach ($grafikDataRaw as $row) {
            $grafikLabels[] = date('M Y', strtotime($row['bulan'] . '-01'));
            $grafikMasuk[] = $row['total_masuk'];
            $grafikKeluar[] = $row['total_keluar'];
        }

        $data = [
            'judul' => 'Laporan Transaksi',
            
            'riwayat_masuk'  => $transModel->getRiwayatMasukPaginated($limit, $offset, $search),
            'riwayat_keluar' => $transModel->getRiwayatKeluarPaginated($limit, $offset, $search),
            'riwayat_rusak'  => $transModel->getRiwayatReturPaginated($limit, $offset, $search),
            
            'fast_moving' => $fastMoving,
            'slow_moving' => $slowMoving,
            
            // Data untuk Chart.js
            'grafik' => [
                'labels' => json_encode($grafikLabels),
                'masuk'  => json_encode($grafikMasuk),
                'keluar' => json_encode($grafikKeluar)
            ],

            // [BARU] Kirim data mentah untuk Tabel Flow di bawah grafik
            'grafik_raw' => $grafikDataRaw 
        ];
        
        $this->view('admin/report_transaksi', $data);
    }

    /**
     * Menampilkan Halaman Laporan Peminjaman
     * (Logika ini SAMA PERSIS dengan PemilikController)
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
        
        // 3. Gunakan method yang SAMA dengan Pemilik
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
        $this->view('admin/report_peminjaman', $data);
    }

    /**
     * Menampilkan Halaman Rekap Absensi (Admin View)
     * UPDATE: Support AJAX Real-time Filter (Search & Status)
     */
    public function rekapAbsensi($page = 1) {
        $filters = [
            'search'  => $_GET['search'] ?? '',
            'status'  => $_GET['status'] ?? '', 
            'month'   => $_GET['month'] ?? date('m'), 
            'year'    => $_GET['year'] ?? date('Y')
        ];
        $limit = 10;
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $absensiModel = $this->model('Absensi_model');
        $totalAbsensi = $absensiModel->getTotalAbsensiCount($filters);
        $totalPages = ceil($totalAbsensi / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedAbsensi = $absensiModel->getAbsensiPaginated($limit, $offset, $filters);
        if (isset($_GET['ajax'])) {
            $formattedData = [];
            foreach ($paginatedAbsensi as $absen) {
                $totalJam = '-';
                $status = 'Alpa';               
                if ($absen['status'] != 'Hadir') {
                    $status = $absen['status'];
                } else {
                    if ($absen['waktu_masuk']) {
                        if ($absen['waktu_pulang']) {
                            $status = 'Hadir';
                            $checkin = new DateTime($absen['waktu_masuk']);
                            $checkout = new DateTime($absen['waktu_pulang']);
                            $interval = $checkin->diff($checkout);
                            $totalJam = $interval->format('%h jam %i mnt');
                        } else {
                            $status = 'Masih Bekerja';
                        }
                    }
                }

                $formattedData[] = [
                    'absen_id'      => $absen['absen_id'],
                    'tanggal'       => date('d-m-Y', strtotime($absen['tanggal'])),
                    'nama_lengkap'  => htmlspecialchars($absen['nama_lengkap']),
                    'waktu_masuk'   => $absen['waktu_masuk'] ? date('H:i:s', strtotime($absen['waktu_masuk'])) : '-',
                    'waktu_pulang'  => $absen['waktu_pulang'] ? date('H:i:s', strtotime($absen['waktu_pulang'])) : '-',
                    'total_jam'     => $totalJam,
                    'status'        => $status,
                    'bukti_foto'    => $absen['bukti_foto'] 
                ];
            }
            header('Content-Type: application/json');
            echo json_encode([
                'absensi' => $formattedData,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
            exit;
        }
        $data = [
            'judul' => 'Rekap Absensi',
            'absensi' => $paginatedAbsensi,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'filters' => $filters
        ];      
        $this->view('admin/rekap_absensi', $data);
    }

    /**
     * Menampilkan Halaman Audit Trail (Admin View)
     * (Logika ini SAMA PERSIS dengan PemilikController)
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

        // 2. Panggil Model yang SUDAH ADA
        $auditModel = $this->model('Audit_model');
        
        // 3. Hitung Total Data
        $totalLogs = $auditModel->getTotalAuditCount($filters);
        $totalPages = ceil($totalLogs / $limit);
        $offset = ($page - 1) * $limit;
        
        // 4. Ambil data
        $paginatedLogs = $auditModel->getAuditLogPaginated($limit, $offset, $filters);

        // 5. Ambil data untuk dropdown filter (Semua user)
        $userModel = $this->model('User_model');
        $allUsers = $userModel->getAllUsers();

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
        $this->view('admin/view_audit_trail', $data);
    }
    /*
    |--------------------------------------------------------------------------
    | METODE UNTUK ADMINISTRASI (BACKUP & RESTORE)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan Halaman Backup & Restore
     */
    public function manageBackup() {
        $data = [
            'judul' => 'Backup & Restore'
        ];
        $this->view('admin/manage_backup', $data);
    }

    /**
     * [AKSI] Memproses permintaan BACKUP DATABASE
     * Ini akan men-generate file .sql dan mengirimkannya sebagai download.
     */
    public function processBackup() {
        // Panggil Model (hanya untuk mendapatkan koneksi $db)
        $dbModel = $this->model('Model'); 
        
        // Ambil info koneksi dari config
        $host = DB_HOST;
        $user = DB_USER;
        $pass = DB_PASS;
        $dbName = DB_NAME;
        
        // Siapkan nama file
        $fileName = "backup_db_gudang_" . date("Y-m-d_H-i-s") . ".sql";

        // PERHATIAN: Ini membutuhkan 'mysqldump' terinstal di server Anda
        // (Biasanya sudah ada di paket XAMPP di folder mysql/bin)
        
        // Path ke mysqldump (sesuaikan jika XAMPP Anda di lokasi berbeda)
        // KITA HARUS MENEMUKANNYA DULU
        $mysqldumpPath = '"D:\xampp\mysql\bin\mysqldump.exe"';
        
        // (Jika di Linux/macOS, path-nya mungkin hanya "mysqldump")
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $mysqldumpPath = '"D:\xampp\mysql\bin\mysqldump.exe"';
        } else {
            $mysqldumpPath = 'mysqldump'; // Asumsi sudah ada di PATH
        }
        
        // Buat command
        $command = "$mysqldumpPath --host=$host --user=$user " . (empty($pass) ? '' : "--password=$pass") . " $dbName";

        // Set header untuk 'memaksa' download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Jalankan command dan kirim outputnya langsung ke browser
        passthru($command, $return_var);

        if ($return_var !== 0) {
            // Jika gagal, set notifikasi (meskipun header mungkin sudah terkirim)
            $_SESSION['flash_message'] = ['text' => 'Gagal membuat backup. Pastikan mysqldump ada di path.', 'type' => 'error'];
        }
        exit;
    }


    /**
     * [AKSI] Memproses permintaan RESTORE DATABASE (SANGAT BERBAHAYA)
     */
    public function processRestore() {
        // 1. Validasi request
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] != UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = ['text' => 'Gagal mengupload file backup.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/manageBackup');
            exit;
        }

        // 2. Validasi Konfirmasi
        if (empty($_POST['konfirmasi_restore']) || $_POST['konfirmasi_restore'] !== 'RESTORE') {
            $_SESSION['flash_message'] = ['text' => 'Konfirmasi "RESTORE" salah ketik. Aksi dibatalkan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/manageBackup');
            exit;
        }

        // 3. Ambil file .sql
        $filePath = $_FILES['sql_file']['tmp_name'];
        
        // 4. Baca isi file SQL
        $sqlContent = file_get_contents($filePath);

        if ($sqlContent === false) {
            $_SESSION['flash_message'] = ['text' => 'Gagal membaca isi file backup.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/manageBackup');
            exit;
        }

        // 5. Eksekusi SQL (Langsung menggunakan koneksi PDO dari Model)
        try {
            $dbModel = $this->model('Model');
            // $dbModel->db adalah koneksi PDO dari Base Model
            $dbModel->db->exec($sqlContent); 

            $_SESSION['flash_message'] = ['text' => 'SUKSES! Database telah di-restore.', 'type' => 'success'];

        } catch (PDOException $e) {
            $_SESSION['flash_message'] = ['text' => 'Restore GAGAL: ' . $e->getMessage(), 'type' => 'error'];
        }
        
        header('Location: ' . BASE_URL . 'admin/manageBackup');
        exit;
    }
    /**
 * [AKSI] Memproses Check-in
 */
public function processCheckIn() {
    $absensiModel = $this->model('Absensi_model');
    $today = $absensiModel->getTodayAttendance($_SESSION['user_id']);

    if (!$today) { // Pastikan belum check-in
        $absensiModel->checkInUser($_SESSION['user_id']);
        $_SESSION['flash_message'] = ['text' => 'Check-in berhasil.', 'type' => 'success'];
    }
    header('Location: ' . BASE_URL . 'admin/dashboard');
    exit;
}

/**
 * [AKSI] Memproses Check-out
 */
public function processCheckOut() {
    $absensiModel = $this->model('Absensi_model');
    $today = $absensiModel->getTodayAttendance($_SESSION['user_id']);

    if ($today && $today['waktu_pulang'] == null) { // Pastikan sudah check-in & belum check-out
        $absensiModel->checkOutUser($today['absen_id']);
        $_SESSION['flash_message'] = ['text' => 'Check-out berhasil.', 'type' => 'success'];
    }
    header('Location: ' . BASE_URL . 'admin/dashboard');
    exit;
}
/**
     * [AJAX] Memproses Hapus Masal (Bulk Delete)
     */
    public function deleteBulkUsers() {
        // 1. Pastikan Request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        // 2. Ambil data JSON yang dikirim JS
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
            exit;
        }

        // 3. PROTEKSI: Jangan biarkan Admin menghapus dirinya sendiri
        if (in_array($_SESSION['user_id'], $ids)) {
            // Hapus ID admin yang sedang login dari daftar hapus
            $ids = array_diff($ids, [$_SESSION['user_id']]);
            
            if (empty($ids)) {
                 echo json_encode(['success' => false, 'message' => 'Anda tidak bisa menghapus akun sendiri!']);
                 exit;
            }
        }

        // 4. Panggil Model
        $userModel = $this->model('User_model');
        if ($userModel->deleteBulkUsers($ids)) {
            echo json_encode(['success' => true, 'message' => count($ids) . ' pengguna berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
        }
        exit;
    }
    /**
     * [AJAX] Proses Update Absensi
     */
    public function updateAbsensi() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['absen_id'];
            $masuk = $_POST['waktu_masuk'];
            $pulang = $_POST['waktu_pulang']; // Bisa string kosong

            $absensiModel = $this->model('Absensi_model');
            
            if ($absensiModel->updateAbsensi($id, $masuk, $pulang)) {
                echo json_encode(['success' => true, 'message' => 'Data absensi berhasil diperbarui.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
            }
        }
    }
    
    /*
    |--------------------------------------------------------------------------
    | METODE BARU: PUSAT KONFIGURASI MASTER DATA (TABULASI)
    |--------------------------------------------------------------------------
    */
    public function masterDataConfig() {
        // Ambil semua data dari masing-masing model
        // Kita menggunakan method 'getAll...' yang sudah tersedia di masing-masing model
        $data = [
            'judul'     => 'Konfigurasi Data Master',
            'suppliers' => $this->model('Supplier_model')->getAllSuppliers(),
            'lokasi'    => $this->model('Lokasi_model')->getAllLokasi(),
            'kategori'  => $this->model('Kategori_model')->getAllKategori(),
            'merek'     => $this->model('Merek_model')->getAllMerek(),
            'satuan'    => $this->model('Satuan_model')->getAllSatuan(),
            'status'    => $this->model('Status_model')->getAllStatus()
        ];
        
        // Panggil view baru (yang akan kita buat di langkah B)
        $this->view('admin/master_data_config', $data);
    }
    /* |--------------------------------------------------------------------------
    | LOGIKA HAPUS MASAL (BULK DELETE) - UNIVERSAL
    |--------------------------------------------------------------------------
    */

    /**
     * Helper privat untuk memproses hapus masal agar kodenya tidak berulang
     */
    private function processBulkDelete($modelName, $methodName) {
        // Pastikan request adalah POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }
        
        // Ambil data JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
            exit;
        }

        // Panggil Model
        $model = $this->model($modelName);
        
        try {
            if ($model->$methodName($ids)) {
                echo json_encode(['success' => true, 'message' => count($ids) . ' data berhasil dihapus.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus data dari database.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // --- Endpoint untuk masing-masing modul ---

    public function deleteBulkBarang() {
        $this->processBulkDelete('Product_model', 'deleteBulkProducts');
    }
    public function deleteBulkKategori() {
        $this->processBulkDelete('Kategori_model', 'deleteBulkKategori');
    }
    public function deleteBulkMerek() {
        $this->processBulkDelete('Merek_model', 'deleteBulkMerek');
    }
    public function deleteBulkSatuan() {
        $this->processBulkDelete('Satuan_model', 'deleteBulkSatuan');
    }
    public function deleteBulkStatus() {
        $this->processBulkDelete('Status_model', 'deleteBulkStatus');
    }
    public function deleteBulkLokasi() {
        $this->processBulkDelete('Lokasi_model', 'deleteBulkLokasi');
    }
    public function deleteBulkSupplier() {
        $this->processBulkDelete('Supplier_model', 'deleteBulkSupplier');
    }


    /**
     * [AKSI] Memproses Edit Absensi Manual (Update Lengkap + File + Hapus File)
     */
    public function updateAbsensiManual() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $status = $_POST['status'];
            $id = $_POST['absen_id'];
            
            // Data Dasar untuk Model
            $data = [
                'absen_id' => $id,
                'status'   => $status
            ];
            
            // Logika Data Berdasarkan Status
            if ($status == 'Hadir') {
                // JIKA HADIR:
                $data['waktu_masuk'] = !empty($_POST['waktu_masuk']) ? $_POST['waktu_masuk'] : null;
                $data['waktu_pulang'] = !empty($_POST['waktu_pulang']) ? $_POST['waktu_pulang'] : null;
                $data['keterangan'] = null; // Hapus keterangan
                
                // 🔥 HAPUS BUKTI FOTO (Set NULL) 🔥
                // Kita kirim kunci 'bukti_foto' dengan nilai null agar Model menghapusnya
                $data['bukti_foto'] = null; 

            } else {
                // JIKA SAKIT/IZIN/ALPA:
                $data['waktu_masuk'] = null; 
                $data['waktu_pulang'] = null;
                $data['keterangan'] = $_POST['keterangan'];

                // Cek Upload File Baru
                if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == UPLOAD_ERR_OK) {
                    $file = $_FILES['bukti_foto'];
                    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

                    if (in_array($fileExt, $allowed)) {
                        if ($file['size'] <= 2000000) { 
                            $buktiNama = "admin_upd_" . $id . "_" . time() . "." . $fileExt;
                            $destination = APPROOT . '/../public/uploads/bukti_absen/' . $buktiNama;
                            
                            if (move_uploaded_file($file['tmp_name'], $destination)) {
                                // Jika upload sukses, masukkan ke data update
                                $data['bukti_foto'] = $buktiNama;
                            }
                        } 
                    }
                }
                // CATATAN: Jika tidak ada file baru diupload saat status Sakit/Izin,
                // kita JANGAN kirim key 'bukti_foto' ke array $data.
                // Agar bukti lama (jika ada) tidak terhapus/tertimpa null.
            }

            $absensiModel = $this->model('Absensi_model');
            
            if ($absensiModel->updateAbsensi($data)) {
                $_SESSION['flash_message'] = ['text' => 'Data absensi berhasil diperbarui.', 'type' => 'success'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal memperbarui data.', 'type' => 'error'];
            }
            
            header('Location: ' . BASE_URL . 'admin/rekapAbsensi');
            exit;
        }
    }

    /**
     * [AJAX] Endpoint untuk melihat detail barang per kategori
     */
    public function getCategoryDetails($kategori_id) {
        // Pastikan ID ada
        if (!isset($kategori_id)) exit;

        $products = $this->model('Product_model')->getProductsByCategoryId($kategori_id);

        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    }

    /**
     * Halaman Detail Arsip Opname (Laporan Lengkap)
     */
    public function detailRiwayatOpname($periodId) {
        $opnameModel = $this->model('Opname_model');
        
        // 1. Ambil Info Header SP
        $periodDetail = $opnameModel->getPeriodDetailById($periodId);
        
        if (!$periodDetail) {
            $_SESSION['flash_message'] = ['text' => 'Data opname tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/riwayatOpname');
            exit;
        }

        // 2. Siapkan Data
        $data = [
            'judul' => 'Laporan Detail Stock Opname',
            'period' => $periodDetail,
            // Siapa mengerjakan apa
            'participants' => $opnameModel->getParticipantDetails($periodId), 
            // Apa hasil hitungannya
            'logs' => $opnameModel->getOpnameLogsDetail($periodId) 
        ];

        $this->view('admin/opname_riwayat_detail', $data);
    }

    /**
     * [AKSI] Export Data Barang (CSV atau Excel)
     * URL: /admin/exportBarang/csv  ATAU  /admin/exportBarang/excel
     */
    public function exportBarang($type = 'csv') {
        // 1. Ambil data dari Model
        $productModel = $this->model('Product_model');
        $products = $productModel->getAllProductsForExport();
        $timestamp = date('Y-m-d_H-i');

        // 2. LOGIKA EXCEL (.xls)
        if ($type == 'excel') {
            $filename = "master_barang_{$timestamp}.xls";
            
            // Header agar dibaca sebagai Excel
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            // Output sebagai Tabel HTML (Excel bisa membacanya)
            echo '<table border="1">';
            echo '<thead>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Merek</th>
                        <th>Satuan</th>
                        <th>Total Stok</th>
                        <th>Stok Minimum</th>
                        <th>Deskripsi</th>
                    </tr>
                  </thead>';
            echo '<tbody>';
            
            foreach ($products as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['kode_barang']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_barang']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_kategori']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_merek']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_satuan']) . '</td>';
                echo '<td style="text-align:center;">' . $row['total_stok'] . '</td>';
                echo '<td style="text-align:center;">' . $row['stok_minimum'] . '</td>';
                echo '<td>' . htmlspecialchars($row['deskripsi']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            exit;
        } 
        
        // 3. LOGIKA CSV (Default)
        else {
            $filename = "master_barang_{$timestamp}.csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Header Kolom
            fputcsv($output, ['Kode Barang', 'Nama Barang', 'Kategori', 'Merek', 'Satuan', 'Total Stok', 'Stok Minimum', 'Deskripsi']);

            // Isi Data
            foreach ($products as $row) {
                fputcsv($output, [
                    $row['kode_barang'], $row['nama_barang'], $row['nama_kategori'],
                    $row['nama_merek'], $row['nama_satuan'], $row['total_stok'],
                    $row['stok_minimum'], $row['deskripsi']
                ]);
            }
            fclose($output);
            exit;
        }
    }

    /**
     * [AKSI] Memproses Import Barang dari CSV
     */
    public function processImportBarang() {
        // 1. Validasi File
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_FILES['csv_file'])) {
            header('Location: ' . BASE_URL . 'admin/barang');
            exit;
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] != UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = ['text' => 'Gagal upload file.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/barang');
            exit;
        }

        // 2. Baca File CSV
        $filename = $file['tmp_name'];
        $rows = [];

        if (($handle = fopen($filename, "r")) !== FALSE) {
            // fgetcsv membaca baris per baris dan memisahkan berdasarkan koma
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        // 3. Kirim ke Model
        if (!empty($rows)) {
            $productModel = $this->model('Product_model');
            $result = $productModel->importBarangSmart($rows);

            // Laporan Hasil
            $msgType = ($result['success'] > 0) ? 'success' : 'warning';
            $msgText = "Import Selesai.<br>✅ Berhasil: <b>{$result['success']}</b> items.<br>⚠️ Gagal/Duplikat: <b>{$result['failed']}</b> items.";
            
            $_SESSION['flash_message'] = ['text' => $msgText, 'type' => $msgType];
        } else {
            $_SESSION['flash_message'] = ['text' => 'File CSV kosong atau tidak terbaca.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'admin/barang');
        exit;
    }

    /**
     * [AJAX] Mendapatkan Kode Barang Otomatis
     */
    public function getAutoCode($prefix = 'BRG') {
        // Hanya proses jika request AJAX (opsional, tapi baik untuk keamanan)
        
        $productModel = $this->model('Product_model');
        $newCode = $productModel->generateNextCode($prefix);

        header('Content-Type: application/json');
        echo json_encode(['code' => $newCode]);
        exit;
    }

    /**
     * [AKSI] Halaman Cetak Label Barcode
     */
    public function cetakLabel($id) {
        $productModel = $this->model('Product_model');
        $product = $productModel->getProductById($id);

        if (!$product) {
            $_SESSION['flash_message'] = ['text' => 'Barang tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/barang');
            exit;
        }

        $data = [
            'judul' => 'Cetak Label Barang',
            'product' => $product
        ];

        // Kita buat view khusus yang bersih
        $this->view('admin/print_label', $data);
    }

    public function detailBarang($id) {
        $productModel = $this->model('Product_model');
        // Ambil data lengkap (bisa pakai getAllProductsForExport dan filter, atau buat method baru getFullDetailById)
        // Biar cepat, kita pakai getProductById, tapi pastikan join-nya lengkap.
        // (Lebih baik update getProductById di model agar ada nama kategori dll)
        
        // SEMENTARA: Kita pakai logic sederhana, ambil product lalu ambil nama kategori terpisah (atau join manual)
        // SARAN: Update getProductById di model agar melakukan JOIN.
        
        $product = $productModel->getProductByIdWithDetails($id); // <-- Buat fungsi ini di Model

        if (!$product) {
            header('Location: ' . BASE_URL . 'admin/barang');
            exit;
        }

        $data = [
            'judul' => 'Detail Barang',
            'product' => $product
        ];
        $this->view('admin/detail_barang', $data);
    }

    /**
     * Menampilkan Halaman Detail Barang Masuk (Full Page)
     */
    public function detailBarangMasuk($id) {
        $transModel = $this->model('Transaction_model');
        $transaksi = $transModel->getTransactionById($id);

        if (!$transaksi) {
            $_SESSION['flash_message'] = ['text' => 'Data transaksi tidak ditemukan.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'admin/riwayatBarangMasuk');
            exit;
        }

        $data = [
            'judul' => 'Detail Transaksi Masuk',
            'transaksi' => $transaksi
        ];

        $this->view('admin/detail_barang_masuk', $data);
    }

    /**
     * [AKSI] Export Riwayat Barang Masuk (CSV atau Excel)
     * URL: /admin/exportRiwayatMasuk/csv?start_date=...&end_date=...
     */
    public function exportRiwayatMasuk($type = 'csv') {
        // 1. Ambil Filter dari URL
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        // 2. Ambil data dari Model (Gunakan method yang sudah ada, tapi tanpa limit/paginasi)
        // Kita perlu method baru di model untuk ambil SEMUA data sesuai filter
        $transactionModel = $this->model('Transaction_model');
        $history = $transactionModel->getAllRiwayatMasukForExport($search, $startDate, $endDate);
        
        $timestamp = date('Y-m-d_H-i');

        // 3. LOGIKA EXCEL (.xls)
        if ($type == 'excel') {
            $filename = "riwayat_masuk_{$timestamp}.xls";
            
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo '<table border="1">';
            echo '<thead>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <th>Tanggal Input</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Supplier</th>
                        <th>Diinput Oleh</th>
                        <th>Lot/Batch</th>
                        <th>Tgl. Produksi</th>
                        <th>Tgl. Kedaluwarsa</th>
                        <th>Keterangan</th>
                    </tr>
                  </thead>';
            echo '<tbody>';
            
            foreach ($history as $row) {
                echo '<tr>';
                echo '<td>' . date('d-m-Y H:i', strtotime($row['created_at'])) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_barang']) . '</td>';
                echo '<td style="text-align:center;">' . $row['jumlah'] . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_satuan']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_supplier']) . '</td>';
                echo '<td>' . htmlspecialchars($row['staff_nama']) . '</td>';
                echo '<td>' . htmlspecialchars($row['lot_number']) . '</td>';
                echo '<td>' . ($row['production_date'] ? date('d-m-Y', strtotime($row['production_date'])) : '-') . '</td>';
                echo '<td>' . ($row['exp_date'] ? date('d-m-Y', strtotime($row['exp_date'])) : '-') . '</td>';
                echo '<td>' . htmlspecialchars($row['keterangan']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            exit;
        } 
        
        // 4. LOGIKA CSV (Default)
        else {
            $filename = "riwayat_masuk_{$timestamp}.csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Header Kolom
            fputcsv($output, ['Tanggal Input', 'Nama Barang', 'Jumlah', 'Satuan', 'Supplier', 'Diinput Oleh', 'Lot/Batch', 'Tgl. Produksi', 'Tgl. Kedaluwarsa', 'Keterangan']);

            // Isi Data
            foreach ($history as $row) {
                fputcsv($output, [
                    date('d-m-Y H:i', strtotime($row['created_at'])),
                    $row['nama_barang'],
                    $row['jumlah'],
                    $row['nama_satuan'],
                    $row['nama_supplier'],
                    $row['staff_nama'],
                    $row['lot_number'],
                    ($row['production_date'] ? date('d-m-Y', strtotime($row['production_date'])) : '-'),
                    ($row['exp_date'] ? date('d-m-Y', strtotime($row['exp_date'])) : '-'),
                    $row['keterangan']
                ]);
            }
            fclose($output);
            exit;
        }
    }

}