<?php

namespace App\Models;

use Core\Model;

class Cart extends Model
{
    /* ===== Lấy danh sách sản phẩm trong giỏ của một người dùng (có tính số ngày thuê, thành tiền) ===== */
    public function getByUser($userId, $selectedOnly = false)
    {
        $sql = "SELECT 
                    c.id AS cart_id,
                    c.product_id,
                    c.quantity,
                    c.size,
                    c.color,
                    c.rent_date,
                    c.return_date,
                    c.price AS unit_price,  
                    p.name,
                    p.image,
                    p.deposit_price,
                    DATEDIFF(c.return_date, c.rent_date) AS days
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";
        
        if ($selectedOnly) {
            $sql .= " AND c.selected = 1";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($items as &$item) {
            $days = (int)($item['days'] ?? 1);
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $depositPrice = (float)($item['deposit_price'] ?? 0);
            $qty = (int)($item['quantity'] ?? 1);
            
            $item['subtotal'] = $unitPrice * $qty * $days;
            $item['deposit'] = $depositPrice * $qty;
            $item['days'] = $days;
            $item['price'] = $unitPrice; 
        }
        
        return $items;
    }

    /* ===== Lấy các sản phẩm trong giỏ đã được chọn (selected = 1) dùng cho trang thanh toán ===== */
    public function getCartItemsForCheckout($userId)
    {
        return $this->getByUser($userId, true);
    }

    /* ===== Tìm một mục trong giỏ theo cart_id và user_id ===== */
    public function findById($cartId, $userId) {
        $stmt = $this->conn->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ===== Cập nhật số lượng của một sản phẩm trong giỏ ===== */
    public function updateQuantityById($cartId, $userId, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cartId, $userId);
        return $stmt->execute();
    }

    /* ===== Cập nhật thông tin size, màu sắc, ngày thuê/trả của một sản phẩm trong giỏ ===== */
    public function updateItem($cartId, $userId, $size, $color, $rentDate = null, $returnDate = null)
    {
        if ($rentDate && $returnDate) {
            $stmt = $this->conn->prepare("UPDATE cart SET size = ?, color = ?, rent_date = ?, return_date = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $size, $color, $rentDate, $returnDate, $cartId, $userId);
        } else {
            $stmt = $this->conn->prepare("UPDATE cart SET size = ?, color = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssii", $size, $color, $cartId, $userId);
        }
        return $stmt->execute();
    }

    /* ===== Xóa một sản phẩm khỏi giỏ hàng ===== */
    public function removeById($cartId, $userId)
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        return $stmt->execute();
    }

    /* ===== Kiểm tra xem một sản phẩm với đầy đủ thuộc tính (size, màu, ngày thuê/trả) đã tồn tại trong giỏ chưa
     * Dùng để tránh trùng lặp khi thêm sản phẩm cùng biến thể ===== */
    public function findItem($userId, $productId, $size, $color, $rentDate, $returnDate)
    {
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ? AND rent_date = ? AND return_date = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissss", $userId, $productId, $size, $color, $rentDate, $returnDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }


    /* ===== Thêm sản phẩm vào giỏ hàng (hoặc cập nhật số lượng nếu đã tồn tại cùng biến thể) ===== */
    public function addItem($userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $price)
    {
        $existing = $this->findItem($userId, $productId, $size, $color, $rentDate, $returnDate);
        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $newQty, $existing['id']);
            return $stmt->execute();
        } else {
            $stmt = $this->conn->prepare("INSERT INTO cart (user_id, product_id, size, color, rent_date, return_date, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssii", $userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $price);
            return $stmt->execute();
        }
    }

    /* ===== Xóa toàn bộ giỏ hàng của người dùng ===== */
    public function clear($userId)
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
 
    public function clearCart($userId)
    {
        return $this->clear($userId);
    }

    /* ===== Tính tổng tiền thuê (chưa bao gồm cọc) dựa trên giá đã lưu trong giỏ ===== */
    public function getTotal($userId)
    {
        $sql = "SELECT SUM(quantity * price) AS total FROM cart WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /* ===== Đếm tổng số lượng sản phẩm trong giỏ hàng của user ===== */
    public function countByUser($userId)
    {
        $stmt = $this->conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }
}