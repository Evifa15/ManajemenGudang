<?php

class Absensi_model extends Model {

    private $table = 'absensi';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total data absensi (Support Filter Tanggal Spesifik & Range)
     */
    public function getTotalAbsensiCount($filters = []) {
        $sql = "SELECT COUNT(a.absen_id) as total 
                FROM " . $this->table . " a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];

        // Filter Pencarian Nama/Email
        if (!empty($filters['search'])) {
            $sql .= " AND (u.nama_lengkap LIKE :search OR u.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Filter Status
        if (!empty($filters['status'])) {
             if ($filters['status'] == 'Masih Bekerja') {
                $sql .= " AND a.status = 'Hadir' AND a.waktu_pulang IS NULL";
            } elseif ($filters['status'] == 'Hadir') {
                $sql .= " AND a.status = 'Hadir' AND a.waktu_pulang IS NOT NULL";
            } else {
                $sql .= " AND a.status = :status";
                $params[':status'] = $filters['status'];
            }
        }

        // Filter User Spesifik (Untuk Laporan Perorangan)
        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :uid";
            $params[':uid'] = $filters['user_id'];
        }
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $filters['role'];
        }

        // --- LOGIKA TANGGAL BARU ---
        
        // 1. Mode Harian (Satu Tanggal Spesifik)
        if (!empty($filters['specific_date'])) {
            $sql .= " AND a.tanggal = :spec_date";
            $params[':spec_date'] = $filters['specific_date'];
        }
        // 2. Mode Laporan (Rentang Tanggal)
        else {
            if (!empty($filters['start_date'])) {
                $sql .= " AND a.tanggal >= :start";
                $params[':start'] = $filters['start_date'];
            }
            if (!empty($filters['end_date'])) {
                $sql .= " AND a.tanggal <= :end";
                $params[':end'] = $filters['end_date'];
            }
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    public function getAbsensiPaginated($limit, $offset, $filters = []) {
        $sql = "SELECT a.absen_id, a.tanggal, a.waktu_masuk, a.waktu_pulang, a.status, a.keterangan, a.bukti_foto, 
                       u.nama_lengkap, u.role 
                FROM " . $this->table . " a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];
        
        // ... (Copy logic filter Search, Status, User_ID dari fungsi count di atas) ...
        if (!empty($filters['search'])) {
            $sql .= " AND (u.nama_lengkap LIKE :search OR u.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
             if ($filters['status'] == 'Masih Bekerja') {
                $sql .= " AND a.status = 'Hadir' AND a.waktu_pulang IS NULL";
            } elseif ($filters['status'] == 'Hadir') {
                $sql .= " AND a.status = 'Hadir' AND a.waktu_pulang IS NOT NULL";
            } else {
                $sql .= " AND a.status = :status";
                $params[':status'] = $filters['status'];
            }
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :uid";
            $params[':uid'] = $filters['user_id'];
        }
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $filters['role'];
        }

        // --- LOGIKA TANGGAL BARU ---
        if (!empty($filters['specific_date'])) {
            $sql .= " AND a.tanggal = :spec_date";
            $params[':spec_date'] = $filters['specific_date'];
        } else {
            if (!empty($filters['start_date'])) {
                $sql .= " AND a.tanggal >= :start";
                $params[':start'] = $filters['start_date'];
            }
            if (!empty($filters['end_date'])) {
                $sql .= " AND a.tanggal <= :end";
                $params[':end'] = $filters['end_date'];
            }
        }

        $sql .= " ORDER BY a.tanggal DESC, a.waktu_masuk ASC LIMIT :limit OFFSET :offset"; // Urutkan berdasarkan jam masuk agar rapi
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
     * [DASHBOARD] Menghitung jumlah staf yang hadir hari ini
     */
    public function getJumlahStafHadirHariIni() {
        $this->query("SELECT COUNT(absen_id) as total 
                      FROM " . $this->table . " 
                      WHERE tanggal = CURDATE()");
        return $this->single()['total'];
    }

    /**
     * [DASHBOARD] Mengambil nama staf yang hadir hari ini
     */
    public function getStafHadirSaatIni() {
        $this->query("SELECT u.nama_lengkap 
                      FROM " . $this->table . " a
                      JOIN users u ON a.user_id = u.user_id
                      WHERE a.tanggal = CURDATE() AND a.waktu_pulang IS NULL
                      ORDER BY a.waktu_masuk ASC");
        return $this->resultSet();
    }
    /**
     * [ABSENSI] Mengecek status absensi user hari ini.
     * @param int $userId ID user
     * @return array|false Data absensi jika ada, false jika belum.
     */
    public function getTodayAttendance($userId) {
        $this->query("SELECT * FROM " . $this->table . " 
                      WHERE user_id = :uid AND tanggal = CURDATE()");
        $this->bind('uid', $userId, PDO::PARAM_INT);
        return $this->single();
    }

    /**
     * [ABSENSI] Melakukan Check-in.
     * @param int $userId ID user
     * @return bool
     */
    public function checkInUser($userId) {
        // Set default status 'Hadir' saat check-in
        $this->query("INSERT INTO " . $this->table . " (user_id, tanggal, waktu_masuk, status) 
                    VALUES (:uid, CURDATE(), CURTIME(), 'Hadir')");
        $this->bind('uid', $userId, PDO::PARAM_INT);
        return $this->execute();
        }

    public function addIzinSakit($data) {
    $this->query("INSERT INTO " . $this->table . " (user_id, tanggal, status, keterangan, bukti_foto) 
                  VALUES (:uid, CURDATE(), :status, :ket, :bukti)");
    $this->bind('uid', $data['user_id'], PDO::PARAM_INT);
    $this->bind('status', $data['status']);
    $this->bind('ket', $data['keterangan']);
    $this->bind('bukti', $data['bukti_foto']); 
    return $this->execute();
    }

    /**
     * [ABSENSI] Melakukan Check-out.
     * @param int $absenId ID absensi (dari getTodayAttendance)
     * @return bool
     */
    public function checkOutUser($absenId) {
        $this->query("UPDATE " . $this->table . " SET waktu_pulang = CURTIME() 
                      WHERE absen_id = :aid AND waktu_pulang IS NULL");
        $this->bind('aid', $absenId, PDO::PARAM_INT);
        return $this->execute();
    }

    /**
     * [ADMIN] Update manual data absensi (Status, Jam, Keterangan, Bukti)
     */
    public function updateAbsensi($data) {
        // Query dasar update
        $sql = "UPDATE " . $this->table . " SET 
                    status = :status,
                    waktu_masuk = :masuk, 
                    waktu_pulang = :pulang,
                    keterangan = :keterangan";
        
        // Logika Update Foto:
        // 1. Jika key 'bukti_foto' ADA di array $data, berarti kita mau update kolom bukti_foto.
        // 2. Jika nilainya NULL (kasus ubah ke Hadir), file dihapus dari DB.
        // 3. Jika nilainya String (kasus upload baru), file diupdate.
        // 4. Jika key TIDAK ADA, berarti file lama dibiarkan (tidak diubah).
        
        if (array_key_exists('bukti_foto', $data)) {
            $sql .= ", bukti_foto = :bukti";
        }

        $sql .= " WHERE absen_id = :id";
        
        $this->query($sql);

        // Binding data wajib
        $this->bind('status', $data['status']);
        $this->bind('masuk', $data['waktu_masuk']); // Model.php akan otomatis set ke NULL jika nilainya null
        $this->bind('pulang', $data['waktu_pulang']); 
        $this->bind('keterangan', $data['keterangan']);
        $this->bind('id', $data['absen_id']);

        // Binding data opsional (bukti)
        if (array_key_exists('bukti_foto', $data)) {
            $this->bind('bukti', $data['bukti_foto']);
        }
        
        return $this->execute();
    }
    /**
     * [EXPORT] Ambil SEMUA data absensi sesuai filter (Tanpa Paginasi)
     */
    public function getAllAbsensiForExport($filters = []) {
        $sql = "SELECT a.tanggal, a.waktu_masuk, a.waktu_pulang, a.status, a.keterangan, 
                       u.nama_lengkap, u.role, u.email 
                FROM " . $this->table . " a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];

        // 1. Filter Pencarian
        if (!empty($filters['search'])) {
            $sql .= " AND (u.nama_lengkap LIKE :search OR u.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // 2. Filter Role
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $filters['role'];
        }

        // 3. Filter User Spesifik
        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :uid";
            $params[':uid'] = $filters['user_id'];
        }

        // 4. Filter Range Tanggal
        if (!empty($filters['start_date'])) {
            $sql .= " AND a.tanggal >= :start";
            $params[':start'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND a.tanggal <= :end";
            $params[':end'] = $filters['end_date'];
        }

        $sql .= " ORDER BY a.tanggal ASC, u.nama_lengkap ASC"; // Urutkan biar rapi di Excel

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->resultSet();
    }
}