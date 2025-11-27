<?php

class Transaction_model extends Model {

    private $table = 'stock_transactions';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total riwayat barang masuk (DENGAN FILTER TANGGAL)
     */
    public function getTotalRiwayatMasukCount($search, $startDate = null, $endDate = null) {
        $sql = "SELECT COUNT(st.transaction_id) as total
                FROM " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                LEFT JOIN users u ON st.user_id = u.user_id
                WHERE st.tipe_transaksi = 'masuk'";
        
        $params = [];
        
        // 1. Filter Search
        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR s.nama_supplier LIKE :search 
                OR u.nama_lengkap LIKE :search 
            )";
            $params[':search'] = '%' . $search . '%';
        }

        // 2. Filter Tanggal (Periode)
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

    /**
     * Mengambil data riwayat barang masuk (DENGAN FILTER TANGGAL)
     */
    public function getRiwayatMasukPaginated($limit, $offset, $search, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    st.created_at, p.nama_barang, st.jumlah, 
                    sat.nama_satuan, s.nama_supplier, 
                    u.nama_lengkap as staff_nama, st.lot_number, st.exp_date, st.bukti_foto
                FROM 
                    " . $this->table . " st
                LEFT JOIN products p ON st.product_id = p.product_id
                LEFT JOIN suppliers s ON st.supplier_id = s.supplier_id
                LEFT JOIN users u ON st.user_id = u.user_id
                LEFT JOIN satuan sat ON p.satuan_id = sat.satuan_id 
                WHERE 
                    st.tipe_transaksi = 'masuk'";
        
        $params = [];

        // 1. Filter Search
        if (!empty($search)) {
            $sql .= " AND (
                p.nama_barang LIKE :search 
                OR st.lot_number LIKE :search 
                OR s.nama_supplier LIKE :search 
                OR u.nama_lengkap LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        // 2. Filter Tanggal
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
    
    /**
     * Menambahkan transaksi barang masuk baru.
     * Ini adalah method transaksional:
     * 1. INSERT ke stock_transactions (buku besar)
     * 2. UPDATE atau INSERT ke product_stock (stok fisik)
     *
     * @param array $data Data dari form
     * @return bool
     * @throws Exception
     */
    public function addBarangMasuk($data) {
        
        // Mulai Transaksi Database
        $this->db->beginTransaction();
        
        try {
            // 1. Catat di 'Buku Besar' (stock_transactions)
            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, supplier_id, lot_number, exp_date, keterangan, bukti_foto) 
                          VALUES 
                            (:pid, :uid, 'masuk', :jml, :sup_id, :lot, :exp, :ket, :foto)");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']); // Diambil dari $_SESSION di controller
            $this->bind('jml', $data['jumlah']);
            $this->bind('sup_id', $data['supplier_id']);
            $this->bind('lot', $data['lot_number']);
            $this->bind('exp', $data['exp_date']);
            $this->bind('ket', $data['keterangan']);
            $this->bind('foto', $data['bukti_foto']); // Nama file
            
            $this->execute();

            // 2. Update (Tambah) Stok Fisik di 'product_stock'
            // Logika: Cek apakah sudah ada stok untuk barang, lokasi, status, dan lot yang SAMA
            
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
                // --- STOK SUDAH ADA (UPDATE) ---
                // Tambahkan jumlah baru ke jumlah lama
                $newQuantity = $existingStock['quantity'] + $data['jumlah'];
                
                $this->query("UPDATE product_stock SET quantity = :qty, exp_date = :exp 
                              WHERE stock_id = :sid");
                $this->bind('qty', $newQuantity);
                $this->bind('exp', $data['exp_date']); // Update exp_date juga
                $this->bind('sid', $existingStock['stock_id']);
                
                $this->execute();

            } else {
                // --- STOK BARU (INSERT) ---
                // Buat baris stok baru di product_stock
                $this->query("INSERT INTO product_stock 
                                (product_id, status_id, lokasi_id, lot_number, exp_date, quantity) 
                              VALUES 
                                (:pid, :stat_id, :lok_id, :lot, :exp, :qty)");
                                
                $this->bind('pid', $data['product_id']);
                $this->bind('stat_id', $data['status_id']); // Status 'Tersedia'
                $this->bind('lok_id', $data['lokasi_id']);
                $this->bind('lot', $data['lot_number']);
                $this->bind('exp', $data['exp_date']);
                $this->bind('qty', $data['jumlah']);
                
                $this->execute();
            }

            // 3. Jika semua query berhasil, 'kunci' (commit) perubahan
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // 4. Jika ada SATU saja error, 'batalkan' (rollBack) semua
            $this->db->rollBack();
            // Kirim pesan error untuk ditangkap oleh Controller
            throw $e; 
        }
    }

    /**
     * [UPDATED] Menghitung total riwayat barang keluar (DENGAN DATE FILTER)
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

        // Filter Tanggal
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

    /**
     * [UPDATED] Mengambil data riwayat barang keluar (DENGAN DATE FILTER)
     */
    public function getRiwayatKeluarPaginated($limit, $offset, $search, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    st.created_at, p.nama_barang, st.jumlah, st.keterangan, 
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

        // Filter Tanggal
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

    /**
     * Menambahkan transaksi barang keluar baru (MENGURANGI STOK).
     * Ini adalah method transaksional:
     * 1. Validasi stok
     * 2. UPDATE (kurangi) product_stock
     * 3. INSERT ke stock_transactions
     *
     * @param array $data Data dari form
     * @return bool
     * @throws Exception
     */
    public function addBarangKeluar($data) {
        
        // Mulai Transaksi Database
        $this->db->beginTransaction();
        
        try {
            // 1. Ambil & Kunci Stok Saat Ini (FOR UPDATE)
            // Kita kunci baris ini agar tidak ada proses lain yang
            // mengambil barang yang sama di waktu yang sama.
            $this->query("SELECT quantity, lot_number FROM product_stock 
                          WHERE stock_id = :stock_id FOR UPDATE");
            $this->bind('stock_id', $data['stock_id']);
            $currentStock = $this->single();

            // 2. Validasi Stok
            if (!$currentStock) {
                throw new Exception("Stok tidak ditemukan.");
            }
            if ($data['jumlah'] > $currentStock['quantity']) {
                throw new Exception("Stok tidak mencukupi! Sisa stok: {$currentStock['quantity']}.");
            }

            // 3. Update (Kurangi) Stok Fisik di 'product_stock'
            $newQuantity = $currentStock['quantity'] - $data['jumlah'];
            $this->query("UPDATE product_stock SET quantity = :qty 
                          WHERE stock_id = :stock_id");
            $this->bind('qty', $newQuantity);
            $this->bind('stock_id', $data['stock_id']);
            $this->execute();

            // 4. Catat di 'Buku Besar' (stock_transactions)
            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, lot_number, keterangan) 
                          VALUES 
                            (:pid, :uid, 'keluar', :jml, :lot, :ket)");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']); // ID Staff
            $this->bind('jml', $data['jumlah']);
            $this->bind('lot', $currentStock['lot_number']); // Ambil lot_number dari stok yang dikunci
            $this->bind('ket', $data['keterangan']); // Alasan/Tujuan
            
            $this->execute();

            // 5. Jika semua berhasil, 'kunci' (commit) perubahan
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // 6. Jika ada error, 'batalkan' (rollBack) semua
            $this->db->rollBack();
            // Kirim pesan error untuk ditangkap oleh Controller
            throw $e; 
        }
    }

    /**
     * [UPDATED] Menghitung total riwayat barang retur (DENGAN DATE FILTER)
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

        // Filter Tanggal
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

    
    /**
     * [UPDATED] Mengambil data riwayat retur (DENGAN DATE FILTER)
     */
    public function getRiwayatReturPaginated($limit, $offset, $search, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    st.created_at, p.nama_barang, st.jumlah, st.keterangan, 
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

        // Filter Tanggal
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
    
    /**
     * Menambahkan transaksi retur/rusak (MEMINDAHKAN STOK).
     * Ini adalah method transaksional:
     * 1. Validasi stok 'Tersedia'.
     * 2. UPDATE (kurangi) stok 'Tersedia' di 'product_stock'.
     * 3. UPDATE (tambah) atau INSERT stok 'Rusak/Karantina' di 'product_stock'.
     * 4. INSERT ke 'stock_transactions' dengan tipe 'retur'.
     *
     * @param array $data Data dari form
     * @return bool
     * @throws Exception
     */
    public function addBarangRetur($data) {
        
        // Mulai Transaksi Database
        $this->db->beginTransaction();
        
        try {
            // 1. Ambil & Kunci Stok Saat Ini (FOR UPDATE)
            $this->query("SELECT quantity, lot_number, lokasi_id FROM product_stock 
                          WHERE stock_id = :stock_id FOR UPDATE");
            $this->bind('stock_id', $data['stock_id_asal']); // stock_id dari barang 'Tersedia'
            $currentStock = $this->single();

            // 2. Validasi Stok 'Tersedia'
            if (!$currentStock) {
                throw new Exception("Stok asal tidak ditemukan.");
            }
            if ($data['jumlah'] > $currentStock['quantity']) {
                throw new Exception("Stok 'Tersedia' tidak mencukupi! Sisa stok: {$currentStock['quantity']}.");
            }

            // 3. Update (Kurangi) Stok Fisik 'Tersedia'
            $newQuantityTersedia = $currentStock['quantity'] - $data['jumlah'];
            $this->query("UPDATE product_stock SET quantity = :qty 
                          WHERE stock_id = :stock_id");
            $this->bind('qty', $newQuantityTersedia);
            $this->bind('stock_id', $data['stock_id_asal']);
            $this->execute();

            // 4. Cek apakah sudah ada stok 'Rusak' (atau status tujuan lain)
            //    di lokasi yang SAMA dan lot yang SAMA
            $this->query("SELECT stock_id, quantity FROM product_stock
                          WHERE product_id = :pid
                            AND lokasi_id = :lok_id
                            AND status_id = :status_tujuan_id
                            AND lot_number = :lot");
            $this->bind('pid', $data['product_id']);
            $this->bind('lok_id', $currentStock['lokasi_id']); // Lokasi sama dengan stok asal
            $this->bind('status_tujuan_id', $data['status_id_tujuan']); // Status baru (misal: 'Rusak')
            $this->bind('lot', $currentStock['lot_number']); // Lot number sama
            $existingDamagedStock = $this->single();

            if ($existingDamagedStock) {
                // --- STOK RUSAK SUDAH ADA (UPDATE) ---
                $newQuantityRusak = $existingDamagedStock['quantity'] + $data['jumlah'];
                $this->query("UPDATE product_stock SET quantity = :qty
                              WHERE stock_id = :sid");
                $this->bind('qty', $newQuantityRusak);
                $this->bind('sid', $existingDamagedStock['stock_id']);
                $this->execute();
            } else {
                // --- STOK RUSAK BARU (INSERT) ---
                $this->query("INSERT INTO product_stock 
                                (product_id, status_id, lokasi_id, lot_number, exp_date, quantity)
                              VALUES
                                (:pid, :stat_id, :lok_id, :lot, :exp, :qty)");
                $this->bind('pid', $data['product_id']);
                $this->bind('stat_id', $data['status_id_tujuan']); // Status baru
                $this->bind('lok_id', $currentStock['lokasi_id']);
                $this->bind('lot', $currentStock['lot_number']);
                $this->bind('exp', $data['exp_date']); // Tanggal kedaluwarsa (jika ada)
                $this->bind('qty', $data['jumlah']);
                $this->execute();
            }

            // 5. Catat di 'Buku Besar' (stock_transactions)
            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, lot_number, status_id, keterangan) 
                          VALUES 
                            (:pid, :uid, 'retur', :jml, :lot, :stat_id, :ket)");
            
            $this->bind('pid', $data['product_id']);
            $this->bind('uid', $data['user_id']); // ID Staff
            $this->bind('jml', $data['jumlah']);
            $this->bind('lot', $currentStock['lot_number']);
            $this->bind('stat_id', $data['status_id_tujuan']); // Catat status barunya
            $this->bind('ket', $data['keterangan']); // Alasan/Sumber
            
            $this->execute();

            // 6. Jika semua berhasil, 'kunci' (commit) perubahan
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // 7. Jika ada error, 'batalkan' (rollBack) semua
            $this->db->rollBack();
            throw $e; // Kirim pesan error
        }
    }
    /**
     * Mengambil riwayat MASUK spesifik per Staff
     * @param int $userId ID Staff yang sedang login
     * @param int $limit Jumlah data yang diambil
     * @return array
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

    /**
     * Mengambil riwayat KELUAR spesifik per Staff
     * @param int $userId ID Staff yang sedang login
     * @param int $limit Jumlah data yang diambil
     * @return array
     */
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

    /**
     * Mengambil riwayat RUSAK spesifik per Staff
     * @param int $userId ID Staff yang sedang login
     * @param int $limit Jumlah data yang diambil
     * @return array
     */
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
    /**
     * [DASHBOARD] Menghitung jumlah transaksi hari ini
     * @param string $tipe ('masuk', 'keluar', 'retur')
     */
    public function getJumlahTransaksiHariIni($tipe) {
        $this->query("SELECT COUNT(transaction_id) as total 
                      FROM " . $this->table . " 
                      WHERE tipe_transaksi = :tipe AND DATE(created_at) = CURDATE()");
        $this->bind('tipe', $tipe);
        return $this->single()['total'];
    }

    /**
     * [DASHBOARD] Menghitung jumlah barang rusak bulan ini
     */
    public function getJumlahRusakBulanIni() {
        $this->query("SELECT SUM(jumlah) as total 
                      FROM " . $this->table . " 
                      WHERE tipe_transaksi = 'retur' 
                      AND MONTH(created_at) = MONTH(CURDATE()) 
                      AND YEAR(created_at) = YEAR(CURDATE())");
        $result = $this->single();
        return (int)$result['total']; // Kembalikan 0 jika null
    }

    /**
     * [DASHBOARD] Mengambil data untuk grafik bulanan
     */
    public function getGrafikBulanan() {
        // Ambil data 6 bulan terakhir
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
}