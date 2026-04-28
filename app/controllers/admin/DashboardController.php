<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;
use Core\Database;

class DashboardController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::check();
    }
    
    public function apiGetStats()
    {
        $productModel = new \App\Models\Product();
        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        
        $product_count = $productModel->countAll();
        $order_count = $orderModel->countAll();
        $user_count = $userModel->countAll();
        $revenue = $orderModel->getRevenue();
        
        $data = [
            'total_products' => (int)$product_count,
            'total_orders' => (int)$order_count,
            'total_users' => (int)$user_count,
            'revenue' => (float)$revenue,
            'message' => 'success'
        ];
        
        return $this->json($data);
    }
    
    // ===== THÊM MỚI: API doanh thu theo tháng (cho biểu đồ) =====
    public function apiMonthlyRevenue()
    {
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    SUM(discount_price) as revenue
                FROM orders 
                WHERE status = 'completed' AND YEAR(created_at) = YEAR(CURDATE())
                GROUP BY MONTH(created_at)
                ORDER BY month ASC";
        
        $result = $db->query($sql);
        
        $monthlyRevenue = array_fill(1, 12, 0);
        $labels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $monthlyRevenue[(int)$row['month']] = (float)$row['revenue'];
            }
        }
        
        return $this->json([
            'success' => true,
            'data' => array_values($monthlyRevenue),
            'labels' => $labels
        ]);
    }
    
    // ===== THÊM MỚI: API top sản phẩm cho thuê nhiều nhất =====
    public function apiTopProducts()
    {
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "SELECT 
                    p.id, 
                    p.name,
                    COUNT(od.id) as total_rented,
                    SUM(od.price * od.quantity * (o.discount_price / o.total_price)) as total_revenue
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                JOIN orders o ON od.order_id = o.id
                WHERE o.status = 'completed'
                GROUP BY p.id
                ORDER BY total_rented DESC
                LIMIT 10";
        
        $result = $db->query($sql);
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        return $this->json([
            'success' => true,
            'data' => $products
        ]);
    }
    
    public function index()
    {
        $this->view('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'activeMenu' => 'dashboard'
        ]);
    }
}