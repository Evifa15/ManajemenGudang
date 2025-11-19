<?php

class Opname_model extends Model {

    private $table = 'stock_opname_log';
    private $periodsTable = 'opname_periods';

    public function __construct() {
        parent::__construct();
    }
    
    // --- STAFF INPUT METHODS (Sudah Ada) ---
    /**
     * [STAFF] Menyimpan hasil hitungan fisik dari Staff.
     */
    public function createOpnameEntry($data) {
        $this->query("INSERT INTO " . $this->table . " 
                        (product_id, staff_user_id, stok_fisik, lot_number, catatan_staff, period_id)
                      VALUES 
                        (:pid, :uid, :fisik, :lot, :catatan, :period_id)");

        $this->bind('pid', $data['product_id']);
        $this->bind('uid', $data['user_id']); 
        $this->bind('fisik', $data['stok_fisik']);
        $this->bind('lot', $data['lot_number']);
        $this->bind('catatan', $data['catatan']);
        $this->bind('period_id', $data['period_id']); // Catat di periode mana hitungan ini terjadi

        return $this->execute();
    }
    
    // --- ADMIN CONTROL METHODS (BARU) ---

    /**
     * [ADMIN] Memeriksa apakah ada periode Opname yang aktif.
     */
    public function getActivePeriod() {
        $this->query("SELECT * FROM " . $this->periodsTable . " WHERE status = 'Aktif' ORDER BY start_date DESC LIMIT 1");
        return $this->single();
    }
    
    /**
     * [ADMIN] Mengambil riwayat periode opname yang sudah selesai.
     */
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
     * [ADMIN] Memulai periode Opname baru.
     */
    public function startNewPeriod($adminUserId) {
        // 1. Pastikan tidak ada periode lain yang aktif
        if ($this->getActivePeriod()) {
            return false; // Gagal, sudah ada yang aktif
        }
        
        // 2. Hapus semua log hitungan lama (opsional, tapi disarankan)
        $this->query("TRUNCATE TABLE " . $this->table);
        $this->execute();
        
        // 3. Mulai periode baru
        $this->query("INSERT INTO " . $this->periodsTable . " (start_date, status, started_by_user_id) VALUES (NOW(), 'Aktif', :uid)");
        $this->bind('uid', $adminUserId, PDO::PARAM_INT);
        return $this->execute();
    }

    /**
     * [ADMIN] Mengambil Laporan Rekonsiliasi (Inti)
     * Ini membandingkan stok sistem (dari product_stock) dengan stok fisik (dari opname_log)
     */
    public function getReconciliationReport($period_id) {
        // 1. Ambil snapshot stok sistem (hanya yang 'Tersedia')
        $this->query("SELECT p.product_id, p.kode_barang, p.nama_barang, 
                           SUM(ps.quantity) as stok_sistem
                      FROM products p
                      LEFT JOIN product_stock ps ON p.product_id = ps.product_id
                      LEFT JOIN status_barang sb ON ps.status_id = sb.status_id
                      WHERE sb.nama_status = 'Tersedia'
                      GROUP BY p.product_id");
        $systemStocks = $this->resultSet();

        // 2. Ambil hitungan fisik dari staff (hanya dari periode ini)
        $this->query("SELECT product_id, SUM(stok_fisik) as total_fisik 
                      FROM " . $this->table . "
                      WHERE period_id = :pid
                      GROUP BY product_id");
        $this->bind('pid', $period_id);
        $physicalCounts = $this->resultSet();
        
        // Ubah hasil fisik menjadi Peta (Map) agar mudah dicari
        $physicalMap = [];
        foreach ($physicalCounts as $count) {
            $physicalMap[$count['product_id']] = $count['total_fisik'];
        }

        // 3. Gabungkan dan hitung selisih
        $report = [];
        foreach ($systemStocks as $item) {
            $fisik = (int)($physicalMap[$item['product_id']] ?? 0);
            $sistem = (int)$item['stok_sistem'];
            $selisih = $fisik - $sistem;

            // Masukkan ke laporan
            $report[] = [
                'product_id' => $item['product_id'],
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nama_barang'],
                'stok_sistem' => $sistem,
                'stok_fisik' => $fisik,
                'selisih' => $selisih
            ];
            
            // (Opsional: Update log individu - kita skip dulu)
        }
        
        // (Kita juga perlu menangani kasus di mana stok fisik ada (misal 5)
        // tapi stok sistem 0, tapi kita skip untuk kesederhanaan)

        return $report;
    }


    /**
     * [ADMIN] Menyetujui penyesuaian (adjustment) stok.
     * Ini adalah logika yang SANGAT BERBAHAYA & PENTING (Transaksional).
     */
    public function processAdjustment($product_id, $selisih, $admin_id, $period_id) {
        
        if ($selisih == 0) {
            return true; // Tidak ada yang perlu disesuaikan
        }

        $this->db->beginTransaction();

        try {
            // 1. Ambil ID status 'Tersedia'
            $this->query("SELECT status_id FROM status_barang WHERE nama_status = 'Tersedia' LIMIT 1");
            $statusTersediaId = $this->single()['status_id'];
            if (!$statusTersediaId) {
                throw new Exception("Status 'Tersedia' tidak ditemukan.");
            }

            // 2. Cari stok 'Tersedia' pertama untuk produk ini
            // (Kita akan bebankan/tambahkan semua selisih ke satu entry stok saja)
            $this->query("SELECT stock_id, quantity FROM product_stock 
                          WHERE product_id = :pid AND status_id = :sid 
                          ORDER BY stock_id ASC LIMIT 1 FOR UPDATE");
            $this->bind('pid', $product_id);
            $this->bind('sid', $statusTersediaId);
            $existingStock = $this->single();

            // 3. Hitung Kuantitas Baru
            $newQuantity = 0;
            if ($existingStock) {
                $newQuantity = $existingStock['quantity'] + $selisih;
            } else {
                // Jika tidak ada stok 'Tersedia' sama sekali, dan selisihnya positif
                if ($selisih > 0) {
                    $newQuantity = $selisih;
                } else {
                    // Jika stok sistem 0 dan fisik 0, selisih 0 (sudah di-handle)
                    // Jika stok sistem 0 dan fisik 0, tapi selisih < 0 (error)
                    throw new Exception("Tidak bisa mengurangi stok 'Tersedia' yang tidak ada.");
                }
            }

            // Validasi stok tidak boleh minus
            if ($newQuantity < 0) {
                throw new Exception("Penyesuaian gagal, stok akan menjadi minus.");
            }

            // 4. Update atau Insert Stok
            if ($existingStock) {
                $this->query("UPDATE product_stock SET quantity = :qty WHERE stock_id = :sid");
                $this->bind('qty', $newQuantity);
                $this->bind('sid', $existingStock['stock_id']);
                $this->execute();
            } else {
                // Buat entri stok baru. (Kita butuh lokasi, kita ambil lokasi pertama barang itu)
                $this->query("SELECT lokasi_id FROM product_stock WHERE product_id = :pid LIMIT 1");
                $this->bind('pid', $product_id);
                $lokasiId = $this.single()['lokasi_id'] ?? null;
                
                $this->query("INSERT INTO product_stock (product_id, status_id, lokasi_id, quantity)
                              VALUES (:pid, :sid, :lid, :qty)");
                $this->bind('pid', $product_id);
                $this->bind('sid', $statusTersediaId);
                $this->bind('lid', $lokasiId); // Bisa null
                $this->bind('qty', $newQuantity);
                $this->execute();
            }

            // 5. Catat di 'Buku Besar' (stock_transactions)
            $this->query("INSERT INTO stock_transactions 
                            (product_id, user_id, tipe_transaksi, jumlah, keterangan) 
                          VALUES 
                            (:pid, :uid, 'opname_adj', :jml, :ket)");
            
            $this->bind('pid', $product_id);
            $this->bind('uid', $admin_id);
            $this->bind('jml', $selisih); // Catat selisihnya (+ atau -)
            $this->bind('ket', "Penyesuaian Opname Periode ID: " . $period_id);
            $this->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * [ADMIN] Menyelesaikan periode opname saat ini.
     */
    public function closeActivePeriod($periodId, $adminId) {
        $this->query("UPDATE " . $this->periodsTable . " 
                      SET status = 'Selesai', end_date = NOW(), finalized_by_user_id = :uid 
                      WHERE period_id = :pid AND status = 'Aktif'");
        $this->bind('uid', $adminId, PDO::PARAM_INT);
        $this->bind('pid', $periodId, PDO::PARAM_INT);
        return $this.execute();
    }
}