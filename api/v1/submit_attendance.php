<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../../config/Database.php';
require_once '../../models/Attendance.php';
require_once '../../models/User.php';

$database = new \Config\Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Keamanan: Verifikasi JWT Token
require_once '../../models/Auth.php';
$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
}
$token = str_replace('Bearer ', '', $authHeader);

$payload = \Models\Auth::validateToken($token);
if (!$payload || $payload['user_id'] != ($data->user_id ?? 0)) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Token tidak valid atau sesi telah berakhir."]);
    exit;
}

if(
    !empty($data->user_id) && 
    !empty($data->auth_type) && 
    !empty($data->latitude) && 
    !empty($data->longitude)
) {
    $user = new \Models\User($db);
    $user->id = $data->user_id;
    $location = $user->getUserLocation();
    
    if($location) {
        $earth_radius = 6371000;
        $latFrom = deg2rad($data->latitude);
        $lonFrom = deg2rad($data->longitude);
        $latTo = deg2rad($location['latitude']);
        $lonTo = deg2rad($location['longitude']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
          cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earth_radius;

        if ($distance <= ($location['radius_meters'] + 10)) { // 10 meter tolerance
            $attendance = new \Models\Attendance($db);
            $attendance->user_id = $data->user_id;
            $attendance->scan_date = date('Y-m-d');
            $attendance->scan_time = date('H:i:s');
            $attendance->auth_type = $data->auth_type;
            $attendance->latitude = $data->latitude;
            $attendance->longitude = $data->longitude;

            if ($attendance->logWebAttendance()) {
                http_response_code(200);
                echo json_encode(["message" => "Absen berhasil dicatat."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Gagal menyimpan ke database."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Jarak Anda terlalu jauh dari lokasi penempatan (" . round($distance) . "m)."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Lokasi penempatan user tidak ditemukan."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Data tidak lengkap."]);
}
?>
