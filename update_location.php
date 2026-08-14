<?php
require_once 'config/Database.php';
$db = (new \Config\Database())->getConnection();

try {
    $db->exec("UPDATE locations SET latitude = -6.2250597785136685, longitude = 106.80199592422348, name = 'Lokasi Uji Coba' WHERE id = 1");
    echo "<h1>Sukses!</h1><p>Koordinat lokasi (ID: 1) berhasil diubah menjadi: -6.2250597785136685, 106.80199592422348</p><a href='index.php'>Kembali ke Login</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
