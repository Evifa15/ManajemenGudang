<?php
/*
 * Base Model (Induk)
 * Tugas: Mengelola koneksi Database (PDO) dan helper query
 */
class Model {
    protected $db;   // Database Handler
    protected $stmt; // Statement

    public function __construct() {
        // Ambil info DB dari config.php
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $options = [
            PDO::ATTR_PERSISTENT => true, // Koneksi 'persistent'
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Mode error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Default fetch
        ];

        // Buat koneksi PDO
        try {
            $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Koneksi Database Gagal: ' . $e->getMessage());
        }
    }

    // --- Ini adalah 5 helper yang akan dipakai 'User_model' ---

    // 1. Menyiapkan query
    public function query($sql) {
        $this->stmt = $this->db->prepare($sql);
    }

    // 2. Binding (mencegah SQL Injection)
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // 3. Eksekusi
    public function execute() {
        return $this->stmt->execute();
    }

    // 4. Ambil 1 baris hasil
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // 5. Ambil semua hasil
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
}