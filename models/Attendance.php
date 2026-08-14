<?php
namespace Models;

use PDO;

class Attendance {
    private $conn;
    private $table_name = "attendance_logs";

    public $user_id;
    public $scan_date;
    public $scan_time;
    public $auth_type;
    public $source;
    public $latitude;
    public $longitude;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function logWebAttendance() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, scan_date, scan_time, auth_type, source, latitude, longitude) 
                  VALUES (:user_id, :scan_date, :scan_time, :auth_type, 'WEB', :latitude, :longitude)";

        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":scan_date", $this->scan_date);
        $stmt->bindParam(":scan_time", $this->scan_time);
        $stmt->bindParam(":auth_type", $this->auth_type);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);

        return $stmt->execute();
    }

    public function logFingerprintAttendance() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, scan_date, scan_time, auth_type, source) 
                  VALUES (:user_id, :scan_date, :scan_time, :auth_type, 'FINGERPRINT')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":scan_date", $this->scan_date);
        $stmt->bindParam(":scan_time", $this->scan_time);
        $stmt->bindParam(":auth_type", $this->auth_type);

        return $stmt->execute();
    }

    public function getMonthlyLogs($user_id, $month, $year) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND MONTH(scan_date) = :month 
                  AND YEAR(scan_date) = :year 
                  ORDER BY scan_date ASC, scan_time ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":month", $month);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NEW RECAP METHOD FOR ADMIN ---
    public function getAllFilteredLogs($start_date, $end_date, $user_id = null) {
        $query = "SELECT a.*, u.name as user_name, r.name as role_name 
                  FROM " . $this->table_name . " a
                  JOIN (
                      SELECT user_id, scan_date, MIN(scan_time) as scan_time, auth_type 
                      FROM " . $this->table_name . " 
                      WHERE scan_date >= :start_date_in AND scan_date <= :end_date_in AND auth_type = 'IN'
                      GROUP BY user_id, scan_date, auth_type
                      
                      UNION ALL
                      
                      SELECT user_id, scan_date, MAX(scan_time) as scan_time, auth_type 
                      FROM " . $this->table_name . " 
                      WHERE scan_date >= :start_date_out AND scan_date <= :end_date_out AND auth_type = 'OUT'
                      GROUP BY user_id, scan_date, auth_type
                  ) filter_q ON a.user_id = filter_q.user_id 
                            AND a.scan_date = filter_q.scan_date 
                            AND a.scan_time = filter_q.scan_time 
                            AND a.auth_type = filter_q.auth_type
                  JOIN users u ON a.user_id = u.id
                  JOIN roles r ON u.role_id = r.id
                  WHERE 1=1";
                  
        if (!empty($user_id)) {
            $query .= " AND a.user_id = :user_id";
        }
        
        $query .= " ORDER BY a.scan_date DESC, u.name ASC, a.scan_time DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date_in", $start_date);
        $stmt->bindParam(":end_date_in", $end_date);
        $stmt->bindParam(":start_date_out", $start_date);
        $stmt->bindParam(":end_date_out", $end_date);
        
        if (!empty($user_id)) {
            $stmt->bindParam(":user_id", $user_id);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
