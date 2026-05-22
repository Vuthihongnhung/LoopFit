<?php
namespace App\Models;

use Core\Model;

class Review extends Model
{
    protected $table = 'reviews';

    /* ===== Lấy tất cả đánh giá của một sản phẩm ===== */
    public function getByProduct($product_id, $limit = 10)
    {
        $sql = "SELECT r.*, u.name as user_name 
                FROM {$this->table} r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? AND r.is_hidden = 0
                ORDER BY r.created_at DESC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL prepare error: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("ii", $product_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Kiểm tra user đã đánh giá sản phẩm này chưa ===== */
    public function hasReviewed($user_id, $product_id)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        return $count > 0;
    }

    /* ===== Thêm đánh giá mới ===== */
    public function add($product_id, $user_id, $rating, $comment)
    {
        $sql = "INSERT INTO {$this->table} (product_id, user_id, rating, comment, created_at, is_hidden) 
                VALUES (?, ?, ?, ?, NOW(), 0)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
        return $stmt->execute();
    }

    /* ===== Ẩn/hiện đánh giá (dùng cho admin) ===== */
    public function toggleHide($id, $is_hidden)
    {
        $sql = "UPDATE {$this->table} SET is_hidden = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $is_hidden, $id);
        return $stmt->execute();
    }

    /* ===== Xóa review ===== */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* ===== Lấy danh sách review của một user ===== */
    public function getByUser($user_id, $limit = 10, $offset = 0)
    {
        $sql = "SELECT r.*, p.name as product_name 
                FROM {$this->table} r
                JOIN products p ON r.product_id = p.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}