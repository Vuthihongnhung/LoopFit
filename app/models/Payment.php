<?php
namespace App\Models;

use Core\Model;

class Payment extends Model
{
    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO payments (order_id, amount, method, status, paid_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("idss", $data['order_id'], $data['amount'], $data['method'], $data['status']);
        return $stmt->execute();
    }

    public function getByOrderId($orderId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateStatusByOrderId($orderId, $status)
    {
        $existing = $this->getByOrderId($orderId);
        if ($existing) {
            $stmt = $this->conn->prepare("UPDATE payments SET status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $status, $orderId);
            return $stmt->execute();
        }

        $amount = 0;
        $method = 'transfer';
        $stmt = $this->conn->prepare("INSERT INTO payments (order_id, amount, method, status, paid_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("idss", $orderId, $amount, $method, $status);
        return $stmt->execute();
    }
}
