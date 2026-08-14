<?php
namespace Models;

use PDO;

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $role_id;
    public $location_id;
    public $pin;
    public $name;
    public $username;
    public $password;
    public $device_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT id, role_id, location_id, name, password, device_id FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->role_id = $row['role_id'];
                $this->location_id = $row['location_id'];
                $this->name = $row['name'];
                $this->device_id = $row['device_id'];
                return true;
            }
        }
        return false;
    }

    public function updateDeviceId() {
        $query = "UPDATE " . $this->table_name . " SET device_id = :device_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':device_id', $this->device_id);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function resetDeviceId() {
        $query = "UPDATE " . $this->table_name . " SET device_id = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }


    public function getUserLocation() {
        $query = "SELECT l.latitude, l.longitude, l.radius_meters, l.name as location_name 
                  FROM locations l 
                  JOIN " . $this->table_name . " u ON u.location_id = l.id 
                  WHERE u.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- NEW CRUD METHODS ---
    public function getAllUsers() {
        $query = "SELECT u.*, r.name as role_name, l.name as location_name 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  LEFT JOIN locations l ON u.location_id = l.id
                  ORDER BY u.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (role_id, location_id, pin, name, username, password) 
                  VALUES (:role_id, :location_id, :pin, :name, :username, :password)";
        
        $stmt = $this->conn->prepare($query);
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":role_id", $this->role_id);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":pin", $this->pin);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        
        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        if (!empty($this->password)) {
            $query = "UPDATE " . $this->table_name . " 
                      SET role_id = :role_id, location_id = :location_id, pin = :pin, 
                          name = :name, username = :username, password = :password
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $this->password);
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET role_id = :role_id, location_id = :location_id, pin = :pin, 
                          name = :name, username = :username
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(":role_id", $this->role_id);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":pin", $this->pin);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":id", $this->id);
        
        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>
