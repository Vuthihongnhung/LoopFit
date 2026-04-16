<?php

namespace App\Models;

use Core\Model;

class Order extends Model
{
    public function getAll()
    {
        $sql = "SELECT o.*, u.name AS user_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.id DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            return [];
        }

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
                WHERE o.id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare getById order lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($userId, $rentDate, $returnDate, $totalPrice, $status = 'pending')
    {
        $sql = "INSERT INTO orders (user_id, rent_date, return_date, total_price, status)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare create order lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("issds", $userId, $rentDate, $returnDate, $totalPrice, $status);

        if (!$stmt->execute()) {
            die('Execute create order lỗi: ' . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    public function getByUser($userId)
    {
        $sql = "SELECT *
                FROM orders
                WHERE user_id = ?
                ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare getByUser lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getByUserId($userId)
    {
        return $this->getByUser($userId);
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare updateStatus lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("si", $status, $id);

        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];

        return $row['total'];
    }
}