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
    
    public function create($code, $discount, $min_order_value, $usage_limit, $start, $end)
{
    $stmt = $this->conn->prepare("INSERT INTO promotions (code, discount_percent, min_order_value, usage_limit, used_count, start_date, end_date) VALUES (?, ?, ?, ?, 0, ?, ?)");
    $stmt->bind_param("sddiss", $code, $discount, $min_order_value, $usage_limit, $start, $end);
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

    // Trong app/models/Promotion.php
public function getActiveVouchers()
{
    $sql = "SELECT * FROM promotions 
            WHERE start_date <= NOW() AND end_date >= NOW()
            ORDER BY 
                CASE 
                    WHEN discount_percent IS NOT NULL AND discount_percent > 0 THEN discount_percent 
                    ELSE 0 
                END DESC,
                discount_amount DESC";
    $result = $this->conn->query($sql);
    if (!$result) return [];
    return $result->fetch_all(MYSQLI_ASSOC);
}

public function getVoucherByCode($code)
{
    $code = $this->conn->real_escape_string($code);
    $sql = "SELECT * FROM promotions 
            WHERE code = '$code' 
              AND start_date <= NOW() 
              AND end_date >= NOW()";
    $result = $this->conn->query($sql);
    if (!$result || $result->num_rows === 0) return null;
    return $result->fetch_assoc();
}
public function getAllWithUsage()
{
    $sql = "SELECT 
                p.*,
                COUNT(o.id) as used_count
            FROM promotions p
            LEFT JOIN orders o ON o.promotion_id = p.id
            GROUP BY p.id
            ORDER BY p.id DESC";
    
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
public function update($id, $code, $discount, $min_order_value, $usage_limit, $start, $end)
{
    $stmt = $this->conn->prepare("UPDATE promotions SET code=?, discount_percent=?, min_order_value=?, usage_limit=?, start_date=?, end_date=? WHERE id=?");
    $stmt->bind_param("sddissi", $code, $discount, $min_order_value, $usage_limit, $start, $end, $id);
    return $stmt->execute();
}
}