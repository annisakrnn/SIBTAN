<?php
class Form {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($id, $file_name, $file_path, $status) {
        $sql = "INSERT INTO [FILES] (id, file_name, file_path, status) 
                VALUES (?, ?, ?, ?)";
        $params = [$id, $file_name, $file_path, $status];
    
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            echo $stmt;
            die(print_r(sqlsrv_errors(), true)); 
        }

        return true; 
    }

    public function update($id, $file_name, $file_path, $status) {
        $sql = "UPDATE [FILES] SET file_path = ?, status = ? WHERE id = ? AND file_name = ?";
        $params = [$file_path, $status, $id, $file_name];
    
        $stmt = sqlsrv_query($this->db, $sql, $params);
    
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        return true; 
    }    

    public function checkForm($userId, $file_name) {
        $sql = "SELECT status FROM FILES WHERE id = ? AND file_name = ?";
        $params = [$userId, $file_name];
        $stmt = sqlsrv_prepare($this->db, $sql, $params);

        if (!$stmt) {
            die(print_r(sqlsrv_errors(), true));
        }

        if (sqlsrv_execute($stmt)) {
            $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $result ? $result['status'] : null;
        }
    }

    public function getFile($userId, $file_name) {
        $sql = "SELECT file_path FROM FILES WHERE id = ? AND file_name = ?";
        $params = [$userId, $file_name];
        $stmt = sqlsrv_prepare($this->db, $sql, $params);

        if (!$stmt) {
            die(print_r(sqlsrv_errors(), true));
        }

        if (sqlsrv_execute($stmt)) {
            $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $result ? $result['file_path'] : null;
        }
    }
    public function updateStatus($userId, $file_name, $newStatus) {
        $sql = "UPDATE FILES SET status = ? WHERE id = ? AND file_name = ?";
        $params = [$newStatus, $userId, $file_name];
        
        $stmt = sqlsrv_query($this->db, $sql, $params);
        
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        return true; 
    }
    public function verificationPending($id) {
        $sql = "SELECT * FROM FILES WHERE status = 'Menunggu Verifikasi' AND id = ?";
        $params = [$id];
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true)); 
        }

        $filesToVerify = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $filesToVerify[] = $row; 
        }

        return $filesToVerify; 
    }

    public function getAll() {
        
        $sql = "
            SELECT * FROM [FILES]
        ";
        
        $stmt = sqlsrv_query($this->db, $sql);
    
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        $forms = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $users[] = $row;
        }
        return $forms;
    }
}
    
    
