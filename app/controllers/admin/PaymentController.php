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
    
    /**
     * Hiển thị danh sách đơn hàng chuyển khoản
     */
    public function index()
    {
        $this->view('admin/payments/index', [
            'pageTitle' => 'Quản lý thanh toán',
            'activeMenu' => 'payments'
        ]);
    }
    
    /**
     * API: Lấy danh sách đơn hàng chuyển khoản
     */
    public function apiGetList()
    {
        header('Content-Type: application/json');
        
        $status = $_GET['status'] ?? 'all';
        
        $sql = "SELECT o.*, 
                       u.name as user_name, 
                       u.email, 
                       u.phone,
                       u.address
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.payment_method = 'transfer'";
        
        if ($status !== 'all') {
            $sql .= " AND o.payment_status = '" . $this->db->real_escape_string($status) . "'";
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $result = $this->db->query($sql);
        $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
        exit;
    }
    
    /**
     * API: Xác nhận đã nhận tiền
     */
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
    
    // Bỏ điều kiện payment_method
   $sql = "UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status IN ('pending_verification', 'unpaid')";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $success = $stmt->execute();
    
    if ($success && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xác nhận thanh toán thành công']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Xác nhận thất bại hoặc đơn hàng không ở trạng thái chờ']);
    }
    exit;
}
}