<?php

class Loan_model extends Model {

    private $table = 'peminjaman';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total riwayat peminjaman (Admin View)
     */
    public function getTotalRiwayatPeminjamanCount($search, $status) {
        $sql = "SELECT COUNT(p.peminjaman_id) as total
                FROM " . $this->table . " p
                LEFT JOIN products pr ON p.product_id = pr.product_id
                LEFT JOIN users u_peminjam ON p.peminjam_user_id = u_peminjam.user_id
                ";
        
        $params = [];
        $whereClauses = [];

        // Filter Search (Nama Peminjam atau Nama Barang)
        if (!empty($search)) {
            $whereClauses[] = "(pr.nama_barang LIKE :search OR u_peminjam.nama_lengkap LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        // Filter Status
        if (!empty($status)) {
            $whereClauses[] = "p.status_pinjam = :status";
            $params[':status'] = $status;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    /**
     * Mengambil data riwayat peminjaman dengan paginasi (Admin View)
     */
    public function getRiwayatPeminjamanPaginated($limit, $offset, $search, $status) {
        $sql = "SELECT 
                    p.tgl_pengajuan, p.tgl_rencana_pinjam, p.tgl_rencana_kembali,
                    p.status_pinjam,
                    pr.nama_barang,
                    u_peminjam.nama_lengkap as nama_peminjam,
                    u_staff.nama_lengkap as nama_staff
                FROM 
                    " . $this->table . " p
                LEFT JOIN products pr ON p.product_id = pr.product_id
                LEFT JOIN users u_peminjam ON p.peminjam_user_id = u_peminjam.user_id
                LEFT JOIN users u_staff ON p.staff_user_id = u_staff.user_id
                ";
        
        $params = [];
        $whereClauses = [];

        // Filter Search (Nama Peminjam atau Nama Barang)
        if (!empty($search)) {
            $whereClauses[] = "(pr.nama_barang LIKE :search OR u_peminjam.nama_lengkap LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        // Filter Status
        if (!empty($status)) {
            $whereClauses[] = "p.status_pinjam = :status";
            $params[':status'] = $status;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " ORDER BY p.tgl_pengajuan DESC LIMIT :limit OFFSET :offset";
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
     * Mengambil data peminjaman (untuk workflow Staff) berdasarkan ARRAY status
     */
    public function getPeminjamanByStatus(array $statuses) {
        // Buat placeholder '?' sebanyak jumlah status
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $sql = "SELECT 
                    p.peminjaman_id, p.tgl_pengajuan, p.tgl_rencana_pinjam, p.tgl_rencana_kembali,
                    p.status_pinjam, p.alasan_pinjam,
                    pr.nama_barang,
                    u_peminjam.nama_lengkap as nama_peminjam
                FROM 
                    " . $this->table . " p
                LEFT JOIN products pr ON p.product_id = pr.product_id
                LEFT JOIN users u_peminjam ON p.peminjam_user_id = u_peminjam.user_id
                WHERE 
                    p.status_pinjam IN ($placeholders)
                ORDER BY 
                    p.tgl_pengajuan ASC";
        
        $this->query($sql);
        
        // Bind semua status ke placeholder '?'
        $i = 1;
        foreach ($statuses as $status) {
            $this->bind($i, $status);
            $i++;
        }
        
        return $this->resultSet();
    }

    /**
     * Mengupdate status peminjaman (digunakan oleh Staff)
     */
    public function updateLoanStatus($peminjaman_id, $new_status, $staff_id, $catatan = null) {
        $sql = "UPDATE " . $this->table . " SET 
                    status_pinjam = :status, 
                    staff_user_id = :staff_id,
                    catatan_staff = :catatan";

        // Set tanggal aktual berdasarkan status baru
        if ($new_status == 'Sedang Dipinjam') {
            $sql .= ", tgl_aktual_ambil = NOW()";
        } else if ($new_status == 'Selesai') {
            $sql .= ", tgl_aktual_kembali = NOW()";
        }

        $sql .= " WHERE peminjaman_id = :peminjaman_id";

        $this->query($sql);
        $this->bind('status', $new_status);
        $this->bind('staff_id', $staff_id, PDO::PARAM_INT);
        $this->bind('catatan', $catatan);
        $this->bind('peminjaman_id', $peminjaman_id, PDO::PARAM_INT);

        return $this->execute();
    }
    // (Tambahkan ini di bawah getRiwayatPeminjamanPaginated)
    /**
     * Membuat pengajuan peminjaman baru (dipanggil Peminjam)
     */
    public function createLoanRequest($data) {
        $this->query("INSERT INTO " . $this->table . " 
                        (product_id, peminjam_user_id, tgl_rencana_pinjam, tgl_rencana_kembali, status_pinjam, alasan_pinjam)
                      VALUES 
                        (:pid, :puid, :trp, :trk, :status, :alasan)");
        
        $this->bind('pid', $data['product_id'], PDO::PARAM_INT);
        $this->bind('puid', $data['peminjam_user_id'], PDO::PARAM_INT);
        $this->bind('trp', $data['tgl_rencana_pinjam']);
        $this->bind('trk', $data['tgl_rencana_kembali']);
        $this->bind('status', $data['status_pinjam']);
        $this->bind('alasan', $data['alasan_pinjam']);
        
        return $this->execute();
    }
    
    /**
     * Mengambil riwayat peminjaman spesifik untuk seorang peminjam (Dashboard Peminjam)
     */
    public function getRiwayatPeminjamanByPeminjamId($peminjamId) {
        $this->query("SELECT 
                        p.tgl_pengajuan, p.tgl_rencana_kembali, p.status_pinjam,
                        pr.nama_barang
                      FROM peminjaman p
                      LEFT JOIN products pr ON p.product_id = pr.product_id
                      WHERE p.peminjam_user_id = :puid
                      ORDER BY p.tgl_pengajuan DESC");

        $this->bind('puid', $peminjamId, PDO::PARAM_INT);
        return $this->resultSet();
    }
    /**
     * [DASHBOARD] Menghitung jumlah peminjaman yang jatuh tempo
     */
    public function getJumlahJatuhTempo() {
        $this->query("SELECT COUNT(peminjaman_id) as total 
                      FROM " . $this->table . " 
                      WHERE status_pinjam = 'Jatuh Tempo'");
        return $this->single()['total'];
    }
}