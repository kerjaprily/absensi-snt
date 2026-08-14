<?php
namespace Controllers;

use Config\Database;
use Models\Attendance;

class DashboardController {
    public function index() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        $isAdmin = ($_SESSION['role_id'] == 1);
        $user_id = $_SESSION['user_id'];
        $name = $_SESSION['name'];
        $role_id = $_SESSION['role_id'];

        $database = new Database();
        $db = $database->getConnection();
        
        $attendance = new Attendance($db);

        $statusMessage = "";
        $statusType = "";

        // Menangani input absen web (hanya untuk non-admin)
        if (!$isAdmin && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'absen_web') {
            $type = $_POST['type'];
            
            if($attendance->create($user_id, $type, 'WEB')) {
                $statusMessage = "Berhasil absen " . ($type == 'IN' ? 'Masuk' : 'Pulang') . " via Web!";
                $statusType = "success";
            } else {
                $statusMessage = "Gagal absen. Mungkin Anda sudah absen hari ini.";
                $statusType = "error";
            }
        }

        $currentMonth = date('m');
        $currentYear = date('Y');
        
        $logs = [];
        if (!$isAdmin) {
            $logs = $attendance->getMonthlyLogs($user_id, $currentMonth, $currentYear);
        } else {
            $start_date = sprintf("%04d-%02d-01", $currentYear, $currentMonth);
            $end_date = date("Y-m-t", strtotime($start_date));
            $logs = $attendance->getAllFilteredLogs($start_date, $end_date);
        }

        require 'views/dashboard/index.php';
    }
}
?>
