<?php

namespace App\Controllers\User;

use Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Middlewares\AuthMiddleware;

class OrderController extends Controller
{
    protected function getUserId()
    {
        // Sửa: session lưu user là mảng
        return $_SESSION['user']['id'] ?? null;
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function index()
    {
        // Kiểm tra đăng nhập
        AuthMiddleware::handle();

        $userId = $this->getUserId();
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $orderModel = new Order();
        $orders = $orderModel->getByUser($userId);

        // Lấy chi tiết sản phẩm nếu có model OrderItem
        if (class_exists('App\Models\OrderItem')) {
            $orderItemModel = new OrderItem();
            foreach ($orders as &$order) {
                $order['items'] = $orderItemModel->getByOrder($order['id']);
            }
        }

        // Dùng view() để có layout user.php
        return $this->view('user/orders', ['orders' => $orders]);
    }
}