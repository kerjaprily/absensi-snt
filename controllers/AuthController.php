<?php
namespace Controllers;

use Config\Database;
use Models\User;
use Models\Auth;

class AuthController {
    public function login() {
        if(isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "dashboard");
            exit;
        }

        $error_message = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("Invalid CSRF Token");
            }
            $database = new Database();
            $db = $database->getConnection();
            
            $user = new User($db);
            $user->username = $_POST['username'] ?? '';
            $user->password = $_POST['password'] ?? '';
            
            if ($user->login()) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user->id;
                $_SESSION['role_id'] = $user->role_id;
                $_SESSION['name'] = $user->name;
                header("Location: " . BASE_URL . "dashboard");
                exit;
            } else {
                $error_message = "Username atau Password salah!";
            }
        }
        
        require 'views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "auth/login");
        exit;
    }
}
?>
