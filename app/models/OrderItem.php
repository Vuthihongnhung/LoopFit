<?php
namespace App\Models;

use Core\Model;

class OrderItem extends Model
{
    public function create($orderId, $productId, $quantity, $price)
    {
        $sql = "INSERT INTO order_details (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);

        return $stmt->execute();
    }

    public function getByOrder($orderId)
    {
        $sql = "SELECT od.*, p.name, p.image
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                WHERE od.order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}