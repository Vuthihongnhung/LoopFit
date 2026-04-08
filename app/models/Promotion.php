<?php
namespace App\Models;

use Core\Model;

class Promotion extends Model
{
    public function getAll()
    {
        $sql = "SELECT * FROM promotions ORDER BY id DESC";
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return [];
        }
        
        $promotions = [];
        while ($row = $result->fetch_assoc()) {
            $promotions[] = $row;
        }
        
        return $promotions;
    }
    
    public function checkExists($code)
    {
        $stmt = $this->conn->prepare("SELECT id FROM promotions WHERE code=?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    
    public function create($code, $discount, $start, $end)
    {
        $stmt = $this->conn->prepare("INSERT INTO promotions (code, discount_percent, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $code, $discount, $start, $end);
        return $stmt->execute();
    }
    
    public function getLastId()
    {
        return $this->conn->insert_id;
    }
     
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM promotions WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}