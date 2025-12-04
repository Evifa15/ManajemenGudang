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

    // 2. Verifikasi Password (TANPA LOGIKA LOCK)
    if (password_verify($password, $user['password'])) {
        unset($user['password']); 
        return ['status' => 'SUCCESS', 'data' => $user];
    } else {
        return ['status' => 'PASSWORD_WRONG'];
    }
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
    // Hapus '->db', gunakan method helper dari class Model
    $this->query("SELECT user_id, nama_lengkap, tanggal_lahir, email, role FROM users WHERE user_id = :id");
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
    // Cek apakah password diisi (diganti) atau tidak
    if (!empty($data['password'])) {
        $query = "UPDATE users SET nama_lengkap = :nama, tanggal_lahir = :tgl, email = :email, role = :role, password = :pass WHERE user_id = :id";
    } else {
        $query = "UPDATE users SET nama_lengkap = :nama, tanggal_lahir = :tgl, email = :email, role = :role WHERE user_id = :id";
    }

    // PERBAIKAN: Gunakan $this->query, bukan $this->db->query
    $this->query($query);
    
    // PERBAIKAN: Gunakan $this->bind
    $this->bind('nama', $data['nama_lengkap']);
    $this->bind('tgl', $data['tanggal_lahir']);
    $this->bind('email', $data['email']);
    $this->bind('role', $data['role']);
    $this->bind('id', $data['user_id']);

    if (!empty($data['password'])) {
        $this->bind('pass', password_hash($data['password'], PASSWORD_DEFAULT));
    }

    // PERBAIKAN: Gunakan $this->execute
    return $this->execute();
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
     * Mengimpor pengguna via CSV (Update dengan Tanggal Lahir)
     */
    public function importUsers($users) {
        $summary = [
            'success' => 0,
            'skipped' => 0,
            'errors'  => 0,
            'skipped_list' => []
        ];

        // PERBAIKAN: Menghapus 'is_active' dan memastikan urutan :email dan :tgl benar
        $sql = "INSERT INTO users (nama_lengkap, email, tanggal_lahir, password, role) 
                VALUES (:nama_lengkap, :email, :tgl, :password, :role)";
        
        $this->query($sql);

        foreach ($users as $user) {
            try {
                $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

                $this->bind('nama_lengkap', $user['nama_lengkap']);
                $this->bind('email', $user['email']);
                $this->bind('tgl', $user['tanggal_lahir']);
                $this->bind('password', $hashedPassword);
                $this->bind('role', $user['role']);
                
                $this->execute();
                $summary['success']++; 

            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                    $summary['skipped']++;
                    $summary['skipped_list'][] = $user['email'];
                } else {
                    $summary['errors']++;
                }
            }
        }

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
     * Update profil user (Versi Stabil: Cek -> Insert/Update)
     */
    public function updateProfile($data) {
        $this->db->beginTransaction(); // Pakai transaksi biar aman

        try {
            // 1. Cek apakah data profil sudah ada?
            $this->query("SELECT profile_id FROM user_profiles WHERE user_id = :uid");
            $this->bind('uid', $data['user_id']);
            $exists = $this->single();

            if ($exists) {
                // A. UPDATE jika sudah ada
                $this->query("UPDATE user_profiles SET 
                                foto_profil = :foto,
                                tempat_lahir = :tempat,
                                tanggal_lahir = :tgl,
                                agama = :agama,
                                telepon = :telp,
                                alamat = :alamat,
                                kota = :kota,
                                provinsi = :prov,
                                kode_pos = :pos
                              WHERE user_id = :uid");
            } else {
                // B. INSERT jika belum ada
                $this->query("INSERT INTO user_profiles 
                                (user_id, foto_profil, tempat_lahir, tanggal_lahir, agama, telepon, alamat, kota, provinsi, kode_pos)
                              VALUES 
                                (:uid, :foto, :tempat, :tgl, :agama, :telp, :alamat, :kota, :prov, :pos)");
            }

            // Bind parameter (Sama untuk Insert/Update)
            $this->bind('uid', $data['user_id']);
            $this->bind('foto', $data['foto_profil']);
            $this->bind('tempat', $data['tempat_lahir']);
            $this->bind('tgl', $data['tanggal_lahir']);
            $this->bind('agama', $data['agama']);
            $this->bind('telp', $data['telepon']);
            $this->bind('alamat', $data['alamat']);
            $this->bind('kota', $data['kota']);
            $this->bind('prov', $data['provinsi']);
            $this->bind('pos', $data['kode_pos']);
            
            $this->execute(); // Eksekusi Query Profil

            // 2. Update Nama Lengkap di tabel Users
            $this->query("UPDATE users SET nama_lengkap = :nama WHERE user_id = :uid");
            $this->bind('nama', $data['nama_lengkap']);
            $this->bind('uid', $data['user_id']);
            $this->execute(); // Eksekusi Query User

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            // Throw error agar bisa ditangkap Controller
            throw new Exception("Gagal update profil: " . $e->getMessage());
        }
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

    /**
     * [BARU] Menambahkan user baru dengan Tanggal Lahir.
     * Menggantikan/Melengkapi fungsi createUser yang lama.
     */
   public function addUser($data) {
        // PERBAIKAN: Menghapus 'is_active' karena tidak ada di database
        $query = "INSERT INTO users (nama_lengkap, tanggal_lahir, email, password, role) 
                  VALUES (:nama, :tgl, :email, :pass, :role)";
        
        $this->query($query);
        
        $this->bind('nama', $data['nama_lengkap']);
        $this->bind('tgl', $data['tanggal_lahir']); 
        $this->bind('email', $data['email']);
        $this->bind('pass', password_hash($data['password'], PASSWORD_DEFAULT)); 
        $this->bind('role', $data['role']);

        return $this->execute();
    }
    
}