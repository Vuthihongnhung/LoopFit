<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;
use Core\Database;

class PaymentController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        AdminMiddleware::check();
        $database = new \Core\Database();
        $this->db = $database->getConnection();
    }
    
    /* ===== Hiển thị trang quản lý thanh toán  ===== */
    public function index()
    {
        $this->view('admin/payments/index', [
            'pageTitle' => 'Quản lý thanh toán',
            'activeMenu' => 'payments'
        ]);
    }
    
    /* ===== API lấy danh sách đơn hàng (có lọc theo phương thức và trạng thái)  ===== */
    public function apiGetList()
    {
        $status = $_GET['status'] ?? 'all';
        $method = $_GET['method'] ?? 'all';
        
        $sql = "SELECT 
                    o.id,
                    u.name AS user_name,
                    u.phone,
                    COALESCE(o.final_total, o.total_price + o.deposit_amount + o.shipping_fee - o.discount_price, 0) AS amount,
                    o.payment_method,
                    o.payment_status,
                    o.created_at,
                    o.status AS order_status
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE 1=1";
        
        // Lọc theo phương thức thanh toán (cash / transfer)
        if ($method !== 'all') {
            $method = $this->db->real_escape_string($method);
            $sql .= " AND o.payment_method = '$method'";
        }
        
        // Lọc theo trạng thái thanh toán
        if ($status !== 'all') {
            $status = $this->db->real_escape_string($status);
            $sql .= " AND o.payment_status = '$status'";
        } else {
            // Mặc định chỉ hiển thị các đơn chưa thanh toán hoặc chờ xác nhận
            $sql .= " AND o.payment_status IN ('unpaid', 'pending_verification', 'paid')";
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return $this->json(['success' => false, 'error' => $this->db->error]);
        }
        $data = $result->fetch_all(MYSQLI_ASSOC);
        return $this->json(['success' => true, 'data' => $data]);
    }
    
    /* ===== API xác nhận thanh toán (cho cả tiền mặt và chuyển khoản)  ===== */
    public function confirmPayment()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method không hợp lệ']);
            exit;
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        
        if ($orderId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID đơn hàng không hợp lệ']);
            exit;
        }
        
        $orderModel = new \App\Models\Order();
        $order = $orderModel->getById($orderId);
        
        if (!$order) {
            echo json_encode(['success' => false, 'error' => 'Đơn hàng không tồn tại']);
            exit;
        }
        
        $allowed = false;
        if ($order['payment_method'] === 'cash' && $order['payment_status'] === 'unpaid') {
            $allowed = true;
        } elseif ($order['payment_method'] === 'transfer' && $order['payment_status'] === 'pending_verification') {
            $allowed = true;
        }
        
        if (!$allowed) {
            echo json_encode([
                'success' => false,
                'error' => 'Đơn hàng không ở trạng thái cần xác nhận thanh toán'
            ]);
            exit;
        }
        
        $updateStmt = $this->db->prepare("UPDATE orders SET payment_status = 'paid', payment_verified_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $orderId);
        $success = $updateStmt->execute();
        
        if ($success && $updateStmt->affected_rows > 0) {
            $updatedOrder = $orderModel->getById($orderId);
            $finalTotal = $updatedOrder['final_total'] ?? 0;
            
            $paymentModel = new \App\Models\Payment();
            $paymentModel->create([
                'order_id' => $orderId,
                'amount'   => $finalTotal,
                'method'   => $order['payment_method'],
                'status'   => 'completed'
            ]);

            $notification = new \App\Models\Notification();
            $notification->createNotification(
                $updatedOrder['user_id'],
                'customer',
                '💳 Thanh toán chuyển khoản xác nhận',
                'Yêu cầu thanh toán của bạn cho đơn #' . $orderId . ' đã được admin xác nhận thành công.',
                'payment_confirmed',
                $orderId,
                'order',
                BASE_URL . '/order/' . $orderId,
                'credit-card',
                1
            );
            
            echo json_encode(['success' => true, 'message' => 'Đã xác nhận thanh toán thành công']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Xác nhận thất bại']);
        }
        exit;
    }
    
    /* ===== Hàm hỗ trợ trả về JSON  ===== */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>