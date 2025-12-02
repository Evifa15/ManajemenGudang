<?php

class Product_model extends Model {

    private $table = 'products';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total produk (dengan SEMUA filter yang kompleks)
     */
    public function getTotalProductCount($search, $kategori, $merek, $status, $satuan = '', $lokasi = '') {
        $sql = "SELECT COUNT(DISTINCT p.product_id) as total 
                FROM products p 
                LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
                LEFT JOIN merek m ON p.merek_id = m.merek_id
                LEFT JOIN product_stock ps ON p.product_id = ps.product_id 
                LEFT JOIN status_barang sb ON ps.status_id = sb.status_id";
        
        $params = [];
        $whereClauses = [];

        if (!empty($search)) {
            $whereClauses[] = "(p.kode_barang LIKE :search OR p.nama_barang LIKE :search OR k.nama_kategori LIKE :search OR m.nama_merek LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($kategori)) {
            $whereClauses[] = "p.kategori_id = :kategori";
            $params[':kategori'] = $kategori;
        }
        if (!empty($merek)) {
            $whereClauses[] = "p.merek_id = :merek";
            $params[':merek'] = $merek;
        }
        if (!empty($satuan)) {
            $whereClauses[] = "p.satuan_id = :satuan";
            $params[':satuan'] = $satuan;
        }
        // Filter berdasarkan Stok
        if (!empty($status)) {
            $whereClauses[] = "ps.status_id = :status";
            $params[':status'] = $status;
        }
        if (!empty($lokasi)) {
            $whereClauses[] = "ps.lokasi_id = :lokasi";
            $params[':lokasi'] = $lokasi;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    /**
     * Mengambil data produk dengan paginasi (dengan SEMUA filter dan JOIN)
     */
    public function getProductsPaginated($limit, $offset, $search, $kategori, $merek, $status, $satuan = '', $lokasi = '') {
        $sql = "SELECT 
                    p.product_id, p.kode_barang, p.nama_barang, p.foto_barang,
                    k.nama_kategori, m.nama_merek, s.nama_satuan,
                    -- Subquery untuk menghitung total stok 'Tersedia'
                    (SELECT COALESCE(SUM(ps.quantity), 0) 
                     FROM product_stock ps 
                     JOIN status_barang sb2 ON ps.status_id = sb2.status_id
                     WHERE ps.product_id = p.product_id 
                     AND sb2.nama_status = 'Tersedia'
                    ) as stok_saat_ini,
                    p.stok_minimum,
                    -- Ambil satu contoh lokasi (GROUP_CONCAT agar tidak duplikat baris)
                    (SELECT l.kode_lokasi FROM product_stock ps3 
                     JOIN lokasi l ON ps3.lokasi_id = l.lokasi_id 
                     WHERE ps3.product_id = p.product_id LIMIT 1) as kode_lokasi
                FROM 
                    products p
                LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
                LEFT JOIN merek m ON p.merek_id = m.merek_id
                LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                -- Join stok untuk keperluan filter
                LEFT JOIN product_stock ps_filter ON p.product_id = ps_filter.product_id
                ";
        
        $params = [];
        $whereClauses = [];

        if (!empty($search)) {
            $whereClauses[] = "(p.kode_barang LIKE :search OR p.nama_barang LIKE :search OR k.nama_kategori LIKE :search OR m.nama_merek LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($kategori)) {
            $whereClauses[] = "p.kategori_id = :kategori";
            $params[':kategori'] = $kategori;
        }
        if (!empty($merek)) {
            $whereClauses[] = "p.merek_id = :merek";
            $params[':merek'] = $merek;
        }
        if (!empty($satuan)) {
            $whereClauses[] = "p.satuan_id = :satuan";
            $params[':satuan'] = $satuan;
        }
        // Filter Stok
        if (!empty($status)) {
            $whereClauses[] = "ps_filter.status_id = :status";
            $params[':status'] = $status;
        }
        if (!empty($lokasi)) {
            $whereClauses[] = "ps_filter.lokasi_id = :lokasi";
            $params[':lokasi'] = $lokasi;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " GROUP BY p.product_id ORDER BY p.nama_barang ASC LIMIT :limit OFFSET :offset";
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
     * Menyimpan data MASTER produk baru (Stok Default 0)
     */
    public function createProduct($data) {
        // Hanya insert ke tabel 'products'. Tidak menyentuh 'product_stock'.
        $this->query("INSERT INTO products 
                        (kode_barang, nama_barang, foto_barang, deskripsi, kategori_id, merek_id, satuan_id, stok_minimum, bisa_dipinjam, lacak_lot_serial) 
                      VALUES 
                        (:kode_barang, :nama_barang, :foto_barang, :deskripsi, :kategori_id, :merek_id, :satuan_id, :stok_minimum, :bisa_dipinjam, :lacak_lot_serial)");
        
        $this->bind('kode_barang', $data['kode_barang']);
        $this->bind('nama_barang', $data['nama_barang']);
        $this->bind('foto_barang', $data['foto_barang'] ?? null);
        $this->bind('deskripsi', $data['deskripsi']);
        $this->bind('kategori_id', $data['kategori_id']);
        $this->bind('merek_id', $data['merek_id']);
        $this->bind('satuan_id', $data['satuan_id']);
        $this->bind('stok_minimum', $data['stok_minimum']);
        $this->bind('bisa_dipinjam', $data['bisa_dipinjam']);
        $this->bind('lacak_lot_serial', $data['lacak_lot_serial']);
        
        return $this->execute();
    }

    /**
     * Update data produk (UPDATED: Ada foto_barang)
     */
    public function updateProduct($data) {
        $this->query("UPDATE products SET 
                        kode_barang = :kode_barang, 
                        nama_barang = :nama_barang,
                        foto_barang = :foto_barang, 
                        deskripsi = :deskripsi, 
                        kategori_id = :kategori_id, 
                        merek_id = :merek_id, 
                        satuan_id = :satuan_id, 
                        stok_minimum = :stok_minimum, 
                        bisa_dipinjam = :bisa_dipinjam, 
                        lacak_lot_serial = :lacak_lot_serial
                      WHERE product_id = :product_id");

        $this->bind('kode_barang', $data['kode_barang']);
        $this->bind('nama_barang', $data['nama_barang']);
        $this->bind('foto_barang', $data['foto_barang'] ?? null); // Bind Foto
        $this->bind('deskripsi', $data['deskripsi']);
        $this->bind('kategori_id', $data['kategori_id']);
        $this->bind('merek_id', $data['merek_id']);
        $this->bind('satuan_id', $data['satuan_id']);
        $this->bind('stok_minimum', $data['stok_minimum']);
        $this->bind('bisa_dipinjam', $data['bisa_dipinjam']);
        $this->bind('lacak_lot_serial', $data['lacak_lot_serial']);
        $this->bind('product_id', $data['product_id'], PDO::PARAM_INT);

        return $this->execute();
    }

    /**
     * [NEW] Ambil Detail Barang Lengkap (untuk halaman Detail)
     */
    public function getProductByIdWithDetails($id) {
        $this->query("SELECT p.*, 
                             k.nama_kategori, m.nama_merek, s.nama_satuan,
                             (SELECT COALESCE(SUM(quantity), 0) FROM product_stock ps WHERE ps.product_id = p.product_id) as stok_saat_ini
                      FROM products p
                      LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
                      LEFT JOIN merek m ON p.merek_id = m.merek_id
                      LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                      WHERE p.product_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    public function getProductById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE product_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menghapus data produk (Master & Stok)
     */
    public function deleteProductById($id) {
        $this->db->beginTransaction();
        try {
            // Hapus stok dulu (Foreign Key)
            $this->query("DELETE FROM product_stock WHERE product_id = :id");
            $this->bind('id', $id, PDO::PARAM_INT);
            $this->execute();

            // Hapus master
            $this->query("DELETE FROM products WHERE product_id = :id");
            $this->bind('id', $id, PDO::PARAM_INT);
            $this->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * [DROPDOWN] Daftar Produk Simple (Bisa difilter per kategori)
     */
    public function getAllProductsList($kategori_id = null) {
        $sql = "SELECT product_id, kode_barang, nama_barang, lacak_lot_serial 
                FROM " . $this->table;
        
        if ($kategori_id != null) {
            $sql .= " WHERE kategori_id = :kid";
        }
        
        $sql .= " ORDER BY nama_barang ASC";
        
        $this->query($sql);
        
        if ($kategori_id != null) {
            $this->bind('kid', $kategori_id);
        }
        
        return $this->resultSet();
    }

    /**
     * [BARANG KELUAR] Ambil Stok Tersedia + FEFO (First Expired First Out)
     */
    public function getAvailableStockForProduct($productId) {
        $this->query("SELECT 
                        ps.stock_id, ps.quantity, ps.lot_number, ps.exp_date,
                        l.kode_lokasi, l.nama_rak
                      FROM product_stock ps
                      LEFT JOIN lokasi l ON ps.lokasi_id = l.lokasi_id
                      LEFT JOIN status_barang sb ON ps.status_id = sb.status_id
                      WHERE ps.product_id = :pid AND sb.nama_status = 'Tersedia' AND ps.quantity > 0
                      ORDER BY ps.exp_date ASC, ps.stock_id ASC");
        
        $this->bind('pid', $productId);
        return $this->resultSet();
    }

    /**
     * [PEMINJAMAN] Ambil produk yang boleh dipinjam
     */
    public function getProductsForLoan() {
        $this->query("SELECT p.product_id, p.kode_barang, p.nama_barang, p.deskripsi, k.nama_kategori
                      FROM products p LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
                      WHERE p.bisa_dipinjam = 1 ORDER BY p.nama_barang ASC");
        return $this->resultSet();
    }

    /**
     * [STAFF] Cari Lokasi Barang
     */
    public function findStockLocationsByName($search) {
        $this->query("SELECT p.nama_barang, p.kode_barang, ps.quantity, ps.lot_number, ps.exp_date, l.kode_lokasi, l.nama_rak
                      FROM product_stock ps
                      JOIN products p ON ps.product_id = p.product_id
                      JOIN lokasi l ON ps.lokasi_id = l.lokasi_id
                      JOIN status_barang sb ON ps.status_id = sb.status_id
                      WHERE (p.nama_barang LIKE :search OR p.kode_barang = :search_plain)
                        AND sb.nama_status = 'Tersedia' AND ps.quantity > 0
                      ORDER BY l.kode_lokasi ASC");
        $this->bind('search', '%' . $search . '%');
        $this->bind('search_plain', $search);
        return $this->resultSet();
    }

    /**
     * [DASHBOARD] Hitung Stok Menipis
     */
    public function getJumlahStokMenipis() {
        $this->query("SELECT COUNT(p.product_id) as total
                      FROM products p
                      LEFT JOIN (
                          SELECT product_id, SUM(quantity) as total_stok
                          FROM product_stock
                          WHERE status_id = (SELECT status_id FROM status_barang WHERE nama_status = 'Tersedia' LIMIT 1)
                          GROUP BY product_id
                      ) as stok ON p.product_id = stok.product_id
                      WHERE COALESCE(stok.total_stok, 0) < p.stok_minimum");
        return $this->single()['total'];
    }

    public function deleteBulkProducts($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->query("DELETE FROM product_stock WHERE product_id IN ($placeholders)");
        foreach ($ids as $k => $id) { $this->bind(($k+1), $id); }
        $this->execute();

        $this->query("DELETE FROM products WHERE product_id IN ($placeholders)");
        foreach ($ids as $k => $id) { $this->bind(($k+1), $id); }
        return $this->execute();
    }

    /**
     * [AJAX] Mengambil daftar produk detail berdasarkan Kategori ID
     */
    public function getProductsByCategoryId($kategori_id) {
        $sql = "SELECT p.kode_barang, p.nama_barang, m.nama_merek, 
                       (SELECT l.kode_lokasi FROM product_stock ps 
                        JOIN lokasi l ON ps.lokasi_id = l.lokasi_id 
                        WHERE ps.product_id = p.product_id LIMIT 1) as lokasi_utama
                FROM products p
                LEFT JOIN merek m ON p.merek_id = m.merek_id
                WHERE p.kategori_id = :kid
                ORDER BY p.nama_barang ASC";
        
        $this->query($sql);
        $this->bind('kid', $kategori_id);
        return $this->resultSet();
    }

    /**
     * [EXPORT] Mengambil semua data produk lengkap untuk Export CSV
     * (Tanpa paginasi, join lengkap untuk label yang bisa dibaca manusia)
     */
    public function getAllProductsForExport() {
        $this->query("SELECT 
                        p.kode_barang, 
                        p.nama_barang,
                        COALESCE(k.nama_kategori, '-') as nama_kategori, 
                        COALESCE(m.nama_merek, '-') as nama_merek, 
                        COALESCE(s.nama_satuan, '-') as nama_satuan,
                        -- Hitung total stok fisik dari tabel stock
                        (SELECT COALESCE(SUM(ps.quantity), 0) FROM product_stock ps WHERE ps.product_id = p.product_id) as total_stok,
                        p.stok_minimum,
                        p.deskripsi
                      FROM products p
                      LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
                      LEFT JOIN merek m ON p.merek_id = m.merek_id
                      LEFT JOIN satuan s ON p.satuan_id = s.satuan_id
                      ORDER BY p.nama_barang ASC");
        return $this->resultSet();
    }

    /**
     * [IMPORT] Smart Import Barang dari CSV
     * Menerima array data baris per baris.
     */
    public function importBarangSmart($rows) {
        $summary = ['success' => 0, 'failed' => 0];

        foreach ($rows as $row) {
            try {
                // Validasi minimal
                if (count($row) < 2) continue;

                $kode     = trim($row[0]);
                $nama     = trim($row[1]);
                $kategori = !empty($row[2]) ? trim($row[2]) : 'Umum';
                $merek    = !empty($row[3]) ? trim($row[3]) : 'Tanpa Merek';
                $satuan   = !empty($row[4]) ? trim($row[4]) : 'Pcs';
                $stok     = !empty($row[5]) ? (int)$row[5] : 0;
                $lokasi   = !empty($row[6]) ? trim($row[6]) : 'GUDANG';

                // Skip header
                if (strtolower($kode) == 'kode barang' || strtolower($kode) == 'kode') continue;

                // 1. Cek Duplikat
                $this->query("SELECT product_id FROM products WHERE kode_barang = :kode");
                $this->bind('kode', $kode);
                $existing = $this->single();

                if ($existing) {
                    $summary['failed']++;
                    continue; 
                }

                // 2. Resolve ID
                $kategori_id = $this->getOrInsertAttribute('kategori', 'nama_kategori', 'kategori_id', $kategori);
                $merek_id    = $this->getOrInsertAttribute('merek', 'nama_merek', 'merek_id', $merek);
                $satuan_id   = $this->getOrInsertAttribute('satuan', 'nama_satuan', 'satuan_id', $satuan);
                
                $lokasi_id   = $this->getOrInsertAttribute('lokasi', 'kode_lokasi', 'lokasi_id', $lokasi, [
                    'nama_rak' => 'Rak Import ' . $lokasi, 
                    'zona' => 'Zona Import'
                ]);

                // 3. Data Barang
                $dataBarang = [
                    'kode_barang' => $kode,
                    'nama_barang' => $nama,
                    'foto_barang' => null, // Import CSV belum support foto
                    'deskripsi'   => 'Hasil Import CSV',
                    'kategori_id' => $kategori_id,
                    'merek_id'    => $merek_id,
                    'satuan_id'   => $satuan_id,
                    'stok_minimum'=> 10, 
                    'bisa_dipinjam'=> 0,
                    'lacak_lot_serial'=> 0,
                    'stok_awal'   => $stok,
                    'lokasi_id'   => $lokasi_id,
                    'status_id'   => 10 
                ];
                
                $this->createProduct($dataBarang);
                $summary['success']++;

            } catch (Exception $e) {
                $summary['failed']++;
            }
        }
        return $summary;
    }

    /**
     * Helper Privat: Cari ID atribut, jika tidak ada -> INSERT baru.
     */
    private function getOrInsertAttribute($table, $nameCol, $idCol, $value, $extraData = []) {
        $this->query("SELECT $idCol FROM $table WHERE $nameCol = :val");
        $this->bind('val', $value);
        $result = $this->single();

        if ($result) {
            return $result[$idCol];
        } else {
            $cols = $nameCol;
            $vals = ":val";
            foreach ($extraData as $k => $v) {
                $cols .= ", $k";
                $vals .= ", '$v'";
            }
            $this->query("INSERT INTO $table ($cols) VALUES ($vals)");
            $this->bind('val', $value);
            $this->execute();
            return $this->db->lastInsertId();
        }
    }

    /**
     * [AUTO-GENERATE] Membuat Kode Barang Otomatis
     */
    public function generateNextCode($prefix = 'BRG') {
        $this->query("SELECT kode_barang FROM " . $this->table . " 
                      WHERE kode_barang LIKE :prefix 
                      ORDER BY LENGTH(kode_barang) DESC, kode_barang DESC 
                      LIMIT 1");
        
        $this->bind('prefix', $prefix . '-%');
        $lastCode = $this->single();

        if ($lastCode) {
            $lastStr = $lastCode['kode_barang'];
            $numberOnly = str_replace($prefix . '-', '', $lastStr);
            $number = (int)$numberOnly;
            $newNumber = $number + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
}