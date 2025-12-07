<?php

class Satuan_model extends Model {

    private $table = 'satuan';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total jumlah satuan (dengan filter pencarian)
     */
    public function getTotalSatuanCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_satuan LIKE :search OR singkatan LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }

        $result = $this->single();
        return $result['total'];
    }

    /**
     * Mengambil data satuan dengan paginasi (dengan filter pencarian)
     */
    public function getSatuanPaginated($limit, $offset, $search = '') {
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_satuan LIKE :search OR singkatan LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nama_satuan ASC LIMIT :limit OFFSET :offset";
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
     * Mengambil satu data satuan berdasarkan ID
     */
    public function getSatuanById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE satuan_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menyimpan data satuan baru ke database
     */
    /**
     * Menyimpan data satuan baru ke database
     */
    public function createSatuan($data) {
        // Update Query: Tambahkan deskripsi
        $this->query("INSERT INTO " . $this->table . " (nama_satuan, singkatan, deskripsi) 
                      VALUES (:nama_satuan, :singkatan, :deskripsi)");

        $this->bind('nama_satuan', $data['nama_satuan']);
        $this->bind('singkatan', $data['singkatan']);
        $this->bind('deskripsi', $data['deskripsi']); // Bind parameter

        return $this->execute();
    }

    /**
     * Mengupdate data satuan di database
     */
    public function updateSatuan($data) {
        // Update Query: Tambahkan deskripsi
        $this->query("UPDATE " . $this->table . " SET 
                        nama_satuan = :nama_satuan,
                        singkatan = :singkatan,
                        deskripsi = :deskripsi
                      WHERE satuan_id = :satuan_id");

        $this->bind('nama_satuan', $data['nama_satuan']);
        $this->bind('singkatan', $data['singkatan']);
        $this->bind('deskripsi', $data['deskripsi']); // Bind parameter
        $this->bind('satuan_id', $data['satuan_id'], PDO::PARAM_INT);

        return $this->execute();
    }
    


    
    /**
     * Menghapus data satuan dari database berdasarkan ID
     */
    public function deleteSatuanById($id) {
        $this->query("DELETE FROM " . $this->table . " WHERE satuan_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->execute();
    }
    // (Tambahkan ini di mana saja di dalam class Satuan_model)
    // Tambahkan fungsi ini di dalam class Satuan_model
    public function getAllSatuan() {
        $this->query('SELECT * FROM satuan ORDER BY nama_satuan ASC');
        return $this->resultSet();
    }
    public function deleteBulkSatuan($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->query("DELETE FROM satuan WHERE satuan_id IN ($placeholders)");
        foreach ($ids as $k => $id) { $this->bind(($k+1), $id); }
        return $this->execute();
    }
}