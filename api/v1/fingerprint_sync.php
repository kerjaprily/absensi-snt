<?php
// Endpoint API untuk menerima data push dari Mesin Fingerprint (seperti ZKTeco ADMS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../../config/Database.php';
require_once '../../models/Attendance.php';

$database = new \Config\Database();
$db = $database->getConnection();

// Keamanan: Cek API Key khusus Mesin (karena diakses dari luar)
$apiKey = $_GET['api_key'] ?? '';
$validApiKey = "SYNC_SECURE_998877";

if ($apiKey !== $validApiKey) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid API Key"]);
    exit;
}

// Mesin absensi umumnya mengirim data lewat POST request biasa (form-data)
$pin = $_POST['pin'] ?? null;          // ID karyawan di mesin fingerprint
$time = $_POST['time'] ?? null;        // Waktu absen dari mesin (contoh: 2026-08-13 07:30:00)
$status = $_POST['status'] ?? null;    // Status dari mesin (misal: 0 untuk Masuk, 1 untuk Pulang)

// Jika mesin mengirim dalam bentuk JSON mentah (seperti aplikasi modern):
if (!$pin) {
    $data = json_decode(file_get_contents("php://input"));
    if ($data) {
        $pin = $data->pin ?? null;
        $time = $data->time ?? null;
        $status = $data->status ?? null;
    }
}

// Untuk keperluan Testing Manual via GET (opsional, hapus di produksi jika perlu)
if (isset($_GET['test'])) {
    $pin = $_GET['pin'] ?? '1234';
    $time = $_GET['time'] ?? date('Y-m-d H:i:s');
    $status = $_GET['status'] ?? '0';
}

if ($pin && $time) {
    // 1. Cari User ID berdasarkan PIN dari mesin
    $query = "SELECT id FROM users WHERE pin = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $pin);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['id'];
        
        // 2. Pecah timestamp (YYYY-MM-DD HH:MM:SS) menjadi Tanggal dan Jam
        $datetime = new DateTime($time);
        $scan_date = $datetime->format('Y-m-d');
        $scan_time = $datetime->format('H:i:s');
        
        // 3. Tentukan Tipe (IN / OUT). Biasanya status 0 = Masuk, 1 = Pulang
        $auth_type = ($status == '0') ? 'IN' : 'OUT';
        
        // 4. Masukkan ke Database
        $attendance = new \Models\Attendance($db);
        $attendance->user_id = $user_id;
        $attendance->scan_date = $scan_date;
        $attendance->scan_time = $scan_time;
        $attendance->auth_type = $auth_type;
        
        if ($attendance->logFingerprintAttendance()) {
            http_response_code(200);
            echo json_encode(["message" => "Data absen fingerprint berhasil disimpan.", "status" => "OK"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal menyimpan ke database."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "PIN tidak terdaftar di sistem."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Parameter tidak lengkap. Butuh 'pin' dan 'time'."]);
}
?>
