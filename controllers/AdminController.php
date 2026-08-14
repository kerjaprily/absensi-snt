<?php
namespace Controllers;

use Config\Database;
use Models\User;
use Models\Location;
use Models\Attendance;
use Models\Auth;

class AdminController {
    private $db;

    public function __construct() {
        if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            die("Akses Ditolak. Anda bukan Admin.");
        }
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function users() {
        $userObj = new User($this->db);
        $locObj = new Location($this->db);
        $message = "";

        // Handle Delete
        if(isset($_GET['delete_id'])) {
            if (!Auth::validateCSRFToken($_GET['csrf_token'] ?? '')) {
                die("Invalid CSRF Token");
            }
            $userObj->id = $_GET['delete_id'];
            if($userObj->delete()) {
                $message = "User berhasil dihapus.";
            }
        }

        // Handle Add
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
            if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("Invalid CSRF Token");
            }
            $userObj->name = $_POST['name'];
            $userObj->username = $_POST['username'];
            $userObj->password = $_POST['password'];
            $userObj->pin = $_POST['pin'];
            $userObj->role_id = $_POST['role_id'];
            $userObj->location_id = $_POST['location_id'];
            
            if($userObj->create()) {
                $message = "User berhasil ditambahkan!";
            } else {
                $message = "Gagal menambahkan user. Pastikan Username/PIN tidak duplikat.";
            }
        }

        $users = $userObj->getAllUsers();
        $locations = $locObj->getAllLocations();
        
        require 'views/admin/users.php';
    }

    public function editUser() {
        $userClass = new User($this->db);
        $locationClass = new Location($this->db);
        
        $locations = $locationClass->getAllLocations();
        $message = "";

        if (isset($_GET['id'])) {
            $edit_user = $userClass->getUserById($_GET['id']);
            if (!$edit_user) {
                die("User tidak ditemukan.");
            }
        } else {
            header("Location: " . BASE_URL . "admin/users");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
            if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("Invalid CSRF Token");
            }
            $userClass->id = $_POST['user_id'];
            $userClass->name = $_POST['name'];
            $userClass->username = $_POST['username'];
            $userClass->role_id = $_POST['role_id'];
            $userClass->location_id = empty($_POST['location_id']) ? null : $_POST['location_id'];
            $userClass->pin = $_POST['pin'];
            $userClass->password = $_POST['password']; // Kosong berarti tidak ganti

            if (isset($_POST['reset_device']) && $_POST['reset_device'] == '1') {
                $userClass->resetDeviceId();
            }
            
            if($userClass->update()){
                $message = "User berhasil diperbarui!";
                $edit_user = $userClass->getUserById($_POST['user_id']); // refresh data
            } else {
                $message = "Gagal memperbarui user.";
            }
        }

        require 'views/admin/edit_user.php';
    }

    public function locations() {
        $locObj = new Location($this->db);
        $message = "";

        // Handle Delete
        if(isset($_GET['delete_id'])) {
            $locObj->id = $_GET['delete_id'];
            if($locObj->delete()) {
                $message = "Lokasi berhasil dihapus.";
            }
        }

        // Handle Add
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
            $locObj->name = $_POST['name'];
            $locObj->latitude = $_POST['latitude'];
            $locObj->longitude = $_POST['longitude'];
            $locObj->radius_meters = $_POST['radius_meters'];
            
            if($locObj->create()) {
                $message = "Lokasi berhasil ditambahkan!";
            } else {
                $message = "Gagal menambahkan lokasi.";
            }
        }

        $locations = $locObj->getAllLocations();
        require 'views/admin/locations.php';
    }

    public function rekap() {
        $attendance = new Attendance($this->db);
        $userObj = new User($this->db);
        
        $selectedMonth = $_GET['month'] ?? date('m');
        $selectedYear = $_GET['year'] ?? date('Y');
        $selectedUserId = $_GET['user_id'] ?? '';
        
        $start_date = sprintf("%04d-%02d-01", $selectedYear, $selectedMonth);
        $end_date = date("Y-m-t", strtotime($start_date));
        
        $logs = $attendance->getAllFilteredLogs($start_date, $end_date, $selectedUserId);
        $users = $userObj->getAllUsers();

        require 'views/admin/rekap.php';
    }

    public function fingerprint() {
        $message = "";
        $messageType = "success";

        // Handle Tambah/Edit Mesin
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save') {
            if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("Invalid CSRF Token");
            }
            $machine_id = $_POST['machine_id'] ?? '';
            $name = trim($_POST['machine_name']);
            $ip = trim($_POST['ip_address']);
            $port = (int)$_POST['port'];
            $key = trim($_POST['comm_key']);

            if (!empty($machine_id)) {
                // Update
                $query = "UPDATE fingerprint_settings SET machine_name = :name, ip_address = :ip, port = :port, comm_key = :key WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":ip", $ip);
                $stmt->bindParam(":port", $port);
                $stmt->bindParam(":key", $key);
                $stmt->bindParam(":id", $machine_id);
                if($stmt->execute()) {
                    $message = "Konfigurasi mesin berhasil diperbarui!";
                } else {
                    $message = "Gagal memperbarui konfigurasi.";
                    $messageType = "error";
                }
            } else {
                // Insert
                $query = "INSERT INTO fingerprint_settings (machine_name, ip_address, port, comm_key) VALUES (:name, :ip, :port, :key)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":ip", $ip);
                $stmt->bindParam(":port", $port);
                $stmt->bindParam(":key", $key);
                if($stmt->execute()) {
                    $message = "Mesin baru berhasil ditambahkan!";
                } else {
                    $message = "Gagal menambahkan mesin.";
                    $messageType = "error";
                }
            }
        }

        // Handle Delete
        if (isset($_GET['delete_id'])) {
            if (!Auth::validateCSRFToken($_GET['csrf_token'] ?? '')) {
                die("Invalid CSRF Token");
            }
            $query = "DELETE FROM fingerprint_settings WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $_GET['delete_id']);
            if($stmt->execute()) {
                $message = "Mesin berhasil dihapus!";
            }
        }

        // Ambil Daftar Mesin
        $query = "SELECT * FROM fingerprint_settings ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $machines = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require 'views/admin/fingerprint.php';
    }
}
?>
