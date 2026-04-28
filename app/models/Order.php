<?php

namespace App\Models;

use Core\Model;

class Order extends Model
{
    /**
     * Lấy tất cả đơn hàng (kèm thông tin user)
     */
    public function getAll()
    {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name, 
                    u.email,
                    u.phone,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status,
                    o.total_deposit,
                    o.deposit_refunded_amount
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

    /**
     * Lấy đơn hàng theo ID (kèm thông tin user)
     */
    public function getById($id)
    {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name, 
                    u.email,
                    u.phone,
                    u.address,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status,
                    o.total_deposit,
                    o.deposit_refunded_amount
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

    /**
     * Lấy chi tiết đơn hàng (kèm thông tin user và sản phẩm)
     * Dùng cho trang chi tiết đơn hàng admin
     */
    public function getDetail($id)
    {
        // Lấy thông tin đơn hàng + user
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name,
                    u.email AS user_email,
                    u.phone,
                    u.address,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status,
                    o.total_deposit,
                    o.deposit_refunded_amount,
                    o.late_fee
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['order' => null, 'items' => []];
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if (!$order) {
            return ['order' => null, 'items' => []];
        }
        
        // Lấy danh sách sản phẩm trong đơn hàng
        $itemsSql = "SELECT 
                        od.*,
                        p.name AS product_name,
                        p.price,
                        p.image,
                        p.deposit_price
                    FROM order_details od
                    LEFT JOIN products p ON od.product_id = p.id
                    WHERE od.order_id = ?";
        
        $stmt = $this->conn->prepare($itemsSql);
        if (!$stmt) {
            return ['order' => $order, 'items' => []];
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return [
            'order' => $order,
            'items' => $items
        ];
    }

    /**
     * Tạo đơn hàng mới
     */
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO orders 
            (user_id, rent_date, return_date, total_price, discount_price, total_deposit, deposit_status, payment_status, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $deposit_status = $data['deposit_status'] ?? 'pending';
        $payment_status = $data['payment_status'] ?? 'pending';
        $status = $data['status'] ?? 'pending';
        
        $stmt->bind_param("issddssss", 
            $data['user_id'], 
            $data['rent_date'], 
            $data['return_date'], 
            $data['total_price'], 
            $data['discount_price'],
            $data['total_deposit'],
            $deposit_status,
            $payment_status,
            $status
        );
        $stmt->execute();
        return $this->conn->insert_id;
    }

    /**
     * Lấy đơn hàng theo user ID
     */
    public function getByUser($userId)
    {
        $sql = "SELECT 
                    o.*,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC, o.id DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Prepare getByUser lỗi: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy đơn hàng theo user ID (alias)
     */
    public function getByUserId($userId)
    {
        return $this->getByUser($userId);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
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

    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus($id, $paymentStatus)
    {
        $sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $paymentStatus, $id);
        return $stmt->execute();
    }

    /**
     * Cập nhật trạng thái cọc
     */
    public function updateDepositStatus($id, $depositStatus, $refundAmount = null)
    {
        if ($refundAmount !== null) {
            $sql = "UPDATE orders SET deposit_status = ?, deposit_refunded_amount = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sdi", $depositStatus, $refundAmount, $id);
        } else {
            $sql = "UPDATE orders SET deposit_status = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $depositStatus, $id);
        }
        return $stmt->execute();
    }

    /**
     * Cập nhật ngày trả thực tế và phí trễ
     */
    public function updateActualReturn($id, $actualReturnDate, $lateFee = 0)
    {
        $sql = "UPDATE orders SET actual_return_date = ?, late_fee = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdi", $actualReturnDate, $lateFee, $id);
        return $stmt->execute();
    }

    /**
     * Đếm tổng số đơn hàng
     */
    public function countAll()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return $row['total'];
    }

    /**
     * Tính doanh thu (tổng discount_price của đơn đã hoàn thành)
     */
    public function getRevenue()
    {
        $result = $this->conn->query("SELECT SUM(discount_price) as total FROM orders WHERE status = 'completed'");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return $row['total'] ?? 0;
    }

    /**
     * Lấy doanh thu theo tháng (cho dashboard)
     */
    public function getRevenueByMonth($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    SUM(discount_price) as revenue
                FROM orders 
                WHERE status = 'completed' AND YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
                ORDER BY month ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $revenues = [];
        while ($row = $result->fetch_assoc()) {
            $revenues[$row['month']] = $row['revenue'];
        }
        
        return $revenues;
    }

    /**
     * Lấy đơn hàng chờ xác nhận chuyển khoản
     */
    public function getPendingVerificationOrders()
    {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name,
                    u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.payment_status = 'pending_verification'
                ORDER BY o.created_at ASC";
        
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Lấy đơn hàng chờ hoàn cọc
     */
    public function getPendingDepositOrders()
    {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name,
                    u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.status = 'returned' AND o.deposit_status = 'pending_return'
                ORDER BY o.actual_return_date ASC";
        
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    /**
 * Cập nhật total_price của đơn dựa trên order_details
 */
public function updateTotalPrice($orderId)
{
    $sql = "SELECT SUM(price * quantity) as total FROM order_details WHERE order_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalPrice = $row['total'] ?? 0;
    
    $updateSql = "UPDATE orders SET total_price = ? WHERE id = ?";
    $updateStmt = $this->conn->prepare($updateSql);
    $updateStmt->bind_param("di", $totalPrice, $orderId);
    return $updateStmt->execute();
}
}