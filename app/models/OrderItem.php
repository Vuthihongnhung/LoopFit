<?php

namespace App\Models;

use Core\Model;

class OrderItem extends Model
{
    /* ===== Tạo chi tiết đơn hàng ===== */
    private function getNextId() {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM order_details";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)($row['next_id'] ?? 1);
        }
        return 1;
    }

    public function create($data)
    {
        $order_id         = $data['order_id'] ?? 0;
        $product_id       = $data['product_id'] ?? 0;
        $quantity         = $data['quantity'] ?? 1;
        $price            = $data['price'] ?? ($data['price_per_day'] ?? 0);
        $deposit_per_item = $data['deposit_per_item'] ?? ($data['deposit_price'] ?? 0);
        $color            = $data['color'] ?? '';
        $size             = $data['size'] ?? '';
        $rental_start     = $data['rental_start'] ?? ($data['rent_date'] ?? null);
        $rental_end       = $data['rental_end'] ?? ($data['return_date'] ?? null);
        $subtotal         = $data['subtotal'] ?? 0;
        $nextId           = $this->getNextId();

        $sql = "INSERT INTO order_details 
                (id, order_id, product_id, quantity, price, deposit_per_item, color, size, rental_start, rental_end, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("OrderItem create prepare failed: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param(
            'iiiiddssssd',
            $nextId,
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

    /* ===== Lấy danh sách tất cả các sản phẩm (chi tiết) của một đơn hàng, kèm thông tin sản phẩm (tên, ảnh) ===== */
    public function getByOrder($orderId) {
        $sql = "SELECT 
                    od.id, 
                    od.order_id, 
                    od.product_id, 
                    od.quantity, 
                    od.price, 
                    od.deposit_per_item, 
                    od.color, 
                    od.size, 
                    od.rental_start, 
                    od.rental_end, 
                    od.subtotal, 
                    od.status_after_return,
                    p.name AS product_name, 
                    p.image 
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Xóa tất cả chi tiết đơn hàng theo mã đơn ===== */
    public function deleteByOrder($orderId)
    {
        $sql = "DELETE FROM order_details WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $orderId);
        return $stmt->execute();
    }

    /* ===== Lấy top sản phẩm được thuê nhiều nhất (dựa trên số lần xuất hiện trong order_details của đơn hoàn tất và đã thanh toán) ===== */
    public function getTopRentedProducts($limit = 10)
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    COUNT(od.id) AS total_rented,
                    SUM(od.subtotal) AS total_revenue
                FROM order_details od
                INNER JOIN products p ON od.product_id = p.id
                INNER JOIN orders o ON od.order_id = o.id
                WHERE o.status = 'completed' AND o.payment_status = 'paid'
                GROUP BY p.id
                ORDER BY total_rented DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Kiểm tra xem người dùng đã mua sản phẩm này (và đơn hàng đã hoàn tất/thành công) chưa ===== */
    public function hasUserBoughtProduct($productId, $userId)
    {
        if (!$this->conn) {
            error_log("OrderItem: Database connection not established");
            return false;
        }

        $check = $this->conn->query("SHOW TABLES LIKE 'order_details'");
        if ($check->num_rows == 0) {
            error_log("Table 'order_details' does not exist!");
            return false;
        }

        $sql = "SELECT od.id 
                FROM order_details od
                INNER JOIN orders o ON od.order_id = o.id
                WHERE od.product_id = ? 
                AND o.user_id = ? 
                AND o.status IN ('completed', 'delivered', 'returned')
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL Prepare Error: " . $this->conn->error);
            error_log("SQL: " . $sql);
            return false;
        }
        
        $stmt->bind_param("ii", $productId, $userId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /* ===== Lấy chi tiết một sản phẩm trong đơn hàng (kết hợp order_details + products) ===== */
    public function getByOrderAndProduct($order_id, $product_id) {
        $sql = "SELECT od.*, p.name AS product_name, p.image AS product_image
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                WHERE od.order_id = ? AND od.product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("ii", $order_id, $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ===== Cập nhật tình trạng sản phẩm sau khi trả (good, damaged, lost) ===== */
    public function updateReturnStatus($itemId, $statusAfterReturn) {
        $sql = "UPDATE order_details SET status_after_return = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $statusAfterReturn, $itemId);
        return $stmt->execute();
    }

    /* ===== Đếm số sản phẩm chưa được cập nhật tình trạng trả (status_after_return IS NULL) trong đơn hàng ===== */
    public function countNotReturnedByOrder($orderId) {
        $sql = "SELECT COUNT(*) as count FROM order_details WHERE order_id = ? AND status_after_return IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['count'];
    }

    /* ===== Lấy thông tin một bản ghi order_details theo ID ===== */
    public function find($id)
    {
        $sql = "SELECT * FROM order_details WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}