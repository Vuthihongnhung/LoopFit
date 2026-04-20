<?php

namespace App\Models;

use Core\Model;

class Cart extends Model
{
    // =====================
    // GET CART BY USER
    // =====================
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
                p.name,
                p.price,
                p.deposit_price,
                p.image,
                DATEDIFF(c.return_date, c.rent_date) AS days
            FROM cart c
            LEFT JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?";
    
    if ($selectedOnly) {
        $sql .= " AND c.selected = 1"; // nếu có cột selected
    }
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($items as &$item) {
        $days = (int)($item['days'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        $depositPrice = (float)($item['deposit_price'] ?? 0);
        $qty = (int)($item['quantity'] ?? 1);
        
        $item['subtotal'] = $price * $qty * $days;
        $item['deposit'] = $depositPrice * $qty;
        $item['days'] = $days;
    }
    
    return $items;
}

public function getCartItemsForCheckout($userId)
{
    // Gọi lại getByUser với tham số selectedOnly = true nếu dùng checkbox
    return $this->getByUser($userId, true);
}

public function clearCart($userId)
{
    return $this->clear($userId);
}

    // =====================
    // FIND BY CART ID
    // =====================
    public function findById($cartId, $userId) {
    $stmt = $this->conn->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

    // =====================
    // UPDATE QUANTITY (cart_id)
    // =====================
    public function updateQuantityById($cartId, $userId, $quantity)
{
    $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cartId, $userId);
    return $stmt->execute();
}

    // =====================
    // REMOVE (cart_id)
    // =====================
    public function removeById($cartId, $userId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM cart 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $userId);

        return $stmt->execute();
    }

    // =====================
    // FIND EXISTING ITEM (product_id)
    // =====================
    public function findItem($userId, $productId)
    {
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // =====================
    // ADD TO CART
    // =====================
    public function add($userId, $productId, $quantity, $size, $rentDate, $returnDate)
{
    $sql = "INSERT INTO cart (user_id, product_id, quantity, size, rent_date, return_date)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("iiisss", $userId, $productId, $quantity, $size, $rentDate, $returnDate);

    return $stmt->execute();
}

public function addItem($userId, $productId, $size, $color, $rentDate, $returnDate, $quantity, $price)
{
    // Kiểm tra xem đã có item trùng chưa (cùng user, product, size, color, rent_date, return_date)
    $stmt = $this->conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ? AND rent_date = ? AND return_date = ?");
    $stmt->bind_param("iissss", $userId, $productId, $size, $color, $rentDate, $returnDate);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
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

    // =====================
    // CLEAR CART
    // =====================
    public function clear($userId)
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }

    // =====================
    // TOTAL PRICE
    // =====================
    public function getTotal($userId)
    {
        $sql = "SELECT SUM(c.quantity * p.price) AS total
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'] ?? 0;
    }

    // =====================
    // COUNT ITEMS
    // =====================
    public function countByUser($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT SUM(quantity) as total 
            FROM cart 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return (int)($row['total'] ?? 0);
    }
}