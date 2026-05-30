<?php

namespace App\Models;

use Core\Model;

class Product extends Model
{
    /* ===== Lấy tất cả sản phẩm kèm tên danh mục ===== */
    public function getAllWithCategory()
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.discount_percent,
                    p.deposit_price,
                    p.description,
                    p.image,
                    COALESCE(SUM(v.stock), p.stock) AS stock,
                    p.category_id,
                    p.gender,
                    p.rented_count,
                    p.rented_quantity,
                    c.name as category_name,
                    p.created_at,
                    COALESCE(SUM(v.rented_quantity), p.rented_quantity, 0) AS current_rented
                FROM products p
                LEFT JOIN product_variants v ON p.id = v.product_id
                LEFT JOIN categories c ON p.category_id = c.id
                GROUP BY p.id
                ORDER BY p.id DESC";
        
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /* ===== Lấy thông tin sản phẩm theo ID ===== */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function find($id)
    {
        return $this->getById($id);
    }
    
    /* ===== Lấy ảnh sản phẩm theo ID (dùng khi xóa) ===== */
    public function getImageById($id)
    {
        $stmt = $this->conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /* ===== Thêm sản phẩm mới ===== */
    public function create($name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $gender = 'unisex', $rented_count = 0)
    {
        $category = '';
        $catStmt = $this->conn->prepare("
            SELECT c.name AS category_name, p.name as parent_name 
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            WHERE c.id = ?
        ");
        if ($catStmt) {
            $catStmt->bind_param("i", $category_id);
            $catStmt->execute();
            $catResult = $catStmt->get_result();
            if ($row = $catResult->fetch_assoc()) {
                $category = $row['parent_name'] ?: $row['category_name'];
            }
            $catStmt->close();
        }
        
        $sql = "INSERT INTO products (name, price, discount_percent, deposit_price, description, image, stock, category_id, category, gender, rented_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Product::create prepare error: ' . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("sdidssiissi", $name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $category, $gender, $rented_count);
        if (!$stmt->execute()) {
            error_log('Product::create execute error: ' . $stmt->error);
            return false;
        }
        return true;
    }
    
    /* ===== Cập nhật sản phẩm ===== */
    public function update($id, $name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $gender, $rented_count)
    {
        $category = '';
        $catStmt = $this->conn->prepare("\n            SELECT c.name AS category_name, p.name as parent_name 
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            WHERE c.id = ?
        ");
        if ($catStmt) {
            $catStmt->bind_param("i", $category_id);
            $catStmt->execute();
            $catResult = $catStmt->get_result();
            if ($row = $catResult->fetch_assoc()) {
                $category = $row['parent_name'] ?: $row['category_name'];
            }
            $catStmt->close();
        }
        
        $sql = "UPDATE products SET 
                    name = ?,
                    price = ?,
                    discount_percent = ?,
                    deposit_price = ?,
                    description = ?,
                    image = ?,
                    stock = ?,
                    category_id = ?,
                    category = ?,
                    gender = ?,
                    rented_count = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Product::update prepare error: ' . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("sdidssiissii", $name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $category, $gender, $rented_count, $id);
        if (!$stmt->execute()) {
            error_log('Product::update execute error: ' . $stmt->error);
            return false;
        }
        return true;
    }
    
    /* ===== Cập nhật riêng colors và sizes ===== */
    public function updateColorsAndSizes($id, $colors, $sizes)
    {
        error_log('Product::updateColorsAndSizes is not supported on the current schema; use product_variants instead.');
        return false;
    }

    public function increaseVariantRentedQuantity($productId, $color, $size, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE product_variants SET rented_quantity = rented_quantity + ? WHERE product_id = ? AND color = ? AND size = ?");
        if (!$stmt) {
            error_log('Product::increaseVariantRentedQuantity prepare error: ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param('iiss', $quantity, $productId, $color, $size);
        return $stmt->execute();
    }

    public function decreaseVariantRentedQuantity($productId, $color, $size, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE product_variants SET rented_quantity = rented_quantity - ? WHERE product_id = ? AND color = ? AND size = ? AND rented_quantity >= ?");
        if (!$stmt) {
            error_log('Product::decreaseVariantRentedQuantity prepare error: ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param('iissi', $quantity, $productId, $color, $size, $quantity);
        return $stmt->execute();
    }

    public function getVariantsByProductId($productId)
    {
        $stmt = $this->conn->prepare("SELECT id, color, size, stock, rented_quantity, (stock - rented_quantity) AS available FROM product_variants WHERE product_id = ? ORDER BY id ASC");
        if (!$stmt) return [];
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteVariantsByProductId($productId)
    {
        $stmt = $this->conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $productId);
        return $stmt->execute();
    }

    public function saveVariants($productId, $variants)
    {
        $this->conn->begin_transaction();
        try {
            $deleteStmt = $this->conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
            if (!$deleteStmt) throw new \Exception($this->conn->error);
            $deleteStmt->bind_param('i', $productId);
            $deleteStmt->execute();
            $deleteStmt->close();

            if (!empty($variants)) {
                $insertStmt = $this->conn->prepare("INSERT INTO product_variants (product_id, color, size, stock, rented_quantity) VALUES (?, ?, ?, ?, ?)");
                if (!$insertStmt) throw new \Exception($this->conn->error);
                foreach ($variants as $variant) {
                    $color = trim($variant['color'] ?? '');
                    $size = trim($variant['size'] ?? '');
                    $stock = max(0, (int)($variant['stock'] ?? 0));
                    $rentedQuantity = max(0, (int)($variant['rented_quantity'] ?? 0));
                    if ($color === '' || $size === '') {
                        continue;
                    }
                    $insertStmt->bind_param('issii', $productId, $color, $size, $stock, $rentedQuantity);
                    $insertStmt->execute();
                }
                $insertStmt->close();
            }

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log('saveVariants error: ' . $e->getMessage());
            return false;
        }
    }

    public function getTotalStockByProductId($productId)
    {
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(stock), 0) as total_stock FROM product_variants WHERE product_id = ?");
        if (!$stmt) return 0;
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)($result['total_stock'] ?? 0);
    }

    /* ===== Xóa cứng sản phẩm cùng toàn bộ dữ liệu liên quan (cart, order_details, reviews) ===== */
    public function deleteCascade($id)
    {
        $this->conn->begin_transaction();
        try {
            // Xóa biến thể sản phẩm
            $stmt = $this->conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new \Exception("Lỗi xóa biến thể: " . $stmt->error);
            $stmt->close();

            // 1. Xóa trong bảng cart
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new \Exception("Lỗi xóa cart: " . $stmt->error);
            $stmt->close();
            
            // 2. Cập nhật order_details: đặt product_id = NULL (giữ lịch sử)
            $stmt = $this->conn->prepare("UPDATE order_details SET product_id = NULL WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new \Exception("Lỗi cập nhật order_details: " . $stmt->error);
            $stmt->close();
            
            // 3. Xóa reviews
            $stmt = $this->conn->prepare("DELETE FROM reviews WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new \Exception("Lỗi xóa reviews: " . $stmt->error);
            $stmt->close();
            
            // 4. Xóa sản phẩm
            $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new \Exception("Lỗi xóa sản phẩm: " . $stmt->error);
            if ($stmt->affected_rows == 0) throw new \Exception("Sản phẩm không tồn tại");
            $stmt->close();
            
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }
    
    // ==================== LỌC, TÌM KIẾM, PHÂN TRANG ====================
    
    /* ===== Lọc sản phẩm theo danh mục, giới tính, giá, sắp xếp, tìm kiếm ===== */
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
        if (!$stmt) return [];
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getProductsByCategories($categoryIds, $gender = 'all', $priceRange = 'all', $sort = 'default')
    {
        return $this->filterProducts($categoryIds, $gender, $priceRange, $sort);
    }
    
    /* ===== Tìm kiếm sản phẩm theo tên (trả về id, name, price, image) ===== */
    public function searchByName($keyword)
    {
        $stmt = $this->conn->prepare("SELECT id, name, price, image FROM products WHERE name LIKE CONCAT('%', ?, '%') LIMIT 10");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /* ===== Lấy sản phẩm cùng danh mục (gợi ý) ===== */
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4)
    {
        if (empty($categoryId)) return [];
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT ?");
        $stmt->bind_param("iii", $categoryId, $excludeId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // ==================== SẢN PHẨM NỔI BẬT, MỚI, GIẢM GIÁ ====================
    
    /* ===== Lấy sản phẩm nổi bật (theo rented_count và mới nhất) ===== */
    public function getFeatured($limit = 8)
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(r.rating), 0) AS avg_rating, 
                       COUNT(r.id) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                GROUP BY p.id
                ORDER BY p.rented_count DESC, p.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /* ===== Lấy sản phẩm giảm giá ===== */
    public function getDiscountedProducts($limit = 100)
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(r.rating), 0) AS avg_rating, 
                       COUNT(r.id) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.discount_percent > 0
                GROUP BY p.id
                ORDER BY p.discount_percent DESC, p.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /* ===== Lấy sản phẩm mới nhất ===== */
    public function getNewProducts($limit = 10)
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(r.rating), 0) AS avg_rating, 
                       COUNT(r.id) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                GROUP BY p.id
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // ==================== THÔNG TIN CHI TIẾT + RATING ====================

    /* ===== Lấy sản phẩm kèm rating trung bình và tổng số đánh giá ===== */
    public function getWithRating($id)
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(r.rating), 0) AS avg_rating, 
                       COUNT(r.id) AS total_reviews,
                       COALESCE(SUM(v.stock), p.stock) AS stock
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                LEFT JOIN product_variants v ON p.id = v.product_id
                WHERE p.id = ?
                GROUP BY p.id";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /* ===== Lấy chi tiết sản phẩm kèm tên danh mục ===== */
    public function getProductDetail($id)
    {
        $sql = "SELECT p.*, c.name as category_name, COALESCE(SUM(v.stock), p.stock) AS stock 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN product_variants v ON p.id = v.product_id
                WHERE p.id = ?
                GROUP BY p.id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /* ===== Tính giá hiện tại sau giảm giá ===== */
    public function getCurrentPrice($price, $discountPercent)
    {
        return $discountPercent > 0 ? $price * (100 - $discountPercent) / 100 : $price;
    }
    
    // ==================== LOGIC TỒN KHO CHO THUÊ THEO THỜI GIAN ====================
    
    /* ===== Lấy số lượng còn trống để cho thuê trong khoảng thời gian ===== */
    public function getAvailableQuantity($productId, $startDate, $endDate, $color = null, $size = null)
    {
        $sql = "SELECT COALESCE(SUM(od.quantity), 0) as rented
                FROM order_details od
                INNER JOIN orders o ON o.id = od.order_id
                WHERE od.product_id = ?
                AND o.status IN ('confirmed', 'rented')
                AND o.actual_return_date IS NULL
                AND o.rent_date <= ?
                AND o.return_date >= ?";

        $params = [$productId, $endDate, $startDate];
        $types = 'iss';

        if ($size !== null && $size !== '') {
            $sql .= " AND od.size = ?";
            $types .= 's';
            $params[] = $size;
        }
        if ($color !== null && $color !== '') {
            $sql .= " AND od.color = ?";
            $types .= 's';
            $params[] = $color;
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("getAvailableQuantity prepare failed: " . $this->conn->error);
            return 0;
        }

        $bindParams = [$types];
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $rentedInPeriod = (int)($result['rented'] ?? 0);

        $stockSql = "SELECT COALESCE(SUM(stock), 0) as total_stock FROM product_variants WHERE product_id = ?";
        $stockParams = [$productId];
        $stockTypes = 'i';
        if ($size !== null && $size !== '') {
            $stockSql .= " AND size = ?";
            $stockTypes .= 's';
            $stockParams[] = $size;
        }
        if ($color !== null && $color !== '') {
            $stockSql .= " AND color = ?";
            $stockTypes .= 's';
            $stockParams[] = $color;
        }

        $stockStmt = $this->conn->prepare($stockSql);
        if (!$stockStmt) {
            error_log("getAvailableQuantity stock prepare failed: " . $this->conn->error);
            return max($this->getTotalStockByProductId($productId) - $rentedInPeriod, 0);
        }

        $bindParams = [$stockTypes];
        foreach ($stockParams as $key => $value) {
            $bindParams[] = &$stockParams[$key];
        }
        call_user_func_array([$stockStmt, 'bind_param'], $bindParams);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result()->fetch_assoc();
        $totalStock = (int)($stockResult['total_stock'] ?? 0);

        if ($totalStock === 0) {
            $product = $this->getById($productId);
            $totalStock = $product ? (int)($product['stock'] ?? 0) : 0;
        }

        return max($totalStock - $rentedInPeriod, 0);
    }
    
    /* ===== Legacy rented_quantity helper for compatibility ===== */
    public function increaseRentedQuantity($productId, $quantity)
    {
        error_log("Product::increaseRentedQuantity is deprecated; use increaseVariantRentedQuantity() with color and size.");
        return false;
    }
    
    /* ===== Legacy rented_quantity helper for compatibility ===== */
    public function decreaseRentedQuantity($productId, $quantity)
    {
        error_log("Product::decreaseRentedQuantity is deprecated; use decreaseVariantRentedQuantity() with color and size.");
        return false;
    }

    /* ===== Giảm tổng số lượng sản phẩm (khi bị hỏng/mất) ===== */
    public function decreaseStock($productId, $quantity)
    {
        $sql = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("decreaseStock prepare error: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param('iii', $quantity, $productId, $quantity);
        return $stmt->execute();
    }
    
    /* ===== Tăng rented_count (tổng số lượt thuê, dùng cho thống kê) ===== */
    public function increaseRentedCount($productId, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE products SET rented_count = rented_count + ? WHERE id = ?");
        $stmt->bind_param('ii', $quantity, $productId);
        return $stmt->execute();
    }
    
    /* ===== Lấy số lượng đang thuê HIỆN TẠI (tính đến ngày hôm nay) ===== */
    public function getCurrentRented($productId)
    {
        $sql = "SELECT COALESCE(SUM(od.quantity), 0) as total_rented
                FROM order_details od
                JOIN orders o ON o.id = od.order_id
                WHERE od.product_id = ? 
                AND o.status IN ('confirmed', 'rented')
                AND o.actual_return_date IS NULL
                AND o.rent_date <= CURDATE()
                AND o.return_date >= CURDATE()";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("getCurrentRented prepare failed: " . $this->conn->error);
            return 0;
        }
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)($result['total_rented'] ?? 0);
    }
    
    
    // ==================== THỐNG KÊ CƠ BẢN ====================
    
    /* ===== Đếm tổng số sản phẩm ===== */
    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM products");
        return $result ? (int)$result->fetch_assoc()['total'] : 0;
    }

    /* ===== Lấy ID vừa insert (sau khi create) ===== */
    public function getLastInsertId()
    {
        return $this->conn->insert_id;
    }
}