<?php
namespace Config;

date_default_timezone_set('Asia/Jakarta');

use PDO;
use PDOException;

class Database {
    private $host = "localhost";
    private $db_name = "absensi_db";
    private $username = "root"; // Default XAMPP username
    private $password = "Meong#0404";     // Default XAMPP password
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
