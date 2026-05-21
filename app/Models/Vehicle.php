<?php
class Vehicle {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllVehicles() {
        $this->db->query("SELECT * FROM vehicles ORDER BY vehicle_number ASC");
        return $this->db->resultSet();
    }

    public function getVehiclesPaginated($search = '', $limit = 10, $offset = 0) {
        $this->db->query("SELECT * FROM vehicles 
                          WHERE vehicle_number LIKE :search OR model LIKE :search OR type LIKE :search 
                          ORDER BY vehicle_number ASC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':search', "%$search%");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalVehicles($search = '') {
        $this->db->query("SELECT COUNT(*) as total FROM vehicles WHERE vehicle_number LIKE :search OR model LIKE :search OR type LIKE :search");
        $this->db->bind(':search', "%$search%");
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getVehicleById($id) {
        $this->db->query("SELECT * FROM vehicles WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addVehicle($data) {
        $this->db->query("INSERT INTO vehicles (vehicle_number, model, type, status) 
                          VALUES (:vehicle_number, :model, :type, :status)");
        $this->db->bind(':vehicle_number', $data['vehicle_number']);
        $this->db->bind(':model', $data['model']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':status', $data['status']);
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateVehicle($id, $data) {
        $this->db->query("UPDATE vehicles 
                          SET vehicle_number = :vehicle_number, model = :model, type = :type, status = :status 
                          WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':vehicle_number', $data['vehicle_number']);
        $this->db->bind(':model', $data['model']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':status', $data['status']);
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteVehicle($id) {
        $this->db->query("DELETE FROM vehicles WHERE id = :id");
        $this->db->bind(':id', $id);
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
