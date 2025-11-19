<?php

class Audit_model extends Model {

    private $table = 'audit_trail';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total log (dengan filter)
     */
    public function getTotalAuditCount($filters = []) {
        $sql = "SELECT COUNT(a.log_id) as total
                FROM " . $this->table . " a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND a.waktu >= :start_date";
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND a.waktu <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        $this->query($sql);
        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }
        return $this->single()['total'];
    }

    /**
     * Mengambil data log dengan paginasi (dengan filter)
     */
    public function getAuditLogPaginated($limit, $offset, $filters = []) {
        $sql = "SELECT a.waktu, a.aksi, a.modul, u.nama_lengkap, u.role
                FROM " . $this->table . " a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND a.waktu >= :start_date";
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND a.waktu <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        $sql .= " ORDER BY a.waktu DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $this->query($sql);
        foreach ($params as $key => &$value) {
            $type = ($key == ':limit' || $key == ':offset' || $key == ':user_id') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->bind($key, $value, $type);
        }
        
        return $this->resultSet();
    }
    /**
     * [DASHBOARD] Mengambil 5 log aktivitas terakhir
     */
    public function getLogTerbaru($limit = 5) {
        $sql = "SELECT a.waktu, a.aksi, u.nama_lengkap
                FROM " . $this->table . " a
                LEFT JOIN users u ON a.user_id = u.user_id
                ORDER BY a.waktu DESC LIMIT :limit";
        
        $this->query($sql);
        $this->bind('limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }
}