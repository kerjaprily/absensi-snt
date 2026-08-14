<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../../config/Database.php';
require_once '../../models/Attendance.php';

if(!isset($_GET['user_id'])){
    http_response_code(400);
    echo json_encode(["message" => "User ID diperlukan."]);
    exit;
}

$database = new \Config\Database();
$db = $database->getConnection();

$user_id = $_GET['user_id'] ?? null;
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

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
if (!$payload || $payload['user_id'] != $user_id) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Token tidak valid atau sesi telah berakhir."]);
    exit;
}

$attendance = new \Models\Attendance($db);

$logs = $attendance->getMonthlyLogs($user_id, $month, $year);

http_response_code(200);
echo json_encode($logs);
?>
