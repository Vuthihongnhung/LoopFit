<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;
use Core\Database;

class OrderController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        AdminMiddleware::check();
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Hiển thị trang danh sách đơn hàng (HTML)
    public function index()
    {
        // Lấy danh sách đơn hàng kèm payment_status, deposit_status
        $sql = "SELECT 
                    o.id,
                    u.name AS user_name,
                    u.email,
                    u.phone,
                    o.total_price,
                    o.discount_price,
                    o.total_deposit,
                    o.status,
                    o.payment_status,
                    o.deposit_status,
                    o.rent_date,
                    o.return_date,
                    o.actual_return_date,
                    o.late_fee,
                    o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC";
        $result = $this->db->query($sql);
        $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
        ob_start();
        require_once __DIR__ . '/../../../views/admin/orders/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Quản lý đơn hàng';
        $activeMenu = 'orders';
        require_once __DIR__ . '/../../../views/layouts/admin.php';
    }
    
public function getAll()
{
    $paymentStatus = $_GET['payment_status'] ?? '';
    $depositStatus = $_GET['deposit_status'] ?? '';
    $paymentMethod = $_GET['payment_method'] ?? '';
    
    $sql = "SELECT 
                o.id,
                u.name AS user_name,
                u.email,
                u.phone,
                o.total_price,
                o.discount_price,
                o.total_deposit,
                o.status,
                o.payment_status,
                o.deposit_status,
                o.payment_method,
                o.rent_date,
                o.return_date,
                o.actual_return_date,
                o.late_fee,
                o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE 1=1";
    
    if ($paymentStatus) {
        $sql .= " AND o.payment_status = '" . $this->db->real_escape_string($paymentStatus) . "'";
    }
    
    if ($depositStatus) {
        $sql .= " AND o.deposit_status = '" . $this->db->real_escape_string($depositStatus) . "'";
    }
    
    if ($paymentMethod) {
        $sql .= " AND o.payment_method = '" . $this->db->real_escape_string($paymentMethod) . "'";
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $result = $this->db->query($sql);
    if (!$result) {
        return $this->json([
            "success" => false,
            "error" => $this->db->error
        ]);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    return $this->json([
        "success" => true,
        "data" => $data
    ]);
}
// API riêng cho đơn chờ xác nhận chuyển khoản
public function getPendingPayments()
{
    $sql = "SELECT 
                o.id,
                u.name AS user_name,
                u.email,
                u.phone,
                o.total_price,
                o.discount_price,
                o.total_deposit,
                o.status,
                o.payment_status,
                o.deposit_status,
                o.payment_method,
                o.rent_date,
                o.return_date,
                o.actual_return_date,
                o.late_fee,
                o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.payment_method = 'transfer' 
            AND o.payment_status = 'pending_verification'
            ORDER BY o.created_at DESC";
    
    $result = $this->db->query($sql);
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    return $this->json(["success" => true, "data" => $data]);
}

// API riêng cho đơn chờ hoàn cọc
public function getPendingDeposits()
{
    $sql = "SELECT 
                o.id,
                u.name AS user_name,
                u.email,
                u.phone,
                o.total_price,
                o.discount_price,
                o.total_deposit,
                o.status,
                o.payment_status,
                o.deposit_status,
                o.payment_method,
                o.rent_date,
                o.return_date,
                o.actual_return_date,
                o.late_fee,
                o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.deposit_status = 'pending_return'
            ORDER BY o.created_at DESC";
    
    $result = $this->db->query($sql);
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    return $this->json(["success" => true, "data" => $data]);
}
    
    // API: Cập nhật trạng thái đơn hàng (AJAX)
    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['pending', 'confirmed', 'renting', 'returned', 'cancelled'];
            
            if (!in_array($status, $allowed)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Trạng thái không hợp lệ'
                ]);
            }
            
            if ($status == 'returned') {
                $stmt = $this->db->prepare("SELECT return_date FROM orders WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                $return_date = strtotime($row['return_date']);
                $today = time();
                $late_fee = 0;
                if ($today > $return_date) {
                    $days_late = ceil(($today - $return_date) / (60 * 60 * 24));
                    $late_fee = $days_late * 50000;
                }
                
                $updateStmt = $this->db->prepare("UPDATE orders SET status = ?, actual_return_date = NOW(), late_fee = ? WHERE id = ?");
                $updateStmt->bind_param("sii", $status, $late_fee, $id);
                $success = $updateStmt->execute();
            } else {
                $updateStmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $updateStmt->bind_param("si", $status, $id);
                $success = $updateStmt->execute();
            }
            
            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Cập nhật thành công'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => $this->db->error
                ]);
            }
        }
    }
    
    // Xác nhận thanh toán (chuyển pending_verification -> paid)
    public function approvePayment()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = intval($_POST['order_id'] ?? 0);
            
            if ($order_id <= 0) {
                return $this->json([
                    'success' => false,
                    'error' => 'ID đơn hàng không hợp lệ'
                ]);
            }
            
            $sql = "UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status = 'pending_verification'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $success = $stmt->execute();
            
            if ($success && $stmt->affected_rows > 0) {
                return $this->json([
                    'success' => true,
                    'message' => 'Đã xác nhận thanh toán thành công'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => 'Không thể xác nhận hoặc đơn hàng không ở trạng thái chờ'
                ]);
            }
        }
    }
    
    // Cập nhật status_after_return cho một order_detail
    public function updateStatusAfterReturn()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $detail_id = intval($_POST['detail_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['good', 'damaged', 'lost'];
            
            if (!in_array($status, $allowed)) {
                return $this->json([
                    'success' => false, 
                    'error' => 'Trạng thái không hợp lệ'
                ]);
            }
            
            $sql = "UPDATE order_details SET status_after_return = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $status, $detail_id);
            $success = $stmt->execute();
            
            if ($success) {
                return $this->json([
                    'success' => true, 
                    'message' => 'Đã cập nhật tình trạng sản phẩm'
                ]);
            } else {
                return $this->json([
                    'success' => false, 
                    'error' => 'Lỗi cập nhật: ' . $this->db->error
                ]);
            }
        }
    }
    
    // Xử lý hoàn tiền cọc dựa trên status_after_return
    public function processDeposit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = intval($_POST['order_id'] ?? 0);
            
            // Lấy thông tin đơn hàng
            $stmt = $this->db->prepare("SELECT deposit_status, total_deposit FROM orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            
            // Kiểm tra điều kiện hoàn cọc (đã trả đồ và chưa hoàn)
            if (!$order || ($order['deposit_status'] != 'pending_return' && $order['deposit_status'] != 'pending')) {
                return $this->json([
                    'success' => false,
                    'error' => 'Đơn hàng không đủ điều kiện hoàn cọc'
                ]);
            }
            
            // Kiểm tra tất cả sản phẩm trong order_details có status_after_return hay chưa
            $stmt = $this->db->prepare("SELECT COUNT(*) as total, 
                                        SUM(CASE WHEN status_after_return = 'good' THEN 1 ELSE 0 END) as good_count
                                        FROM order_details WHERE order_id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            
            $all_good = ($stats['total'] == $stats['good_count']);
            $total_deposit = floatval($order['total_deposit'] ?? 0);
            
            if ($all_good) {
                // Hoàn toàn bộ tiền cọc
                $refund_amount = $total_deposit;
                $new_status = 'refunded_full';
            } else {
                // Tính tiền hoàn dựa trên sản phẩm good
                $stmt = $this->db->prepare("SELECT SUM(deposit_price) as refundable 
                                            FROM order_details 
                                            WHERE order_id = ? AND status_after_return = 'good'");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $refund = $result->fetch_assoc();
                $refund_amount = floatval($refund['refundable'] ?? 0);
                $new_status = 'refunded_partial';
            }
            
            // Cập nhật deposit_status và số tiền đã hoàn
            $sql = "UPDATE orders SET deposit_status = ?, deposit_refunded_amount = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sdi", $new_status, $refund_amount, $order_id);
            $success = $stmt->execute();
            
            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Hoàn cọc thành công với số tiền: ' . number_format($refund_amount) . ' VND',
                    'refund_amount' => $refund_amount
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => 'Lỗi khi cập nhật hoàn cọc: ' . $this->db->error
                ]);
            }
        }
    }
    
    // Hiển thị chi tiết đơn hàng (trang HTML)
    public function detail()
    {
        $order_id = intval($_GET['id'] ?? 0);
        if (!$order_id) {
            header('Location: ' . BASE_URL . '/admin/orders');
            exit();
        }
        
        $orderModel = new \App\Models\Order();
        $data = $orderModel->getDetail($order_id);
        
        if (!$data['order']) {
            header('Location: ' . BASE_URL . '/admin/orders');
            exit();
        }
        
        return $this->view('admin.orders.detail', [
        'order' => $data['order'],
        'items' => $data['items']
    ]);
    }
    
    // API: Lấy chi tiết đơn hàng (JSON)
    public function apiGetDetail()
    {
        $order_id = intval($_GET['id'] ?? 0);
        
        $order_sql = "SELECT o.*, u.name, u.email, u.phone 
                      FROM orders o
                      JOIN users u ON o.user_id = u.id
                      WHERE o.id = ?";
        $stmt = $this->db->prepare($order_sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        $detail_sql = "SELECT od.*, p.name, p.price 
                       FROM order_details od
                       JOIN products p ON od.product_id = p.id
                       WHERE od.order_id = ?";
        $stmt = $this->db->prepare($detail_sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $this->json([
            "success" => true,
            "order" => $order,
            "items" => $items
        ]);
    }
}
?>