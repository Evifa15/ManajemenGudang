<?php

class User_model extends Model {

    private $table = 'users';

    public function __construct() {
        parent::__construct(); 
    }

    /**
     * Validasi Login dengan Logika Bertingkat & Lockout
     * Mengembalikan Array: ['status' => '...', 'data' => ...]
     */
    public function checkLogin($email, $password) {
        // 1. Cari User berdasarkan Email
        $this->query("SELECT * FROM " . $this->table . " WHERE email = :email");
        $this->bind('email', $email);
        $user = $this->single();

        // KASUS A: Email Tidak Ditemukan
        if (!$user) {
            return ['status' => 'EMAIL_NOT_FOUND'];
        }

        // KASUS B: Akun Terkunci
        if ($user['is_locked'] == 1) {
            return ['status' => 'ACCOUNT_LOCKED'];
        }

        // 2. Verifikasi Password
        if (password_verify($password, $user['password'])) {
            // SUKSES: Reset percobaan login jika berhasil
            $this->resetLoginAttempts($user['user_id']);
            unset($user['password']); // Hapus password dari array session
            return ['status' => 'SUCCESS', 'data' => $user];
        } else {
            // GAGAL: Password Salah -> Increment percobaan
            $attempts = $user['login_attempts'] + 1;
            $this->updateLoginAttempts($user['user_id'], $attempts);

            // Cek apakah sudah 5 kali salah?
            if ($attempts >= 5) {
                $this->lockAccount($user['user_id']);
                return ['status' => 'LOCKED_NOW']; // Baru saja terkunci
            }

            return ['status' => 'PASSWORD_WRONG', 'attempts' => $attempts];
        }
    }

    // --- HELPER METHODS BARU ---

    private function updateLoginAttempts($userId, $count) {
        $this->query("UPDATE " . $this->table . " SET login_attempts = :count WHERE user_id = :id");
        $this->bind('count', $count);
        $this->bind('id', $userId);
        $this->execute();
    }

    private function resetLoginAttempts($userId) {
        $this->query("UPDATE " . $this->table . " SET login_attempts = 0 WHERE user_id = :id");
        $this->bind('id', $userId);
        $this->execute();
    }

    private function lockAccount($userId) {
        $this->query("UPDATE " . $this->table . " SET is_locked = 1 WHERE user_id = :id");
        $this->bind('id', $userId);
        $this->execute();
    }

    /**
     * Mengambil semua data user dari database.
     */
    public function getAllUsers() {
        // REVISI: status_login DIHAPUS dari SELECT
        $this->query("SELECT user_id, email, nama_lengkap, role FROM users");
        return $this->resultSet();
    }

    /**
     * Mengambil satu data user berdasarkan ID.
     */
    public function getUserById($id) {
        $this->query("SELECT * FROM " . $this->table . " WHERE user_id = :id");
        $this->bind('id', $id);
        return $this->single();
    }

    /**
     * Menyimpan data user baru ke database.
     */
    public function createUser($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // REVISI: status_login DIHAPUS dari INSERT
        $this->query("INSERT INTO users (nama_lengkap, email, password, role) 
                      VALUES (:nama_lengkap, :email, :password, :role)");

        $this->bind('nama_lengkap', $data['nama_lengkap']);
        $this->bind('email', $data['email']);
        $this->bind('password', $hashedPassword);
        $this->bind('role', $data['role']);

        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Mengupdate data user di database.
     */
    public function updateUser($data) {
        
        $isPasswordEmpty = empty($data['password']);

        // REVISI: status_login DIHAPUS dari UPDATE
        $query = "UPDATE users SET 
                    nama_lengkap = :nama_lengkap, 
                    email = :email, 
                    role = :role"; 
        
        if (!$isPasswordEmpty) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE user_id = :user_id";
        
        $this->query($query);

        $this->bind('nama_lengkap', $data['nama_lengkap']);
        $this->bind('email', $data['email']);
        $this->bind('role', $data['role']);
        $this->bind('user_id', $data['user_id']);

        if (!$isPasswordEmpty) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->bind('password', $hashedPassword);
        }

        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Menghapus data user.
     */
    public function deleteUserById($id) {
        $this->query("DELETE FROM users WHERE user_id = :id");
        $this->bind('id', $id);
        if ($this->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Menghitung total jumlah pengguna (REVISI: Hapus parameter status)
     */
    public function getTotalUserCount($search = '', $role = '') {
        $sql = "SELECT COUNT(*) as total FROM users";
        $params = [];
        $whereClauses = [];

        if (!empty($search)) {
            $whereClauses[] = "(nama_lengkap LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($role)) {
            $whereClauses[] = "role = :role";
            $params[':role'] = $role;
        }

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
     * Mengambil data user dengan paginasi (REVISI: Hapus parameter status & kolom status_login)
     */
    public function getUsersPaginated($limit, $offset, $search = '', $role = '') {
        // REVISI PENTING: Hapus status_login dari sini!
        $sql = "SELECT user_id, email, nama_lengkap, role FROM users";
        $params = [];
        $whereClauses = [];

        if (!empty($search)) {
            $whereClauses[] = "(nama_lengkap LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($role)) {
            $whereClauses[] = "role = :role";
            $params[':role'] = $role;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " ORDER BY nama_lengkap ASC LIMIT :limit OFFSET :offset";
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
     * Mengimpor pengguna via CSV (VERSI SMART: Skip Duplikat)
     * Tidak membatalkan semua jika ada satu yang gagal.
     */
    public function importUsers($users) {
        $summary = [
            'success' => 0,
            'skipped' => 0,
            'errors'  => 0,
            'skipped_list' => [] // Menyimpan email yang duplikat
        ];

        // Query disiapkan sekali di luar loop agar efisien
        $this->query("INSERT INTO users (nama_lengkap, email, password, role) 
                      VALUES (:nama_lengkap, :email, :password, :role)");

        foreach ($users as $user) {
            try {
                // Hash password
                $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

                $this->bind('nama_lengkap', $user['nama_lengkap']);
                $this->bind('email', $user['email']);
                $this->bind('password', $hashedPassword);
                $this->bind('role', $user['role']);
                
                // Coba Eksekusi per satu baris
                $this->execute();
                $summary['success']++; // Jika berhasil, tambah counter sukses

            } catch (PDOException $e) {
                // Cek Kode Error 1062 (Duplicate Entry / Email Kembar)
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                    // Jangan hentikan proses, cukup catat sebagai 'skipped'
                    $summary['skipped']++;
                    $summary['skipped_list'][] = $user['email'];
                } else {
                    // Error lain (misal koneksi putus), catat error
                    $summary['errors']++;
                }
            }
        }

        // Kembalikan laporan lengkap ke Controller
        return $summary;
    }

    /**
     * Mengambil data gabungan profil.
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
     * Update profil user (FIXED: Eksekusi query profil sebelum query user)
     */
    public function updateProfile($data) {
        // 1. QUERY PERTAMA: Update Tabel Profil (Foto, Alamat, dll)
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

        // --- INI YANG SEBELUMNYA HILANG! ---
        // Kita harus eksekusi query profil dulu sebelum menyiapkan query nama.
        $this->execute(); 
        // -----------------------------------

        // 2. QUERY KEDUA: Update Tabel Users (Nama Lengkap)
        $this->query("UPDATE users SET nama_lengkap = :nama_lengkap WHERE user_id = :user_id");
        $this->bind('nama_lengkap', $data['nama_lengkap']);
        $this->bind('user_id', $data['user_id'], PDO::PARAM_INT);
        
        return $this->execute(); 
    }

    /**
     * Ganti password mandiri.
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        $this->query("SELECT password FROM users WHERE user_id = :user_id");
        $this->bind('user_id', $userId, PDO::PARAM_INT);
        $user = $this->single();

        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan.'];
        }

        if (!password_verify($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Password lama Anda salah!'];
        }

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
     * Helper filter role.
     */
    public function getUsersByRole($role) {
        $this->query("SELECT user_id, nama_lengkap FROM " . $this->table . " WHERE role = :role ORDER BY nama_lengkap ASC");
        $this->bind('role', $role);
        return $this->resultSet();
    }
    /**
     * Menghapus banyak user sekaligus (Bulk Delete).
     * @param array $ids Array berisi ID user yang akan dihapus
     */
    public function deleteBulkUsers($ids) {
        // Buat placeholder tanda tanya (?) sebanyak jumlah ID. Contoh: (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "DELETE FROM " . $this->table . " WHERE user_id IN ($placeholders)";
        
        $this->query($sql);
        
        // Bind setiap ID ke placeholder
        foreach ($ids as $k => $id) {
            $this->bind(($k + 1), $id);
        }
        
        return $this->execute();
    }
    
}