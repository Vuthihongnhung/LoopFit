<?php

namespace App\Models;

use Core\Model;

class Address extends Model
{
    protected $table = 'addresses';

    // Sử dụng $conn từ Model (mysqli)
    public function getByUser($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY is_default DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getByIdAndUser($id, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (user_id, full_name, phone, address_line, is_default) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isssi', $data['user_id'], $data['full_name'], $data['phone'], $data['address_line'], $data['is_default']);
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET full_name = ?, phone = ?, address_line = ?, is_default = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssii', $data['full_name'], $data['phone'], $data['address_line'], $data['is_default'], $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function resetDefault($userId)
    {
        $sql = "UPDATE {$this->table} SET is_default = 0 WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        return $stmt->execute();
    }

    public function setDefault($userId, $addressId)
    {
        $this->resetDefault($userId);
        $sql = "UPDATE {$this->table} SET is_default = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $addressId);
        return $stmt->execute();
    }
}