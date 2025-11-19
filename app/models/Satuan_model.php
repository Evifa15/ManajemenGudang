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
    public function createSatuan($data) {
        $this->query("INSERT INTO " . $this->table . " (nama_satuan, singkatan) 
                      VALUES (:nama_satuan, :singkatan)");

        $this->bind('nama_satuan', $data['nama_satuan']);
        $this->bind('singkatan', $data['singkatan']);

        return $this->execute();
    }

    /**
     * Mengupdate data satuan di database
     */
    public function updateSatuan($data) {
        $this->query("UPDATE " . $this->table . " SET 
                        nama_satuan = :nama_satuan,
                        singkatan = :singkatan
                      WHERE satuan_id = :satuan_id");

        $this->bind('nama_satuan', $data['nama_satuan']);
        $this->bind('singkatan', $data['singkatan']);
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
    public function getAllSatuan() {
        $this->query("SELECT * FROM " . $this->table . " ORDER BY nama_satuan ASC");
        return $this->resultSet();
    }
}