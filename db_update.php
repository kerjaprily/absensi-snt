<?php
require 'config/Database.php';
$db = (new Config\Database())->getConnection();
$db->exec('CREATE TABLE IF NOT EXISTS fingerprint_settings (id INT AUTO_INCREMENT PRIMARY KEY, ip_address VARCHAR(50), port INT DEFAULT 4370, comm_key VARCHAR(50) DEFAULT \'0\');');
$db->exec('INSERT INTO fingerprint_settings (ip_address, port, comm_key) SELECT \'192.168.1.201\', 4370, \'0\' WHERE NOT EXISTS (SELECT 1 FROM fingerprint_settings);');
echo "OK";
