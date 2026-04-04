<?php
namespace App\Models;

use Core\Model;

class User extends Model
{
    public function getAll()
    {
        $sql = "SELECT id, name, email, created_at FROM users ORDER BY id DESC";
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
        $stmt = $this->conn->prepare("SELECT status FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
    
    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
        return $result->fetch_assoc()['total'];
    }
    
    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, password, role FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}