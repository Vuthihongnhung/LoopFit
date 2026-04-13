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
                    pc.name as menu_category_name,
                    p.created_at
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_categories pc ON p.menu_category_id = pc.id
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
    
    public function update($id, $name, $price, $description, $image, $stock, $menu_category_id)
    {
        $stmt = $this->conn->prepare("UPDATE products 
            SET name=?, price=?, description=?, image=?, stock=?, menu_category_id=? 
            WHERE id=?");
        
        $stmt->bind_param("sdssiii", $name, $price, $description, $image, $stock, $menu_category_id, $id);
        
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
    
    public function create($name, $price, $description, $image, $stock, $menu_category_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO products (name, price, description, image, stock, menu_category_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssii", $name, $price, $description, $image, $stock, $menu_category_id);
        return $stmt->execute();
    }
    
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Hàm lọc sản phẩm cho User
    public function getFilteredProducts($category_id, $gender, $price_range, $style = null) {
        if (!$this->conn) return []; 

        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($category_id)) {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        if (!empty($gender) && $gender !== 'all') {
            $sql .= " AND (gender = ? OR gender = 'all')";
            $params[] = $gender;
            $types .= "s";
        }

        if (!empty($price_range) && $price_range !== 'all') {
            $parts = explode('-', $price_range);
            if (isset($parts[1]) && $parts[1] === 'up') {
                $sql .= " AND price >= ?";
                $params[] = (float)$parts[0];
                $types .= "d";
            } elseif (isset($parts[1])) {
                $sql .= " AND price BETWEEN ? AND ?";
                $params[] = (float)$parts[0];
                $params[] = (float)$parts[1];
                $types .= "dd";
            }
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}