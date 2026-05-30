<?php

namespace App\Models;

use Core\Model;

class Order extends Model
{
    public function __construct() {
        parent::__construct();
        if (!$this->conn) die("Order: Không thể kết nối DB");
    }

    // ==================== CREATE ====================

    /* ===== Tạo mới một đơn hàng  ===== */
    private function getNextId() {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM orders";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)($row['next_id'] ?? 1);
        }
        return 1;
    }

    public function create($data) {
        $nextId = $this->getNextId();
        $sql = "INSERT INTO orders 
                (id, user_id, address_id, rent_date, return_date, total_price, discount_price, shipping_fee, deposit_amount, final_total, payment_method, notes, status, payment_status, deposit_status, payment_expiry, promotion_id, late_fee, late_fee_status, late_fee_paid_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['success' => false, 'message' => 'Prepare lỗi: ' . $this->conn->error];

        $stmt->bind_param(
            "iiissdddddssssssidss",
            $nextId,
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
            $data['payment_expiry'],
            $data['promotion_id'],
            $data['late_fee'],
            $data['late_fee_status'],
            $data['late_fee_paid_at']
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'id' => $nextId];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'Execute lỗi: ' . $error];
        }
    }

    // ==================== QUERY ĐƠN HÀNG ====================

    /* ===== Lấy danh sách đơn hàng của một người dùng (không kèm items) ===== */
    public function getByUser($userId, $status = null) {
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

    /* ===== Lấy danh sách đơn hàng của user kèm chi tiết sản phẩm (order_items) ===== */
    public function getUserOrdersWithItems($userId, $status = null) {
        $orders = $this->getByUser($userId, $status);
        $itemModel = new OrderItem();
        foreach ($orders as &$order) {
            $items = $itemModel->getByOrder($order['id']);
            // Chuẩn hóa thêm thông tin cho frontend
            foreach ($items as &$item) {
                $item['rental_start'] = $order['rent_date'];
                $item['rental_end']   = $order['return_date'];
            }
            $order['items'] = $items;
        }
        return $orders;
    }

    /* ===== Lấy chi tiết một đơn hàng (kèm thông tin người dùng và danh sách sản phẩm) ===== */
    public function getDetail($id) {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name,
                    u.email AS user_email,
                    u.phone,
                    u.address,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status,
                    o.deposit_refunded_amount,
                    o.late_fee
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['order' => null, 'items' => []];
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) return ['order' => null, 'items' => []];

        $itemModel = new OrderItem();
        $items = $itemModel->getByOrder($id);
        // Bổ sung thông tin sản phẩm nếu cần
        $productModel = new Product();
        foreach ($items as &$item) {
            $product = $productModel->find($item['product_id']);
            if ($product) {
                $item['product_name'] = $product['name'];
                $item['product_image'] = $product['image'];
                $item['original_price'] = $product['price'];
                $item['deposit_price'] = $product['deposit_price'];
            }
            $item['price_per_day'] = $item['price'] ?? 0;
        }
        return ['order' => $order, 'items' => $items];
    }

    /* ===== Lấy tất cả đơn hàng (dành cho admin) ===== */
    public function getAll() {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name, 
                    u.email,
                    u.phone,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status,
                    o.deposit_refunded_amount
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.id DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ===== Lấy đơn hàng theo ID (không kèm items) ===== */
    public function getById($id) {
        $sql = "SELECT 
                    o.*,
                    u.name AS user_name, 
                    u.email,
                    u.phone,
                    u.address,
                    o.discount_price,
                    o.payment_status,
                    o.deposit_status,
                    o.deposit_refunded_amount
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ===== Lấy đơn hàng của user theo trạng thái cụ thể (chỉ dùng cho API user) ===== */
    public function getByUserAndStatus($userId, $status)
    {
        $sql = "SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $userId, $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== CẬP NHẬT ĐƠN HÀNG ====================
    
    /* ===== Cập nhật trạng thái đơn hàng ===== */
    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /* ===== Cập nhật trạng thái thanh toán, tự động set payment_verified_at nếu status = 'paid' ===== */
    public function updatePaymentStatus($orderId, $status)
    {
        $sql = "UPDATE orders SET payment_status = ?, payment_verified_at = IF(? = 'paid', NOW(), NULL) WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $status, $orderId);
        return $stmt->execute();
    }

    /* ===== Cập nhật trạng thái cọc và số tiền hoàn trả (nếu có) ===== */
    public function updateDepositStatus($orderId, $status, $refundAmount = null)
    {
        if ($refundAmount !== null) {
            $sql = "UPDATE orders SET deposit_status = ?, deposit_refunded_amount = ?, deposit_refunded_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sdi", $status, $refundAmount, $orderId);
        } else {
            $sql = "UPDATE orders SET deposit_status = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $status, $orderId);
        }
        return $stmt->execute();
    }

    /* ===== Cập nhật ngày trả thực tế và phí trễ ===== */
    public function updateActualReturn($id, $actualReturnDate, $lateFee = 0) {
        $stmt = $this->conn->prepare("UPDATE orders SET actual_return_date = ?, late_fee = ? WHERE id = ?");
        $stmt->bind_param("sdi", $actualReturnDate, $lateFee, $id);
        return $stmt->execute();
    }

    /* ===== Tính lại tổng tiền thuê dựa trên các order_items (không gồm cọc, phí) ===== */
    public function updateTotalPrice($orderId) {
        $itemModel = new OrderItem();
        $total = $itemModel->getTotalByOrder($orderId);
        $stmt = $this->conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
        $stmt->bind_param("di", $total, $orderId);
        return $stmt->execute();
    }

    /* ===== Cập nhật trạng thái phí trễ ===== */
    public function updateLateFeeStatus($orderId, $status) {
        $stmt = $this->conn->prepare("UPDATE orders SET late_fee_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    // ==================== XÓA ====================

    /* ===== Xóa đơn hàng ===== */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ==================== THỐNG KÊ ====================

    /* ===== Đếm tổng số đơn hàng ===== */
    public function countAll() {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        return $result ? (int)$result->fetch_assoc()['total'] : 0;
    }

    /* ===== Doanh thu từ các đơn hoàn tất và đã thanh toán ===== */
    public function getRevenue() {
        $result = $this->conn->query("SELECT SUM(final_total) as total FROM orders WHERE status = 'completed' AND payment_status = 'paid'");
        return $result ? (float)$result->fetch_assoc()['total'] : 0;
    }

    /* ===== Doanh thu theo từng tháng trong năm (chỉ tính đơn completed) ===== */
    public function getRevenueByMonth($year = null) {
        if (!$year) $year = date('Y');
        $stmt = $this->conn->prepare("SELECT 
                    MONTH(created_at) as month,
                    SUM(discount_price) as revenue
                FROM orders 
                WHERE status = 'completed' AND YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
                ORDER BY month ASC");
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $revenues = [];
        while ($row = $result->fetch_assoc()) {
            $revenues[$row['month']] = $row['revenue'];
        }
        return $revenues;
    }

    /* ===== Top sản phẩm được thuê nhiều nhất (doanh số cao nhất) ===== */
    public function getTopRentedProducts($limit = 5) {
        $stmt = $this->conn->prepare("SELECT 
                    p.id,
                    p.name AS product_name,
                    COUNT(od.id) AS rental_count,
                    SUM(od.subtotal) AS revenue
                FROM order_details od
                INNER JOIN products p ON od.product_id = p.id
                INNER JOIN orders o ON od.order_id = o.id
                WHERE o.status NOT IN ('cancelled', 'pending')
                GROUP BY p.id
                ORDER BY revenue DESC
                LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== ĐƠN HÀNG CHỜ XÁC NHẬN / HOÀN CỌC ====================

    /* ===== Đơn hàng chờ xác nhận thanh toán chuyển khoản ===== */
    public function getPendingVerificationOrders() {
        $sql = "SELECT o.*, u.name AS user_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.payment_status = 'pending_verification'
                ORDER BY o.created_at ASC";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Đơn hàng chờ hoàn cọc (status = 'returned' và deposit_status = 'pending_return') ===== */
    public function getPendingDepositOrders() {
        $sql = "SELECT o.*, u.name AS user_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.status = 'returned' AND o.deposit_status = 'pending_return'
                ORDER BY o.actual_return_date ASC";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /* ===== Đếm số đơn hàng chuyển khoản đang chờ xác nhận thanh toán ===== */
    public function countPendingTransferOrders() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_method = 'transfer' AND payment_status = 'pending_verification'");
        return $result ? (int)$result->fetch_assoc()['count'] : 0;
    }

    /* ===== Lấy danh sách (giới hạn) đơn hàng chuyển khoản chờ xác nhận ===== */
    public function getPendingTransferOrders($limit = 5) {
        $sql = "SELECT o.id, u.name as user_name, o.final_total, o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.payment_method = 'transfer' AND o.payment_status = 'pending_verification'
                ORDER BY o.created_at ASC
                LIMIT " . intval($limit);
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ===== Đếm số đơn hàng chờ hoàn cọc (deposit_status = 'pending_return') ===== */
    public function countPendingDepositReturns() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM orders WHERE deposit_status = 'pending_return'");
        return $result ? (int)$result->fetch_assoc()['count'] : 0;
    }

    /* ===== Lấy danh sách (giới hạn) đơn hàng chờ hoàn cọc ===== */
    public function getPendingDepositReturns($limit = 5) {
        $sql = "SELECT o.id, u.name as user_name, o.deposit_amount, o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.deposit_status = 'pending_return'
                ORDER BY o.created_at ASC
                LIMIT " . intval($limit);
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ===== Lấy trạng thái hoàn cọc của một đơn hàng cụ thể ===== */
    public function getDepositReturnStatus($orderId) {
        $stmt = $this->conn->prepare("SELECT deposit_status, deposit_returned_at, deposit_amount FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ==================== LOGIC XÁC NHẬN & TRẢ HÀNG (TỒN KHO) ====================
    
    /* ===== Xác nhận đơn hàng (Admin) ===== 
     * - Kiểm tra số lượng khả dụng theo thời gian thuê (rent_date -> return_date)
     * - Cập nhật rented_quantity, rented_count trong bảng products
     * - Cập nhật trạng thái đơn hàng thành 'confirmed'
     * @param int $orderId
     * @return bool|string Trả về true nếu thành công, false nếu lỗi, hoặc string thông báo lỗi
     */
    public function confirmOrder($orderId) {
        $this->conn->begin_transaction();
        try {
            // Lấy thông tin đơn hàng
            $order = $this->getById($orderId);
            if (!$order) throw new \Exception("Đơn hàng không tồn tại");
            if ($order['status'] !== 'pending') throw new \Exception("Đơn hàng đã được xử lý trước đó");

            $itemModel = new OrderItem();
            $items = $itemModel->getByOrder($orderId);
            if (empty($items)) throw new \Exception("Đơn hàng không có sản phẩm");

            $productModel = new Product();
            $rentStart = $order['rent_date'];
            $rentEnd   = $order['return_date'];

            // Kiểm tra khả dụng cho từng sản phẩm
            foreach ($items as $item) {
                $available = $productModel->getAvailableQuantity($item['product_id'], $rentStart, $rentEnd);
                if ($available < $item['quantity']) {
                    throw new \Exception("Sản phẩm ID {$item['product_id']} chỉ còn {$available} cái có thể thuê trong khoảng {$rentStart} - {$rentEnd}");
                }
            }

            // Cập nhật tồn kho: tăng rented_quantity cho biến thể tương ứng
            foreach ($items as $item) {
                $productModel->increaseVariantRentedQuantity(
                    $item['product_id'],
                    $item['color'] ?? '',
                    $item['size'] ?? '',
                    $item['quantity']
                );
            }

            // Cập nhật trạng thái đơn hàng
            $sql = "UPDATE orders SET status = 'confirmed' WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("confirmOrder error: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Xử lý trả hàng (Admin)
     * - Nếu hàng trả bình thường: giảm rented_quantity (trả lại kho)
     * - Nếu hàng bị hư hỏng: không giảm rented_quantity (coi như mất hàng)
     * - Cập nhật actual_return_date, status = 'returned'
     * @param int $orderId
     * @param bool $isDamaged
     * @return bool
     */
    public function returnOrder($orderId, $isDamaged = false) {
        $this->conn->begin_transaction();
        try {
            $order = $this->getById($orderId);
            if (!$order) throw new \Exception("Đơn hàng không tồn tại");
            if (!in_array($order['status'], ['confirmed', 'rented'])) {
                throw new \Exception("Đơn hàng chưa được xác nhận hoặc đã trả trước đó");
            }

            $itemModel = new OrderItem();
            $items = $itemModel->getByOrder($orderId);
            $productModel = new Product();

            if (!$isDamaged) {
                // Hàng trả bình thường: giảm rented_quantity cho biến thể tương ứng
                foreach ($items as $item) {
                    $productModel->decreaseVariantRentedQuantity(
                        $item['product_id'],
                        $item['color'] ?? '',
                        $item['size'] ?? '',
                        $item['quantity']
                    );
                }
            } else {
                // Hàng hư hỏng: không trả lại kho
                // Ở đây chỉ cập nhật trạng thái đơn, không thay đổi rented_quantity
            }

            // Cập nhật đơn hàng
            $sql = "UPDATE orders SET status = 'returned', actual_return_date = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("returnOrder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hoàn tất trả hàng cho đơn (chuyển trạng thái thành 'completed' và cập nhật kho nếu chưa)
     * @param int $orderId
     * @return bool
     */
    public function completeReturn($orderId)
    {
        $order = $this->getById($orderId);
        if (!$order || !in_array($order['status'], ['returning', 'confirmed', 'renting'])) {
            return false;
        }

        // Lấy danh sách sản phẩm trong đơn
        $items = $this->getOrderItems($orderId);
        if (empty($items)) {
            error_log("completeReturn: Không có sản phẩm nào cho đơn $orderId");
            return false;
        }

        $productModel = new \App\Models\Product();
        $this->conn->begin_transaction();
        try {
            $actualReturnDate = $order['actual_return_date'] ?? date('Y-m-d');
            $sql = "UPDATE orders SET status = 'completed', actual_return_date = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('si', $actualReturnDate, $orderId);
            $stmt->execute();

            // Chuyển deposit_status sang 'pending_return' để admin có thể hoàn cọc
            $this->updateDepositStatus($orderId, 'pending_return');

            // Tăng rented_count (tổng lượt thuê) cho từng sản phẩm
            foreach ($items as $item) {
                $productModel->increaseRentedCount($item['product_id'], $item['quantity']);
            }

            // Nếu admin chưa cập nhật actual_return_date trước đó, giảm rented_quantity bây giờ
            if (empty($order['actual_return_date'])) {
                foreach ($items as $item) {
                    $productModel->decreaseVariantRentedQuantity(
                        $item['product_id'],
                        $item['color'] ?? '',
                        $item['size'] ?? '',
                        $item['quantity']
                    );
                }
            }

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("completeReturn error: " . $e->getMessage());
            return false;
        }
    }
    
    /* ===== Cập nhật nhiều trường đơn hàng cùng lúc ===== */
    public function updateOrder($id, $data)
    {
        $fields = [];
        $params = [];
        $types = '';
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
            if (is_int($value)) $types .= 'i';
            elseif (is_double($value)) $types .= 'd';
            else $types .= 's';
        }
        
        if (empty($fields)) return false;
        $params[] = $id;
        $types .= 'i';
        
        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    /* ===== Hủy đơn hàng đã xác nhận: hoàn trả rented_quantity và chuyển trạng thái 'cancelled ===== */
    public function cancelConfirmedOrder($orderId)
    {
        $order = $this->getById($orderId);
        if (!$order || $order['status'] != 'confirmed') return false;
        
        $items = $this->getOrderItems($orderId);
        $productModel = new \App\Models\Product();
        
        $this->conn->begin_transaction();
        try {
            foreach ($items as $item) {
                $productModel->decreaseVariantRentedQuantity(
                    $item['product_id'],
                    $item['color'] ?? '',
                    $item['size'] ?? '',
                    $item['quantity']
                );
            }
            $sql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /* ===== Lấy danh sách sản phẩm (order_details) của một đơn hàng ===== */
    public function getOrderItems($orderId)
    {
        $sql = "SELECT * FROM order_details WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== HÀM TIỆN ÍCH ====================

    /* ===== Lấy lỗi cuối cùng của kết nối database ===== */
    public function getLastError() {
        return $this->conn ? $this->conn->error : 'Không có kết nối database';
    }
}