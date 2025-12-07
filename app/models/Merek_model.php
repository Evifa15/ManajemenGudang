<?php

class Merek_model extends Model {

    private $table = 'merek';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total jumlah merek (dengan filter pencarian)
     */
    public function getTotalMerekCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_merek LIKE :search";
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
     * Mengambil data merek dengan paginasi (dengan filter pencarian)
     */
    public function getMerekPaginated($limit, $offset, $search = '') {
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_merek LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nama_merek ASC LIMIT :limit OFFSET :offset";
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
     * Mengambil satu data merek berdasarkan ID
     */
    public function getMerekById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE merek_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menyimpan data merek baru ke database
     */
    public function createMerek($data) {
    $this->query("INSERT INTO merek (nama_merek, deskripsi, status) VALUES (:nama_merek, :deskripsi, :status)");
    $this->bind('nama_merek', $data['nama_merek']);
    $this->bind('deskripsi', $data['deskripsi']);
    $this->bind('status', $data['status']); 
    return $this->execute();
    }

    /**
     * Mengupdate data merek di database
     */
    public function updateMerek($data) {
    $this->query("UPDATE merek SET 
                    nama_merek = :nama_merek,
                    deskripsi = :deskripsi,
                    status = :status
                  WHERE merek_id = :merek_id");

    $this->bind('nama_merek', $data['nama_merek']);
    $this->bind('deskripsi', $data['deskripsi']);
    $this->bind('status', $data['status']); 
    $this->bind('merek_id', $data['merek_id'], PDO::PARAM_INT);
    return $this->execute();
}

    /**
     * Menghapus data merek dari database berdasarkan ID
     */
    public function deleteMerekById($id) {
        $this->query("DELETE FROM " . $this->table . " WHERE merek_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->execute();
    }

    public function getAllMerek() {
        $this->query("SELECT * FROM " . $this->table . " ORDER BY nama_merek ASC");
        return $this->resultSet();
    }

    /**
     * Menghapus banyak merek sekaligus (Bulk Delete)
     */
    public function deleteBulkMerek($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $this->query("DELETE FROM merek WHERE merek_id IN ($placeholders)");
        
        foreach ($ids as $k => $id) {
            $this->bind(($k + 1), $id);
        }
        
        return $this->execute();
    }

    /**
     * Fungsi ini khusus dipanggil saat Input Barang Baru
     */
    public function getActiveMerek() {
        $this->query("SELECT * FROM merek WHERE status = 'Aktif' ORDER BY nama_merek ASC");
        return $this->resultSet();
    }
}