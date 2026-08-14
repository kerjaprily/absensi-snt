<?php
require_once '../config/Database.php';
require_once '../classes/Attendance.php';

use Config\Database;
use Classes\Attendance;

header("Content-Type: text/plain"); // Mesin fingerprint biasanya request plain text, bukan JSON

$database = new Database();
$db = $database->getConnection();

$pin = $_POST['pin'] ?? null;
$timestamp = $_POST['time'] ?? null;
$status = $_POST['status'] ?? null;

if ($pin && $timestamp) {
    // Find User ID by PIN
    $stmt = $db->prepare("SELECT id FROM users WHERE pin = ?");
    $stmt->execute([$pin]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $attendance = new Attendance($db);
        $attendance->user_id = $user['id'];
        
        $datetime = new \DateTime($timestamp);
        $attendance->scan_date = $datetime->format('Y-m-d');
        $attendance->scan_time = $datetime->format('H:i:s');
        $attendance->auth_type = ($status == '0') ? 'IN' : 'OUT'; // 0 = IN, 1 = OUT (adjust based on machine)

        if ($attendance->logFingerprintAttendance()) {
             echo "OK"; 
        } else {
             echo "ERROR: Failed to save to database";
        }
    } else {
        echo "ERROR: User PIN not found";
    }
} else {
    echo "ERROR: Invalid parameters";
}
?>
