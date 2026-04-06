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

    // --- CÁC HÀM ADMIN (Giữ lại các hàm cũ đã thêm trước đó) ---
    // ... (các hàm getById, create, update, delete đã thêm ở bước trước)

    // --- HÀM LỌC SẢN PHẨM (Dành cho phía User - Fix lỗi hiện tại) ---
    public function getFilteredProducts($category_id, $gender, $price_range, $style = null) {
        if (!$this->conn) return []; 

        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        $types = "";

        // Lọc theo danh mục
        if (!empty($category_id)) {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        // Lọc theo giới tính
        if (!empty($gender) && $gender !== 'all') {
            $sql .= " AND (gender = ? OR gender = 'all')";
            $params[] = $gender;
            $types .= "s";
        }

        // Lọc theo khoảng giá
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