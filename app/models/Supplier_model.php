<?php

class Supplier_model extends Model {

    private $table = 'suppliers';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total jumlah supplier (dengan filter pencarian)
     */
    public function getTotalSupplierCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_supplier LIKE :search OR kontak_person LIKE :search OR email LIKE :search";
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
     * Mengambil data supplier dengan paginasi (dengan filter pencarian)
     */
    public function getSuppliersPaginated($limit, $offset, $search = '') {
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE nama_supplier LIKE :search OR kontak_person LIKE :search OR email LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nama_supplier ASC LIMIT :limit OFFSET :offset";
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
     * Mengambil satu data supplier berdasarkan ID
     */
    public function getSupplierById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE supplier_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menyimpan data supplier baru ke database
     */
    public function createSupplier($data) {
        $this->query("INSERT INTO " . $this->table . " (nama_supplier, kontak_person, telepon, email, alamat) 
                      VALUES (:nama_supplier, :kontak_person, :telepon, :email, :alamat)");

        $this->bind('nama_supplier', $data['nama_supplier']);
        $this->bind('kontak_person', $data['kontak_person']);
        $this->bind('telepon', $data['telepon']);
        $this->bind('email', $data['email']);
        $this->bind('alamat', $data['alamat']);

        return $this->execute();
    }

    /**
     * Mengupdate data supplier di database
     */
    public function updateSupplier($data) {
        $this->query("UPDATE " . $this->table . " SET 
                        nama_supplier = :nama_supplier, 
                        kontak_person = :kontak_person, 
                        telepon = :telepon, 
                        email = :email, 
                        alamat = :alamat 
                      WHERE supplier_id = :supplier_id");

        $this->bind('nama_supplier', $data['nama_supplier']);
        $this->bind('kontak_person', $data['kontak_person']);
        $this->bind('telepon', $data['telepon']);
        $this->bind('email', $data['email']);
        $this->bind('alamat', $data['alamat']);
        $this->bind('supplier_id', $data['supplier_id'], PDO::PARAM_INT);

        return $this->execute();
    }

    /**
     * Menghapus data supplier dari database berdasarkan ID
     */
    public function deleteSupplierById($id) {
        $this->query("DELETE FROM " . $this->table . " WHERE supplier_id = :id");
        $this->bind('id', $id, PDO::PARAM_INT);
        return $this->execute();
    }
    // (Tambahkan ini di mana saja di dalam class Supplier_model)
    public function getAllSuppliers() {
        $this->query("SELECT * FROM " . $this->table . " ORDER BY nama_supplier ASC");
        return $this->resultSet();
    }
}