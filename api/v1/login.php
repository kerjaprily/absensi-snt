<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../../config/Database.php';
require_once '../../models/User.php';

$database = new \Config\Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->username) && !empty($data->password)){
    $user = new \Models\User($db);
    $user->username = $data->username;
    $user->password = $data->password;
    
    if($user->login()){
        if($user->role_id == 1) {
            http_response_code(403);
            echo json_encode(["message" => "Akun admin tidak diperkenankan menggunakan aplikasi mobile."]);
        } else {
            $incoming_device_id = $data->device_id ?? '';
            
            // Logika Device Binding
            if (empty($user->device_id)) {
                // Pertama kali login, simpan device ID
                $user->device_id = $incoming_device_id;
                $user->updateDeviceId();
            } else {
                // Login berikutnya, cocokkan device ID
                if ($user->device_id !== $incoming_device_id && !empty($incoming_device_id)) {
                    http_response_code(403);
                    echo json_encode(["message" => "Akses Ditolak! Akun ini telah tertaut dengan perangkat HP lain."]);
                    exit;
                }
            }

            // Generate JWT Token
            require_once '../../models/Auth.php';
            $token = \Models\Auth::generateToken($user->id, $user->role_id);
            
            http_response_code(200);
            echo json_encode([
                "message" => "Login berhasil.",
                "user_id" => $user->id,
                "name" => $user->name,
                "role_id" => $user->role_id,
                "token" => $token
            ]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Username atau password salah."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Data tidak lengkap."]);
}
?>
