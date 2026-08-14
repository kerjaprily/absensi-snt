<?php
namespace Models;

use PDO;

class Location {
    private $conn;
    private $table_name = "locations";

    public $id;
    public $name;
    public $latitude;
    public $longitude;
    public $radius_meters;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getLocationById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            $this->radius_meters = $row['radius_meters'];
            return true;
        }
        return false;
    }

    public function getAllLocations() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NEW CRUD METHODS ---
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, latitude, longitude, radius_meters) VALUES (:name, :lat, :lng, :radius)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":lat", $this->latitude);
        $stmt->bindParam(":lng", $this->longitude);
        $stmt->bindParam(":radius", $this->radius_meters);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>
