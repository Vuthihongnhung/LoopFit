<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Middlewares\AdminMiddleware;

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
    
   public function index()
{
    require_once __DIR__ . '/../../../views/admin/dashboard.php';
}
}