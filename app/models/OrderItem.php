<?php

namespace App\Models;

use Core\Model;

class OrderItem extends Model
{
    /**
     * Tạo chi tiết đơn hàng (lưu vào bảng order_details)
     */
    public function create($data)
    {
        $order_id        = $data['order_id'] ?? 0;
        $product_id      = $data['product_id'] ?? 0;
        $quantity        = $data['quantity'] ?? 1;
        $price           = $data['price'] ?? ($data['price_per_day'] ?? 0);
        $deposit_per_item = $data['deposit_per_item'] ?? ($data['deposit_price'] ?? 0);
        $color           = $data['color'] ?? '';
        $size            = $data['size'] ?? '';
        $rental_start    = $data['rental_start'] ?? ($data['rent_date'] ?? null);
        $rental_end      = $data['rental_end'] ?? ($data['return_date'] ?? null);
        $subtotal        = $data['subtotal'] ?? 0;

        $sql = "INSERT INTO order_details 
                (order_id, product_id, quantity, price, deposit_per_item, color, size, rental_start, rental_end, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("OrderItem create prepare failed: " . $this->conn->error);
            return false;
        }
        // Kiểu: i, i, i, d, d, s, s, s, s, d
        $stmt->bind_param(
            'iiiddssssd',
            $order_id,
            $product_id,
            $quantity,
            $price,
            $deposit_per_item,
            $color,
            $size,
            $rental_start,
            $rental_end,
            $subtotal
        );
        $result = $stmt->execute();
        if (!$result) {
            error_log("OrderItem create execute failed: " . $stmt->error);
        }
        return $result;
    }

    /**
     * Lấy danh sách sản phẩm của một đơn hàng từ bảng order_details
     */
    public function getByOrder($orderId) {
        $sql = "SELECT od.*, p.name AS product_name, p.image 
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Xóa tất cả detail của một đơn hàng
     */
    public function deleteByOrder($orderId)
    {
        $sql = "DELETE FROM order_details WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $orderId);
        return $stmt->execute();
    }
}