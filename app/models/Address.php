<?php
namespace App\Models;
use Core\Model;

class Address extends Model {
    public function __construct() {
        parent::__construct(); // khởi tạo $this->conn từ Model
    }

    public function getByUser($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM addresses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data) {
        $sql = "INSERT INTO addresses (user_id, full_name, phone, address_line, is_default) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssi", $data['user_id'], $data['full_name'], $data['phone'], $data['address_line'], $data['is_default']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $sql = "UPDATE addresses SET full_name = ?, phone = ?, address_line = ?, is_default = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssii", $data['full_name'], $data['phone'], $data['address_line'], $data['is_default'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM addresses WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function resetDefault($userId) {
        $stmt = $this->conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function setDefaultOnly($addressId) {
        $stmt = $this->conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?");
        $stmt->bind_param("i", $addressId);
        return $stmt->execute();
    }
}