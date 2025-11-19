<?php
// (Asumsi) Anda punya file konfigurasi database di:
// require_once '../app/config/database.php'; 
// Dan file Model dasar di:
// require_once '../app/core/Model.php';

class User_model extends Model { // 'Model' adalah class dasar dari core/Model.php

    private $table = 'users';

    public function __construct() {
        // (Asumsi) Class Model dasar Anda akan 
        // meng-handle koneksi database ($this->db)
        parent::__construct(); 
    }

    /**
     * Memvalidasi login pengguna.
     * * @param string $email Email dari form
     * @param string $password Password mentah (plain text) dari form
     * @return array|false Data user jika berhasil, false jika gagal
     */
    public function checkLogin($email, $password) {
    
    $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
    
    // Panggil method dari class Model (induknya)
    $this->query($query);
    $this->bind('email', $email);
    
    $user = $this->single(); // <-- Ini baris 28 yang sudah diperbaiki

    if ($user) {
        // 2. User ditemukan. Verifikasi password.
        $hashedPassword = $user['password'];

        if (password_verify($password, $hashedPassword)) {
            // 3. Password cocok!
            // Jangan kirim password hash ke controller
            unset($user['password']); 
            return $user;
        }
    }

    // 4. Jika user tidak ditemukan ATAU password salah
    return false;
}

    /**
     * Mengambil semua data user dari database.
     */
    public function getAllUsers() {
        // Kita tidak SELECT *, tapi kolom spesifik
        $this->query("SELECT user_id, email, nama_lengkap, role, status_login FROM users");
        return $this->resultSet();
    }

    /**
     * Mengambil satu data user berdasarkan ID.
     * (Ini akan kita pakai untuk 'forceChangePassword' dan 'edit')
     */
    public function getUserById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE user_id = :id");
        $this->bind('id', $id);
        return $this->single();
    }
    /**
     * Menyimpan data user baru ke database.
     * @param array $data Data user dari form
     * @return bool True jika berhasil, false jika gagal
     */
    public function createUser($data) {
        // 1. Hash password sebelum disimpan!
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // 2. Siapkan query
        $this->query("INSERT INTO users (nama_lengkap, email, password, role, status_login) 
                      VALUES (:nama_lengkap, :email, :password, :role, :status_login)");

        // 3. Binding data
        $this->bind('nama_lengkap', $data['nama_lengkap']);
        $this->bind('email', $data['email']);
        $this->bind('password', $hashedPassword);
        $this->bind('role', $data['role']);
        $this->bind('status_login', $data['status_login']);

        // 4. Eksekusi
        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Mengupdate data user di database.
     * @param array $data Data user dari form
     * @return bool True jika berhasil, false jika gagal
     */
    public function updateUser($data) {
        
        // Cek apakah password baru diisi
        $isPasswordEmpty = empty($data['password']);

        // --- Bangun Query SQL ---
        $query = "UPDATE users SET 
                    nama_lengkap = :nama_lengkap, 
                    email = :email, 
                    role = :role, 
                    status_login = :status_login";
        
        // Jika password diisi, tambahkan ke query
        if (!$isPasswordEmpty) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE user_id = :user_id";
        
        // Siapkan query
        $this->query($query);

        // --- Binding Data ---
        $this->bind('nama_lengkap', $data['nama_lengkap']);
        $this->bind('email', $data['email']);
        $this->bind('role', $data['role']);
        $this->bind('status_login', $data['status_login']);
        $this->bind('user_id', $data['user_id']);

        // Jika password diisi, hash dan bind
        if (!$isPasswordEmpty) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->bind('password', $hashedPassword);
        }

        // --- Eksekusi ---
        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Menghapus data user dari database berdasarkan ID.
     * @param int $id ID user yang akan dihapus
     * @return bool True jika berhasil, false jika gagal
     */
    public function deleteUserById($id) {
        // 1. Siapkan query
        $this->query("DELETE FROM users WHERE user_id = :id");

        // 2. Binding data
        $this->bind('id', $id);

        // 3. Eksekusi
        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Menghitung total jumlah pengguna (dengan filter search, role, status)
     */
    public function getTotalUserCount($search = '', $role = '', $status = '') {
        $sql = "SELECT COUNT(*) as total FROM users";
        $params = [];
        $whereClauses = [];

        // Tambahkan filter PENCARIAN
        if (!empty($search)) {
            $whereClauses[] = "(nama_lengkap LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Tambahkan filter ROLE
        if (!empty($role)) {
            $whereClauses[] = "role = :role";
            $params[':role'] = $role;
        }

        // Tambahkan filter STATUS
        if (!empty($status)) {
            $whereClauses[] = "status_login = :status";
            $params[':status'] = $status;
        }

        // Gabungkan semua WHERE clause
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $this->query($sql);

        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }

        $result = $this->single();
        return $result['total'];
    }

    /**
     * Mengambil data user dengan paginasi (dengan filter search, role, status)
     */
    public function getUsersPaginated($limit, $offset, $search = '', $role = '', $status = '') {
        $sql = "SELECT user_id, email, nama_lengkap, role, status_login FROM users";
        $params = [];
        $whereClauses = [];

        // Tambahkan filter PENCARIAN
        if (!empty($search)) {
            $whereClauses[] = "(nama_lengkap LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Tambahkan filter ROLE
        if (!empty($role)) {
            $whereClauses[] = "role = :role";
            $params[':role'] = $role;
        }

        // Tambahkan filter STATUS
        if (!empty($status)) {
            $whereClauses[] = "status_login = :status";
            $params[':status'] = $status;
        }

        // Gabungkan semua WHERE clause
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Tambahkan LIMIT dan OFFSET
        $sql .= " ORDER BY nama_lengkap ASC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $this->query($sql);

        // Bind semua parameter
        foreach ($params as $key => &$value) {
            $type = ($key == ':limit' || $key == ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->bind($key, $value, $type);
        }
        
        return $this->resultSet();
    }
    /**
     * Mengimpor banyak pengguna sekaligus menggunakan transaksi.
     * @param array $users Array berisi data pengguna
     * @return array Hasil import (sukses, gagal, error)
     */
    public function importUsers($users) {
        $successCount = 0;
        $failCount = 0;
        $failedEmails = [];

        // Mulai Transaksi Database
        // Ini memastikan 'SEMUA' atau 'TIDAK SAMA SEKALI'
        try {
            // $this->db adalah koneksi PDO dari Model Induk
            $this->db->beginTransaction();

            // Siapkan query sekali saja
            $this->query("INSERT INTO users (nama_lengkap, email, password, role, status_login) 
                          VALUES (:nama_lengkap, :email, :password, :role, :status_login)");

            foreach ($users as $user) {
                // Hash password acak yang di-generate oleh controller
                $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

                $this->bind('nama_lengkap', $user['nama_lengkap']);
                $this->bind('email', $user['email']);
                $this->bind('password', $hashedPassword);
                $this->bind('role', $user['role']);
                $this->bind('status_login', $user['status_login']);
                
                // Coba eksekusi
                if ($this->execute()) {
                    $successCount++;
                } else {
                    $failCount++;
                    $failedEmails[] = $user['email'];
                }
            }

            // Jika tidak ada error, 'kunci' (commit) semua perubahan
            $this->db->commit();
            return ['success' => $successCount, 'fail' => $failCount, 'failed_emails' => $failedEmails];

        } catch (Exception $e) {
            // Jika ada 1 saja error (misal: email duplikat),
            // 'batalkan' (rollBack) semua yang sudah di-insert
            $this->db->rollBack();
            return ['success' => 0, 'fail' => count($users), 'error' => $e->getMessage()];
        }
    }
    /**
     * Mengupdate password user dan mengaktifkan statusnya.
     * @param int $userId ID user
     * @param string $newPassword Password baru (plain text)
     * @return bool
     */
    public function updatePasswordAndActivate($userId, $newPassword) {
        // 1. Hash password baru
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // 2. Siapkan query
        // PERBAIKAN: $this.query() menjadi $this->query()
        $this->query("UPDATE " . $this->table . " SET 
                        password = :password, 
                        status_login = 'aktif' 
                      WHERE user_id = :user_id");

        // 3. Binding data
        // PERBAIKAN: $this.bind() menjadi $this->bind()
        $this->bind('password', $hashedPassword);
        $this->bind('user_id', $userId, PDO::PARAM_INT);

        // 4. Eksekusi
        // PERBAIKAN: $this.execute() menjadi $this->execute()
        return $this->execute();
    }
    /**
     * Mengambil data gabungan (users & user_profiles) berdasarkan user_id.
     */
    public function getJoinedUserProfile($userId) {
        $this->query("SELECT u.email, u.nama_lengkap, u.role,
                         up.* FROM users u
                      LEFT JOIN user_profiles up ON u.user_id = up.user_id
                      WHERE u.user_id = :user_id");
        $this->bind('user_id', $userId, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * Menyimpan atau Mengupdate data user_profiles.
     * Menggunakan 'INSERT ... ON DUPLICATE KEY UPDATE'
     * Ini akan otomatis membuat profil jika belum ada, atau mengupdatenya jika sudah ada.
     */
    public function updateProfile($data) {
        $this->query("INSERT INTO user_profiles 
                        (user_id, foto_profil, tempat_lahir, tanggal_lahir, agama, telepon, alamat, kota, provinsi, kode_pos)
                      VALUES 
                        (:user_id, :foto_profil, :tempat_lahir, :tanggal_lahir, :agama, :telepon, :alamat, :kota, :provinsi, :kode_pos)
                      ON DUPLICATE KEY UPDATE
                        foto_profil = :foto_profil,
                        tempat_lahir = :tempat_lahir,
                        tanggal_lahir = :tanggal_lahir,
                        agama = :agama,
                        telepon = :telepon,
                        alamat = :alamat,
                        kota = :kota,
                        provinsi = :provinsi,
                        kode_pos = :kode_pos");

        // Bind semua data
        $this->bind('user_id', $data['user_id'], PDO::PARAM_INT);
        $this->bind('foto_profil', $data['foto_profil']);
        $this->bind('tempat_lahir', $data['tempat_lahir']);
        $this->bind('tanggal_lahir', $data['tanggal_lahir']);
        $this->bind('agama', $data['agama']);
        $this->bind('telepon', $data['telepon']);
        $this->bind('alamat', $data['alamat']);
        $this->bind('kota', $data['kota']);
        $this->bind('provinsi', $data['provinsi']);
        $this->bind('kode_pos', $data['kode_pos']);

        // Juga update nama_lengkap di tabel 'users'
        $this->query("UPDATE users SET nama_lengkap = :nama_lengkap WHERE user_id = :user_id");
        $this->bind('nama_lengkap', $data['nama_lengkap']);
        $this->bind('user_id', $data['user_id'], PDO::PARAM_INT);
        $this->execute(); // Jalankan update nama

        return $this->execute(); // Jalankan update profil
    }

    /**
     * Mengganti password mandiri (dengan verifikasi password lama)
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        // 1. Ambil HANYA password hash dari user
        $this->query("SELECT password FROM users WHERE user_id = :user_id");
        $this->bind('user_id', $userId, PDO::PARAM_INT);
        $user = $this->single();

        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan.'];
        }

        // 2. Verifikasi password lama
        if (!password_verify($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Password lama Anda salah!'];
        }

        // 3. Hash dan update password baru
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->query("UPDATE users SET password = :new_password WHERE user_id = :user_id");
        $this->bind('new_password', $newHashedPassword);
        $this->bind('user_id', $userId, PDO::PARAM_INT);
        
        if ($this->execute()) {
            return ['success' => true, 'message' => 'Password berhasil diubah.'];
        } else {
            return ['success' => false, 'message' => 'Gagal mengupdate password di database.'];
        }
    }
    /**
     * Mengambil semua user dengan role tertentu (untuk filter)
     */
    public function getUsersByRole($role) {
        $this->query("SELECT user_id, nama_lengkap FROM " . $this->table . " WHERE role = :role ORDER BY nama_lengkap ASC");
        $this->bind('role', $role);
        return $this->resultSet();
    }
}