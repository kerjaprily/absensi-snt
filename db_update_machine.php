<?php
require 'config/Database.php';
$db = (new Config\Database())->getConnection();

try {
    // Tambah kolom jika belum ada
    $db->exec("ALTER TABLE fingerprint_settings ADD COLUMN machine_name VARCHAR(100) DEFAULT 'Mesin Fingerprint' AFTER id");
    // Update data awal jika diperlukan
    $db->exec("UPDATE fingerprint_settings SET machine_name = 'Mesin Utama' WHERE machine_name = 'Mesin Fingerprint'");
    echo "Success";
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Success";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
