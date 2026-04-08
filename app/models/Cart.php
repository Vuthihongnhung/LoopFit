<?php

namespace App\Models;

use Core\Model;

class Cart extends Model
{
    public function getByUser($userId)
    {
        $sql = "SELECT c.*, p.name, p.price, p.image, p.stock
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findItem($userId, $productId)
    {
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function add($userId, $productId, $quantity)
    {
        $existing = $this->findItem($userId, $productId);

        if ($existing) {
            $newQty = (int)$existing['quantity'] + (int)$quantity;

            $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                die('Prepare UPDATE lỗi: ' . $this->conn->error);
            }

            $stmt->bind_param("ii", $newQty, $existing['id']);

            if (!$stmt->execute()) {
                die('Execute UPDATE lỗi: ' . $stmt->error);
            }

            return true;
        }

        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare INSERT lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("iii", $userId, $productId, $quantity);

        if (!$stmt->execute()) {
            die('Execute INSERT lỗi: ' . $stmt->error);
        }

        return true;
    }

    public function updateQuantity($userId, $productId, $quantity)
    {
        $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare UPDATE QTY lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("iii", $quantity, $userId, $productId);

        if (!$stmt->execute()) {
            die('Execute UPDATE QTY lỗi: ' . $stmt->error);
        }

        return true;
    }

    public function remove($userId, $productId)
    {
        $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare DELETE lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("ii", $userId, $productId);

        if (!$stmt->execute()) {
            die('Execute DELETE lỗi: ' . $stmt->error);
        }

        return true;
    }

    public function clear($userId)
    {
        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }

    public function getTotal($userId)
    {
        $sql = "SELECT SUM(c.quantity * p.price) AS total
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'] ?? 0;
    }
}