<?php

class Opname_model extends Model {

    private $table = 'stock_opname_log';
    private $periodsTable = 'opname_periods';
    private $tasksTable = 'opname_tasks'; // Tabel Baru

    public function __construct() {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | 1. METODE ADMIN (COMMANDER)
    |--------------------------------------------------------------------------
    */

    /**
     * [ADMIN] Membuat Perintah Opname Baru & Membagi Tugas
     */
    public function createOpnameCommand($data) {
        // Gunakan Transaksi agar aman (Insert Period + Insert Tasks)
        $this->db->beginTransaction();

        try {
            // A. Cek periode aktif
            if ($this->getActivePeriod()) {
                throw new Exception("Masih ada periode opname yang aktif. Harap selesaikan dulu.");
            }

            // B. Insert ke Tabel Periode (Surat Perintah)
            $query = "INSERT INTO " . $this->periodsTable . " 
                        (start_date, status, started_by_user_id, nomor_sp, target_selesai, catatan_admin, scope_kategori) 
                      VALUES 
                        (NOW(), 'Aktif', :uid, :sp, :target, :catatan, :scope)";
            
            $this->query($query);
            $this->bind('uid', $data['user_id']);
            $this->bind('sp', $data['nomor_sp']);
            $this->bind('target', $data['target_selesai']); // Bisa null
            $this->bind('catatan', $data['catatan_admin']);
            $this->bind('scope', $data['scope_kategori']); // String, misal "1,5,9" atau "ALL"
            
            $this->execute();
            $periodId = $this->db->lastInsertId();

            // C. Generate Tasks (Membagi 'Kue' Tugas per Kategori)
            $categories = [];

            if ($data['scope_kategori'] === 'ALL') {
                // Ambil SEMUA kategori yang ada di DB
                $this->query("SELECT kategori_id FROM kategori");
                $categories = $this->resultSet();
            } else {
                // Ambil kategori spesifik yang dipilih Admin
                // Convert string "1,5,9" menjadi array
                $catIds = explode(',', $data['scope_kategori']);
                $categories = array_map(function($id) { 
                    return ['kategori_id' => (int)$id]; 
                }, $catIds);
            }

            // Insert baris tugas ke tabel opname_tasks
            // Awalnya status 'Pending' (Belum diambil staff)
            $taskQuery = "INSERT INTO " . $this->tasksTable . " (period_id, kategori_id, status_task) VALUES (:pid, :kid, 'Pending')";
            
            foreach ($categories as $cat) {
                $this->query($taskQuery);
                $this->bind('pid', $periodId);
                $this->bind('kid', $cat['kategori_id']);
                $this->execute();
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            // Melempar error agar bisa ditangkap Controller
            throw $e; 
        }
    }

    /**
     * [ADMIN] Memantau Progres Tugas (Siapa ngerjain apa)
     */
    public function getTaskProgress($periodId) {
        // Ambil daftar tugas beserta nama kategori dan nama staff (jika sudah diambil)
        $this->query("SELECT t.*, k.nama_kategori, u.nama_lengkap as staff_name
                      FROM " . $this->tasksTable . " t
                      JOIN kategori k ON t.kategori_id = k.kategori_id
                      LEFT JOIN users u ON t.assigned_to_user_id = u.user_id
                      WHERE t.period_id = :pid
                      ORDER BY k.nama_kategori ASC");
        $this->bind('pid', $periodId);
        return $this->resultSet();
    }

    /**
     * [ADMIN] Mengambil Info Periode Aktif (Detail Lengkap)
     */
    public function getActivePeriod() {
        $this->query("SELECT p.*, u.nama_lengkap as admin_name 
                      FROM " . $this->periodsTable . " p
                      LEFT JOIN users u ON p.started_by_user_id = u.user_id
                      WHERE p.status = 'Aktif' 
                      ORDER BY p.start_date DESC LIMIT 1");
        return $this->single();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. METODE STAFF (EXECUTOR)
    |--------------------------------------------------------------------------
    */

    /**
     * [STAFF] Mengambil tugas yang saya ambil (My Task)
     */
    public function getMyActiveTask($periodId, $userId) {
        $this->query("SELECT t.*, k.nama_kategori 
                      FROM " . $this->tasksTable . " t
                      JOIN kategori k ON t.kategori_id = k.kategori_id
                      WHERE t.period_id = :pid 
                        AND t.assigned_to_user_id = :uid 
                        AND t.status_task = 'In Progress'
                      LIMIT 1");
        $this->bind('pid', $periodId);
        $this->bind('uid', $userId);
        return $this->single();
    }

    /**
     * [STAFF] Mengambil (Claim) Tugas Kategori
     */
    public function claimTask($taskId, $userId) {
        // Pastikan tugas masih 'Pending'
        $this->query("SELECT status_task FROM " . $this->tasksTable . " WHERE task_id = :tid");
        $this->bind('tid', $taskId);
        $task = $this->single();

        if ($task && $task['status_task'] == 'Pending') {
            $this->query("UPDATE " . $this->tasksTable . " 
                          SET assigned_to_user_id = :uid, 
                              status_task = 'In Progress', 
                              waktu_mulai = NOW() 
                          WHERE task_id = :tid");
            $this->bind('uid', $userId);
            $this->bind('tid', $taskId);
            return $this->execute();
        }
        return false;
    }

    /**
     * [STAFF] Menyelesaikan (Submit) Tugas
     */
    public function submitTask($taskId, $userId) {
        $this->query("UPDATE " . $this->tasksTable . " 
                      SET status_task = 'Submitted', waktu_selesai = NOW() 
                      WHERE task_id = :tid AND assigned_to_user_id = :uid");
        $this->bind('tid', $taskId);
        $this->bind('uid', $userId);
        return $this->execute();
    }

    /**
     * [STAFF] Input Hitungan Fisik (Ke Tabel Log)
     * (Sama seperti sebelumnya, tapi menggunakan 'opname_id' yang sudah fix)
     */
    public function createOpnameEntry($data) {
        // Cek duplikat input
        $this->query("SELECT opname_id FROM " . $this->table . " 
                      WHERE period_id = :pid AND product_id = :prod_id AND staff_user_id = :uid AND lot_number = :lot");
        $this->bind('pid', $data['period_id']);
        $this->bind('prod_id', $data['product_id']);
        $this->bind('uid', $data['user_id']);
        $this->bind('lot', $data['lot_number']);
        $existing = $this->single();

        if ($existing) {
            // Update
            $this->query("UPDATE " . $this->table . " SET stok_fisik = :fisik, catatan_staff = :catatan, created_at = NOW() 
                          WHERE opname_id = :id");
            $this->bind('fisik', $data['stok_fisik']);
            $this->bind('catatan', $data['catatan']);
            $this->bind('id', $existing['opname_id']);
        } else {
            // Insert
            $this->query("INSERT INTO " . $this->table . " 
                            (product_id, staff_user_id, stok_fisik, lot_number, catatan_staff, period_id)
                          VALUES 
                            (:pid, :uid, :fisik, :lot, :catatan, :period_id)");
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']); 
            $this->bind('fisik', $data['stok_fisik']);
            $this->bind('lot', $data['lot_number']);
            $this->bind('catatan', $data['catatan']);
            $this->bind('period_id', $data['period_id']);
        }
        return $this->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | 3. METODE FINALISASI & LAPORAN (Existing)
    |--------------------------------------------------------------------------
    */

    public function getReconciliationReport($period_id) {
        // 1. Ambil stok sistem
        $this->query("SELECT p.product_id, p.kode_barang, p.nama_barang, 
                           COALESCE(SUM(ps.quantity), 0) as stok_sistem
                      FROM products p
                      LEFT JOIN product_stock ps ON p.product_id = ps.product_id
                      LEFT JOIN status_barang sb ON ps.status_id = sb.status_id
                      WHERE sb.nama_status = 'Tersedia' OR ps.product_id IS NULL
                      GROUP BY p.product_id");
        $systemStocks = $this->resultSet();

        // 2. Ambil hitungan fisik
        $this->query("SELECT product_id, SUM(stok_fisik) as total_fisik 
                      FROM " . $this->table . "
                      WHERE period_id = :pid
                      GROUP BY product_id");
        $this->bind('pid', $period_id);
        $physicalCounts = $this->resultSet();
        
        $physicalMap = [];
        foreach ($physicalCounts as $count) {
            $physicalMap[$count['product_id']] = $count['total_fisik'];
        }

        // 3. Gabung
        $report = [];
        foreach ($systemStocks as $item) {
            $fisik = (int)($physicalMap[$item['product_id']] ?? 0);
            $sistem = (int)$item['stok_sistem'];
            $selisih = $fisik - $sistem;

            $report[] = [
                'product_id' => $item['product_id'],
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nama_barang'],
                'stok_sistem' => $sistem,
                'stok_fisik' => $fisik,
                'selisih' => $selisih
            ];
        }
        return $report;
    }

    public function processAdjustment($product_id, $selisih, $admin_id, $period_id) {
        if ($selisih == 0) return true;
        $this->db->beginTransaction();
        try {
            $this->query("SELECT status_id FROM status_barang WHERE nama_status = 'Tersedia' LIMIT 1");
            $res = $this->single();
            $statusTersediaId = $res ? $res['status_id'] : null;
            if (!$statusTersediaId) throw new Exception("Status 'Tersedia' tidak ditemukan.");

            $this->query("SELECT stock_id, quantity FROM product_stock 
                          WHERE product_id = :pid AND status_id = :sid 
                          ORDER BY exp_date DESC LIMIT 1 FOR UPDATE");
            $this->bind('pid', $product_id);
            $this->bind('sid', $statusTersediaId);
            $existingStock = $this->single();

            if ($existingStock) {
                $newQuantity = $existingStock['quantity'] + $selisih;
                if ($newQuantity < 0) $newQuantity = 0;
                $this->query("UPDATE product_stock SET quantity = :qty WHERE stock_id = :sid");
                $this->bind('qty', $newQuantity);
                $this->bind('sid', $existingStock['stock_id']);
                $this->execute();
            } else {
                if ($selisih > 0) {
                    $this->query("SELECT lokasi_id FROM lokasi LIMIT 1");
                    $loc = $this->single();
                    $lokasiId = $loc ? $loc['lokasi_id'] : null;
                    $this->query("INSERT INTO product_stock (product_id, status_id, lokasi_id, quantity, lot_number) VALUES (:pid, :sid, :lid, :qty, 'ADJ-OPNAME')");
                    $this->bind('pid', $product_id);
                    $this->bind('sid', $statusTersediaId);
                    $this->bind('lid', $lokasiId);
                    $this->bind('qty', $selisih);
                    $this->execute();
                }
            }

            $this->query("INSERT INTO stock_transactions (product_id, user_id, tipe_transaksi, jumlah, keterangan) VALUES (:pid, :uid, 'opname_adj', :jml, :ket)");
            $this->bind('pid', $product_id);
            $this->bind('uid', $admin_id);
            $this->bind('jml', abs($selisih));
            $ket = "Adjustment Opname #$period_id. Selisih: " . ($selisih > 0 ? "+$selisih" : $selisih);
            $this->bind('ket', $ket);
            $this->execute();
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function closeActivePeriod($periodId, $adminId) {
        $this->query("UPDATE " . $this->periodsTable . " SET status = 'Selesai', end_date = NOW(), finalized_by_user_id = :uid WHERE period_id = :pid AND status = 'Aktif'");
        $this->bind('uid', $adminId, PDO::PARAM_INT);
        $this->bind('pid', $periodId, PDO::PARAM_INT);
        return $this->execute();
    }
    
    public function getCompletedPeriods($limit = 10) {
        $this->query("SELECT p.*, u.nama_lengkap as finalized_by
                      FROM " . $this->periodsTable . " p
                      LEFT JOIN users u ON p.finalized_by_user_id = u.user_id
                      WHERE p.status = 'Selesai' 
                      ORDER BY p.end_date DESC LIMIT :limit");
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    /**
     * [STAFF] Mengambil SEMUA tugas yang sedang dikerjakan user ini
     */
    public function getMyActiveTasks($periodId, $userId) {
        $this->query("SELECT t.*, k.nama_kategori 
                      FROM " . $this->tasksTable . " t
                      JOIN kategori k ON t.kategori_id = k.kategori_id
                      WHERE t.period_id = :pid 
                        AND t.assigned_to_user_id = :uid 
                        AND t.status_task = 'In Progress'
                      ORDER BY k.nama_kategori ASC");
        $this->bind('pid', $periodId);
        $this->bind('uid', $userId);
        return $this->resultSet(); // Mengembalikan banyak baris
    }

    /**
     * [HELPER] Ambil detail satu tugas berdasarkan ID (untuk validasi masuk workspace)
     */
    public function getTaskById($taskId) {
        $this->query("SELECT t.*, k.nama_kategori 
                      FROM " . $this->tasksTable . " t
                      JOIN kategori k ON t.kategori_id = k.kategori_id
                      WHERE t.task_id = :tid");
        $this->bind('tid', $taskId);
        return $this->single();
    }
    /**
     * [STAFF] Mengambil riwayat input opname user ini untuk kategori tertentu
     * (Digunakan untuk menampilkan tabel checklist di halaman input)
     */
    public function getMyEntriesByCategory($periodId, $userId, $kategoriId) {
        $this->query("SELECT l.product_id, l.stok_fisik, l.lot_number, l.created_at 
                      FROM " . $this->table . " l
                      JOIN products p ON l.product_id = p.product_id
                      WHERE l.period_id = :pid 
                        AND l.staff_user_id = :uid 
                        AND p.kategori_id = :kid");
        
        $this->bind('pid', $periodId);
        $this->bind('uid', $userId);
        $this->bind('kid', $kategoriId);
        
        return $this->resultSet();
    }

    /*
    |--------------------------------------------------------------------------
    | 4. METODE ARSIP & DETAIL (HISTORICAL DATA)
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil Detail Periode + Nama Admin Pembuat & Penutup
     */
    public function getPeriodDetailById($periodId) {
        $this->query("SELECT p.*, 
                             creator.nama_lengkap as creator_name, 
                             finalizer.nama_lengkap as finalizer_name
                      FROM " . $this->periodsTable . " p
                      LEFT JOIN users creator ON p.started_by_user_id = creator.user_id
                      LEFT JOIN users finalizer ON p.finalized_by_user_id = finalizer.user_id
                      WHERE p.period_id = :pid");
        $this->bind('pid', $periodId);
        return $this->single();
    }

    /**
     * Ambil Daftar Staff yang terlibat & Status Tugas mereka
     */
    public function getParticipantDetails($periodId) {
        $this->query("SELECT t.*, u.nama_lengkap as staff_name, k.nama_kategori
                      FROM " . $this->tasksTable . " t
                      LEFT JOIN users u ON t.assigned_to_user_id = u.user_id
                      JOIN kategori k ON t.kategori_id = k.kategori_id
                      WHERE t.period_id = :pid
                      ORDER BY k.nama_kategori ASC");
        $this->bind('pid', $periodId);
        return $this->resultSet();
    }

    /**
     * Ambil Detail Log Hitungan Fisik (Hasil Akhir)
     */
    public function getOpnameLogsDetail($periodId) {
        $this->query("SELECT l.*, p.kode_barang, p.nama_barang, p.satuan_id, 
                             u.nama_lengkap as counter_name,
                             s.nama_satuan
                      FROM " . $this->table . " l
                      JOIN products p ON l.product_id = p.product_id
                      LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                      LEFT JOIN users u ON l.staff_user_id = u.user_id
                      WHERE l.period_id = :pid
                      ORDER BY p.nama_barang ASC");
        $this->bind('pid', $periodId);
        return $this->resultSet();
    }
}