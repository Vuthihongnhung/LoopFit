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
                p.deposit_price,
                p.description,
                p.image,
                p.stock,
                p.category_id,
                p.colors,
                p.sizes,
                p.gender,
                p.rented_count,
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
    
public function update($id, $name, $price, $deposit_price, $description, $image, $stock, $category_id, $sizes, $colors, $gender, $rented_count = 0)
{
    $stmt = $this->conn->prepare("UPDATE products 
        SET name=?, price=?, deposit_price=?, description=?, image=?, stock=?, category_id=?, sizes=?, colors=?, gender=?, rented_count=? 
        WHERE id=?");
    
    // Chuỗi đúng: s (name), d (price), d (deposit_price), s (description), s (image), i (stock), i (category_id), s (sizes), s (colors), s (gender), i (rented_count), i (id)
    $stmt->bind_param("sddssiisssii", $name, $price, $deposit_price, $description, $image, $stock, $category_id, $sizes, $colors, $gender, $rented_count, $id);
    
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

    // ===== THÊM METHOD CREATE (quan trọng) =====
  // ===== METHOD CREATE (đã sửa - thêm deposit_price, sizes, colors, gender) =====
public function create($name, $price, $deposit_price, $description, $image, $stock, $category_id, $sizes = null, $colors = null, $gender = 'unisex', $rented_count = 0)
{
    if (is_array($sizes)) {
        $sizes = implode(',', $sizes);
    }
    if (is_array($colors)) {
        $colors = implode(',', $colors);
    }
    
    // Lấy tên danh mục LỚN (parent) từ category_id
    $category = '';
    $catStmt = $this->conn->prepare("
        SELECT p.name as parent_name 
        FROM categories c 
        LEFT JOIN categories p ON c.parent_id = p.id 
        WHERE c.id = ?
    ");
    if ($catStmt) {
        $catStmt->bind_param("i", $category_id);
        $catStmt->execute();
        $catResult = $catStmt->get_result();
        if ($row = $catResult->fetch_assoc()) {
            $category = $row['parent_name'];
        }
        $catStmt->close();
    }
    
    $sql = "INSERT INTO products (name, price, deposit_price, description, image, stock, category_id, category, sizes, colors, gender, rented_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $this->conn->prepare($sql);
    
    if (!$stmt) {
        die("Prepare error: " . $this->conn->error);
    }
    
    $stmt->bind_param("sdsssisssssi", $name, $price, $deposit_price, $description, $image, $stock, $category_id, $category, $sizes, $colors, $gender, $rented_count);
    if (!$stmt->execute()) {
        die("Execute error: " . $stmt->error);
    }
    
    return true;
}
        public function updateColorsAndSizes($id, $colors, $sizes)
    {
        $colorsStr = is_array($colors) ? implode(',', $colors) : $colors;
        $sizesStr = is_array($sizes) ? implode(',', $sizes) : $sizes;
        
        $stmt = $this->conn->prepare("UPDATE products SET colors = ?, sizes = ? WHERE id = ?");
        $stmt->bind_param("ssi", $colorsStr, $sizesStr, $id);
        return $stmt->execute();
    }
    public function getLastInsertId()
{
    return $this->conn->insert_id;
}
public function delete($id)
{
    $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
}