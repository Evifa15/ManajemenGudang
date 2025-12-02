<?php

class Transaction_model extends Model {

    private $table = 'stock_transactions';

    public function __construct() {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | METODE BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function getTotalRiwayatMasukCount($search, $startDate = null, $endDate = null) {
        $sql = "SELECT COUNT(st.transaction_id) as total
                FROM " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                LEFT JOIN users u ON st.user_id = u.user_id
                WHERE st.tipe_transaksi = 'masuk'";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR s.nama_supplier LIKE :search 
                OR u.nama_lengkap LIKE :search 
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    public function getRiwayatMasukPaginated($limit, $offset, $search, $startDate = null, $endDate = null) {
        // Tambahkan st.production_date dan st.keterangan di sini
        $sql = "SELECT 
                    st.transaction_id, st.created_at, p.nama_barang, st.jumlah, 
                    sat.nama_satuan, s.nama_supplier, 
                    u.nama_lengkap as staff_nama, 
                    st.lot_number, st.production_date, st.exp_date, st.bukti_foto, st.keterangan
                FROM 
                    " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN satuan sat ON p.satuan_id = sat.satuan_id 
                WHERE 
                    st.tipe_transaksi = 'masuk'";
        
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR s.nama_supplier LIKE :search 
                OR u.nama_lengkap LIKE :search 
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $sql .= " ORDER BY st.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $this->query($sql);
        foreach ($params as $key => &$value) {
            $type = ($key == ':limit' || $key == ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->bind($key, $value, $type);
        }
        
        return $this->resultSet();
    }
    
    public function addBarangMasuk($data) {
        $this->db->beginTransaction();
        try {
            // 1. Insert ke Riwayat Transaksi
            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, supplier_id, lot_number, production_date, exp_date, keterangan, bukti_foto) 
                          VALUES 
                            (:pid, :uid, 'masuk', :jml, :sup_id, :lot, :prod_date, :exp, :ket, :foto)");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']);
            $this->bind('jml', $data['jumlah']);
            $this->bind('sup_id', $data['supplier_id']);
            $this->bind('lot', $data['lot_number']);
            $this->bind('prod_date', $data['production_date']); // [BARU]
            $this->bind('exp', $data['exp_date']);
            $this->bind('ket', $data['keterangan']);
            $this->bind('foto', $data['bukti_foto']);
            
            $this->execute();

            // 2. Cek Stok Lama (Berdasarkan Barang, Lokasi, Status, dan Lot)
            $this->query("SELECT stock_id, quantity FROM product_stock 
                          WHERE product_id = :pid 
                            AND lokasi_id = :lok_id 
                            AND status_id = :stat_id 
                            AND lot_number = :lot");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('lok_id', $data['lokasi_id']);
            $this->bind('stat_id', $data['status_id']);
            $this->bind('lot', $data['lot_number']);
            
            $existingStock = $this->single();

            if ($existingStock) {
                // UPDATE STOK (Tambah Jumlah & Update Tanggal)
                $newQuantity = $existingStock['quantity'] + $data['jumlah'];
                $this->query("UPDATE product_stock SET 
                                quantity = :qty, 
                                production_date = :prod_date, 
                                exp_date = :exp 
                              WHERE stock_id = :sid");
                $this->bind('qty', $newQuantity);
                $this->bind('prod_date', $data['production_date']); // [BARU]
                $this->bind('exp', $data['exp_date']);
                $this->bind('sid', $existingStock['stock_id']);
                $this->execute();
            } else {
                // INSERT STOK BARU
                $this->query("INSERT INTO product_stock 
                                (product_id, status_id, lokasi_id, lot_number, production_date, exp_date, quantity) 
                              VALUES 
                                (:pid, :stat_id, :lok_id, :lot, :prod_date, :exp, :qty)");
                $this->bind('pid', $data['product_id']);
                $this->bind('stat_id', $data['status_id']);
                $this->bind('lok_id', $data['lokasi_id']);
                $this->bind('lot', $data['lot_number']);
                $this->bind('prod_date', $data['production_date']); // [BARU]
                $this->bind('exp', $data['exp_date']);
                $this->bind('qty', $data['jumlah']);
                $this->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e; 
        }
    }

    /*
    |--------------------------------------------------------------------------
    | METODE BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function getTotalRiwayatKeluarCount($search, $startDate = null, $endDate = null) {
        $sql = "SELECT COUNT(st.transaction_id) as total
                FROM " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                WHERE st.tipe_transaksi = 'keluar'";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR st.keterangan LIKE :search 
                OR u.nama_lengkap LIKE :search 
                OR s.nama_satuan LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    public function getRiwayatKeluarPaginated($limit, $offset, $search, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    st.transaction_id, st.created_at, p.nama_barang, st.jumlah, st.keterangan, 
                    u.nama_lengkap as staff_nama, st.lot_number, s.nama_satuan
                FROM 
                    " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                WHERE 
                    st.tipe_transaksi = 'keluar'";
        
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR st.keterangan LIKE :search 
                OR u.nama_lengkap LIKE :search
                OR s.nama_satuan LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $sql .= " ORDER BY st.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $this->query($sql);
        foreach ($params as $key => &$value) {
            $type = ($key == ':limit' || $key == ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->bind($key, $value, $type);
        }
        
        return $this->resultSet();
    }

    public function addBarangKeluar($data) {
        $this->db->beginTransaction();
        try {
            $this->query("SELECT quantity, lot_number FROM product_stock 
                          WHERE stock_id = :stock_id FOR UPDATE");
            $this->bind('stock_id', $data['stock_id']);
            $currentStock = $this->single();

            if (!$currentStock) {
                throw new Exception("Stok tidak ditemukan.");
            }
            if ($data['jumlah'] > $currentStock['quantity']) {
                throw new Exception("Stok tidak mencukupi! Sisa stok: {$currentStock['quantity']}.");
            }

            $newQuantity = $currentStock['quantity'] - $data['jumlah'];
            $this->query("UPDATE product_stock SET quantity = :qty 
                          WHERE stock_id = :stock_id");
            $this->bind('qty', $newQuantity);
            $this->bind('stock_id', $data['stock_id']);
            $this->execute();

            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, lot_number, keterangan) 
                          VALUES 
                            (:pid, :uid, 'keluar', :jml, :lot, :ket)");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']);
            $this->bind('jml', $data['jumlah']);
            $this->bind('lot', $currentStock['lot_number']);
            $this->bind('ket', $data['keterangan']);
            $this->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e; 
        }
    }

    /*
    |--------------------------------------------------------------------------
    | METODE RETUR & RUSAK
    |--------------------------------------------------------------------------
    */

    public function getTotalRiwayatReturCount($search, $startDate = null, $endDate = null) {
        $sql = "SELECT COUNT(st.transaction_id) as total
                FROM " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN status_barang sb ON st.status_id = sb.status_id
                WHERE st.tipe_transaksi = 'retur'";
        
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR sb.nama_status LIKE :search 
                OR u.nama_lengkap LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    public function getRiwayatReturPaginated($limit, $offset, $search, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    st.transaction_id, st.created_at, p.nama_barang, st.jumlah, st.keterangan, 
                    u.nama_lengkap as staff_nama, st.lot_number, sb.nama_status
                FROM 
                    " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN status_barang sb ON st.status_id = sb.status_id
                WHERE 
                    st.tipe_transaksi = 'retur'";
        
        $params = [];

        if (!empty($search)) {
             $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR sb.nama_status LIKE :search 
                OR u.nama_lengkap LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $sql .= " ORDER BY st.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $this->query($sql);
        foreach ($params as $key => &$value) {
            $type = ($key == ':limit' || $key == ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->bind($key, $value, $type);
        }
        
        return $this->resultSet();
    }
    
    public function addBarangRetur($data) {
        $this->db->beginTransaction();
        try {
            $this->query("SELECT quantity, lot_number, lokasi_id FROM product_stock 
                          WHERE stock_id = :stock_id FOR UPDATE");
            $this->bind('stock_id', $data['stock_id_asal']); 
            $currentStock = $this->single();

            if (!$currentStock) {
                throw new Exception("Stok asal tidak ditemukan.");
            }
            if ($data['jumlah'] > $currentStock['quantity']) {
                throw new Exception("Stok 'Tersedia' tidak mencukupi! Sisa stok: {$currentStock['quantity']}.");
            }

            $newQuantityTersedia = $currentStock['quantity'] - $data['jumlah'];
            $this->query("UPDATE product_stock SET quantity = :qty 
                          WHERE stock_id = :stock_id");
            $this->bind('qty', $newQuantityTersedia);
            $this->bind('stock_id', $data['stock_id_asal']);
            $this->execute();

            $this->query("SELECT stock_id, quantity FROM product_stock
                          WHERE product_id = :pid
                            AND lokasi_id = :lok_id
                            AND status_id = :status_tujuan_id
                            AND lot_number = :lot");
            $this->bind('pid', $data['product_id']);
            $this->bind('lok_id', $currentStock['lokasi_id']); 
            $this->bind('status_tujuan_id', $data['status_id_tujuan']); 
            $this->bind('lot', $currentStock['lot_number']);
            $existingDamagedStock = $this->single();

            if ($existingDamagedStock) {
                $newQuantityRusak = $existingDamagedStock['quantity'] + $data['jumlah'];
                $this->query("UPDATE product_stock SET quantity = :qty
                              WHERE stock_id = :sid");
                $this->bind('qty', $newQuantityRusak);
                $this->bind('sid', $existingDamagedStock['stock_id']);
                $this->execute();
            } else {
                $this->query("INSERT INTO product_stock 
                                (product_id, status_id, lokasi_id, lot_number, exp_date, quantity)
                              VALUES
                                (:pid, :stat_id, :lok_id, :lot, :exp, :qty)");
                $this->bind('pid', $data['product_id']);
                $this->bind('stat_id', $data['status_id_tujuan']);
                $this->bind('lok_id', $currentStock['lokasi_id']);
                $this->bind('lot', $currentStock['lot_number']);
                $this->bind('exp', $data['exp_date']);
                $this->bind('qty', $data['jumlah']);
                $this->execute();
            }

            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, lot_number, status_id, keterangan) 
                          VALUES 
                            (:pid, :uid, 'retur', :jml, :lot, :stat_id, :ket)");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']);
            $this->bind('jml', $data['jumlah']);
            $this->bind('lot', $currentStock['lot_number']);
            $this->bind('stat_id', $data['status_id_tujuan']);
            $this->bind('ket', $data['keterangan']);
            
            $this->execute();
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | METODE PRIBADI STAFF
    |--------------------------------------------------------------------------
    */

    public function getRiwayatMasukByUserId($userId, $limit = 50) {
        $sql = "SELECT st.created_at, p.nama_barang, st.jumlah, s.nama_supplier, 
                       st.lot_number, st.exp_date, st.bukti_foto
                FROM stock_transactions st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                WHERE st.tipe_transaksi = 'masuk' AND st.user_id = :uid
                ORDER BY st.created_at DESC LIMIT :limit";
        
        $this->query($sql);
        $this->bind('uid', $userId, PDO::PARAM_INT);
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    public function getRiwayatKeluarByUserId($userId, $limit = 50) {
        $sql = "SELECT st.created_at, p.nama_barang, st.jumlah, st.keterangan, 
                       st.lot_number, s.nama_satuan
                FROM stock_transactions st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                WHERE st.tipe_transaksi = 'keluar' AND st.user_id = :uid
                ORDER BY st.created_at DESC LIMIT :limit";
        
        $this->query($sql);
        $this->bind('uid', $userId, PDO::PARAM_INT);
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    public function getRiwayatReturByUserId($userId, $limit = 50) {
        $sql = "SELECT st.created_at, p.nama_barang, st.jumlah, st.keterangan, 
                       st.lot_number, sb.nama_status
                FROM stock_transactions st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN status_barang sb ON st.status_id = sb.status_id
                WHERE st.tipe_transaksi = 'retur' AND st.user_id = :uid
                ORDER BY st.created_at DESC LIMIT :limit";
        
        $this->query($sql);
        $this->bind('uid', $userId, PDO::PARAM_INT);
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    /*
    |--------------------------------------------------------------------------
    | METODE ANALITIK & STATISTIK (INI YANG BARU)
    |--------------------------------------------------------------------------
    */

    public function getJumlahTransaksiHariIni($tipe) {
        $this->query("SELECT COUNT(transaction_id) as total 
                      FROM " . $this->table . " 
                      WHERE tipe_transaksi = :tipe AND DATE(created_at) = CURDATE()");
        $this->bind('tipe', $tipe);
        return $this->single()['total'];
    }

    public function getJumlahRusakBulanIni() {
        $this->query("SELECT SUM(jumlah) as total 
                      FROM " . $this->table . " 
                      WHERE tipe_transaksi = 'retur' 
                      AND MONTH(created_at) = MONTH(CURDATE()) 
                      AND YEAR(created_at) = YEAR(CURDATE())");
        $result = $this->single();
        return (int)$result['total'];
    }

    public function getGrafikBulanan() {
        $this->query("SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as bulan,
                        SUM(CASE WHEN tipe_transaksi = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
                        SUM(CASE WHEN tipe_transaksi = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
                      FROM " . $this->table . "
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                      GROUP BY bulan
                      ORDER BY bulan ASC");
        return $this->resultSet();
    }

    /**
     * [ANALITIK] Mengambil Top X Barang Paling Sering Keluar (Fast Moving)
     */
    public function getFastMovingItems($limit = 5) {
        $this->query("SELECT p.nama_barang, p.kode_barang, SUM(st.jumlah) as total_keluar 
                      FROM stock_transactions st
                      JOIN products p ON st.product_id = p.product_id
                      WHERE st.tipe_transaksi = 'keluar' 
                        AND st.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      GROUP BY st.product_id
                      ORDER BY total_keluar DESC
                      LIMIT :limit");
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    /**
     * [ANALITIK] Mengambil Top X Barang Paling Sedikit Keluar (Slow Moving)
     */
    public function getSlowMovingItems($limit = 5) {
        $this->query("SELECT p.nama_barang, p.kode_barang, 
                             COALESCE(SUM(st.jumlah), 0) as total_keluar,
                             (SELECT SUM(quantity) FROM product_stock ps WHERE ps.product_id = p.product_id) as sisa_stok
                      FROM products p
                      LEFT JOIN stock_transactions st 
                        ON p.product_id = st.product_id 
                        AND st.tipe_transaksi = 'keluar' 
                        AND st.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      GROUP BY p.product_id
                      HAVING sisa_stok > 0 -- Hanya barang yang ada stoknya
                      ORDER BY total_keluar ASC
                      LIMIT :limit");
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }
    /**
     * [DETAIL] Mengambil satu data transaksi lengkap berdasarkan ID
     */
    public function getTransactionById($id) {
        $sql = "SELECT 
                    st.*, 
                    p.kode_barang, p.nama_barang, p.foto_barang, -- [TAMBAHKAN INI]
                    sat.nama_satuan,
                    s.nama_supplier, 
                    u.nama_lengkap as staff_nama,
                    k.nama_kategori, m.nama_merek
                FROM 
                    " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN satuan sat ON p.satuan_id = sat.satuan_id
                LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
                LEFT JOIN merek m ON p.merek_id = m.merek_id
                WHERE 
                    st.transaction_id = :id";
        
        $this->query($sql);
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * [EXPORT] Mengambil SEMUA riwayat barang masuk untuk Export (Tanpa Limit)
     */
    public function getAllRiwayatMasukForExport($search, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    st.created_at, p.nama_barang, st.jumlah, 
                    sat.nama_satuan, s.nama_supplier, 
                    u.nama_lengkap as staff_nama, 
                    st.lot_number, st.production_date, st.exp_date, st.keterangan
                FROM 
                    " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN satuan sat ON p.satuan_id = sat.satuan_id 
                WHERE 
                    st.tipe_transaksi = 'masuk'";
        
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR s.nama_supplier LIKE :search 
                OR u.nama_lengkap LIKE :search 
            )";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(st.created_at) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $sql .= " ORDER BY st.created_at DESC"; // Tanpa LIMIT

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value); // Menggunakan bind() yang sudah ada (otomatis deteksi tipe)
        }
        
        return $this->resultSet();
    }
    /**
     * [AUTO] Generate Nomor Batch Otomatis (Format: BATCH-YYMMDD-XXX)
     */
    public function generateBatchNumber() {
        // Prefix berdasarkan tanggal hari ini (Misal: BATCH-251129)
        $prefix = 'BATCH-' . date('ymd'); 
        
        // Cari lot number terakhir yang mirip dengan prefix hari ini
        $this->query("SELECT lot_number FROM " . $this->table . " 
                      WHERE lot_number LIKE :prefix 
                      ORDER BY transaction_id DESC LIMIT 1");
        
        $this->bind('prefix', $prefix . '%');
        $last = $this->single();

        if ($last) {
            // Jika ada, ambil bagian nomor urut di belakang (misal 001)
            $parts = explode('-', $last['lot_number']);
            $lastSeq = end($parts); // Ambil elemen terakhir
            $newSeq = (int)$lastSeq + 1;
        } else {
            // Jika belum ada hari ini, mulai dari 1
            $newSeq = 1;
        }

        // Gabungkan: BATCH-251129-001
        return $prefix . '-' . str_pad($newSeq, 3, '0', STR_PAD_LEFT);
    }
}