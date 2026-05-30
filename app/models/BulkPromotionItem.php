<?php
namespace App\Models;

use Core\Model;

class BulkPromotionItem extends Model
{
    /* ===== Thêm sản phẩm vào chương trình khuyến mãi ===== */
    public function addProduct($bulk_promotion_id, $product_id)
    {
        // Kiểm tra sản phẩm đã có trong chương trình này chưa
        $stmt = $this->conn->prepare(
            "SELECT id FROM bulk_promotion_items WHERE bulk_promotion_id = ? AND product_id = ?"
        );
        $stmt->bind_param("ii", $bulk_promotion_id, $product_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return false; // Sản phẩm đã tồn tại
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO bulk_promotion_items (bulk_promotion_id, product_id) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $bulk_promotion_id, $product_id);
        return $stmt->execute();
    }

    /* ===== Xóa sản phẩm khỏi chương trình khuyến mãi ===== */
    public function removeProduct($bulk_promotion_id, $product_id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM bulk_promotion_items WHERE bulk_promotion_id = ? AND product_id = ?"
        );
        $stmt->bind_param("ii", $bulk_promotion_id, $product_id);
        return $stmt->execute();
    }

    /* ===== Lấy danh sách sản phẩm của chương trình khuyến mãi ===== */
    public function getItems($bulk_promotion_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT bpi.*, p.name as product_name, p.price 
             FROM bulk_promotion_items bpi
             LEFT JOIN products p ON bpi.product_id = p.id
             WHERE bpi.bulk_promotion_id = ?"
        );
        $stmt->bind_param("i", $bulk_promotion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    /* ===== Xóa tất cả sản phẩm của chương trình ===== */
    public function removeAllItems($bulk_promotion_id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM bulk_promotion_items WHERE bulk_promotion_id = ?"
        );
        $stmt->bind_param("i", $bulk_promotion_id);
        return $stmt->execute();
    }
}
