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
    public function create($data) {
        $sql = "INSERT INTO payments (order_id, amount, method, status, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Payment::create - Prepare lỗi: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("idss", $data['order_id'], $data['amount'], $data['method'], $data['status']);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Payment::create - Execute lỗi: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    // Bạn có thể thêm các method khác nếu cần, ví dụ: getByOrderId($orderId)
}