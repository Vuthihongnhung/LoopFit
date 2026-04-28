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
     * Lọc sản phẩm theo danh mục (có thể là một ID hoặc mảng ID), giới tính, giá và sắp xếp
     *
     * @param int|array|null $categoryIds Một ID hoặc mảng các ID danh mục
     * @param string $gender
     * @param string $priceRange
     * @param string $sort  (default, popular, price_asc, price_desc)
     * @return array
     */
    public function filterProducts($categoryIds = null, $gender = 'all', $priceRange = 'all', $sort = 'default', $search = '')
{
    $sql = "SELECT p.*, 
                   COALESCE(AVG(r.rating), 0) AS avg_rating, 
                   COUNT(r.id) AS total_reviews
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($categoryIds)) {
        if (is_array($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= " AND p.category_id IN ($placeholders)";
            foreach ($categoryIds as $id) {
                $params[] = $id;
                $types .= "i";
            }
        } else {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryIds;
            $types .= "i";
        }
    }

    if ($gender !== 'all') {
        $sql .= " AND p.gender = ?";
        $params[] = $gender;
        $types .= "s";
    }
    if ($priceRange !== 'all') {
        if ($priceRange == '0-100000') {
            $sql .= " AND p.price BETWEEN 0 AND 100000";
        } elseif ($priceRange == '100000-300000') {
            $sql .= " AND p.price BETWEEN 100000 AND 300000";
        } elseif ($priceRange == '300000-up') {
            $sql .= " AND p.price >= 300000";
        }
    }
    if (!empty($search)) {
        $sql .= " AND p.name LIKE CONCAT('%', ?, '%')";
        $params[] = $search;
        $types .= "s";
    }

    $sql .= " GROUP BY p.id";

    if ($sort == 'popular') {
        $sql .= " ORDER BY p.rented_count DESC";
    } else {
        $sql .= " ORDER BY p.id DESC";
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
     * Lấy sản phẩm theo mảng danh mục (giữ tên cũ để tương thích)
     */
    public function getProductsByCategories($categoryIds, $gender = 'all', $priceRange = 'all', $sort = 'default')
    {
        return $this->filterProducts($categoryIds, $gender, $priceRange, $sort);
    }

    /**
     * Lấy sản phẩm cùng danh mục (dùng category_id)
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

    public function getFeatured($limit = 8) {
        $sql = "SELECT p.*, 
                    COALESCE(AVG(r.rating), 0) AS avg_rating, 
                    COUNT(r.id) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                GROUP BY p.id
                ORDER BY p.rented_count DESC, p.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getWithRating($id)
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(r.rating), 0) AS avg_rating, 
                       COUNT(r.id) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.id = ?
                GROUP BY p.id";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function searchByName($keyword) {
        $stmt = $this->conn->prepare("SELECT id, name, price, image FROM products WHERE name LIKE CONCAT('%', ?, '%') LIMIT 10");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}