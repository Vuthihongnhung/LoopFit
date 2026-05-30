<?php
namespace App\Models;
use Core\Model;

class Payment extends Model
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Tạo bản ghi thanh toán mới
     * @param array $data ['order_id', 'amount', 'method', 'status']
     * @return bool
     */
    private function getNextId() {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM payments";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)($row['next_id'] ?? 1);
        }
        return 1;
    }

    public function create($data) {
        $nextId = $this->getNextId();
        $sql = "INSERT INTO payments (id, order_id, amount, method, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Payment::create - Prepare lỗi: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iidss", $nextId, $data['order_id'], $data['amount'], $data['method'], $data['status']);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Payment::create - Execute lỗi: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
}