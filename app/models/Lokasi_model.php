<?php

class Lokasi_model extends Model {

    private $table = 'lokasi';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total jumlah lokasi (dengan filter pencarian)
     */
    public function getTotalLokasiCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE kode_lokasi LIKE :search OR nama_rak LIKE :search OR zona LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $this->query($sql); // PERBAIKAN: ->
        foreach ($params as $key => $value) {
            $this->bind($key, $value); // PERBAIKAN: ->
        }

        $result = $this->single(); // PERBAIKAN: ->
        return $result['total'];
    }

    /**
     * Mengambil data lokasi dengan paginasi (dengan filter pencarian)
     */
    public function getLokasiPaginated($limit, $offset, $search = '') {
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE kode_lokasi LIKE :search OR nama_rak LIKE :search OR zona LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY kode_lokasi ASC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $this->query($sql); // PERBAIKAN: ->

        foreach ($params as $key => &$value) {
            $type = ($key == ':limit' || $key == ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->bind($key, $value, $type); // PERBAIKAN: ->
        }
        
        return $this->resultSet(); // PERBAIKAN: ->
    }

    /**
     * Mengambil satu data lokasi berdasarkan ID
     */
    public function getLokasiById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE lokasi_id = :id"); // PERBAIKAN: ->
        $this->bind('id', $id, PDO::PARAM_INT); // PERBAIKAN: ->
        return $this->single(); // PERBAIKAN: ->
    }

    /**
     * Menyimpan data lokasi baru ke database
     */
    public function createLokasi($data) {
        $this->query("INSERT INTO " . $this->table . " 
                      (kode_lokasi, nama_rak, zona, deskripsi) 
                      VALUES (:kode_lokasi, :nama_rak, :zona, :deskripsi)");
        
        $this->bind('kode_lokasi', $data['kode_lokasi']);
        $this->bind('nama_rak', $data['nama_rak']);
        $this->bind('zona', $data['zona']);
        $this->bind('deskripsi', $data['deskripsi']);
        
        return $this->execute();
    }

    /**
     * Mengupdate data lokasi di database
     */
    public function updateLokasi($data) {
        $this->query("UPDATE " . $this->table . " SET 
                        kode_lokasi = :kode_lokasi,
                        nama_rak = :nama_rak,
                        zona = :zona,
                        deskripsi = :deskripsi
                      WHERE lokasi_id = :lokasi_id");

        $this->bind('kode_lokasi', $data['kode_lokasi']);
        $this->bind('nama_rak', $data['nama_rak']);
        $this->bind('zona', $data['zona']);
        $this->bind('deskripsi', $data['deskripsi']);
        $this->bind('lokasi_id', $data['lokasi_id']);
        
        return $this->execute();
    }

    // Tambahan: Fungsi Cek Kode Unik (biar tidak duplikat)
    public function checkKodeExists($kode) {
        $this->query("SELECT lokasi_id FROM " . $this->table . " WHERE kode_lokasi = :kode");
        $this->bind('kode', $kode);
        $this->execute();
        return $this->rowCount() > 0;
    }

    /**
     * Menghapus data lokasi dari database berdasarkan ID
     */
    public function deleteLokasiById($id) {
        $this->query("DELETE FROM " . $this->table . " WHERE lokasi_id = :id"); // PERBAIKAN: ->
        $this->bind('id', $id, PDO::PARAM_INT); // PERBAIKAN: ->
        return $this->execute(); // PERBAIKAN: ->
    }
    // (Tambahkan ini di mana saja di dalam class Lokasi_model)
    public function getAllLokasi() {
        $this->query("SELECT * FROM " . $this->table . " ORDER BY kode_lokasi ASC");
        return $this->resultSet();
    }
    public function deleteBulkLokasi($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->query("DELETE FROM lokasi WHERE lokasi_id IN ($placeholders)");
        foreach ($ids as $k => $id) { $this->bind(($k+1), $id); }
        return $this->execute();
    }
}