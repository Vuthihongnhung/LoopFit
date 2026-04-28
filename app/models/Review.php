<?php
namespace App\Models;

use Core\Model;

class Review extends Model
{
    protected $table = 'reviews';

    /**
     * Lấy tất cả đánh giá của một sản phẩm (kèm tên người dùng)
     */
    public function getByProduct($product_id)
    {
        $sql = "SELECT r.*, u.name as user_name 
                FROM {$this->table} r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? 
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Kiểm tra user đã đánh giá sản phẩm này chưa
     */
    public function hasReviewed($user_id, $product_id)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        return $count > 0;
    }

    /**
     * Thêm đánh giá mới
     */
    public function add($product_id, $user_id, $rating, $comment)
    {
        $sql = "INSERT INTO {$this->table} (product_id, user_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
        return $stmt->execute();
    }
}