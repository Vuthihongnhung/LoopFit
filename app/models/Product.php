<?php
namespace App\Models;

use Core\Model;

class Product extends Model
{
    public function getAllWithCategory()
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.description,
                    p.image,
                    p.stock,
                    p.category_id,
                    c.name as category_name,
                    p.created_at
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return [];
        }
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    public function getImageById($id)
    {
        $stmt = $this->conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function update($id, $name, $price, $description, $image, $stock, $category_id)
    {
        $stmt = $this->conn->prepare("UPDATE products 
            SET name=?, price=?, description=?, image=?, stock=?, category_id=? 
            WHERE id=?");
        
        $stmt->bind_param("sdssiii", $name, $price, $description, $image, $stock, $category_id, $id);
        
        return $stmt->execute();
    }
    
    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM products");
        return $result->fetch_assoc()['total'];
    }
    
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}