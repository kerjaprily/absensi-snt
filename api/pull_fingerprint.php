<?php
// Script Backend Simulasi Tarik Data Fingerprint
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Akses Ditolak. Harus login sebagai Admin."]);
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/Database.php';

$database = new \Config\Database();
$db = $database->getConnection();

$machine_id = $_GET['machine_id'] ?? null;

if (!$machine_id) {
    echo json_encode(["status" => "error", "message" => "ID Mesin tidak disertakan."]);
    exit;
}

// Ambil IP dari database
$query = "SELECT * FROM fingerprint_settings WHERE id = :id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $machine_id);
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config || empty($config['ip_address'])) {
    echo json_encode(["status" => "error", "message" => "Konfigurasi IP belum diatur."]);
    exit;
}

$ip = $config['ip_address'];
$port = $config['port'];

// SIMULASI KONEKSI SOCKET
// Pada implementasi nyata, di sini akan digunakan library seperti "tad-php" 
// atau membuka fsockopen UDP ke port 4370.

// Karena UDP bersifat connectionless, fsockopen selalu mengembalikan true meskipun IP tujuan mati.
// Untuk mengecek koneksi sesungguhnya, kita harus mengirim paket dan menunggu balasan (stream_set_timeout).
// Karena saat ini kita tahu belum ada mesin fisik yang terhubung, kita buat fungsi timeout-nya merespons gagal.
$connection_test = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);

sleep(2); // delay pura-pura loading

// Simulasikan gagal membaca balasan dari mesin (karena mesin memang belum ada)
$read_response = false; 

if ($connection_test && $read_response) {
    fclose($connection_test);
    echo json_encode([
        "status" => "success", 
        "message" => "Berhasil terhubung ke $ip dan menarik 0 data terbaru."
    ]);
} else {
    if($connection_test) fclose($connection_test);
    echo json_encode([
        "status" => "error", 
        "message" => "Mesin di IP $ip:$port tidak memberikan respon (Request Timeout). Pastikan mesin menyala, terhubung ke LAN yang sama, dan IP sudah sesuai."
    ]);
}
?>
