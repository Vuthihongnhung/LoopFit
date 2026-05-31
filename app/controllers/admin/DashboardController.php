<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;

class DashboardController extends Controller
{
    /* ===== Hiển thị trang dashboard (tổng quan) dành cho admin ===== */
    public function index()
    {
        $orderModel = new \App\Models\Order();
        $topProducts = $orderModel->getTopRentedProducts(10);
        
        $this->view('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'activeMenu' => 'dashboard',
            'topProducts' => $topProducts
        ]);
    }

    /* ===== API: Lấy các số liệu thống kê tổng quan ===== */
    public function apiGetStats()
    {
        $productModel = new \App\Models\Product();
        $orderModel = new \App\Models\Order();
        $userModel = new \App\Models\User();
        
        $product_count = $productModel->countAll(); // Tổng sản phẩm
        $order_count = $orderModel->countAll(); // Tổng đơn hàng
        $user_count = $userModel->countAll();   // Tổng người dùng
        $revenue = $orderModel->getRevenue();   // Doanh thu
        
        $pendingTransferCount = $orderModel->countPendingTransferOrders();  // Đơn chuyển khoản chờ xác nhận
        $pendingDepositCount = $orderModel->countPendingDepositReturns();   // Đơn chờ hoàn cọc
        
        $data = [
            'total_products' => (int)$product_count,
            'total_orders' => (int)$order_count,
            'total_users' => (int)$user_count,
            'revenue' => (float)$revenue,
            'pending_transfers' => (int)$pendingTransferCount,
            'pending_deposits' => (int)$pendingDepositCount,
            'message' => 'success'
        ];
        
        return $this->json($data);
    }

    /* ===== API: Lấy 5 đơn hàng chờ thanh toán chuyển khoản gần nhất ===== */
    public function apiGetPendingTransferOrders()
    {
        $orderModel = new \App\Models\Order();
        $orders = $orderModel->getPendingTransferOrders(5);
        
        foreach ($orders as &$order) {
            $order['amount'] = (float)($order['final_total'] ?? 0);
            unset($order['final_total']);
        }
        
        return $this->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /* ===== API: Lấy 5 đơn hàng chờ hoàn cọc gần nhất ===== */
    public function apiGetPendingDepositReturns()
    {
        $orderModel = new \App\Models\Order();
        $orders = $orderModel->getPendingDepositReturns(5);
        
        foreach ($orders as &$order) {
            $order['deposit_amount'] = (float)($order['deposit_amount'] ?? 0);
        }
        
        return $this->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /* ===== API: Lấy doanh thu theo từng tháng trong năm hiện tại ===== */
    public function apiMonthlyRevenue()
    {
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    SUM(total_price - discount_price) as revenue
                FROM orders 
                WHERE status = 'completed' AND payment_status = 'paid' AND YEAR(created_at) = YEAR(CURDATE())
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

    /* ===== API: Lấy 10 sản phẩm được thuê nhiều nhất ===== */
    public function apiTopProducts()
    {
        $orderItemModel = new \App\Models\OrderItem();
        $topProducts = $orderItemModel->getTopRentedProducts(10);
        
        return $this->json([
            'success' => true,
            'data' => $topProducts
        ]);
    }

    /* ===== Hỗ trợ trả về phản hồi JSON với mã HTTP status code ===== */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

       public function getRevenueDaily()
    {
        $orderModel = new \App\Models\Order();
        $revenue = $orderModel->getRevenueDaily(7);
        $this->json([
            'labels' => $revenue['labels'],
            'datasets' => [[
                'label' => 'Doanh thu (VNĐ)',
                'data' => $revenue['data'],
                'backgroundColor' => 'rgba(37, 99, 235, 0.7)',
                'borderColor' => '#2563eb',
                'borderWidth' => 1,
                'borderRadius' => 6
            ]]
        ]);
    }

    public function getRevenueMonthly()
    {
        $orderModel = new \App\Models\Order();
        $revenue = $orderModel->getRevenueMonthly(12);
        $this->json([
            'labels' => $revenue['labels'],
            'datasets' => [[
                'label' => 'Doanh thu (VNĐ)',
                'data' => $revenue['data'],
                'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                'borderColor' => '#2563eb',
                'borderWidth' => 1,
                'fill' => false
            ]]
        ]);
    }

    public function getRevenueQuarterly()
    {
        $orderModel = new \App\Models\Order();
        $revenue = $orderModel->getRevenueQuarterly();
        $this->json([
            'labels' => $revenue['labels'],
            'datasets' => [[
                'label' => 'Doanh thu (VNĐ)',
                'data' => $revenue['data'],
                'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                'borderColor' => '#10b981',
                'borderWidth' => 1,
                'borderRadius' => 6
            ]]
        ]);
    }

    public function getRevenueYearly()
    {
        $orderModel = new \App\Models\Order();
        $revenue = $orderModel->getRevenueYearly(5);
        $this->json([
            'labels' => $revenue['labels'],
            'datasets' => [[
                'label' => 'Doanh thu (VNĐ)',
                'data' => $revenue['data'],
                'backgroundColor' => 'rgba(234, 179, 8, 0.6)',
                'borderColor' => '#f59e0b',
                'borderWidth' => 1,
                'fill' => false
            ]]
        ]);
    }
}