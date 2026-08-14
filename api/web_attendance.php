<?php
session_start();
require_once '../config/Database.php';
require_once '../classes/Attendance.php';
require_once '../classes/User.php';

use Config\Database;
use Classes\Attendance;
use Classes\User;

if(!isset($_SESSION['user_id'])) {
    die("UNAUTHORIZED");
}

$database = new Database();
$db = $database->getConnection();

$authType = $_POST['auth_type'] ?? null;
$lat = $_POST['latitude'] ?? null;
$long = $_POST['longitude'] ?? null;

if ($authType && $lat && $long) {
    // Re-validate distance on backend to prevent fake GPS request tampering
    $user = new User($db);
    $user->id = $_SESSION['user_id'];
    $location = $user->getUserLocation();
    
    if($location) {
        // Haversine calculation in PHP
        $earth_radius = 6371000;
        $latFrom = deg2rad($lat);
        $lonFrom = deg2rad($long);
        $latTo = deg2rad($location['latitude']);
        $lonTo = deg2rad($location['longitude']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
          cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earth_radius;

        // Beri sedikit toleransi selisih kalkulasi jarak js & php (contoh: +5 meter)
        if ($distance <= ($location['radius_meters'] + 5)) {
            $attendance = new Attendance($db);
            $attendance->user_id = $_SESSION['user_id'];
            $attendance->scan_date = date('Y-m-d');
            $attendance->scan_time = date('H:i:s');
            $attendance->auth_type = $authType;
            $attendance->latitude = $lat;
            $attendance->longitude = $long;

            if ($attendance->logWebAttendance()) {
                echo "OK";
            } else {
                echo "ERROR_DB";
            }
        } else {
            echo "ERROR_OUT_OF_RADIUS_BACKEND (Distance: " . round($distance) . "m)";
        }
    } else {
        echo "ERROR_LOCATION_NOT_FOUND";
    }
} else {
    echo "ERROR_MISSING_PARAMS";
}
?>
