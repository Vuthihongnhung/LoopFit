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
                    p.stock,
                    p.category_id,
                    p.colors,
                    p.sizes,
                    p.gender,
                    p.rented_count,
                    p.rented_quantity,
                    c.name as category_name,
                    p.created_at
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
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
    public function create($name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $sizes = null, $colors = null, $gender = 'unisex', $rented_count = 0)
    {
        if (is_array($sizes)) $sizes = implode(',', $sizes);
        if (is_array($colors)) $colors = implode(',', $colors);
        
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
        
        $sql = "INSERT INTO products (name, price, discount_percent, deposit_price, description, image, stock, category_id, category, sizes, colors, gender, rented_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param("sdddsssissssi", $name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $category, $sizes, $colors, $gender, $rented_count);
        return $stmt->execute();
    }
    
    /* ===== Cập nhật sản phẩm ===== */
    public function update($id, $name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $sizes, $colors, $gender, $rented_count)
    {
        if (is_array($sizes)) $sizes = implode(',', $sizes);
        if (is_array($colors)) $colors = implode(',', $colors);
        
        $sql = "UPDATE products SET 
                    name = ?,
                    price = ?,
                    discount_percent = ?,
                    deposit_price = ?,
                    description = ?,
                    image = ?,
                    stock = ?,
                    category_id = ?,
                    sizes = ?,
                    colors = ?,
                    gender = ?,
                    rented_count = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param("sdiississsssi", $name, $price, $discount_percent, $deposit_price, $description, $image, $stock, $category_id, $sizes, $colors, $gender, $rented_count, $id);
        return $stmt->execute();
    }
    
    /* ===== Cập nhật riêng colors và sizes ===== */
    public function updateColorsAndSizes($id, $colors, $sizes)
    {
        $colorsStr = is_array($colors) ? implode(',', $colors) : $colors;
        $sizesStr = is_array($sizes) ? implode(',', $sizes) : $sizes;
        
        $stmt = $this->conn->prepare("UPDATE products SET colors = ?, sizes = ? WHERE id = ?");
        $stmt->bind_param("ssi", $colorsStr, $sizesStr, $id);
        return $stmt->execute();
    }
    
    /* ===== Xóa cứng sản phẩm cùng toàn bộ dữ liệu liên quan (cart, order_details, reviews) ===== */
    public function deleteCascade($id)
    {
        $this->conn->begin_transaction();
        try {
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
                       COUNT(r.id) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
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
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
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
    public function getAvailableQuantity($productId, $startDate, $endDate)
    {
        $sql = "SELECT COALESCE(SUM(od.quantity), 0) as rented
                FROM order_details od
                INNER JOIN orders o ON o.id = od.order_id
                WHERE od.product_id = ?
                AND o.status IN ('confirmed', 'rented')
                AND o.actual_return_date IS NULL
                AND o.rent_date <= ?
                AND o.return_date >= ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("getAvailableQuantity prepare failed: " . $this->conn->error);
            return 0;
        }
        $stmt->bind_param('iss', $productId, $endDate, $startDate);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $rentedInPeriod = $result['rented'] ?? 0;
        
        $product = $this->getById($productId);
        $totalStock = $product ? $product['stock'] : 0;
        return max($totalStock - $rentedInPeriod, 0);
    }
    
    /* ===== Tăng rented_quantity (số lượng đang thuê hiện tại) khi xác nhận đơn ===== */
    public function increaseRentedQuantity($productId, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE products SET rented_quantity = rented_quantity + ? WHERE id = ?");
        if (!$stmt) {
            error_log("increaseRentedQuantity prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param('ii', $quantity, $productId);
        return $stmt->execute();
    }
    
    /* ===== Giảm rented_quantity (số lượng đang thuê) khi trả hàng bình thường ===== */
    public function decreaseRentedQuantity($productId, $quantity)
    {
        $sql = "UPDATE products SET rented_quantity = rented_quantity - ? WHERE id = ? AND rented_quantity >= ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("decreaseRentedQuantity prepare error: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param('iii', $quantity, $productId, $quantity);
        return $stmt->execute();
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