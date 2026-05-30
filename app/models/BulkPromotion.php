<?php
namespace App\Models;

use Core\Model;

class BulkPromotion extends Model
{
    /* ===== Lấy danh sách tất cả chương trình khuyến mãi ===== */
    public function getAll()
{
    $sql = "SELECT bp.*, 
                   CASE 
                       WHEN bp.scope_type = 'all_products' THEN (SELECT COUNT(*) FROM products)
                       WHEN bp.scope_type = 'category' THEN (
                           SELECT COUNT(*) FROM products WHERE category_id IN (
                               SELECT category_id FROM bulk_promotion_categories WHERE bulk_promotion_id = bp.id
                           )
                       )
                       WHEN bp.scope_type = 'specific_products' THEN (
                           SELECT COUNT(*) FROM bulk_promotion_items WHERE bulk_promotion_id = bp.id
                       )
                       ELSE 0
                   END as product_count
            FROM bulk_promotions bp
            ORDER BY bp.id DESC";
    $result = $this->conn->query($sql);
    if (!$result) return [];
    $promotions = [];
    while ($row = $result->fetch_assoc()) {
        $promotions[] = $row;
    }
    return $promotions;
}

    /* ===== Lấy chi tiết chương trình khuyến mãi theo ID ===== */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM bulk_promotions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ===== Tạo mới một chương trình khuyến mãi ===== */
    public function create($name, $promotion_type, $discount_value, $fixed_price, $scope_type, $start_date, $end_date, $is_active = 1, $category_id = null)
{
    $stmt = $this->conn->prepare(
        "INSERT INTO bulk_promotions (name, promotion_type, discount_value, fixed_price, scope_type, category_id, start_date, end_date, is_active) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssddssssi", $name, $promotion_type, $discount_value, $fixed_price, $scope_type, $category_id, $start_date, $end_date, $is_active);
    return $stmt->execute();
}

    /* ===== Cập nhật chương trình khuyến mãi ===== */
    public function update($id, $name, $promotion_type, $discount_value, $fixed_price, $scope_type, $start_date, $end_date, $is_active = 1, $category_id = null)
{
    $stmt = $this->conn->prepare(
        "UPDATE bulk_promotions 
         SET name = ?, promotion_type = ?, discount_value = ?, fixed_price = ?, scope_type = ?, category_id = ?, start_date = ?, end_date = ?, is_active = ? 
         WHERE id = ?"
    );
    $stmt->bind_param("ssddssssii", $name, $promotion_type, $discount_value, $fixed_price, $scope_type, $category_id, $start_date, $end_date, $is_active, $id);
    return $stmt->execute();
}

    /* ===== Xóa chương trình khuyến mãi ===== */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM bulk_promotions WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Xóa các sản phẩm liên kết
            $stmtItems = $this->conn->prepare("DELETE FROM bulk_promotion_items WHERE bulk_promotion_id = ?");
            $stmtItems->bind_param("i", $id);
            return $stmtItems->execute();
        }
        return false;
    }

    /* ===== Lấy ID của bản ghi vừa được insert ===== */
    public function getLastId()
    {
        return $this->conn->insert_id;
    }

    /* ===== Lấy các sản phẩm áp dụng chương trình khuyến mãi ===== */
    public function getPromotionProducts($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT bpi.*, p.name as product_name 
             FROM bulk_promotion_items bpi
             LEFT JOIN products p ON bpi.product_id = p.id
             WHERE bpi.bulk_promotion_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    /* ===== Lấy chương trình khuyến mãi áp dụng cho sản phẩm ===== */
    public function getPromotionsByProduct($product_id)
    {
        $sql = "SELECT bp.* FROM bulk_promotions bp
                INNER JOIN bulk_promotion_items bpi ON bp.id = bpi.bulk_promotion_id
                WHERE bpi.product_id = ? AND bp.is_active = 1
                AND bp.start_date <= NOW() AND bp.end_date >= NOW()
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ===== Lấy chương trình khuyến mãi áp dụng cho danh mục (hỗ trợ cả danh mục cha) ===== */
public function getPromotionsByCategory($category_id)
{
    // Lấy tất cả danh mục con (bao gồm cả chính nó)
    $categoryModel = new \App\Models\Category();
    $allCategoryIds = $categoryModel->getAllDescendants($category_id);
    if (empty($allCategoryIds)) {
        $allCategoryIds = [$category_id];
    }
    
    // Tạo placeholder cho IN clause
    $placeholders = implode(',', array_fill(0, count($allCategoryIds), '?'));
    
    $sql = "SELECT DISTINCT bp.* FROM bulk_promotions bp
            INNER JOIN bulk_promotion_categories bpc ON bp.id = bpc.bulk_promotion_id
            WHERE bpc.category_id IN ($placeholders)
            AND bp.scope_type = 'category'
            AND bp.is_active = 1
            AND bp.start_date <= NOW() 
            AND bp.end_date >= NOW()
            LIMIT 1";
    
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return null;
    
    // Bind các category_id
    $types = str_repeat('i', count($allCategoryIds));
    $stmt->bind_param($types, ...$allCategoryIds);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

    /* ===== Lấy chương trình khuyến mãi toàn bộ sản phẩm ===== */
    public function getPromotionsForAllProducts()
    {
        $sql = "SELECT * FROM bulk_promotions 
                WHERE scope_type = 'all_products' 
                AND is_active = 1
                AND start_date <= NOW() AND end_date >= NOW()
                LIMIT 1";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
    /* ===== Lưu danh sách danh mục cho chương trình (tự động lấy cả danh mục con) ===== */
public function saveCategories($bulk_promotion_id, $category_ids)
{
    // Xóa cũ
    $stmt = $this->conn->prepare("DELETE FROM bulk_promotion_categories WHERE bulk_promotion_id = ?");
    $stmt->bind_param("i", $bulk_promotion_id);
    $stmt->execute();
    
    if (empty($category_ids)) return true;
    
    // Lấy tất cả danh mục con (bao gồm cả cha)
    $categoryModel = new \App\Models\Category();
    $allCategoryIds = [];
    foreach ($category_ids as $cat_id) {
        $descendants = $categoryModel->getAllDescendants($cat_id);
        $allCategoryIds = array_merge($allCategoryIds, $descendants);
    }
    $allCategoryIds = array_unique($allCategoryIds);
    
    // Lưu tất cả
    $sql = "INSERT INTO bulk_promotion_categories (bulk_promotion_id, category_id) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    foreach ($allCategoryIds as $cat_id) {
        $stmt->bind_param("ii", $bulk_promotion_id, $cat_id);
        $stmt->execute();
    }
    return true;
}

/* ===== Lấy danh sách danh mục của chương trình ===== */
public function getCategoryIds($bulk_promotion_id)
{
    $stmt = $this->conn->prepare("SELECT category_id FROM bulk_promotion_categories WHERE bulk_promotion_id = ?");
    $stmt->bind_param("i", $bulk_promotion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['category_id'];
    }
    return $ids;
}
/* ===== Lưu danh sách sản phẩm cho chương trình ===== */
public function saveProducts($bulk_promotion_id, $product_ids)
{
    // Xóa cũ
    $stmt = $this->conn->prepare("DELETE FROM bulk_promotion_items WHERE bulk_promotion_id = ?");
    $stmt->bind_param("i", $bulk_promotion_id);
    $stmt->execute();
    
    if (empty($product_ids)) return true;
    
    $sql = "INSERT INTO bulk_promotion_items (bulk_promotion_id, product_id) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    foreach ($product_ids as $pid) {
        $stmt->bind_param("ii", $bulk_promotion_id, $pid);
        $stmt->execute();
    }
    return true;
}
}
