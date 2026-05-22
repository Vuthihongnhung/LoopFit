<?php
namespace App\Models;
use Core\Model;

class Address extends Model {
    public function __construct() {
        parent::__construct(); 
    }

    /* ===== Lấy danh sách địa chỉ của một người dùng ===== */
    public function getByUser($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Lấy thông tin một địa chỉ theo ID ===== */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM addresses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /* ===== Thêm địa chỉ mới ===== */
    public function create($data) {
        $sql = "INSERT INTO addresses (user_id, full_name, phone, address_line, is_default) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssi", $data['user_id'], $data['full_name'], $data['phone'], $data['address_line'], $data['is_default']);
        return $stmt->execute();
    }

    /* ===== Cập nhật địa chỉ ===== */
    public function update($id, $data) {
        $sql = "UPDATE addresses SET full_name = ?, phone = ?, address_line = ?, is_default = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssii", $data['full_name'], $data['phone'], $data['address_line'], $data['is_default'], $id);
        return $stmt->execute();
    }

    /* ===== Xóa địa chỉ ===== */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM addresses WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* ===== Reset tất cả địa chỉ của user về không mặc định ===== */
    public function resetDefault($userId) {
        $stmt = $this->conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    /* ===== Đặt một địa chỉ cụ thể làm mặc định ===== */
    public function setDefaultOnly($addressId) {
        $stmt = $this->conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?");
        $stmt->bind_param("i", $addressId);
        return $stmt->execute();
    }
}