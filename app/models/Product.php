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
    
    public function getById($id) {
    $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

    /**
     * Lọc sản phẩm theo danh mục, giới tính, giá và sắp xếp
     *
     * @param int|null $categoryId
     * @param string $gender
     * @param string $priceRange
     * @param string $sort
     * @return array
     */
    public function filterProducts($categoryId = null, $gender = 'all', $priceRange = 'all', $sort = 'default')
{
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($categoryId)) {
        $sql .= " AND category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }
    if ($gender !== 'all') {
        $sql .= " AND gender = ?";
        $params[] = $gender;
        $types .= "s";
    }
    if ($priceRange !== 'all') {
        if ($priceRange == '0-100000') {
            $sql .= " AND price BETWEEN 0 AND 100000";
        } elseif ($priceRange == '100000-300000') {
            $sql .= " AND price BETWEEN 100000 AND 300000";
        } elseif ($priceRange == '300000-up') {
            $sql .= " AND price >= 300000";
        }
    }

    if ($sort == 'popular') {
        $sql .= " ORDER BY rented_count DESC";
    } else {
        $sql .= " ORDER BY id DESC";
    }

    $stmt = $this->conn->prepare($sql);
    if ($stmt === false) return [];
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    /**
     * Lấy sản phẩm cùng danh mục (dùng category_id)
     *
     * @param int $categoryId
     * @param int $excludeId
     * @param int $limit
     * @return array
     */
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4)
{
    if (empty($categoryId)) return [];
    $sql = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("iii", $categoryId, $excludeId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


    public function search($keyword, $limit = 30)
    {
        $keyword = trim($keyword);
        if ($keyword === '') return [];
        $like = '%' . $keyword . '%';
        $limit = max(1, min((int)$limit, 100));
        $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY id DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssi", $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
