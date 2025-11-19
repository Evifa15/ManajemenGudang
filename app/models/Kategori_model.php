<?php

class Kategori_model extends Model {

    private $table = 'kategori';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total jumlah kategori (dengan filter pencarian)
     */
    public function getTotalKategoriCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_kategori LIKE :search OR deskripsi LIKE :search";
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
     * Mengambil data kategori dengan paginasi (dengan filter pencarian)
     */
    public function getKategoriPaginated($limit, $offset, $search = '') {
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_kategori LIKE :search OR deskripsi LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nama_kategori ASC LIMIT :limit OFFSET :offset";
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
     * Mengambil satu data kategori berdasarkan ID
     */
    public function getKategoriById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE kategori_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menyimpan data kategori baru ke database
     */
    public function createKategori($data) {
        $this->query("INSERT INTO " . $this->table . " (nama_kategori, deskripsi) 
                      VALUES (:nama_kategori, :deskripsi)");

        $this->bind('nama_kategori', $data['nama_kategori']);
        $this->bind('deskripsi', $data['deskripsi']);

        return $this->execute();
    }

    /**
     * Mengupdate data kategori di database
     */
    public function updateKategori($data) {
        $this->query("UPDATE " . $this->table . " SET 
                        nama_kategori = :nama_kategori, 
                        deskripsi = :deskripsi 
                      WHERE kategori_id = :kategori_id");

        $this->bind('nama_kategori', $data['nama_kategori']);
        $this->bind('deskripsi', $data['deskripsi']);
        $this->bind('kategori_id', $data['kategori_id'], PDO::PARAM_INT);

        return $this->execute();
    }

    /**
     * Menghapus data kategori dari database berdasarkan ID
     */
    public function deleteKategoriById($id) {
        $this->query("DELETE FROM " . $this->table . " WHERE kategori_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->execute();
    }
    // (Tambahkan ini di mana saja di dalam class Kategori_model)
    public function getAllKategori() {
        $this->query("SELECT * FROM " . $this->table . " ORDER BY nama_kategori ASC");
        return $this->resultSet();
    }
}