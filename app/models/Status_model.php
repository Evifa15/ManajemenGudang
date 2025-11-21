<?php

class Status_model extends Model {

    private $table = 'status_barang';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total jumlah status (dengan filter pencarian)
     */
    public function getTotalStatusCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_status LIKE :search OR deskripsi LIKE :search";
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
     * Mengambil data status dengan paginasi (dengan filter pencarian)
     */
    public function getStatusPaginated($limit, $offset, $search = '') {
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_status LIKE :search OR deskripsi LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nama_status ASC LIMIT :limit OFFSET :offset";
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
     * Mengambil satu data status berdasarkan ID
     */
    public function getStatusById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE status_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menyimpan data status baru ke database
     */
    public function createStatus($data) {
        $this->query("INSERT INTO " . $this->table . " (nama_status, deskripsi) 
                      VALUES (:nama_status, :deskripsi)");

        $this->bind('nama_status', $data['nama_status']);
        $this->bind('deskripsi', $data['deskripsi']);

        return $this->execute();
    }

    /**
     * Mengupdate data status di database
     */
    public function updateStatus($data) {
        $this->query("UPDATE " . $this->table . " SET 
                        nama_status = :nama_status,
                        deskripsi = :deskripsi
                      WHERE status_id = :status_id");

        $this->bind('nama_status', $data['nama_status']);
        $this->bind('deskripsi', $data['deskripsi']);
        $this->bind('status_id', $data['status_id'], PDO::PARAM_INT);

        return $this->execute();
    }

    /**
     * Menghapus data status dari database berdasarkan ID
     */
    public function deleteStatusById($id) {
        $this->query("DELETE FROM " . $this->table . " WHERE status_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->execute();
    }

    /**
     * Mengambil semua status (untuk dropdown)
     */
    public function getAllStatus() {
        $this->query("SELECT * FROM " . $this->table . " ORDER BY nama_status ASC");
        return $this->resultSet();
    }

    /**
     * Mengambil ID status berdasarkan nama (Penting untuk logika 'Tersedia')
     */
    public function getStatusIdByName($nama) {
        $this->query("SELECT status_id FROM " . $this->table . " WHERE nama_status = :nama LIMIT 1");
        $this->bind('nama', $nama);
        return $this->single();
    }

    /**
     * Menghapus banyak status sekaligus (Bulk Delete)
     */
    public function deleteBulkStatus($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $this->query("DELETE FROM " . $this->table . " WHERE status_id IN ($placeholders)");
        
        foreach ($ids as $k => $id) {
            $this->bind(($k + 1), $id);
        }
        
        return $this->execute();
    }
}