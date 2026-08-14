<?php
namespace Models;

class Auth {
    private static $secret_key = "ABSENSI_PINTAR_SECRET_KEY_2026"; // Harusnya disimpan di env

    // Fungsi membuat JWT sederhana
    public static function generateToken($user_id, $role_id) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user_id,
            'role_id' => $role_id,
            'iat' => time(),
            'exp' => time() + (86400 * 30) // Expire 30 hari
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    // Fungsi memvalidasi JWT
    public static function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;

        $valid_signature = hash_hmac('sha256', $header . "." . $payload, self::$secret_key, true);
        $valid_base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($valid_signature));

        if (hash_equals($valid_base64UrlSignature, $signature)) {
            $payloadData = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
            if ($payloadData['exp'] >= time()) {
                return $payloadData; // Kembalikan payload jika valid
            }
        }
        return false;
    }

    // CSRF Generator
    public static function getCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // CSRF Validator
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
