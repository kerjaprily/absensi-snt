CREATE DATABASE IF NOT EXISTS absensi_db;
USE absensi_db;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_meters INT NOT NULL DEFAULT 100
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    location_id INT NOT NULL,
    pin VARCHAR(50) UNIQUE,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

CREATE TABLE IF NOT EXISTS attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    scan_date DATE NOT NULL,
    scan_time TIME NOT NULL,
    auth_type ENUM('IN', 'OUT') NOT NULL,
    source ENUM('FINGERPRINT', 'WEB') NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Dummy Data Insertion
INSERT IGNORE INTO roles (name) VALUES ('Admin'), ('Guru'), ('Siswa');

-- Dummy 2 locations out of 17 for testing
INSERT IGNORE INTO locations (name, latitude, longitude, radius_meters) VALUES 
('Gedung Utama', -6.2250597785136685, 106.80199592422348, 100),
('Gedung Cabang', -6.210000, 106.820000, 100);

-- Admin user (password: password)
INSERT IGNORE INTO users (role_id, location_id, pin, name, username, password) VALUES 
(1, 1, '1001', 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
