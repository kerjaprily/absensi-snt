<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/User.php';

use Config\Database;
use Classes\User;

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Get User's assigned location
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$user->id = $_SESSION['user_id'];
$location = $user->getUserLocation();

if(!$location) {
    die("Error: Lokasi pengguna tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absen Web Geotagging</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        // Inject PHP variables to JavaScript
        const assignedLat = <?php echo $location['latitude']; ?>;
        const assignedLong = <?php echo $location['longitude']; ?>;
        const maxRadius = <?php echo $location['radius_meters']; ?>;
        const locationName = "<?php echo $location['location_name']; ?>";
    </script>
</head>
<body>
    <nav class="dashboard-nav glass">
        <div class="logo"><strong>Absensi Pintar</strong></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="attendance-card glass">
            <h2>Absen Web Geotagging</h2>
            <p style="margin: 10px 0 20px; color: var(--text-muted);">
                Lokasi Anda ditugaskan di: <strong><?php echo htmlspecialchars($location['location_name']); ?></strong><br>
                Batas Radius: <?php echo $location['radius_meters']; ?> meter
            </p>

            <div style="margin: 20px 0; font-size: 14px;" id="location-info">
                Mencari koordinat Anda... Pastikan GPS aktif.
            </div>
            
            <button class="btn-primary" id="btn-in" onclick="submitAttendance('IN')" disabled>Absen Masuk</button>
            <button class="btn-primary" id="btn-out" onclick="submitAttendance('OUT')" disabled style="margin-top: 10px; background: #6366f1;">Absen Pulang</button>

            <div id="status-message" class="status-box"></div>
        </div>
    </div>

    <script src="assets/js/geotagging.js"></script>
</body>
</html>
