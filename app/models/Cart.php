<?php

namespace App\Models;

use Core\Model;

class Cart extends Model
{
    // =====================
    // GET CART BY USER
    // =====================
    public function getByUser($userId)
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
                    p.image
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // =====================
    // FIND BY CART ID
    // =====================
    public function findById($cartId, $userId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM cart 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // =====================
    // UPDATE QUANTITY (cart_id)
    // =====================
    public function updateQuantityById($cartId, $userId, $quantity)
    {
        $stmt = $this->conn->prepare("
            UPDATE cart 
            SET quantity = ? 
            WHERE id = ? AND user_id = ?
        ");
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