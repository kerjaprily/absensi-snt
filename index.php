<?php
session_start();

// Deteksi Base URL secara otomatis (mendukung XAMPP sub-folder maupun root)
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($script_dir, '/') . '/');

require_once 'config/Database.php';

// Autoload sederhana untuk Models dan Controllers
spl_autoload_register(function ($class_name) {
    // Pecah namespace berdasarkan backslash
    $parts = explode('\\', $class_name);
    // Ubah bagian pertama (nama folder seperti 'Controllers' atau 'Models') menjadi huruf kecil
    if(isset($parts[0])) {
        $parts[0] = strtolower($parts[0]);
    }
    // Gabungkan kembali menjadi path file (controllers/AuthController.php)
    $path = implode('/', $parts) . '.php';
    
    if (file_exists($path)) {
        require_once $path;
    }
});

// Ambil rute dari URL (Mendukung .htaccess maupun PHP Built-in Server)
$route = $_GET['route'] ?? '';
if (empty($route)) {
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base_path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    
    if ($base_path !== '' && strpos($request_uri, $base_path) === 0) {
        $route = substr($request_uri, strlen($base_path));
    } else {
        $route = $request_uri;
    }
    $route = trim($route, '/');
}

if (empty($route) || $route == 'index.php') {
    $route = 'auth/login';
}

switch ($route) {
    case 'auth/login':
        $controller = new \Controllers\AuthController();
        $controller->login();
        break;
    case 'auth/logout':
        $controller = new \Controllers\AuthController();
        $controller->logout();
        break;
    case 'dashboard':
        $controller = new \Controllers\DashboardController();
        $controller->index();
        break;
    case 'admin/users':
        $controller = new \Controllers\AdminController();
        $controller->users();
        break;
    case 'admin/edit_user':
        $controller = new \Controllers\AdminController();
        $controller->editUser();
        break;
    case 'admin/locations':
        $controller = new \Controllers\AdminController();
        $controller->locations();
        break;
    case 'admin/rekap':
        $controller = new \Controllers\AdminController();
        $controller->rekap();
        break;
    case 'admin/fingerprint':
        $controller = new \Controllers\AdminController();
        $controller->fingerprint();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found - Halaman tidak ditemukan.";
        break;
}
?>
