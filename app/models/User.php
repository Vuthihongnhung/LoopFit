<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    public function getAll()
    {
        $sql = "SELECT id, name, email, phone, address, role, status, created_at 
                FROM users 
                ORDER BY id DESC";
        $result = $this->conn->query($sql);

        if (!$result) {
            return [];
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    public function getStatusById($id)
    {
        $stmt = $this->conn->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];

        return $row['total'];
    }

    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function findByEmail($email)
    {
        return $this->getByEmail($email);
    }

    public function create($data)
    {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $phone = $data['phone'] ?? '';
        $address = $data['address'] ?? '';
        $role = $data['role'] ?? 'user';
        $status = $data['status'] ?? 'active';

        $sql = "INSERT INTO users (name, email, password, phone, address, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare INSERT user lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("sssssss", $name, $email, $password, $phone, $address, $role, $status);

        if (!$stmt->execute()) {
            die('Execute INSERT user lỗi: ' . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    public function updateProfile($id, $data)
    {
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $address = $data['address'] ?? '';

        $sql = "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare UPDATE profile lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("sssi", $name, $phone, $address, $id);

        return $stmt->execute();
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Cập nhật mật khẩu mới cho user
     * @param int $id
     * @param string $hashedPassword Mật khẩu đã được hash
     * @return bool
     */
    public function updatePassword($id, $hashedPassword)
    {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $id);
        return $stmt->execute();
    }
}