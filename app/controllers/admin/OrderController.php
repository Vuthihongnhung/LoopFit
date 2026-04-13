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
    ob_start();
    require_once __DIR__ . '/../../../views/admin/orders/index.php';
    $content = ob_get_clean();
    $pageTitle = 'Quản lý đơn hàng';
    $activeMenu = 'orders';
    require_once __DIR__ . '/../../../views/layouts/admin.php';
}
    
    // API: Lấy danh sách đơn hàng (JSON)
    public function getAll()
    {
        $sql = "SELECT 
                    o.id,
                    u.name AS user_name,
                    u.email,
                    o.total_price,
                    o.status,
                    o.rent_date,
                    o.return_date,
                    o.actual_return_date,
                    o.late_fee,
                    o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC";

        $result = $this->db->query($sql);

        if (!$result) {
            return $this->json([
                "success" => false,
                "error" => $this->db->errorInfo()[2]
            ]);
        }

        $data = $result->fetchAll(\PDO::FETCH_ASSOC);

        return $this->json([
            "success" => true,
            "data" => $data
        ]);
    }
    
    // API: Lấy chi tiết đơn hàng (JSON)
    public function apiGetDetail()
    {
        $order_id = intval($_GET['id'] ?? 0);

        // Sử dụng prepared statement để tránh SQL injection
        $order_sql = "SELECT o.*, u.name, u.email 
                      FROM orders o
                      JOIN users u ON o.user_id = u.id
                      WHERE o.id = ?";
        $stmt = $this->db->prepare($order_sql);
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        $detail_sql = "SELECT od.*, p.name 
                       FROM order_details od
                       JOIN products p ON od.product_id = p.id
                       WHERE od.order_id = ?";
        $stmt = $this->db->prepare($detail_sql);
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->json([
            "success" => true,
            "order" => $order,
            "items" => $items
        ]);
    }
    
    // API: Cập nhật trạng thái đơn hàng
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
                $stmt = $this->db->prepare("SELECT return_date FROM orders WHERE id=?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                $return_date = strtotime($row['return_date']);
                $today = time();
                $late_fee = 0;

                if ($today > $return_date) {
                    $days_late = ceil(($today - $return_date) / (60 * 60 * 24));
                    $late_fee = $days_late * 50000;
                }

                $sql = "UPDATE orders 
                        SET status=?, actual_return_date=NOW(), late_fee=?
                        WHERE id=?";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([$status, $late_fee, $id]);
            } else {
                $sql = "UPDATE orders SET status=? WHERE id=?";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([$status, $id]);
            }

            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Cập nhật thành công'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => $this->db->errorInfo()[2]
                ]);
            }
        }
    }
    
    // Hiển thị chi tiết đơn hàng (trang HTML)
    public function detail()
    {
        $order_id = $_GET['id'] ?? 0;
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
        
        return $this->render('admin/orders/detail.php', [
            'order' => $data['order'],
            'items' => $data['items']
        ]);
    }
}