<?php

namespace App\Models;

use Core\Model;

class Order extends Model
{
    public function __construct() {
        parent::__construct();
        if (!$this->conn) die("Order: Không thể kết nối DB");
    }

    public function create($data) {
        $sql = "INSERT INTO orders 
                (user_id, address_id, rent_date, return_date, total_price, discount_price, shipping_fee, deposit_amount, final_total, payment_method, notes, status, payment_status, deposit_status, payment_expiry) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Prepare lỗi: ' . $this->conn->error];

        $stmt->bind_param(
            "iissdddddssssss",
            $data['user_id'],
            $data['address_id'],
            $data['rent_date'],
            $data['return_date'],
            $data['total_price'],
            $data['discount_price'],
            $data['shipping_fee'],
            $data['deposit_amount'],
            $data['final_total'],
            $data['payment_method'],
            $data['notes'],
            $data['status'],
            $data['payment_status'],
            $data['deposit_status'],
            $data['payment_expiry']
        );

        if ($stmt->execute()) {
            $id = $this->conn->insert_id;
            $stmt->close();
            return ['success' => true, 'id' => $id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'Execute lỗi: ' . $error];
        }
    }

    public function getAll()
    {
        $sql = "SELECT o.*, u.name AS user_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.id DESC";
        $result = $this->conn->query($sql);
        if (!$result) return [];
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    public function getById($id)
    {
        $sql = "SELECT o.*, u.name AS user_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) die('Prepare getById order lỗi: ' . $this->conn->error);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Lấy đơn hàng theo user, có thể lọc theo status
     * @param int $userId
     * @param string|null $status nếu null hoặc 'all' thì lấy tất cả
     */
    public function getByUser($userId, $status = null)
    {
        $sql = "SELECT * FROM orders WHERE user_id = ?";
        if ($status && $status !== 'all') {
            $sql .= " AND status = ?";
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($status && $status !== 'all') {
            $stmt->bind_param("is", $userId, $status);
        } else {
            $stmt->bind_param("i", $userId);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Alias
    public function getByUserId($userId)
    {
        return $this->getByUser($userId);
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) die('Prepare updateStatus lỗi: ' . $this->conn->error);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return $row['total'];
    }

    public function updatePaymentStatus($orderId, $status)
    {
        $sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    // Thêm vào cuối class Order
    public function getLastError() {
        return $this->conn ? $this->conn->error : 'Không có kết nối database';
    }
}